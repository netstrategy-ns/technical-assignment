<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { Order } from '@/types/models';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    order: Order;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'My Orders', href: '/orders' },
    { title: `Order #${props.order.id}` },
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
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex max-w-2xl flex-col gap-6 p-4">
            <FlashMessages />

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Order #{{ order.id }}</h1>
                    <p class="text-muted-foreground">{{ formatDate(order.created_at) }}</p>
                </div>
                <Badge
                    :variant="order.status === 'confirmed' ? 'default' : 'destructive'"
                    class="capitalize"
                >
                    {{ order.status }}
                </Badge>
            </div>

            <!-- Event info -->
            <Card v-if="order.event">
                <CardHeader>
                    <CardTitle>Event</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="font-semibold">{{ order.event.title }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ order.event.venue }}, {{ order.event.city }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDate(order.event.starts_at) }}
                    </p>
                </CardContent>
            </Card>

            <!-- Order items -->
            <Card>
                <CardHeader>
                    <CardTitle>Tickets</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between"
                    >
                        <div>
                            <p class="font-medium">{{ item.ticket_type?.name ?? 'Ticket' }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ item.quantity }} x &euro;{{ Number(item.unit_price).toFixed(2) }}
                            </p>
                        </div>
                        <span class="font-medium">
                            &euro;{{ (item.quantity * Number(item.unit_price)).toFixed(2) }}
                        </span>
                    </div>

                    <Separator />

                    <div class="flex items-center justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>&euro;{{ Number(order.total_amount).toFixed(2) }}</span>
                    </div>
                </CardContent>
            </Card>

            <Button as-child variant="outline">
                <Link href="/orders">Back to Orders</Link>
            </Button>
        </div>
    </AppLayout>
</template>
