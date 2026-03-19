<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoices_are_sorted_by_number_desc_by_default(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create([
            'code' => 'PLN',
            'name' => 'Polski Zloty',
            'exchange_rate' => 1,
            'is_default' => true,
        ]);

        $contractor = Contractor::create([
            'name' => 'Kontrahent Test',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
        ]);

        $this->createInvoice($user->id, $contractor->id, $currency->id, 'F/2026/001');
        $this->createInvoice($user->id, $contractor->id, $currency->id, 'F/2026/002');

        $response = $this->get(route('invoices.index'));
        $response->assertOk();

        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'F/2026/002') < strpos($content, 'F/2026/001'));
    }

    public function test_invoices_can_be_filtered_by_search_phrase(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::create([
            'code' => 'PLN',
            'name' => 'Polski Zloty',
            'exchange_rate' => 1,
            'is_default' => true,
        ]);

        $contractor = Contractor::create([
            'name' => 'Kontrahent Test',
            'nip' => '1234567890',
            'address_street' => 'Testowa 1',
            'city' => 'Testowo',
        ]);

        $this->createInvoice($user->id, $contractor->id, $currency->id, 'F/2026/001');
        $this->createInvoice($user->id, $contractor->id, $currency->id, 'SPECIAL/2026/099');

        $response = $this->get(route('invoices.index', ['q' => 'SPECIAL']));
        $response->assertOk();
        $response->assertSee('SPECIAL/2026/099');
        $response->assertDontSee('F/2026/001');
    }

    public function test_currencies_are_paginated_and_second_page_is_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (range(1, 11) as $index) {
            Currency::create([
                'code' => sprintf('X%02d', $index),
                'name' => 'Waluta ' . $index,
                'exchange_rate' => 1 + $index,
                'is_default' => $index === 1,
            ]);
        }

        $firstPage = $this->get(route('currencies.index'));
        $firstPage->assertOk();
        $firstPage->assertSee('Waluta 1');
        $firstPage->assertDontSee('Waluta 11');

        $secondPage = $this->get(route('currencies.index', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('Waluta 11');
    }

    private function createInvoice(int $userId, int $contractorId, int $currencyId, string $number): void
    {
        $invoice = Invoice::create([
            'number' => $number,
            'issue_date' => now()->toDateString(),
            'sale_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'payment_method' => 'Przelew',
            'contractor_id' => $contractorId,
            'user_id' => $userId,
            'currency_id' => $currencyId,
            'net_total' => 100,
            'vat_total' => 23,
            'gross_total' => 123,
            'status' => 'issued',
        ]);

        $invoice->items()->create([
            'name' => 'Pozycja testowa',
            'quantity' => 1,
            'unit' => 'szt.',
            'net_price' => 100,
            'vat_rate' => 0.23,
            'vat_amount' => 23,
            'gross_amount' => 123,
        ]);
    }
}
