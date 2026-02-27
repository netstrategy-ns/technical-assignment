import type { EventFilters } from '@/types/models';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

export function useEventFilters(initial: EventFilters) {
    const filters = reactive<EventFilters>({ ...initial });

    function apply() {
        const params: Record<string, string | number | boolean> = {};

        if (filters.search) params.search = filters.search;
        if (filters.category_id) params.category_id = filters.category_id;
        if (filters.city) params.city = filters.city;
        if (filters.date_from) params.date_from = filters.date_from;
        if (filters.date_to) params.date_to = filters.date_to;
        if (filters.featured) params.featured = '1';
        if (filters.sort && filters.sort !== 'nearest') params.sort = filters.sort;

        router.get('/events', params, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function reset() {
        filters.search = '';
        filters.category_id = '';
        filters.city = '';
        filters.date_from = '';
        filters.date_to = '';
        filters.featured = false;
        filters.sort = 'nearest';

        router.get('/events', {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    return {
        filters,
        apply,
        reset,
    };
}
