<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('content.recurring.title') }}
            </h2>
            <a href="{{ route('recurring_invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('content.recurring.add_new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-table-filters placeholder="Szukaj po kontrahencie, NIP, częstotliwości, statusie lub walucie" />
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="contractor" :label="__('content.invoices.contractor')" :current-sort="request('sort', 'next_issue_date')" :current-dir="request('dir', 'desc')" /></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="frequency" :label="__('content.recurring.frequency')" :current-sort="request('sort', 'next_issue_date')" :current-dir="request('dir', 'desc')" /></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="next_issue_date" :label="__('content.recurring.next_issue_date')" :current-sort="request('sort', 'next_issue_date')" :current-dir="request('dir', 'desc')" /></th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="gross_total" :label="__('content.invoices.gross_amount')" :current-sort="request('sort', 'next_issue_date')" :current-dir="request('dir', 'desc')" /></th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="status" :label="__('content.recurring.status')" :current-sort="request('sort', 'next_issue_date')" :current-dir="request('dir', 'desc')" /></th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recurringInvoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $invoice->contractor->name }}</div>
                                            <div class="text-xs text-gray-500">NIP: {{ $invoice->contractor->nip }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ __('content.recurring.frequencies.' . $invoice->frequency) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $invoice->next_issue_date->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">
                                            {{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ __('content.recurring.' . $invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('recurring_invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('content.common.preview') }}</a>
                                            <!-- ToDo: Add Edit/Delete -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 italic">{{ __('content.recurring.no_invoices') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $recurringInvoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
