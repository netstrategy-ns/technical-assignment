import type { BuildEventsIndexUrlOptions, EventCardEvent, EventCategoryOption, EventFiltersState } from '@/types/models/event';
import { buildEventsIndexUrl as buildEventsIndexUrlFromFilters } from '@/composables/useEventFilters';

export type { EventFiltersState } from '@/types/models/event';

const defaultBaseUrl = '/events';

export type { EventCardEvent, EventCategoryOption } from '@/types/models/event';

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
    buildEventsIndexUrl: (filters: EventFiltersState, options?: BuildEventsIndexUrlOptions) =>
        buildEventsIndexUrlFromFilters(eventsBaseUrl, filters, options),
});
