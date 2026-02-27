<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import EventCard from '@/components/EventCard.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import { Button } from '@/components/ui/button';
import type { Event } from '@/types/models';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

defineProps<{
    featuredEvents: Event[];
    upcomingEvents: Event[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <FlashMessages />

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Featured Events</h1>
                    <p class="text-muted-foreground">
                        Discover the hottest upcoming events
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link href="/events">View All Events</Link>
                </Button>
            </div>

            <div
                v-if="featuredEvents.length > 0"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <EventCard
                    v-for="event in featuredEvents"
                    :key="event.id"
                    :event="event"
                />
            </div>
            <div v-else class="py-12 text-center text-muted-foreground">
                <p>No featured events at the moment. Check back soon!</p>
                <Button as-child variant="outline" class="mt-4">
                    <Link href="/events">Browse All Events</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
