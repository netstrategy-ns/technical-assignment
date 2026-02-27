<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Search, X } from 'lucide-vue-next';
import type { Category, EventFilters as EventFiltersType } from '@/types/models';

const props = defineProps<{
    categories: Category[];
    filters: EventFiltersType;
}>();

const emit = defineEmits<{
    (e: 'apply'): void;
    (e: 'reset'): void;
}>();

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function handleSearchInput() {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => emit('apply'), 400);
}

function handleCategoryChange(event: globalThis.Event) {
    const target = event.target as HTMLSelectElement;
    props.filters.category_id = target.value ? Number(target.value) : '';
    emit('apply');
}

function handleSortChange(event: globalThis.Event) {
    const target = event.target as HTMLSelectElement;
    props.filters.sort = target.value as EventFiltersType['sort'];
    emit('apply');
}

function handleFeaturedChange(checked: boolean | 'indeterminate') {
    props.filters.featured = checked === true;
    emit('apply');
}
</script>

<template>
    <div class="space-y-4 rounded-lg border p-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative">
                <Label for="search" class="sr-only">Search</Label>
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input
                    id="search"
                    v-model="filters.search"
                    placeholder="Search events..."
                    class="pl-9"
                    @input="handleSearchInput"
                />
            </div>

            <div>
                <Label for="category" class="sr-only">Category</Label>
                <select
                    id="category"
                    :value="filters.category_id ?? ''"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    @change="handleCategoryChange"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>

            <div>
                <Label for="city" class="sr-only">City</Label>
                <Input
                    id="city"
                    v-model="filters.city"
                    placeholder="City..."
                    @keyup.enter="emit('apply')"
                />
            </div>

            <div>
                <Label for="sort" class="sr-only">Sort by</Label>
                <select
                    id="sort"
                    :value="filters.sort ?? 'nearest'"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    @change="handleSortChange"
                >
                    <option value="nearest">Nearest Date</option>
                    <option value="newest">Newest First</option>
                    <option value="featured">Featured First</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <Label for="date_from" class="whitespace-nowrap text-sm">From</Label>
                <Input
                    id="date_from"
                    type="date"
                    v-model="filters.date_from"
                    class="w-auto"
                    @change="emit('apply')"
                />
            </div>
            <div class="flex items-center gap-2">
                <Label for="date_to" class="whitespace-nowrap text-sm">To</Label>
                <Input
                    id="date_to"
                    type="date"
                    v-model="filters.date_to"
                    class="w-auto"
                    @change="emit('apply')"
                />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox
                    id="featured"
                    :checked="!!filters.featured"
                    @update:checked="handleFeaturedChange"
                />
                <Label for="featured" class="cursor-pointer text-sm">Featured only</Label>
            </div>

            <Button variant="ghost" size="sm" @click="emit('reset')">
                <X class="mr-1 size-4" />
                Reset
            </Button>
        </div>
    </div>
</template>
