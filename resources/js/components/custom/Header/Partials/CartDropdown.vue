<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCart } from '@/composables/useCart';

const page = usePage();
const cartUrl = computed(() => (page.props.urls as Record<string, string>)?.cart ?? '/cart');

const { items, totalItems, totalAmount, isEmpty, remove, setQuantity } = useCart();
const maxReachedMessages = ref<Record<string, boolean>>({});

const hasCartItems = computed(() => totalItems.value > 0);
const badgeLabel = computed(() =>
    totalItems.value > 99 ? '99+' : String(totalItems.value),
);
const totalAmountFormatted = computed(() =>
    formatPrice(totalAmount.value),
);
const itemsByEvent = computed(() => {
    const byEvent = new Map<
        number,
        {
            eventId: number;
            eventSlug: string;
            eventTitle: string;
            eventTotal: number;
            lines: Array<{
                ticketId: number;
                ticketTypeName: string;
                price: string;
                maxPerUser: number | null;
                quantity: number;
                lineTotal: number;
            }>;
        }
    >();

    for (const item of items.value) {
        const priceNumber = parseFloat(item.price);
        const lineTotal = priceNumber * item.quantity;

        let group = byEvent.get(item.eventId);
        if (!group) {
            group = {
                eventId: item.eventId,
                eventSlug: item.eventSlug,
                eventTitle: item.eventTitle,
                eventTotal: 0,
                lines: [],
            };
            byEvent.set(item.eventId, group);
        }

        group.lines.push({
            ticketId: item.ticketId,
            ticketTypeName: item.ticketTypeName,
            price: item.price,
            maxPerUser: item.maxPerUser,
            quantity: item.quantity,
            lineTotal,
        });
        group.eventTotal += lineTotal;
    }

    return Array.from(byEvent.values());
});

function formatPrice(value: number): string {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

function decrementQuantity(eventId: number, ticketId: number, quantity: number) {
    if (quantity <= 1) {
        remove(eventId, ticketId);
        return;
    }

    maxReachedMessages.value[`${eventId}-${ticketId}`] = false;
    setQuantity(eventId, ticketId, quantity - 1);
}

function incrementQuantity(
    eventId: number,
    ticketId: number,
    quantity: number,
    maxPerUser: number | null,
) {
    if (maxPerUser != null && quantity >= maxPerUser) {
        maxReachedMessages.value[`${eventId}-${ticketId}`] = true;
        return;
    }

    maxReachedMessages.value[`${eventId}-${ticketId}`] = false;
    setQuantity(eventId, ticketId, quantity + 1);
}

function hasReachedMaxMessage(eventId: number, ticketId: number) {
    return maxReachedMessages.value[`${eventId}-${ticketId}`] === true;
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                aria-label="Apri carrello"
                class="relative"
            >
                <ShoppingCart class="size-5" />
                <span
                    v-if="hasCartItems"
                    class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-medium text-primary-foreground"
                >
                    {{ badgeLabel }}
                </span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80 max-h-[min(70vh,24rem)] overflow-y-auto">
            <div class="p-2 font-medium text-sm">
                Carrello
            </div>
            <template v-if="isEmpty">
                <p class="px-2 py-4 text-sm text-muted-foreground">
                    Nessun biglietto nel carrello.
                </p>
            </template>
            <template v-else>
                <div class="max-h-88 space-y-3 overflow-y-auto px-2 pb-2">
                    <section
                        v-for="eventGroup in itemsByEvent"
                        :key="eventGroup.eventId"
                        class="rounded-lg border border-sidebar-border/50 bg-card/60"
                    >
                        <div class="border-b border-sidebar-border/50 px-3 py-2">
                            <p class="truncate text-sm font-medium" :title="eventGroup.eventTitle">
                                {{ eventGroup.eventTitle }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Totale evento: {{ formatPrice(eventGroup.eventTotal) }}
                            </p>
                        </div>

                        <ul class="divide-y divide-sidebar-border/40">
                            <li
                                v-for="line in eventGroup.lines"
                                :key="line.ticketId"
                                class="space-y-2 px-3 py-3 text-sm"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ line.ticketTypeName }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            Prezzo unitario: {{ formatPrice(parseFloat(line.price)) }}
                                        </p>
                                        <p v-if="line.maxPerUser" class="text-xs text-muted-foreground">
                                            Max per utente: {{ line.maxPerUser }}
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Rimuovi dal carrello"
                                        @click="remove(eventGroup.eventId, line.ticketId)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <Button
                                                variant="outline"
                                                size="icon-sm"
                                                aria-label="Diminuisci quantità"
                                                @click="decrementQuantity(eventGroup.eventId, line.ticketId, line.quantity)"
                                            >
                                                <Minus class="size-4" />
                                            </Button>
                                            <span class="min-w-6 text-center text-sm font-medium">
                                                {{ line.quantity }}
                                            </span>
                                            <Button
                                                variant="outline"
                                                size="icon-sm"
                                                aria-label="Aumenta quantità"
                                                :disabled="line.maxPerUser != null && line.quantity >= line.maxPerUser"
                                                @click="incrementQuantity(eventGroup.eventId, line.ticketId, line.quantity, line.maxPerUser)"
                                            >
                                                <Plus class="size-4" />
                                            </Button>
                                        </div>
                                        <p
                                            v-if="hasReachedMaxMessage(eventGroup.eventId, line.ticketId)"
                                            class="mt-2 text-xs text-amber-600 dark:text-amber-400"
                                        >
                                            Hai raggiunto il limite massimo per utente.
                                        </p>
                                    </div>

                                    <p class="text-sm font-medium">
                                        {{ formatPrice(line.lineTotal) }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>
                <div class="border-t border-sidebar-border/50 px-2 py-2 text-sm font-medium">
                    Totale: {{ totalAmountFormatted }}
                </div>
            </template>
            <div class="border-t border-sidebar-border/50 p-2">
                <Link
                    :href="cartUrl"
                    class="block w-full rounded-md bg-primary px-3 py-2 text-center text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    Vai al carrello
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
