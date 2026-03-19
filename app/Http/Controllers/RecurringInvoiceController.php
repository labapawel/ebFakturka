<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Product;
use App\Models\RecurringInvoice;
use App\Models\VatRate;
use App\Support\TableFilters;
use App\Support\VatSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecurringInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'next_issue_date', ['contractor', 'frequency', 'next_issue_date', 'gross_total', 'status']);
        $dir = TableFilters::direction($request, 'desc');
        $perPage = TableFilters::perPage($request);

        $sortMap = [
            'contractor' => 'contractors.name',
            'frequency' => 'recurring_invoices.frequency',
            'next_issue_date' => 'recurring_invoices.next_issue_date',
            'gross_total' => 'recurring_invoices.gross_total',
            'status' => 'recurring_invoices.status',
        ];

        $recurringInvoices = RecurringInvoice::query()
            ->leftJoin('contractors', 'contractors.id', '=', 'recurring_invoices.contractor_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'recurring_invoices.currency_id')
            ->select('recurring_invoices.*')
            ->with(['contractor', 'currency'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('contractors.name', 'like', "%{$search}%")
                        ->orWhere('contractors.nip', 'like', "%{$search}%")
                        ->orWhere('recurring_invoices.frequency', 'like', "%{$search}%")
                        ->orWhere('recurring_invoices.status', 'like', "%{$search}%")
                        ->orWhere('currencies.code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortMap[$sort], $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('recurring_invoices.index', compact('recurringInvoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contractors = Contractor::all();
        $currencies = Currency::all();
        $products = Product::with('vatRate')->get();
        
        $isVatExempt = VatSettings::isExempt();
        
        if ($isVatExempt) {
            $vatRates = VatRate::where('name', 'zw')->get();
        } else {
            $vatRates = VatRate::active()->get();
        }

        return view('recurring_invoices.create', compact('contractors', 'currencies', 'products', 'vatRates', 'isVatExempt'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'currency_id' => 'required|exists:currencies,id',
            'frequency' => 'required|in:monthly,quarterly,yearly,custom',
            'frequency_interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'next_issue_date' => 'required|date|after_or_equal:start_date',
            'end_date' => 'nullable|date|after:start_date',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.net_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated) {
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

            $recurringInvoice = RecurringInvoice::create([
                'contractor_id' => $validated['contractor_id'],
                'user_id' => Auth::id(),
                'currency_id' => $validated['currency_id'],
                'frequency' => $validated['frequency'],
                'frequency_interval' => $validated['frequency_interval'],
                'start_date' => $validated['start_date'],
                'next_issue_date' => $validated['next_issue_date'],
                'end_date' => $validated['end_date'],
                'payment_method' => $validated['payment_method'],
                'net_total' => $netTotal,
                'vat_total' => $vatTotal,
                'gross_total' => $grossTotal,
                'status' => 'active',
            ]);

            $recurringInvoice->items()->createMany($itemsData);
        });

        return redirect()->route('recurring_invoices.index')->with('success', 'Utworzono cykliczną fakturę.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringInvoice $recurringInvoice)
    {
         $isVatExempt = VatSettings::isExempt();
         return view('recurring_invoices.show', compact('recurringInvoice', 'isVatExempt'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecurringInvoice $recurringInvoice)
    {
        $contractors = Contractor::all();
        $currencies = Currency::all();
        $products = Product::with('vatRate')->get();
        
        $isVatExempt = VatSettings::isExempt();
        
        if ($isVatExempt) {
            $vatRates = VatRate::where('name', 'zw')->get();
        } else {
            $vatRates = VatRate::active()->get();
        }

        return view('recurring_invoices.edit', compact('recurringInvoice', 'contractors', 'currencies', 'products', 'vatRates', 'isVatExempt'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
         $validated = $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'currency_id' => 'required|exists:currencies,id',
            'frequency' => 'required|in:monthly,quarterly,yearly,custom',
            'frequency_interval' => 'required|integer|min:1',
            'next_issue_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.net_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated, $recurringInvoice) {
            // Recalculate totals
            $netTotal = 0;
            $vatTotal = 0;
            $grossTotal = 0;
            
            // Delete old items and create new ones (simplest approach for update)
            $recurringInvoice->items()->delete();
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                // ... same logic as store ...
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

            $recurringInvoice->update([
                'contractor_id' => $validated['contractor_id'],
                'currency_id' => $validated['currency_id'],
                'frequency' => $validated['frequency'],
                'frequency_interval' => $validated['frequency_interval'],
                'next_issue_date' => $validated['next_issue_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'net_total' => $netTotal,
                'vat_total' => $vatTotal,
                'gross_total' => $grossTotal,
            ]);

            $recurringInvoice->items()->createMany($itemsData);
        });

        return redirect()->route('recurring_invoices.index')->with('success', 'Zaktualizowano cykliczną fakturę.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->delete();
        return redirect()->route('recurring_invoices.index')->with('success', 'Usunięto cykliczną fakturę.');
    }
}
