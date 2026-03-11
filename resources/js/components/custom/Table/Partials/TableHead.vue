<script setup lang="ts">
import { ChevronDown, ChevronUp, ChevronsUpDown } from "lucide-vue-next";
import type { TableColumn, TableSort } from "@/components/custom/Table/types";

const props = defineProps<{
  columns: TableColumn[];
  sort: TableSort;
  hasActions?: boolean;
}>();

const emit = defineEmits<{
  "update:sort": [sort: TableSort];
}>();

const onSort = (column: TableColumn): void => {
  if (!column.sortable) {
    return;
  }

  if (props.sort.field !== column.field_name) {
    emit("update:sort", {
      field: column.field_name,
      dir: "asc",
    });
    return;
  }

  if (props.sort.dir === "asc") {
    emit("update:sort", {
      field: column.field_name,
      dir: "desc",
    });
    return;
  }

  emit("update:sort", {
    field: null,
    dir: "asc",
  });
};

const isSorted = (fieldName: string): boolean => props.sort.field === fieldName;
const sortDirection = (fieldName: string): "asc" | "desc" => {
  if (!isSorted(fieldName)) {
    return "asc";
  }

  return props.sort.dir;
};
</script>

<template>
  <thead>
    <tr
      class="border-b border-border text-xs uppercase tracking-wide bg-primary text-primary-foreground"
    >
      <th
        v-if="props.hasActions"
        class="h-10 px-3 text-center font-medium whitespace-nowrap border-r border-border"
      >
        Azioni
      </th>
      <th
        v-for="column in columns"
        :key="column.field_name"
        class="h-10 px-3 text-left font-medium whitespace-nowrap border-r border-border"
      >
        <button
          type="button"
          class="inline-flex items-center gap-1 hover:text-muted-foreground"
          :class="{
            'cursor-pointer': column.sortable,
            'cursor-not-allowed': !column.sortable,
          }"
          :disabled="!column.sortable"
          @click="onSort(column)"
        >
          {{ column.label }}
          <template v-if="column.sortable">
            <span class="text-xs">
              <ChevronUp
                v-if="
                  isSorted(column.field_name) &&
                  sortDirection(column.field_name) === 'asc'
                "
                class="size-3.5"
              />
              <ChevronDown
                v-else-if="
                  isSorted(column.field_name) &&
                  sortDirection(column.field_name) === 'desc'
                "
                class="size-3.5"
              />
              <ChevronsUpDown v-else class="size-3.5" />
            </span>
          </template>
        </button>
      </th>
    </tr>
  </thead>
</template>
