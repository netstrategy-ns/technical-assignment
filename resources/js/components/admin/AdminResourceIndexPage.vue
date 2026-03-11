<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { Ban, Pencil, Trash2 } from "lucide-vue-next";
import { computed, toRef, useSlots } from "vue";
import PaginationNav from "@/components/custom/Pagination/PaginationNav.vue";
import PerPageSelect from "@/components/custom/Pagination/PerPageSelect.vue";
import { Table } from "@/components/custom/Table";
import type {
  TableColumn,
  TableSort,
  TableFiltersState,
} from "@/components/custom/Table/types";
import { useTable } from "@/composables/useTable";
import { PER_PAGE_OPTIONS } from "@/constants";
import AdminLayout from "@/layouts/AdminLayout.vue";
import type { BreadcrumbItem } from "@/types";
import type { PaginatedResponse } from "@/types/models/pagination";

type ResourceOption = {
  id: string;
  label: string;
};

interface ResourceIndexProps {
  resource: string;
  resources: ResourceOption[];
  columns: TableColumn[];
  rows: PaginatedResponse<Record<string, unknown>>;
  sort: TableSort;
  filters: TableFiltersState;
  title?: string;
  breadcrumbs?: BreadcrumbItem[];
  hasActions?: boolean;
}

const props = withDefaults(defineProps<ResourceIndexProps>(), {
  title: "Gestione risorse amministrative",
  breadcrumbs: () => [],
  hasActions: true,
});

const slots = useSlots();
const hasActions = computed(() => props.hasActions || slots.actions !== undefined);
const currentResource = computed(() =>
  props.resources.find((resource) => resource.id === props.resource)
);

const {
  tableColumns,
  tableRows,
  tableSort,
  tableFilters,
  tablePagination,
  perPage,
  onFiltersUpdate,
  onSortUpdate,
  onPerPageUpdate,
} = useTable({
  columns: toRef(props, "columns"),
  rows: toRef(props, "rows"),
  sort: toRef(props, "sort"),
  filters: toRef(props, "filters"),
});
</script>

<template>
  <Head :title="props.title" />

  <AdminLayout :breadcrumbs="props.breadcrumbs">
    <div class="space-y-6 bg-gray-200">

      <div class="px-6 py-4 border-b border-border">
        <Table
                class="rounded-none"
          :columns="tableColumns"
          :rows="tableRows"
                :sort="tableSort"
          :filters="tableFilters"
          :has-actions="hasActions"
          :loading="false"
          :title="`Gestione: ${currentResource?.label ?? ''}`"
          @update:filters="onFiltersUpdate"
          @update:sort="onSortUpdate"
        >
          <template #controls>
            <div class="flex items-center justify-end bg-white px-6">
              <PerPageSelect
                v-model="perPage"
                :options="PER_PAGE_OPTIONS"
                label-before="Elementi per pagina"
                @update:modelValue="onPerPageUpdate"
              />
            </div>
          </template>
          <template #actions="slotProps">
            <slot name="actions" v-bind="slotProps">
              <button
                type="button"
                class="transition-colors hover:text-blue-300 cursor-pointer"
                title="Aggiorna"
                aria-label="Aggiorna"
              >
                <Pencil class="size-4" />
              </button>
              <button
                type="button"
                class="ml-2 transition-colors hover:text-amber-500 cursor-pointer"
                title="Disattiva"
                aria-label="Disattiva"
              >
                <Ban class="size-4" />
              </button>
              <button
                type="button"
                class="ml-2 transition-colors hover:text-red-800 cursor-pointer"
                title="Cancella"
                aria-label="Cancella"
              >
                <Trash2 class="size-4" />
              </button>
            </slot>
          </template>
        </Table>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-6">
          <PaginationNav :pagination="tablePagination" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
