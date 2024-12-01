<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';

// import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

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

const isModalOpen = ref(false);
const currentModal = ref(null);

const handleDropdownSelect = (option, userId, type) => {
    switch (option.action) {
        case 'edit':
            // Редирект на страницу редактирования
            // if (type === 'manager') {
            //     router.get(route('admin.edit.manager', { manager: userId }));
            // } else if (type === 'client') {
            //     router.get(route(`${props.role}.edit.client`, { client: userId }));
            // }
            openModal(type, userId, 'edit');
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

const modalTitles = {
    manager: {
        add: 'Добавление менеджера',
        edit: 'Изменение менеджера',
    },
    client: {
        add: 'Добавление клиента',
        edit: 'Изменение клиента',
    },
};


// const urls = {
//     manager: 'manager-create',
//     client: 'client-create',
// };

const openModal = (type, userId, action = 'add') => {
    currentModal.value = { type, userId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
};
</script>

<template>

    <Head title="All Users" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Пользователи</h2>
                <div>

                    <!-- <ResponsiveNavLink :href="route('admin.registration.client')">Добавить клиента</ResponsiveNavLink> -->

                    <button @click="openModal('client')" class="add_client link-btn">
                        Добавить клиента
                    </button>
                    <!-- :href="route('admin.registration.manager')" -->
                    <button @click="openModal('manager')" class="add_manager link-btn">
                        Добавить менеджера
                    </button>
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
                                <Dropdown :options="[
                                    { label: 'Изменить', action: 'edit' },
                                    { label: 'Сбросить пароль', action: 'resetPassword' },
                                    { label: 'Удалить', action: 'delete' },
                                ]" @select="handleDropdownSelect($event, manager.id, 'manager')">
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>
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
                                <Dropdown :options="[
                                    { label: 'Изменить', action: 'edit' },
                                    { label: 'Сбросить пароль', action: 'resetPassword' },
                                    { label: 'Удалить клиента', action: 'delete' },
                                ]" @select="handleDropdownSelect($event, client, 'client')">
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>

    <!-- 
    @response="userData = $event"
    :url="urls[currentModal]"
    -->
    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal.type][currentModal.action]"
        @close="closeModal">
        <template #default>
            <div v-if="currentModal.type === 'manager'">
                <!-- <p>{{ currentModal.action === 'edit' ? 'Редактирование менеджера' : 'Добавление менеджера' }} с ID: {{ currentModal.userId }}</p> -->
                <div v-if="currentModal.action === 'edit'">
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input type="tel" id="phone" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" />
                            </div>
                        </div>
                    </form>
                </div>
                <div v-else>
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input type="tel" id="phone" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" />
                                <p class="warning">
                                    На эту почту придет письмо c ссылкой на создание пароля
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div v-if="currentModal.type === 'client'">
                <!-- <p>{{ currentModal.action === 'edit' ? 'Редактирование клиента' : 'Добавление клиента' }} с ID: {{ currentModal.userId }}</p> -->
                <div v-if="currentModal.action === 'edit'">
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input type="tel" id="phone" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" />
                            </div>
                        </div>
                        <p class="c_data" style="margin-top: 16px;">Менеджер</p>
                        <div class="input flex flex-column">
                            <label for="manager">Выберите менеджера</label>
                            <select id="manager"></select>
                        </div>
                    </form>
                </div>
                <div v-else>
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input type="tel" id="phone" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" />
                                <p class="warning">
                                    На эту почту придет письмо c ссылкой на создание пароля
                                </p>
                            </div>
                        </div>
                        <p class="c_data" style="margin-top: 16px;">Менеджер</p>
                        <div class="input flex flex-column">
                            <label for="manager">Выберите менеджера</label>
                            <select id="manager"></select>
                        </div>
                        <p class="c_data" style="margin-top: 16px;">Договор</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="contract">Номер договора*</label>
                                <input type="text" id="contract" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="deadline">Срок договора*</label>
                                <select id="deadline "></select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button v-if="currentModal.action === 'edit'" class="btn-save">Сохранить</button>
                <button v-else class="btn-save">Создать</button>
            </div>
        </template>
    </BaseModal>
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
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
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
    background: none;
}

.add_manager:hover {
    background: #e2e7e9;
}

.btn-cancel,
.btn-save {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
}

.btn-save {
    background: #4e9f7d;
    color: #fff;
    transition: 0.3s;
}

.btn-save:hover {
    background: #428569;
}

.btn-cancel {
    margin-right: 10px;
    color: #242424;
    background: #f3f5f6;
    transition: 0.3s;
}

.btn-cancel:hover {
    background: #dfe4e7;
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

/* form {
    column-gap: 16px;
} */

.input {
    width: 100%;
    row-gap: 8px;
}

.input label,
.warning {
    color: #969ba0;
}

.input input,
.input select {
    border: 1px solid #e8eaeb;
    height: 45px;
    border-radius: 12px;
    width: 100%;
    padding: 0 20px;
}

.c_data {
    font-weight: 500;
}
</style>
