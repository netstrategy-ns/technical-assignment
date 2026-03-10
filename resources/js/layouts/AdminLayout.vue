<script setup lang="ts">
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AdminSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AdminSidebarLayout>
</template>
