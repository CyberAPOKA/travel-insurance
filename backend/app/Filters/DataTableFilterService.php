<?php

namespace App\Filters;

use App\Filters\Contracts\FilterTypeInterface;
use App\Filters\Types\BetweenFilter;
use App\Filters\Types\ContainsFilter;
use App\Filters\Types\DateAfterFilter;
use App\Filters\Types\DateBeforeFilter;
use App\Filters\Types\DateIsFilter;
use App\Filters\Types\DateIsNotFilter;
use App\Filters\Types\EndsWithFilter;
use App\Filters\Types\EqualsFilter;
use App\Filters\Types\GreaterThanFilter;
use App\Filters\Types\GreaterThanOrEqualFilter;
use App\Filters\Types\InFilter;
use App\Filters\Types\LessThanFilter;
use App\Filters\Types\LessThanOrEqualFilter;
use App\Filters\Types\NotEqualsFilter;
use App\Filters\Types\StartsWithFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Serviço reutilizável de filtros avançados para DataTables.
 * Aplica filtros no formato PrimeVue (global + por coluna com constraints).
 * Use em qualquer tabela do sistema; cada tipo de filtro é um serviço em App\Filters\Types.
 *
 * @see \App\Filters\Contracts\FilterTypeInterface
 * @see \App\Filters\Types\*
 */
class DataTableFilterService
{
    /** @var array<string, FilterTypeInterface> matchMode (lowercase) => instance */
    private static ?array $registry = null;

    /**
     * Aplica os filtros à query.
     *
     * @param  array<string, mixed>  $filtersMeta  Meta dos filtros (formato PrimeVue DataTable)
     * @param  array<string, string>  $fieldMap     Mapa filterField => coluna DB
     * @param  array<string>  $globalFields        Colunas para busca global (LIKE em qualquer uma)
     */
    public function apply(Builder $query, array $filtersMeta, array $fieldMap, array $globalFields = []): Builder
    {
        $this->applyGlobalFilter($query, $filtersMeta, $globalFields);

        foreach ($fieldMap as $filterField => $column) {
            $meta = $filtersMeta[$filterField] ?? null;
            if (! is_array($meta)) {
                continue;
            }

            if (array_key_exists('constraints', $meta) && is_array($meta['constraints'])) {
                $this->applyConstraints($query, $column, $meta);
                continue;
            }

            $value = $meta['value'] ?? null;
            $matchMode = $meta['matchMode'] ?? null;
            if ($this->isEmptyFilterValue($value)) {
                continue;
            }
            $this->applyOneConstraint($query, $column, $matchMode, $value, 'where');
        }

        return $query;
    }

    /**
     * Aplica filtro global (busca em múltiplas colunas).
     *
     * @param  array<string>  $globalFields
     */
    public function applyGlobalFilter(Builder $query, array $filtersMeta, array $globalFields): void
    {
        $global = Arr::get($filtersMeta, 'global.value');
        if (is_string($global)) {
            $global = trim($global);
        }
        if ($global === null || $global === '' || $globalFields === []) {
            return;
        }
        $query->where(function (Builder $q) use ($global, $globalFields) {
            foreach ($globalFields as $i => $col) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method}($col, 'like', '%'.$global.'%');
            }
        });
    }

    /**
     * Aplica várias restrições de uma coluna (modo avançado: operator + constraints).
     */
    private function applyConstraints(Builder $query, string $column, array $meta): void
    {
        $operator = strtolower((string) ($meta['operator'] ?? 'and'));
        $bool = $operator === 'or' ? 'or' : 'and';
        $constraints = $meta['constraints'];

        $query->where(function (Builder $q) use ($constraints, $column, $bool) {
            $first = true;
            foreach ($constraints as $constraint) {
                if (! is_array($constraint)) {
                    continue;
                }
                $value = $constraint['value'] ?? null;
                $matchMode = $constraint['matchMode'] ?? null;
                if ($this->isEmptyFilterValue($value)) {
                    continue;
                }
                $boolMethod = $first ? 'where' : ($bool === 'or' ? 'orWhere' : 'where');
                $this->applyOneConstraint($q, $column, $matchMode, $value, $boolMethod);
                $first = false;
            }
        });
    }

    private function applyOneConstraint(Builder $query, string $column, mixed $matchMode, mixed $value, string $boolMethod): void
    {
        $mode = is_string($matchMode) ? strtolower($matchMode) : 'contains';
        $filter = $this->getFilterType($mode);
        if ($filter !== null) {
            $filter->apply($query, $column, $value, $boolMethod);
            return;
        }
        // Fallback: contains
        $query->{$boolMethod}($column, 'like', '%'.$value.'%');
    }

    private function getFilterType(string $matchMode): ?FilterTypeInterface
    {
        $registry = $this->getRegistry();
        $key = $this->normalizeMatchMode($matchMode);

        return $registry[$key] ?? $registry['contains'] ?? null;
    }

    /**
     * Normaliza o matchMode do frontend (ex: PrimeVue camelCase) para a chave do registro.
     */
    private function normalizeMatchMode(string $matchMode): string
    {
        $mode = strtolower($matchMode);
        $aliases = [
            'lessthan' => 'lt',
            'less_than' => 'lt',
            'lessthanorequalto' => 'lte',
            'less_than_or_equal_to' => 'lte',
            'greaterthan' => 'gt',
            'greater_than' => 'gt',
            'greaterthanorequalto' => 'gte',
            'greater_than_or_equal_to' => 'gte',
        ];

        return $aliases[$mode] ?? $mode;
    }

    private function getRegistry(): array
    {
        if (self::$registry !== null) {
            return self::$registry;
        }

        $types = [
            EqualsFilter::class,
            NotEqualsFilter::class,
            ContainsFilter::class,
            StartsWithFilter::class,
            EndsWithFilter::class,
            LessThanFilter::class,
            LessThanOrEqualFilter::class,
            GreaterThanFilter::class,
            GreaterThanOrEqualFilter::class,
            InFilter::class,
            BetweenFilter::class,
            DateIsFilter::class,
            DateIsNotFilter::class,
            DateBeforeFilter::class,
            DateAfterFilter::class,
        ];

        self::$registry = [];
        foreach ($types as $class) {
            $instance = app($class);
            if ($instance instanceof FilterTypeInterface) {
                self::$registry[$instance::matchMode()] = $instance;
            }
        }

        return self::$registry;
    }

    public function isEmptyFilterValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        if (is_array($value)) {
            $filtered = array_values(array_filter($value, fn ($v) => ! $this->isEmptyFilterValue($v)));

            return $filtered === [];
        }

        return false;
    }
}
