<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoryEventsSlider from '@/components/custom/Sliders/CategoryEventsSlider.vue';
import HeroEventsSlider from '@/components/custom/Sliders/HeroEventsSlider.vue';
import FrontendLayout from '@/layouts/FrontendLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});

const eventsIndex = computed(() => urls.value.eventsIndex ?? '/events');

withDefaults(
    defineProps<{
        featuredEvents: Array<{
            id: number;
            slug: string;
            title: string;
            location: string | null;
            image_url: string | null;
            starts_at: string | null;
            category: { id: number; name: string } | null;
            venueType?: { id: number; name: string } | null;
        }>;
        eventsByCategory: Array<{
            category: { id: number; name: string; slug: string };
            events: Array<{
                id: number;
                slug: string;
                title: string;
                location: string | null;
                image_url: string | null;
                starts_at: string | null;
                category: { id: number; name: string } | null;
                venueType?: { id: number; name: string } | null;
            }>;
        }>;
        canRegister: boolean;
    }>(),
    { featuredEvents: () => [], eventsByCategory: () => [], canRegister: true },
);
</script>

<template>
    <FrontendLayout>
        <Head title="Home" />
        <div class="w-full">
            <div v-if="!featuredEvents?.length" class="rounded-lg border border-sidebar-border/70 bg-card p-8 text-center text-muted-foreground">
                Nessun evento in evidenza al momento.
            </div>
            <HeroEventsSlider v-else :events="featuredEvents" :events-base-url="eventsIndex" />

            <template v-for="section in eventsByCategory" :key="section.category.id">
                <section class="py-8 md:px-16">
                    <CategoryEventsSlider
                        :category-name="section.category.name"
                        :category-slug="section.category.slug"
                        :events="section.events"
                        :events-index-url="eventsIndex"
                        :events-base-url="eventsIndex"
                    />
                </section>
            </template>

            <section class="mt-12 py-8 px-4 text-center">
                <Link
                    :href="eventsIndex"
                    class="inline-flex items-center rounded-lg border border-sidebar-border/70 bg-card px-4 py-2 text-sm font-medium hover:bg-muted"
                >
                    Vedi tutti gli eventi
                </Link>
            </section>
        </div>
    </FrontendLayout>
</template>
