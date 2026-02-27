<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { Order, PaginatedData } from '@/types/models';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    orders: PaginatedData<Order>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/' },
    { title: 'My Orders', href: '/orders' },
];

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="My Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <FlashMessages />

            <div>
                <h1 class="text-2xl font-bold">My Orders</h1>
                <p class="text-muted-foreground">View your past purchases</p>
            </div>

            <div v-if="orders.data.length > 0" class="space-y-4">
                <Card v-for="order in orders.data" :key="order.id">
                    <CardContent class="flex items-center justify-between p-4">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">
                                {{ order.event?.title ?? 'Unknown Event' }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Order #{{ order.id }} &mdash;
                                {{ formatDate(order.created_at) }}
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <Badge
                                    :variant="order.status === 'confirmed' ? 'default' : 'destructive'"
                                    class="text-xs capitalize"
                                >
                                    {{ order.status }}
                                </Badge>
                                <span class="text-sm font-medium">
                                    &euro;{{ Number(order.total_amount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                        <Button as-child variant="outline" size="sm">
                            <Link :href="`/orders/${order.id}`">View</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
            <div v-else class="py-12 text-center text-muted-foreground">
                <p>You haven't placed any orders yet.</p>
                <Button as-child variant="outline" class="mt-4">
                    <Link href="/events">Browse Events</Link>
                </Button>
            </div>

            <Pagination :links="orders.links" />
        </div>
    </AppLayout>
</template>
