<footer class="mt-12 py-6 border-t border-gray-200 text-center text-sm text-gray-500">
    <div class="container mx-auto px-4">
        <p class="mt-1">
            &copy; {{ date('Y') }} <strong>{{ config('app.name', 'ebFakturka') }}</strong>. Wszystkie prawa zastrzeżone.
            Stworzone z <span class="text-rose-500">&hearts;</span> dla lepszej księgowości.
            <span class="mx-2">|</span>
            v{{ config('app.version', '1.0.0') }}
        </p>
    </div>
</footer>
