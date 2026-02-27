<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle, AlertCircle, Info } from 'lucide-vue-next';

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string | null; error?: string | null; info?: string | null });
</script>

<template>
    <div v-if="flash?.success || flash?.error || flash?.info" class="space-y-2">
        <Alert v-if="flash?.success">
            <CheckCircle class="size-4" />
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>
        <Alert v-if="flash?.error" variant="destructive">
            <AlertCircle class="size-4" />
            <AlertDescription>{{ flash.error }}</AlertDescription>
        </Alert>
        <Alert v-if="flash?.info">
            <Info class="size-4" />
            <AlertDescription>{{ flash.info }}</AlertDescription>
        </Alert>
    </div>
</template>
