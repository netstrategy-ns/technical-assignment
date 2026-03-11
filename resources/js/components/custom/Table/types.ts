export type TableColumnInputType = 'text' | 'number' | 'date' | 'datetime-local' | 'checkbox' | 'select';

export type TableColumnOption = {
    value: string | number | boolean;
    label: string;
};

export type TableColumn = {
    field_name: string;
    label: string;
    placeholder?: string | null;
    cast_type: string;
    input_type?: TableColumnInputType | null;
    options?: TableColumnOption[];
    filterable: boolean;
    sortable: boolean;
    default_sort: 'asc' | 'desc' | null;
};

export type TableSort = {
    field: string | null;
    dir: 'asc' | 'desc';
};

export type TableFiltersState = Record<string, string | number | boolean | null>;
