<?php

namespace App\Support;

class VatSettings
{
    public const VAT_PAYER = 'vat';
    public const VAT_EXEMPT = 'exempt';
    public const REASON_STATUTE = 'P_19A';
    public const REASON_DIRECTIVE = 'P_19B';
    public const REASON_OTHER = 'P_19C';

    public static function mode(): string
    {
        $value = trim((string) config('vat.mode', self::VAT_PAYER));

        if ($value === '' || in_array(strtolower($value), ['false', '0', self::VAT_PAYER], true)) {
            return self::VAT_PAYER;
        }

        if (in_array(strtolower($value), ['true', '1', self::VAT_EXEMPT], true)) {
            return self::VAT_EXEMPT;
        }

        return $value;
    }

    public static function isExempt(): bool
    {
        return self::mode() !== self::VAT_PAYER;
    }

    public static function legalBasis(): ?string
    {
        if (!self::isExempt()) {
            return null;
        }

        $value = trim((string) config('vat.exemption_reason_text', ''));

        return $value !== '' ? $value : 'Przepis ustawy albo aktu wydanego na podstawie ustawy, na podstawie ktorego podatnik stosuje zwolnienie od podatku';
    }

    public static function reasonField(): string
    {
        $value = trim((string) config('vat.exemption_reason_type', self::REASON_STATUTE));

        return in_array($value, [self::REASON_STATUTE, self::REASON_DIRECTIVE, self::REASON_OTHER], true)
            ? $value
            : self::REASON_STATUTE;
    }

    public static function options(): array
    {
        return [
            self::VAT_PAYER => 'VAT dla vatowcow',
            self::VAT_EXEMPT => 'Firma zwolniona z VAT',
        ];
    }

    public static function reasonTypeOptions(): array
    {
        return [
            self::REASON_STATUTE => 'Przepis ustawy albo aktu wydanego na podstawie ustawy',
            self::REASON_DIRECTIVE => 'Przepis dyrektywy 2006/112/WE',
            self::REASON_OTHER => 'Inna podstawa prawna',
        ];
    }
}
