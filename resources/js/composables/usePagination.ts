import { router, usePage } from '@inertiajs/vue3';
import { updateCurrentUrlParams } from '@/composables/useQueryUrl';
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

// Gestisce la paginazione usando useQueryUrl per aggiornare l'URL corrente
export const usePagination = () => {
    const page = usePage();

    const getUrlForPage = (pageNumber: number): string => {
        return updateCurrentUrlParams(page.url, { page: pageNumber });
    };

    const onPageChange = (pageNumber: number): void => {
        const url = getUrlForPage(pageNumber);
        router.visit(url);
    };

    const getUrlForPerPage = (perPage: PerPageOption): string => {
        return updateCurrentUrlParams(page.url, { per_page: perPage, page: 1 });
    };

    const onPerPageChange = (perPage: PerPageOption): void => {
        router.visit(getUrlForPerPage(perPage));
    };

    return { getUrlForPage, onPageChange, getUrlForPerPage, onPerPageChange };
};
