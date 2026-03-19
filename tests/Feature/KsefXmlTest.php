<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class KsefXmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_ksef_xml()
    {
        Config::set('company.bank_name', 'mBank');
        Config::set('company.bank_account', '06 1140 2004 0000 0000 0000 0000');
        Config::set('company.factor_bank_name', 'Factor Bank');
        Config::set('company.factor_bank_account', 'PL12114020040000000000000001');

        $user = User::factory()->create();
        $this->actingAs($user);

        $vatRate = VatRate::create(['name' => '23%', 'rate' => 0.23, 'is_active' => true]);
        $currency = Currency::create(['code' => 'PLN', 'name' => 'Polski Złoty', 'exchange_rate' => 1.0, 'is_default' => true]);
        $contractor = Contractor::create([
            'name' => 'Test Client',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
            'is_jst' => true,
            'is_vat_group_member' => false,
            'ksef_legal_name' => 'Miasto Bielsko-Biala',
            'ksef_legal_street' => 'pl. Ratuszowy',
            'ksef_legal_building' => '1',
            'ksef_legal_postal_code' => '43-300',
            'ksef_legal_city' => 'Bielsko-Biala',
            'ksef_correspondence_name' => 'MIEJSKIE SCHRONISKO DLA BEZDOMNYCH ZWIERZAT',
            'ksef_correspondence_street' => 'ul. Reksia',
            'ksef_correspondence_building' => '48',
            'ksef_correspondence_postal_code' => '43-300',
            'ksef_correspondence_city' => 'BIELSKO-BIALA',
            'ksef_customer_number' => '1520062',
        ]);
        
        $invoice = Invoice::create([
            'number' => 'FV/2026/02/XML',
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_method' => 'Przelew',
            'contractor_id' => $contractor->id,
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'net_total' => 100.00,
            'vat_total' => 23.00,
            'gross_total' => 123.00,
            'status' => 'issued',
        ]);

        $invoice->items()->create([
            'name' => 'Test Item',
            'quantity' => 1,
            'unit' => 'szt.',
            'net_price' => 100.00,
            'vat_rate' => 0.23,
            'vat_amount' => 23.00,
            'gross_amount' => 123.00,
        ]);

        $response = $this->get(route('invoices.xml', $invoice));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<KodFormularza kodSystemowy="FA (3)" wersjaSchemy="1-0E">FA</KodFormularza>', $content);
        $this->assertStringContainsString('<NIP>1234567890</NIP>', $content);
        $this->assertStringContainsString('<Nazwa>Miasto Bielsko-Biala</Nazwa>', $content);
        $this->assertStringContainsString('<AdresKoresp><KodKraju>PL</KodKraju><AdresL1>MIEJSKIE SCHRONISKO DLA BEZDOMNYCH ZWIERZAT ul. Reksia 48 43-300 BIELSKO-BIALA</AdresL1></AdresKoresp>', $content);
        $this->assertStringContainsString('<NrKlienta>1520062</NrKlienta>', $content);
        $this->assertStringContainsString('<JST>1</JST>', $content);
        $this->assertStringContainsString('<GV>2</GV>', $content);
        $this->assertStringContainsString('<P_15>123.00</P_15>', $content);
        $this->assertStringContainsString('<Platnosc><TerminPlatnosci><Termin>', $content);
        $this->assertStringContainsString('<FormaPlatnosci>6</FormaPlatnosci>', $content);
        $this->assertStringContainsString('<RachunekBankowy><NrRB>06114020040000000000000000</NrRB><NazwaBanku>mBank</NazwaBanku></RachunekBankowy>', $content);
        $this->assertStringContainsString('<RachunekBankowyFaktora><NrRB>PL12114020040000000000000001</NrRB><NazwaBanku>Factor Bank</NazwaBanku></RachunekBankowyFaktora>', $content);
    }

    public function test_can_generate_ksef_xml_with_vat_exemption_reason_field(): void
    {
        Config::set('vat.mode', 'exempt');
        Config::set('vat.exemption_reason_type', 'P_19C');
        Config::set('vat.exemption_reason_text', 'Inna podstawa prawna zwolnienia wskazana przez podatnika');

        $user = User::factory()->create();
        $this->actingAs($user);

        Currency::create(['code' => 'PLN', 'name' => 'Polski Zloty', 'exchange_rate' => 1.0, 'is_default' => true]);
        $contractor = Contractor::create([
            'name' => 'Klient Zwolniony',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
            'is_jst' => false,
            'is_vat_group_member' => false,
        ]);

        $invoice = Invoice::create([
            'number' => 'FV/2026/03/ZWXML',
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_method' => 'Przelew',
            'contractor_id' => $contractor->id,
            'user_id' => $user->id,
            'currency_id' => Currency::query()->value('id'),
            'net_total' => 100.00,
            'vat_total' => 0.00,
            'gross_total' => 100.00,
            'status' => 'issued',
        ]);

        $invoice->items()->create([
            'name' => 'Usluga zwolniona',
            'quantity' => 1,
            'unit' => 'szt.',
            'net_price' => 100.00,
            'vat_rate' => 0.00,
            'vat_amount' => 0.00,
            'gross_amount' => 100.00,
        ]);

        $response = $this->get(route('invoices.xml', $invoice));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<Zwolnienie><P_19>1</P_19><P_19C>Inna podstawa prawna zwolnienia wskazana przez podatnika</P_19C></Zwolnienie>', $content);
        $this->assertStringNotContainsString('<P_19A>', $content);
    }
}
