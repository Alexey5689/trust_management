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

const deleteUser = (userId) => {
    if (confirm('Вы точно хотите удалить менеджера?')) {
        Inertia.delete(route('admin.delete.user', { user: userId }));
    }
};

const resetPassword = (userId) => {
    if (confirm('Вы уверены, что хотите сбросить пароль?')) {
        Inertia.post(route('reset.password', { user: userId }));
    }
};
</script>

<template>
    <Head title="All Users" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="title">Пользователи</h2>
        </template>
        <template #main>
            <ResponsiveNavLink :href="route('admin.registration.client')"> Добавить клиента </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('admin.registration.manager')"> Добавить менеджера </ResponsiveNavLink>
            <div class="card">
                <header>
                    <h2 class="title-card">Клиенты</h2>
                </header>
                <!-- {{ props.clients }} -->
                <div class="card-content flex" v-for="client in props.clients" :key="client.id">
                    <div class="card-item">
                        <InputLabel for="number" value="№" />
                        <p class="text">{{ client.id }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="full_name" value="ФИО" />
                        <p class="text">{{ client.full_name }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="phone_number" value="Номер телефона" />
                        <p class="text">{{ client.phone_number }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="email" value="Email" />
                        <p class="text">{{ client.email }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="manager" value="Менджер" />
                        <p class="text">{{ client.manager_full_name }}</p>
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
                            <DropdownLink @click="resetPassword(client.id)" as="button"> Сбросить пароль </DropdownLink>
                            <DropdownLink v-if="role === 'admin'" @click="deleteUser(client.id)" as="button">
                                Удалить
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
            <div class="card">
                <header>
                    <h2 class="title-card">Менеджеры</h2>
                </header>
                <div class="card-content flex" v-for="manager in managers" :key="manager.id">
                    <div class="card-item">
                        <InputLabel for="number" value="№" />
                        <p class="text">{{ manager.id }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="full_name" value="ФИО" />
                        <p class="text">{{ manager.full_name }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="phone_number" value="Номер телефона" />
                        <p class="text">{{ manager.phone_number }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="email" value="Email" />
                        <p class="text">{{ manager.email }}</p>
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
                            <DropdownLink @click="resetPassword(manager.id)" as="button">
                                Сбросить пароль
                            </DropdownLink>
                            <DropdownLink @click="deleteUser(manager.id)" as="button"> Удалить </DropdownLink>
                        </template>
                    </Dropdown>
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

.title {
    margin-top: 54px;
    line-height: 42px;
    font-size: 30px;
    font-weight: 600;
    color: #000;
    margin-bottom: 32px;
}

.card {
    background: #fff;
    padding: 32px;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
}

.card-content {
    margin-top: 32px;
}

.card-item {
    width: 33.33%;
}

.text {
    margin-bottom: 16px;
}
</style>
