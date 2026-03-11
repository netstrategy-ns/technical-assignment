<script setup lang="ts">
import { computed } from 'vue';
import TableBody from '@/components/custom/Table/Partials/TableBody.vue';
import TableFilters from '@/components/custom/Table/Partials/TableFilters.vue';
import TableHead from '@/components/custom/Table/Partials/TableHead.vue';
import type {
    TableColumn,
    TableFiltersState,
    TableSort,
    TableProps,
} from '@/components/custom/Table/types';

const props = withDefaults(
    defineProps<TableProps>(),
    {
        sort: () => ({ field: null, dir: 'asc' }),
        filters: () => ({}),
        emptyMessage: 'Nessun elemento presente.',
        loading: false,
        class: '',
    },
);

const emit = defineEmits<{
    'update:filters': [filters: TableFiltersState];
    'update:sort': [sort: TableSort];
}>();

const safeColumns = computed<TableColumn[]>(() => Array.isArray(props.columns) ? props.columns : []);
const safeRows = computed<Record<string, unknown>[]>(() => Array.isArray(props.rows) ? props.rows : []);
const safeSort = computed<TableSort>(() => props.sort ?? { field: null, dir: 'asc' });
const hasRows = computed(() => safeRows.value.length > 0);
const hasActions = computed(() => props.hasActions ?? false);

const onSortUpdate = (sort: TableSort): void => {
    emit('update:sort', sort);
};

const onFilterUpdate = (filters: TableFiltersState): void => {
    emit('update:filters', filters);
};
</script>

<template>
    <div class="flex flex-col gap-6 bg-white p-6" :class="props.class">
        <header v-if="props.title" class="px-3 pb-3">
            <h3 class="text-lg font-semibold">{{ title }}</h3>
            <p class="text-sm text-muted-foreground">{{ safeRows.length }} risultati</p>
        </header>

        <TableFilters
            :columns="safeColumns"
            :filters="filters ?? {}"
            @update:modelValue="onFilterUpdate"
        />

        <slot name="controls-top">
            <slot name="controls" />
        </slot>

        <div class="overflow-x-auto">
            <table class="w-max min-w-full table-auto border p-6">
                <TableHead
                    :columns="safeColumns"
                    :sort="safeSort"
                    :has-actions="hasActions"
                    @update:sort="onSortUpdate"
                />
                <TableBody
                    :columns="safeColumns"
                    :rows="safeRows"
                    :has-actions="hasActions"
                    :loading="loading"
                >
                    <template v-if="$slots.row" #row="slotProps">
                        <slot name="row" v-bind="slotProps" />
                    </template>

                    <template #actions="props">
                        <slot name="actions" v-bind="props" />
                    </template>
                </TableBody>
            </table>
        </div>

        <div
            v-if="!loading && !hasRows"
            class="px-3 py-6 text-center text-sm text-muted-foreground"
        >
            {{ emptyMessage }}
        </div>

        <slot name="controls-bottom" />
    </div>
</template>
