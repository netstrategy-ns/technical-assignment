import TableBody from '@/components/custom/Table/Partials/TableBody.vue';
import TableFiltersComponent from '@/components/custom/Table/Partials/TableFilters.vue';
import TableHead from '@/components/custom/Table/Partials/TableHead.vue';
import Table from '@/components/custom/Table/Table.vue';

export { Table, TableBody, TableFiltersComponent as TableFilters, TableHead };
export type {
    TableColumn,
    TableColumnInputType,
    TableColumnOption,
    TableFiltersState,
    TableSort,
} from '@/components/custom/Table/types';
