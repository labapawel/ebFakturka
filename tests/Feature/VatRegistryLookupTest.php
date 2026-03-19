<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VatRegistryLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_check_vat_registry_for_contractor(): void
    {
        Http::fake([
            'https://wl-api.mf.gov.pl/api/search/nip/*' => Http::response([
                'result' => [
                    'subject' => [
                        'name' => 'Przykładowa Grupa VAT Sp. z o.o.',
                        'nip' => '1234567890',
                        'statusVat' => 'Czynny',
                        'regon' => '123456789',
                        'workingAddress' => 'Testowa 1, 00-001 Warszawa',
                    ],
                    'requestId' => 'test-request-id',
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contractors.check_vat_registry'), [
            'nip' => '123-456-78-90',
        ]);

        $response->assertOk()
            ->assertJson([
                'nip' => '1234567890',
                'status_vat' => 'Czynny',
                'is_vat_group_member_guess' => true,
            ]);
    }
}
