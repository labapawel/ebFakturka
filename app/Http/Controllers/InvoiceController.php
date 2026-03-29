<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\VatRate;
use App\Services\InvoiceNumberGenerator;
use App\Services\KsefService;
use App\Support\TableFilters;
use App\Support\VatSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'number', ['number', 'issue_date', 'contractor', 'gross_total', 'currency', 'status']);
        $dir = TableFilters::direction($request, 'desc');
        $perPage = TableFilters::perPage($request);

        $sortMap = [
            'number' => 'invoices.number',
            'issue_date' => 'invoices.issue_date',
            'contractor' => 'contractors.name',
            'gross_total' => 'invoices.gross_total',
            'currency' => 'currencies.code',
            'status' => 'invoices.ksef_status',
        ];

        $invoices = Invoice::query()
            ->sales()
            ->leftJoin('contractors', 'contractors.id', '=', 'invoices.contractor_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->select('invoices.*')
            ->with(['contractor', 'currency'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoices.number', 'like', "%{$search}%")
                        ->orWhere('contractors.name', 'like', "%{$search}%")
                        ->orWhere('currencies.code', 'like', "%{$search}%")
                        ->orWhere('invoices.ksef_number', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortMap[$sort], $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contractors = Contractor::all();
        $currencies = Currency::all();

        $isVatExempt = VatSettings::isExempt();

        if ($isVatExempt) {
            $vatRates = VatRate::where('name', 'zw')->get();
        } else {
            $vatRates = VatRate::active()->get();
        }

        $products = Product::with('vatRate')->get();

        // Sugerowany numer (nie gwarantuje braku kolizji przy high concurrency, ale dla MVP ok)
        $suggestedNumber = app(InvoiceNumberGenerator::class)->peekNextNumber();

        return view('invoices.create', compact('contractors', 'currencies', 'vatRates', 'products', 'suggestedNumber', 'isVatExempt'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('invoices', 'number')->where(function ($query) use ($request) {
                    return $query->where('type', 'sales')
                        ->where('contractor_id', $request->contractor_id);
                }),
            ],
            'issue_date' => 'required|date',
            'sale_date' => 'required|date',
            'due_date' => 'required|date',
            'payment_method' => 'required|string',
            'description' => 'nullable|string',
            'contractor_id' => 'required|exists:contractors,id',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.net_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric',
        ]);

        $numberGenerator = app(InvoiceNumberGenerator::class);
        $suggestedNumber = $numberGenerator->peekNextNumber();
        $requestedNumber = $validated['number'];
        $finalNumber = $requestedNumber;

        if ($requestedNumber === $suggestedNumber) {
            $finalNumber = $numberGenerator->reserveNextNumber();
        } else {
            $numberGenerator->syncCounterFromNumber($requestedNumber);
        }

        DB::transaction(function () use ($validated, $finalNumber) {
            $netTotal = 0;
            $vatTotal = 0;
            $grossTotal = 0;

            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $quantity = $item['quantity'];
                $netPrice = $item['net_price'];
                $vatRate = $item['vat_rate'];

                $netValue = $quantity * $netPrice;
                $vatValue = $netValue * $vatRate;
                $grossValue = $netValue + $vatValue;

                $netTotal += $netValue;
                $vatTotal += $vatValue;
                $grossTotal += $grossValue;

                $itemsData[] = [
                    'name' => $item['name'],
                    'quantity' => $quantity,
                    'unit' => $item['unit'],
                    'net_price' => $netPrice,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatValue,
                    'gross_amount' => $grossValue,
                ];
            }

            $contractor = Contractor::findOrFail($validated['contractor_id']);

            $invoice = Invoice::create([
                'type' => 'sales',
                'number' => $finalNumber,
                'issue_date' => $validated['issue_date'],
                'sale_date' => $validated['sale_date'],
                'due_date' => $validated['due_date'],
                'payment_method' => $validated['payment_method'],
                'description' => $validated['description'] ?? null,
                'contractor_id' => $validated['contractor_id'],
                'user_id' => Auth::id(),
                'currency_id' => $validated['currency_id'],
                'net_total' => $netTotal,
                'vat_total' => $vatTotal,
                'gross_total' => $grossTotal,
                'status' => 'issued',
                'seller_name' => config('company.name'),
                'seller_nip' => config('company.nip'),
                'seller_street' => config('company.street'),
                'seller_building' => config('company.building_number'),
                'seller_postal_code' => config('company.postal_code'),
                'seller_city' => config('company.city'),
                'bank_account' => config('company.bank_account'),
                'bank_name' => config('company.bank_name'),
                'buyer_name' => $contractor->name,
                'buyer_nip' => $contractor->nip,
                'buyer_street' => $contractor->address_street,
                'buyer_building' => $contractor->address_building,
                'buyer_apartment' => $contractor->address_apartment,
                'buyer_postal_code' => $contractor->postal_code,
                'buyer_city' => $contractor->city,
            ]);

            $invoice->items()->createMany($itemsData);
        });

        return redirect()->route('invoices.index')->with('success', 'Faktura została wystawiona.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $isVatExempt = VatSettings::isExempt();
        $vatExemptionReason = VatSettings::legalBasis();

        return view('invoices.show', compact('invoice', 'isVatExempt', 'vatExemptionReason'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        return redirect()->route('invoices.index')->with('error', 'Edycja faktur w przygotowaniu.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->ksef_status === 'sent') {
            return back()->with('error', 'Nie można usunąć faktury wysłanej do KSeF.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Faktura została usunięta.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $isVatExempt = VatSettings::isExempt();
        $vatExemptionReason = VatSettings::legalBasis();
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'isVatExempt', 'vatExemptionReason'));

        return $pdf->download('Faktura_' . str_replace('/', '_', $invoice->number) . '.pdf');
    }

    public function downloadXml(Invoice $invoice, KsefService $ksefService)
    {
        $xml = $ksefService->generateXml($invoice);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="faktura-' . str_replace('/', '_', $invoice->number) . '.xml"',
        ]);
    }

    public function sendToKsef(Invoice $invoice, KsefService $ksefService, \App\Services\KsefClient $ksefClient)
    {
        if ($invoice->ksef_status === 'sent') {
            return back()->with('error', 'Ta faktura została już wysłana do KSeF.');
        }

        try {
            $validationErrors = $ksefService->validateForSending($invoice->loadMissing(['contractor', 'currency', 'items']));
            if ($validationErrors !== []) {
                return back()->with('error', 'Faktura nie może zostać wysłana do KSeF: ' . implode(' ', $validationErrors));
            }

            $xml = $ksefService->generateXml($invoice);
            $result = $ksefClient->sendInvoice($xml);
            $statusResponse = $ksefClient->waitForSessionInvoiceStatus(
                $result['sessionReferenceNumber'],
                $result['invoiceReferenceNumber']
            );

            $statusCode = (int) ($statusResponse['status']['code'] ?? 0);
            $statusDescription = $statusResponse['status']['description'] ?? 'Nieznany status KSeF';
            $statusDetails = $statusResponse['status']['details'] ?? [];
            $detailsText = is_array($statusDetails) ? implode(' ', $statusDetails) : (string) $statusDetails;
            $ksefNumber = $statusResponse['ksefNumber'] ?? null;

            if ($statusCode === 200 && filled($ksefNumber)) {
                $invoice->update([
                    'ksef_status' => 'sent',
                    'ksef_number' => $ksefNumber,
                ]);

                return back()->with('success', "Faktura wysłana do KSeF. Numer KSeF: {$ksefNumber}");
            }

            if (in_array($statusCode, [100, 150], true)) {
                $invoice->update([
                    'ksef_status' => 'pending',
                    'ksef_number' => null,
                ]);

                return back()->with('success', "Faktura została przyjęta do przetwarzania w KSeF. Numer referencyjny dokumentu: {$result['invoiceReferenceNumber']}");
            }

            $invoice->update([
                'ksef_status' => 'error',
                'ksef_number' => null,
            ]);

            $message = "KSeF odrzucił fakturę ({$statusCode}): {$statusDescription}";
            if ($detailsText !== '') {
                $message .= ' ' . $detailsText;
            }

            return back()->with('error', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Błąd komunikacji z KSeF: ' . $e->getMessage());
        }
    }
}
