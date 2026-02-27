<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import EventCard from '@/components/EventCard.vue';
import EventFilters from '@/components/EventFilters.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import Pagination from '@/components/Pagination.vue';
import { useEventFilters } from '@/composables/useEventFilters';
import type { Category, Event, EventFilters as EventFiltersType, PaginatedData } from '@/types/models';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    events: PaginatedData<Event>;
    categories: Category[];
    filters: EventFiltersType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'Events', href: '/events' },
];

const { filters, apply, reset } = useEventFilters(props.filters);
</script>

<template>
    <Head title="Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <FlashMessages />

            <div>
                <h1 class="text-2xl font-bold">All Events</h1>
                <p class="text-muted-foreground">Find and book tickets for upcoming events</p>
            </div>

            <EventFilters
                :categories="categories"
                :filters="filters"
                @apply="apply"
                @reset="reset"
            />

            <div
                v-if="events.data.length > 0"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <EventCard
                    v-for="event in events.data"
                    :key="event.id"
                    :event="event"
                />
            </div>
            <div v-else class="py-12 text-center text-muted-foreground">
                <p>No events found matching your criteria.</p>
            </div>

            <Pagination :links="events.links" />
        </div>
    </AppLayout>
</template>
