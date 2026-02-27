<script setup lang="ts">
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Clock, CheckCircle, XCircle, Users } from 'lucide-vue-next';
import type { QueueStatus } from '@/types/models';

defineProps<{
    status: QueueStatus;
}>();
</script>

<template>
    <Alert>
        <Users v-if="status.status === 'waiting'" class="size-4" />
        <CheckCircle v-else-if="status.status === 'active'" class="size-4" />
        <Clock v-else-if="status.status === 'completed'" class="size-4" />
        <XCircle v-else class="size-4" />

        <AlertTitle class="flex items-center gap-2">
            Queue Status
            <Badge
                :variant="
                    status.status === 'active'
                        ? 'default'
                        : status.status === 'waiting'
                          ? 'secondary'
                          : 'destructive'
                "
                class="text-xs capitalize"
            >
                {{ status.status }}
            </Badge>
        </AlertTitle>
        <AlertDescription>
            <template v-if="status.status === 'waiting'">
                You are in the queue. There
                {{ status.ahead === 1 ? 'is' : 'are' }}
                <strong>{{ status.ahead }}</strong>
                {{ status.ahead === 1 ? 'person' : 'people' }} ahead of you. Please wait...
            </template>
            <template v-else-if="status.status === 'active'">
                It's your turn! You can now reserve tickets.
            </template>
            <template v-else-if="status.status === 'expired'">
                Your queue access has expired. You may rejoin the queue.
            </template>
            <template v-else-if="status.status === 'completed'">
                You have completed your purchase. Thank you!
            </template>
        </AlertDescription>
    </Alert>
</template>
