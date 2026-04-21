# Mapa Aplikacji — ebFakturka

## Technologie

| Warstwa | Technologia |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Baza danych | MySQL 5.7+ |
| Frontend Web | Blade + Tailwind CSS 4.0 + Alpine.js 3 |
| Build | Vite 7.0 |
| Frontend Mobile | React Native 0.81 + Expo 54 + TypeScript |
| API Auth | Laravel Sanctum |
| PDF | barryvdh/laravel-dompdf |
| Integracje | KSeF API, GUS API |

---

## Moduły Aplikacji

```
ebFakturka
│
├── AUTH
│   ├── Logowanie / wylogowanie
│   ├── Rejestracja (tylko admin może dodawać)
│   ├── Reset hasła (e-mail)
│   └── Weryfikacja e-mail
│
├── DASHBOARD
│   ├── Statystyki faktur (wystawione, zapłacone, zaległe)
│   ├── Ostatnie faktury
│   └── Szybkie akcje
│
├── FAKTURY SPRZEDAŻOWE (/invoices)
│   ├── Lista z filtrowaniem / sortowaniem / paginacją
│   ├── Tworzenie faktury
│   │   ├── Wybór kontrahenta (+ inline-search)
│   │   ├── Dynamiczna tabela pozycji
│   │   ├── Przeliczenia netto/VAT/brutto
│   │   ├── Obsługa walut (PLN, EUR, USD...)
│   │   └── Automatyczna numeracja (konfigurowalna)
│   ├── Edycja faktury
│   ├── Podgląd / druk (PDF)
│   ├── Eksport XML (KSeF format FA(3) / 1-0E)
│   ├── Wysyłka do KSeF
│   │   ├── Walidacja przed wysyłką
│   │   ├── Szyfrowanie sesji (RSA/AES)
│   │   └── Tracking statusu (sent / pending / error)
│   ├── Wysyłka e-mail (PDF załącznik)
│   ├── Korekta faktury
│   └── Snapshot danych sprzedawcy i kupującego
│
├── FAKTURY ZAKUPOWE (/purchase-invoices)
│   ├── Pobieranie listy z KSeF
│   ├── Pobieranie i odszyfrowanie XML
│   ├── Zapis XML lokalnie (storage/app/ksef/invoices)
│   ├── Import danych (numer, daty, kwoty, waluta)
│   ├── Edycja i klasyfikacja
│   └── Podgląd / PDF
│
├── FAKTURY CYKLICZNE (/recurring_invoices)
│   ├── Definicja cyklu
│   │   ├── Częstotliwość: monthly / quarterly / yearly / custom
│   │   ├── Data start / data end / następna data wystawienia
│   │   └── Pozycje szablonu faktury
│   ├── Automatyczne generowanie (Scheduler — codziennie)
│   └── Historia wystawionych faktur
│
├── KONTRAHENCI (/contractors)
│   ├── Lista z filtrowaniem
│   ├── CRUD
│   ├── Pobieranie danych z GUS (po NIP/REGON)
│   ├── Sprawdzanie statusu VAT (CEIDG/VAT registry)
│   ├── Pola KSeF: adres prawny, korespondencyjny, numer klienta
│   ├── Flagi: JST, członek grupy VAT
│   └── Dane odbiorcy dostawy (recipient_*)
│
├── PRODUKTY (/products)
│   ├── Lista + CRUD
│   ├── Powiązanie ze stawką VAT
│   └── Domyślna jednostka miary
│
├── SŁOWNIKI
│   ├── Stawki VAT (/vat_rates) — 0%, 5%, 8%, 23%, ZW
│   └── Waluty (/currencies) — PLN, EUR, USD, GBP, CZK, HUF, SKK
│
├── USTAWIENIA (/settings)
│   ├── Dane firmy (z .env)
│   ├── Format numeracji faktur
│   ├── Konfiguracja KSeF
│   └── Ustawienia VAT (zwolnienie)
│
├── UŻYTKOWNICY (/users) [tylko admin]
│   ├── Lista + CRUD
│   ├── Role: admin | user
│   └── Granularne uprawnienia (JSON array)
│
├── PROFIL (/profile)
│   ├── Edycja danych użytkownika
│   ├── Zmiana hasła
│   └── Usunięcie konta
│
├── BACKUPY (/backups)
│   ├── Export bazy danych
│   └── Import bazy danych
│
└── API REST (Laravel Sanctum)
    ├── POST /api/login
    ├── POST /api/logout
    ├── GET  /api/invoices
    ├── GET  /api/invoices/{id}
    ├── GET  /api/purchase-invoices
    └── GET  /api/contractors
```

---

## Modele i Relacje

