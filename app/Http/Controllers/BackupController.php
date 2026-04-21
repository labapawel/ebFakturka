<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    protected $tablesToBackup = [
        'users',
        'contractors',
        'currencies',
        'vat_rates',
        'products',
        'invoices',
        'invoice_items',
        'recurring_invoices',
        'recurring_invoice_items',
        'invoice_counters'
    ];

    public function index()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('backups.index');
    }

    public function export()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $data = [];
        foreach ($this->tablesToBackup as $table) {
            $data[$table] = DB::table($table)->get()->toArray();
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $tmpFile = tempnam(sys_get_temp_dir(), 'ebfakturka_backup_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('database.json', $json);

        foreach (['ksef/sent', 'ksef/invoices'] as $dir) {
            foreach (Storage::disk('local')->files($dir) as $filePath) {
                $zip->addFromString($filePath, Storage::disk('local')->get($filePath));
            }
        }

        $zip->close();

        $filename = 'ebFakturka-backup-' . date('Y-m-d_H-i-s') . '.zip';

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:zip,json,txt',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $xmlFiles = [];

        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                return redirect()->back()->with('error', 'Nie można otworzyć pliku ZIP kopii zapasowej.');
            }

            $jsonContent = $zip->getFromName('database.json');
            if ($jsonContent === false) {
                $zip->close();
                return redirect()->back()->with('error', 'Nieprawidłowy plik ZIP — brak pliku database.json.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with($name, '.xml')) {
                    $xmlFiles[$name] = $zip->getFromIndex($i);
                }
            }

            $zip->close();
            $data = json_decode($jsonContent, true);
        } else {
            $data = json_decode(File::get($file->getRealPath()), true);
        }

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Nieprawidłowy plik kopii zapasowej.');
        }

        try {
            DB::beginTransaction();

            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            foreach ($this->tablesToBackup as $table) {
                if (isset($data[$table])) {
                    DB::table($table)->truncate();
                    $chunks = array_chunk($data[$table], 500);
                    foreach ($chunks as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            }

            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            foreach ($xmlFiles as $path => $content) {
                Storage::disk('local')->put($path, $content);
            }

            $xmlCount = count($xmlFiles);
            $message = 'Kopia zapasowa została pomyślnie przywrócona.';
            if ($xmlCount > 0) {
                $message .= " Przywrócono {$xmlCount} " . ($xmlCount === 1 ? 'plik' : 'pliki/plików') . ' XML KSeF.';
            }

            return redirect()->route('backups.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return redirect()->back()->with('error', 'Wystąpił błąd podczas przywracania danych: ' . $e->getMessage());
        }
    }
}
