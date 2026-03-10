import type { Ref } from 'vue';

export type EventSortValue = 'date_asc' | 'date_desc' | 'featured_first';

export interface EventCategoryOption {
    id: number;
    name: string;
    slug: string;
}

export type EventFiltersSortValue = EventSortValue | string | null;

export interface EventFiltersState {
    featured: boolean;
    category?: string | null;
    search?: string | null;
    location?: string | null;
    start_date?: string | null;
    end_date?: string | null;
    available_tickets?: boolean;
    sort?: EventFiltersSortValue;
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

export interface EventCategoryReference {
    id: number;
    name: string;
}

export interface EventVenueReference {
    id: number;
    name: string;
}

export interface EventCardEvent {
    id: number;
    slug: string;
    title: string;
    location: string | null;
    image_url: string | null;
    starts_at: string | null;
    is_featured?: boolean;
    category?: EventCategoryReference | null;
    venueType?: EventVenueReference | null;
}

export interface HomeEventsByCategory {
    category: EventCategoryOption;
    events: EventCardEvent[];
}
