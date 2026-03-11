<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasAdminTable
{
    abstract public static function tableColumns(): array;

    public static function tableFilterScopes(): array
    {
        return static::buildTableScopeMap('filter');
    }

    public static function tableSortScopes(): array
    {
        return static::buildTableScopeMap('sort');
    }

    protected static function buildTableScopeMap(string $operation): array
    {
        $scopePrefix = $operation === 'sort' ? 'sortBy' : 'filterBy';
        $map = [];

        foreach (static::tableColumns() as $column) {
            $fieldName = (string) ($column['field_name'] ?? '');
            if ($fieldName === '') {
                continue;
            }

            $scope = static::resolveTableScopeForField($fieldName, $scopePrefix);
            if ($scope === null) {
                continue;
            }

            $map[$fieldName] = $scope;
        }

        return $map;
    }

    protected static function resolveTableScopeForField(string $fieldName, string $scopePrefix): ?string
    {
        $segments = array_filter(explode('.', $fieldName), fn (string $segment): bool => $segment !== '');

        for ($i = 0; $i < count($segments); $i++) {
            $candidateSegments = array_slice($segments, $i);
            $candidate = static::toStudlyParts($candidateSegments);
            $scope = $scopePrefix . $candidate;

            if (method_exists(static::class, "scope{$scope}")) {
                return $scope;
            }

        }

        return null;
    }

    protected static function toStudlyParts(array $parts): string
    {
        $studied = array_map(
            static function (string $part): string {
                $normalized = str_replace('_', ' ', $part);
                return Str::studly($normalized);
            },
            $parts,
        );

        return implode('', $studied);
    }

    public static function tableFilterableColumns(): array
    {
        return array_values(array_filter(
            static::tableColumns(),
            static fn (array $column): bool => (bool) ($column['filterable'] ?? false),
        ));
    }

    public static function tableSortableColumns(): array
    {
        return array_values(array_filter(
            static::tableColumns(),
            static fn (array $column): bool => (bool) ($column['sortable'] ?? false),
        ));
    }

    public static function tableDefaultSort(): ?array
    {
        foreach (static::tableColumns() as $column) {
            if (! isset($column['default_sort']) || $column['default_sort'] === null) {
                continue;
            }

            return [
                'field' => $column['field_name'],
                'dir' => $column['default_sort'],
            ];
        }

        return null;
    }

    public static function tableFilterRequestKey(string $fieldName): string
    {
        return str_replace('.', '__', $fieldName);
    }

    public static function normalizeTableFilterValue(mixed $value): mixed
    {
        if (is_array($value)) {
            if ($value === []) {
                return null;
            }

            $value = $value[0] ?? null;
        }

        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = trim($value);

            if ($normalized === '') {
                return null;
            }

            $lower = strtolower($normalized);
            if ($lower === 'true') {
                return true;
            }

            if ($lower === 'false') {
                return false;
            }

            if (is_numeric($normalized) && ctype_digit($normalized)) {
                return (int) $normalized;
            }

            if (is_numeric($normalized)) {
                return (float) $normalized;
            }

            return $normalized;
        }

        return $value;
    }

    public static function requestedTableFilters(array $payload): array
    {
        $filters = [];
        $filterScopes = static::tableFilterScopes();

        foreach (array_keys($filterScopes) as $fieldName) {
            $requestKey = static::tableFilterRequestKey($fieldName);

            $rawValue = array_key_exists($requestKey, $payload)
                ? $payload[$requestKey]
                : ($payload[$fieldName] ?? null);

            $value = static::normalizeTableFilterValue($rawValue);
            if ($value === null) {
                continue;
            }

            if (is_bool($value) && $value === false) {
                continue;
            }

            $filters[$fieldName] = $value;
        }

        return $filters;
    }

    public static function applyTableFilters(Builder $query, array $payload): Builder
    {
        $filters = static::requestedTableFilters($payload);
        $scopes = static::tableFilterScopes();

        foreach ($filters as $fieldName => $value) {
            if (! array_key_exists($fieldName, $scopes)) {
                continue;
            }

            $scope = $scopes[$fieldName];

            if (! is_string($scope) || ! method_exists(static::class, "scope{$scope}")) {
                continue;
            }

            $query->{$scope}($value);
        }

        return $query;
    }

    public static function parseTableSort(array $payload): array
    {
        $defaultSort = static::tableDefaultSort();
        $sortField = is_string($payload['sort'] ?? null) ? trim((string) $payload['sort']) : null;
        $sortDir = is_string($payload['sort_dir'] ?? null) ? strtolower(trim((string) $payload['sort_dir'])) : null;

        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = $defaultSort['dir'] ?? 'asc';
        }

        if ($sortField === null) {
            return $defaultSort ?? ['field' => null, 'dir' => $sortDir];
        }

        $sortableFields = array_map(
            static fn (array $column): string => (string) $column['field_name'],
            static::tableSortableColumns(),
        );

        if (! in_array($sortField, $sortableFields, true)) {
            return $defaultSort ?? ['field' => null, 'dir' => $sortDir];
        }

        return [
            'field' => $sortField,
            'dir' => $sortDir,
        ];
    }

    public static function applyTableSort(Builder $query, string $field, string $dir): Builder
    {
        $direction = strtolower($dir) === 'desc' ? 'desc' : 'asc';
        $sortScopes = static::tableSortScopes();

        if (isset($sortScopes[$field]) && is_string($sortScopes[$field]) && method_exists(static::class, "scope{$sortScopes[$field]}")) {
            $query->{$sortScopes[$field]}($direction);
            return $query;
        }

        return $query->orderBy($field, $direction);
    }
}
