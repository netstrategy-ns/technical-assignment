import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { TableColumn, TableFiltersState, TableSort } from '@/components/custom/Table/types';
import { DEFAULT_PER_PAGE } from '@/constants';
import type { PaginatedResponse, PerPageOption } from '@/types/models/pagination';

type RequestPayloadValues = string | number | boolean | null | undefined;
type RequestPayload = Record<string, RequestPayloadValues>;

export interface UseTableFiltersOptions {
    emit: (filters: TableFiltersState) => void;
    debounceMs?: number;
}

export const useTableFilters = (
    columns: Ref<TableColumn[]>,
    filters: Ref<TableFiltersState>,
    options: UseTableFiltersOptions,
) => {
    const { emit, debounceMs = 250 } = options;

    const localFilters = reactive<TableFiltersState>({});
    const filterableColumns = computed(() => columns.value.filter((column) => column.filterable));

    const syncFilters = (): void => {
        Object.keys(localFilters).forEach((key) => {
            delete localFilters[key];
        });

        Object.entries(filters.value).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
                return;
            }

            localFilters[key] = value;
        });
    };

    const normalizeAndEmit = (): void => {
        const payload: TableFiltersState = {};

        filterableColumns.value.forEach((column) => {
            const key = column.field_name;
            const value = localFilters[key];

            if (value === undefined || value === null || value === '') {
                return;
            }

            if (column.input_type === 'checkbox') {
                if (value === false) {
                    return;
                }

                payload[key] = true;
                return;
            }

            payload[key] = value;
        });

        emit(payload);
    };

    const emitFiltersTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

    const clearEmitFilterTimer = (): void => {
        if (emitFiltersTimeout.value !== null) {
            clearTimeout(emitFiltersTimeout.value);
            emitFiltersTimeout.value = null;
        }
    };

    const emitFilters = (immediate = false): void => {
        clearEmitFilterTimer();

        if (immediate) {
            normalizeAndEmit();
            return;
        }

        emitFiltersTimeout.value = setTimeout(() => {
            emitFiltersTimeout.value = null;
            normalizeAndEmit();
        }, debounceMs);
    };

    const onInput = (column: TableColumn, value: unknown): void => {
        const inputType = column.input_type ?? 'text';

        switch (inputType) {
            case 'checkbox':
                localFilters[column.field_name] = Boolean(value);
                break;
            case 'number':
                localFilters[column.field_name] = typeof value === 'number'
                    ? value
                    : (String(value) === '' ? '' : Number(String(value)));
                break;
            default:
                localFilters[column.field_name] = String(value ?? '');
                break;
        }

        emitFilters();
    };

    const onInputAndCommit = (column: TableColumn, value: unknown): void => {
        onInput(column, value);
        commitFilters();
    };

    const commitFilters = (): void => {
        emitFilters(true);
    };

    const resetFilters = (): void => {
        Object.keys(localFilters).forEach((key) => {
            delete localFilters[key];
        });

        clearEmitFilterTimer();
        emit({});
    };

    const isSelectColumn = (column: TableColumn): boolean => {
        return column.input_type === 'select';
    };

    watch(() => filters.value, syncFilters, { deep: true, immediate: true });

    return {
        localFilters,
        filterableColumns,
        onInput,
        onInputAndCommit,
        commitFilters,
        resetFilters,
        isSelectColumn,
    };
};

export interface UseTableOptions {
    columns: Ref<TableColumn[]>;
    rows: Ref<PaginatedResponse<Record<string, unknown>>>;
    sort: Ref<TableSort>;
    filters: Ref<TableFiltersState>;
}

const defaultPaginatedResponse: PaginatedResponse<Record<string, unknown>> = {
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: DEFAULT_PER_PAGE,
    total: 0,
    links: [],
};

const toSafePerPage = (value: unknown): PerPageOption => {
    const candidate = value as number;

    if (candidate === 12 || candidate === 24 || candidate === 32 || candidate === 48) {
        return candidate;
    }

    return DEFAULT_PER_PAGE;
};

