<?php

namespace App\Filters\Types;

use App\Filters\Contracts\FilterTypeInterface;
use Illuminate\Database\Eloquent\Builder;

class InFilter implements FilterTypeInterface
{
    public static function matchMode(): string
    {
        return 'in';
    }

    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);
        $values = array_values(array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : $v, $values), fn ($v) => $v !== null && $v !== ''));

        if ($values === []) {
            return;
        }

        if ($boolMethod === 'orWhere') {
            $query->orWhereIn($column, $values);
        } else {
            $query->whereIn($column, $values);
        }
    }
}
