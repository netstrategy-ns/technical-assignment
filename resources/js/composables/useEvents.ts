import { buildEventsIndexUrl as buildEventsIndexUrlFromFilters } from '@/composables/useEventFilters';
import type { BuildEventsIndexUrlOptions, EventFiltersState } from '@/types/models/event';

export type { EventFiltersState } from '@/types/models/event';

const defaultBaseUrl = '/events';

export type { EventCardEvent, EventCategoryOption } from '@/types/models/event';

// Costruzione e normalizzazione URL evento singolo (no slash finale)
export const buildEventDetailUrl = (slug: string, baseUrl: string = defaultBaseUrl): string => {
    const base = (baseUrl ?? defaultBaseUrl).replace(/\/$/, '');
    return `${base}/${slug}`;
};

// Re-esporta la costruzione URL filtrato delegandola a useEventFilters
export const buildEventsIndexUrl = buildEventsIndexUrlFromFilters;

// Restituisce helper per URL di dettaglio e indice eventi
export const useEvents = (eventsBaseUrl: string = defaultBaseUrl) => ({
    buildEventDetailUrl: (slug: string) => buildEventDetailUrl(slug, eventsBaseUrl),
    buildEventsIndexUrl: (filters: EventFiltersState, options?: BuildEventsIndexUrlOptions) =>
        buildEventsIndexUrlFromFilters(eventsBaseUrl, filters, options),
});
