<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { computed } from 'vue';
import EventCard from '@/components/custom/Cards/EventCard.vue';
import type { EventCardEvent } from '@/composables/useEvents';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const MAX_SLIDES = 15;

const props = withDefaults(
    defineProps<{
        categoryName: string;
        categorySlug: string;
        events: EventCardEvent[];
        eventsIndexUrl?: string;
        eventsBaseUrl?: string;
    }>(),
    { eventsIndexUrl: '/events', eventsBaseUrl: '/events' },
);

const slidesEvents = computed(() => props.events.slice(0, MAX_SLIDES));
</script>

<template>
    <section class="w-full">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-foreground">{{ categoryName }}</h2>
            <Link
                :href="`${eventsIndexUrl.replace(/\?.*$/, '')}?category=${categorySlug}`"
                class="text-sm font-medium text-primary hover:underline"
            >
                Mostra tutti
            </Link>
        </div>
        <div class="relative">
            <Swiper
                :modules="[Navigation, Pagination]"
                :slides-per-view="4"
                :space-between="24"
                :slides-per-group="1"
                navigation
                :pagination="{ clickable: true }"
                class="category-events-swiper"
                :breakpoints="{
                    320: { slidesPerView: 1 },
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 4 },
                }"
            >
                <SwiperSlide v-for="event in slidesEvents" :key="event.id">
                    <EventCard :event="event" :events-base-url="eventsBaseUrl ?? '/events'" />
                </SwiperSlide>
            </Swiper>
        </div>
    </section>
</template>

<style>
.category-events-swiper .swiper-button-prev,
.category-events-swiper .swiper-button-next {
    color: hsl(var(--primary));
}

/* Nascondi freccia sinistra sulla prima slide e freccia destra sull'ultima */
.category-events-swiper .swiper-button-prev.swiper-button-disabled,
.category-events-swiper .swiper-button-next.swiper-button-disabled {
    display: none;
}

/* Bullet sotto le card: pagination in flow con margine sopra */
.category-events-swiper .swiper-pagination {
    position: relative;
    margin-top: 1rem;
}

.category-events-swiper .swiper-pagination-bullet-active {
    background: hsl(var(--primary));
}

/* Stessa altezza card: le slide si allungano (stretch), la card riempie la slide */
.category-events-swiper .swiper-wrapper {
    align-items: stretch;
}

.category-events-swiper .swiper-slide > * {
    height: 100%;
}
</style>
