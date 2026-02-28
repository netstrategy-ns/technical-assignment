<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { formatDate } from '@/lib/formatters';

type Order = {
  id: number;
  status: string;
  total_amount: number;
  created_at: string;
};

type Ticket = {
  id: number;
  status: string;
  purchased_at: string;
  event?: { title: string; city: string };
  ticket_type?: { name: string };
};

const orders = ref<Order[]>([]);
const tickets = ref<Ticket[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const loadAccount = async () => {
  loading.value = true;
  error.value = null;
  try {
    const [ordersRes, ticketsRes] = await Promise.all([
      fetch('/account/orders'),
      fetch('/account/tickets'),
    ]);

    if (!ordersRes.ok || !ticketsRes.ok) {
      throw new Error('Errore nel caricamento dell\'area account.');
    }

    orders.value = await ordersRes.json();
    tickets.value = await ticketsRes.json();
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore inatteso.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadAccount);
</script>

<template>
  <Head title="Account" />
  <main class="min-h-screen bg-slate-950 text-slate-100">
    <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
        <div>
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Account</p>
          <h1 class="text-2xl font-semibold">Ordini e biglietti</h1>
        </div>
        <Link href="/" class="rounded-full border border-white/20 px-4 py-2 text-sm hover:border-white/50">
          Torna agli eventi
        </Link>
      </div>
    </header>

    <section class="mx-auto w-full max-w-5xl px-6 py-10">
      <div v-if="loading" class="text-sm text-slate-300">Caricamento...</div>
      <div v-if="error" class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
        {{ error }}
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
          <h3 class="text-lg font-semibold">Ordini</h3>
          <div v-if="orders.length === 0" class="mt-4 text-sm text-slate-300">
            Nessun ordine disponibile.
          </div>
          <div v-else class="mt-4 space-y-3">
            <div
              v-for="order in orders"
              :key="order.id"
              class="rounded-2xl border border-white/10 bg-slate-950/60 p-4"
            >
              <div class="flex items-center justify-between text-sm">
                <span>Ordine #{{ order.id }}</span>
                <span class="uppercase text-lime-300">{{ order.status }}</span>
              </div>
              <p class="mt-2 text-sm text-slate-300">
                Totale: € {{ order.total_amount }} · {{ formatDate(order.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
          <h3 class="text-lg font-semibold">Biglietti</h3>
          <div v-if="tickets.length === 0" class="mt-4 text-sm text-slate-300">
            Nessun biglietto disponibile.
          </div>
          <div v-else class="mt-4 space-y-3">
            <div
              v-for="ticket in tickets"
              :key="ticket.id"
              class="rounded-2xl border border-white/10 bg-slate-950/60 p-4"
            >
              <p class="text-sm font-semibold">{{ ticket.event?.title ?? 'Evento' }}</p>
              <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                {{ ticket.ticket_type?.name ?? 'Ticket' }}
              </p>
              <p class="mt-2 text-sm text-slate-300">
                Stato: {{ ticket.status }} · {{ formatDate(ticket.purchased_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>
