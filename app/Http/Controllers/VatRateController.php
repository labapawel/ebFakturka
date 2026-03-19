<?php

namespace App\Http\Controllers;

use App\Models\VatRate;
use App\Support\TableFilters;
use Illuminate\Http\Request;

class VatRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'name', ['name', 'rate', 'is_active']);
        $dir = TableFilters::direction($request);
        $perPage = TableFilters::perPage($request);

        $vatRates = VatRate::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('rate', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('vat_rates.index', compact('vatRates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vat_rates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|between:0,1',
            'is_active' => 'boolean',
        ]);

        VatRate::create($validated);

        return redirect()->route('vat_rates.index')->with('success', 'Stawka VAT została dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VatRate $vatRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VatRate $vatRate)
    {
        return view('vat_rates.edit', compact('vatRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VatRate $vatRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|between:0,1',
            'is_active' => 'boolean',
        ]);

        $vatRate->update($validated);

        return redirect()->route('vat_rates.index')->with('success', 'Stawka VAT została zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VatRate $vatRate)
    {
        $vatRate->delete();
        return redirect()->route('vat_rates.index')->with('success', 'Stawka VAT została usunięta.');
    }
}
