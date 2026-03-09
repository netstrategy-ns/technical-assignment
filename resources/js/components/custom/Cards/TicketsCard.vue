<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useCart } from '@/composables/useCart';
import { useAuthRedirect } from '@/composables/useAuthRedirect';

type QueueStatus = {
    is_queue_enabled: boolean;
    status: 'waiting' | 'enabled' | 'expired' | 'completed' | null;
    position: number | null;
    estimated_wait_seconds: number | null;
    entered_at: string | null;
    enabled_at: string | null;
    enabled_until: string | null;
};

const page = usePage();
const user = computed(() => (page.props.auth as { user?: unknown })?.user);
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);

const { add, getItemByTicketId, quantityForTicket } = useCart();
const quantities = ref<Record<number, number>>({});
const maxReachedMessages = ref<Record<number, boolean>>({});
const actionErrors = ref<Record<number, string>>({});
const loadingTickets = ref<Record<number, boolean>>({});

const props = defineProps<{
    event: {
        id: number;
        slug: string;
        title: string;
        queue_enabled?: boolean;
        ticket_types: Array<{
            id: number;
            name: string;
            quota_quantity: number;
            available_quantity: number;
            tickets: Array<{
                id: number;
                price: string;
                max_per_user: number | null;
                available_quantity: number;
                user_hold_quantity: number;
            }>;
        }>;
    };
    saleNotStarted: boolean;
    queueStatus: QueueStatus | null;
    onCartUpdated?: () => void | Promise<void>;
}>();

const isQueueEnabledForEvent = computed(() => Boolean(props.event.queue_enabled));
const isQueueBlockingActions = computed(() => isQueueEnabledForEvent.value && props.queueStatus?.status !== 'enabled');

