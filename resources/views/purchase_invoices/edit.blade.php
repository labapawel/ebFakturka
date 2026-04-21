<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edycja Faktury Zakupowej {{ $invoice->number }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('purchase_invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Wróć do listy
                </a>
                <a href="{{ route('purchase_invoices.show', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-center text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Podgląd Faktury
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Informacje o fakturze (Tylko do odczytu) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informacje podstawowe (KSeF)</h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <span class="block text-gray-500 mb-1">Sprzedawca</span>
                            <div class="font-semibold">{{ $invoice->contractor->name }}</div>
                            <div>NIP: {{ $invoice->contractor->nip }}</div>
                        </div>
                        <div class="text-right">
                            <span class="block text-gray-500 mb-1">Numer KSeF</span>
                            <div class="font-mono text-xs text-gray-600 break-all">{{ $invoice->ksef_number ?? 'Brak numeru KSeF' }}</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg text-sm">
                        <div>
                            <span class="block text-gray-500">Data wystawienia</span>
                            <span class="font-bold">{{ $invoice->issue_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Data sprzedaży</span>
                            <span class="font-bold">{{ $invoice->sale_date->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Kwota Brutto</span>
                            <span class="font-bold text-indigo-700">{{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Numer Dokumentu</span>
                            <span class="font-bold">{{ $invoice->number }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formularz edycji danych wewnętrznych -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Wewnętrzne dane księgowe</h3>
                    
                    <form action="{{ route('purchase_invoices.update', $invoice) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="flex flex-col gap-6">
                            
                            <div class="mb-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Status księgowania</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="relative flex items-center p-4 rounded-lg border cursor-pointer hover:bg-gray-50 {{ old('booking_status', $invoice->booking_status) === 'to_book' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                                        <input type="radio" name="booking_status" value="to_book" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('booking_status', $invoice->booking_status) === 'to_book' ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">Do księgowania</span>
                                            <span class="block text-xs text-gray-500 mt-1">Faktura czeka na zaksięgowanie</span>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-center p-4 rounded-lg border cursor-pointer hover:bg-green-50 {{ old('booking_status', $invoice->booking_status) === 'booked' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                                        <input type="radio" name="booking_status" value="booked" class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500" {{ old('booking_status', $invoice->booking_status) === 'booked' ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">Zaksięgowano</span>
                                            <span class="block text-xs text-gray-500 mt-1">Faktura wprowadzona do ksiąg</span>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-center p-4 rounded-lg border cursor-pointer hover:bg-red-50 {{ old('booking_status', $invoice->booking_status) === 'do_not_book' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                                        <input type="radio" name="booking_status" value="do_not_book" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" {{ old('booking_status', $invoice->booking_status) === 'do_not_book' ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">Nie księguj</span>
                                            <span class="block text-xs text-gray-500 mt-1">Nieznana / Nie należy do firmy</span>
                                        </div>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('booking_status')" class="mt-2" />
                            </div>
                            
                            <div>
                                <label for="accounting_note" class="block text-sm font-bold text-gray-700 mb-2">Opis księgowy / Cel zakupu</label>
                                <textarea id="accounting_note" name="accounting_note" rows="5" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Dodaj wewnętrzny opis dla księgowości (np. koszty biurowe, delegacja, projekt X)...">{{ old('accounting_note', $invoice->accounting_note) }}</textarea>
                                <p class="mt-2 text-sm text-gray-500">Opis ten będzie widoczny na liście faktur zakupowych po najechaniu na ikonkę.</p>
                            </div>
                            
                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    Zapisz zmiany w systemie
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
