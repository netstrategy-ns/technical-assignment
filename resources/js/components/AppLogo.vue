<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    class?: HTMLAttributes['class'];
};

defineProps<Props>();

const logoSrc = '/storage/images/t2e_logo.png';
</script>

<template>
    <img
        :src="logoSrc"
        alt="TickMe"
        :class="$props.class"
        v-bind="$attrs"
    >
</template>
