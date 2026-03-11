import type { QueryParamValue } from '@/types/models/query';

// Normalizza la base URL per evitare query e slash finali
export const normalizeBaseUrl = (baseUrl: string, fallback = '/events'): string => {
    return (baseUrl ?? fallback).replace(/\?.*$/, '').replace(/\/$/, '') || fallback;
};

// Normalizza il valore del parametro della query
const normalizeQueryValue = (value: QueryParamValue): string | null => {
    if (value === null || value === undefined) {
        return null;
    }

    if (typeof value === 'boolean') {
        return value ? '1' : null;
    }

    const raw = String(value).trim();

    return raw === '' ? null : raw;
};

// Costruisce l'URL con i parametri della query
// Compone un URL con i parametri passati, pulendo quelli vuoti
export const buildUrlWithQuery = (baseUrl: string, params: Record<string, QueryParamValue>, fallback = '/events'): string => {
    const base = normalizeBaseUrl(baseUrl, fallback);
    const url = new URL(base, window.location.origin);

    for (const [key, value] of Object.entries(params)) {
        const normalized = normalizeQueryValue(value);

        if (normalized === null) {
            url.searchParams.delete(key);
            continue;
        }

        url.searchParams.set(key, normalized);
    }

    return `${url.pathname}${url.search}`;
};

// Aggiorna i parametri della query nell'URL corrente
export const updateCurrentUrlParams = (currentUrl: string, params: Record<string, QueryParamValue>): string => {
    const url = new URL(currentUrl, window.location.origin);

    for (const [key, value] of Object.entries(params)) {
        const normalized = normalizeQueryValue(value);

        if (normalized === null) {
            url.searchParams.delete(key);
            continue;
        }

        url.searchParams.set(key, normalized);
    }

    return `${url.pathname}${url.search}`;
};

// Ottiene il valore numerico del parametro della query
export const getNumericQueryParam = (url: string, key: string, fallback = 0): number => {
    const parsed = new URL(url, window.location.origin).searchParams.get(key);

    if (parsed === null) {
        return fallback;
    }

    const value = Number.parseInt(parsed, 10);

    return Number.isNaN(value) ? fallback : value;
};
