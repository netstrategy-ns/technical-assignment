<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCart, useCartExpirationAutoRefresh, useCartHoldExpiredEvent } from '@/composables/useCart';
import { useCartItemsByEvent } from '@/composables/useCartItemsByEvent';
import { useCartQuantityActions } from '@/composables/useCartQuantityActions';
import { useFormatData } from '@/composables/useFormatData';

const page = usePage();
const cartUrl = computed(() => (page.props.urls as Record<string, string>)?.cart ?? '/cart');
const checkoutUrl = computed(() => (page.props.urls as Record<string, string>)?.checkout ?? '/checkout');

const { items, totalItems, totalAmount, isEmpty, remove, update } = useCart();
useCartExpirationAutoRefresh();
const { formatPrice } = useFormatData();
const {
    maxReachedMessages,
    actionErrors,
    loadingHolds,
    maxQuantityForHold,
    hasReachedUserLimit,
    decrementQuantity,
    incrementQuantity,
} = useCartQuantityActions({ remove, update });
const itemsByEvent = useCartItemsByEvent({ items });

const hasCartItems = computed(() => totalItems.value > 0);
const badgeLabel = computed(() =>
    totalItems.value > 99 ? '99+' : String(totalItems.value),
);
const totalAmountFormatted = computed(() => formatPrice(totalAmount.value));

const refreshCartPayload = (): void => {
    router.visit(window.location.href, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        only: ['cart'],
    });
};

useCartHoldExpiredEvent(refreshCartPayload);

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
                                            Prenotazione valida fino alle
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
