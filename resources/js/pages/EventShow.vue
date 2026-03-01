<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import { csrfToken } from '@/lib/csrf';
import { formatDate } from '@/lib/formatters';

type TicketType = {
  id: number;
  name: string;
  price: number;
  total_quantity: number;
  available_quantity: number;
};

type EventDetail = {
  id: number;
  title: string;
  description: string;
  category: string;
  starts_at: string;
  ends_at?: string | null;
  venue: string;
  city: string;
  image_url?: string | null;
  is_featured: boolean;
  sales_start_at: string;
  queue_enabled: boolean;
  ticket_types: TicketType[];
};

const page = usePage();
const event = page.props.event as EventDetail;
const queueStatus = ref<string | null>(null);
const queueMessage = ref<string | null>(null);
const quantities = ref<Record<number, number>>({});
const message = ref<string | null>(null);

const canBuy = () => new Date(event.sales_start_at) <= new Date();

let queueInterval: number | null = null;

const loadQueueStatus = async () => {
  queueMessage.value = null;
  try {
    const response = await fetch(`/queue/status?event_id=${event.id}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
    });

    if (response.ok) {
      const payload = await response.json();
      queueStatus.value = payload.status;
      if (queueStatus.value === 'allowed') {
        stopQueuePolling();
      }
      return;
    }

    queueStatus.value = null;
  } catch {
    queueStatus.value = null;
  }
};

const startQueuePolling = () => {
  if (queueInterval) return;
  queueInterval = window.setInterval(() => {
    loadQueueStatus();
  }, 15000);
};

const stopQueuePolling = () => {
  if (!queueInterval) return;
  window.clearInterval(queueInterval);
  queueInterval = null;
};

const enterQueue = async () => {
  queueMessage.value = null;
  try {
    const response = await fetch('/queue/enter', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
      body: JSON.stringify({ event_id: event.id }),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
      queueMessage.value = payload.message ?? 'Impossibile entrare in coda.';
      return;
    }

    queueStatus.value = payload.status;
    queueMessage.value = 'Sei in coda. Attendi che venga abilitato l\'accesso.';
    startQueuePolling();
  } catch {
    queueMessage.value = 'Impossibile entrare in coda.';
  }
};

const reserve = async (ticketTypeId: number) => {
  message.value = null;
  const quantity = quantities.value[ticketTypeId] ?? 1;

  const response = await fetch('/holds', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': csrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include',
    body: JSON.stringify({
      event_id: event.id,
      ticket_type_id: ticketTypeId,
      quantity,
    }),
  });

  if (!response.ok) {
    const payload = await response.json().catch(() => ({}));
    message.value = payload.message ?? 'Impossibile creare la prenotazione.';
    return;
  }

  message.value = 'Prenotazione effettuata. Vai al carrello per concludere il pagamento.';
};

const goToCart = () => {
  window.location.href = '/cart';
};

if (event.queue_enabled) {
  loadQueueStatus();
  startQueuePolling();
}

onUnmounted(() => {
  stopQueuePolling();
});
</script>

<template>
  <Head title="Dettaglio evento" />
  <main class="min-h-screen bg-slate-950 text-slate-100">
    <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
        <div>
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Evento</p>
          <h1 class="text-2xl font-semibold">Dettaglio evento</h1>
        </div>
        <Link href="/" class="rounded-full border border-white/20 px-4 py-2 text-sm hover:border-white/50">
          Torna agli eventi
        </Link>
      </div>
    </header>

    <section class="mx-auto w-full max-w-5xl px-6 py-10">
      <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6">
          <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900/70">
            <div class="h-64 bg-slate-800">
              <img v-if="event.image_url" :src="event.image_url" :alt="event.title" class="h-full w-full object-cover" />
            </div>
            <div class="p-6">
              <p class="text-xs uppercase tracking-[0.2em] text-lime-300">{{ event.category }}</p>
              <h2 class="mt-3 text-3xl font-semibold">{{ event.title }}</h2>
              <p class="mt-2 text-sm text-slate-300">{{ event.description }}</p>
              <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-300">
                <span>{{ event.city }} · {{ event.venue }}</span>
                <span>{{ formatDate(event.starts_at) }}</span>
                <span v-if="event.ends_at">fino a {{ formatDate(event.ends_at) }}</span>
              </div>
            </div>
          </div>

          <div v-if="!canBuy()" class="rounded-2xl border border-amber-400/30 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">
            Le vendite aprono il {{ formatDate(event.sales_start_at) }}.
          </div>

          <div v-if="message" class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-200">
            {{ message }}
          </div>
        </div>

        <aside class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
          <template v-if="event.queue_enabled && queueStatus !== 'allowed'">
            <h3 class="text-lg font-semibold">Questo evento richiede la coda</h3>
            <p class="mt-2 text-sm text-slate-300">
              Per acquistare devi entrare nella coda e attendere l'accesso.
            </p>
            <button
              v-if="queueStatus !== 'waiting'"
              class="mt-4 w-full cursor-pointer rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900"
              @click="enterQueue"
            >
              Entra in coda
            </button>
            <button
              v-else
              class="mt-4 w-full cursor-pointer rounded-full border border-white/20 px-4 py-2 text-sm"
              @click="loadQueueStatus"
            >
              Aggiorna stato
            </button>
            <p v-if="queueMessage" class="mt-4 text-sm text-slate-200">
              {{ queueMessage }}
            </p>
            <p v-if="queueStatus && queueStatus !== 'completed'" class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400">
              Stato: {{ queueStatus }}
            </p>
          </template>

          <template v-else>
            <h3 class="text-lg font-semibold">Biglietti disponibili</h3>
            <div class="mt-4 space-y-4">
              <div
                v-for="ticket in event.ticket_types"
                :key="ticket.id"
                class="rounded-2xl border border-white/10 bg-slate-950/60 p-4"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-semibold">{{ ticket.name }}</p>
                    <p class="text-xs text-slate-400">
                      Disponibili: {{ ticket.available_quantity }} / {{ ticket.total_quantity }}
                    </p>
                  </div>
                  <p class="text-sm font-semibold">€ {{ ticket.price }}</p>
                </div>
                <div class="mt-3 flex items-center gap-3">
                  <input
                    v-model.number="quantities[ticket.id]"
                    type="number"
                    min="1"
                    :max="ticket.available_quantity"
                    class="w-20 rounded-lg border border-white/10 bg-slate-950/60 px-2 py-1 text-sm text-slate-100"
                  />
                  <button
                    class="cursor-pointer rounded-full bg-lime-400 px-3 py-1 text-sm font-semibold text-slate-900 disabled:opacity-40"
                    :disabled="!canBuy() || ticket.available_quantity <= 0"
                    @click="reserve(ticket.id)"
                  >
                    Prenota
                  </button>
                </div>
              </div>
            </div>

            <button
              class="mt-6 w-full cursor-pointer rounded-full border border-white/20 px-4 py-2 text-sm"
              @click="goToCart"
            >
              Vai al carrello
            </button>
          </template>
        </aside>
      </div>
    </section>
  </main>
</template>
