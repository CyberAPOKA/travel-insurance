<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-01');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validQuotePayload(array $overrides = []): array
    {
        return array_merge([
            'destination' => 'EUROPE',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'travelers' => [
                [
                    'name' => 'Ana',
                    'birth_date' => '1990-03-15',
                    'add_ons' => ['LUGGAGE', 'ADVENTURE_SPORTS'],
                ],
                [
                    'name' => 'John',
                    'birth_date' => '1948-11-02',
                    'add_ons' => ['ADVENTURE_SPORTS', 'LUGGAGE'],
                ],
            ],
        ], $overrides);
    }

    #[Test]
    public function it_requires_authentication_to_create_quotes(): void
    {
        $response = $this->postJson('/api/quotes', $this->validQuotePayload());

        $response->assertUnauthorized();
    }

    #[Test]
    public function it_creates_and_persists_a_quote_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/quotes', $this->validQuotePayload());

        $response->assertCreated()
            ->assertJsonPath('charged_days', 11)
            ->assertJsonPath('final_total', 852.5)
            ->assertJsonPath('travelers.0.name', 'Ana')
            ->assertJsonPath('travelers.1.applied_add_ons', ['LUGGAGE']);

        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseHas('quotes', [
            'user_id' => $user->id,
            'destination' => 'EUROPE',
            'final_total' => 852.5,
        ]);
        $this->assertDatabaseCount('quote_travelers', 2);
    }

    #[Test]
    public function it_validates_quote_input(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/quotes', $this->validQuotePayload([
            'destination' => 'INVALID',
            'end_date' => '2026-07-01',
            'travelers' => [],
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['destination', 'end_date', 'travelers']);
    }

    #[Test]
    public function it_lists_quotes_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'NATIONAL',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
            'charged_days' => 5,
            'group_discount_percentage' => 0,
            'final_total' => 50,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        Quote::query()->create([
            'user_id' => $otherUser->id,
            'destination' => 'EUROPE',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'charged_days' => 11,
            'group_discount_percentage' => 0,
            'final_total' => 100,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        $response = $this->getJson('/api/quotes');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.destination', 'NATIONAL')
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'source'],
            ])
            ->assertJsonPath('meta.source', 'database');
    }

    #[Test]
    public function it_returns_cache_source_on_repeated_list_requests(): void
    {
        config([
            'quotes.list_cache.enabled' => true,
            'quotes.list_cache.ttl_seconds' => 3600,
            'cache.default' => 'array',
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'NATIONAL',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
            'charged_days' => 5,
            'group_discount_percentage' => 0,
            'final_total' => 50,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        $this->getJson('/api/quotes')
            ->assertOk()
            ->assertJsonPath('meta.source', 'database');

        $this->getJson('/api/quotes')
            ->assertOk()
            ->assertJsonPath('meta.source', 'cache');
    }

    #[Test]
    public function it_paginates_quote_results(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        foreach (range(1, 20) as $index) {
            Quote::query()->create([
                'user_id' => $user->id,
                'destination' => 'NATIONAL',
                'start_date' => '2026-07-10',
                'end_date' => '2026-07-14',
                'charged_days' => 5,
                'group_discount_percentage' => 0,
                'final_total' => $index,
                'warnings' => [],
                'calculation_breakdown' => null,
            ]);
        }

        $response = $this->getJson('/api/quotes?per_page=10&page=2');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 20);
    }

    #[Test]
    public function it_shows_a_quote_only_for_its_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $quote = Quote::query()->create([
            'user_id' => $owner->id,
            'destination' => 'NATIONAL',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
            'charged_days' => 5,
            'group_discount_percentage' => 0,
            'final_total' => 50,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        Sanctum::actingAs($intruder);
        $this->getJson("/api/quotes/{$quote->id}")->assertForbidden();

        Sanctum::actingAs($owner);
        $this->getJson("/api/quotes/{$quote->id}")
            ->assertOk()
            ->assertJsonPath('id', $quote->id);
    }

    #[Test]
    public function it_updates_an_existing_quote_instead_of_creating_a_duplicate(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/quotes', $this->validQuotePayload([
            'travelers' => [
                [
                    'name' => 'Ana',
                    'birth_date' => '1990-03-15',
                    'add_ons' => [],
                ],
            ],
        ]));

        $quoteId = $createResponse->json('id');

        $updateResponse = $this->putJson("/api/quotes/{$quoteId}", $this->validQuotePayload([
            'destination' => 'NATIONAL',
            'travelers' => [
                [
                    'name' => 'Ana',
                    'birth_date' => '1990-03-15',
                    'add_ons' => ['LUGGAGE'],
                ],
            ],
        ]));

        $updateResponse->assertOk()
            ->assertJsonPath('destination', 'NATIONAL')
            ->assertJsonPath('travelers.0.applied_add_ons', ['LUGGAGE']);

        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseHas('quotes', [
            'id' => $quoteId,
            'destination' => 'NATIONAL',
        ]);
    }
}
