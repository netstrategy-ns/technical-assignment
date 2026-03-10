<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import EventCard from '@/components/custom/Cards/EventCard.vue';
import EventFilters from '@/components/custom/Filters/EventFilters.vue';
import PaginationNav from '@/components/custom/Pagination/PaginationNav.vue';
import PerPageSelect from '@/components/custom/Pagination/PerPageSelect.vue';
import { useAuthRedirect } from '@/composables/useAuthRedirect';
import type { EventCardEvent, EventFiltersState } from '@/composables/useEvents';
import type { PerPageOption } from '@/composables/usePagination';
import { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS, usePagination } from '@/composables/usePagination';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const eventsIndex = computed(() => urls.value.eventsIndex ?? '/events');
const { storeCurrent } = useAuthRedirect();
storeCurrent('login');

const props = defineProps<{
    events: {
        data: EventCardEvent[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories?: Array<{ id: number; name: string; slug: string }>;
    filters: EventFiltersState;
    activeCategory?: { id: number; name: string; slug: string } | null;
}>();

const { onPerPageChange } = usePagination();

const effectivePerPage = computed(() => {
    const p = props.events.per_page;
    return PER_PAGE_OPTIONS.includes(p as PerPageOption) ? (p as PerPageOption) : DEFAULT_PER_PAGE;
});
</script>

<template>
    <ApplicationLayout>
        <Head title="Eventi" />
        <div class="w-full px-4 py-8">
            <h1 class="mb-4 text-2xl font-semibold">
                Tutti gli Eventi
            </h1>
            <EventFilters
                :filters="filters"
                :categories="categories ?? []"
                :events-index-url="eventsIndex"
                class="mb-6"
            />
            <div
                v-if="events.total > 0"
                class="mb-4 flex items-center justify-end"
            >
                <PerPageSelect
                    :model-value="effectivePerPage"
                    @update:model-value="(v: number) => onPerPageChange(v as PerPageOption)"
                />
            </div>
            <div v-if="events.data.length === 0" class="rounded-lg border border-sidebar-border/70 bg-card p-8 text-center text-muted-foreground">
                Nessun evento disponibile.
            </div>
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <EventCard
                    v-for="event in events.data"
                    :key="event.id"
                    :event="event"
                    :events-base-url="eventsIndex"
                />
            </div>
            <PaginationNav v-if="events.last_page > 1" :pagination="events" class="mt-8" />
        </div>
    </ApplicationLayout>
</template>
