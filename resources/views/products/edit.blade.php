<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.products.edit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.update', $product) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('content.products.name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <!-- Net Price -->
                            <div>
                                <x-input-label for="net_price" :value="__('content.products.net_price')" />
                                <x-text-input id="net_price" class="block mt-1 w-full" type="number" step="0.01" name="net_price" :value="old('net_price', $product->net_price)" required />
                                <x-input-error :messages="$errors->get('net_price')" class="mt-2" />
                            </div>

                            <!-- Vat Rate -->
                            <div>
                                <x-input-label for="vat_rate_id" :value="__('content.products.vat_rate')" />
                                <select id="vat_rate_id" name="vat_rate_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($vatRates as $rate)
                                        <option value="{{ $rate->id }}" {{ old('vat_rate_id', $product->vat_rate_id) == $rate->id ? 'selected' : '' }}>{{ $rate->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vat_rate_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <!-- Unit -->
                            <div>
                                <x-input-label for="unit" :value="__('content.products.unit_hint')" />
                                <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $product->unit)" required />
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <!-- PKWiU -->
                            <div>
                                <x-input-label for="pkwiu" :value="__('content.products.pkwiu_hint')" />
                                <x-text-input id="pkwiu" class="block mt-1 w-full" type="text" name="pkwiu" :value="old('pkwiu', $product->pkwiu)" />
                                <x-input-error :messages="$errors->get('pkwiu')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('content.common.update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