const maxQuantityForTicket = (
    availableForTicket: number,
    maxPerUser: number | null,
    currentHeldQuantity: number,
): number => {
    if (availableForTicket <= 0) return 0;
    if (maxPerUser != null && maxPerUser > 0) {
        return Math.max(0, Math.min(availableForTicket, maxPerUser - currentHeldQuantity));
    }
    return availableForTicket;
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

const getHeldQuantity = (ticketId: number) => {
    return quantityForTicket(ticketId);
};

const getExpiresAtLabel = (ticketId: number) => {
    const expiresAt = getItemByTicketId(ticketId)?.expires_at;

    if (!expiresAt) {
        return null;
    }

    return new Date(expiresAt).toLocaleTimeString('it-IT', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const isAtSelectionMax = (
    ticketId: number,
    availableForTicket: number,
    maxPerUser: number | null,
): boolean => {
    return getSelectedQuantity(ticketId) >= maxQuantityForTicket(
        availableForTicket,
        maxPerUser,
        getHeldQuantity(ticketId),
    );
};

const decrementSelection = (ticketId: number) => {
    quantities.value[ticketId] = Math.max(1, getSelectedQuantity(ticketId) - 1);
    hideMaxReachedMessage(ticketId);
    actionErrors.value[ticketId] = '';
};

const incrementSelection = (
    ticketId: number,
    availableForTicket: number,
    maxPerUser: number | null,
): void => {
    if (isAtSelectionMax(ticketId, availableForTicket, maxPerUser)) {
        showMaxReachedMessage(ticketId);
        return;
    }

    quantities.value[ticketId] = getSelectedQuantity(ticketId) + 1;
    hideMaxReachedMessage(ticketId);
    actionErrors.value[ticketId] = '';
};

const isCartAtMax = (ticketId: number, maxPerUser: number | null): boolean => {
    if (maxPerUser == null || maxPerUser <= 0) {
        return false;
    }

    return getHeldQuantity(ticketId) >= maxPerUser;
};

const hasReachedUserLimit = (ticketId: number, maxPerUser: number | null): boolean => {
    if (maxPerUser == null || maxPerUser <= 0) {
        return false;
    }

    return getHeldQuantity(ticketId) >= maxPerUser;
};

const { storeCurrent } = useAuthRedirect();

const storeAuthRedirectForLogin = (): void => {
    storeCurrent('login');
};

const storeAuthRedirectForRegister = (): void => {
    storeCurrent('register');
};

const addToCart = (
    ticketId: number,
    availableForTicket: number,
    maxPerUser: number | null,
): void => {
    if (loadingTickets.value[ticketId]) {
        return;
    }

    if (isCartAtMax(ticketId, maxPerUser)) {
        showMaxReachedMessage(ticketId);
        return;
    }

    if (isQueueBlockingActions.value) {
        actionErrors.value[ticketId] = 'Entra in coda e attendi l\'abilitazione per acquistare questo evento.';
        return;
    }

    const maxQty = maxQuantityForTicket(availableForTicket, maxPerUser, getHeldQuantity(ticketId));
    const qty = Math.min(maxQty, Math.max(1, getSelectedQuantity(ticketId)));

    if (qty <= 0) {
        showMaxReachedMessage(ticketId);
        return;
    }

    loadingTickets.value[ticketId] = true;
    actionErrors.value[ticketId] = '';

    add(ticketId, qty, {
        onSuccess: () => {
            quantities.value[ticketId] = 1;
            hideMaxReachedMessage(ticketId);
            void props.onCartUpdated?.();
        },
        onError: (errors) => {
            actionErrors.value[ticketId] = errors.quantity ?? errors.ticket_id ?? 'Impossibile aggiungere il biglietto al carrello.';
        },
        onFinish: () => {
            loadingTickets.value[ticketId] = false;
        },
    });
};

const queueBlockMessage = computed(() => {
    if (!isQueueBlockingActions.value || props.queueStatus === null) {
        return '';
    }

    if (props.queueStatus.status === 'waiting') {
        return `In coda: posizione ${props.queueStatus.position ?? '-'} (attesa stimata ${props.queueStatus.estimated_wait_seconds ?? 0}s).`;
    }

    if (props.queueStatus.status === 'expired') {
        return 'Il tuo slot è scaduto. Entra di nuovo in coda.';
    }

    return `Stato coda: ${props.queueStatus.status}.`;
});
</script>

<template>
<section>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="tt in event.ticket_types"
                :key="tt.id"
                class="rounded-xl border border-sidebar-border/70 bg-card p-4"
            >
                <h3 class="text-xl font-bold">{{ tt.name }}</h3>
                <ul class="mt-2 space-y-3">
                    <li
                        v-for="ticket in tt.tickets"
                        :key="ticket.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-sidebar-border/50 p-3 text-sm"
                    >
                        <div class="w-full">
                            <span class="text-xl font-medium">Prezzo: € {{ ticket.price }}</span>
                            <div class="mt-1 flex w-full items-center justify-between text-xs">
                                <span>
                                Disponibili per questo tipo:
                                <strong>{{ tt.available_quantity }}</strong> / <strong>{{ tt.quota_quantity }}</strong>
                                </span>
                                <span
                                    v-if="ticket.max_per_user"
                                    :class="hasReachedUserLimit(ticket.id, ticket.max_per_user)
                                        ? 'font-medium text-destructive'
                                        : 'text-muted-foreground'"
                                >
                                    Max {{ ticket.max_per_user }} per utente
                                </span>
                            </div>
                            <p v-if="getHeldQuantity(ticket.id) > 0" class="mt-1 text-xs text-primary">
                                Nel carrello: {{ getHeldQuantity(ticket.id) }}
                                <template v-if="getExpiresAtLabel(ticket.id)"> scade alle {{ getExpiresAtLabel(ticket.id) }}</template>
                            </p>
                        </div>
                        <template v-if="!saleNotStarted && ticket.available_quantity > 0">
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
                                        :disabled="isAtSelectionMax(ticket.id, ticket.available_quantity, ticket.max_per_user)"
                                        @click="incrementSelection(ticket.id, ticket.available_quantity, ticket.max_per_user)"
                                    >
                                        <Plus class="size-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="secondary"
                                        :disabled="isCartAtMax(ticket.id, ticket.max_per_user) || isQueueBlockingActions"
                                        @click="addToCart(ticket.id, ticket.available_quantity, ticket.max_per_user)"
                                    >
                                        <ShoppingCart class="size-4" />
                                        {{ loadingTickets[ticket.id] ? 'Aggiunta...' : 'Aggiungi al carrello' }}
                                    </Button>
                                </div>
                                <p
                                    v-if="isQueueBlockingActions"
                                    class="mt-2 text-right text-xs text-blue-600 dark:text-blue-300"
                                >
                                    {{ queueBlockMessage }}
                                </p>
                                <p
                                    v-if="maxReachedMessages[ticket.id]"
                                    class="mt-2 text-right text-xs text-amber-600 dark:text-amber-400"
                                >
                                    Hai raggiunto il limite massimo per utente.
                                </p>
                                <p
                                    v-if="actionErrors[ticket.id]"
                                    class="mt-2 text-right text-xs text-destructive"
                                >
                                    {{ actionErrors[ticket.id] }}
                                </p>
                            </div>
                            <span v-else class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Link
                                    href="/login"
                                    class="bg-muted-foreground text-muted rounded-md px-2 py-1"
                                    @click="storeAuthRedirectForLogin"
                                >
                                    Accedi
                                </Link>
                                <template v-if="canRegister">
                                    <Link
                                        href="/register"
                                        class="bg-primary text-primary-foreground rounded-md px-2 py-1"
                                        @click="storeAuthRedirectForRegister"
                                    >
                                        Registrati
                                    </Link>
                                </template>
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
