<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Minus, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useCart, useCartAutoRefresh, useCartExpirationAutoRefresh, useCartHoldExpiredEvent } from '@/composables/useCart';
import { useCartItemsByEvent } from '@/composables/useCartItemsByEvent';
import { useCartQuantityActions } from '@/composables/useCartQuantityActions';
import { useFormatData } from '@/composables/useFormatData';
import { useRemainingTime } from '@/composables/useRemainingTime';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const checkoutUrl = computed(() => urls.value.checkout ?? '/checkout');

const { items, totalItems, totalAmount, isEmpty, remove, update, refresh } = useCart();
useCartAutoRefresh();
useCartExpirationAutoRefresh();
useCartHoldExpiredEvent(refresh);
const { formatPrice } = useFormatData();
const { parseRemainingSeconds, formatRemainingTime } = useRemainingTime();
const itemsByEvent = useCartItemsByEvent({
    items,
    parseRemainingSeconds,
});
const {
    maxReachedMessages,
    actionErrors,
    loadingHolds,
    maxQuantityForHold,
    hasReachedUserLimit,
    decrementQuantity,
    incrementQuantity,
} =
    useCartQuantityActions({
        remove,
        update,
    });

const totalAmountFormatted = computed(() => formatPrice(totalAmount.value));


</script>

<template>
    <ApplicationLayout>
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
    </ApplicationLayout>
</template>
