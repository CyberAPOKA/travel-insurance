<?php

namespace App\Filters\Types;

use App\Filters\Contracts\FilterTypeInterface;
use Illuminate\Database\Eloquent\Builder;

class ContainsFilter implements FilterTypeInterface
{
    public static function matchMode(): string
    {
        return 'contains';
    }

    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void
    {
        $query->{$boolMethod}($column, 'like', '%'.$value.'%');
    }
}
