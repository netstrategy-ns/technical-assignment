<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Calendar, LayoutGrid, ShoppingBag } from 'lucide-vue-next';
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth.user);

// Public nav items — visible to everyone
const publicNavItems: NavItem[] = [
    {
        title: 'Events',
        href: '/events',
        icon: Calendar,
    },
];

// Auth nav items — only visible when logged in
const authNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Events',
        href: '/events',
        icon: Calendar,
    },
    {
        title: 'My Orders',
        href: '/orders',
        icon: ShoppingBag,
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    if (isAuthenticated.value) {
        return authNavItems;
    }
    return publicNavItems;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
