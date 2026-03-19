<x-app-layout>
    <x-slot name="header">
        {{ __('content.dashboard.title') }}
    </x-slot>

    <div>
        <div class="w-full mx-auto">
            
            <!-- Statystyki -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Karta: Przychód w tym miesiącu -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl md:col-span-2 lg:col-span-1 text-white" style="background: linear-gradient(135deg, #6366f1 0%, #9333ea 100%);">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-white bg-opacity-20">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium opacity-80">{{ __('content.dashboard.month_revenue') }}</p>
                                <p class="text-2xl font-bold">{{ number_format($monthRevenue, 2) }} PLN</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Karta: Wydatki w tym miesiącu -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl md:col-span-2 lg:col-span-1 text-white" style="background: linear-gradient(135deg, #ec4899 0%, #e11d48 100%);">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-white bg-opacity-20">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium opacity-80">{{ __('content.dashboard.month_expenses') }}</p>
                                <p class="text-2xl font-bold">{{ number_format($monthExpenses, 2) }} PLN</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Karta: Bilans w tym miesiącu -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl md:col-span-2 lg:col-span-1 text-white" style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-white bg-opacity-20">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium opacity-80">{{ __('content.dashboard.balance') }}</p>
                                <p class="text-2xl font-bold">{{ number_format($monthBalance, 2) }} PLN</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Karta: Ilość faktur -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('content.dashboard.issued_invoices') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $monthInvoicesCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Karta: Ilość faktur zakupowych -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-rose-100 text-rose-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('content.dashboard.purchase_invoices') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $monthPurchaseCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Karta: Aktywne Cykle -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('content.dashboard.active_recurring') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $activeRecurringCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Karta: Kontrahenci -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('content.dashboard.contractors') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalContractors }}</p>
                        </div>
                    </div>
                </div>

                <!-- Karta: Produkty -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('content.dashboard.products') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Ostatnie Faktury Sprzedaży -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('content.dashboard.recent_invoices') }}</h3>
                        <a href="{{ route('invoices.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ __('content.dashboard.view_all') }} &rarr;</a>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.number') }}</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.contractor') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.gross_amount') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentInvoices as $invoice)
                                        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                            <td class="px-3 py-4 whitespace-nowrap font-medium text-gray-900 text-sm">{{ $invoice->number }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-gray-700 text-sm">
                                                <div class="font-medium truncated">{{ Str::limit($invoice->contractor->name, 20) }}</div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-right font-bold text-gray-900 text-sm">
                                                {{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('content.common.preview') }}</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-500 italic">{{ __('content.dashboard.no_invoices') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ostatnie Faktury Zakupowe -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('content.dashboard.recent_purchase_invoices') }}</h3>
                        <a href="{{ route('purchase_invoices.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ __('content.dashboard.view_all') }} &rarr;</a>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.number') }}</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.contractor') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('content.invoices.gross_amount') }}</th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentPurchaseInvoices as $invoice)
                                        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                            <td class="px-3 py-4 whitespace-nowrap font-medium text-gray-900 text-sm">{{ $invoice->number }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-gray-700 text-sm">
                                                <div class="font-medium truncated">{{ Str::limit($invoice->contractor->name, 20) }}</div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-right font-bold text-gray-900 text-sm">
                                                {{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('purchase_invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('content.common.preview') }}</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-500 italic">{{ __('content.dashboard.no_purchase_invoices') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
