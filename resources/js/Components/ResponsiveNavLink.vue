<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    // active: {
    //     type: Boolean,
    // },
});

// const classes = computed(() =>
//     props.active
//         ? ''
//         : ''
// );
</script>

<template>
    <Link :href="href" class="link-btn justify-center align-center">
    <slot />
    </Link>
</template>

<style scoped>
.link-btn {
    background: #F3F5F6;
    height: 45px;
    display: inline-flex;
    padding: 12px 20px;
    border-radius: 12px;
}
</style>