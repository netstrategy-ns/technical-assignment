import { router, usePage } from '@inertiajs/vue3';
import { updateCurrentUrlParams } from '@/composables/useQueryUrl';
import { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS } from '@/constants';
import type { PerPageOption } from '@/types/models/pagination';

export type { PaginationLink, PaginationNavPayload, PaginatedResponse, PerPageOption } from '@/types/models/pagination';

export { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS };

// Gestisce la paginazione usando useQueryUrl per aggiornare l'URL corrente
// Wrapper per calcolo URL di paginazione e navigazione pagina/per_page
export const usePagination = () => {
    const page = usePage();

    // Costruisce URL con parametro page aggiornato
    const getUrlForPage = (pageNumber: number): string => {
        return updateCurrentUrlParams(page.url, { page: pageNumber });
    };

    // Naviga alla pagina selezionata
    const onPageChange = (pageNumber: number): void => {
        const url = getUrlForPage(pageNumber);
        router.visit(url);
    };

    // Costruisce URL con nuovo per_page e reset pagina
    const getUrlForPerPage = (perPage: PerPageOption): string => {
        return updateCurrentUrlParams(page.url, { per_page: perPage, page: 1 });
    };

    // Naviga alla lista con quantità elementi per pagina aggiornata
    const onPerPageChange = (perPage: PerPageOption): void => {
        router.visit(getUrlForPerPage(perPage));
    };

    return { getUrlForPage, onPageChange, getUrlForPerPage, onPerPageChange };
};
