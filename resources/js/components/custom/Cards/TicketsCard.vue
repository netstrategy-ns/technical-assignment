<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useCart } from '@/composables/useCart';

const page = usePage();
const user = computed(() => (page.props.auth as { user?: unknown })?.user);
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);

const { add, items } = useCart();
const quantities = ref<Record<number, number>>({});
const maxReachedMessages = ref<Record<number, boolean>>({});

defineProps<{
    event: {
        id: number;
        slug: string;
        title: string;
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

const maxQuantityForTicket = (
    availableForType: number,
    maxPerUser: number | null,
): number => {
    if (availableForType <= 0) return 0;
    if (maxPerUser != null && maxPerUser > 0) {
        return Math.min(availableForType, maxPerUser);
    }
    return availableForType;
};

const hideMaxReachedMessage = (ticketId: number) => {
    maxReachedMessages.value[ticketId] = false;
};

const showMaxReachedMessage = (ticketId: number) => {
    maxReachedMessages.value[ticketId] = true;
};

const getSelectedQuantity = (ticketId: number) => {
    return quantities.value[ticketId] ?? 1;
};

const isAtSelectionMax = (
    ticketId: number,
    availableForType: number,
    maxPerUser: number | null,
): boolean => {
    if (maxPerUser == null || maxPerUser <= 0) {
        return false;
    }

    return getSelectedQuantity(ticketId) >= maxQuantityForTicket(availableForType, maxPerUser);
};

const decrementSelection = (ticketId: number) => {
    quantities.value[ticketId] = Math.max(1, getSelectedQuantity(ticketId) - 1);
    hideMaxReachedMessage(ticketId);
};

const incrementSelection = (
    ticketId: number,
    availableForType: number,
    maxPerUser: number | null,
): void => {
    if (isAtSelectionMax(ticketId, availableForType, maxPerUser)) {
        showMaxReachedMessage(ticketId);
        return;
    }

    quantities.value[ticketId] = getSelectedQuantity(ticketId) + 1;
    hideMaxReachedMessage(ticketId);
};

const isCartAtMax = (ticketId: number, maxPerUser: number | null): boolean => {
    if (maxPerUser == null || maxPerUser <= 0) {
        return false;
    }

    const existing = items.value.find((item) => item.ticketId === ticketId);

    return existing != null && existing.quantity >= maxPerUser;
};

const addToCart = (
    eventId: number,
    eventSlug: string,
    eventTitle: string,
    ticketTypeId: number,
    ticketTypeName: string,
    ticketId: number,
    price: string,
    availableForType: number,
    maxPerUser: number | null,
): void => {
    if (isCartAtMax(ticketId, maxPerUser)) {
        showMaxReachedMessage(ticketId);
        return;
    }

    const maxQty = maxQuantityForTicket(availableForType, maxPerUser);
    const qty = Math.min(maxQty, Math.max(1, getSelectedQuantity(ticketId)));
    add({
        eventId,
        eventSlug,
        eventTitle,
        ticketTypeId,
        ticketTypeName,
        ticketId,
        price,
        maxPerUser,
        quantity: qty,
    });
    hideMaxReachedMessage(ticketId);
};
</script>

<template>
    <section class="mt-8">
        <h2 class="mb-4 text-lg font-semibold">Biglietti</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="tt in event.ticket_types"
                :key="tt.id"
                class="rounded-xl border border-sidebar-border/70 bg-card p-4"
            >
                <h3 class="text-xl font-bold">{{ tt.name }}</h3>
                <p class="text-sm text-muted-foreground">
                    <template v-if="tt.quota_quantity > 0">
                        Disponibili: <strong>{{ tt.available_quantity }}</strong> su <strong>{{ tt.quota_quantity }}</strong>
                        <template v-if="tt.available_quantity === 0"> (esaurito)</template>
                    </template>
                    <template v-else>
                        Quota non impostata per questa tipologia.
                    </template>
                </p>
                <ul class="mt-2 space-y-3">
                    <li
                        v-for="ticket in tt.tickets"
                        :key="ticket.id"
                        class="flex flex-wrap items-center justify-between gap-2 text-sm"
                    >
                        <span class="text-xl font-medium ">Prezzo: € {{ ticket.price }}</span>
                        <span v-if="ticket.max_per_user">Max {{ ticket.max_per_user }} per utente</span>
                        <template v-if="!saleNotStarted && tt.available_quantity > 0">
                            <div v-if="user" class="w-full sm:w-auto sm:flex-1">
                                <div class="flex items-center gap-2 sm:justify-end">
                                    <Button
                                        variant="outline"
                                        size="icon-sm"
                                        aria-label="Diminuisci quantità"
                                        @click="decrementSelection(ticket.id)"
                                    >
                                        <Minus class="size-4" />
                                    </Button>
                                    <span class="flex h-8 min-w-12 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium">
                                        {{ getSelectedQuantity(ticket.id) }}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="icon-sm"
                                        aria-label="Aumenta quantità"
                                        :disabled="isAtSelectionMax(ticket.id, tt.available_quantity, ticket.max_per_user)"
                                        @click="incrementSelection(ticket.id, tt.available_quantity, ticket.max_per_user)"
                                    >
                                        <Plus class="size-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="secondary"
                                        @click="addToCart(event.id, event.slug, event.title, tt.id, tt.name, ticket.id, ticket.price, tt.available_quantity, ticket.max_per_user)"
                                    >
                                        <ShoppingCart class="size-4" />
                                        Aggiungi al carrello
                                    </Button>
                                </div>
                                <p
                                    v-if="maxReachedMessages[ticket.id]"
                                    class="mt-2 text-right text-xs text-amber-600 dark:text-amber-400"
                                >
                                    Hai raggiunto il limite massimo per utente.
                                </p>
                            </div>
                            <span v-else class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Link href="/login" class="bg-muted-foreground text-muted rounded-md px-2 py-1">Accedi</Link>
                                <template v-if="canRegister"><Link href="/register" class="bg-primary text-primary-foreground rounded-md px-2 py-1">Registrati</Link></template>
                            </span>
                        </template>
                    </li>
                </ul>
                <p v-if="saleNotStarted" class="mt-2 text-sm text-muted-foreground">
                    Aggiungi al carrello disponibile quando la vendita sarà aperta.
                </p>
            </div>
        </div>
    </section>
</template>
