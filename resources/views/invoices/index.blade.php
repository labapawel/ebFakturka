<x-app-layout>
    <x-slot name="topbar">
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row  sm:justify-between">
            <h2 class="flex flex-col gap-2 sm:flex-row font-semibold text-xl text-gray-800 leading-tight">
                {{ __('content.invoices.title') }}
            </h2>
            <div class="grid grid-cols-4 gap-2">
            <div class="col-span-2">

            <form method="GET" class="w-full max-w-xl" oninput="clearTimeout(this._searchTimeout); this._searchTimeout = setTimeout(() => this.requestSubmit(), 400);">
            <input type="hidden" name="sort" value="{{ request('sort', 'number') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Szukaj po numerze, kontrahencie, walucie lub numerze KSeF"
                class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </form>
        </div>

            <div class="col-span-1 ">
                <form method="GET" class=" ">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="sort" value="{{ request('sort', 'number') }}">
                    <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

                    <label for="invoice-per-page" class="whitespace-nowrap text-sm font-medium text-gray-700">Na strone</label>
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
                <div class="col-span-1">

                <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500">
                    {{ __('content.invoices.issue_invoice') }}
                </a>
            </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="number" :label="__('content.invoices.number')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="issue_date" :label="__('content.invoices.issue_date')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="contractor" :label="__('content.invoices.contractor')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="gross_total" :label="__('content.invoices.gross_amount')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><x-table-sort-link column="currency" :label="__('content.invoices.currency')" :current-sort="request('sort', 'number')" :current-dir="request('dir', 'desc')" /></th>
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
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $invoice->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $invoice->contractor->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($invoice->gross_total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $invoice->currency->code }}</td>
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
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-600 hover:text-gray-900">{{ __('content.common.preview') }}</a>
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
