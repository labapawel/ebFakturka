<?php

namespace App\Services;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use App\Support\VatSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SimpleXMLElement;

class KsefService
{
    public function validateForSending(Invoice $invoice): array
    {
        $errors = [];

        if (!$invoice->contractor) {
            $errors[] = 'Brak kontrahenta na fakturze.';
        }

        if (!$invoice->currency) {
            $errors[] = 'Brak waluty na fakturze.';
        }

        if ($invoice->items->isEmpty()) {
            $errors[] = 'Faktura musi zawierać co najmniej jedną pozycję.';
        }

        if (blank(config('company.nip'))) {
            $errors[] = 'Brak NIP wystawcy w konfiguracji firmy.';
        }

        if (blank(config('company.name'))) {
            $errors[] = 'Brak nazwy wystawcy w konfiguracji firmy.';
        }

        if ($invoice->contractor) {
            if (blank($invoice->contractor->nip)) {
                $errors[] = 'Kontrahent nie ma uzupełnionego NIP.';
            }

            if (blank($invoice->contractor->name)) {
                $errors[] = 'Kontrahent nie ma uzupełnionej nazwy.';
            }
        }

        return $errors;
    }

    public function generateXml(Invoice $invoice): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Faktura xmlns="http://crd.gov.pl/wzor/2025/06/25/13775/"></Faktura>');

        $naglowek = $xml->addChild('Naglowek');
        $naglowek->addChild('KodFormularza', 'FA')->addAttribute('kodSystemowy', 'FA (3)');
        $naglowek->KodFormularza->addAttribute('wersjaSchemy', '1-0E');
        $naglowek->addChild('WariantFormularza', '3');
        $naglowek->addChild('DataWytworzeniaFa', now()->format('Y-m-d\TH:i:s\Z'));
        $naglowek->addChild('SystemInfo', 'ebFakturka');

        if ($invoice->type === 'purchase') {
            $sprzedawca = $invoice->contractor;

            $podmiot1 = $xml->addChild('Podmiot1');
            $podmiot1->addChild('PrefiksPodatnika', 'PL');
            $daneId1 = $podmiot1->addChild('DaneIdentyfikacyjne');
            $daneId1->addChild('NIP', $sprzedawca->nip ?? 'BRAK');
            $daneId1->addChild('Nazwa', $this->contractorLegalName($sprzedawca));
            $this->appendContractorMainAddress($podmiot1, $sprzedawca);
            $this->appendContractorCorrespondenceData($podmiot1, $sprzedawca);

            $podmiot2 = $xml->addChild('Podmiot2');
            $daneId2 = $podmiot2->addChild('DaneIdentyfikacyjne');
            $daneId2->addChild('NIP', config('company.nip'));
            $daneId2->addChild('Nazwa', config('company.name'));
            $adres2 = $podmiot2->addChild('Adres');
            $adres2->addChild('KodKraju', 'PL');
            $adres2->addChild('AdresL1', trim(config('company.street') . ' ' . config('company.building_number')));
            $adres2->addChild('AdresL2', trim(config('company.postal_code') . ' ' . config('company.city')));
            $podmiot2->addChild('JST', $this->booleanToKsefFlag((bool) ($sprzedawca->is_jst ?? false)));
            $podmiot2->addChild('GV', $this->booleanToKsefFlag((bool) ($sprzedawca->is_vat_group_member ?? false)));
        } else {
            $podmiot1 = $xml->addChild('Podmiot1');
            $podmiot1->addChild('PrefiksPodatnika', 'PL');
            $daneId1 = $podmiot1->addChild('DaneIdentyfikacyjne');
            $daneId1->addChild('NIP', config('company.nip'));
            $daneId1->addChild('Nazwa', config('company.name'));
            $adres1 = $podmiot1->addChild('Adres');
            $adres1->addChild('KodKraju', 'PL');
            $adres1->addChild('AdresL1', trim(config('company.street') . ' ' . config('company.building_number')));
            $adres1->addChild('AdresL2', trim(config('company.postal_code') . ' ' . config('company.city')));

            $podmiot2 = $xml->addChild('Podmiot2');
            $daneId2 = $podmiot2->addChild('DaneIdentyfikacyjne');
            $daneId2->addChild('NIP', $invoice->contractor->nip ?? 'BRAK');
            $daneId2->addChild('Nazwa', $this->contractorLegalName($invoice->contractor));
            $this->appendContractorMainAddress($podmiot2, $invoice->contractor);
            $this->appendContractorCorrespondenceData($podmiot2, $invoice->contractor);
            $podmiot2->addChild('JST', $this->booleanToKsefFlag((bool) ($invoice->contractor->is_jst ?? false)));
            $podmiot2->addChild('GV', $this->booleanToKsefFlag((bool) ($invoice->contractor->is_vat_group_member ?? false)));
        }

