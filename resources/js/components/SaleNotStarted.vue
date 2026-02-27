<script setup lang="ts">
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Clock } from 'lucide-vue-next';

const props = defineProps<{
    saleStartsAt: string;
}>();

const formattedDate = new Date(props.saleStartsAt).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});
</script>

<template>
    <Alert>
        <Clock class="size-4" />
        <AlertTitle>Sale Not Yet Open</AlertTitle>
        <AlertDescription>
            Ticket sales for this event will begin on
            <strong>{{ formattedDate }}</strong
            >. Check back then!
        </AlertDescription>
    </Alert>
</template>
