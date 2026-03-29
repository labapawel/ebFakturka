<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceCounter;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    public function peekNextNumber(?Carbon $date = null, string $type = 'sales'): string
    {
        $date = $date ? $date->copy() : now();
        $format = $this->numberFormat();
        [$prefix, $suffix] = $this->prefixAndSuffix($format, $date);
        [$periodYear, $periodMonth] = $this->periodForFormat($format, $date);

        $counter = InvoiceCounter::where('type', $type)->first();
        $lastNumber = 0;

        if ($counter) {
            $samePeriod = $counter->period_year === $periodYear && $counter->period_month === $periodMonth;
            if ($samePeriod) {
                $lastNumber = (int) $counter->last_number;
            }
        } else {
            $lastNumber = $this->lastNumberFromInvoices($type, $prefix, $suffix);
        }

        return $this->buildNumber($date, $lastNumber + 1);
    }

    public function reserveNextNumber(?Carbon $date = null, string $type = 'sales'): string
    {
        $date = $date ? $date->copy() : now();
        $format = $this->numberFormat();
        [$prefix, $suffix] = $this->prefixAndSuffix($format, $date);
        [$periodYear, $periodMonth] = $this->periodForFormat($format, $date);

        return DB::transaction(function () use ($type, $date, $format, $prefix, $suffix, $periodYear, $periodMonth) {
            $counter = InvoiceCounter::where('type', $type)->lockForUpdate()->first();

            if (!$counter) {
                try {
                    $counter = InvoiceCounter::create([
                        'type' => $type,
                        'period_year' => $periodYear,
                        'period_month' => $periodMonth,
                        'last_number' => $this->lastNumberFromInvoices($type, $prefix, $suffix),
                    ]);
                } catch (QueryException $exception) {
                    $counter = InvoiceCounter::where('type', $type)->lockForUpdate()->first();
                }
            }

            $periodChanged = $counter->period_year !== $periodYear || $counter->period_month !== $periodMonth;
            if ($periodChanged) {
                $counter->period_year = $periodYear;
                $counter->period_month = $periodMonth;
                $counter->last_number = $this->lastNumberFromInvoices($type, $prefix, $suffix);
            }

            $nextNumber = (int) $counter->last_number + 1;
            $number = $this->buildNumber($date, $nextNumber);

            while (Invoice::where('type', $type)->where('number', $number)->exists()) {
                $nextNumber++;
                $number = $this->buildNumber($date, $nextNumber);
            }

            $counter->last_number = $nextNumber;
            $counter->save();

            return $number;
        });
    }

    public function syncCounterFromNumber(string $number, ?Carbon $date = null, string $type = 'sales'): void
    {
        $date = $date ? $date->copy() : now();
        $format = $this->numberFormat();
        [$prefix, $suffix] = $this->prefixAndSuffix($format, $date);
        [$periodYear, $periodMonth] = $this->periodForFormat($format, $date);

        if (!str_starts_with($number, $prefix) || !str_ends_with($number, $suffix)) {
            return;
        }

        $raw = substr($number, strlen($prefix));
        if ($suffix !== '') {
            $raw = substr($raw, 0, -strlen($suffix));
        }

        if ($raw === '' || preg_match('/^\d+$/', $raw) !== 1) {
            return;
        }

        $parsed = (int) $raw;

        DB::transaction(function () use ($type, $periodYear, $periodMonth, $parsed) {
            $counter = InvoiceCounter::where('type', $type)->lockForUpdate()->first();

            if (!$counter) {
                $counter = InvoiceCounter::create([
                    'type' => $type,
                    'period_year' => $periodYear,
                    'period_month' => $periodMonth,
                    'last_number' => $parsed,
                ]);

                return;
            }

            $periodChanged = $counter->period_year !== $periodYear || $counter->period_month !== $periodMonth;
            if ($periodChanged) {
                $counter->period_year = $periodYear;
                $counter->period_month = $periodMonth;
                $counter->last_number = $parsed;
                $counter->save();
                return;
            }

            if ($parsed > (int) $counter->last_number) {
                $counter->last_number = $parsed;
                $counter->save();
            }
        });
    }

    private function numberFormat(): string
    {
        return (string) env('INVOICE_NUMBER_FORMAT', 'FV/{Y}/{m}/{nr}');
    }

    private function buildNumber(Carbon $date, int $number): string
    {
        $format = $this->numberFormat();
        [$prefix, $suffix] = $this->prefixAndSuffix($format, $date);
        $padding = (int) env('INVOICE_NUMBER_PADDING', 3);

        return $prefix . str_pad((string) $number, $padding, '0', STR_PAD_LEFT) . $suffix;
    }

    private function prefixAndSuffix(string $format, Carbon $date): array
    {
        $pattern = str_replace(
            ['{Y}', '{y}', '{m}', '{d}'],
            [$date->format('Y'), $date->format('y'), $date->format('m'), $date->format('d')],
            $format
        );

        $parts = explode('{nr}', $pattern);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function periodForFormat(string $format, Carbon $date): array
    {
        $hasYear = str_contains($format, '{Y}') || str_contains($format, '{y}');
        $hasMonth = str_contains($format, '{m}');

        $year = $hasYear ? (int) $date->format('Y') : null;
        $month = $hasMonth ? (int) $date->format('m') : null;

        return [$year, $month];
    }

    private function lastNumberFromInvoices(string $type, string $prefix, string $suffix): int
    {
        $query = Invoice::query()->where('type', $type)->where('number', 'like', $prefix . '%' . $suffix);

        $lastInvoice = $query->orderBy('number', 'desc')->first();
        if (!$lastInvoice) {
            return 0;
        }

        $raw = substr($lastInvoice->number, strlen($prefix));
        if ($suffix !== '') {
            $raw = substr($raw, 0, -strlen($suffix));
        }

        if ($raw === '' || preg_match('/^\d+$/', $raw) !== 1) {
            return 0;
        }

        return (int) $raw;
    }
}
