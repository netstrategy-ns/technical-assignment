<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import HoldCountdown from '@/components/HoldCountdown.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { Event, Hold } from '@/types/models';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    event: Event;
    holds: Hold[];
    total: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'Events', href: '/events' },
    { title: props.event.title, href: `/events/${props.event.slug}` },
    { title: 'Checkout' },
];

const isProcessing = ref(false);

// Generate a unique idempotency key for this checkout session
const idempotencyKey = crypto.randomUUID();

function handleCheckout() {
    if (isProcessing.value) return;
    isProcessing.value = true;

    router.post(
        `/events/${props.event.slug}/checkout`,
        { idempotency_key: idempotencyKey },
        {
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex max-w-2xl flex-col gap-6 p-4">
            <FlashMessages />

            <div>
                <h1 class="text-2xl font-bold">Checkout</h1>
                <p class="text-muted-foreground">Review your order for {{ event.title }}</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Order Summary</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-3">
                        <div
                            v-for="hold in holds"
                            :key="hold.id"
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p class="font-medium">{{ hold.ticket_type?.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ hold.quantity }} x &euro;{{
                                        Number(hold.ticket_type?.price).toFixed(2)
                                    }}
                                </p>
                                <div class="mt-0.5 text-xs">
                                    Expires in:
                                    <HoldCountdown :expires-at="hold.expires_at" />
                                </div>
                            </div>
                            <span class="font-medium">
                                &euro;{{
                                    (
                                        hold.quantity * Number(hold.ticket_type?.price ?? 0)
                                    ).toFixed(2)
                                }}
                            </span>
                        </div>
                    </div>

                    <Separator />

                    <div class="flex items-center justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>&euro;{{ Number(total).toFixed(2) }}</span>
                    </div>
                </CardContent>
                <CardFooter class="flex flex-col gap-2">
                    <Button
                        class="w-full"
                        size="lg"
                        :disabled="isProcessing"
                        @click="handleCheckout"
                    >
                        {{ isProcessing ? 'Processing...' : 'Confirm Purchase' }}
                    </Button>
                    <Button as-child variant="ghost" class="w-full">
                        <Link :href="`/events/${event.slug}`">Back to Event</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
