<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\VatRate;
use App\Services\KsefClient;
use App\Services\KsefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_invoice_and_download_pdf()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $vatRate = VatRate::create(['name' => '23%', 'rate' => 0.23, 'is_active' => true]);
        $currency = Currency::create(['code' => 'PLN', 'name' => 'Polski Złoty', 'exchange_rate' => 1.0, 'is_default' => true]);
        $contractor = Contractor::create(['name' => 'Test Client', 'nip' => '1234567890', 'address_street' => 'Testowa 1', 'city' => 'Testowo']);
        $product = Product::create(['name' => 'Test Product', 'unit' => 'szt.', 'net_price' => 100.00, 'vat_rate_id' => $vatRate->id]);

        $response = $this->post(route('invoices.store'), [
            'number' => 'FV/2026/02/001',
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'payment_method' => 'Przelew',
            'contractor_id' => $contractor->id,
            'currency_id' => $currency->id,
            'items' => [
                [
                    'name' => 'Test Product',
                    'quantity' => 2,
                    'unit' => 'szt.',
                    'net_price' => 100.00,
                    'vat_rate' => 0.23
                ]
            ]
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', ['number' => 'FV/2026/02/001', 'gross_total' => 246.00]); // 200 netto + 46 vat
        $this->assertDatabaseHas('invoice_items', ['name' => 'Test Product', 'quantity' => 2, 'gross_amount' => 246.00]);

        $invoice = \App\Models\Invoice::first();

        $pdfResponse = $this->get(route('invoices.pdf', $invoice));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_marks_invoice_as_sent_only_after_successful_ksef_processing(): void
    {
        $invoice = $this->createInvoiceForKsef();

        $ksefService = Mockery::mock(KsefService::class);
        $ksefService->shouldReceive('validateForSending')->once()->andReturn([]);
        $ksefService->shouldReceive('generateXml')->once()->andReturn('<Faktura />');
        $this->app->instance(KsefService::class, $ksefService);

        $ksefClient = Mockery::mock(KsefClient::class);
        $ksefClient->shouldReceive('sendInvoice')->once()->andReturn([
            'sessionReferenceNumber' => '20260318-SO-SESSION-01',
            'invoiceReferenceNumber' => '20260318-EE-INVOICE-01',
        ]);
        $ksefClient->shouldReceive('waitForSessionInvoiceStatus')->once()->andReturn([
            'ksefNumber' => '3421285036-20260318-ABCDEF123456-01',
            'status' => [
                'code' => 200,
                'description' => 'Sukces',
                'details' => [],
            ],
        ]);
        $this->app->instance(KsefClient::class, $ksefClient);

        $response = $this->post(route('invoices.send_to_ksef', $invoice));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'ksef_status' => 'sent',
            'ksef_number' => '3421285036-20260318-ABCDEF123456-01',
        ]);
    }

    public function test_marks_invoice_as_error_when_ksef_rejects_document(): void
    {
        $invoice = $this->createInvoiceForKsef();

        $ksefService = Mockery::mock(KsefService::class);
        $ksefService->shouldReceive('validateForSending')->once()->andReturn([]);
        $ksefService->shouldReceive('generateXml')->once()->andReturn('<Faktura />');
        $this->app->instance(KsefService::class, $ksefService);

        $ksefClient = Mockery::mock(KsefClient::class);
        $ksefClient->shouldReceive('sendInvoice')->once()->andReturn([
            'sessionReferenceNumber' => '20260318-SO-SESSION-02',
            'invoiceReferenceNumber' => '20260318-EE-INVOICE-02',
        ]);
        $ksefClient->shouldReceive('waitForSessionInvoiceStatus')->once()->andReturn([
            'status' => [
                'code' => 450,
                'description' => 'Błąd weryfikacji semantyki dokumentu faktury',
                'details' => ['Brak wymaganej sekcji.'],
            ],
        ]);
        $this->app->instance(KsefClient::class, $ksefClient);

        $response = $this->post(route('invoices.send_to_ksef', $invoice));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'ksef_status' => 'error',
            'ksef_number' => null,
        ]);
    }

    public function test_marks_invoice_as_pending_when_ksef_is_still_processing(): void
    {
        $invoice = $this->createInvoiceForKsef();

        $ksefService = Mockery::mock(KsefService::class);
        $ksefService->shouldReceive('validateForSending')->once()->andReturn([]);
        $ksefService->shouldReceive('generateXml')->once()->andReturn('<Faktura />');
        $this->app->instance(KsefService::class, $ksefService);

        $ksefClient = Mockery::mock(KsefClient::class);
        $ksefClient->shouldReceive('sendInvoice')->once()->andReturn([
            'sessionReferenceNumber' => '20260318-SO-SESSION-03',
            'invoiceReferenceNumber' => '20260318-EE-INVOICE-03',
        ]);
        $ksefClient->shouldReceive('waitForSessionInvoiceStatus')->once()->andReturn([
            'status' => [
                'code' => 150,
                'description' => 'Trwa przetwarzanie',
                'details' => [],
            ],
        ]);
        $this->app->instance(KsefClient::class, $ksefClient);

        $response = $this->post(route('invoices.send_to_ksef', $invoice));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'ksef_status' => 'pending',
            'ksef_number' => null,
        ]);
    }

    private function createInvoiceForKsef(): Invoice
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create(['code' => 'PLN', 'name' => 'Polski Zloty', 'exchange_rate' => 1.0, 'is_default' => true]);
        $contractor = Contractor::create([
            'name' => 'Test Client',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
        ]);

        $invoice = Invoice::create([
            'number' => 'FV/2026/03/' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
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
            'name' => 'Test Product',
            'quantity' => 1,
            'unit' => 'szt.',
            'net_price' => 100.00,
            'vat_rate' => 0.23,
            'vat_amount' => 23.00,
            'gross_amount' => 123.00,
        ]);

        return $invoice;
    }
}
