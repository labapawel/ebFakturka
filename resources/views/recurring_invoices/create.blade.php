<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.recurring.add_new') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="invoiceForm()">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('recurring_invoices.store') }}">
                @csrf
                <!-- Cycle Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('content.recurring.title') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="frequency" :value="__('content.recurring.frequency')" />
                                <select id="frequency" name="frequency" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="monthly">{{ __('content.recurring.frequencies.monthly') }}</option>
                                    <option value="quarterly">{{ __('content.recurring.frequencies.quarterly') }}</option>
                                    <option value="yearly">{{ __('content.recurring.frequencies.yearly') }}</option>
                                    <option value="custom">{{ __('content.recurring.frequencies.custom') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('frequency')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="frequency_interval" :value="__('Interwał (co ile miesięcy)')" />
                                <x-text-input id="frequency_interval" class="block mt-1 w-full" type="number" name="frequency_interval" value="1" min="1" required />
                                <p class="text-xs text-gray-500 mt-1">Nip. 1 = co miesiąc, 3 = co kwartał</p>
                            </div>
                            <div>
                                <x-input-label for="start_date" :value="__('Data Startu')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" value="{{ date('Y-m-d') }}" required />
                            </div>
                            <div>
                                <x-input-label for="next_issue_date" :value="__('content.recurring.next_issue_date')" />
                                <x-text-input id="next_issue_date" class="block mt-1 w-full" type="date" name="next_issue_date" value="{{ date('Y-m-d') }}" required />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('Data Końca (opcjonalnie)')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details (Contractor & Items) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('content.invoices.details') }}</h3>
                        
                        <!-- Contractor & Currency -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="col-span-2">
                                <x-input-label for="contractor_id" :value="__('content.invoices.contractor')" />
                                <select id="contractor_id" name="contractor_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Wybierz kontrahenta --</option>
                                    @foreach($contractors as $contractor)
                                        <option value="{{ $contractor->id }}" {{ old('contractor_id') == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }} ({{ $contractor->nip }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('contractor_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="currency_id" :value="__('content.invoices.currency')" />
                                <select id="currency_id" name="currency_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ $currency->is_default ? 'selected' : '' }}>{{ $currency->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                         <div class="mb-6">
                            <x-input-label for="payment_method" :value="__('content.invoices.payment_method')" />
                            <select id="payment_method" name="payment_method" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="Przelew">{{ __('content.common.payment_methods.transfer') }}</option>
                                <option value="Gotówka">{{ __('content.common.payment_methods.cash') }}</option>
                                <option value="Karta">{{ __('content.common.payment_methods.card') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left py-2">Produkt / Usługa</th>
                                    <th class="text-left py-2 w-24">{{ __('content.common.quantity') }}</th>
                                    <th class="text-left py-2 w-20">JM</th>
                                    <th class="text-left py-2 w-32">Cena @if(!$isVatExempt) Netto @else @endif</th>
                                    @if(!$isVatExempt)
                                    <th class="text-left py-2 w-24">VAT</th>
                                    <th class="text-right py-2 w-32">Wartość Netto</th>
                                    @endif
                                    <th class="text-right py-2 w-32">Wartość @if(!$isVatExempt) Brutto @else @endif</th>
                                    <th class="py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-t">
                                        <td class="py-2 pr-2">
                                            <input type="text" :name="'items['+index+'][name]'" x-model="item.name" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Nazwa produktu" required>
                                            <select @change="loadProduct($event.target.value, index)" class="mt-1 w-full text-xs text-gray-500 border-gray-200 rounded">
                                                <option value="">(Wybierz produkt z bazy)</option>
                                                <template x-for="p in availableProducts" :key="p.id">
                                                    <option :value="p.id" x-text="p.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" step="0.01" class="w-full border-gray-300 rounded-md shadow-sm text-sm text-right" required>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input type="text" :name="'items['+index+'][unit]'" x-model="item.unit" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input type="number" :name="'items['+index+'][net_price]'" x-model="item.net_price" step="0.01" class="w-full border-gray-300 rounded-md shadow-sm text-sm text-right" required>
                                        </td>
                                        @if(!$isVatExempt)
                                        <td class="py-2 pr-2">
                                            <select :name="'items['+index+'][vat_rate]'" x-model="item.vat_rate" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                @foreach($vatRates as $rate)
                                                    <option value="{{ $rate->rate }}">{{ $rate->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 text-right text-sm">
                                            <span x-text="formatMoney(calculateNet(item))"></span>
                                        </td>
                                        @else
                                            <input type="hidden" :name="'items['+index+'][vat_rate]'" value="0">
                                        @endif
                                        <td class="py-2 text-right text-sm font-bold">
                                            <span x-text="formatMoney(calculateGross(item))"></span>
                                        </td>
                                        <td class="py-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <button type="button" @click="addItem()" class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-semibold">
                            + Dodaj Pozycję
                        </button>
                    </div>
                </div>

                <!-- Totals -->
                <div class="flex justify-end mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-1/3 p-6">
                        @if(!$isVatExempt)
                        <div class="flex justify-between py-1 border-b">
                            <span>Razem Netto:</span>
                            <span class="font-semibold" x-text="formatMoney(totals.net)"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b">
                            <span>Razem VAT:</span>
                            <span class="font-semibold" x-text="formatMoney(totals.vat)"></span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2 text-xl font-bold text-indigo-700">
                            <span>Razem Brutto:</span>
                            <span x-text="formatMoney(totals.gross)"></span>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Zapisz Cykl') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                items: [
                    { name: '', quantity: 1, unit: 'szt.', net_price: 0, vat_rate: {{ $isVatExempt ? 0 : 0.23 }} }
                ],
                availableProducts: @json($products),
                
                addItem() {
                    this.items.push({ name: '', quantity: 1, unit: 'szt.', net_price: 0, vat_rate: {{ $isVatExempt ? 0 : 0.23 }} });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                loadProduct(productId, index) {
                    if (!productId) return;
                    const product = this.availableProducts.find(p => p.id == productId);
                    if (product) {
                        this.items[index].name = product.name;
                        this.items[index].unit = product.unit;
                        this.items[index].net_price = product.net_price;
                        this.items[index].vat_rate = @json($isVatExempt) ? 0 : (product.vat_rate ? product.vat_rate.rate : 0.23);
                    }
                },
                calculateNet(item) {
                    return item.quantity * item.net_price;
                },
                calculateGross(item) {
                    let net = this.calculateNet(item);
                    return net * (1 + parseFloat(item.vat_rate));
                },
                get totals() {
                    let net = 0;
                    let gross = 0;
                    this.items.forEach(item => {
                        net += this.calculateNet(item);
                        gross += this.calculateGross(item);
                    });
                    return {
                        net: net,
                        vat: gross - net,
                        gross: gross
                    };
                },
                formatMoney(amount) {
                    return new Intl.NumberFormat('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
                }
            }
        }
    </script>
</x-app-layout>
