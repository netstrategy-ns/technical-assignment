<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CartDropdown from '../CartDropdown.vue';
import { useAuthRedirect } from '@/composables/useAuthRedirect';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const eventsIndex = computed(() => urls.value.eventsIndex ?? '/events');
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);
const user = computed(() => (page.props.auth as { user?: unknown })?.user);
const ordersIndex = computed(() => urls.value.orders ?? '/orders');
const adminDashboard = computed(() => (urls.value as Record<string, string>).adminDashboard ?? '/admin/dashboard');
const canAccessAdmin = computed(() => Boolean((page.props.auth as { canAccessAdmin?: boolean })?.canAccessAdmin));
const { storeCurrent } = useAuthRedirect();

const setLoginRedirect = (): void => {
    storeCurrent('login');
};

const setRegisterRedirect = (): void => {
    storeCurrent('register');
};
</script>

<template>
    <nav class="flex items-center gap-6" aria-label="Navigazione principale">
        <Link
            href="/"
            class="text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            Home
        </Link>
        <Link
            :href="eventsIndex"
            class="text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            Eventi
        </Link>
        <CartDropdown v-if="user" />
        <template v-if="user">
            <Link
                :href="ordersIndex"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                I miei ordini
            </Link>
            <Link
                v-if="canAccessAdmin"
                :href="adminDashboard"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                Dashboard
            </Link>
        </template>
        <template v-else>
            <Link
                href="/login"
                @click="setLoginRedirect"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                Accedi
            </Link>
            <Link
                v-if="canRegister"
                href="/register"
                @click="setRegisterRedirect"
                class="rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm transition-colors hover:bg-accent/50"
            >
                Registrati
            </Link>
        </template>
    </nav>
</template>
