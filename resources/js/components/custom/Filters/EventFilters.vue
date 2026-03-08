<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, toRef } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { EventFiltersState } from '@/composables/useEventFilters';
import {
    buildEventsIndexUrl,
    EVENT_SORT_OPTIONS,
    useEventFiltersPanel,
} from '@/composables/useEventFilters';
import type { EventCategoryOption } from '@/composables/useEvents';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        categories?: EventCategoryOption[];
        eventsIndexUrl?: string;
        filterIdPrefix?: string;
        filters: EventFiltersState;
    }>(),
    {
        categories: () => [],
        eventsIndexUrl: '/events',
        filterIdPrefix: 'event-filter',
    },
);

const filtersRef = toRef(props, 'filters');
const baseUrl = props.eventsIndexUrl ?? '/events';
const searchInputRef = ref<{ $el?: HTMLInputElement } | null>(null);

const {
    search,
    category,
    start_date,
    end_date,
    location,
    availableTickets,
    featured,
    sort,
    currentPerPage,
    applyFilters,
    applyFiltersDebounced,
    resetFilters,
    hasActiveFilters,
    buildQuery,
} = useEventFiltersPanel(baseUrl, filtersRef, {
    preserveState: true,
    debounceMs: 700,
    searchInputRef,
});

const onAvailableTicketsChange = (value: boolean): void => {
    const query = buildQuery();
    query.available_tickets = value;
    const url = buildEventsIndexUrl(baseUrl, query, { perPage: currentPerPage.value });
    router.visit(url);
};

const id = (name: string): string => {
    return props.filterIdPrefix ? `${props.filterIdPrefix}-${name}` : name;
};

const onFeaturedChange = (value: boolean): void => {
    const query = buildQuery();
    query.featured = value;
    const url = buildEventsIndexUrl(baseUrl, query, { perPage: currentPerPage.value });
    router.visit(url);
};
</script>

<template>
    <section
        class="rounded-xl border border-border bg-card p-4 shadow-sm w-full"
        aria-label="Filtra eventi"
    >
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-2">
                <Label :for="id('search')">Cerca per titolo</Label>
                <Input
                    ref="searchInputRef"
                    :id="id('search')"
                    v-model="search"
                    type="search"
                    placeholder="es. Concerto..."
                    autocomplete="off"
                    class="w-full"
                    @input="applyFiltersDebounced"
                    @keydown.enter="applyFilters"
                />
            </div>
            <div v-if="categories.length" class="space-y-2">
                <Label :for="id('category')">Categoria</Label>
                <select
                    :id="id('category')"
                    v-model="category"
                    :class="cn(
                        'h-9 w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-1 text-base shadow-xs outline-none transition-colors',
                        'focus-visible:ring-2 focus-visible:ring-primary md:text-sm',
                    )"
                    @change="applyFilters"
                >
                    <option value="">Tutte le categorie</option>
                    <option
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.slug"
                    >
                        {{ cat.name }}
                    </option>
                </select>
            </div>
            <div class="space-y-2">
                <Label :for="id('start_date')">Data da</Label>
                <Input
                    :id="id('start_date')"
                    v-model="start_date"
                    type="date"
                    class="w-full"
                    @change="applyFilters"
                    @keydown.enter="applyFilters"
                />
            </div>
            <div class="space-y-2">
                <Label :for="id('end_date')">Data a</Label>
                <Input
                    :id="id('end_date')"
                    v-model="end_date"
                    type="date"
                    class="w-full"
                    @change="applyFilters"
                    @keydown.enter="applyFilters"
                />
            </div>
            <div class="space-y-2">
                <Label :for="id('location')">Luogo</Label>
                <Input
                    :id="id('location')"
                    v-model="location"
                    type="text"
                    placeholder="es. Milano"
                    class="w-full"
                    @keyup.enter="applyFilters"
                    @blur="applyFilters"
                />
            </div>
            <div class="space-y-2">
                <Label :for="id('sort')">Ordina per</Label>
                <select
                    :id="id('sort')"
                    v-model="sort"
                    :class="cn(
                        'h-9 w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-1 text-base shadow-xs outline-none transition-colors',
                        'focus-visible:ring-2 focus-visible:ring-primary md:text-sm',
                    )"
                    @change="applyFilters"
                >
                    <option
                        v-for="opt in EVENT_SORT_OPTIONS"
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>
            </div>
            <div class="flex items-center gap-2 pt-8">
                <Switch
                    :id="id('featured')"
                    :model-value="featured"
                    @update:model-value="onFeaturedChange"
                />
                <Label :for="id('featured')" class=" cursor-pointer font-normal">
                    Solo in evidenza
                </Label>
            </div>
            <div class="flex items-center gap-2 pt-8">
                <Switch
                    :id="id('available_tickets')"
                    :model-value="availableTickets"
                    @update:model-value="onAvailableTicketsChange"
                />
                <Label :for="id('available_tickets')" class=" cursor-pointer font-normal">
                    Solo con posti disponibili
                </Label>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <Button size="sm" @click="applyFilters" class="cursor-pointer hover:bg-primary/85">
                Filtra
            </Button>
            <Button
                v-if="hasActiveFilters"
                size="sm"
                variant="ghost"
                class="bg-muted-foreground hover:bg-muted-foreground/80 text-white hover:text-white/90 cursor-pointer"
                @click="resetFilters"
            >
                Resetta filtri
            </Button>
        </div>
    </section>
</template>
