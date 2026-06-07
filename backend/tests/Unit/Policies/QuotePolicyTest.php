<?php

namespace Tests\Unit\Policies;

use App\Models\Quote;
use App\Models\User;
use App\Policies\QuotePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuotePolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_the_owner_to_view_and_update_a_quote(): void
    {
        $owner = User::factory()->create();
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

        $policy = new QuotePolicy;

        $this->assertTrue($policy->view($owner, $quote));
        $this->assertTrue($policy->update($owner, $quote));
    }

    #[Test]
    public function it_denies_other_users_from_viewing_or_updating_a_quote(): void
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

        $policy = new QuotePolicy;

        $this->assertFalse($policy->view($intruder, $quote));
        $this->assertFalse($policy->update($intruder, $quote));
    }
}
