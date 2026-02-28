<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import { formatDate } from '@/lib/formatters';

type EventItem = {
  id: number;
  title: string;
  description: string;
  category: string;
  starts_at: string;
  city: string;
  image_url?: string | null;
  is_featured: boolean;
};

type Filters = {
  search: string;
  category: string;
  city: string;
  date_from: string;
  date_to: string;
  order: string;
};

const featured = ref<EventItem[]>([]);
const events = ref<EventItem[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const page = usePage();
const user = computed(() => page.props?.auth?.user);

const filters = reactive<Filters>({
  search: '',
  category: '',
  city: '',
  date_from: '',
  date_to: '',
  order: 'starts_at_asc',
});

const allCategories = ref<string[]>([]);

const categories = computed(() => {
  if (allCategories.value.length) {
    return allCategories.value;
  }
  const unique = new Set(events.value.map((event) => event.category));
  return Array.from(unique).sort();
});

const hasFilters = computed(() =>
  Object.values(filters).some((value) => value && value !== 'starts_at_asc'),
);


const fetchFeatured = async () => {
  const response = await fetch('/events/featured');
  if (!response.ok) {
    throw new Error('Errore nel caricamento degli eventi in evidenza.');
  }
  featured.value = await response.json();
};

const fetchEvents = async () => {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value) params.set(key, value);
  });

  const response = await fetch(`/events?${params.toString()}`);
  if (!response.ok) {
    throw new Error('Errore nel caricamento degli eventi.');
  }
  events.value = await response.json();

  if (!hasFilters.value) {
    const unique = new Set(events.value.map((event) => event.category));
    allCategories.value = Array.from(unique).sort();
  }
};

const loadData = async () => {
  loading.value = true;
  error.value = null;
  try {
    await Promise.all([fetchFeatured(), fetchEvents()]);
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore inatteso.';
  } finally {
    loading.value = false;
  }
};

const applyFilters = async () => {
  loading.value = true;
  error.value = null;
  try {
    await fetchEvents();
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Errore inatteso.';
  } finally {
    loading.value = false;
  }
};

const resetFilters = async () => {
  filters.search = '';
  filters.category = '';
  filters.city = '';
  filters.date_from = '';
  filters.date_to = '';
  filters.order = 'starts_at_asc';
  await applyFilters();
};

onMounted(loadData);
</script>

<template>
  <Head title="Ticketing" />
  <main class="min-h-screen bg-slate-950 text-slate-100">
    <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-5">
        <div>
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Portale</p>
          <h1 class="text-2xl font-semibold">Ticketing</h1>
        </div>
        <div class="flex items-center gap-3 text-sm">
          <template v-if="user">
            <Link href="/cart" class="rounded-full border border-white/20 px-4 py-2 hover:border-white/50">
              Carrello
            </Link>
            <Link href="/account" class="rounded-full border border-white/20 px-4 py-2 hover:border-white/50">
              Account
            </Link>
          </template>
          <template v-else>
            <Link href="/login" class="rounded-full border border-white/20 px-4 py-2 hover:border-white/50">
              Accedi
            </Link>
            <Link href="/register" class="rounded-full bg-lime-400 px-4 py-2 font-semibold text-slate-900">
              Registrati
            </Link>
          </template>
        </div>
      </div>
    </header>

    <section class="mx-auto w-full max-w-6xl px-6 py-10">
      <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6">
        <h3 class="text-lg font-semibold">Filtra la ricerca</h3>
        <div class="mt-4 grid gap-4 text-sm">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Cerca per titolo o descrizione"
            class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
          />
          <div class="grid gap-4 sm:grid-cols-2">
            <select
              v-model="filters.category"
              class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
            >
              <option value="">Tutte le categorie</option>
              <option v-for="category in categories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
            <input
              v-model="filters.city"
              type="text"
              placeholder="Città"
              class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
            />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
            />
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
            />
          </div>
          <select
            v-model="filters.order"
            class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-slate-100 focus:border-lime-400 focus:outline-none"
          >
            <option value="starts_at_asc">Data più vicina</option>
            <option value="starts_at_desc">Data più lontana</option>
            <option value="created_at_desc">Più recenti</option>
            <option value="featured_first">In evidenza prima</option>
          </select>
          <div class="flex flex-wrap gap-3">
            <button
              class="cursor-pointer rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900"
              @click="applyFilters"
            >
              Applica filtri
            </button>
            <button
              class="cursor-pointer rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 disabled:cursor-not-allowed"
              :disabled="!hasFilters"
              @click="resetFilters"
            >
              Reset
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto w-full max-w-6xl px-6 pb-8">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold">In evidenza</h3>
      </div>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div
          v-for="event in featured"
          :key="event.id"
          class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60"
        >
          <div class="h-36 bg-slate-800">
            <img
              v-if="event.image_url"
              :src="event.image_url"
              :alt="event.title"
              class="h-full w-full object-cover"
            />
          </div>
          <div class="p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-lime-300">
              {{ event.category }}
            </p>
            <h4 class="mt-2 text-lg font-semibold">{{ event.title }}</h4>
            <p class="mt-1 text-sm text-slate-300">{{ event.city }} · {{ formatDate(event.starts_at) }}</p>
            <Link
              :href="`/events/${event.id}`"
              class="mt-3 inline-flex items-center text-sm text-lime-300 hover:text-lime-200"
            >
              Vedi dettagli →
            </Link>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto w-full max-w-6xl px-6 pb-16">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold">Tutti gli eventi</h3>
        <span v-if="loading" class="text-xs text-slate-400">Caricamento...</span>
      </div>

      <p v-if="error" class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
        {{ error }}
      </p>

      <div v-if="!loading" class="mt-4 grid gap-4 md:grid-cols-2">
        <div
          v-for="event in events"
          :key="event.id"
          class="flex gap-4 rounded-2xl border border-white/10 bg-slate-900/60 p-4"
        >
          <div class="h-20 w-20 overflow-hidden rounded-xl bg-slate-800">
            <img
              v-if="event.image_url"
              :src="event.image_url"
              :alt="event.title"
              class="h-full w-full object-cover"
            />
          </div>
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <h4 class="text-lg font-semibold">{{ event.title }}</h4>
              <span v-if="event.is_featured" class="rounded-full bg-lime-400/20 px-2 py-1 text-xs text-lime-200">
                Featured
              </span>
            </div>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ event.category }}</p>
            <p class="mt-2 text-sm text-slate-300">{{ event.city }} · {{ formatDate(event.starts_at) }}</p>
            <p class="mt-2 text-xs text-slate-400 line-clamp-2">{{ event.description }}</p>
            <Link
              :href="`/events/${event.id}`"
              class="mt-3 inline-flex items-center text-sm text-lime-300 hover:text-lime-200"
            >
              Vedi dettagli →
            </Link>
          </div>
        </div>
      </div>

      <div v-if="!loading && events.length === 0" class="mt-6 rounded-2xl border border-white/10 bg-slate-900/60 p-6">
        <p class="text-sm text-slate-300">Nessun evento trovato con questi filtri.</p>
      </div>
    </section>
  </main>
</template>
