<?php

namespace App\Filters\Types;

use App\Filters\Contracts\FilterTypeInterface;
use Illuminate\Database\Eloquent\Builder;

class EqualsFilter implements FilterTypeInterface
{
    public static function matchMode(): string
    {
        return 'equals';
    }

    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void
    {
        $query->{$boolMethod}($column, '=', $value);
    }
}
