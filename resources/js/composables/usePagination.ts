import { router, usePage } from '@inertiajs/vue3';
import type { MaybeRefOrGetter } from 'vue';
import { toValue } from 'vue';
import { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS } from '@/constants';

export type PerPageOption = (typeof PER_PAGE_OPTIONS)[number];
export { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS };

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}


export interface PaginationNavPayload {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

export const usePagination = (pagination: MaybeRefOrGetter<PaginationNavPayload>) => {
    const page = usePage();

    const getUrlForPage = (pageNumber: number): string | null => {
        const p = toValue(pagination);
        const link = p.links.find((l) => l.label === String(pageNumber));
        return link?.url ?? null;
    };

    const onPageChange = (pageNumber: number): void => {
        const url = getUrlForPage(pageNumber);
        if (url) {
            router.visit(url);
        }
    };

    const getUrlForPerPage = (perPage: PerPageOption): string => {
        const base = new URL(page.url, window.location.origin);
        base.searchParams.set('per_page', String(perPage));
        base.searchParams.set('page', '1');
        return base.pathname + base.search;
    };

    const onPerPageChange = (perPage: PerPageOption): void => {
        router.visit(getUrlForPerPage(perPage));
    };

    return { getUrlForPage, onPageChange, getUrlForPerPage, onPerPageChange };
};
