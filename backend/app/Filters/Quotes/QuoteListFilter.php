<?php

namespace App\Filters\Quotes;

use App\Filters\DataTableFilterService;
use Illuminate\Database\Eloquent\Builder;

class QuoteListFilter
{
    /** @var array<string, string> */
    private const FIELD_MAP = [
        'destination' => 'destination',
        'start_date' => 'start_date',
        'end_date' => 'end_date',
        'charged_days' => 'charged_days',
        'group_discount_percentage' => 'group_discount_percentage',
        'final_total' => 'final_total',
    ];

    public function __construct(
        private readonly DataTableFilterService $filterService,
    ) {}

    /**
     * @param  Builder<\App\Models\Quote>  $query
     * @param  array<string, mixed>  $filtersMeta
     * @return Builder<\App\Models\Quote>
     */
    public function apply(Builder $query, array $filtersMeta): Builder
    {
        $travelersMeta = $filtersMeta['travelers_count'] ?? null;
        unset($filtersMeta['travelers_count']);

        $this->filterService->apply($query, $filtersMeta, self::FIELD_MAP, []);
        $this->applyGlobalSearch($query, $filtersMeta);
        $this->applyTravelersCountFilter($query, $travelersMeta);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filtersMeta
     */
    private function applyGlobalSearch(Builder $query, array $filtersMeta): void
    {
        $global = $filtersMeta['global']['value'] ?? null;

        if (is_string($global)) {
            $global = trim($global);
        }

        if ($global === null || $global === '') {
            return;
        }

        $likeTerm = '%'.$global.'%';

        $query->where(function (Builder $builder) use ($global, $likeTerm) {
            $builder->where('destination', 'like', $likeTerm);

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $global) === 1) {
                $builder->orWhereDate('start_date', $global)
                    ->orWhereDate('end_date', $global);
            }

            $builder->orWhereHas(
                'travelers',
                static fn (Builder $travelerQuery) => $travelerQuery->where('name', 'like', $likeTerm),
            );
        });
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private function applyTravelersCountFilter(Builder $query, ?array $meta): void
    {
        if (! is_array($meta)) {
            return;
        }

        $value = $this->extractFilterValue($meta);

        if ($value === null) {
            return;
        }

        $query->has('travelers', '=', (int) $value);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function extractFilterValue(array $meta): mixed
    {
        if (array_key_exists('constraints', $meta) && is_array($meta['constraints'])) {
            foreach ($meta['constraints'] as $constraint) {
                if (! is_array($constraint)) {
                    continue;
                }

                $value = $constraint['value'] ?? null;

                if (! $this->filterService->isEmptyFilterValue($value)) {
                    return $value;
                }
            }

            return null;
        }

        $value = $meta['value'] ?? null;

        return $this->filterService->isEmptyFilterValue($value) ? null : $value;
    }
}
