<?php

namespace App\Filters\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterTypeInterface
{
    /**
     * Nome do match mode (ex: 'contains', 'equals', 'dateIs').
     * Deve corresponder ao enviado pelo frontend (PrimeVue FilterMatchMode).
     */
    public static function matchMode(): string;

    /**
     * Aplica a restrição de filtro na query.
     *
     * @param  'where'|'orWhere'  $boolMethod
     */
    public function apply(Builder $query, string $column, mixed $value, string $boolMethod): void;
}
