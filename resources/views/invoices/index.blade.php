<x-app-layout>
    <x-slot name="topbar">
        <!-- Search moved to header slot per user request -->
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col gap-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('content.invoices.title') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <!-- Search -->
                <div class="w-full">
                    <form method="GET" class="w-full" oninput="clearTimeout(this._searchTimeout); this._searchTimeout = setTimeout(() => this.requestSubmit(), 400);">
                        <input type="hidden" name="sort" value="{{ request('sort', 'number') }}">
                        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Szukaj..."
                            class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </form>
                </div>

                <!-- Per Page -->
                <div class="flex md:justify-center">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="q" value="{{ request('q') }}">
                        <input type="hidden" name="sort" value="{{ request('sort', 'number') }}">
                        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

                        <label for="invoice-per-page" class="whitespace-nowrap text-sm font-medium text-gray-700">Na stronę</label>
                        <select
                            id="invoice-per-page"
                            name="per_page"
                            onchange="this.form.submit()"
                            class="w-24 rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            @foreach ([10, 25, 50, 100] as $option)
                                <option value="{{ $option }}" @selected((int) request('per_page', 10) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- Add Button -->
                <div class="flex md:justify-end">
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500 shadow-sm">
                        {{ __('content.invoices.issue_invoice') }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="number" :label="__('content.invoices.number')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="issue_date" :label="__('content.invoices.issue_date')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="contractor" :label="__('content.invoices.contractor')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="gross_total" :label="__('content.invoices.gross_amount')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Księgowanie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="status" :label="__('content.invoices.status')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:underline">
                                            {{ $invoice->number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $invoice->buyer_name }}</div>
                                        @if($invoice->buyer_recipient_name)
                                            <div class="text-xs text-indigo-500 italic">Odb: {{ $invoice->buyer_recipient_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            @if($invoice->booking_status === 'booked')
                                                <span class="text-green-600" title="Zaksięgowano">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                </span>
                                            @elseif($invoice->booking_status === 'do_not_book')
                                                <span class="text-red-500" title="Nie księguj">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </span>
                                            @else
                                                <span class="text-gray-300" title="Do księgowania">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </span>
                                            @endif

                                            @if($invoice->accounting_note)
                                                <div class="relative group">
                                                    <svg class="w-5 h-5 text-indigo-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                                    <div class="absolute left-full ml-2 bottom-0 hidden group-hover:block z-50 w-48 p-2 bg-gray-800 text-white text-xs rounded shadow-lg">
                                                        {{ $invoice->accounting_note }}
                                                        <div class="absolute w-2 h-2 bg-gray-800 rotate-45 -left-1 bottom-2"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            @if($invoice->ksef_status === 'sent')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 w-fit">
                                                    {{ __('content.invoices.ksef_sent') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 w-fit">
                                                    {{ $invoice->status_pl }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('content.common.preview') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
