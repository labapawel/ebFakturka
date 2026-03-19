@props([
    'placeholder' => 'Szukaj...',
    'perPageOptions' => [10, 25, 50, 100],
])

<form method="GET" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
    <input type="hidden" name="sort" value="{{ request('sort') }}">
    <input type="hidden" name="dir" value="{{ request('dir') }}">

    <div class="grid gap-4 lg:grid-cols-4 lg:items-end">
        <div class="lg:col-span-3 lg:max-w-none">
            <label for="table-search" class="mb-1 block text-sm font-medium text-gray-700">Szukaj</label>
            <input
                id="table-search"
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ $placeholder }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        <div class="lg:col-span-1">
            <label for="table-per-page" class="mb-1 block text-sm font-medium text-gray-700">Na strone</label>
            <select
                id="table-per-page"
                name="per_page"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) request('per_page', 10) === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
        >
            Filtruj
        </button>

        @if(request()->hasAny(['q', 'sort', 'dir', 'per_page']))
            <a
                href="{{ url()->current() }}"
                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-100"
            >
                Wyczysc
            </a>
        @endif
    </div>
</form>
