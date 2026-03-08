<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useCart } from '@/composables/useCart';
import FrontendLayout from '@/layouts/FrontendLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});

const { items, totalItems, totalAmount, isEmpty, remove } = useCart();

const totalAmountFormatted = computed(() => formatPrice(totalAmount.value));

const itemsByEvent = computed(() => {
    const byEvent = new Map<
        number,
        {
            eventId: number;
            eventSlug: string;
            eventTitle: string;
            lines: Array<{
                ticketTypeName: string;
                ticketId: number;
                price: string;
                quantity: number;
                subtotal: number;
            }>;
        }
    >();
    for (const item of items.value) {
        let group = byEvent.get(item.eventId);
        if (!group) {
            group = {
                eventId: item.eventId,
                eventSlug: item.eventSlug,
                eventTitle: item.eventTitle,
                lines: [],
            };
            byEvent.set(item.eventId, group);
        }
        const priceNum = parseFloat(item.price);
        group.lines.push({
            ticketTypeName: item.ticketTypeName,
            ticketId: item.ticketId,
            price: item.price,
            quantity: item.quantity,
            subtotal: priceNum * item.quantity,
        });
    }
    return Array.from(byEvent.values());
});

function formatPrice(value: number): string {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}
</script>

<template>
    <FrontendLayout>
        <Head title="Carrello" />
        <div class="w-full px-4 py-8">
            <div class="mx-auto max-w-3xl">
                <h1 class="text-2xl font-semibold">Carrello</h1>

                <div v-if="isEmpty" class="mt-8 rounded-xl border border-sidebar-border/70 bg-card p-8 text-center text-muted-foreground">
                    <p>Il carrello è vuoto.</p>
                    <Link
                        :href="urls.eventsIndex ?? '/events'"
                        class="mt-4 inline-block text-primary underline-offset-4 hover:underline"
                    >
                        Sfoglia gli eventi
                    </Link>
                </div>

                <template v-else>
                    <div class="mt-6 space-y-8">
                        <section
                            v-for="group in itemsByEvent"
                            :key="group.eventId"
                            class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                        >
                            <Link
                                :href="`/events/${group.eventSlug}`"
                                class="text-lg font-medium text-foreground underline-offset-4 hover:underline"
                            >
                                {{ group.eventTitle }}
                            </Link>
                            <p class="mt-1 text-sm text-muted-foreground">
                                <Link
                                    :href="`/events/${group.eventSlug}`"
                                    class="text-primary underline-offset-4 hover:underline"
                                >
                                    Dettaglio evento
                                </Link>
                            </p>
                            <ul class="mt-4 space-y-3 border-t border-sidebar-border/50 pt-4">
                                <li
                                    v-for="line in group.lines"
                                    :key="line.ticketId"
                                    class="flex flex-wrap items-center justify-between gap-2 text-sm"
                                >
                                    <span>
                                        {{ line.ticketTypeName }} — {{ line.quantity }} × {{ formatPrice(parseFloat(line.price)) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ formatPrice(line.subtotal) }}</span>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            aria-label="Rimuovi dal carrello"
                                            @click="remove(group.eventId, line.ticketId)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </li>
                            </ul>
                        </section>
                    </div>

                    <div class="mt-8 flex flex-col items-end gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6">
                        <p class="text-lg font-semibold">
                            Totale: {{ totalAmountFormatted }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ totalItems }} biglietto/i in carrello
                        </p>
                        <!-- In modulo 06: link a checkout -->
                    </div>
                </template>
            </div>
        </div>
    </FrontendLayout>
</template>