```
User
 ├── hasMany → Invoice (wystawione faktury)
 └── hasMany → RecurringInvoice

Contractor
 ├── hasMany → Invoice
 └── hasMany → RecurringInvoice

Invoice  ──── centralny model
 ├── belongsTo → User
 ├── belongsTo → Contractor
 ├── belongsTo → Currency
 ├── hasMany   → InvoiceItem
 ├── type: 'sales' | 'purchase'
 ├── status: draft | issued | saved | sent | paid | cancelled
 └── ksef_status: sent | fetched | pending | error

InvoiceItem
 ├── belongsTo → Invoice
 └── belongsTo → VatRate (opcjonalnie)

RecurringInvoice
 ├── belongsTo → User
 ├── belongsTo → Contractor
 ├── belongsTo → Currency
 └── hasMany   → RecurringInvoiceItem

InvoiceCounter
 └── Licznik numeracji per (type, rok, miesiąc)

Product
 └── belongsTo → VatRate

VatRate   — słownik stawek VAT
Currency  — słownik walut
```

---

## Integracje Zewnętrzne

### KSeF (Krajowy System e-Faktur)
```
KsefService (logika)
 ├── authenticate()         sesja z certyfikatem
 ├── sendInvoice()          wysyłka FA(3) XML
 ├── checkStatus()          status wysłanej faktury
 ├── getInvoices()          lista faktur zakupowych
 └── fetchInvoice()         pobranie + odszyfrowanie XML

KsefClient (HTTP)
 ├── Enkrypcja RSA/AES
 ├── Obsługa tokenów
 └── Deserializacja odpowiedzi XML
```

### GUS (Główny Urząd Statystyczny)
```
GusService
 ├── searchByNip(nip)       dane firmy po NIP
 └── normalizeNip(nip)      formatowanie NIP
```

### VatRegistryService
```
VatRegistryService
 └── checkVatStatus(nip)    status VAT firmy w CEIDG
```

---

## Aplikacja Mobilna (./mobi)

```
React Native (Expo) + TypeScript + NativeWind

Ekrany:
├── login.tsx              Logowanie
├── (app)/
│   ├── index.tsx          Dashboard
│   ├── invoices.tsx       Lista faktur sprzedażowych
│   ├── purchases.tsx      Lista faktur zakupowych
│   └── profile.tsx        Profil użytkownika

Warstwa danych:
├── AuthService.ts         Login / logout / token
├── InvoiceService.ts      Wywołania API
├── DatabaseService.ts     Cache SQLite (offline)
└── InvoiceRepository.ts   Data access layer

Stan globalny:
└── AuthContext.tsx        Token + dane użytkownika

Funkcje:
├── Logowanie → token Sanctum
├── Listy faktur (sprzedażowe / zakupowe)
├── Szczegóły faktury + pozycje
├── Pobieranie i udostępnianie PDF
└── Offline-first (SQLite cache)
```

---

## Scheduler (Cron)

```
app/Console/Commands/
├── ProcessRecurringInvoices.php  — codziennie, generuje faktury cykliczne
└── TestKsefConnection.php        — pomocniczy, test połączenia z KSeF
```

---

## Konfiguracja (.env)

```
# Dane firmy
COMPANY_NAME, COMPANY_NIP
COMPANY_STREET, BUILDING_NUMBER, POSTAL_CODE, CITY
COMPANY_BANK_NAME, COMPANY_BANK_ACCOUNT
COMPANY_FACTOR_BANK_NAME, COMPANY_FACTOR_BANK_ACCOUNT  (opcjonalnie)

# VAT
VAT_EXEMPT=false
VAT_EXEMPT_REASON_TYPE, VAT_EXEMPT_REASON_TEXT

# Numeracja faktur
INVOICE_NUMBER_FORMAT="FV/{Y}/{m}/{nr}"
INVOICE_NUMBER_PADDING=4

# KSeF
KSEF_URL, KSEF_NIP, KSEF_TOKEN, KSEF_PASS
KSEF_CERT, KSEF_PRIVATE_KEY

# Baza
DB_CONNECTION=mysql
DB_DATABASE=ebFakturka
```

---

## Środowiska

```
ebFakturka/          produkcja/dev (główny)
ebfakturka2/         instancja 2
ebfakturka3/         instancja 3
ebfakturka4/         instancja 4

sync-instances.sh    rsync → synchronizuje kod (chroni .env i storage/)
```

---

## Struktura katalogów (kluczowe)

```
ebFakturka/
├── app/
│   ├── Console/Commands/     schedulery
│   ├── Http/Controllers/     14 kontrolerów
│   ├── Http/Middleware/      SetLocale
│   ├── Models/               10 modeli
│   ├── Services/             KSeF, GUS, PDF, numeracja
│   └── View/Components/      AppLayout, GuestLayout
├── config/                   app, ksef, vat, company, ...
├── database/
│   ├── migrations/           24 migracje
│   └── seeders/              5 seederów
├── docs/                     dokumentacja projektu
├── ksef-docs/                dokumentacja API KSeF (25+ plików)
├── mobi/                     React Native app
├── prompts/                  specyfikacje modułów + przykłady KSeF
├── resources/views/          53 szablony Blade
├── routes/web.php            wszystkie trasy
├── storage/app/ksef/         pobrane XML z KSeF
└── tests/                    testy PHPUnit
```
