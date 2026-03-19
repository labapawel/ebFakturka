@props([
    'column',
    'label',
    'currentSort' => null,
    'currentDir' => 'asc',
])

@php
    $isActive = $currentSort === $column;
    $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->query(), ['sort' => $column, 'dir' => $nextDir]);
    $indicator = $isActive ? ($currentDir === 'asc' ? '&uarr;' : '&darr;') : '';
@endphp

<a
    href="{{ request()->url() . '?' . http_build_query($query) }}"
    class="inline-flex items-center gap-1 hover:text-gray-700"
>
    <span>{{ $label }}</span>
    @if ($indicator !== '')
        <span class="text-[10px]">{!! $indicator !!}</span>
    @endif
</a>
