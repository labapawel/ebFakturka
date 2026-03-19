<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KsefClient;
use Carbon\Carbon;

class TestKsefConnection extends Command
{
    protected $signature = 'ksef:test';
    protected $description = 'Test connection to KSeF API';

    public function handle(KsefClient $client)
    {
        $this->info('Testing KSeF Connection...');
        $this->info('URL: ' . config('ksef.url'));
        $this->info('NIP: ' . config('ksef.nip'));
        
        try {
            $this->info('Attempting Authentication...');
            $token = $client->getSessionToken();
            $this->info('Authentication Successful! Session Token acquired.');
            $this->info('Token: ' . substr($token, 0, 10) . '...');
            
            $this->info('Fetching Invoices (Last 30 days)...');
            $toDate = Carbon::now();
            $fromDate = Carbon::now()->subDays(30);
            
            $response = $client->getInvoices($fromDate, $toDate);
            
            $count = count($response['invoiceHeaderList'] ?? []);
            $this->info("Found {$count} invoices.");
            
            if ($count > 0) {
                $first = $response['invoiceHeaderList'][0];
                $this->info('First Invoice Ref: ' . $first['ksefReferenceNumber']);
                
                $this->info('Attempting to download XML for first invoice...');
                $xml = $client->getInvoiceXml($first['ksefReferenceNumber']);
                $this->info('XML Downloaded successfully (' . strlen($xml) . ' bytes).');
            } else {
                $this->warn('No invoices found in the last 30 days. Try creating one in KSeF Test App or extend range.');
            }
            
        } catch (\Exception $e) {
            $this->error('KSeF Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
