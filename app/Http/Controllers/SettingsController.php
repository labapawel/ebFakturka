<?php

namespace App\Http\Controllers;

use App\Support\VatSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Zmienne kluczowe wyświetlane na stronie głównej w ustawieniach.
     */
    protected $mainSettings = [
        'VAT_EXEMPT',
        'VAT_EXEMPT_REASON_TYPE',
        'VAT_EXEMPT_REASON_TEXT',
        "BIR_KEY",
        'INVOICE_NUMBER_FORMAT',
        'INVOICE_NUMBER_PADDING',
        'COMPANY_NAME',
        'COMPANY_NIP',
        'COMPANY_STREET',
        'COMPANY_BUILDING_NUMBER',
        'COMPANY_POSTAL_CODE',
        'COMPANY_CITY',
        'COMPANY_BANK_NAME',
        'COMPANY_BANK_ACCOUNT',
        'COMPANY_FACTOR_BANK_NAME',
        'COMPANY_FACTOR_BANK_ACCOUNT',
    ];

    /**
     * Zmienne KSeF.
     */
    protected $ksefSettings = [
        'KSEF_token',
        'KSEF_url',
        'KSEF_PASS'
    ];

    /**
     * Zmienne traktowane jako hasła (ukrywamy ich wartość).
     */
    protected $hiddenSettings = [
        'DB_PASSWORD',
        'APP_KEY',
        'KSEF_PASS',
        'KSEF_token',
        'BIR_KEY'
    ];

    public function index()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            // Bezpiecznik jak plik env usunięty
            abort(500, 'Unable to locate .env file.');
        }
        
        $envContent = File::get($envFile);
        $envs = $this->parseEnv($envContent);

        $mainEnvs = [];
        $ksefEnvs = [];
        $advancedEnvs = [];

        foreach ($envs as $key => $value) {
            $data = [
                'key' => $key,
                'value' => in_array($key, $this->hiddenSettings) ? '' : $value,
                'isHidden' => in_array($key, $this->hiddenSettings)
            ];

            if (in_array($key, $this->mainSettings)) {
                $mainEnvs[$key] = $data;
            } elseif (in_array($key, $this->ksefSettings)) {
                $ksefEnvs[$key] = $data;
            } else {
                $advancedEnvs[$key] = $data;
            }
        }
        
        // Ensure all main settings appear even if they aren't currently stored in .env
        foreach ($this->mainSettings as $key) {
            if (!isset($mainEnvs[$key])) {
                $mainEnvs[$key] = [
                    'key' => $key,
                    'value' => '',
                    'isHidden' => in_array($key, $this->hiddenSettings)
                ];
            }
        }

        foreach ($this->ksefSettings as $key) {
            if (!isset($ksefEnvs[$key])) {
                $ksefEnvs[$key] = [
                    'key' => $key,
                    'value' => '',
                    'isHidden' => in_array($key, $this->hiddenSettings)
                ];
            }
        }

        if (isset($mainEnvs['VAT_EXEMPT']) && $mainEnvs['VAT_EXEMPT']['value'] === '') {
            $mainEnvs['VAT_EXEMPT']['value'] = VatSettings::VAT_PAYER;
        }

        $vatOptions = VatSettings::options();
        $vatReasonTypeOptions = VatSettings::reasonTypeOptions();

        if (isset($mainEnvs['VAT_EXEMPT_REASON_TYPE']) && $mainEnvs['VAT_EXEMPT_REASON_TYPE']['value'] === '') {
            $mainEnvs['VAT_EXEMPT_REASON_TYPE']['value'] = VatSettings::REASON_STATUTE;
        }

        if (isset($mainEnvs['VAT_EXEMPT_REASON_TEXT']) && $mainEnvs['VAT_EXEMPT_REASON_TEXT']['value'] === '') {
            $mainEnvs['VAT_EXEMPT_REASON_TEXT']['value'] = 'Przepis ustawy albo aktu wydanego na podstawie ustawy, na podstawie ktorego podatnik stosuje zwolnienie od podatku';
        }

        return view('settings.index', compact('mainEnvs', 'ksefEnvs', 'advancedEnvs', 'vatOptions', 'vatReasonTypeOptions'));
    }

    public function update(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            return redirect()->back()->with('error', 'Unable to locate .env file.');
        }

        $envContent = File::get($envFile);
        $envsToUpdate = $request->input('env', []);

        // Handling file uploads for KSeF certificates
        if ($request->hasFile('ksef_cert')) {
            $request->validate([
                'ksef_cert' => 'required|file|mimetypes:application/x-x509-ca-cert,text/plain|mimes:crt,pem,txt'
            ]);
            $request->file('ksef_cert')->storeAs('ksef', 'cert.crt', 'local');
        }

        if ($request->hasFile('ksef_key')) {
            $request->validate([
                'ksef_key' => 'required|file|mimetypes:application/pkcs8,application/x-pem-file,text/plain|mimes:key,pem,txt'
            ]);
            $request->file('ksef_key')->storeAs('ksef', 'private.key', 'local');
        }

        foreach ($envsToUpdate as $key => $value) {
            // Jeśli to ukryte haslo i jest puste pole to ignorujemy zmiane (aby nie nadpisac pustym)
            if (in_array($key, $this->hiddenSettings) && empty($value)) {
                continue;
            }

            // Obsługa wartości ze spacjami i znakami specjalnymi: opakuj zmienną w cudzysłów
            if (preg_match('/\s/', $value) || strpos($value, '#') !== false || $value === '') {
                $value = '"' . str_replace('"', '\"', $value) . '"';
            }

            // Znajdz klucz=. Wtedy go nadpisz z całą linią
            if (preg_match('/^' . preg_quote($key) . '=.*/m', $envContent)) {
                $envContent = preg_replace('/^' . preg_quote($key) . '=.*/m', $key . '=' . $value, $envContent);
            } else {
                // Jeśli klucza nie ma w pliku to dodaj na koncu
                $envContent .= "\n" . $key . '=' . $value;
            }
        }

        File::put($envFile, $envContent);
        
        Artisan::call('config:clear');

        return redirect()->route('settings.index')->with('success', 'Ustawienia systemu zostały pomyślnie zaktualizowane.');
    }

    private function parseEnv($content)
    {
        $lines = explode("\n", $content);
        $envs = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || strpos($line, '#') === 0) {
                // Ignore empty lines and comments
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $value = trim($value, '"\''); // Usuwamy ewentualne otaczające cudzysłowy/apostrofy
                $envs[$key] = $value;
            }
        }

        return $envs;
    }
}
