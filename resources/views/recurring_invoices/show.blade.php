<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Szczegóły Cyklu') }} #{{ $recurringInvoice->id }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('recurring_invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('content.common.back') }}
                </a>
                <a href="{{ route('recurring_invoices.edit', $recurringInvoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('content.common.edit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cycle Info -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                             <h3 class="font-bold text-lg mb-4">{{ __('Informacje o Cyklu') }}</h3>
                             <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                                <div class="px-4 py-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('content.invoices.status') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $recurringInvoice->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ __('content.recurring.' . $recurringInvoice->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="px-4 py-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('content.recurring.frequency') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0">{{ __('content.recurring.frequencies.' . $recurringInvoice->frequency) }}</dd>
                                </div>
                                <div class="px-4 py-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('content.recurring.interval') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0">Co {{ $recurringInvoice->frequency_interval }} msc</dd>
                                </div>
                                <div class="px-4 py-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium text-gray-500">Następne wystawienie</dt>
                                    <dd class="mt-1 text-sm font-bold text-indigo-600 sm:mt-0">{{ $recurringInvoice->next_issue_date->format('Y-m-d') }}</dd>
                                </div>
                                <div class="px-4 py-3 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium text-gray-500">Data Startu</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0">{{ $recurringInvoice->start_date->format('Y-m-d') }}</dd>
                                </div>
                             </dl>
                        </div>
                    </div>
                </div>

                <!-- Invoice Content -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="font-bold text-lg mb-4">{{ __('Dane Faktury') }}</h3>
                            
                            <div class="mb-4 p-4 bg-gray-50 rounded">
                                <h4 class="font-semibold text-sm text-gray-500 uppercase">{{ __('content.invoices.contractor') }}</h4>
                                <p class="text-lg font-bold">{{ $recurringInvoice->contractor->name }}</p>
                                <p>NIP: {{ $recurringInvoice->contractor->nip }}</p>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 mb-6">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('content.invoices.name') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('content.common.quantity') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cena Netto</th>
                                        @if(!$isVatExempt)
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">VAT</th>
                                        @endif
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wartość Brutto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recurringInvoice->items as $item)
                                    <tr>
                                        <td class="px-3 py-4 text-sm text-gray-900">{{ $item->name }}</td>
                                        <td class="px-3 py-4 text-sm text-gray-900 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td class="px-3 py-4 text-sm text-gray-900 text-right">{{ number_format($item->net_price, 2) }}</td>
                                        @if(!$isVatExempt)
                                        <td class="px-3 py-4 text-sm text-gray-900 text-right">{{ (float)$item->vat_rate * 100 }}%</td>
                                        @endif
                                        <td class="px-3 py-4 text-sm text-gray-900 text-right font-bold">{{ number_format($item->gross_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="{{ $isVatExempt ? 3 : 4 }}" class="px-3 py-3 text-right font-bold">Razem:</td>
                                        <td class="px-3 py-3 text-right font-bold text-indigo-700">
                                            {{ number_format($recurringInvoice->gross_total, 2) }} {{ $recurringInvoice->currency->code }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
