<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="body-logo">
        <div>
            <Link href="/">
            </Link>
        </div>

        <div class="">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.body-logo {
    background-color: #F3F5F6;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
</style>