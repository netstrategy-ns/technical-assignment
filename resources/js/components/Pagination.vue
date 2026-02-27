<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-center gap-1">
        <template v-for="link in links" :key="link.label">
            <Button
                v-if="link.url"
                :as-child="true"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
            >
                <Link :href="link.url" preserve-scroll v-html="link.label" />
            </Button>
            <Button
                v-else
                variant="outline"
                size="sm"
                disabled
                v-html="link.label"
            />
        </template>
    </nav>
</template>