export const useTable = ({ columns, rows, sort, filters }: UseTableOptions) => {
    const page = usePage();
    const baseUrl = computed(() => page.url.split('?')[0]);

    const tableColumns = computed(() => (Array.isArray(columns.value) ? columns.value : []));
    const tableDefaultSort = computed<TableSort>(() => {
        const defaultColumn = tableColumns.value.find(
            (column) => column.default_sort === 'asc' || column.default_sort === 'desc',
        );
        const defaultDirection = defaultColumn?.default_sort;

        if (!defaultColumn || defaultColumn.field_name === '' || defaultDirection == null) {
            return {
                field: null,
                dir: 'asc',
            };
        }

        return {
            field: defaultColumn.field_name,
            dir: defaultDirection,
        };
    });

    const tablePagination = computed<PaginatedResponse<Record<string, unknown>>>(() => rows.value ?? defaultPaginatedResponse);
    const tableRows = computed<Record<string, unknown>[]>(() => {
        const rowsData = tablePagination.value.data;

        return Array.isArray(rowsData) ? rowsData : [];
    });

    const tableSort = ref<TableSort>(sort.value ?? tableDefaultSort.value);
    const tableFilters = ref<TableFiltersState>(filters.value ?? {});
    const perPage = ref<PerPageOption>(toSafePerPage(rows.value?.per_page));
    const hasDefaultDescSortCycling = ref(false);

    watch(
        () => sort.value,
        (nextSort) => {
            tableSort.value = nextSort ?? tableDefaultSort.value;
            hasDefaultDescSortCycling.value = false;
        },
    );
    watch(
        () => filters.value,
        (nextFilters) => {
            tableFilters.value = nextFilters ?? {};
        },
    );
    watch(
        () => rows.value?.per_page,
        (nextPerPage) => {
            perPage.value = toSafePerPage(nextPerPage);
        },
    );

    const toRequestKey = (fieldName: string): string => fieldName.replaceAll('.', '__');

    const buildRequestPayload = (overrides: RequestPayload = {}): RequestPayload => {
        const normalizedFilters: RequestPayload = {};

        Object.entries(tableFilters.value).forEach(([fieldName, value]) => {
            if (value === null || value === undefined || value === '') {
                return;
            }

            if (typeof value === 'boolean' && !value) {
                return;
            }

            normalizedFilters[toRequestKey(fieldName)] = value;
        });

        const activeSort = tableSort.value;
        const sortPayload = activeSort.field === null
            ? {}
            : {
                  sort: activeSort.field,
                  sort_dir: activeSort.dir,
              };

        return {
            per_page: perPage.value,
            ...sortPayload,
            ...normalizedFilters,
            ...overrides,
        };
    };

    const visit = (overrides: RequestPayload = {}, preservePage = false): void => {
        const payload = buildRequestPayload(overrides);

        if (!preservePage) {
            payload.page = 1;
        }

        router.get(baseUrl.value, payload, {
            preserveState: false,
            preserveScroll: true,
            replace: true,
        });
    };

    const onFiltersUpdate = (nextFilters: TableFiltersState): void => {
        tableFilters.value = nextFilters;
        visit();
    };

    const onSortUpdate = (nextSort: TableSort): void => {
        const isDefaultColumnSort =
            tableDefaultSort.value.field !== null &&
            tableDefaultSort.value.field === tableSort.value.field &&
            tableDefaultSort.value.dir === tableSort.value.dir;

        if (nextSort.field === null && isDefaultColumnSort && tableDefaultSort.value.dir === 'desc') {
            if (hasDefaultDescSortCycling.value) {
                tableSort.value = tableDefaultSort.value;
                hasDefaultDescSortCycling.value = false;
            } else {
                tableSort.value = { field: tableDefaultSort.value.field, dir: 'asc' };
                hasDefaultDescSortCycling.value = true;
            }

            visit();
            return;
        }

        hasDefaultDescSortCycling.value = false;
        tableSort.value = nextSort;
        visit();
    };

    const onPerPageUpdate = (value: number): void => {
        perPage.value = toSafePerPage(value);
        visit();
    };

    return {
        tableColumns,
        tableDefaultSort,
        tablePagination,
        tableRows,
        tableSort,
        tableFilters,
        perPage,
        onFiltersUpdate,
        onSortUpdate,
        onPerPageUpdate,
    };
};
