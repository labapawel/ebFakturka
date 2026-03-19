<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\KsefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseInvoiceKsefImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_purchase_invoice_from_ksef_xml_and_stores_original_xml(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Faktura xmlns="http://crd.gov.pl/wzor/2023/06/29/12648/">
    <Podmiot1>
        <DaneIdentyfikacyjne>
            <NIP>5250001001</NIP>
            <Nazwa>Dostawca Testowy Sp. z o.o.</Nazwa>
        </DaneIdentyfikacyjne>
        <Adres>
            <AdresPol>
                <Ulica>Testowa</Ulica>
                <NrDomu>12</NrDomu>
                <NrLokalu>4</NrLokalu>
                <Miejscowosc>Warszawa</Miejscowosc>
                <KodPocztowy>00-001</KodPocztowy>
            </AdresPol>
        </Adres>
    </Podmiot1>
    <Fa>
        <KodWaluty>PLN</KodWaluty>
        <P_1>2026-03-01</P_1>
        <P_2>FV/03/2026/15</P_2>
        <P_6>2026-03-01</P_6>
        <P_13_1>100.00</P_13_1>
        <P_14_1>23.00</P_14_1>
        <P_15>123.00</P_15>
        <FaWiersz>
            <P_7>Usługa abonamentowa</P_7>
            <P_8A>mies.</P_8A>
            <P_8B>1</P_8B>
            <P_9A>100.00</P_9A>
            <P_11>100.00</P_11>
            <P_12>23</P_12>
        </FaWiersz>
    </Fa>
</Faktura>
XML;

        $service = app(KsefService::class);
        $invoice = $service->importInvoiceFromKsef($xml, '1111111111-20260301-ABCDEF000000-01', $user->id);

        $this->assertSame('purchase', $invoice->type);
        $this->assertSame('1111111111-20260301-ABCDEF000000-01', $invoice->ksef_number);
        $this->assertSame('fetched', $invoice->ksef_status);
        $this->assertSame('Dostawca Testowy Sp. z o.o.', $invoice->contractor->name);
        $this->assertSame('PLN', $invoice->currency->code);
        $this->assertCount(1, $invoice->items);
        $this->assertSame('Usługa abonamentowa', $invoice->items->first()->name);
        $this->assertEquals(123.00, (float) $invoice->gross_total);

        Storage::disk('local')->assertExists('ksef/invoices/1111111111-20260301-ABCDEF000000-01.xml');
    }
}
