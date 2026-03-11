<script setup lang="ts">
import { toRef } from 'vue';
import type { TableColumn, TableFiltersState } from '@/components/custom/Table/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useTableFilters } from '@/composables/useTable';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        columns: TableColumn[];
        filters: TableFiltersState;
        idPrefix?: string;
        class?: string;
    }>(),
    {
        idPrefix: 'table-filter',
        class: '',
    },
);

const emit = defineEmits<{
    'update:modelValue': [filters: TableFiltersState];
}>();

const {
    localFilters,
    filterableColumns,
    onInput,
    onInputAndCommit,
    commitFilters,
    resetFilters,
    isSelectColumn,
} = useTableFilters(toRef(props, 'columns'), toRef(props, 'filters'), {
    emit: (nextFilters) => emit('update:modelValue', nextFilters),
});

const getInputModelValue = (column: TableColumn): string | number | undefined => {
    const value = localFilters[column.field_name];

    if (value === null || value === undefined || typeof value === 'boolean') {
        return undefined;
    }

    return value;
};
</script>

<template>
    <form
        class="grid gap-3 border p-6 bg-muted/30 sm:grid-cols-2 lg:grid-cols-4"
        :class="cn('sticky top-0 z-10', props.class)"
        @submit.prevent
    >
        <div
            v-for="column in filterableColumns"
            :key="column.field_name"
            class="space-y-1"
        >
            <Label :for="`${props.idPrefix}-${column.field_name}`">
                {{ column.label }}
            </Label>

            <Input
                v-if="column.input_type === 'text' || column.input_type === 'datetime-local' || column.input_type === 'date' || !column.input_type"
                :id="`${props.idPrefix}-${column.field_name}`"
                :type="column.input_type ?? 'text'"
                :placeholder="column.placeholder ?? ''"
                class="h-9 w-full"
    :model-value="getInputModelValue(column)"
                @update:model-value="onInput(column, $event)"
                @blur="commitFilters"
                @change="commitFilters"
                @keydown.enter.prevent="commitFilters"
            />

            <Input
                v-if="column.input_type === 'number'"
                :id="`${props.idPrefix}-${column.field_name}`"
                type="number"
                :placeholder="column.placeholder ?? ''"
                class="h-9 w-full"
                :model-value="getInputModelValue(column)"
                step="1"
                @update:model-value="onInput(column, $event)"
                @blur="commitFilters"
                @change="commitFilters"
                @keydown.enter.prevent="commitFilters"
            />

            <select
                v-if="isSelectColumn(column)"
                :id="`${props.idPrefix}-${column.field_name}`"
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-left text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                :class="
                    getInputModelValue(column) === '' ||
                    getInputModelValue(column) === undefined ||
                    getInputModelValue(column) === null
                        ? 'text-muted-foreground'
                        : 'text-foreground'
                "
                :value="getInputModelValue(column)"
                @change="onInput(column, ($event.target as HTMLSelectElement).value)"
                @blur="commitFilters"
            >
                <option value="" hidden>
                    {{ column.placeholder ?? 'Seleziona' }}
                </option>
                <template v-if="(column.options?.length ?? 0) > 0">
                    <option
                        v-for="option in column.options"
                        :key="`${String(option.value)}-${option.label}`"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </template>
                <option v-else value="" disabled>
                    Nessun elemento disponibile
                </option>
            </select>

            <div
                v-if="column.input_type === 'checkbox'"
                class="flex items-center gap-2 pt-2"
            >
                <Switch
                    :id="`${props.idPrefix}-${column.field_name}`"
                    :model-value="Boolean(localFilters[column.field_name])"
                    @update:model-value="onInputAndCommit(column, $event)"
                />
                <Label :for="`${props.idPrefix}-${column.field_name}`">Solo valori attivi</Label>
            </div>
        </div>

        <div class="col-span-full mt-1">
            <Button type="button" size="sm" variant="outline" @click="resetFilters">
                Reset filtri
            </Button>
        </div>
    </form>
</template>
