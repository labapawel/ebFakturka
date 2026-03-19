<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VatExemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_exempt_logic_in_pdf_and_xml(): void
    {
        Config::set('vat.mode', 'exempt');
        Config::set('vat.exemption_reason_type', 'P_19A');
        Config::set('vat.exemption_reason_text', 'Przepis ustawy albo aktu wydanego na podstawie ustawy, na podstawie ktorego podatnik stosuje zwolnienie od podatku');

        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create([
            'code' => 'PLN',
            'name' => 'Polski Zloty',
            'exchange_rate' => 1.0,
            'is_default' => true,
        ]);

        $contractor = Contractor::create([
            'name' => 'Test Client',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
        ]);

        $invoice = Invoice::create([
            'number' => 'FV/2026/02/ZW',
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_method' => 'Przelew',
            'contractor_id' => $contractor->id,
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'net_total' => 100.00,
            'vat_total' => 0.00,
            'gross_total' => 100.00,
            'status' => 'issued',
        ]);

        $invoice->items()->create([
            'name' => 'Test Item',
            'quantity' => 1,
            'unit' => 'szt.',
            'net_price' => 100.00,
            'vat_rate' => 0.00,
            'vat_amount' => 0.00,
            'gross_amount' => 100.00,
        ]);

        $responsePdf = $this->get(route('invoices.pdf', $invoice));
        $responsePdf->assertStatus(200);

        $responseXml = $this->get(route('invoices.xml', $invoice));
        $responseXml->assertStatus(200);

        $content = $responseXml->getContent();

        $this->assertStringContainsString('<P_13_7>100.00</P_13_7>', $content);
        $this->assertStringContainsString('<Zwolnienie><P_19>1</P_19><P_19A>Przepis ustawy albo aktu wydanego na podstawie ustawy, na podstawie ktorego podatnik stosuje zwolnienie od podatku</P_19A></Zwolnienie>', $content);
        $this->assertStringContainsString('<P_12>zw</P_12>', $content);
        $this->assertStringNotContainsString('<P_13_1>', $content);
        $this->assertStringNotContainsString('<P_14_1>', $content);
    }
}
