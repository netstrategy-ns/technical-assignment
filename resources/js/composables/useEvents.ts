import type { EventFiltersState } from '@/composables/useEventFilters';
import { buildEventsIndexUrl as buildEventsIndexUrlFromFilters } from '@/composables/useEventFilters';

export type { EventFiltersState } from '@/composables/useEventFilters';

const defaultBaseUrl = '/events';

export interface EventCardEvent {
    id: number;
    slug: string;
    title: string;
    location: string | null;
    image_url: string | null;
    starts_at: string | null;
    is_featured?: boolean;
    category?: { id: number; name: string } | null;
    venueType?: { id: number; name: string } | null;
}

export interface EventCategoryOption {
    id: number;
    name: string;
    slug: string;
}

// Costruzione e normalizzazione URL evento singolo (no slug ---> no slash)
export const buildEventDetailUrl = (slug: string, baseUrl: string = defaultBaseUrl): string => {
    const base = (baseUrl ?? defaultBaseUrl).replace(/\/$/, '');
    return `${base}/${slug}`;
};

// Permette di utilizzare buildEventsIndexUrl per costruire l'URL con i filtri senza importare useEventFilters
export const buildEventsIndexUrl = buildEventsIndexUrlFromFilters;

// Esporta le funzioni per passare dal componente solo slug, filtri e opzioni per la costruzione dell'URL
export const useEvents = (eventsBaseUrl: string = defaultBaseUrl) => ({
    buildEventDetailUrl: (slug: string) => buildEventDetailUrl(slug, eventsBaseUrl),
    buildEventsIndexUrl: (filters: EventFiltersState, options?: { resetPage?: boolean; perPage?: number }) =>
        buildEventsIndexUrlFromFilters(eventsBaseUrl, filters, options),
});
