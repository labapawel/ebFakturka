
# ebFakturka

Aplikacja do fakturowania oparta na Laravel 12 i TailwindCSS 4.0.

## Wymagania

- PHP ^8.2
- Composer
- Node.js & NPM
- MySQL

## Instalacja

1. Sklonuj repozytorium
2. Zainstaluj zależności PHP:
   ```bash
   composer install
   ```
3. Zainstaluj zależności JS:
   ```bash
   npm install
   npm run build
   ```
4. Skopiuj `.env.example` do `.env`:
   ```bash
   cp .env.example .env
   ```
5. Wygeneruj klucz aplikacji:
   ```bash
   php artisan key:generate
   ```
6. Skonfiguruj bazę danych w `.env`:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ebFakturka
   DB_USERNAME=root
   DB_PASSWORD=
   ```
7. Utwórz bazę danych `ebFakturka` w Twoim serwerze MySQL.
8. Uruchom migracje:
   ```bash
   php artisan migrate --seed
   ```

## Konfiguracja
### Dane Firmy (Wystawcy)
Uzupełnij dane swojej firmy w pliku `.env`:
```ini
COMPANY_NAME="Moja Firma Sp. z o.o."
COMPANY_NIP="1234567890"
COMPANY_STREET="Biznesowa"
COMPANY_BUILDING_NUMBER="1"
COMPANY_POSTAL_CODE="00-000"
COMPANY_CITY="Warszawa"
COMPANY_BANK_NAME="Nazwa Banku"
COMPANY_BANK_ACCOUNT="00 0000 0000 0000 0000 0000 0000"
```

### Zwolnienie z VAT
Aby włączyć tryb zwolnienia z VAT (ukrywanie stawek i kwot VAT), ustaw w `.env`:
```ini
VAT_EXEMPT=true
```
Domyślnie `false` (płatnik VAT).

### Format Numeracji Faktur
Możesz dostosować format numeru faktury w `.env`. System automatycznie wykrywa zmianę okresu i resetuje numerację.
Dostępne zmienne: `{Y}` (rok 2026), `{y}` (rok 26), `{m}` (miesiąc), `{d}` (dzień), `{nr}` (kolejny numer).

Przykłady:
- `FV/{Y}/{m}/{nr}` -> `FV/2026/02/001` (Domyślny)
- `F/{Y}/{nr}` -> `F/2026/001` (Numeracja roczna)
- `INV-{m}-{d}-{nr}` -> `INV-02-08-001` (Numeracja dzienna)

```ini
INVOICE_NUMBER_FORMAT="F/{Y}/{nr}"
INVOICE_NUMBER_PADDING=4  # Ilość cyfr numeru (np. 0001)
```

## Uruchamianie

W dwóch osobnych terminalach:
```bash
php artisan serve
npm run dev
```

## Testy

Testy uruchamiane są na osobnej bazie danych. Użyj przygotowanego skryptu:

```bash
./run_tests.sh
```

Wymaga utworzenia bazy danych testowej (np. `ebfakturka_test`) i skonfigurowania jej w `phpunit.xml` lub `.env.testing`.

## Funkcjonalności

- Zarządzanie kontrahentami (pobieranie danych z GUS)
- Zarządzanie produktami, stawkami VAT, walutami
- Wystawianie faktur sprzedaży (PDF)
- Integracja z KSeF (wysyłka i pobieranie)
- Zarządzanie użytkownikami (tylko administrator)

## Dokumentacja Modułów

Szczegółowa dokumentacja modułów znajduje się w katalogu `prompts/`.
