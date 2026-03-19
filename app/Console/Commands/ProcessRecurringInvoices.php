<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Przetwarza faktury cykliczne i generuje nowe faktury, jeśli nadszedł czas wystawienia.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $this->info("Rozpoczynam przetwarzanie faktur cyklicznych na dzień: {$today->toDateString()}");

        $recurringInvoices = RecurringInvoice::where('status', 'active')
            ->whereDate('next_issue_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
            })
            ->get();

        if ($recurringInvoices->isEmpty()) {
            $this->info("Brak faktur cyklicznych do przetworzenia.");
            return;
        }

        $count = 0;

        foreach ($recurringInvoices as $recurring) {
            DB::transaction(function () use ($recurring, $today, &$count) {
                // Generuj numer faktury
                $number = $this->generateNextInvoiceNumber($today);

                // Twórz Fakturę
                $invoice = Invoice::create([
                    'user_id' => $recurring->user_id,
                    'contractor_id' => $recurring->contractor_id,
                    'number' => $number, // FV/YYYY/MM/Number
                    'issue_date' => $today,
                    'sale_date' => $today,
                    'due_date' => $today->copy()->addDays(14),
                    'payment_method' => $recurring->payment_method,
                    'currency_id' => $recurring->currency_id,
                    'net_total' => $recurring->net_total,
                    'vat_total' => $recurring->vat_total,
                    'gross_total' => $recurring->gross_total,
                    'status' => 'issued', // Domyję status
                    'type' => 'sales',
                ]);

                // Twórz Pozycje
                foreach ($recurring->items as $item) {
                    $invoice->items()->create([
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'net_price' => $item->net_price,
                        'vat_rate' => $item->vat_rate,
                        'vat_amount' => $item->vat_amount,
                        'gross_amount' => $item->gross_amount,
                    ]);
                }

                // Aktualizuj datę następnego wystawienia
                $nextDate = Carbon::parse($recurring->next_issue_date);
                $interval = $recurring->frequency_interval;

                switch ($recurring->frequency) {
                    case 'monthly':
                        $nextDate->addMonths($interval);
                        break;
                    case 'quarterly':
                        $nextDate->addMonths(3 * $interval);
                        break;
                    case 'yearly':
                        $nextDate->addYears($interval);
                        break;
                    default: // custom treated as months for simplicity
                         $nextDate->addMonths($interval);
                        break;
                }

                $recurring->update(['next_issue_date' => $nextDate]);
                $count++;
                
                $this->info("Wygenerowano fakturę: {$invoice->number} dla cyklu ID: {$recurring->id}");
            });
        }

        $this->info("Zakończono. Wygenerowano {$count} nowych faktur.");
    }

    private function generateNextInvoiceNumber($date)
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        
        // Znajdź ostatnią fakturę z tego miesiąca
        $lastInvoice = Invoice::whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->where('type', 'sales')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Zakładamy format FV/YYYY/MM/Numer
            $parts = explode('/', $lastInvoice->number);
            $lastNumber = intval(end($parts));
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }

        return sprintf('FV/%s/%s/%04d', $year, $month, $number);
    }
}
