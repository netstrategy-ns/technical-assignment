<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const eventsIndex = computed(() => urls.value.eventsIndex ?? '/events');
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);
const user = computed(() => (page.props.auth as { user?: unknown })?.user);

const navLinkClass =
    'block rounded-md px-3 py-2 text-base text-muted-foreground transition-colors hover:bg-accent hover:text-foreground';
</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button variant="ghost" size="icon" aria-label="Apri menu">
                <Menu class="size-5" />
            </Button>
        </SheetTrigger>
        <SheetContent side="right" class="w-full max-w-full sm:max-w-full">
            <SheetHeader class="flex flex-row items-center justify-between space-y-0 border-b border-sidebar-border/80 pb-4 pr-12">
                <Link href="/" class="inline-block shrink-0 transition-opacity hover:opacity-90">
                    <AppLogo class="h-9 w-auto" />
                </Link>
                <SheetTitle class="sr-only">Menu di navigazione</SheetTitle>
            </SheetHeader>
            <nav class="mt-6 flex flex-col gap-1" aria-label="Navigazione principale">
                <Link href="/" :class="navLinkClass">Home</Link>
                <Link :href="eventsIndex" :class="navLinkClass">Eventi</Link>
                <template v-if="user">
                    <Link href="/dashboard" :class="navLinkClass">Dashboard</Link>
                </template>
                <template v-else>
                    <Link href="/login" :class="navLinkClass">Accedi</Link>
                    <Link v-if="canRegister" href="/register" :class="navLinkClass">Registrati</Link>
                </template>
            </nav>
        </SheetContent>
    </Sheet>
</template>
