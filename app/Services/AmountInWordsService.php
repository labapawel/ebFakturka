<?php

namespace App\Services;

class AmountInWordsService
{
    private $moze_jednosci = [
        'zero', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć',
        'dziesięć', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 'piętnaście', 'szesnaście', 'siedemnaście', 'osiemnaście', 'dziewiętnaście'
    ];

    private $moze_dziesiatki = [
        '', '', 'dwadzieścia', 'trzydzieści', 'czterdzieści', 'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt'
    ];

    private $moze_setki = [
        '', 'sto', 'dwieście', 'trzysta', 'czterysta', 'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset'
    ];

    private $moze_tysiace = [
        ['tysiąc', 'tysiące', 'tysięcy'],
        ['milion', 'miliony', 'milionów'],
        ['miliard', 'miliardy', 'miliardów'],
    ];

    public function kwotaSlownie(float $kwota, string $waluta = 'PLN'): string
    {
        $kwota = round($kwota, 2);
        $zlotowki = floor($kwota);
        $grosze = round(($kwota - $zlotowki) * 100);

        $slownie = $this->liczbaSlownie((int)$zlotowki);
        
        // Obsługa nazwy waluty
        $walutaOpis = match($waluta) {
            'PLN' => $this->odmiana($zlotowki, ['złoty', 'złote', 'złotych']),
            'EUR' => $this->odmiana($zlotowki, ['euro', 'euro', 'euro']),
            'USD' => $this->odmiana($zlotowki, ['dolar', 'dolary', 'dolarów']),
            default => $waluta,
        };

        $groszeOpis = match($waluta) {
            'PLN' => 'gr', // Skrót jest najbardziej czytelny
            'EUR' => 'cent',
            'USD' => 'cent',
            default => 'setnych',
        };

        return trim($slownie) . ' ' . $walutaOpis . ' ' . $grosze . '/100';
    }

    private function liczbaSlownie(int $liczba): string
    {
        if ($liczba == 0) return 'zero';

        $wynik = '';
        $licznik_grup = 0;

        while ($liczba > 0) {
            $grupa = $liczba % 1000;
            $liczba = floor($liczba / 1000);

            if ($grupa > 0) {
                $opis_grupy = '';
                $setki = floor($grupa / 100);
                $dziesiatki_jednosci = $grupa % 100;

                if ($setki > 0) {
                    $opis_grupy .= $this->moze_setki[$setki] . ' ';
                }

                if ($dziesiatki_jednosci > 0) {
                    if ($dziesiatki_jednosci < 20) {
                        $opis_grupy .= $this->moze_jednosci[$dziesiatki_jednosci] . ' ';
                    } else {
                        $dziesiatki = floor($dziesiatki_jednosci / 10);
                        $jednosci = $dziesiatki_jednosci % 10;
                        $opis_grupy .= $this->moze_dziesiatki[$dziesiatki] . ' ';
                        if ($jednosci > 0) {
                            $opis_grupy .= $this->moze_jednosci[$jednosci] . ' ';
                        }
                    }
                }

                if ($licznik_grup > 0) {
                    $opis_grupy .= $this->odmiana($grupa, $this->moze_tysiace[$licznik_grup - 1]) . ' ';
                }

                $wynik = $opis_grupy . $wynik;
            } else {
                // Grupa to 000, ale jeśli są wyższe grupy, to nic nie dodajemy, po prostu licznik leci dalej
            }
            
            $licznik_grup++;
        }

        return trim($wynik);
    }

    private function odmiana(int $ilosc, array $odmiany): string
    {
        if ($ilosc == 1) return $odmiany[0];
        
        $reszta10 = $ilosc % 10;
        $reszta100 = $ilosc % 100;

        if ($reszta10 >= 2 && $reszta10 <= 4 && ($reszta100 < 10 || $reszta100 >= 20)) {
            return $odmiany[1];
        }

        return $odmiany[2];
    }
}
