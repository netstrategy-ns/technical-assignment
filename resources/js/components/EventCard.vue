<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { CalendarDays, ImageIcon, MapPin } from 'lucide-vue-next';
import type { Event } from '@/types/models';

defineProps<{
    event: Event;
}>();

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Link :href="`/events/${event.slug}`" class="group block">
        <Card class="h-full transition-shadow group-hover:shadow-md">
            <CardHeader class="p-0">
                <div
                    class="flex aspect-video items-center justify-center overflow-hidden rounded-t-xl border-b bg-muted"
                >
                    <img
                        v-if="event.image"
                        :src="event.image"
                        :alt="event.title"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex flex-col items-center justify-center gap-2">
                        <ImageIcon class="size-8 text-muted-foreground/50" />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="space-y-2 p-4">
                <div class="flex flex-wrap items-center gap-1.5">
                    <Badge v-if="event.is_featured" variant="default" class="text-xs">Featured</Badge>
                    <Badge v-if="event.category" variant="secondary" class="text-xs">{{
                        event.category.name
                    }}</Badge>
                </div>
                <h3
                    class="line-clamp-2 text-base font-semibold leading-tight group-hover:underline"
                >
                    {{ event.title }}
                </h3>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <CalendarDays class="size-3" />
                    <span>{{ formatDate(event.starts_at) }}</span>
                </div>
                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                    <MapPin class="size-3" />
                    <span>{{ event.venue }}, {{ event.city }}</span>
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