        $fa = $xml->addChild('Fa');
        $fa->addChild('KodWaluty', $invoice->currency->code);
        $fa->addChild('P_1', $invoice->issue_date->format('Y-m-d'));
        $fa->addChild('P_2', $invoice->number);
        $fa->addChild('P_6', $invoice->sale_date->format('Y-m-d'));
        $fa->addChild('RodzajFaktury', 'VAT');

        $isVatExempt = VatSettings::isExempt();
        $vatExemptionReason = VatSettings::legalBasis();

        if ($isVatExempt) {
            $fa->addChild('P_13_7', number_format($invoice->net_total, 2, '.', ''));
            $fa->addChild('P_15', number_format($invoice->gross_total, 2, '.', ''));
            $zwolnienie = $fa->addChild('Zwolnienie');
            $zwolnienie->addChild('P_19', '1');
            if ($vatExemptionReason) {
                $zwolnienie->addChild(VatSettings::reasonField(), $vatExemptionReason);
            }
        } else {
            $fa->addChild('P_13_1', number_format($invoice->net_total, 2, '.', ''));
            $fa->addChild('P_14_1', number_format($invoice->vat_total, 2, '.', ''));
            $fa->addChild('P_15', number_format($invoice->gross_total, 2, '.', ''));
        }

        foreach ($invoice->items as $index => $item) {
            $wiersz = $fa->addChild('FaWiersz');
            $wiersz->addChild('NrWierszaFa', $index + 1);
            $wiersz->addChild('P_7', $item->name);
            $wiersz->addChild('P_8A', $item->unit);
            $wiersz->addChild('P_8B', number_format($item->quantity, 2, '.', ''));
            $wiersz->addChild('P_9A', number_format($item->net_price, 2, '.', ''));
            $wiersz->addChild('P_11', number_format($item->net_price * $item->quantity, 2, '.', ''));

            if ($isVatExempt) {
                $wiersz->addChild('P_12', 'zw');
            } else {
                $wiersz->addChild('P_12', $item->vat_rate ? ($item->vat_rate * 100) : 'ZW');
            }
        }

        $this->appendPaymentData($fa, $invoice);

