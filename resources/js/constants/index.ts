// ------------------------------------------------------------
// Paginazione
// ------------------------------------------------------------
export const PER_PAGE_OPTIONS = [12, 24, 32, 48] as const;
export const DEFAULT_PER_PAGE = 24;

// ------------------------------------------------------------
// Ordinamento
// ------------------------------------------------------------
export const EVENT_SORT_OPTIONS = [
    { value: 'date_asc', label: 'Data crescente' },
    { value: 'date_desc', label: 'Data decrescente' },
    { value: 'featured_first', label: 'In evidenza prima' },
] as const;

export const DEFAULT_SORT = 'date_asc' as const;
