<?php

namespace App\Support;

use Illuminate\Http\Request;

class TableFilters
{
    /**
     * @param  array<int>  $allowed
     */
    public static function perPage(Request $request, int $default = 10, array $allowed = [10, 25, 50, 100]): int
    {
        $value = (int) $request->integer('per_page', $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * @param  array<string>  $allowed
     */
    public static function sort(Request $request, string $default, array $allowed): string
    {
        $value = (string) $request->query('sort', $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    public static function direction(Request $request, string $default = 'asc'): string
    {
        $value = strtolower((string) $request->query('dir', $default));

        return in_array($value, ['asc', 'desc'], true) ? $value : $default;
    }

    public static function search(Request $request): string
    {
        return trim((string) $request->query('q', ''));
    }
}
