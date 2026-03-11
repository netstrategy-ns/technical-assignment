<?php

namespace App\Concerns;

use Carbon\Carbon;

trait NormalizeQueryFilters
{

    protected function normalizeText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function normalizeDate(mixed $value, bool $startOfDay): ?string
    {
        $value = $this->normalizeText($value);

        if ($value === null) {
            return null;
        }

        $date = Carbon::parse($value);

        return $startOfDay
            ? $date->startOfDay()->toDateTimeString()
            : $date->endOfDay()->toDateTimeString();
    }

    protected function normalizeSort(string $field, array $allowed, string $default): string
    {
        $value = $this->string($field, $default)->toString();

        return in_array($value, $allowed, true) ? $value : $default;
    }

    protected function normalizePerPage(string $field = 'per_page', int $default = 24, int $min = 1, int $max = 100): int
    {
        $perPage = (int) $this->input($field, $default);

        return max($min, min($max, $perPage));
    }
}
