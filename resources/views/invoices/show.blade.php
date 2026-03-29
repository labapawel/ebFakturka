<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Faktura {{ $invoice->number }}
                </h2>
                @if($invoice->ksef_status === 'sent')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        KSeF: {{ $invoice->ksef_number }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Nie wysĹ‚ano do KSeF
                    </span>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    WrĂłÄ‡
                </a>
                
                @if($invoice->ksef_status !== 'sent')
                <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edytuj
                </a>
                <form action="{{ route('invoices.send_to_ksef', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        WyĹ›lij do KSeF
                    </button>
                </form>
                @else
                <form action="{{ route('invoices.prepare_correction', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Przygotuj korektÄ™
                    </button>
                </form>
                @endif

                <a href="{{ route('invoices.xml', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    Pobierz XML (KSeF)
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Pobierz PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Header -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="font-bold text-gray-500 mb-2">{{ __('content.invoices.seller') }}</h3>
                            <p>{{ config('company.name') }}</p>
                            <p>{{ config('company.address_line_1') }}</p>
                            <p>{{ config('company.address_line_2') }}</p>
                            <p>NIP: {{ config('company.nip') }}</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-gray-500 mb-2">{{ __('content.invoices.buyer') }}</h3>
                            <p class="font-bold">{{ $invoice->contractor->name }}</p>
                            <p>{{ $invoice->contractor->address_street }} {{ $invoice->contractor->address_building }}</p>
                            <p>{{ $invoice->contractor->postal_code }} {{ $invoice->contractor->city }}</p>
                            <p>NIP: {{ $invoice->contractor->nip }}</p>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-4 gap-4 mb-8 bg-gray-50 p-4 rounded text-sm">
                        <div>
                            <span class="block text-gray-500">Data wystawienia</span>
                            <span class="font-bold">{{ $invoice->issue_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Data sprzedaĹĽy</span>
                            <span class="font-bold">{{ $invoice->sale_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Termin pĹ‚atnoĹ›ci</span>
                            <span class="font-bold">{{ $invoice->due_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Metoda pĹ‚atnoĹ›ci</span>
                            <span class="font-bold">{{ $invoice->payment_method }}</span>
                        </div>
                    </div>

                    @if($invoice->description)
                    <div class="mb-8 p-4 bg-gray-50 rounded">
                        <span class="block text-gray-500 text-sm mb-1">Opis / Uwagi</span>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $invoice->description }}</p>
                    </div>
                    @endif

                    @if($isVatExempt && !empty($vatExemptionReason))
                    <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded">
                        <span class="block text-amber-700 text-sm mb-1">Podstawa prawna zwolnienia z VAT</span>
                        <p class="text-amber-900">{{ $vatExemptionReason }}</p>
                    </div>
                    @endif

                    <!-- Items -->
                    <table class="min-w-full divide-y divide-gray-200 mb-8">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lp.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('content.invoices.name') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('content.common.quantity') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">JM</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cena @if(!$isVatExempt) Netto @else @endif</th>
                                @if(!$isVatExempt)
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">VAT</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">WartoĹ›Ä‡ Netto</th>
                                @endif
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">WartoĹ›Ä‡ @if(!$isVatExempt) Brutto @else @endif</th>
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
                                    @if(!$isVatExempt)
                                    <td class="px-4 py-2 text-right">{{ $item->vat_rate * 100 }}%</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($item->quantity * $item->net_price, 2) }}</td>
                                    @endif
                                    <td class="px-4 py-2 text-right font-bold">{{ number_format($item->gross_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="flex justify-between mt-8">
                        <div>
                            <p class="font-bold text-gray-700">SĹ‚ownie:</p>
                            <p class="text-gray-600">{{ $invoice->amount_in_words }}</p>

                            <div class="mt-4">
                                <p class="font-bold text-gray-700">Dane do przelewu:</p>
                                <p class="text-gray-600">Bank: {{ config('company.bank_name', 'Brak nazwy banku') }}</p>
                                <p class="text-gray-600">Konto: <span class="font-mono font-medium">{{ config('company.bank_account', 'Brak numeru konta') }}</span></p>
                            </div>
                        </div>
                        <div class="w-1/3">
                            @if(!$isVatExempt)
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
                                <span>Do ZapĹ‚aty:</span>
                                <span>{{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

