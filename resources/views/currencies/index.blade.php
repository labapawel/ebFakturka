<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('content.nav.currencies') }}
            </h2>
            <a href="{{ route('currencies.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('content.common.add_new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-table-filters placeholder="Szukaj po kodzie lub nazwie waluty" />
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="code" :label="__('content.currencies.code')" :current-sort="request('sort', 'code')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="name" :label="__('content.currencies.name')" :current-sort="request('sort', 'code')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="exchange_rate" :label="__('content.currencies.rate')" :current-sort="request('sort', 'code')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="is_default" :label="__('content.currencies.is_default')" :current-sort="request('sort', 'code')" :current-dir="request('dir', 'asc')" /></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($currencies as $currency)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $currency->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $currency->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $currency->exchange_rate }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $currency->is_default ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $currency->is_default ? 'Tak' : 'Nie' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('currencies.edit', $currency) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">{{ __('content.common.edit') }}</a>
                                        @if(!$currency->is_default)
                                            <form action="{{ route('currencies.destroy', $currency) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('content.common.confirm_delete') }}')">{{ __('content.common.delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $currencies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
