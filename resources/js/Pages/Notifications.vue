<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    notifications: {
        type: Array,
        required: true,
    },
    user: {
        type: Array,
        required: true,
    },
});
</script>
<template>
    <Head title="Notifications" />
    <AuthenticatedLayout :user="props.user" :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Уведомления</h2>
            </div>
        </template>
        <template #main>
            {{ props.notifications }}
        </template>
    </AuthenticatedLayout>
</template>

<style scoped></style>
