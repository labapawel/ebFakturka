<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Faktura Zakupowa {{ $invoice->number }}
                </h2>
                @if($invoice->ksef_status === 'fetched')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        KSeF: Pobrana
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Status: {{ $invoice->ksef_status }}
                    </span>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('purchase_invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Wróć
                </a>
                
                <a href="{{ route('purchase_invoices.xml', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    Pobierz XML (KSeF)
                </a>
                <a href="{{ route('purchase_invoices.pdf', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Pobierz PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Header (Swapped for Purchase Invoice) -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="font-bold text-gray-500 mb-2">{{ __('content.invoices.seller') }}</h3>
                            <p class="font-bold">{{ $invoice->contractor->name }}</p>
                            <p>{{ $invoice->contractor->address_street }} {{ $invoice->contractor->address_building }}</p>
                            <p>{{ $invoice->contractor->postal_code }} {{ $invoice->contractor->city }}</p>
                            <p>NIP: {{ $invoice->contractor->nip }}</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-gray-500 mb-2">{{ __('content.invoices.buyer') }}</h3>
                            <p>{{ config('company.name') }}</p>
                            <p>{{ config('company.address_line_1') }}</p>
                            <p>{{ config('company.address_line_2') }}</p>
                            <p>NIP: {{ config('company.nip') }}</p>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-4 gap-4 mb-8 bg-gray-50 p-4 rounded text-sm">
                        <div>
                            <span class="block text-gray-500">Data wystawienia</span>
                            <span class="font-bold">{{ $invoice->issue_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Data sprzedaży</span>
                            <span class="font-bold">{{ $invoice->sale_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Termin płatności</span>
                            <span class="font-bold">{{ $invoice->due_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Metoda płatności</span>
                            <span class="font-bold">{{ $invoice->payment_method }}</span>
                        </div>
                    </div>

                    <!-- Items -->
                    <table class="min-w-full divide-y divide-gray-200 mb-8">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lp.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('content.invoices.name') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('content.common.quantity') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">JM</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cena @if(!$isVatExempt || $invoice->type === 'purchase') Netto @else @endif</th>
                                @if(!$isVatExempt || $invoice->type === 'purchase')
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">VAT</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Wartość Netto</th>
                                @endif
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Wartość @if(!$isVatExempt || $invoice->type === 'purchase') Brutto @else @endif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($invoice->items as $index => $item)
                                <tr>
                                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2">{{ $item->name }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-2">{{ $item->unit }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($item->net_price, 2) }}</td>
                                    @if(!$isVatExempt || $invoice->type === 'purchase')
                                    <td class="px-4 py-2 text-right">{{ $item->vat_rate * 100 }}%</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($item->quantity * $item->net_price, 2) }}</td>
                                    @endif
                                    <td class="px-4 py-2 text-right font-bold">{{ number_format($item->gross_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="flex justify-end mb-8">
                        <div class="w-1/3">
                            @if(!$isVatExempt || $invoice->type === 'purchase')
                            <div class="flex justify-between py-1 border-b">
                                <span>Razem Netto:</span>
                                <span>{{ number_format($invoice->net_total, 2) }} {{ $invoice->currency->code }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b">
                                <span>Razem VAT:</span>
                                <span>{{ number_format($invoice->vat_total, 2) }} {{ $invoice->currency->code }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between py-2 text-lg font-bold">
                                <span>Do Zapłaty:</span>
                                <span>{{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Accounting Form -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacje Księgowe</h3>
                        <form action="{{ route('purchase_invoices.update', $invoice) }}" method="POST" class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                            @csrf
                            @method('PUT')
                            
                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_booked" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" @checked($invoice->is_booked)>
                                        <span class="ml-2 text-sm text-gray-600 font-medium font-bold">Faktura zaksięgowana</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label for="accounting_note" class="block text-sm font-medium text-gray-700 mb-1">Opis do faktury</label>
                                    <textarea id="accounting_note" name="accounting_note" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Dodaj wewnętrzny opis dla księgowości...">{{ $invoice->accounting_note }}</textarea>
                                </div>
                                
                                <div>
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Zapisz dane księgowe
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
