<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileAvatar from '@/components/custom/UserProfile/ProfileAvatar.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuthRedirect } from '@/composables/useAuthRedirect';
import type { User } from '@/types';
import CartDropdown from '../CartDropdown.vue';
import UserDropdownContent from '../UserDropdownContent.vue';

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const eventsIndex = computed(() => urls.value.eventsIndex ?? '/events');
const ordersIndex = computed(() => urls.value.orders ?? '/orders');
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);
const user = computed(() => (page.props.auth as { user?: User })?.user);
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
        <Link
            v-if="user"
            :href="ordersIndex"
            class="text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            I miei ordini
        </Link>

        <template v-if="user">
            <CartDropdown />

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        class="rounded-full transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        aria-label="Apri menu profilo"
                    >
                        <ProfileAvatar :user="user" />
                    </button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="end"
                    :side-offset="10"
                    class="w-64 rounded-xl"
                >
                    <UserDropdownContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
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
