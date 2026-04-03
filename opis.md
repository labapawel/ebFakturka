# ebFakturka - System do wystawiania faktur

## Opis programu
Program **ebFakturka** to nowoczesna aplikacja webowa zbudowana w oparciu o framework **Laravel**, służąca do zarządzania procesem fakturowania w firmie. System umożliwia ewidencję kontrahentów, produktów oraz wystawianie różnego rodzaju dokumentów sprzedażowych i zakupowych.

## Do czego służy?
- **Wystawianie faktur**: Tworzenie profesjonalnych dokumentów VAT, faktur zwolnionych oraz korekt.
- **Zarządzanie kontrahentami**: Baza dostawców i odbiorców z obsługą specyficznych typów podmiotów, w tym Jednostek Samorządu Terytorialnego (JST).
- **Obsługa JST (Podwójna Tożsamość)**: Specjalna funkcjonalność pozwalająca na rozróżnienie Nabywcy (np. Gmina) od Odbiorcy (np. konkretna Szkoła lub Przedszkole), co jest kluczowe dla poprawnego raportowania do KSeF.
- **Magazyn produktów**: Zarządzanie listą towarów i usług z predefiniowanymi stawkami VAT i cenami.
- **Snapshotting danych**: System zapisuje stan danych kontrahenta i sprzedawcy w momencie wystawienia faktury, co gwarantuje niezmienność historycznych dokumentów nawet po aktualizacji kartoteki kontrahenta.

## Z czym współpracuje?
- **KSeF (Krajowy System e-Faktur)**: Pełna integracja z rządową platformą. Program umożliwia generating plików XML zgodnych ze strukturą logiczną FA(3), wysyłanie faktur do systemu KSeF oraz odbieranie i importowanie faktur kosztowych bezpośrednio z platformy.
- **Baza danych SQL**: Wykorzystuje relacyjną bazę danych do bezpiecznego przechowywania informacji.
- **Tailwind CSS & Alpine.js**: Zapewnia nowoczesny, responsywny i interaktywny interfejs użytkownika.
