<script setup lang="ts">
import AdminResourceIndexPage from '@/components/admin/AdminResourceIndexPage.vue';
import type { TableColumn, TableFiltersState, TableSort } from '@/components/custom/Table/types';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedResponse } from '@/types/models/pagination';

type ResourceOption = {
    id: string;
    label: string;
};

type Props = {
    resource: string;
    resources: ResourceOption[];
    columns: TableColumn[];
    rows: PaginatedResponse<Record<string, unknown>>;
    sort: TableSort;
    filters: TableFiltersState;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventi',
        href: '/admin/events',
    },
];
</script>

<template>
    <AdminResourceIndexPage
        :resource="props.resource"
        :resources="props.resources"
        :columns="props.columns"
        :rows="props.rows"
        :sort="props.sort"
        :filters="props.filters"
        title="Gestione eventi"
        :breadcrumbs="breadcrumbs"
    />
</template>
