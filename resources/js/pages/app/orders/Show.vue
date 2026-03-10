<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useFormatData } from "@/composables/useFormatData";
import ApplicationLayout from "@/layouts/ApplicationLayout.vue";

defineProps<{
  order: {
    id: number;
    status: string;
    total_amount: number | string;
    created_at: string;
    order_items: Array<{
      id: number;
      quantity: number;
      unit_price: string;
      ticket: {
        id: number;
        ticket_type: {
          id: number;
          name: string;
          event: {
            id: number;
            slug: string;
            title: string;
          };
        };
      };
    }>;
  };
}>();

const { formatPrice, statusLabel, formatDate } = useFormatData();

const subtotalByItem = (item: { quantity: number; unit_price: string }) =>
  Number(item.unit_price) * item.quantity;
</script>

<template>
  <ApplicationLayout>
    <Head :title="`Ordine #${order.id}`" />
    <div class="w-full px-4 py-8">
      <div class="mx-auto max-w-3xl">
        <div class="mt-6 flex flex-wrap justify-between items-start">
          <div>
            <h1 class="text-2xl font-semibold">Ordine #{{ order.id }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
              Stato: <span class="font-medium">{{ statusLabel(order.status) }}</span>
            </p>
          </div>
          <div>
            <Link
              href="/orders"
              class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 text-sm"
            >
              Torna alla lista ordini
            </Link>
          </div>
        </div>

        <section
          class="mt-6 rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
        >
          <h2 class="text-lg font-medium">Biglietti acquistati</h2>
          <ul class="mt-4 space-y-3">
            <li
              v-for="item in order.order_items"
              :key="item.id"
              class="rounded-lg border border-sidebar-border/50 bg-card/40 p-3"
            >
              <div class="my-3 flex flex-wrap justify-between gap-4 text-sm">
                <span>Data acquisto: {{ formatDate(order.created_at) }}</span>
                <span
                  >Stato:
                  <span
                    class="font-medium text-white rounded-full px-2 py-1"
                    :class="
                      order.status === 'completed'
                        ? 'bg-green-600'
                        : order.status === 'pending'
                        ? 'bg-yellow-500'
                        : 'bg-red-500'
                    "
                    >{{ statusLabel(order.status) }}</span
                  ></span
                >
              </div>
              <p class="font-medium">{{ item.ticket.ticket_type.name }}</p>
              <div
                class="flex flex-wrap gap-4 md:gap-0 justify-between items-center text-sm text-muted-foreground"
              >
                <div>
                  Evento:
                  <Link
                    :href="`/events/${item.ticket.ticket_type.event.slug}`"
                    class="underline-offset-4 hover:underline"
                  >
                    {{ item.ticket.ticket_type.event.title }}
                  </Link>
                </div>
                <div class="flex justify-end gap-4 text-sm text-primary">
                  <span>Prezzo unitario: {{ formatPrice(item.unit_price) }}</span>
                </div>
              </div>

              <hr class="mt-6 mb-2 border-sidebar-border/70" />
              <div class="mt-2 flex flex-wrap justify-between gap-4 text-sm">
                <span>Quantità: {{ item.quantity }}</span>
                <p class="mt-1 text-sm text-primary font-medium">
                  Subtotale: {{ formatPrice(subtotalByItem(item)) }}
                </p>
              </div>
            </li>
          </ul>
          <div class="mt-6 flex justify-end items-center">
            <p class="text-muted-foreground">
              Importo totale:
              <span class="font-medium text-foreground">{{
                formatPrice(order.total_amount)
              }}</span>
            </p>
          </div>
        </section>
      </div>
    </div>
  </ApplicationLayout>
</template>
