<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
// import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';

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
    status: {
        type: String,
        required: false,
    },
});

// const deleteUser = (userId) => {
//     if (confirm('Вы точно хотите удалить пользователя?')) {
//         Inertia.delete(route('delete.user', { user: userId }));
//     }
// };

// const resetPassword = (userId) => {
//     if (confirm('Вы уверены, что хотите сбросить пароль пользователю?')) {
//         Inertia.post(route('reset.password', { user: userId }));
//     }
// };

const handleDropdownSelect = (option, userId, type) => {
    switch (option.action) {
        case 'edit':
            // Редирект на страницу редактирования
            if (type === 'manager') {
                router.get(route('admin.edit.manager', { manager: userId }));
            } else if (type === 'client') {
                router.get(route(`${props.role}.edit.client`, { client: userId }));
            }
            break;
        case 'resetPassword':
            if (confirm('Вы уверены, что хотите сбросить пароль?')) {
                router.post(route('reset.password', { user: userId }));
            }
            break;
        case 'delete':
            if (confirm('Вы уверены, что хотите удалить пользователя?')) {
                router.delete(route('delete.user', { user: userId }));
            }
            break;
        default:
            console.error('Неизвестное действие:', option.action);
    }
};
</script>

<template>
    <Head title="All Users" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Пользователи</h2>
                <div>
                    <ResponsiveNavLink :href="route('admin.registration.client')" class="add_client">
                        Добавить клиента
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.registration.manager')" class="add_manager">
                        Добавить менеджера
                    </ResponsiveNavLink>
                </div>
            </div>
        </template>
        <template #main>
            <div class="main flex flex-column">
                <div class="card">
                    <header>
                        <h2 class="title-card">Менеджеры</h2>
                    </header>
                    <div class="card-content">
                        <ul class="thead-manager align-center">
                            <li class="order">№</li>
                            <li>ФИО</li>
                            <li>Номер телефона</li>
                            <li>Email</li>
                        </ul>
                        <div class="items-manager align-center" v-for="manager in managers" :key="manager.id">
                            <div class="card-item order">
                                <p class="text">{{ manager.id }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ manager.full_name }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ manager.phone_number }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ manager.email }}</p>
                            </div>
                            <div class="card-item ellipsis">
                                <Dropdown
                                    :options="[
                                        { label: 'Изменить', action: 'edit' },
                                        { label: 'Сбросить пароль', action: 'resetPassword' },
                                        { label: 'Удалить', action: 'delete' },
                                    ]"
                                    @select="handleDropdownSelect($event, manager.id, 'manager')"
                                >
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        {{ $page.props.auth.user.name }}

                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
<template #content>
                                <DropdownLink :href="route('admin.edit.manager', {
                                    manager: manager.id,
                                })
                                    " :disabled="manager.active === false" as="button">
                                    Изменить
                                </DropdownLink>
                                <DropdownLink @click="resetPassword(manager.id)" as="button">
                                    Сбросить пароль
                                </DropdownLink>
                                <DropdownLink @click="deleteUser(manager.id)" as="button"> Удалить </DropdownLink>
                            </template>
</Dropdown> -->
                        </div>
                    </div>
                </div>

                <div class="card">
                    <header>
                        <h2 class="title-card">Клиенты</h2>
                    </header>
                    <!-- {{ props.clients }} -->
                    <div class="card-content">
                        <ul class="thead-client align-center">
                            <li class="order">№</li>
                            <li>ФИО</li>
                            <li>Номер телефона</li>
                            <li>Email</li>
                            <li>Менеджер</li>
                        </ul>
                        <div class="items-client align-center" v-for="client in props.clients" :key="client.id">
                            <div class="card-item order">
                                <p class="text">{{ client.id }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ client.full_name }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ client.phone_number }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ client.email }}</p>
                            </div>
                            <div class="card-item">
                                <p class="text">{{ client.manager_full_name }}</p>
                            </div>
                            <div class="card-item ellipsis">
                                <Dropdown
                                    :options="[
                                        { label: 'Изменить', action: 'edit' },
                                        { label: 'Сбросить пароль', action: 'resetPassword' },
                                        { label: 'Удалить клиента', action: 'delete' },
                                    ]"
                                    @select="handleDropdownSelect($event, client, 'client')"
                                >
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        {{ $page.props.auth.user.name }}

                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <template #content>
                                <DropdownLink :href="route(`${props.role}.edit.client`, { client: client.id })"
                                    :disabled="client.active === false" as="button">
                                    Изменить
                                </DropdownLink>
                                <DropdownLink @click="resetPassword(client.id)" as="button"> Сбросить пароль
                                </DropdownLink>
                                <DropdownLink v-if="role === 'admin'" @click="deleteUser(client.id)" as="button">
                                    Удалить
                                </DropdownLink>
                            </template>
                        </Dropdown> -->
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
    border-bottom: 1px solid #f3f5f6;
    padding: 24px 32px 20px 32px;
}

.card-content {
    padding: 20px 32px 32px 32px;
}

.thead-manager {
    height: 55px;
    display: grid;
    grid-template-columns: 50px 3.5fr 3fr 3fr 1fr;
    border-bottom: 1px solid #f3f5f6;
}

.items-manager {
    padding: 16px 0;
    display: grid;
    grid-template-columns: 50px 3.5fr 3fr 3fr 1fr;
    border-bottom: 1px solid #f3f5f6;
}

.thead-client {
    height: 55px;
    display: grid;
    grid-template-columns: 50px 3fr 2.5fr 2.5fr 3fr 1fr;
    border-bottom: 1px solid #f3f5f6;
}

.items-client {
    padding: 16px 0;
    display: grid;
    grid-template-columns: 50px 3fr 2.5fr 2.5fr 3fr 1fr;
    border-bottom: 1px solid #f3f5f6;
}

.thead-manager li,
.thead-client li {
    font-size: 16px;
    font-weight: 600;
    line-height: 23.2px;
    letter-spacing: 0.01em;
    color: #969ba0;
}

.link-btn {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
}

.add_client {
    background: #4e9f7d;
    color: #fff;
    margin-right: 16px;
    transition: 0.3s;
}

.add_client:hover {
    background: #428569;
}

.add_manager {
    transition: 0.3s;
}

.add_manager:hover {
    background: #e2e7e9;
}

.main {
    row-gap: 32px;
}

.ellipsis {
    margin-left: auto;
    padding-right: 12px;
}

.order {
    padding-left: 12px;
}
</style>
