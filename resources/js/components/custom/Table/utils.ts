import type { TableColumn } from '@/components/custom/Table/types';

const toSnakeCase = (value: string): string =>
    value.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase();

const toCamelCase = (value: string): string =>
    value.replace(/_([a-z])/g, (_, char: string): string => char.toUpperCase());

export function getNestedValue(row: Record<string, unknown>, fieldName: string): unknown {
    return fieldName.split('.').reduce((value: unknown, key: string): unknown => {
        if (value === null || value === undefined || typeof value !== 'object') {
            return null;
        }

        const record = value as Record<string, unknown>;
        const bySameKey = record[key];
        const bySnake = record[toSnakeCase(key)];
        const byCamel = record[toCamelCase(key)];

        return bySameKey ?? bySnake ?? byCamel ?? null;
    }, row);
}

export function formatCellValue(value: unknown, column: TableColumn): string {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    if (Array.isArray(column.options) && column.options.length > 0) {
        const optionValue = String(value);
        const option = column.options.find((item) => String(item.value) === optionValue);

        if (option?.label !== undefined) {
            return option.label;
        }
    }

    if (column.cast_type === 'boolean') {
        return value ? 'Sì' : 'No';
    }

    if (column.cast_type.startsWith('datetime')) {
        const date = new Date(String(value));
        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleString('it-IT');
    }

    if (column.cast_type.startsWith('currency')) {
        const match = column.cast_type.match(/^currency(?::(\d+))?$/);
        const precision = match === null ? 2 : Number(match[1] ?? 2);
        const numeric = Number(value);

        if (Number.isNaN(numeric)) {
            return String(value);
        }

        return numeric.toLocaleString('it-IT', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: precision,
            maximumFractionDigits: precision,
        });
    }

    if (column.cast_type.startsWith('decimal')) {
        const numeric = Number(value);
        if (Number.isNaN(numeric)) {
            return String(value);
        }

        return numeric.toLocaleString('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    return String(value);
}
