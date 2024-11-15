<script setup>
import InputLabel from '@/Components/InputLabel.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
defineProps({
    managers: {
        type: Array,
        required: true,
    },
    role: {
        type: Object,
        required: true,
    },
})

const deleteUser = (managerId) => {
    if (confirm('Вы точно хотите удалить менеджера?')) {
        Inertia.delete(route('admin.delete.user', { user: managerId }))
    }
}

const resetPassword = (managerId) => {
    if (confirm('Вы уверены, что хотите сбросить пароль?')) {
        Inertia.post(route('admin.reset.password', { user: managerId }))
    }
}
</script>
<template>
    <Head title="Managers" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Менеджеры
            </h2>
            <ResponsiveNavLink :href="route('admin.registration.manager')">
                Добавить менеджера
            </ResponsiveNavLink>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h2 class="text-lg font-medium text-gray-900">
                                Менеджеры
                            </h2>
                            <div class="flex items-center gap-4 p-6"></div>
                            <div
                                class="client"
                                v-for="manager in managers"
                                :key="manager.id"
                            >
                                <div>
                                    <InputLabel for="last_name" value="ID" />
                                    {{ manager.id }}
                                </div>
                                <div>
                                    <InputLabel for="last_name" value="ФИО" />
                                    {{ manager.last_name }}
                                    {{ manager.first_name }}
                                    {{ manager.middle_name }}
                                </div>
                                <div>
                                    <InputLabel
                                        for="last_name"
                                        value="Номер телефона"
                                    />
                                    {{ manager.phone_number }}
                                </div>
                                <div>
                                    <InputLabel for="last_name" value="Email" />
                                    {{ manager.email }}
                                </div>
                                <div>
                                    <InputLabel
                                        for="last_name"
                                        value="Договор"
                                    />
                                    <div>
                                        {{ manager.manager_contracts.length }}
                                    </div>
                                </div>
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <!-- <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink> -->
                                        <DropdownLink
                                            :href="
                                                route('admin.edit.manager', {
                                                    manager: manager.id,
                                                })
                                            "
                                            :disabled="manager.active === false"
                                            as="button"
                                        >
                                            Изменить
                                        </DropdownLink>
                                        <DropdownLink
                                            @click="resetPassword(manager.id)"
                                            as="button"
                                        >
                                            Сбросить пароль
                                        </DropdownLink>
                                        <DropdownLink
                                            @click="deleteUser(manager.id)"
                                            as="button"
                                        >
                                            Удалить
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped>
.client {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style>
