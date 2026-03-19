<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VatRegistryService
{
    private const BASE_URL = 'https://wl-api.mf.gov.pl';

    public function searchByNip(string $nip, ?Carbon $date = null): array
    {
        $normalizedNip = preg_replace('/\D+/', '', $nip ?? '');

        if (!$normalizedNip || strlen($normalizedNip) !== 10) {
            throw new RuntimeException('Podaj poprawny 10-cyfrowy numer NIP.');
        }

        $queryDate = ($date ?? Carbon::today())->toDateString();
        $url = self::BASE_URL . "/api/search/nip/{$normalizedNip}";

        $response = Http::timeout(15)
            ->acceptJson()
            ->get($url, ['date' => $queryDate]);

        if (!$response->successful()) {
            throw new RuntimeException('Nie udało się pobrać danych z wykazu VAT.');
        }

        $payload = $response->json();
        $subject = data_get($payload, 'result.subject');

        if (!$subject) {
            throw new RuntimeException('Nie znaleziono podmiotu w wykazie VAT dla wskazanego NIP.');
        }

        $name = data_get($subject, 'name', '');
        $isVatGroupMember = $this->guessVatGroupMember($name);

        return [
            'nip' => $normalizedNip,
            'name' => $name,
            'status_vat' => data_get($subject, 'statusVat'),
            'regon' => data_get($subject, 'regon'),
            'working_address' => data_get($subject, 'workingAddress') ?? data_get($subject, 'residenceAddress'),
            'request_id' => data_get($payload, 'result.requestId'),
            'request_date' => $queryDate,
            'is_vat_group_member_guess' => $isVatGroupMember,
            'guess_note' => $isVatGroupMember
                ? 'Wykryto możliwą grupę VAT na podstawie nazwy podmiotu w wykazie VAT.'
                : 'Wykaz VAT nie daje osobnego pola grupy VAT. Ewentualna sugestia wynika wyłącznie z nazwy podmiotu.',
        ];
    }

    private function guessVatGroupMember(string $name): bool
    {
        $normalized = mb_strtolower($name);

        return str_contains($normalized, 'grupa vat') || str_contains($normalized, 'gv');
    }
}
