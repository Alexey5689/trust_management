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
    role: {
        type: String,
        required: true,
    },
});

const deleteUser = (clientId) => {
    if (confirm('Вы точно хотите удалить менеджера?')) {
        Inertia.delete(route('admin.delete.user', { user: clientId }));
    }
};

const resetPassword = (clientId) => {
    if (confirm('Вы уверены, что хотите сбросить пароль?')) {
        Inertia.post(route('reset.password', { user: clientId }));
    }
};
</script>
<template>
    <Head title="Clients" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Клиенты</h2>
            <ResponsiveNavLink :href="route(`${props.role}.registration.client`)"> Добавить клиента </ResponsiveNavLink>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            {{ clients }}
                            <div v-if="clients.length == 0">Клиентов нет</div>
                            <div v-else class="client" v-for="client in clients" :key="client.id">
                                <div>
                                    <InputLabel for="last_name" value="ID" />
                                    {{ client.id }}
                                </div>
                                <div :class="!client.active ? 'text-red' : ''">
                                    <InputLabel for="last_name" value="ФИО" />
                                    {{ client.full_name }}
                                </div>
                                <div>
                                    <InputLabel for="last_name" value="Номер телефона" />
                                    {{ client.phone_number }}
                                </div>
                                <div>
                                    <InputLabel for="last_name" value="Email" />
                                    {{ client.email }}
                                </div>
                                <div>
                                    <InputLabel for="last_name" value="Договор" />
                                    <div>
                                        {{ client.user_contracts.length }}
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
                                        <DropdownLink
                                            :href="route(`${props.role}.edit.client`, { client: client.id })"
                                            :disabled="client.active === false"
                                            as="button"
                                        >
                                            Изменить
                                        </DropdownLink>
                                        <DropdownLink @click="resetPassword(client.id)" as="button">
                                            Сбросить пароль
                                        </DropdownLink>
                                        <DropdownLink
                                            v-if="role === 'admin'"
                                            @click="deleteUser(client.id)"
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
.text-red {
    color: red;
}
</style>
