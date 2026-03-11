<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
</script>

<template>
    <Link href="/" class="inline-block transition-opacity hover:opacity-90">
        <AppLogo class="h-8 w-auto" />
    </Link>
</template>
