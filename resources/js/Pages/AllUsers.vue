<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Inertia } from '@inertiajs/inertia';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
    managers: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="All Users" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Пользователи</h2>
        </template>
        <template #main>
            <div class="py-12">
                <ResponsiveNavLink :href="route('admin.registration.client')"> Добавить клиента </ResponsiveNavLink>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <h2>Клиенты</h2>
                        <div class="p-6 text-gray-900">
                            {{ props.clients.length === 0 ? 'Клиентов нет' : props.clients }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="py-12">
                <ResponsiveNavLink :href="route('admin.registration.manager')"> Добавить менеджера </ResponsiveNavLink>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <h2>Менеджеры</h2>
                        <div class="p-6 text-gray-900">
                            {{ props.managers.length === 0 ? 'Менеджеров нет' : props.managers }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
