<?php

namespace App\Filters\Types;

use App\Filters\Contracts\FilterTypeInterface;
use Illuminate\Database\Eloquent\Builder;

class BetweenFilter implements FilterTypeInterface
{
    public static function matchMode(): string
    {
        return 'between';
    }

    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void
    {
        if (! is_array($value) || count($value) < 2) {
            return;
        }

        [$min, $max] = array_values($value);
        $minEmpty = $this->isEmpty($min);
        $maxEmpty = $this->isEmpty($max);

        if ($minEmpty && $maxEmpty) {
            return;
        }

        $isDate = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);

        if (! $minEmpty && ! $maxEmpty) {
            if ($isDate($min) || $isDate($max)) {
                $query->{$boolMethod}(function (Builder $q) use ($column, $min, $max) {
                    $q->whereDate($column, '>=', $min)->whereDate($column, '<=', $max);
                });
                return;
            }
            $query->{$boolMethod}(function (Builder $q) use ($column, $min, $max) {
                $q->where($column, '>=', $min)->where($column, '<=', $max);
            });
            return;
        }

        if (! $minEmpty) {
            $op = '>=';
            $isDate($min)
                ? $query->{$boolMethod.'Date'}($column, $op, $min)
                : $query->{$boolMethod}($column, $op, $min);
            return;
        }

        if (! $maxEmpty) {
            $op = '<=';
            $isDate($max)
                ? $query->{$boolMethod.'Date'}($column, $op, $max)
                : $query->{$boolMethod}($column, $op, $max);
        }
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        return false;
    }
}
