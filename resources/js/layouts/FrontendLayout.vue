<script setup lang="ts">
import CustomFooter from '@/components/custom/Footer/Footer.vue';
import CustomHeader from '@/components/custom/Header/Header.vue';
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background">
        <CustomHeader />
        <main class="w-full flex-1">
            <slot />
        </main>
        <CustomFooter />
    </div>
</template>
