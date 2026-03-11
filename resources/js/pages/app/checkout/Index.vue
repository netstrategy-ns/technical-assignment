<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useCheckout } from '@/composables/useCheckout';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';

const {
    cart,
    urls,
    errorMessage,
    isSubmitting,
    loadingText,
    itemsByEvent,
    totalAmountFormatted,
    formatPrice,
    confirmCheckout,
} = useCheckout();
</script>

<template>
    <ApplicationLayout>
        <Head title="Checkout" />
        <div class="w-full px-4 py-8">
            <div class="mx-auto max-w-3xl">
                <h1 class="text-2xl font-semibold">Checkout</h1>

                <p v-if="errorMessage" class="mt-3 rounded-lg border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive">
                    {{ errorMessage }}
                </p>

                <template v-if="cart.isEmpty.value">
                    <div class="mt-8 rounded-xl border border-sidebar-border/70 bg-card p-8 text-center text-muted-foreground">
                        <p>Nessun biglietto nel carrello.</p>
                        <Link
                            :href="urls.cart ?? '/cart'"
                            class="mt-4 inline-block text-primary underline-offset-4 hover:underline"
                        >
                            Torna al carrello
                        </Link>
                    </div>
                </template>

                <template v-else>
                    <div class="mt-6 space-y-6">
                        <section
                            v-for="group in itemsByEvent"
                            :key="group.event.id"
                            class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                        >
                            <h2 class="text-lg font-medium">
                                {{ group.event.title }}
                            </h2>
                            <Link
                                :href="`/events/${group.event.slug}`"
                                class="text-sm text-primary underline-offset-4 hover:underline"
                            >
                                Dettaglio evento
                            </Link>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li
                                    v-for="line in group.lines"
                                    :key="line.holdId"
                                    class="flex items-center justify-between border-b border-sidebar-border/50 pb-2"
                                >
                                    <span>
                                        {{ line.ticketTypeName }} x {{ line.quantity }}
                                    </span>
                                    <span class="text-right">
                                        <span>Prezzo singolo: {{ formatPrice(line.price) }}</span>
                                        <span class="font-medium block">Subtotale: {{ formatPrice(line.subtotal) }}</span>
                                </span>
                                </li>
                            </ul>
                            <div class="flex justify-end mt-4 text-primary font-bold">
                                Totale evento: {{ formatPrice(group.eventTotal) }}
                            </div>
                        </section>

                        <section class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-lg font-semibold">Totale</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ cart.totalItems.value }} biglietto/i
                                    </p>
                                </div>
                                <p class="text-2xl font-semibold">{{ totalAmountFormatted }}</p>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <Link
                                    :href="urls.cart ?? '/cart'"
                                    class="inline-block rounded-md border border-sidebar-border/80 px-4 py-2 text-sm font-medium transition-colors hover:bg-accent/50"
                                >
                                    Torna al carrello
                                </Link>
                                <Button
                                    variant="default"
                                    class="px-4"
                                    :disabled="isSubmitting"
                                    @click="confirmCheckout"
                                >
                                    {{ loadingText }}
                                </Button>
                            </div>
                        </section>
                    </div>
                </template>
            </div>
        </div>
    </ApplicationLayout>
</template>
