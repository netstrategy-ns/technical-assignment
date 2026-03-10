<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { Menu, UserRound } from "lucide-vue-next";
import AppLogo from "@/components/AppLogo.vue";
import { Button } from "@/components/ui/button";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import CartDropdown from "../CartDropdown.vue";
import { useAuthRedirect } from "@/composables/useAuthRedirect";
import UserDropdownContent from "../UserDropdownContent.vue";
import type { User } from "@/types";

const page = usePage();
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
const eventsIndex = computed(() => urls.value.eventsIndex ?? "/events");
const canRegister = computed(() => (page.props.canRegister as boolean) ?? true);
const user = computed(() => (page.props.auth as { user?: User })?.user);
const ordersIndex = computed(() => urls.value.orders ?? "/orders");
const { storeCurrent } = useAuthRedirect();

const setLoginRedirect = (): void => {
  storeCurrent("login");
};

const setRegisterRedirect = (): void => {
  storeCurrent("register");
};

const navLinkClass =
  "block rounded-md px-3 py-2 text-base text-muted-foreground transition-colors hover:bg-accent hover:text-foreground";
</script>

<template>
  <div class="flex items-center gap-1">
    <CartDropdown />
    <Sheet>
      <SheetTrigger as-child>
        <Button variant="ghost" size="icon" aria-label="Apri menu">
          <Menu class="size-5" />
        </Button>
      </SheetTrigger>
      <SheetContent side="right" class="flex w-full max-w-full flex-col sm:max-w-full">
        <SheetHeader
          class="flex flex-row items-center justify-between space-y-0 border-b border-sidebar-border/80 pb-4 pr-12"
        >
          <Link
            href="/"
            class="inline-block shrink-0 transition-opacity hover:opacity-90"
          >
            <AppLogo class="h-9 w-auto" />
          </Link>
          <SheetTitle class="sr-only">Menu di navigazione</SheetTitle>
        </SheetHeader>
        <nav class="mt-6 flex w-full flex-1 flex-col gap-1" aria-label="Navigazione principale">
          <Link href="/" :class="navLinkClass">Home</Link>
          <Link :href="eventsIndex" :class="navLinkClass">Eventi</Link>

          <template v-if="user">
            <Link :href="ordersIndex" :class="navLinkClass">I miei ordini</Link>
            <div class="mt-auto border-t border-sidebar-border/70">
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="outline" class="w-full justify-start">
                    <UserRound class="mr-2 h-4 w-4" />
                    Account
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                  align="end"
                    side="top"
                    :side-offset="0"
                    class="w-(--reka-dropdown-menu-trigger-width) max-w-full rounded-xl"
                >
                  <UserDropdownContent :user="user" class="w-full" />
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </template>
          <template v-else>
            <Link href="/login" :class="navLinkClass" @click="setLoginRedirect"
              >Accedi</Link
            >
            <Link
              v-if="canRegister"
              href="/register"
              :class="navLinkClass"
              @click="setRegisterRedirect"
              >Registrati</Link
            >
          </template>
        </nav>
      </SheetContent>
    </Sheet>
  </div>
</template>
