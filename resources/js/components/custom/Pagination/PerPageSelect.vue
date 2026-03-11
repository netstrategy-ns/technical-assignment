<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { PerPageOption } from '@/composables/usePagination';
import { DEFAULT_PER_PAGE, PER_PAGE_OPTIONS } from '@/composables/usePagination';

const props = withDefaults(
    defineProps<{
        modelValue: number;
        options?: readonly number[];
        labelBefore?: string
        labelAfter?: string;
    }>(),
    {
        options: () => PER_PAGE_OPTIONS,
        labelBefore: 'Mostra',
        labelAfter: 'per pagina',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const normalize = (value: number): PerPageOption => {
    const opts = props.options as readonly number[];
    return opts.includes(value) ? (value as PerPageOption) : DEFAULT_PER_PAGE;
};

const displayValue = () => normalize(props.modelValue);

const onSelect = (opt: number): void => {
    emit('update:modelValue', opt);
};
</script>

<template>
    <div class="flex items-center gap-2">
        <span class="text-sm text-muted-foreground">{{ labelBefore }}</span>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 min-w-18 justify-between gap-1 border-sidebar-border/70"
                >
                    {{ displayValue() }}
                    <ChevronDown class="size-3.5 opacity-50" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
                <DropdownMenuItem
                    v-for="opt in options"
                    :key="opt"
                    @select="onSelect(opt)"
                >
                    {{ opt }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
        <span class="text-sm text-muted-foreground">{{ labelAfter }}</span>
    </div>
</template>
