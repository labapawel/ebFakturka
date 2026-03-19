<?php

namespace App\Services;

use DOMDocument;
use RuntimeException;

class GusService
{
    private const ENDPOINT = 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc';
    private const ACTION_BASE = 'http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/';
    private const SILOS_REPORTS = [
        '1' => 'BIR11OsFizycznaDzialalnoscCeidg',
        '2' => 'BIR11OsFizycznaGosSam',
        '3' => 'BIR11OsFizycznaGosSamPraw',
        '4' => 'BIR11OsFizycznaInnaFPP',
        '5' => 'BIR11OsPrawna',
        '6' => 'BIR11OsPrawna',
        '7' => 'BIR11JednostkaLokalnaCEIDG',
        '8' => 'BIR11JednostkaLokalnaGosSam',
        '9' => 'BIR11JednostkaLokalnaGosSamPraw',
    ];

    private string $endpoint;
    private string $birKey;
    private string $sessionId = '';

    public function __construct()
    {
        $this->birKey = (string) env('BIR_KEY', '');

        if ($this->birKey === '') {
            throw new RuntimeException('Brak konfiguracji BIR_KEY dla integracji GUS.');
        }

        $this->endpoint = self::ENDPOINT;
    }

    public function fetchByNip(string $nip): array
    {
        $normalizedNip = preg_replace('/\D+/', '', $nip);

        if (!is_string($normalizedNip) || strlen($normalizedNip) !== 10) {
            throw new RuntimeException('Podaj prawidlowy numer NIP.');
        }

        $this->login();

        try {
            $entities = $this->searchByNip($normalizedNip);

            if ($entities === []) {
                throw new RuntimeException('Nie znaleziono podmiotu w GUS dla podanego NIP.');
            }

            $basicData = $entities[0];
            $regon = $basicData['Regon'] ?? '';
            $silosId = $basicData['SilosID'] ?? '6';
            $fullReport = [];

            if ($regon !== '') {
                $reports = $this->fetchFullReport($regon, $silosId);
                $fullReport = $reports[0] ?? $reports;
            }

            return $this->mapToContractorPayload($normalizedNip, $basicData, $fullReport);
        } finally {
            $this->logout();
        }
    }

    private function login(): void
    {
        $body = <<<XML
            <Zaloguj xmlns="http://CIS/BIR/PUBL/2014/07">
                <pKluczUzytkownika>{$this->escapeXml($this->birKey)}</pKluczUzytkownika>
            </Zaloguj>
        XML;

        $responseXml = $this->soapCall('Zaloguj', $body);
        $sessionId = $this->extractValue($responseXml, 'ZalogujResult');

        if ($sessionId === '') {
            $fault = $this->extractSoapFault($responseXml);
            $message = $fault !== ''
                ? "Logowanie do GUS nie powiodlo sie: {$fault}"
                : 'Logowanie do GUS nie powiodlo sie. Sprawdz BIR_KEY.';

            throw new RuntimeException($message);
        }

        $this->sessionId = $sessionId;
    }

    private function searchByNip(string $nip): array
    {
        $body = <<<XML
            <DaneSzukajPodmioty xmlns="http://CIS/BIR/PUBL/2014/07">
                <pParametryWyszukiwania xmlns:dat="http://CIS/BIR/PUBL/2014/07/DataContract">
                    <dat:Nip>{$this->escapeXml($nip)}</dat:Nip>
                </pParametryWyszukiwania>
            </DaneSzukajPodmioty>
        XML;

        $responseXml = $this->soapCall('DaneSzukajPodmioty', $body);
        $resultXml = $this->extractValue($responseXml, 'DaneSzukajPodmiotyResult');

        if (trim($resultXml) === '') {
            return [];
        }

        return $this->parseDataXml($resultXml);
    }

    private function fetchFullReport(string $regon, string $silosId): array
    {
        $reportName = self::SILOS_REPORTS[$silosId] ?? 'BIR11OsPrawna';

        $body = <<<XML
            <DanePobierzPelnyRaport xmlns="http://CIS/BIR/PUBL/2014/07">
                <pRegon>{$this->escapeXml($regon)}</pRegon>
                <pNazwaRaportu>{$this->escapeXml($reportName)}</pNazwaRaportu>
            </DanePobierzPelnyRaport>
        XML;

        $responseXml = $this->soapCall('DanePobierzPelnyRaport', $body);
        $resultXml = $this->extractValue($responseXml, 'DanePobierzPelnyRaportResult');

        if (trim($resultXml) === '') {
            return [];
        }

        return $this->parseDataXml($resultXml);
    }

