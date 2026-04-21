<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

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
        $filename = 'ebFakturka-backup-' . date('Y-m-d_H-i-s') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function import(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimetypes:application/json,text/plain|mimes:json,txt',
        ]);

        $file = $request->file('backup_file');
        $content = File::get($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !is_array($data)) {
            return redirect()->back()->with('error', 'Nieprawidłowy plik kopii zapasowej. Wybierz poprawny plik JSON.');
        }

        try {
            DB::beginTransaction();

            // Disable foreign key checks for MySQL
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            foreach ($this->tablesToBackup as $table) {
                if (isset($data[$table])) {
                    // Truncate existing data
                    DB::table($table)->truncate();
                    
                    // Insert data in chunks to prevent memory issues
                    $chunks = array_chunk($data[$table], 500);
                    foreach ($chunks as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            }

            // Re-enable foreign key checks for MySQL
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            return redirect()->route('backups.index')->with('success', 'Kopia zapasowa została pomyślnie przywrócona.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Try to re-enable foreign keys if rollback happens
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return redirect()->back()->with('error', 'Wystąpił błąd podczas przywracania danych: ' . $e->getMessage());
        }
    }
}
