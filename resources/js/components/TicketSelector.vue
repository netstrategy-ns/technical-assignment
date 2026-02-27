<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { TicketTypeAvailability } from '@/types/models';

defineProps<{
    ticketTypes: TicketTypeAvailability[];
    saleStarted: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'hold', ticketTypeId: number, quantity: number): void;
}>();

const quantities = ref<Record<number, number>>({});

function getQuantity(ticketTypeId: number): number {
    return quantities.value[ticketTypeId] ?? 1;
}

function setQuantity(ticketTypeId: number, value: number) {
    quantities.value[ticketTypeId] = value;
}

function handleReserve(tt: TicketTypeAvailability) {
    const qty = getQuantity(tt.ticket_type_id);
    if (qty > 0 && qty <= Math.min(tt.per_user_limit - tt.user_held, tt.available)) {
        emit('hold', tt.ticket_type_id, qty);
    }
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="tt in ticketTypes"
            :key="tt.ticket_type_id"
            class="flex items-center justify-between gap-4 rounded-lg border p-3"
        >
            <div class="min-w-0 flex-1">
                <p class="font-medium">{{ tt.name }}</p>
                <p class="text-sm text-muted-foreground">&euro;{{ Number(tt.price).toFixed(2) }}</p>
                <div class="mt-1 flex flex-wrap gap-1">
                    <Badge v-if="tt.available > 0" variant="secondary" class="text-xs">
                        {{ tt.available }} available
                    </Badge>
                    <Badge v-else variant="destructive" class="text-xs">Sold Out</Badge>
                    <Badge
                        v-if="tt.user_held > 0"
                        variant="default"
                        class="text-xs"
                    >
                        {{ tt.user_held }} in cart
                    </Badge>
                </div>
            </div>

            <div
                v-if="saleStarted && tt.available > 0 && !disabled && tt.user_held < tt.per_user_limit"
                class="flex shrink-0 items-center gap-2"
            >
                <Label :for="`qty-${tt.ticket_type_id}`" class="sr-only">Quantity</Label>
                <Input
                    :id="`qty-${tt.ticket_type_id}`"
                    type="number"
                    :min="1"
                    :max="Math.min(tt.per_user_limit - tt.user_held, tt.available)"
                    :model-value="getQuantity(tt.ticket_type_id)"
                    class="w-16 text-center"
                    @update:model-value="(v: string | number) => setQuantity(tt.ticket_type_id, Number(v))"
                />
                <Button size="sm" @click="handleReserve(tt)">Reserve</Button>
            </div>
        </div>

        <p v-if="disabled" class="text-center text-sm text-muted-foreground">
            You must have queue access to reserve tickets.
        </p>
    </div>
</template>