    private function logout(): void
    {
        if ($this->sessionId === '') {
            return;
        }

        $body = <<<XML
            <Wyloguj xmlns="http://CIS/BIR/PUBL/2014/07">
                <pIdentyfikatorSesji>{$this->escapeXml($this->sessionId)}</pIdentyfikatorSesji>
            </Wyloguj>
        XML;

        try {
            $this->soapCall('Wyloguj', $body);
        } finally {
            $this->sessionId = '';
        }
    }

    private function soapCall(string $action, string $bodyContent): string
    {
        $actionUri = self::ACTION_BASE.$action;
        $envelope = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
               xmlns:wsa="http://www.w3.org/2005/08/addressing">
    <soap:Header>
        <wsa:Action>{$actionUri}</wsa:Action>
        <wsa:To>{$this->endpoint}</wsa:To>
    </soap:Header>
    <soap:Body>
{$bodyContent}
    </soap:Body>
</soap:Envelope>
XML;

        $headers = [
            'Content-Type: application/soap+xml;charset=UTF-8;action="'.$actionUri.'"',
            'Host: '.parse_url($this->endpoint, PHP_URL_HOST),
        ];

        if ($this->sessionId !== '') {
            $headers[] = 'sid: '.$this->sessionId;
        }

        $handle = curl_init($this->endpoint);

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $envelope,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($error !== '') {
            throw new RuntimeException("Blad polaczenia z GUS: {$error}");
        }

        if (!is_string($response) || $response === '') {
            throw new RuntimeException('GUS nie zwrocil odpowiedzi.');
        }

        if ($httpCode >= 400) {
            throw new RuntimeException("GUS API zwrocil HTTP {$httpCode}.");
        }

        return $response;
    }

    private function extractValue(string $soapXml, string $tagName): string
    {
        $xmlPart = $this->extractXmlFromMtom($soapXml);
        $document = new DOMDocument();

        if (!@$document->loadXML($xmlPart)) {
            return '';
        }

        $nodes = $document->getElementsByTagName($tagName);

        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function extractXmlFromMtom(string $response): string
    {
        $xmlStart = strpos($response, '<?xml');
        if ($xmlStart !== false) {
            $xmlResponse = substr($response, $xmlStart);
            $envelope = $this->extractSoapEnvelope($xmlResponse);

            return $envelope !== '' ? $envelope : $xmlResponse;
        }

        foreach (['<s:Envelope', '<soap:Envelope'] as $tag) {
            $position = strpos($response, $tag);
            if ($position !== false) {
                $xmlResponse = substr($response, $position);
                $envelope = $this->extractSoapEnvelope($xmlResponse);

                return $envelope !== '' ? $envelope : $xmlResponse;
            }
        }

        return $response;
    }

    private function extractSoapEnvelope(string $response): string
    {
        foreach (['</s:Envelope>', '</soap:Envelope>'] as $closingTag) {
            $end = strpos($response, $closingTag);
            if ($end !== false) {
                return substr($response, 0, $end + strlen($closingTag));
            }
        }

        return '';
    }

    private function parseDataXml(string $xml): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        if (!@$document->loadXML('<?xml version="1.0" encoding="UTF-8"?>'.$xml)) {
            throw new RuntimeException('Nie udalo sie sparsowac odpowiedzi GUS.');
        }

        $root = $document->documentElement;
        $rows = $root->tagName === 'dane'
            ? [$root]
            : iterator_to_array($root->getElementsByTagName('dane'));

        $results = [];

        foreach ($rows as $row) {
            $record = [];

            foreach ($row->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $record[$child->tagName] = trim($child->textContent);
                }
            }

            if ($record !== []) {
                $results[] = $record;
            }
        }

        return $results;
    }

    private function extractSoapFault(string $soapXml): string
    {
        $xmlPart = $this->extractXmlFromMtom($soapXml);
        $document = new DOMDocument();

        if (!@$document->loadXML($xmlPart)) {
            return '';
        }

        foreach (['Text', 'Reason', 'faultstring'] as $tagName) {
            $nodes = $document->getElementsByTagName($tagName);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                if ($text !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    private function mapToContractorPayload(string $nip, array $basicData, array $fullReport): array
    {
        return [
            'name' => $fullReport['nazwa'] ?? $basicData['Nazwa'] ?? '',
            'nip' => $nip,
            'address_street' => $fullReport['ulica'] ?? $basicData['Ulica'] ?? '',
            'address_building' => $fullReport['nrNieruchomosci'] ?? $basicData['NrNieruchomosci'] ?? '',
            'address_apartment' => $fullReport['nrLokalu'] ?? $basicData['NrLokalu'] ?? '',
            'postal_code' => $fullReport['kodPocztowy'] ?? $basicData['KodPocztowy'] ?? '',
            'city' => $fullReport['miejscowosc'] ?? $basicData['Miejscowosc'] ?? '',
            'gus_data' => [
                'podstawowe' => $basicData,
                'pelny' => $fullReport,
            ],
        ];
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
