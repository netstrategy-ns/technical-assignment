<script setup lang="ts">
import { computed, useSlots } from 'vue';
import type { TableColumn } from '@/components/custom/Table/types';
import { getNestedValue, formatCellValue } from '@/components/custom/Table/utils';

const props = defineProps<{
    columns: TableColumn[];
    rows: Record<string, unknown>[];
    hasActions?: boolean;
    loading?: boolean;
}>();

const slots = useSlots();

const hasCustomRow = computed(() => Boolean(slots.row));
const rowColspan = computed(() => props.columns.length + (props.hasActions ? 1 : 0));
</script>

<template>
    <tbody>
        <tr
            v-if="loading"
            class="border-b border-border"
        >
            <td
                :colspan="props.columns.length + (props.hasActions ? 1 : 0)"
                class="px-3 py-8 text-center text-sm text-muted-foreground"
            >
                Caricamento...
            </td>
        </tr>

        <template v-else>
            <template v-for="row in rows" :key="(row.id as number | string | undefined) ?? Math.random().toString(36)">
                <template v-if="hasCustomRow">
                    <slot
                        name="row"
                        :row="row"
                        :columns="columns"
                        :has-actions="props.hasActions"
                        :row-colspan="rowColspan"
                    />
                </template>
                <tr
                    v-else
                    class="group border-b border-border transition-colors hover:bg-primary/60 hover:text-primary-foreground even:bg-muted/70"
                >
                    <td
                        v-if="props.hasActions"
                        class="px-3 py-3 text-sm whitespace-nowrap border-r border-border group-hover:text-white"
                    >
                        <slot name="actions" :row="row" />
                    </td>

                    <td
                        v-for="column in columns"
                        :key="column.field_name"
                        class="px-3 py-3 text-sm whitespace-nowrap border-r border-border"
                    >
                        <slot
                            :name="`cell-${column.field_name}`"
                            :row="row"
                            :value="getNestedValue(row, column.field_name)"
                            :column="column"
                        >
                            {{ formatCellValue(getNestedValue(row, column.field_name), column) }}
                        </slot>
                    </td>
                </tr>
            </template>
        </template>
    </tbody>
</template>
