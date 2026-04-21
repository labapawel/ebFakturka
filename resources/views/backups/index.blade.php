<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('content.nav.backups') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-md mb-4 shadow-sm" role="alert">
                    <p class="font-bold">Sukces</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4 shadow-sm" role="alert">
                    <p class="font-bold">Błąd</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4 shadow-sm" role="alert">
                    <p class="font-bold">Błąd walidacji</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Karta Eksportu -->
                <div class="p-6 bg-white shadow-lg rounded-xl flex flex-col justify-between border border-slate-200">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-indigo-100 rounded-full text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">Eksport Danych (Kopia Zapasowa)</h3>
                        </div>
                        <p class="text-slate-600 mt-2">
                            Pobierz kopię zapasową całej bazy danych oraz plików XML faktur KSeF w formacie archiwum ZIP. Plik zawiera <code class="bg-slate-100 px-1 rounded text-sm">database.json</code> z danymi bazy oraz foldery <code class="bg-slate-100 px-1 rounded text-sm">ksef/sent/</code> i <code class="bg-slate-100 px-1 rounded text-sm">ksef/invoices/</code> z oryginalnymi dokumentami XML. Może zostać użyty w procesie "Importu Danych" poniżej.
                        </p>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('backups.export') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors shadow-md">
                            Pobierz Kopię Zapasową (.zip)
                        </a>
                    </div>
                </div>

                <!-- Karta Importu -->
                <div class="p-6 bg-white shadow-lg rounded-xl flex flex-col justify-between border border-slate-200">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-emerald-100 rounded-full text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">Import Danych (Przywracanie)</h3>
                        </div>
                        <p class="text-slate-600 mt-2 mb-4">
                            Wybierz plik z kopią zapasową systemu w formacie ZIP (nowy) lub JSON (stary format) i kliknij 'Przywróć Dane'. Plik ZIP przywróci zarówno bazę danych jak i oryginalne pliki XML faktur KSeF.<br>
                            <span class="font-bold text-red-500">Uwaga: ta operacja bezpowrotnie wyczyści i nadpisze dotychczasową zawartość bazy danych!</span>
                        </p>
                    </div>

                    <form action="{{ route('backups.import') }}" method="POST" enctype="multipart/form-data" class="mt-4" id="importForm" onsubmit="return confirm('Czy na pewno chcesz przywrócić dane? Ta operacja usunie wszystkie obecne informacje z systemu.');">
                        @csrf
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-slate-900" for="backup_file">Załącz plik kopii zapasowej</label>
                            <input class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                id="backup_file" name="backup_file" type="file" accept=".zip,.json" required>
                            <p class="mt-1 text-sm text-slate-500" id="file_input_help">Pliki .zip (z XML KSeF) lub .json (stary format) wygenerowane z systemu ebFakturka.</p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors shadow-md">
                                Przywróć Dane
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
