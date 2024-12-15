<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({
    contracts: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
});
</script>
<template>
    <Head title="ContractsClient" />
    <AuthenticatedLayout :userRole="role" :notifications="props.status">
        <template #header> </template>
        <template #main> {{ contracts }}</template>
    </AuthenticatedLayout>
</template>
