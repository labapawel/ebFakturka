<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Faktury Zakupowe') }}
            </h2>
            <form action="{{ route('purchase_invoices.fetch') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    {{ __('Pobierz z KSeF') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="">
        <div class="w-full mx-auto">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-table-filters placeholder="Szukaj po numerze, numerze KSeF, kontrahencie lub walucie" />

                    @if($invoices->isEmpty())
                        <div class="text-center py-10 text-gray-500">
                            Brak faktur zakupowych. Kliknij "Pobierz z KSeF", aby pobrać nowe dokumenty.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="number" label="Numer wew." :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="ksef_number" label="Numer KSeF" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="issue_date" label="Data wystawienia" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="contractor" :label="__('content.invoices.contractor')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="gross_total" label="Kwota Brutto" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Księgowanie</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="status" :label="__('content.invoices.status')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($invoices as $invoice)
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $invoice->number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono" title="{{ $invoice->ksef_number }}">
                                                {{ \Illuminate\Support\Str::limit($invoice->ksef_number, 15) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $invoice->issue_date->format('Y-m-d') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $invoice->contractor->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                {{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    @if($invoice->is_booked)
                                                        <span class="text-green-600" title="Zaksięgowano">
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                        </span>
                                                    @else
                                                        <span class="text-gray-300" title="Do zaksięgowania">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        </span>
                                                    @endif

                                                    @if($invoice->accounting_note)
                                                        <div class="relative group">
                                                            <svg class="w-5 h-5 text-indigo-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                                            <div class="absolute left-full ml-2 bottom-0 hidden group-hover:block z-50 w-48 p-2 bg-gray-800 text-white text-xs rounded shadow-lg whitespace-normal">
                                                                {{ $invoice->accounting_note }}
                                                                <div class="absolute w-2 h-2 bg-gray-800 rotate-45 -left-1 bottom-2"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $invoice->ksef_status ?? 'Pobrana' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('purchase_invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Otwórz</a>
                                                <a href="{{ route('purchase_invoices.pdf', $invoice) }}" class="text-gray-600 hover:text-gray-900 mr-2">PDF</a>
                                                <a href="{{ route('purchase_invoices.xml', $invoice) }}" class="text-gray-600 hover:text-gray-900">XML</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
