<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Package, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
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
import { dashboard as adminDashboard } from '@/routes/admin';
import type { NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();

const canAccessAdmin = computed(() => {
    const auth = page.props.auth as { canAccessAdmin?: boolean; user?: { is_admin?: boolean; isAdmin?: boolean } } | undefined;

    return Boolean(auth?.canAccessAdmin ?? auth?.user?.is_admin ?? auth?.user?.isAdmin);
});

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: adminDashboard(),
            icon: LayoutGrid,
        },
    ];

    if (canAccessAdmin.value) {
        items.push(
            {
                title: 'Eventi',
                href: '/admin/events',
                icon: Package,
            },
            {
                title: 'Categorie eventi',
                href: '/admin/event-categories',
                icon: Package,
            },
            {
                title: 'Biglietti',
                href: '/admin/tickets',
                icon: Package,
            },
            {
                title: 'Ordini',
                href: '/admin/orders',
                icon: Package,
            },
            {
                title: 'Coda eventi',
                href: '/admin/queue-entries',
                icon: Package,
            },
            {
                title: 'Utenti',
                href: '/admin/users',
                icon: Users,
            },
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="adminDashboard()">
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
