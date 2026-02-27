<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import HoldCountdown from '@/components/HoldCountdown.vue';
import { Trash2 } from 'lucide-vue-next';
import type { Hold } from '@/types/models';

defineProps<{
    holds: Hold[];
    total: string;
    eventSlug: string;
}>();

const emit = defineEmits<{
    (e: 'remove', holdId: number): void;
}>();
</script>

<template>
    <Card v-if="holds.length > 0">
        <CardHeader>
            <CardTitle>Your Cart</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
            <div
                v-for="hold in holds"
                :key="hold.id"
                class="flex items-start justify-between gap-2"
            >
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">{{ hold.ticket_type?.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ hold.quantity }} x &euro;{{ Number(hold.ticket_type?.price).toFixed(2) }}
                    </p>
                    <HoldCountdown :expires-at="hold.expires_at" class="mt-0.5 text-xs" />
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="text-sm font-medium">
                        &euro;{{
                            (hold.quantity * Number(hold.ticket_type?.price ?? 0)).toFixed(2)
                        }}
                    </span>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="size-8 p-0 text-muted-foreground hover:text-destructive"
                        @click="emit('remove', hold.id)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>
            </div>

            <Separator />

            <div class="flex items-center justify-between font-bold">
                <span>Total</span>
                <span>&euro;{{ Number(total).toFixed(2) }}</span>
            </div>
        </CardContent>
        <CardFooter>
            <Button as-child class="w-full">
                <Link :href="`/events/${eventSlug}/checkout`">Proceed to Checkout</Link>
            </Button>
        </CardFooter>
    </Card>
</template>
