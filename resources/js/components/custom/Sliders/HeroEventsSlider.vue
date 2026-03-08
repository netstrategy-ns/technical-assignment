<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Autoplay, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import type { EventCardEvent } from '@/composables/useEvents';
import { buildEventDetailUrl } from '@/composables/useEvents';
import 'swiper/css';
import 'swiper/css/pagination';

withDefaults(
    defineProps<{
        events: EventCardEvent[];
        eventsBaseUrl?: string;
    }>(),
    { eventsBaseUrl: '/events' },
);

const heroBackgroundStyle = (imageUrl: string | null): Record<string, string> => {
    if (!imageUrl) return {};
    return { backgroundImage: `url(${imageUrl})` };
};
</script>

<template>
    <div class="relative w-full">
        <Swiper
            :modules="[Autoplay, Pagination]"
            :slides-per-view="1"
            :space-between="0"
            loop
            :autoplay="{
                delay: 3000,
                disableOnInteraction: false,
            }"
            :pagination="{ clickable: true }"
            class="hero-events-swiper"
        >
            <SwiperSlide v-for="event in events" :key="event.id">
                <Link
                    :href="buildEventDetailUrl(event.slug, eventsBaseUrl)"
                    class="group relative flex min-h-[70vh] w-full items-end justify-center bg-muted"
                >
                    <div
                        v-if="event.image_url"
                        class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                        :style="heroBackgroundStyle(event.image_url)"
                    />
                    <div v-else class="absolute inset-0 bg-linear-to-br from-muted to-muted/80" />
                    <div
                        class="absolute inset-0 bg-linear-to-t from-black/85 via-black/40 to-transparent"
                        aria-hidden
                    />
                    <div class="relative z-10 w-full px-4 pb-16 pt-24 text-center sm:px-8 md:pb-20 md:pt-32">
                        <p
                            v-if="event.category"
                            class="mb-2 text-sm font-medium uppercase tracking-wider text-white/90"
                        >
                            {{ event.category.name }}
                        </p>
                        <h2 class="text-3xl font-bold text-white drop-shadow-lg sm:text-4xl md:text-5xl lg:text-6xl">
                            {{ event.title }}
                        </h2>
                        <p class="mt-3 text-lg text-white/90 sm:text-xl">
                            {{ event.starts_at
                                ? new Date(event.starts_at).toLocaleString('it-IT', { dateStyle: 'full', timeStyle: 'short' })
                                : '' }}
                        </p>
                        <p v-if="event.location" class="mt-1 text-white/80">
                            {{ event.location }}
                        </p>
                        <span
                            class="mt-6 inline-flex items-center rounded-lg bg-white/20 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-colors group-hover:bg-white/30"
                        >
                            Scopri evento →
                        </span>
                    </div>
                </Link>
            </SwiperSlide>
        </Swiper>
    </div>
</template>

<style>
.hero-events-swiper .swiper-pagination-bullet {
    background: rgba(255, 255, 255, 0.5);
}
.hero-events-swiper .swiper-pagination-bullet-active {
    background: white;
}
</style>
