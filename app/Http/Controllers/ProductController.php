<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VatRate;
use App\Support\TableFilters;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'name', ['name', 'net_price', 'vat_rate', 'unit', 'pkwiu']);
        $dir = TableFilters::direction($request);
        $perPage = TableFilters::perPage($request);

        $sortMap = [
            'name' => 'products.name',
            'net_price' => 'products.net_price',
            'vat_rate' => 'vat_rates.name',
            'unit' => 'products.unit',
            'pkwiu' => 'products.pkwiu',
        ];

        $products = Product::query()
            ->leftJoin('vat_rates', 'vat_rates.id', '=', 'products.vat_rate_id')
            ->select('products.*')
            ->with('vatRate')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.unit', 'like', "%{$search}%")
                        ->orWhere('products.pkwiu', 'like', "%{$search}%")
                        ->orWhere('vat_rates.name', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortMap[$sort], $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vatRates = VatRate::active()->get();
        return view('products.create', compact('vatRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:10',
            'net_price' => 'required|numeric|min:0',
            'vat_rate_id' => 'required|exists:vat_rates,id',
            'pkwiu' => 'nullable|string|max:20',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produkt został dodany.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $vatRates = VatRate::active()->get();
        return view('products.edit', compact('product', 'vatRates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:10',
            'net_price' => 'required|numeric|min:0',
            'vat_rate_id' => 'required|exists:vat_rates,id',
            'pkwiu' => 'nullable|string|max:20',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produkt został zaktualizowany.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produkt został usunięty.');
    }
}
