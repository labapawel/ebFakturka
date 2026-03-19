<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class KsefClient
{
    protected string $baseUrl;
    protected string $nip;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('ksef.url');
        $this->nip = config('ksef.nip');
        $this->token = config('ksef.token');
    }

    protected function http()
    {
        return Http::withOptions([
            'verify' => false,
            'version' => 1.1, // Force HTTP/1.1
            'allow_redirects' => true,
            'cookies' => new \GuzzleHttp\Cookie\CookieJar(),
        ])->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'pl-PL,pl;q=0.9,en-US;q=0.8,en;q=0.7',
            'Upgrade-Insecure-Requests' => '1',
        ]);
    }

    protected function get(string $url, array $query = [], array $headers = [])
    {
        $client = $this->http();
        if (!empty($headers)) {
            $client = $client->withHeaders($headers);
        }

        $response = $client->get($url, $query);
        return $this->handleIncapsula($response, 'get', $url, $query, $headers);
    }

    protected function post(string $url, array $data = [], array $headers = [])
    {
        $client = $this->http();
        if (!empty($headers)) {
            $client = $client->withHeaders($headers);
        }

        if (($headers['Content-Type'] ?? null) === 'application/json') {
            $response = $client->send('POST', $url, ['json' => $data]);
        } else {
            $response = $client->post($url, $data);
        }

        return $this->handleIncapsula($response, 'post', $url, $data, $headers);
    }

    protected function handleIncapsula($response, $method, $url, $data, $headers = [])
    {
        // Check if response is the Incapsula "Loading" page
        // It's usually a 200 OK or 302 Found with specific HTML content
        $body = $response->body();
        if (strpos($body, '<HTML>') !== false && strpos($body, 'Loading') !== false) {
             // It's Incapsula. Wait and Retry.
             sleep(2);
             
             $client = $this->http();
             if (!empty($headers)) {
                 $client = $client->withHeaders($headers);
             }
             
             if ($method === 'get') {
                 return $client->get($url, $data);
             } else {
                 return $client->post($url, $data);
             }
        }
        
        return $response;
    }

    /* 
    public function getSessionToken(): string
    {
         if (Cache::has('ksef_session_token')) {
            return Cache::get('ksef_session_token');
        }

        $token = $this->authenticate();
        Cache::put('ksef_session_token', $token, now()->addMinutes(20));

        return $token;
    }
    */
    // Keeping original getSessionToken but commenting out to not break replace format, 
    // actually I will just keep the methods and update usage in authenticate().

    public function getSessionToken(): string
    {
        if (Cache::has('ksef_session_token')) {
            return Cache::get('ksef_session_token');
        }

        $token = $this->authenticate();
        Cache::put('ksef_session_token', $token, now()->addMinutes(14));

        return $token;
    }

    protected function authenticate(): string
    {
        // 1. Pobranie auth challenge
        $challenge = $this->getAuthChallenge();
        if (!$challenge) {
            throw new Exception("Błąd pobierania auth challenge z KSeF.");
        }

        // 2. Zaszyfrowanie tokena KSeF
        $encryptedToken = $this->encryptToken($this->token, $challenge['timestamp']);
        if (!$encryptedToken) {
            throw new Exception("Błąd szyfrowania tokena KSeF.");
        }

        // 3. Wysłanie żądania uwierzytelnienia tokenem KSeF
        $authResponse = $this->submitKsefTokenAuthRequest($challenge, $encryptedToken);
        if (!$authResponse) {
            throw new Exception("Błąd weryfikacji tokena KSeF.");
        }

        // 4. Pobieranie accessToken (session token)
        $tokens = $this->redeemToken($authResponse['authenticationToken']['token'] ?? $authResponse['authenticationToken']);
        if (!$tokens || !isset($tokens['accessToken'])) {
            throw new Exception("Błąd pobrania Access Tokena z KSeF.");
        }

        return $tokens['accessToken'];
    }

    private function getAuthChallenge(): ?array
    {
        $response = $this->post("{$this->baseUrl}/auth/challenge", [], ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
        if (!$response->successful()) {
            throw new Exception("KSeF Challenge Error: " . $response->body());
        }
        $data = $response->json();
        return (isset($data['challenge']) && isset($data['timestamp'])) ? ['challenge' => $data['challenge'], 'timestamp' => $data['timestamp']] : null;
    }

    private function encryptToken(string $ksefToken, string $timestamp): ?string
    {
        try {
            $dt = new \DateTime($timestamp);
            $dt->setTimezone(new \DateTimeZone('UTC'));
            $seconds = $dt->format('U');
            $microseconds = (int)$dt->format('u');
            $timestampMs = ((int)$seconds * 1000) + intdiv($microseconds, 1000);
            
            $plaintext = "{$ksefToken}|{$timestampMs}";
            $publicKeyPem = $this->getKsefPublicKey();
            
            if (!$publicKeyPem) {
                return null;
            }
            
            $encrypted = $this->encryptRsaOaep($plaintext, $publicKeyPem);
            return $encrypted ? base64_encode($encrypted) : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function encryptRsaOaep(string $plaintext, string $certPem): ?string
    {
        $tempDir = sys_get_temp_dir();
        $plainFile = $tempDir . '/ksef_plain_' . uniqid() . '.txt';
        $certFile = $tempDir . '/ksef_cert_' . uniqid() . '.pem';
        $keyFile = $tempDir . '/ksef_pubkey_' . uniqid() . '.pem';
        $encFile = $tempDir . '/ksef_enc_' . uniqid() . '.bin';

        try {
            file_put_contents($plainFile, $plaintext);
            file_put_contents($certFile, $certPem);

            $extractKeyCmd = sprintf('openssl x509 -in %s -pubkey -noout -out %s 2>&1', escapeshellarg($certFile), escapeshellarg($keyFile));
            exec($extractKeyCmd, $output, $return);
            if ($return !== 0) return null;

            // Używamy opcji dla SHA-256 (i MGF1 SHA-256) by spełnić wymagania KSeF (AES i Auth).
            $encryptCmd = sprintf('openssl pkeyutl -encrypt -pubin -inkey %s -pkeyopt rsa_padding_mode:oaep -pkeyopt rsa_oaep_md:sha256 -pkeyopt rsa_mgf1_md:sha256 -in %s -out %s 2>&1', escapeshellarg($keyFile), escapeshellarg($plainFile), escapeshellarg($encFile));
            exec($encryptCmd, $output, $return);
            if ($return !== 0 || !file_exists($encFile)) return null;

            return file_get_contents($encFile) ?: null;
        } finally {
            @unlink($plainFile); @unlink($certFile); @unlink($keyFile); @unlink($encFile);
        }
    }

    private function getKsefPublicKey(string $usage = 'KsefTokenEncryption'): ?string
    {
        $response = $this->get("{$this->baseUrl}/security/public-key-certificates");
        if (!$response->successful()) {
            throw new Exception("KSeF Public Key Error: " . $response->body());
        }
        $certificates = $response->json();
        $tokenEncryptionCert = collect($certificates)->first(fn($c) => in_array($usage, $c['usage'] ?? []));
        
        if (!$tokenEncryptionCert || !isset($tokenEncryptionCert['certificate'])) return null;
        
        $certBinary = base64_decode($tokenEncryptionCert['certificate']);
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($certBinary), 64, "\n") . "-----END CERTIFICATE-----\n";
    }

    private function submitKsefTokenAuthRequest(array $challenge, string $encryptedToken): ?array
    {
        $payload = [
            'challenge' => $challenge['challenge'],
            'contextIdentifier' => ['type' => 'nip', 'value' => $this->nip],
            'encryptedToken' => $encryptedToken
        ];
        $response = $this->post("{$this->baseUrl}/auth/ksef-token", $payload, ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
        
        if (!in_array($response->status(), [200, 202])) {
            throw new Exception("KSeF Auth Token Error: " . $response->body());
        }
        return $response->json();
    }

    private function redeemToken(string $authenticationToken): ?array
    {
        $response = $this->post("{$this->baseUrl}/auth/token/redeem", [], [
            'Authorization' => 'Bearer ' . $authenticationToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
        if (!$response->successful()) {
            throw new Exception("KSeF Redeem Token Error: " . $response->body());
        }
        
        $data = $response->json();
        $accessToken = $data['accessToken']['token'] ?? $data['accessToken'] ?? ($data['access_token']['token'] ?? $data['access_token'] ?? null);
        $refreshToken = $data['refreshToken']['token'] ?? $data['refreshToken'] ?? ($data['refresh_token']['token'] ?? $data['refresh_token'] ?? null);
        
        return ($accessToken && $refreshToken) ? ['accessToken' => $accessToken, 'refreshToken' => $refreshToken] : null;
    }

    public function getInvoices(Carbon $from, Carbon $to, string $subjectType = 'Subject2')
    {
        $sessionToken = $this->getSessionToken();
        
        $payload = [
            'subjectType' => $subjectType,
            'dateRange' => [
                'dateType' => 'Invoicing',
                'from' => $from->setTimezone('UTC')->format('Y-m-d\TH:i:s.vP'),
                'to' => $to->setTimezone('UTC')->format('Y-m-d\TH:i:s.vP')
            ]
        ];

        $response = $this->post("{$this->baseUrl}/invoices/query/metadata?PageSize=100&PageOffset=0", $payload, [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        if (!$response->successful()) {
             if ($response->status() === 401) {
                 Cache::forget('ksef_session_token');
                 return $this->getInvoices($from, $to, $subjectType); // Retry raz
             }
             throw new Exception("KSeF Query Error: " . $response->body());
        }

        return $response->json();
    }
    
    public function getInvoiceXml(string $ksefReferenceNumber)
    {
        $sessionToken = $this->getSessionToken();
        
        $url = "{$this->baseUrl}/invoices/ksef/{$ksefReferenceNumber}";
        $client = $this->http();
        
        $headers = [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Accept' => 'application/octet-stream'
        ];
        
        $response = $client->withHeaders($headers)->get($url);
        $response = $this->handleIncapsula($response, 'get', $url, [], $headers);

        // Handle 401 as expired token edge-case
        if ($response->status() === 401) {
            Cache::forget('ksef_session_token');
            // Try one more time with new token
            $sessionToken = $this->getSessionToken();
            $headers['Authorization'] = 'Bearer ' . $sessionToken;
            $response = $client->withHeaders($headers)->get($url);
        }

        if (!$response->successful()) {
            throw new Exception("KSeF GetInvoice Error: " . $response->body());
        }
        
        return $response->body();
    }

    public function sendInvoice(string $xmlContent): array
    {
        $sessionToken = $this->getSessionToken();
        
        // Krok 0: Pobierz certyfikat do szyfrowania klucza symetrycznego
        $publicKeyCertPem = $this->getKsefPublicKey('SymmetricKeyEncryption');
        if (!$publicKeyCertPem) {
            throw new Exception("Błąd pobrania klucza SymmetricKeyEncryption z KSeF.");
        }

        // Krok 1: Wygeneruj klucz symetryczny
        $symmetricKey = openssl_random_pseudo_bytes(32);
        $iv = openssl_random_pseudo_bytes(16);

        // Krok 2: Zaszyfruj klucz symetryczny
        $encryptedSymmetricKeyBinary = $this->encryptRsaOaep($symmetricKey, $publicKeyCertPem);
        if (!$encryptedSymmetricKeyBinary) {
            throw new Exception("Błąd szyfrowania klucza symetrycznego AES.");
        }
        $encryptedSymmetricKey = base64_encode($encryptedSymmetricKeyBinary);

        // Krok 3: Zaszyfruj fakturę
        $encryptedInvoice = openssl_encrypt(
            $xmlContent,
            'AES-256-CBC',
            $symmetricKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($encryptedInvoice === false) {
            throw new Exception("Błąd szyfrowania faktury XML: " . openssl_error_string());
        }

        // Krok 4a: Otwórz sesję interaktywną
        $sessionRef = $this->openInteractiveSession($sessionToken, $encryptedSymmetricKey, $iv);

        // Krok 4b: Wyślij fakturę
        $invoiceData = $this->submitInvoice($sessionToken, $sessionRef, $xmlContent, $encryptedInvoice);

        // Krok 5: Zamknij sesję
        $this->closeInteractiveSession($sessionToken, $sessionRef);

        return [
            'sessionReferenceNumber' => $sessionRef,
            'invoiceReferenceNumber' => $invoiceData['referenceNumber'],
            'invoiceHash' => $invoiceData['invoiceHashBase64Url']
        ];
    }

    public function getSessionInvoiceStatus(string $sessionReferenceNumber, string $invoiceReferenceNumber): array
    {
        $sessionToken = $this->getSessionToken();

        $response = $this->get("{$this->baseUrl}/sessions/{$sessionReferenceNumber}/invoices/{$invoiceReferenceNumber}", [], [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Accept' => 'application/json',
        ]);

        if ($response->status() === 401) {
            Cache::forget('ksef_session_token');

            return $this->getSessionInvoiceStatus($sessionReferenceNumber, $invoiceReferenceNumber);
        }

        if (!$response->successful()) {
            throw new Exception("KSeF GetSessionInvoiceStatus Error: " . $response->body());
        }

        return $response->json();
    }

    public function waitForSessionInvoiceStatus(string $sessionReferenceNumber, string $invoiceReferenceNumber, int $attempts = 4, int $delaySeconds = 2): array
    {
        $latestStatus = [];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $latestStatus = $this->getSessionInvoiceStatus($sessionReferenceNumber, $invoiceReferenceNumber);
            $statusCode = (int) ($latestStatus['status']['code'] ?? 0);

            if (!in_array($statusCode, [100, 150], true)) {
                return $latestStatus;
            }

            if ($attempt < $attempts) {
                sleep($delaySeconds);
            }
        }

        return $latestStatus;
    }

    private function openInteractiveSession(string $sessionToken, string $encryptedSymmetricKey, string $iv): string
    {
        $payload = [
            'formCode' => [
                'systemCode' => 'FA (3)',
                'schemaVersion' => '1-0E',
                'value' => 'FA'
            ],
            'encryption' => [
                'encryptedSymmetricKey' => $encryptedSymmetricKey,
                'initializationVector' => base64_encode($iv)
            ],
        ];

        $response = $this->post("{$this->baseUrl}/sessions/online", $payload, [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        if ($response->status() !== 201) {
            throw new Exception("Błąd otwierania sesji wysyłkowej KSeF: " . $response->body());
        }

        return $response->json('referenceNumber');
    }

    private function submitInvoice(string $sessionToken, string $sessionRef, string $xmlContent, string $encryptedInvoice): array
    {
        $invoiceHashBinary = hash('sha256', $xmlContent, true);
        $invoiceHash = base64_encode($invoiceHashBinary);
        $invoiceHashBase64Url = strtr(rtrim(base64_encode($invoiceHashBinary), '='), '+/', '-_');
        $invoiceSize = strlen($xmlContent);

        $encryptedHash = base64_encode(hash('sha256', $encryptedInvoice, true));
        $encryptedSize = strlen($encryptedInvoice);

        $payload = [
            'invoiceHash' => $invoiceHash,
            'invoiceSize' => $invoiceSize,
            'encryptedInvoiceHash' => $encryptedHash,
            'encryptedInvoiceSize' => $encryptedSize,
            'encryptedInvoiceContent' => base64_encode($encryptedInvoice)
        ];

        $response = $this->post("{$this->baseUrl}/sessions/online/{$sessionRef}/invoices", $payload, [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        if (!in_array($response->status(), [201, 202])) {
            throw new Exception("Błąd wysyłania dokumentu do KSeF: " . $response->body());
        }

        return [
            'referenceNumber' => $response->json('referenceNumber'),
            'invoiceHashBase64Url' => $invoiceHashBase64Url
        ];
    }

    private function closeInteractiveSession(string $sessionToken, string $sessionRef): void
    {
        $this->post("{$this->baseUrl}/sessions/online/{$sessionRef}/close", [], [
            'Authorization' => 'Bearer ' . $sessionToken,
            'Content-Length' => '0'
        ]);
        // Ignorujemy błędy zamykania, aby nie blokować procesu, ew. logujemy
    }
}
