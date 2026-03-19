<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.vat_rates.edit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('vat_rates.update', $vatRate) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('content.vat_rates.name_hint')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $vatRate->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Rate -->
                        <div class="mt-4">
                            <x-input-label for="rate" :value="__('content.vat_rates.rate_hint')" />
                            <x-text-input id="rate" class="block mt-1 w-full" type="number" step="0.0001" name="rate" :value="old('rate', $vatRate->rate)" required />
                            <x-input-error :messages="$errors->get('rate')" class="mt-2" />
                        </div>

                        <!-- Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $vatRate->is_active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('content.vat_rates.active') }}</span>
                            </label>
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
