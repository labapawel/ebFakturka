<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Support\TableFilters;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'code', ['code', 'name', 'exchange_rate', 'is_default']);
        $dir = TableFilters::direction($request);
        $perPage = TableFilters::perPage($request);

        $currencies = Currency::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies',
            'name' => 'required|string|max:255',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            Currency::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        Currency::create($validated);

        return redirect()->route('currencies.index')->with('success', 'Waluta została dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $currency->update($validated);

        return redirect()->route('currencies.index')->with('success', 'Waluta została zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Nie można usunąć domyślnej waluty.');
        }

        $currency->delete();
        return redirect()->route('currencies.index')->with('success', 'Waluta została usunięta.');
    }
}
