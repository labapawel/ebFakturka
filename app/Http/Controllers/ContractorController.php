<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Services\GusService;
use App\Services\VatRegistryService;
use App\Support\TableFilters;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index(Request $request)
    {
        $search = TableFilters::search($request);
        $sort = TableFilters::sort($request, 'name', ['name', 'nip', 'city']);
        $dir = TableFilters::direction($request);
        $perPage = TableFilters::perPage($request);

        $contractors = Contractor::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('contractors.index', compact('contractors'));
    }

    public function create()
    {
        $suggestedCustomerNumber = $this->nextCustomerNumber();

        return view('contractors.create', compact('suggestedCustomerNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:20',
            'address_street' => 'nullable|string|max:255',
            'address_building' => 'nullable|string|max:20',
            'address_apartment' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'ksef_legal_name' => 'nullable|string|max:255',
            'ksef_legal_street' => 'nullable|string|max:255',
            'ksef_legal_building' => 'nullable|string|max:20',
            'ksef_legal_apartment' => 'nullable|string|max:20',
            'ksef_legal_postal_code' => 'nullable|string|max:20',
            'ksef_legal_city' => 'nullable|string|max:255',
            'ksef_correspondence_name' => 'nullable|string|max:255',
            'ksef_correspondence_street' => 'nullable|string|max:255',
            'ksef_correspondence_building' => 'nullable|string|max:20',
            'ksef_correspondence_apartment' => 'nullable|string|max:20',
            'ksef_correspondence_postal_code' => 'nullable|string|max:20',
            'ksef_correspondence_city' => 'nullable|string|max:255',
            'ksef_customer_number' => 'nullable|string|max:100',
            'is_jst' => 'nullable|boolean',
            'is_vat_group_member' => 'nullable|boolean',
        ]);

        $validated['is_jst'] = $request->boolean('is_jst');
        $validated['is_vat_group_member'] = $request->boolean('is_vat_group_member');
        $validated['ksef_customer_number'] = $validated['ksef_customer_number'] ?: $this->nextCustomerNumber();

        Contractor::create($validated);

        return redirect()->route('contractors.index')->with('success', 'Kontrahent zostal dodany.');
    }

    public function show(Contractor $contractor)
    {
        //
    }

    public function edit(Contractor $contractor)
    {
        return view('contractors.edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:20',
            'address_street' => 'nullable|string|max:255',
            'address_building' => 'nullable|string|max:20',
            'address_apartment' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'ksef_legal_name' => 'nullable|string|max:255',
            'ksef_legal_street' => 'nullable|string|max:255',
            'ksef_legal_building' => 'nullable|string|max:20',
            'ksef_legal_apartment' => 'nullable|string|max:20',
            'ksef_legal_postal_code' => 'nullable|string|max:20',
            'ksef_legal_city' => 'nullable|string|max:255',
            'ksef_correspondence_name' => 'nullable|string|max:255',
            'ksef_correspondence_street' => 'nullable|string|max:255',
            'ksef_correspondence_building' => 'nullable|string|max:20',
            'ksef_correspondence_apartment' => 'nullable|string|max:20',
            'ksef_correspondence_postal_code' => 'nullable|string|max:20',
            'ksef_correspondence_city' => 'nullable|string|max:255',
            'ksef_customer_number' => 'nullable|string|max:100',
            'is_jst' => 'nullable|boolean',
            'is_vat_group_member' => 'nullable|boolean',
        ]);

        $validated['is_jst'] = $request->boolean('is_jst');
        $validated['is_vat_group_member'] = $request->boolean('is_vat_group_member');

        $contractor->update($validated);

        return redirect()->route('contractors.index')->with('success', 'Kontrahent zostal zaktualizowany.');
    }

    public function destroy(Contractor $contractor)
    {
        $contractor->delete();

        return redirect()->route('contractors.index')->with('success', 'Kontrahent zostal usuniety.');
    }

    private function nextCustomerNumber(): string
    {
        $max = Contractor::query()
            ->whereNotNull('ksef_customer_number')
            ->pluck('ksef_customer_number')
            ->filter(fn ($value) => is_string($value) || is_numeric($value))
            ->map(fn ($value) => preg_match('/^\d+$/', trim((string) $value)) ? (int) trim((string) $value) : null)
            ->filter(fn ($value) => $value !== null)
            ->max();

        return (string) (($max ?? 0) + 1);
    }

    public function fetchGus(Request $request, GusService $gusService)
    {
        $validated = $request->validate([
            'nip' => 'required|string',
        ]);

        try {
            return response()->json($gusService->fetchByNip($validated['nip']));
        } catch (\RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'Nie znaleziono podmiotu') ? 404 : 422;

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    public function checkVatRegistry(Request $request, VatRegistryService $vatRegistryService)
    {
        $validated = $request->validate([
            'nip' => 'required|string',
        ]);

        try {
            return response()->json($vatRegistryService->searchByNip($validated['nip']));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