        return $xml->asXML();
    }

    public function importInvoiceFromKsef(string $payload, string $ksefNumber, ?int $userId = null): Invoice
    {
        $xmlContent = $this->normalizeKsefInvoicePayload($payload);
        $invoiceData = $this->extractInvoiceDataFromXml($xmlContent, $ksefNumber);
        $resolvedUserId = $this->resolveUserId($userId);

        return DB::transaction(function () use ($invoiceData, $xmlContent, $ksefNumber, $resolvedUserId) {
            $contractor = Contractor::updateOrCreate(
                ['nip' => $invoiceData['contractor']['nip'] ?: null, 'name' => $invoiceData['contractor']['name']],
                $invoiceData['contractor']
            );

            $currency = Currency::firstOrCreate(
                ['code' => $invoiceData['currency_code']],
                [
                    'name' => $invoiceData['currency_code'],
                    'exchange_rate' => 1,
                    'is_default' => $invoiceData['currency_code'] === 'PLN',
                ]
            );

            $invoice = Invoice::updateOrCreate(
                ['ksef_number' => $ksefNumber],
                [
                    'type' => 'purchase',
                    'number' => $invoiceData['number'],
                    'issue_date' => $invoiceData['issue_date'],
                    'sale_date' => $invoiceData['sale_date'],
                    'due_date' => $invoiceData['due_date'],
                    'payment_method' => $invoiceData['payment_method'],
                    'contractor_id' => $contractor->id,
                    'user_id' => $resolvedUserId,
                    'currency_id' => $currency->id,
                    'net_total' => $invoiceData['net_total'],
                    'vat_total' => $invoiceData['vat_total'],
                    'gross_total' => $invoiceData['gross_total'],
                    'status' => 'issued',
                    'ksef_status' => 'fetched',
                ]
            );

            $invoice->items()->delete();
            $invoice->items()->createMany($invoiceData['items']);

            Storage::disk('local')->put($this->storedXmlPath($ksefNumber), $xmlContent);

            return $invoice->fresh(['contractor', 'currency', 'items']);
        });
    }

    public function getStoredXmlContents(Invoice|string $invoice): ?string
    {
        $path = $this->storedXmlPath($invoice instanceof Invoice ? $invoice->ksef_number : $invoice);

        if (!$path || !Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    public function storedXmlPath(?string $ksefNumber): ?string
    {
        if (!$ksefNumber) {
            return null;
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $ksefNumber);

        return "ksef/invoices/{$safeName}.xml";
    }

    private function normalizeKsefInvoicePayload(string $payload): string
    {
        if ($this->looksLikeXml($payload)) {
            return $payload;
        }

        return $this->decryptInvoicePayload($payload);
    }

    private function looksLikeXml(string $content): bool
    {
        $trimmed = ltrim($content);

        return str_starts_with($trimmed, '<?xml') || str_starts_with($trimmed, '<');
    }

    private function decryptInvoicePayload(string $payload): string
    {
        $privateKeyPath = config('ksef.private_key_path');

        if (!$privateKeyPath || !is_file($privateKeyPath)) {
            throw new RuntimeException('Brak klucza prywatnego KSeF do odszyfrowania pobranej faktury.');
        }

        $keyDetails = $this->getPrivateKeyDetails($privateKeyPath, config('ksef.key_passphrase'));
        $encryptedKeySize = (int) ceil(($keyDetails['bits'] ?? 2048) / 8);

        $encryptedSymmetricKey = substr($payload, 0, $encryptedKeySize);
        $iv = substr($payload, $encryptedKeySize, 16);
        $ciphertext = substr($payload, $encryptedKeySize + 16);

        if ($encryptedSymmetricKey === '' || strlen($iv) !== 16 || $ciphertext === '') {
            throw new RuntimeException('Nie udało się rozpoznać zaszyfrowanego formatu odpowiedzi KSeF.');
        }

        $symmetricKey = $this->decryptSymmetricKey($encryptedSymmetricKey, $privateKeyPath, config('ksef.key_passphrase'));
        $xml = openssl_decrypt($ciphertext, 'AES-256-CBC', $symmetricKey, OPENSSL_RAW_DATA, $iv);

        if ($xml === false) {
            throw new RuntimeException('Nie udało się odszyfrować zawartości faktury z KSeF.');
        }

        $xml = $this->removePkcs7Padding($xml);

        if (!$this->looksLikeXml($xml)) {
            throw new RuntimeException('Po odszyfrowaniu nie otrzymano poprawnego dokumentu XML.');
        }

        return $xml;
    }

    private function getPrivateKeyDetails(string $privateKeyPath, ?string $passphrase): array
    {
        $key = openssl_pkey_get_private('file://' . $privateKeyPath, $passphrase ?: '');

        if ($key === false) {
            throw new RuntimeException('Nie udało się odczytać klucza prywatnego KSeF.');
        }

        $details = openssl_pkey_get_details($key) ?: [];
        openssl_free_key($key);

        return $details;
    }

    private function decryptSymmetricKey(string $encryptedKey, string $privateKeyPath, ?string $passphrase): string
    {
        $tempDir = sys_get_temp_dir();
        $encryptedFile = $tempDir . '/ksef_enc_key_' . uniqid('', true) . '.bin';
        $decryptedFile = $tempDir . '/ksef_dec_key_' . uniqid('', true) . '.bin';

        file_put_contents($encryptedFile, $encryptedKey);

        try {
            $command = sprintf(
                'openssl pkeyutl -decrypt -inkey %s %s -pkeyopt rsa_padding_mode:oaep -pkeyopt rsa_oaep_md:sha256 -pkeyopt rsa_mgf1_md:sha256 -in %s -out %s 2>&1',
                escapeshellarg($privateKeyPath),
                $passphrase ? '-passin pass:' . escapeshellarg($passphrase) : '',
                escapeshellarg($encryptedFile),
                escapeshellarg($decryptedFile)
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0 || !is_file($decryptedFile)) {
                throw new RuntimeException('Nie udało się odszyfrować klucza symetrycznego KSeF: ' . implode("\n", $output));
            }

            $decrypted = file_get_contents($decryptedFile);

            if ($decrypted === false || $decrypted === '') {
                throw new RuntimeException('Odszyfrowany klucz symetryczny KSeF jest pusty.');
            }

            return $decrypted;
        } finally {
            @unlink($encryptedFile);
            @unlink($decryptedFile);
        }
    }

    private function removePkcs7Padding(string $data): string
    {
        $padLength = ord(substr($data, -1));

        if ($padLength > 0 && $padLength <= 16) {
            return substr($data, 0, -$padLength);
        }

        return $data;
    }

    private function extractInvoiceDataFromXml(string $xmlContent, string $ksefNumber): array
    {
        $document = new \DOMDocument();

        if (!@$document->loadXML($xmlContent)) {
            throw new RuntimeException('Nie udało się sparsować XML pobranego z KSeF.');
        }

        $xpath = new \DOMXPath($document);

        $number = $this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_2']") ?: $ksefNumber;
        $issueDate = Carbon::parse($this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_1']"));
        $saleDate = $this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_6']")
            ? Carbon::parse($this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_6']"))
            : $issueDate->copy();
        $dueDate = $this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_22']")
            ? Carbon::parse($this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_22']"))
            : $issueDate->copy()->addDays(14);

        $currencyCode = $this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='KodWaluty']") ?: 'PLN';
        $netTotal = $this->sumValues($xpath, "//*[local-name()='Fa']/*[starts-with(local-name(),'P_13_')]");
        $vatTotal = $this->sumValues($xpath, "//*[local-name()='Fa']/*[starts-with(local-name(),'P_14_')]");
        $grossTotal = $this->floatValue($this->firstValue($xpath, "//*[local-name()='Fa']/*[local-name()='P_15']"));

        $contractor = [
            'name' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='Nazwa']")
                ?: trim(
                    ($this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='ImiePierwsze']") ?? '') . ' ' .
                    ($this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='Nazwisko']") ?? '')
                ),
            'nip' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='NIP']"),
            'address_street' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='AdresPol']/*[local-name()='Ulica']"),
            'address_building' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='AdresPol']/*[local-name()='NrDomu']"),
            'address_apartment' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='AdresPol']/*[local-name()='NrLokalu']"),
            'postal_code' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='AdresPol']/*[local-name()='KodPocztowy']"),
            'city' => $this->firstValue($xpath, "//*[local-name()='Podmiot1']//*[local-name()='AdresPol']/*[local-name()='Miejscowosc']"),
        ];

        if (empty($contractor['name'])) {
            throw new RuntimeException('Pobrana faktura z KSeF nie zawiera danych sprzedawcy.');
        }

        $items = [];
        foreach ($xpath->query("//*[local-name()='FaWiersz']") as $itemNode) {
            $itemName = $this->firstValue($xpath, ".//*[local-name()='P_7']", $itemNode) ?: 'Pozycja z KSeF';
            $quantity = $this->floatValue($this->firstValue($xpath, ".//*[local-name()='P_8B']", $itemNode), 1);
            $unit = $this->firstValue($xpath, ".//*[local-name()='P_8A']", $itemNode) ?: 'szt.';
            $netPrice = $this->floatValue($this->firstValue($xpath, ".//*[local-name()='P_9A']", $itemNode));
            $netValue = $this->floatValue($this->firstValue($xpath, ".//*[local-name()='P_11']", $itemNode));
            $vatRate = $this->normalizeVatRate($this->firstValue($xpath, ".//*[local-name()='P_12']", $itemNode));
            $vatAmount = round($netValue * $vatRate, 2);

            $items[] = [
                'name' => $itemName,
                'quantity' => $quantity,
                'unit' => $unit,
                'net_price' => $netPrice,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'gross_amount' => round($netValue + $vatAmount, 2),
            ];
        }

        if ($items === []) {
            $items[] = [
                'name' => 'Import z KSeF',
                'quantity' => 1,
                'unit' => 'usł.',
                'net_price' => $netTotal,
                'vat_rate' => $netTotal > 0 ? round($vatTotal / $netTotal, 4) : 0,
                'vat_amount' => $vatTotal,
                'gross_amount' => $grossTotal,
            ];
        }

        return [
            'number' => $number,
            'issue_date' => $issueDate->toDateString(),
            'sale_date' => $saleDate->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'payment_method' => 'Przelew',
            'currency_code' => $currencyCode,
            'net_total' => $netTotal,
            'vat_total' => $vatTotal,
            'gross_total' => $grossTotal,
            'contractor' => $contractor,
            'items' => $items,
        ];
    }

    private function firstValue(\DOMXPath $xpath, string $expression, ?\DOMNode $contextNode = null): ?string
    {
        $node = $xpath->query($expression, $contextNode)->item(0);

        return $node ? trim($node->textContent) : null;
    }

    private function sumValues(\DOMXPath $xpath, string $expression): float
    {
        $sum = 0.0;

        foreach ($xpath->query($expression) as $node) {
            $sum += $this->floatValue($node->textContent);
        }

        return round($sum, 2);
    }

    private function floatValue(?string $value, float $default = 0): float
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }

        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (float) $normalized : $default;
    }

    private function normalizeVatRate(?string $value): float
    {
        $normalized = mb_strtolower(trim((string) $value));

        if ($normalized === '' || in_array($normalized, ['zw', 'oo', 'np'], true)) {
            return 0.0;
        }

        $numeric = $this->floatValue($normalized);

        return $numeric > 1 ? round($numeric / 100, 4) : round($numeric, 4);
    }

    private function resolveUserId(?int $userId): int
    {
        if ($userId) {
            return $userId;
        }

        if (auth()->id()) {
            return auth()->id();
        }

        $firstUserId = User::query()->value('id');
        if ($firstUserId) {
            return $firstUserId;
        }

        throw new RuntimeException('Brak użytkownika, do którego można przypisać importowaną fakturę z KSeF.');
    }

    private function booleanToKsefFlag(bool $value): string
    {
        return $value ? '1' : '2';
    }

    private function contractorLegalName(Contractor $contractor): string
    {
        return $contractor->ksef_legal_name ?: $contractor->name;
    }

    private function appendContractorMainAddress(SimpleXMLElement $node, Contractor $contractor): void
    {
        $address = $node->addChild('Adres');
        $address->addChild('KodKraju', 'PL');
        $address->addChild('AdresL1', $this->formatAddressLine(
            $contractor->ksef_legal_street ?: $contractor->address_street,
            $contractor->ksef_legal_building ?: $contractor->address_building,
            $contractor->ksef_legal_apartment ?: $contractor->address_apartment
        ));

        $line2 = trim(($contractor->ksef_legal_postal_code ?: $contractor->postal_code).' '.($contractor->ksef_legal_city ?: $contractor->city));
        if ($line2 !== '') {
            $address->addChild('AdresL2', $line2);
        }
    }

    private function appendContractorCorrespondenceData(SimpleXMLElement $node, Contractor $contractor): void
    {
        $correspondenceLine = trim(implode(' ', array_filter([
            $contractor->ksef_correspondence_name,
            $this->formatAddressLine(
                $contractor->ksef_correspondence_street,
                $contractor->ksef_correspondence_building,
                $contractor->ksef_correspondence_apartment
            ),
            trim(($contractor->ksef_correspondence_postal_code ?? '').' '.($contractor->ksef_correspondence_city ?? '')),
        ])));

        if ($correspondenceLine !== '') {
            $address = $node->addChild('AdresKoresp');
            $address->addChild('KodKraju', 'PL');
            $address->addChild('AdresL1', $correspondenceLine);
        }

        if (!empty($contractor->ksef_customer_number)) {
            $node->addChild('NrKlienta', $contractor->ksef_customer_number);
        }
    }

    private function formatAddressLine(?string $street, ?string $building, ?string $apartment): string
    {
        $line = trim(implode(' ', array_filter([$street, $building])));

        if (!empty($apartment)) {
            $line = trim($line.' / '.$apartment);
        }

        return $line;
    }

    private function appendPaymentData(SimpleXMLElement $fa, Invoice $invoice): void
    {
        $paymentMethod = $this->resolvePaymentMethodCode($invoice->payment_method);
        $dueDate = $invoice->due_date?->format('Y-m-d');
        $bankAccount = $this->normalizeBankAccount(config('company.bank_account'));
        $factorBankAccount = $this->normalizeBankAccount(config('company.factor_bank_account'));

        if ($paymentMethod === null && $dueDate === null && $bankAccount === null && $factorBankAccount === null) {
            return;
        }

        $platnosc = $fa->addChild('Platnosc');

        if ($dueDate !== null) {
            $terminPlatnosci = $platnosc->addChild('TerminPlatnosci');
            $terminPlatnosci->addChild('Termin', $dueDate);
        }

        if ($paymentMethod !== null) {
            $platnosc->addChild('FormaPlatnosci', (string) $paymentMethod);
        }

        if ($bankAccount !== null) {
            $rachunekBankowy = $platnosc->addChild('RachunekBankowy');
            $rachunekBankowy->addChild('NrRB', $bankAccount);

            if (filled(config('company.bank_name'))) {
                $rachunekBankowy->addChild('NazwaBanku', config('company.bank_name'));
            }
        }

        if ($factorBankAccount !== null) {
            $rachunekFaktora = $platnosc->addChild('RachunekBankowyFaktora');
            $rachunekFaktora->addChild('NrRB', $factorBankAccount);

            if (filled(config('company.factor_bank_name'))) {
                $rachunekFaktora->addChild('NazwaBanku', config('company.factor_bank_name'));
            }
        }
    }

    private function resolvePaymentMethodCode(?string $paymentMethod): ?int
    {
        $normalized = mb_strtolower(trim((string) $paymentMethod));

        return match ($normalized) {
            'gotowka', 'gotówka', 'cash' => 1,
            'karta', 'card' => 2,
            'bon' => 3,
            'czek', 'check' => 4,
            'kredyt', 'credit' => 5,
            'przelew', 'transfer' => 6,
            'mobilna', 'mobile', 'blik' => 7,
            default => null,
        };
    }

    private function normalizeBankAccount(?string $bankAccount): ?string
    {
        $normalized = strtoupper((string) preg_replace('/\s+/', '', (string) $bankAccount));

        return $normalized !== '' ? $normalized : null;
    }
}
