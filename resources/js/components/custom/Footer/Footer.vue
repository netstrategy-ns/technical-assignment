<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appName = computed(() => (page.props.name as string) ?? 'TickMe');
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="min-w-0 shrink-0 border-t border-sidebar-border/80 bg-card px-4 py-6"
        role="contentinfo"
    >
        <div class="flex w-full flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6">
            <span class="text-sm text-muted-foreground">
                © {{ currentYear }}
                <Link href="/" class="font-medium text-foreground hover:underline">{{ appName }}</Link>
            </span>
        </div>
    </footer>
</template>
