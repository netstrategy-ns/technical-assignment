<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FrontendLayout from '@/layouts/FrontendLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});

defineProps<{
    event: {
        id: number;
        slug: string;
        title: string;
        description: string | null;
        location: string | null;
        image_url: string | null;
        starts_at: string | null;
        ends_at: string | null;
        sale_starts_at: string | null;
        category: { id: number; name: string } | null;
        venueType: { id: number; name: string } | null;
        ticket_types: Array<{
            id: number;
            name: string;
            quota_quantity: number;
            available_quantity: number;
            tickets: Array<{
                id: number;
                price: string;
                quantity_total: number | null;
                max_per_user: number | null;
            }>;
        }>;
    };
    saleNotStarted: boolean;
}>();
</script>

<template>
    <FrontendLayout>
        <Head :title="event?.title ?? 'Dettaglio evento'" />
        <div class="w-full px-4 py-8">
            <article v-if="event" class="w-full">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="aspect-video overflow-hidden rounded-xl bg-muted md:min-h-0">
                        <img
                            v-if="event.image_url"
                            :src="event.image_url"
                            :alt="event.title"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                            Nessuna immagine
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-semibold">{{ event.title }}</h1>
                        <p class="mt-2 text-muted-foreground">
                            {{ event.starts_at ? new Date(event.starts_at).toLocaleString('it-IT', { dateStyle: 'full', timeStyle: 'short' }) : '' }}
                            <template v-if="event.ends_at">
                                – {{ new Date(event.ends_at).toLocaleString('it-IT', { timeStyle: 'short' }) }}
                            </template>
                        </p>
                        <p class="mt-1 text-muted-foreground">{{ event.location ?? '—' }}</p>
                        <p v-if="event.category" class="mt-1 text-sm text-muted-foreground">{{ event.category.name }}</p>
                        <p v-if="event.venueType" class="text-sm text-muted-foreground">{{ event.venueType.name }}</p>
                        <div v-if="event.description" class="mt-4 flex-1 text-foreground" v-html="event.description" />
                    </div>
                </div>

                <div v-if="saleNotStarted" class="mt-6 rounded-lg border border-amber-500/50 bg-amber-500/10 p-4 text-amber-800 dark:text-amber-200">
                    La vendita non è ancora iniziata. I biglietti non sono acquistabili.
                </div>

                <section class="mt-8">
                    <h2 class="mb-4 text-lg font-semibold">Biglietti</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="tt in event.ticket_types"
                            :key="tt.id"
                            class="rounded-xl border border-sidebar-border/70 bg-card p-4"
                        >
                            <h3 class="font-medium">{{ tt.name }}</h3>
                            <p class="text-sm text-muted-foreground">
                                <template v-if="tt.quota_quantity > 0">
                                    Disponibili: <strong>{{ tt.available_quantity }}</strong> su <strong>{{ tt.quota_quantity }}</strong>
                                    <template v-if="tt.available_quantity === 0"> (esaurito)</template>
                                </template>
                                <template v-else>
                                    Quota non impostata per questa tipologia.
                                </template>
                            </p>
                            <ul class="mt-2 space-y-2">
                                <li
                                    v-for="ticket in tt.tickets"
                                    :key="ticket.id"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span>Prezzo: € {{ ticket.price }}</span>
                                    <span v-if="ticket.max_per_user">Max {{ ticket.max_per_user }} per utente</span>
                                </li>
                            </ul>
                            <p v-if="saleNotStarted" class="mt-2 text-sm text-muted-foreground">
                                Aggiungi al carrello disponibile quando la vendita sarà aperta.
                            </p>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </FrontendLayout>
</template>
