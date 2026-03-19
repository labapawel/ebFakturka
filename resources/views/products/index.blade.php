<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Produkty') }}
            </h2>
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Dodaj nowy') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-table-filters placeholder="Szukaj po nazwie, PKWiU, jednostce lub stawce VAT" />
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="name" :label="__('content.products.name')" :current-sort="request('sort', 'name')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="net_price" label="Cena Netto" :current-sort="request('sort', 'name')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="vat_rate" label="VAT" :current-sort="request('sort', 'name')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="unit" label="JM" :current-sort="request('sort', 'name')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="pkwiu" label="PKWiU" :current-sort="request('sort', 'name')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->net_price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->vatRate->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->pkwiu }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">{{ __('content.common.edit') }}</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('content.common.confirm_delete') }}')">{{ __('content.common.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
