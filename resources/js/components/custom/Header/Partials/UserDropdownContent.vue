<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, LogOut, ReceiptText, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';
import { logout } from '@/routes';

type Props = {
    user: User;
    showOrders?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showOrders: false,
});

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const adminDashboard = computed(
    () => urls.value.adminDashboard ?? '/admin/dashboard',
);
const canAccessAdmin = computed(() =>
    Boolean((page.props.auth as { canAccessAdmin?: boolean })?.canAccessAdmin),
);
const settingsProfile = computed(() => urls.value.profile ?? '/user/settings/profile');
const ordersUrl = computed(() => urls.value.orders ?? '/orders');

const handleLogout = (): void => {
    router.flushAll();
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-3 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator />

    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="settingsProfile"
                prefetch
            >
                <UserRound class="mr-2 h-4 w-4" />
                Profilo
            </Link>
        </DropdownMenuItem>

        <DropdownMenuItem
            v-if="props.showOrders"
            :as-child="true"
        >
            <Link class="block w-full cursor-pointer" :href="ordersUrl">
                <ReceiptText class="mr-2 h-4 w-4" />
                I miei ordini
            </Link>
        </DropdownMenuItem>

        <DropdownMenuItem v-if="canAccessAdmin" :as-child="true">
            <Link class="block w-full cursor-pointer" :href="adminDashboard">
                <LayoutDashboard class="mr-2 h-4 w-4" />
                Dashboard
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator />

    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Esci
        </Link>
    </DropdownMenuItem>
</template>
