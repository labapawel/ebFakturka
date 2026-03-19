<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dodaj Walutę') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('currencies.store') }}">
                        @csrf

                        <!-- Code -->
                        <div>
                            <x-input-label for="code" :value="__('Kod (np. PLN)')" />
                            <x-text-input id="code" class="block mt-1 w-full uppercase" type="text" name="code" :value="old('code')" required autofocus maxlength="3" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nazwa (np. Polski Złoty)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Exchange Rate -->
                        <div class="mt-4">
                            <x-input-label for="exchange_rate" :value="__('Kurs wymiany')" />
                            <x-text-input id="exchange_rate" class="block mt-1 w-full" type="number" step="0.0001" name="exchange_rate" :value="old('exchange_rate', 1.0000)" required />
                            <x-input-error :messages="$errors->get('exchange_rate')" class="mt-2" />
                        </div>

                        <!-- Default -->
                        <div class="block mt-4">
                            <label for="is_default" class="inline-flex items-center">
                                <input id="is_default" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_default" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Domyślna') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Zapisz') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
