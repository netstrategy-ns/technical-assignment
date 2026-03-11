<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    class?: HTMLAttributes['class'];
};

defineProps<Props>();

const brandSrc = '/storage/images/t2e_brand.png';
</script>

<template>
    <img
        :src="brandSrc"
        alt=""
        role="presentation"
        :class="$props.class"
        v-bind="$attrs"
    >
</template>
