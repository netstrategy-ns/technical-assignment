<script setup lang="ts">
import {
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
    PaginationRoot,
} from 'reka-ui';
import type { PaginationNavPayload } from '@/composables/usePagination';
import { usePagination } from '@/composables/usePagination';
import { cn } from '@/lib/utils';

const props = defineProps<{
    pagination: PaginationNavPayload;
    /** Classi applicate al nav root */
    class?: string;
}>();

const { onPageChange } = usePagination();
</script>

<template>
    <div
        v-if="pagination.total > 0"
        :class="cn('flex flex-wrap items-center justify-center gap-4', props.class)"
    >
        <PaginationRoot
            v-if="pagination.last_page > 1"
            :page="pagination.current_page"
            :total="pagination.total"
            :items-per-page="pagination.per_page"
            :sibling-count="2"
            show-edges
            class="flex justify-center"
            @update:page="onPageChange"
        >
        <PaginationList v-slot="{ items }" class="flex items-center gap-2">
            <PaginationFirst
                :class="
                    cn(
                        'rounded border px-3 py-1 text-sm',
                        pagination.current_page === 1
                            ? 'cursor-not-allowed border-sidebar-border/50 bg-muted/50 text-muted-foreground'
                            : 'border-sidebar-border/70 hover:bg-muted',
                    )
                "
            >
                «
            </PaginationFirst>
            <PaginationPrev
                :class="
                    cn(
                        'rounded border px-3 py-1 text-sm',
                        pagination.current_page === 1
                            ? 'cursor-not-allowed border-sidebar-border/50 bg-muted/50 text-muted-foreground'
                            : 'border-sidebar-border/70 hover:bg-muted',
                    )
                "
            >
                ‹
            </PaginationPrev>
            <!-- Sotto md: solo testo; da md in su: numeri di pagina -->
            <span class="shrink-0 px-1 text-sm text-muted-foreground md:hidden">
                Pag {{ pagination.current_page }} di {{ pagination.last_page }}
            </span>
            <div class="hidden md:flex md:items-center md:gap-2">
                <template v-for="(item, index) in items" :key="index">
                    <PaginationListItem
                        v-if="item.type === 'page'"
                        :value="item.value"
                        :class="
                            cn(
                                'rounded border px-3 py-1 text-sm',
                                item.value === pagination.current_page
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-sidebar-border/70 hover:bg-muted',
                            )
                        "
                    >
                        {{ item.value }}
                    </PaginationListItem>
                    <PaginationEllipsis v-else :index="index" class="px-2 text-muted-foreground">
                        …
                    </PaginationEllipsis>
                </template>
            </div>
            <PaginationNext
                :class="
                    cn(
                        'rounded border px-3 py-1 text-sm',
                        pagination.current_page === pagination.last_page
                            ? 'cursor-not-allowed border-sidebar-border/50 bg-muted/50 text-muted-foreground'
                            : 'border-sidebar-border/70 hover:bg-muted',
                    )
                "
            >
                ›
            </PaginationNext>
            <PaginationLast
                :class="
                    cn(
                        'rounded border px-3 py-1 text-sm',
                        pagination.current_page === pagination.last_page
                            ? 'cursor-not-allowed border-sidebar-border/50 bg-muted/50 text-muted-foreground'
                            : 'border-sidebar-border/70 hover:bg-muted',
                    )
                "
            >
                »
            </PaginationLast>
        </PaginationList>
        </PaginationRoot>
    </div>
</template>
