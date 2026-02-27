<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import TicketSelector from '@/components/TicketSelector.vue';
import CartSummary from '@/components/CartSummary.vue';
import SaleNotStarted from '@/components/SaleNotStarted.vue';
import QueueStatusComponent from '@/components/QueueStatus.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { CalendarDays, ImageIcon, MapPin, Clock } from 'lucide-vue-next';
import { useQueuePolling } from '@/composables/useQueuePolling';
import type { Event, TicketTypeAvailability, Hold, QueueStatus } from '@/types/models';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    event: Event;
    availability: TicketTypeAvailability[];
    userHolds: Hold[];
    cartTotal: string;
    queueStatus: QueueStatus | null;
    saleStarted: boolean;
}>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth.user);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'Events', href: '/events' },
    { title: props.event.title },
];

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

// Queue polling
const { status: queueStatusLive, startPolling } = useQueuePolling(
    props.event.slug,
    props.queueStatus,
);

// Start polling if user is in queue and waiting
if (props.queueStatus?.status === 'waiting') {
    startPolling();
}

// Watch for queue status changes — reload page when activated
watch(
    () => queueStatusLive.value?.status,
    (newStatus, oldStatus) => {
        if (oldStatus === 'waiting' && newStatus === 'active') {
            router.reload();
        }
    },
);

// Determine if ticket selection is allowed
const canSelectTickets = computed(() => {
    if (!isAuthenticated.value) return false;
    if (!props.saleStarted) return false;
    if (!props.event.queue_enabled) return true;
    // For queue events, need active access
    return (
        queueStatusLive.value?.status === 'active' ||
        props.queueStatus?.status === 'active'
    );
});

// Queue: need to join
const needsQueueJoin = computed(() => {
    if (!props.event.queue_enabled) return false;
    if (!isAuthenticated.value) return false;
    if (!props.saleStarted) return false;
    return !props.queueStatus;
});

// Queue: waiting
const isInQueue = computed(() => {
    return props.queueStatus?.status === 'waiting' || queueStatusLive.value?.status === 'waiting';
});

function handleHold(ticketTypeId: number, quantity: number) {
    router.post(
        `/events/${props.event.slug}/holds`,
        { ticket_type_id: ticketTypeId, quantity },
        { preserveScroll: true },
    );
}

function handleRemoveHold(holdId: number) {
    router.delete(`/holds/${holdId}`, { preserveScroll: true });
}

function handleJoinQueue() {
    router.post(`/events/${props.event.slug}/queue`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <FlashMessages />

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Event header -->
                    <div>
                        <div
                            class="mb-4 flex aspect-video items-center justify-center overflow-hidden rounded-xl border bg-muted"
                        >
                            <img
                                v-if="event.image"
                                :src="event.image"
                                :alt="event.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex flex-col items-center justify-center gap-2">
                                <ImageIcon class="size-10 text-muted-foreground/50" />
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <Badge v-if="event.is_featured">Featured</Badge>
                            <Badge v-if="event.category" variant="secondary">{{
                                event.category.name
                            }}</Badge>
                            <Badge v-if="event.queue_enabled" variant="outline">Queue Required</Badge>
                        </div>

                        <h1 class="text-3xl font-bold">{{ event.title }}</h1>

                        <div class="mt-3 space-y-1 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <CalendarDays class="size-4" />
                                <span>{{ formatDate(event.starts_at) }}</span>
                                <span v-if="event.ends_at"> - {{ formatDate(event.ends_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <MapPin class="size-4" />
                                <span>{{ event.venue }}, {{ event.city }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock class="size-4" />
                                <span>Sale starts: {{ formatDate(event.sale_starts_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Description -->
                    <div>
                        <h2 class="mb-2 text-lg font-semibold">About this event</h2>
                        <p class="whitespace-pre-line text-muted-foreground">
                            {{ event.description }}
                        </p>
                    </div>

                    <Separator />

                    <!-- Sale not started -->
                    <SaleNotStarted
                        v-if="!saleStarted"
                        :sale-starts-at="event.sale_starts_at"
                    />

                    <!-- Queue status -->
                    <template v-if="saleStarted && event.queue_enabled && isAuthenticated">
                        <QueueStatusComponent
                            v-if="queueStatusLive"
                            :status="queueStatusLive"
                        />
                        <QueueStatusComponent
                            v-else-if="queueStatus"
                            :status="queueStatus"
                        />
                        <Button
                            v-if="needsQueueJoin"
                            class="w-full"
                            @click="handleJoinQueue"
                        >
                            Join Queue
                        </Button>
                    </template>

                    <!-- Login prompt -->
                    <div
                        v-if="!isAuthenticated && saleStarted"
                        class="rounded-lg border p-4 text-center"
                    >
                        <p class="text-muted-foreground">Please log in to purchase tickets.</p>
                    </div>

                    <!-- Ticket selection -->
                    <Card v-if="saleStarted">
                        <CardHeader>
                            <CardTitle>Tickets</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <TicketSelector
                                :ticket-types="availability"
                                :sale-started="saleStarted"
                                :disabled="!canSelectTickets"
                                @hold="handleHold"
                            />
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar: Cart -->
                <div class="lg:col-span-1">
                    <div class="sticky top-4">
                        <CartSummary
                            v-if="userHolds.length > 0"
                            :holds="userHolds"
                            :total="cartTotal"
                            :event-slug="event.slug"
                            @remove="handleRemoveHold"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
