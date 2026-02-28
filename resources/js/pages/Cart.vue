<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { csrfToken } from '@/lib/csrf';
import { formatDate } from '@/lib/formatters';

type HoldItem = {
  id: number;
  quantity: number;
  expires_at: string;
  event: { id: number; title: string; city: string };
  ticket_type: { id: number; name: string; price: number };
};

const holds = ref<HoldItem[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const message = ref<string | null>(null);

const fetchHolds = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await fetch('/holds');
    if (!response.ok) {
      throw new Error('Errore nel caricamento delle prenotazioni.');
    }
    holds.value = await response.json();
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore inatteso.';
  } finally {
    loading.value = false;
  }
};

const getIdempotencyKey = (eventId: number) => {
  const storageKey = `idempotency_checkout_${eventId}`;
  const existing = sessionStorage.getItem(storageKey);
  if (existing) return existing;
  const key = crypto.randomUUID();
  sessionStorage.setItem(storageKey, key);
  return key;
};

const groupedByEvent = (items: HoldItem[]) => {
  const map = new Map<number, HoldItem[]>();
  items.forEach((hold) => {
    const group = map.get(hold.event.id) ?? [];
    group.push(hold);
    map.set(hold.event.id, group);
  });
  return Array.from(map.entries());
};

const checkout = async (eventId: number) => {
  message.value = null;
  const response = await fetch('/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
      'Idempotency-Key': getIdempotencyKey(eventId),
    },
    credentials: 'same-origin',
    body: JSON.stringify({ event_id: eventId }),
  });

  if (!response.ok) {
    const payload = await response.json().catch(() => ({}));
    message.value = payload.message ?? 'Checkout non riuscito.';
    return;
  }

  message.value = 'Ordine confermato!';
  sessionStorage.removeItem(`idempotency_checkout_${eventId}`);
  await fetchHolds();
};

onMounted(fetchHolds);
</script>

<template>
  <Head title="Carrello" />
  <main class="min-h-screen bg-slate-950 text-slate-100">
    <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
        <div>
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Carrello</p>
          <h1 class="text-2xl font-semibold">Le tue prenotazioni</h1>
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

      <div v-if="message" class="mb-4 rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-200">
        {{ message }}
      </div>

      <div v-if="!loading && holds.length === 0" class="rounded-2xl border border-white/10 bg-slate-900/60 p-6">
        <p class="text-sm text-slate-300">Non hai prenotazioni attive.</p>
      </div>

      <div class="space-y-6" v-if="holds.length">
        <div
          v-for="[eventId, items] in groupedByEvent(holds)"
          :key="eventId"
          class="rounded-2xl border border-white/10 bg-slate-900/60 p-4"
        >
          <div class="flex items-start justify-between">
            <div>
              <h3 class="text-lg font-semibold">{{ items[0].event.title }}</h3>
              <p class="text-sm text-slate-300">{{ items[0].event.city }}</p>
            </div>
            <button
              class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900"
              @click="checkout(eventId)"
            >
              Concludi pagamento
            </button>
          </div>

          <div class="mt-4 space-y-3">
            <div
              v-for="hold in items"
              :key="hold.id"
              class="rounded-xl border border-white/10 bg-slate-950/60 p-3"
            >
              <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ hold.ticket_type.name }}</p>
              <p class="mt-1 text-sm text-slate-300">
                Quantità: {{ hold.quantity }} · € {{ hold.ticket_type.price }}
              </p>
              <p class="mt-1 text-xs text-slate-400">Scade: {{ formatDate(hold.expires_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>
