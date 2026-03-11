<script setup lang="ts">
import DesktopLogo from './Partials/Desktop/Logo.vue';
import DesktopNavbar from './Partials/Desktop/Navbar.vue';
import MobileLogo from './Partials/Mobile/Logo.vue';
import MobileNavbar from './Partials/Mobile/Navbar.vue';
</script>

<template>
    <header
        class="min-w-0 shrink-0 border-b border-sidebar-border/80 bg-card px-4 py-3"
        role="banner"
    >
        <div class="flex w-full items-center justify-between gap-4">
            <!-- Desktop: logo + navbar -->
            <div class="hidden w-full items-center justify-between md:flex">
                <DesktopLogo />
                <DesktopNavbar />
            </div>
            <!-- Mobile: logo + hamburger -->
            <div class="flex w-full items-center justify-between md:hidden">
                <MobileLogo />
                <MobileNavbar />
            </div>
        </div>
    </header>
</template>
