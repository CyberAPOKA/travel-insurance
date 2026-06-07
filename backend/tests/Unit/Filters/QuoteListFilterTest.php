<?php

namespace Tests\Unit\Filters;

use App\Filters\Quotes\QuoteListFilter;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteListFilterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_filters_quotes_by_destination_and_global_search(): void
    {
        $user = User::factory()->create();

        $nationalQuote = Quote::query()->create([
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

        $nationalQuote->travelers()->create([
            'user_id' => $user->id,
            'name' => 'Alice',
            'birth_date' => '1990-03-15',
            'add_ons' => [],
            'age' => 36,
            'subtotal' => 50,
            'applied_add_ons' => [],
        ]);

        Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'EUROPE',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'charged_days' => 11,
            'group_discount_percentage' => 0,
            'final_total' => 100,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        $filter = app(QuoteListFilter::class);

        $results = $filter
            ->apply(Quote::query()->forUser($user->id), [
                'destination' => [
                    'value' => 'NATIONAL',
                    'matchMode' => 'equals',
                ],
                'global' => [
                    'value' => 'Alice',
                    'matchMode' => 'contains',
                ],
            ])
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame($nationalQuote->id, $results->first()->id);
    }
}
