<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\KsefClient;
use App\Services\KsefService;
use App\Support\TableFilters;
use App\Support\VatSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'number', ['number', 'ksef_number', 'issue_date', 'contractor', 'gross_total', 'status']);
        $dir = TableFilters::direction($request, 'desc');
        $perPage = TableFilters::perPage($request);

        $sortMap = [
            'number' => 'invoices.number',
            'ksef_number' => 'invoices.ksef_number',
            'issue_date' => 'invoices.issue_date',
            'contractor' => 'contractors.name',
            'gross_total' => 'invoices.gross_total',
            'status' => 'invoices.ksef_status',
        ];

        $invoices = Invoice::purchase()
            ->leftJoin('contractors', 'contractors.id', '=', 'invoices.contractor_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->select('invoices.*')
            ->with(['contractor', 'currency'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoices.number', 'like', "%{$search}%")
                        ->orWhere('invoices.ksef_number', 'like', "%{$search}%")
                        ->orWhere('contractors.name', 'like', "%{$search}%")
                        ->orWhere('currencies.code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortMap[$sort], $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('purchase_invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->type !== 'purchase') {
            abort(404);
        }

        $isVatExempt = VatSettings::isExempt();

        return view('purchase_invoices.show', compact('invoice', 'isVatExempt'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->type !== 'purchase') {
            abort(404);
        }

        $validated = $request->validate([
            'is_booked' => 'nullable|boolean',
            'accounting_note' => 'nullable|string'
        ]);

        $invoice->update([
            'is_booked' => $request->boolean('is_booked'),
            'accounting_note' => $request->input('accounting_note'),
        ]);

        return redirect()->route('purchase_invoices.show', $invoice)->with('success', 'Dane księgowe zostały zaktualizowane.');
    }

    public function fetch(Request $request, KsefClient $client, KsefService $service)
    {
        try {
            $toDate = Carbon::now();
            $fromDate = Carbon::now()->subDays(30);
            $response = $client->getInvoices($fromDate, $toDate, 'Subject2');

            $invoiceList = $response['invoices'] ?? [];
            $importedCount = 0;
            $skippedCount = 0;

            foreach ($invoiceList as $header) {
                $refNumber = $header['ksefNumber'] ?? $header['ksefReferenceNumber'] ?? null;

                if (!$refNumber) {
                    continue;
                }

                if (Invoice::where('ksef_number', $refNumber)->exists()) {
                    $skippedCount++;
                    continue;
                }

                $payload = $client->getInvoiceXml($refNumber);
                $service->importInvoiceFromKsef($payload, $refNumber, Auth::id());
                $importedCount++;
            }

            if ($importedCount === 0 && $skippedCount > 0) {
                return redirect()
                    ->route('purchase_invoices.index')
                    ->with('info', 'Nie znaleziono nowych faktur do importu. Wszystkie dokumenty z ostatnich 30 dni są już zapisane.');
            }

            if ($importedCount === 0) {
                return redirect()
                    ->route('purchase_invoices.index')
                    ->with('info', 'Nie znaleziono nowych faktur w KSeF z ostatnich 30 dni.');
            }

            $message = "Pobrano {$importedCount} nowych faktur z KSeF.";
            if ($skippedCount > 0) {
                $message .= " Pominięto {$skippedCount} dokumentów już zapisanych w systemie.";
            }

            return redirect()->route('purchase_invoices.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('purchase_invoices.index')->with('error', 'Błąd podczas pobierania z KSeF: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        $isVatExempt = VatSettings::isExempt();
        $vatExemptionReason = VatSettings::legalBasis();
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'isVatExempt', 'vatExemptionReason'));

        return $pdf->download('Faktura_Zakupowa_' . str_replace('/', '_', $invoice->number) . '.pdf');
    }

    public function downloadXml(Invoice $invoice, KsefService $ksefService)
    {
        $xml = $ksefService->getStoredXmlContents($invoice) ?? $ksefService->generateXml($invoice);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="faktura-zakupowa-' . str_replace('/', '_', $invoice->number) . '.xml"',
        ]);
    }
}
