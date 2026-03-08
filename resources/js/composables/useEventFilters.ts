import { router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import type { Ref } from 'vue';
import { computed, nextTick, ref, watch } from 'vue';
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
    featured: Ref<boolean>;
    sort: Ref<string>;
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
    const base = (baseUrl ?? defaultBaseUrl).replace(/\?.*$/, '');
    const params = new URLSearchParams();

    if (filters.featured) {
        params.set('featured', '1');
    }
    if (filters.category) {
        params.set('category', filters.category);
    }
    if (filters.search != null && filters.search !== '') {
        params.set('search', filters.search);
    }
    if (filters.location != null && filters.location !== '') {
        params.set('location', filters.location);
    }
    if (filters.start_date) {
        params.set('start_date', filters.start_date);
    }
    if (filters.end_date) {
        params.set('end_date', filters.end_date);
    }
    const sortVal = filters.sort?.trim();
    if (sortVal && sortVal !== DEFAULT_SORT) {
        params.set('sort', sortVal);
    }
    if (options.resetPage !== false) {
        params.set('page', '1');
    }
    if (options.perPage != null) {
        params.set('per_page', String(options.perPage));
    }

    const query = params.toString();
    return query ? `${base}?${query}` : base;
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
    const sort = ref(filtersFromServer.value.sort ?? DEFAULT_SORT);

    watch(
        filtersFromServer,
        (f) => {
            search.value = f.search ?? '';
            category.value = f.category ?? '';
            start_date.value = f.start_date ?? '';
            end_date.value = f.end_date ?? '';
            location.value = f.location ?? '';
            featured.value = f.featured ?? false;
            sort.value = (f.sort as EventSortValue) ?? DEFAULT_SORT;
        },
        { deep: true },
    );

    const currentPerPage = (): number => {
        const url = new URL(page.url, window.location.origin);
        const p = url.searchParams.get('per_page');
        const n = p ? parseInt(p, 10) : NaN;
        return PER_PAGE_OPTIONS.includes(n as (typeof PER_PAGE_OPTIONS)[number]) ? n : DEFAULT_PER_PAGE;
    };

    const buildQuery = (): EventFiltersState => ({
        featured: featured.value,
        category: category.value?.trim() || undefined,
        search: search.value ?? undefined,
        location: location.value ?? undefined,
        start_date: start_date.value || undefined,
        end_date: end_date.value || undefined,
        sort: sort.value || DEFAULT_SORT,
    });

    const applyFilters = () => {
        const url = buildEventsIndexUrl(baseUrl, buildQuery(), {
            resetPage: true,
            perPage: currentPerPage(),
        });
        router.visit(url, { preserveState });
    };

    const applyFiltersWithPreservedFocus = () => {
        const url = buildEventsIndexUrl(baseUrl, buildQuery(), {
            resetPage: true,
            perPage: currentPerPage(),
        });
        router.visit(url, {
            preserveState,
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
        featured.value = false;
        sort.value = DEFAULT_SORT;
        const url = buildEventsIndexUrl(baseUrl, { featured: false, sort: DEFAULT_SORT }, { perPage: currentPerPage() });
        router.visit(url, { preserveState });
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
            (q.sort != null && q.sort !== DEFAULT_SORT)
        );
    });

    return {
        search,
        category,
        start_date,
        end_date,
        location,
        featured,
        sort,
        applyFilters,
        applyFiltersDebounced,
        resetFilters,
        hasActiveFilters,
        buildQuery,
    };
};
