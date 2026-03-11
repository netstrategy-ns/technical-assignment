<script setup lang="ts">
import { ChevronDown, ChevronRight } from "lucide-vue-next";
import { Pencil, RotateCcw, Trash2 } from "lucide-vue-next";
import { ref } from "vue";
import AdminResourceIndexPage from "@/components/admin/AdminResourceIndexPage.vue";
import type {
  TableColumn,
  TableFiltersState,
  TableSort,
} from "@/components/custom/Table/types";
import { formatCellValue, getNestedValue } from "@/components/custom/Table/utils";
import type { BreadcrumbItem } from "@/types";
import type { PaginatedResponse } from "@/types/models/pagination";

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

type OrderItem = {
  id?: string | number | null;
  quantity?: number | string | null;
  unit_price?: number | string | null;
  ticket?: {
    ticket_type?: {
      name?: string | null;
    };
  } | null;
};

type OrderRow = Record<string, unknown> & {
  id?: string | number | null;
  public_id?: string | null;
  order_items?: OrderItem[];
  orderItems?: OrderItem[];
};

const props = defineProps<Props>();
const expandedOrderIds = ref<Set<string>>(new Set());

const asRecord = (row: Record<string, unknown>): OrderRow => row;
const toNumber = (value: unknown): number => {
  const number = Number(value);
  return Number.isFinite(number) ? number : 0;
};
const formatMoney = (value: unknown): string =>
  toNumber(value).toLocaleString("it-IT", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
const getOrderId = (row: OrderRow): string =>
  String((row.id as string | number | null | undefined) ?? row.public_id);

const isOrderExpanded = (row: OrderRow): boolean =>
  expandedOrderIds.value.has(getOrderId(row));

const toggleOrderExpanded = (row: OrderRow): void => {
  const orderId = getOrderId(row);
  const next = new Set(expandedOrderIds.value);

  if (next.has(orderId)) {
    next.delete(orderId);
  } else {
    next.add(orderId);
  }

  expandedOrderIds.value = next;
};

const orderItems = (row: OrderRow): OrderItem[] => {
  const orderItemsValue = row.order_items ?? (row as Record<string, unknown>).orderItems;
  return Array.isArray(orderItemsValue) ? (orderItemsValue as OrderItem[]) : [];
};
const hasOrderChildren = (row: OrderRow): boolean => orderItems(row).length > 0;

const itemSubTotal = (item: OrderItem): number =>
  toNumber(item.quantity) * toNumber(item.unit_price);

const onEditOrderItem = (): void => {};

const onRefundOrderItem = (): void => {};

const onDeleteOrderItem = (): void => {};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Ordini",
    href: "/admin/orders",
  },
];

const orderItemsHeaders = [
  "Tipologia",
  "Quantità",
  "Prezzo unitario",
  "Totale riga",
  "Azioni",
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
    title="Gestione ordini"
    :breadcrumbs="breadcrumbs"
    :has-actions="true"
  >
    <template #row="slotProps">
      <tr
        class="group border-b border-border transition-colors hover:bg-primary/60 hover:text-primary-foreground even:bg-muted/70"
        :class="{ 'cursor-pointer': hasOrderChildren(asRecord(slotProps.row)) }"
        @click="
          hasOrderChildren(asRecord(slotProps.row)) &&
            toggleOrderExpanded(asRecord(slotProps.row))
        "
      >
        <td
          v-if="slotProps.hasActions"
          class="px-3 py-3 text-sm whitespace-nowrap border-r border-border group-hover:text-white"
        >
          <button
            v-if="hasOrderChildren(asRecord(slotProps.row))"
            type="button"
            class="inline-flex items-center rounded p-1 text-muted-foreground hover:text-primary-foreground"
            @click.stop="toggleOrderExpanded(asRecord(slotProps.row))"
            :aria-label="
              isOrderExpanded(asRecord(slotProps.row))
                ? 'Comprimi dettagli ordine'
                : 'Espandi dettagli ordine'
            "
          >
            <ChevronRight
              v-if="!isOrderExpanded(asRecord(slotProps.row))"
              class="size-4"
            />
            <ChevronDown v-else class="size-4" />
          </button>
        </td>
        <td
          v-for="column in slotProps.columns"
          :key="column.field_name"
          class="px-3 py-3 text-sm whitespace-nowrap border-r border-border"
        >
          {{
            formatCellValue(
              getNestedValue(asRecord(slotProps.row), column.field_name),
              column
            )
          }}
        </td>
      </tr>
      <tr
        v-if="
          isOrderExpanded(asRecord(slotProps.row)) &&
          hasOrderChildren(asRecord(slotProps.row))
        "
      >
        <td
          :colspan="
            slotProps.rowColspan ??
            slotProps.columns.length + (slotProps.hasActions ? 1 : 0)
          "
          class="bg-muted/40"
        >
          <div class="px-6 py-3">
            <p class="mb-2 text-sm font-medium">Biglietti ordinati</p>
            <div class="overflow-x-auto">
              <table
                v-if="orderItems(asRecord(slotProps.row)).length > 0"
                class="w-full min-w-max text-left text-sm"
              >
                <thead class="text-xs uppercase text-muted-foreground">
                  <tr>
                    <th
                      v-for="header in orderItemsHeaders"
                      :key="header"
                      class="px-3 py-2 font-medium"
                    >
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in orderItems(asRecord(slotProps.row))"
                    :key="(item.id as string | number | undefined) ?? `order-item-${index}`"
                  >
                    <td class="px-3 py-2 border-r border-border">
                      {{ item?.ticket?.ticket_type?.name || "N/D" }}
                    </td>
                    <td class="px-3 py-2 border-r border-border">
                      {{ item.quantity ?? 0 }}
                    </td>
                    <td class="px-3 py-2 border-r border-border">
                      {{ formatMoney(item.unit_price) }}
                    </td>
                    <td class="px-3 py-2 border-r border-border">
                      {{ formatMoney(itemSubTotal(item)) }}
                    </td>
                    <td class="px-3 py-2 border-r border-border">
                      <div class="flex items-center gap-2">
                        <button
                          type="button"
                          class="inline-flex items-center rounded p-1 text-blue-600 hover:text-blue-800"
                          @click.stop="onEditOrderItem()"
                          title="Modifica"
                          aria-label="Modifica riga ordine"
                        >
                          <Pencil class="size-4" />
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center rounded p-1 text-amber-600 hover:text-amber-800"
                          @click.stop="onRefundOrderItem()"
                          title="Rimborso"
                          aria-label="Rimborso riga ordine"
                        >
                          <RotateCcw class="size-4" />
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center rounded p-1 text-red-600 hover:text-red-800"
                          @click.stop="onDeleteOrderItem()"
                          title="Elimina"
                          aria-label="Elimina riga ordine"
                        >
                          <Trash2 class="size-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-else class="text-sm text-muted-foreground">
                Nessun biglietto associato a questo ordine.
              </div>
            </div>
          </div>
        </td>
      </tr>
    </template>
  </AdminResourceIndexPage>
</template>
