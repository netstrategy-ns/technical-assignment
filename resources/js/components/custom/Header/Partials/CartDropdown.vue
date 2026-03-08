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
const checkoutUrl = computed(() => (page.props.urls as Record<string, string>)?.checkout ?? '/checkout');

const { items, totalItems, totalAmount, isEmpty, remove, update } = useCart();
const maxReachedMessages = ref<Record<number, boolean>>({});
const actionErrors = ref<Record<number, string>>({});
const loadingHolds = ref<Record<number, boolean>>({});

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
            event: {
                id: number;
                slug: string;
                title: string;
            };
            eventTotal: number;
            lines: Array<{
                holdId: number;
                ticketId: number;
                ticketTypeName: string;
                price: string;
                maxPerUser: number | null;
                quantity: number;
                lineTotal: number;
                expiresAt: string | null;
                availableQuantity: number;
            }>;
        }
    >();

    for (const item of items.value) {
        const priceNumber = parseFloat(item.ticket.price);
        const lineTotal = priceNumber * item.quantity;

        let group = byEvent.get(item.event.id);
        if (!group) {
            group = {
                event: item.event,
                eventTotal: 0,
                lines: [],
            };
            byEvent.set(item.event.id, group);
        }

        group.lines.push({
            holdId: item.id,
            ticketId: item.ticket.id,
            ticketTypeName: item.ticket_type.name,
            price: item.ticket.price,
            maxPerUser: item.ticket.max_per_user,
            quantity: item.quantity,
            lineTotal,
            expiresAt: item.expires_at,
            availableQuantity: item.ticket.available_quantity,
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

function maxQuantityForHold(availableQuantity: number, maxPerUser: number | null): number {
    if (maxPerUser != null && maxPerUser > 0) {
        return Math.min(availableQuantity, maxPerUser);
    }

    return availableQuantity;
}

function hasReachedUserLimit(quantity: number, maxPerUser: number | null): boolean {
    return maxPerUser != null && maxPerUser > 0 && quantity >= maxPerUser;
}

function decrementQuantity(holdId: number, quantity: number) {
    if (loadingHolds.value[holdId]) {
        return;
    }

    maxReachedMessages.value[holdId] = false;
    actionErrors.value[holdId] = '';

    if (quantity <= 1) {
        loadingHolds.value[holdId] = true;
        remove(holdId, {
            onFinish: () => {
                loadingHolds.value[holdId] = false;
            },
        });
        return;
    }

    loadingHolds.value[holdId] = true;
    update(holdId, quantity - 1, {
        onError: (errors) => {
            actionErrors.value[holdId] = errors.quantity ?? 'Impossibile aggiornare la quantita.';
        },
        onFinish: () => {
            loadingHolds.value[holdId] = false;
        },
    });
}

function incrementQuantity(
    holdId: number,
    quantity: number,
    availableQuantity: number,
    maxPerUser: number | null,
) {
    if (loadingHolds.value[holdId]) {
        return;
    }

    const maxQuantity = maxQuantityForHold(availableQuantity, maxPerUser);

    if (quantity >= maxQuantity) {
        maxReachedMessages.value[holdId] = true;
        return;
    }

    maxReachedMessages.value[holdId] = false;
    actionErrors.value[holdId] = '';
    loadingHolds.value[holdId] = true;

    update(holdId, quantity + 1, {
        onError: (errors) => {
            actionErrors.value[holdId] = errors.quantity ?? 'Impossibile aggiornare la quantita.';
        },
        onFinish: () => {
            loadingHolds.value[holdId] = false;
        },
    });
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
                <div class="space-y-3 px-2 pb-2">
                    <section
                        v-for="eventGroup in itemsByEvent"
                        :key="eventGroup.event.id"
                        class="rounded-lg border border-sidebar-border/50 bg-card/60"
                    >
                        <div class="border-b border-sidebar-border/50 px-3 py-2">
                            <p class="truncate text-sm font-medium" :title="eventGroup.event.title">
                                {{ eventGroup.event.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Totale evento: {{ formatPrice(eventGroup.eventTotal) }}
                            </p>
                        </div>

                        <ul class="divide-y divide-sidebar-border/40">
                            <li
                                v-for="line in eventGroup.lines"
                                :key="line.holdId"
                                class="space-y-2 px-3 py-3 text-sm"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ line.ticketTypeName }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            Prezzo unitario: {{ formatPrice(parseFloat(line.price)) }}
                                        </p>
                                        <p
                                            v-if="line.maxPerUser"
                                            class="text-xs"
                                            :class="hasReachedUserLimit(line.quantity, line.maxPerUser)
                                                ? 'font-medium text-destructive'
                                                : 'text-muted-foreground'"
                                        >
                                            Max per utente: {{ line.maxPerUser }}
                                        </p>
                                        <p v-if="line.expiresAt" class="text-xs text-muted-foreground">
                                            Prenotazione fino alle
                                            {{ new Date(line.expiresAt).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) }}
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Rimuovi dal carrello"
                                        @click="remove(line.holdId)"
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
                                                :disabled="loadingHolds[line.holdId]"
                                                @click="decrementQuantity(line.holdId, line.quantity)"
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
                                                :disabled="loadingHolds[line.holdId] || line.quantity >= maxQuantityForHold(line.availableQuantity, line.maxPerUser)"
                                                @click="incrementQuantity(line.holdId, line.quantity, line.availableQuantity, line.maxPerUser)"
                                            >
                                                <Plus class="size-4" />
                                            </Button>
                                        </div>
                                        <p
                                            v-if="maxReachedMessages[line.holdId]"
                                            class="mt-2 text-xs text-amber-600 dark:text-amber-400"
                                        >
                                            Hai raggiunto il limite massimo per utente.
                                        </p>
                                        <p
                                            v-if="actionErrors[line.holdId]"
                                            class="mt-2 text-xs text-destructive"
                                        >
                                            {{ actionErrors[line.holdId] }}
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
            <div class="space-y-2 border-t border-sidebar-border/50 p-2">
                <Link
                    :href="cartUrl"
                    class="block w-full rounded-md bg-primary px-3 py-2 text-center text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    Vai al carrello
                </Link>
                <Link
                    v-if="!isEmpty"
                    :href="checkoutUrl"
                    class="block w-full rounded-md border border-sidebar-border/70 px-3 py-2 text-center text-sm font-medium text-foreground hover:bg-accent/50"
                >
                    Vai al checkout
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
