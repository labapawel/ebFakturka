<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GusImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_gus_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $gusService = Mockery::mock(GusService::class);
        $gusService->shouldReceive('fetchByNip')
            ->once()
            ->with('1234567890')
            ->andReturn([
                'name' => 'ACME Sp. z o.o.',
                'nip' => '1234567890',
                'address_street' => 'Prosta',
                'address_building' => '10',
                'address_apartment' => '5',
                'postal_code' => '00-001',
                'city' => 'Warszawa',
                'gus_data' => [
                    'podstawowe' => ['Nazwa' => 'ACME Sp. z o.o.'],
                    'pelny' => ['nazwa' => 'ACME Sp. z o.o.'],
                ],
            ]);

        $this->app->instance(GusService::class, $gusService);

        $response = $this->postJson(route('contractors.fetch_gus'), [
            'nip' => '1234567890',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'ACME Sp. z o.o.',
                'city' => 'Warszawa',
            ]);
    }

    public function test_gus_fetch_validation_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('contractors.fetch_gus'), [
            'nip' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nip']);
    }

    public function test_gus_fetch_not_found(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $gusService = Mockery::mock(GusService::class);
        $gusService->shouldReceive('fetchByNip')
            ->once()
            ->with('0000000000')
            ->andThrow(new \RuntimeException('Nie znaleziono podmiotu w GUS dla podanego NIP.'));

        $this->app->instance(GusService::class, $gusService);

        $response = $this->postJson(route('contractors.fetch_gus'), [
            'nip' => '0000000000',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Nie znaleziono podmiotu w GUS dla podanego NIP.']);
    }
}
