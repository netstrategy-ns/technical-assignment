<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useFormatData } from "@/composables/useFormatData";
import ApplicationLayout from "@/layouts/ApplicationLayout.vue";
import type { OrderListItem } from '@/types/models/order';

defineProps<{
  orders: OrderListItem[];
  totalOrders: number;
}>();

const { formatPrice, statusLabel, formatDate } = useFormatData();
</script>

<template>
  <ApplicationLayout>
    <Head title="I miei ordini" />
    <div class="w-full px-4 py-8">
      <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-semibold">I miei ordini</h1>
        <p class="mt-2 text-sm text-muted-foreground">Totale ordini: {{ totalOrders }}</p>

        <p
          v-if="orders.length === 0"
          class="mt-8 rounded-xl border border-sidebar-border/70 bg-card p-8 text-center text-muted-foreground"
        >
          Nessun ordine presente.
        </p>

        <ul v-else class="mt-6 space-y-4">
          <li
            v-for="order in orders"
            :key="order.public_id ?? order.id"
            class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-5"
          >
            <div
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex flex-col gap-3 text-sm">
                <div class="text-sm text-muted-foreground">
                  Ordine
                  <span
                    class="text-xs font-medium text-white rounded-full px-2 py-1"
                    :class="
                      order.status === 'completed'
                        ? 'bg-green-600'
                        : order.status === 'pending'
                        ? 'bg-yellow-500'
                        : 'bg-red-500'
                    "
                    >{{ statusLabel(order.status) }}</span
                  >
                </div>
                <span class="text-sm text-muted-foreground">
                  Nr:
                  <span class="font-medium text-foreground">{{
                    order.public_id ?? `#${order.id}`
                  }}</span>
                </span>
                <span class="text-sm text-muted-foreground">
                  Data Acquisto:
                  <span class="font-medium text-foreground">{{
                    formatDate(order.created_at)
                  }}</span></span
                >
              </div>
              <p class="text-right text-lg font-semibold">
                {{ formatPrice(order.total_amount) }}
              </p>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm"></div>
            <div class="mt-4 flex justify-end">
              <Link
                v-if="order.public_id"
                :href="`/orders/${order.public_id}`"
                class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 text-sm"
              >
                Vedi dettaglio ordine
              </Link>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </ApplicationLayout>
</template>
