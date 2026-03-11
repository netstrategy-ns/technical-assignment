<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { EventCardEvent } from '@/composables/useEvents';

withDefaults(
    defineProps<{
        event: EventCardEvent;
        /** Base URL per il dettaglio evento (es. /events). Il link sarà base/slug */
        eventsBaseUrl: string;
    }>(),
    { eventsBaseUrl: '/events' },
);
</script>

<template>
    <Link
        :href="`${eventsBaseUrl.replace(/\/$/, '')}/${event.slug}`"
        class="relative flex flex-col overflow-hidden rounded-lg border border-sidebar-border/70 bg-card transition-shadow hover:shadow-md"
    >
        <div class="relative aspect-video shrink-0 bg-muted">
            <span
                v-if="event.is_featured"
                class="absolute right-2 top-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground shadow-sm"
            >
                In evidenza
            </span>
            <img
                v-if="event.image_url"
                :src="event.image_url"
                :alt="event.title"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full items-center justify-center text-muted-foreground text-sm">
                Nessuna immagine
            </div>
        </div>
        <!-- Altezza fissa: stesse righe per tutte le card così l'altezza non si sfasa con il wrap -->
        <div class="flex min-h-[10rem] flex-col p-4">
            <span
                v-if="event.category"
                class="mb-2 inline-block w-fit rounded-full border border-black/20 bg-muted px-2.5 py-0.5 text-xs font-medium text-foreground"
            >
                {{ event.category.name }}
            </span>
            <h2 class="font-semibold text-foreground line-clamp-2">{{ event.title }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ event.starts_at ? new Date(event.starts_at).toLocaleDateString('it-IT', { dateStyle: 'medium' }) : '' }}
            </p>
            <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">{{ event.location ?? '—' }}</p>
        </div>
    </Link>
</template>
