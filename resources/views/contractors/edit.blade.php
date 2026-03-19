<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.contractors.edit') }}
        </h2>
    </x-slot>

    @php
        $showLegalKsef = old('ksef_legal_name', $contractor->ksef_legal_name)
            || old('ksef_legal_street', $contractor->ksef_legal_street)
            || old('ksef_legal_postal_code', $contractor->ksef_legal_postal_code)
            || old('ksef_legal_city', $contractor->ksef_legal_city);
        $showCorrespondenceKsef = old('ksef_correspondence_name', $contractor->ksef_correspondence_name)
            || old('ksef_correspondence_street', $contractor->ksef_correspondence_street)
            || old('ksef_correspondence_postal_code', $contractor->ksef_correspondence_postal_code)
            || old('ksef_correspondence_city', $contractor->ksef_correspondence_city)
            || old('ksef_customer_number', $contractor->ksef_customer_number);
    @endphp
    <div class="py-12" x-data='{ showLegalKsef: {{ $showLegalKsef ? "true" : "false" }}, showCorrespondenceKsef: {{ $showCorrespondenceKsef ? "true" : "false" }} }'>
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('contractors.update', $contractor) }}">
                        @csrf
                        @method('PUT')

                        <div class="flex gap-4 items-end">
                            <div class="grow">
                                <x-input-label for="nip" :value="__('content.contractors.nip')" />
                                <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip', $contractor->nip)" />
                                <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                            </div>
                            @if(env('BIR_KEY'))
                            <div>
                                <button type="button" id="fetch-gus-btn" class="inline-flex items-center w-full justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:bg-gray-500 active:bg-gray-700 focus:outline-none transition ease-in-out duration-150 h-10 mb-1">
                                    {{ __('content.contractors.fetch_gus') }}
                                </button>
                            </div>
                            @endif
                        </div>

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('content.contractors.name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $contractor->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="col-span-1">
                                <x-input-label for="address_street" :value="__('content.contractors.street')" />
                                <x-text-input id="address_street" class="block mt-1 w-full" type="text" name="address_street" :value="old('address_street', $contractor->address_street)" />
                            </div>
                            <div>
                                <x-input-label for="address_building" :value="__('content.contractors.building')" />
                                <x-text-input id="address_building" class="block mt-1 w-full" type="text" name="address_building" :value="old('address_building', $contractor->address_building)" />
                            </div>
                            <div>
                                <x-input-label for="address_apartment" :value="__('content.contractors.apartment')" />
                                <x-text-input id="address_apartment" class="block mt-1 w-full" type="text" name="address_apartment" :value="old('address_apartment', $contractor->address_apartment)" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="postal_code" :value="__('content.contractors.postal_code')" />
                                <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $contractor->postal_code)" />
                            </div>
                            <div>
                                <x-input-label for="city" :value="__('content.contractors.city')" />
                                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $contractor->city)" />
                            </div>
                        </div>

                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <label for="show_legal_ksef" class="inline-flex items-center gap-3">
                                <input id="show_legal_ksef" type="checkbox" x-model="showLegalKsef" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-800">Dane KSeF nabywcy prawnego</span>
                            </label>
                        </div>

                        <div class="mt-4 border-t border-gray-200 pt-6" x-show="showLegalKsef" x-transition>
                            <h3 class="text-base font-semibold text-gray-900">Dane KSeF nabywcy prawnego</h3>
                            <p class="mt-1 text-sm text-gray-500">Opcjonalne. Jesli puste, KSeF uzyje podstawowej nazwy i adresu kontrahenta.</p>
                            <div class="mt-4">
                                <x-input-label for="ksef_legal_name" :value="'Nazwa nabywcy prawnego do KSeF'" />
                                <x-text-input id="ksef_legal_name" class="block mt-1 w-full" type="text" name="ksef_legal_name" :value="old('ksef_legal_name', $contractor->ksef_legal_name)" />
                            </div>
                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <div class="col-span-1">
                                    <x-input-label for="ksef_legal_street" :value="'Ulica nabywcy prawnego'" />
                                    <x-text-input id="ksef_legal_street" class="block mt-1 w-full" type="text" name="ksef_legal_street" :value="old('ksef_legal_street', $contractor->ksef_legal_street)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_legal_building" :value="'Nr domu nabywcy prawnego'" />
                                    <x-text-input id="ksef_legal_building" class="block mt-1 w-full" type="text" name="ksef_legal_building" :value="old('ksef_legal_building', $contractor->ksef_legal_building)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_legal_apartment" :value="'Nr lokalu nabywcy prawnego'" />
                                    <x-text-input id="ksef_legal_apartment" class="block mt-1 w-full" type="text" name="ksef_legal_apartment" :value="old('ksef_legal_apartment', $contractor->ksef_legal_apartment)" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="ksef_legal_postal_code" :value="'Kod pocztowy nabywcy prawnego'" />
                                    <x-text-input id="ksef_legal_postal_code" class="block mt-1 w-full" type="text" name="ksef_legal_postal_code" :value="old('ksef_legal_postal_code', $contractor->ksef_legal_postal_code)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_legal_city" :value="'Miasto nabywcy prawnego'" />
                                    <x-text-input id="ksef_legal_city" class="block mt-1 w-full" type="text" name="ksef_legal_city" :value="old('ksef_legal_city', $contractor->ksef_legal_city)" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <label for="show_correspondence_ksef" class="inline-flex items-center gap-3">
                                <input id="show_correspondence_ksef" type="checkbox" x-model="showCorrespondenceKsef" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-800">Dane KSeF odbiorcy / korespondencyjne</span>
                            </label>
                        </div>

                        <div class="mt-4 border-t border-gray-200 pt-6" x-show="showCorrespondenceKsef" x-transition>
                            <h3 class="text-base font-semibold text-gray-900">Dane KSeF odbiorcy / korespondencyjne</h3>
                            <p class="mt-1 text-sm text-gray-500">Uzupelnij, jesli faktura ma miec osobny AdresKoresp lub NrKlienta.</p>
                            <div class="mt-4">
                                <x-input-label for="ksef_correspondence_name" :value="'Nazwa odbiorcy / jednostki'" />
                                <x-text-input id="ksef_correspondence_name" class="block mt-1 w-full" type="text" name="ksef_correspondence_name" :value="old('ksef_correspondence_name', $contractor->ksef_correspondence_name)" />
                            </div>
                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <div class="col-span-1">
                                    <x-input-label for="ksef_correspondence_street" :value="'Ulica odbiorcy / korespondencyjna'" />
                                    <x-text-input id="ksef_correspondence_street" class="block mt-1 w-full" type="text" name="ksef_correspondence_street" :value="old('ksef_correspondence_street', $contractor->ksef_correspondence_street)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_correspondence_building" :value="'Nr domu odbiorcy / korespondencyjny'" />
                                    <x-text-input id="ksef_correspondence_building" class="block mt-1 w-full" type="text" name="ksef_correspondence_building" :value="old('ksef_correspondence_building', $contractor->ksef_correspondence_building)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_correspondence_apartment" :value="'Nr lokalu odbiorcy / korespondencyjny'" />
                                    <x-text-input id="ksef_correspondence_apartment" class="block mt-1 w-full" type="text" name="ksef_correspondence_apartment" :value="old('ksef_correspondence_apartment', $contractor->ksef_correspondence_apartment)" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="ksef_correspondence_postal_code" :value="'Kod pocztowy odbiorcy / korespondencyjny'" />
                                    <x-text-input id="ksef_correspondence_postal_code" class="block mt-1 w-full" type="text" name="ksef_correspondence_postal_code" :value="old('ksef_correspondence_postal_code', $contractor->ksef_correspondence_postal_code)" />
                                </div>
                                <div>
                                    <x-input-label for="ksef_correspondence_city" :value="'Miasto odbiorcy / korespondencyjne'" />
                                    <x-text-input id="ksef_correspondence_city" class="block mt-1 w-full" type="text" name="ksef_correspondence_city" :value="old('ksef_correspondence_city', $contractor->ksef_correspondence_city)" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <x-input-label for="ksef_customer_number" :value="'Numer klienta w systemie sprzedawcy'" />
                                <x-text-input id="ksef_customer_number" class="block mt-1 w-full" type="text" name="ksef_customer_number" :value="old('ksef_customer_number', $contractor->ksef_customer_number)" />
                            </div>
                        </div>

                        <div class="space-y-3 mt-4">
                            <div class="flex flex-col gap-2">
                                <label for="is_jst" class="inline-flex items-center gap-3">
                                    <input id="is_jst" type="checkbox" name="is_jst" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_jst', $contractor->is_jst) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ __('content.contractors.is_jst') }}</span>
                                </label>
                                <label for="is_vat_group_member" class="inline-flex items-center gap-3">
                                    <input id="is_vat_group_member" type="checkbox" name="is_vat_group_member" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_vat_group_member', $contractor->is_vat_group_member) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ __('content.contractors.is_vat_group_member') }}</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <button type="button" id="check-vat-registry-btn" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                        {{ __('content.contractors.check_vat_registry') }}
                                    </button>
                                    <span id="vat-registry-result" class="text-sm text-gray-600"></span>
                                </div>
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

    <script>
        document.getElementById('fetch-gus-btn').addEventListener('click', function() {
            const nip = document.getElementById('nip').value;
            if (!nip) return alert('Wpisz NIP');

            fetch('{{ route("contractors.fetch_gus") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nip: nip })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('name').value = data.name;
                    document.getElementById('address_street').value = data.address_street;
                    document.getElementById('address_building').value = data.address_building;
                    document.getElementById('address_apartment').value = data.address_apartment;
                    document.getElementById('postal_code').value = data.postal_code;
                    document.getElementById('city').value = data.city;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Błąd połączenia z GUS');
            });
        });

        document.getElementById('check-vat-registry-btn').addEventListener('click', function() {
            const nip = document.getElementById('nip').value;
            const resultEl = document.getElementById('vat-registry-result');
            if (!nip) return alert('Wpisz NIP');

            const btn = this;
            const originalText = btn.innerText;
            btn.innerText = 'Sprawdzanie...';
            btn.disabled = true;
            resultEl.textContent = '';

            fetch('{{ route("contractors.check_vat_registry") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nip: nip })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    return Promise.reject(data.message || 'Nie udało się sprawdzić podmiotu w wykazie VAT.');
                }
                return data;
            })
            .then(data => {
                resultEl.textContent = `${data.status_vat ?? 'Brak statusu'}${data.name ? ' - ' + data.name : ''}`;

                if (data.is_vat_group_member_guess) {
                    document.getElementById('is_vat_group_member').checked = true;
                }
            })
            .catch(error => {
                resultEl.textContent = error;
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        });
    </script>
</x-app-layout>
