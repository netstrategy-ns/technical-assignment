<script setup lang="ts">
import { useHoldCountdown } from '@/composables/useHoldCountdown';

const props = defineProps<{
    expiresAt: string;
}>();

const { formatted, isExpired } = useHoldCountdown(props.expiresAt);
</script>

<template>
    <span
        :class="[
            'font-mono tabular-nums',
            isExpired ? 'text-destructive' : 'text-orange-600 dark:text-orange-400',
        ]"
    >
        {{ isExpired ? 'Expired' : formatted }}
    </span>
</template>
