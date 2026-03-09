<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Minus, Plus, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useCart, useCartAutoRefresh, useCartExpirationAutoRefresh, useCartHoldExpiredEvent } from '@/composables/useCart';
import FrontendLayout from '@/layouts/FrontendLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const checkoutUrl = computed(() => urls.value.checkout ?? '/checkout');

const { items, totalItems, totalAmount, isEmpty, remove, update, refresh } = useCart();
useCartAutoRefresh();
useCartExpirationAutoRefresh();
useCartHoldExpiredEvent(refresh);
const maxReachedMessages = ref<Record<number, boolean>>({});
const actionErrors = ref<Record<number, string>>({});
const loadingHolds = ref<Record<number, boolean>>({});
const now = ref(Date.now());
let expirationTickerId: number | null = null;

function parseRemainingSeconds(expiresAt: string | null): number {
    if (!expiresAt) {
        return 0;
    }

    const expiresAtMs = Date.parse(expiresAt);
    if (!Number.isFinite(expiresAtMs)) {
        return 0;
    }

    return Math.max(0, Math.floor((expiresAtMs - now.value) / 1000));
}

function formatRemainingTime(totalSeconds: number): string {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

onMounted(() => {
    expirationTickerId = window.setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (expirationTickerId !== null) {
        window.clearInterval(expirationTickerId);
        expirationTickerId = null;
    }
});

const totalAmountFormatted = computed(() => formatPrice(totalAmount.value));

const itemsByEvent = computed(() => {
    const byEvent = new Map<
        number,
        {
            event: {
                id: number;
                slug: string;
                title: string;
            };
            lines: Array<{
                holdId: number;
                ticketTypeName: string;
                ticketId: number;
                price: string;
                maxPerUser: number | null;
                quantity: number;
                subtotal: number;
                expiresAt: string | null;
                availableQuantity: number;
                remainingSeconds: number;
                isExpired: boolean;
            }>;
        }
    >();
    for (const item of items.value) {
        const remainingSeconds = parseRemainingSeconds(item.expires_at);
        let group = byEvent.get(item.event.id);
        if (!group) {
            group = {
                event: item.event,
                lines: [],
            };
            byEvent.set(item.event.id, group);
        }
        const priceNum = parseFloat(item.ticket.price);
        group.lines.push({
            holdId: item.id,
            ticketTypeName: item.ticket_type.name,
            ticketId: item.ticket.id,
            price: item.ticket.price,
            maxPerUser: item.ticket.max_per_user,
            quantity: item.quantity,
            subtotal: priceNum * item.quantity,
            expiresAt: item.expires_at,
            availableQuantity: item.ticket.available_quantity,
            remainingSeconds,
            isExpired: remainingSeconds <= 0,
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

function maxQuantityForHold(availableQuantity: number, maxPerUser: number | null): number {
    if (maxPerUser != null && maxPerUser > 0) {
        return Math.min(availableQuantity, maxPerUser);
    }

    return availableQuantity;
}

function hasReachedUserLimit(quantity: number, maxPerUser: number | null): boolean {
    return maxPerUser != null && maxPerUser > 0 && quantity >= maxPerUser;
}

function decrementQuantity(holdId: number, quantity: number, isExpired: boolean) {
    if (loadingHolds.value[holdId] || isExpired) {
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
    isExpired: boolean,
) {
    if (loadingHolds.value[holdId] || isExpired) {
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
                            :key="group.event.id"
                            class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                        >
                            <Link
                                :href="`/events/${group.event.slug}`"
                                class="text-lg font-medium text-foreground underline-offset-4 hover:underline"
                            >
                                {{ group.event.title }}
                            </Link>
                            <p class="mt-1 text-sm text-muted-foreground">
                                <Link
                                    :href="`/events/${group.event.slug}`"
                                    class="text-primary underline-offset-4 hover:underline"
                                >
                                    Dettaglio evento
                                </Link>
                            </p>
                            <ul class="mt-4 space-y-3 border-t border-sidebar-border/50 pt-4">
                                <li
                                    v-for="line in group.lines"
                                    :key="line.holdId"
                                    class="space-y-3 text-sm"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium">
                                                {{ line.ticketTypeName }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Prezzo unitario: {{ formatPrice(parseFloat(line.price)) }}
                                            </p>
                                            <p
                                                v-if="line.maxPerUser"
                                                class="mt-1 text-xs"
                                                :class="hasReachedUserLimit(line.quantity, line.maxPerUser)
                                                    ? 'font-medium text-destructive'
                                                    : 'text-muted-foreground'"
                                            >
                                                Max per utente: {{ line.maxPerUser }}
                                            </p>
                                            <p v-if="line.expiresAt" class="mt-1 text-xs" :class="line.isExpired ? 'font-medium text-destructive' : 'text-muted-foreground'">
                                                <template v-if="line.isExpired">
                                                    Prenotazione scaduta
                                                </template>
                                                <template v-else>
                                                    Prenotazione valida fino alle
                                                    {{ new Date(line.expiresAt).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) }}
                                                    (scade tra {{ formatRemainingTime(line.remainingSeconds) }})
                                                </template>
                                            </p>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            aria-label="Rimuovi dal carrello"
                                            :disabled="loadingHolds[line.holdId]"
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
                                                    :disabled="loadingHolds[line.holdId] || line.isExpired"
                                                    @click="decrementQuantity(line.holdId, line.quantity, line.isExpired)"
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
                                                    :disabled="loadingHolds[line.holdId] || line.quantity >= maxQuantityForHold(line.availableQuantity, line.maxPerUser) || line.isExpired"
                                                    @click="incrementQuantity(line.holdId, line.quantity, line.availableQuantity, line.maxPerUser, line.isExpired)"
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
                                        <span class="font-medium">{{ formatPrice(line.subtotal) }}</span>
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
                        <Link
                            :href="checkoutUrl"
                            class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Vai al checkout
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </FrontendLayout>
</template>
