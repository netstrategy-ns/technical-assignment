<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import TicketsCard from "@/components/custom/Cards/TicketsCard.vue";
import {
  useCartExpirationAutoRefresh,
  useCartHoldExpiredEvent,
} from "@/composables/useCart";
import type { QueueEvent, QueueStatus } from "@/composables/useEventQueue";
import { useEventQueue } from "@/composables/useEventQueue";
import FrontendLayout from "@/layouts/FrontendLayout.vue";
import { useAuthRedirect } from '@/composables/useAuthRedirect';

type ShowEvent = QueueEvent;

const props = defineProps<{
  event: ShowEvent;
  saleNotStarted: boolean;
  queueStatus: QueueStatus;
}>();

const page = usePage();
const isAuthenticated = computed(() =>
  Boolean((page.props.auth as { user?: unknown })?.user)
);
const { storeCurrent } = useAuthRedirect();
storeCurrent('login');

const eventData = ref<ShowEvent>(props.event);
const currentEvent = computed(() => eventData.value);

const totalAvailableTickets = computed(() =>
  currentEvent.value.ticket_types.reduce((total, ticketType) => {
    return total + Math.max(0, ticketType.available_quantity);
  }, 0)
);

const {
  queueStatus,
  queueError,
  queueMessage,
  isEnabledInQueue,
  joinDisabled,
  shouldShowJoinAction,
  statusLabel,
  refreshQueueStatus,
  joinQueue,
} = useEventQueue<ShowEvent>({
  event: currentEvent,
  initialQueueStatus: props.queueStatus,
  isAuthenticated,
  onEventUpdated: (updatedEvent) => {
    eventData.value = updatedEvent;
  },
});

const refreshEventAndCart = (): void => {
  void refreshQueueStatus();
};

useCartExpirationAutoRefresh();
useCartHoldExpiredEvent(refreshEventAndCart);
</script>

<template>
  <FrontendLayout>
    <Head :title="currentEvent?.title ?? 'Dettaglio evento'" />
    <div class="w-full px-4 py-8">
      <article v-if="currentEvent" class="w-full">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
          <div class="aspect-video overflow-hidden rounded-xl bg-muted md:min-h-0">
            <img
              v-if="currentEvent.image_url"
              :src="currentEvent.image_url"
              :alt="currentEvent.title"
              class="h-full w-full object-cover"
            />
            <div
              v-else
              class="flex h-full items-center justify-center text-muted-foreground"
            >
              Nessuna immagine
            </div>
          </div>
          <div class="flex flex-col">
            <span
              class=" mb-3 w-fit flex items-center justify-center bg-chart-2 text-sm font-medium text-white px-3 py-1 rounded-full"
              v-if="currentEvent.category"
            >
              {{ currentEvent.category.name }}
            </span>
            <h1 class="text-2xl font-semibold">{{ currentEvent.title }}</h1>
            <p class="mt-2 text-muted-foreground first-letter:uppercase">
              {{
                currentEvent.starts_at
                  ? new Date(currentEvent.starts_at).toLocaleString("it-IT", {
                      dateStyle: "full",
                      timeStyle: "short",
                    })
                  : ""
              }}
              <template v-if="currentEvent.ends_at">
                –
                {{
                  new Date(currentEvent.ends_at).toLocaleString("it-IT", {
                    timeStyle: "short",
                  })
                }}
              </template>
            </p>
            <p class="mt-1 text-sm text-muted-foreground">{{ currentEvent.location ?? "—" }}</p>
            <p v-if="currentEvent.category" class="mt-1 text-sm text-muted-foreground">{{ currentEvent.category.name }}</p>
            <p v-if="currentEvent.venueType" class="text-sm text-muted-foreground">{{ currentEvent.venueType.name }}</p>
            <div
              v-if="currentEvent.description"
              class="mt-4 flex-1 text-foreground"
              v-html="currentEvent.description"
            />
          </div>
        </div>

        <div
          v-if="saleNotStarted"
          class="mt-6 rounded-lg border border-amber-500/50 bg-amber-500/10 p-4 text-amber-800 dark:text-amber-200"
        >
          La vendita non è ancora iniziata. I biglietti non sono acquistabili.
        </div>

        <section
          v-if="isAuthenticated && currentEvent.queue_enabled"
          class="mt-6 space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900"
        >
          <h2
            class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-200"
          >
            Stato accesso coda
          </h2>
          <p class="text-sm text-slate-700 dark:text-slate-200">{{ statusLabel }}</p>
          <p v-if="queueMessage" class="text-xs text-emerald-600">{{ queueMessage }}</p>
          <p v-if="queueError" class="text-xs text-red-600">{{ queueError }}</p>
          <button
            v-if="shouldShowJoinAction"
            :disabled="joinDisabled"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:cursor-not-allowed disabled:opacity-50"
            @click="joinQueue"
          >
            Entra in coda
          </button>
          <p
            v-if="isEnabledInQueue && queueStatus?.enabled_until"
            class="text-xs text-slate-600 dark:text-slate-300"
          >
            Slot valido fino alle
            {{
              new Date(queueStatus.enabled_until).toLocaleTimeString("it-IT", {
                hour: "2-digit",
                minute: "2-digit",
              })
            }}
          </p>
        </section>

        <h2 class="mt-8 text-lg font-semibold">
          Biglietti disponibili: {{ totalAvailableTickets }}
        </h2>
        <TicketsCard
          :event="currentEvent"
          :sale-not-started="saleNotStarted"
          :queue-status="queueStatus"
          :on-cart-updated="refreshQueueStatus"
        />
      </article>
    </div>
  </FrontendLayout>
</template>
