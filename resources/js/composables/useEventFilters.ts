import { router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import type { Ref } from 'vue';
import { computed, nextTick, ref, watch } from 'vue';
import {
    buildUrlWithQuery,
    getNumericQueryParam,
    normalizeBaseUrl,
} from '@/composables/useQueryUrl';
import {
    DEFAULT_PER_PAGE,
    DEFAULT_SORT,
    EVENT_SORT_OPTIONS,
    PER_PAGE_OPTIONS,
} from '@/constants';

const defaultBaseUrl = '/events';

// ------------------------------------------------------------
// Types e Interfaces
// ------------------------------------------------------------


export type EventSortValue = (typeof EVENT_SORT_OPTIONS)[number]['value'];
export { DEFAULT_SORT, EVENT_SORT_OPTIONS };

export interface EventFiltersState {
    featured: boolean;
    category?: string | null;
    search?: string | null;
    location?: string | null;
    start_date?: string | null;
    end_date?: string | null;
    available_tickets?: boolean;
    sort?: EventSortValue | string | null;
}

export interface BuildEventsIndexUrlOptions {
    resetPage?: boolean;
    perPage?: number;
}

export interface UseEventFiltersPanelOptions {
    preserveState?: boolean;
    debounceMs?: number;
    searchInputRef?: Ref<{ $el?: HTMLInputElement } | null>;
}

export interface UseEventFiltersPanelReturn {
    search: Ref<string>;
    category: Ref<string>;
    start_date: Ref<string>;
    end_date: Ref<string>;
    location: Ref<string>;
    availableTickets: Ref<boolean>;
    featured: Ref<boolean>;
    sort: Ref<string>;
    currentPerPage: Ref<number>;
    applyFilters: () => void;
    applyFiltersDebounced: () => void;
    resetFilters: () => void;
    hasActiveFilters: Ref<boolean>;
    buildQuery: () => EventFiltersState;
}

// ------------------------------------------------------------
// Funzioni
// ------------------------------------------------------------

// Mantiene lo stesso formato dell'URL quando si applicano i filtri
export const buildEventsIndexUrl = (
    baseUrl: string = defaultBaseUrl,
    filters: EventFiltersState = { featured: false },
    options: BuildEventsIndexUrlOptions = {},
): string => {
    const base = normalizeBaseUrl(baseUrl ?? defaultBaseUrl);
    const sortVal = filters.sort?.trim();

    return buildUrlWithQuery(
        base,
        {
            featured: filters.featured,
            category: filters.category,
            search: filters.search,
            location: filters.location,
            start_date: filters.start_date,
            end_date: filters.end_date,
            available_tickets: filters.available_tickets,
            sort: sortVal && sortVal !== DEFAULT_SORT ? sortVal : undefined,
            page: options.resetPage !== false ? 1 : undefined,
            per_page: options.perPage,
        },
        defaultBaseUrl,
    );
};


export const filtersStateFromPayload = (payload: Record<string, unknown>): EventFiltersState => {
    const sortRaw = payload.sort != null ? String(payload.sort) : null;
    const sort = sortRaw && ['date_asc', 'date_desc', 'featured_first'].includes(sortRaw) ? sortRaw : DEFAULT_SORT;
    return {
        featured: Boolean(payload.featured),
        category: payload.category != null ? String(payload.category) : null,
        search: payload.search != null ? String(payload.search) : null,
        location: payload.location != null ? String(payload.location) : null,
        start_date: payload.start_date != null ? String(payload.start_date) : null,
        end_date: payload.end_date != null ? String(payload.end_date) : null,
        available_tickets: Boolean(payload.available_tickets),
        sort,
    };
};

export const useEventFilters = (eventsIndexUrl: string = defaultBaseUrl) => {
    return {
        buildEventsIndexUrl: (filters: EventFiltersState, options?: BuildEventsIndexUrlOptions) =>
            buildEventsIndexUrl(eventsIndexUrl, filters, options),
        filtersStateFromPayload,
    };
};


export const useEventFiltersPanel = (
    baseUrl: string,
    filtersFromServer: Ref<EventFiltersState>,
    options: UseEventFiltersPanelOptions = {},
): UseEventFiltersPanelReturn => {
    const { preserveState = true, debounceMs = 400, searchInputRef } = options;
    const page = usePage();

    const search = ref(filtersFromServer.value.search ?? '');
    const category = ref(filtersFromServer.value.category ?? '');
    const start_date = ref(filtersFromServer.value.start_date ?? '');
    const end_date = ref(filtersFromServer.value.end_date ?? '');
    const location = ref(filtersFromServer.value.location ?? '');
    const featured = ref(filtersFromServer.value.featured ?? false);
    const availableTickets = ref(filtersFromServer.value.available_tickets ?? false);
    const sort = ref(filtersFromServer.value.sort ?? DEFAULT_SORT);

    const getSearchInputElement = (): HTMLInputElement | null =>
        searchInputRef?.value?.$el ?? null;

    const isSearchInputFocused = (): boolean => {
        const input = getSearchInputElement();

        return input != null && typeof document !== 'undefined' && document.activeElement === input;
    };

    watch(
        filtersFromServer,
        (f) => {
            if (!isSearchInputFocused()) {
                search.value = f.search ?? '';
            }
            category.value = f.category ?? '';
            start_date.value = f.start_date ?? '';
            end_date.value = f.end_date ?? '';
            location.value = f.location ?? '';
            featured.value = f.featured ?? false;
            availableTickets.value = f.available_tickets ?? false;
            sort.value = (f.sort as EventSortValue) ?? DEFAULT_SORT;
        },
        { deep: true },
    );

    const currentPerPage = computed(() => {
        const n = getNumericQueryParam(page.url, 'per_page', DEFAULT_PER_PAGE);
        return PER_PAGE_OPTIONS.includes(n as (typeof PER_PAGE_OPTIONS)[number]) ? n : DEFAULT_PER_PAGE;
    });

    const buildQuery = (): EventFiltersState => ({
        featured: featured.value,
        category: category.value?.trim() || undefined,
        search: search.value ?? undefined,
        location: location.value ?? undefined,
        start_date: start_date.value || undefined,
        end_date: end_date.value || undefined,
        available_tickets: availableTickets.value,
        sort: sort.value || DEFAULT_SORT,
    });

    const applyFilters = () => {
        const url = buildEventsIndexUrl(baseUrl, buildQuery(), {
            resetPage: true,
            perPage: currentPerPage.value,
        });
        router.visit(url, { preserveState, replace: true, preserveScroll: true });
    };

    const applyFiltersWithPreservedFocus = () => {
        const url = buildEventsIndexUrl(baseUrl, buildQuery(), {
            resetPage: true,
            perPage: currentPerPage.value,
        });
        router.visit(url, {
            preserveState,
            replace: true,
            preserveScroll: true,
            onFinish: () => {
                nextTick(() => searchInputRef?.value?.$el?.focus());
            },
        });
    };

    const applyFiltersDebounced = useDebounceFn(
        searchInputRef ? applyFiltersWithPreservedFocus : applyFilters,
        debounceMs,
    );

    const resetFilters = () => {
        search.value = '';
        category.value = '';
        start_date.value = '';
        end_date.value = '';
        location.value = '';
        availableTickets.value = false;
        featured.value = false;
        sort.value = DEFAULT_SORT;
        const url = buildEventsIndexUrl(
            baseUrl,
            { featured: false, available_tickets: false, sort: DEFAULT_SORT },
            { perPage: currentPerPage.value },
        );
        router.visit(url, { preserveState, replace: true, preserveScroll: true });
    };

    const hasActiveFilters = computed(() => {
        const q = buildQuery();
        return (
            q.featured === true ||
            (q.category != null && q.category !== '') ||
            (q.search != null && q.search.trim() !== '') ||
            (q.location != null && q.location.trim() !== '') ||
            (q.start_date != null && q.start_date !== '') ||
            (q.end_date != null && q.end_date !== '') ||
            q.available_tickets === true ||
            (q.sort != null && q.sort !== DEFAULT_SORT)
        );
    });

    return {
        search,
        category,
        start_date,
        end_date,
        location,
        availableTickets,
        featured,
        sort,
        currentPerPage,
        applyFilters,
        applyFiltersDebounced,
        resetFilters,
        hasActiveFilters,
        buildQuery,
    };
};
