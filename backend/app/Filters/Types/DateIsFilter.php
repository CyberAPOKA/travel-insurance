<?php

namespace App\Filters\Types;

use App\Filters\Contracts\FilterTypeInterface;
use Illuminate\Database\Eloquent\Builder;

class DateIsFilter implements FilterTypeInterface
{
    public static function matchMode(): string
    {
        return 'dateis';
    }

    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void
    {
        $query->{$boolMethod.'Date'}($column, '=', $value);
    }
}
