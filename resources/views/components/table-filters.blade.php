@props([
    'placeholder' => 'Szukaj...',
    'perPageOptions' => [10, 25, 50, 100],
])

<form method="GET" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4" oninput="clearTimeout(this._searchTimeout); this._searchTimeout = setTimeout(() => this.requestSubmit(), 400);">
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
                onchange="this.form.requestSubmit()"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) request('per_page', 10) === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>
