<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import { fetchData } from '@/helpers';
import { calculateDeadlineDate } from '@/helpers.js';
import InputError from '@/Components/InputError.vue';

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
const error = ref(null);
const userData = ref({});
const selectedDuration = ref('');

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

const getInfo = async (url, userId) => {
    console.log(url, userId);
    try {
        const data = await fetchData(url, { user: userId }); // Ожидаем завершения запроса
        userData.value = data.user ? data.user : data;
    } catch (err) {
        error.value = err; // Сохраняем ошибку
    } finally {
    }
};

const handleDropdownSelect = (option, userId, type) => {
    switch (option.action) {
        case 'edit':
            openModal(type, userId, 'edit', option.url);
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

const openModal = (type, userId, action = 'add', url) => {
    // console.log(type, userId, action, url);

    if (action === 'edit') getInfo(url, userId);

    currentModal.value = { type, userId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
    form.reset(
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'phone_number',
        'manager_id',
        'contract_number',
        'sum',
        'deadline',
        'procent',
        'payments',
        'agree_with_terms',
        'create_date',
    );
};

const form = useForm({
    last_name: '',
    first_name: '',
    middle_name: '',
    phone_number: '+7',
    email: '',
    contract_number: null,
    sum: null,
    deadline: '', // Срок договора
    procent: '', // Процентная ставка
    agree_with_terms: false, // Для чекбокса
    create_date: new Date().toISOString().substr(0, 10), // Дата заключения
    contract_status: true,
    payments: '', // Выплаты
    manager_id: '', // Новое поле для выбора менеджера
});
watch(
    userData,
    (newData) => {
        form.last_name = newData.last_name;
        form.first_name = newData.first_name;
        form.middle_name = newData.middle_name;
        form.email = newData.email;
        form.phone_number = newData.phone_number;
        form.contract_number = newData.contract_number;
        form.sum = newData.sum;
        form.deadline = newData.deadline;
        form.procent = newData.procent;
        form.payments = newData.payments;
        form.manager_id = newData.manager_id;
    },
    { immediate: true },
);
const handleDeadlineChange = (event) => {
    const selectedDuration = event.target.value;
    if (selectedDuration === '1 год') {
        form.deadline = calculateDeadlineDate(1, form.create_date);
    } else {
        form.deadline = calculateDeadlineDate(3, form.create_date);
    }
};
const addCountryCode = () => {
    if (!form.phone_number.startsWith('+7')) {
        form.phone_number = '+7'; // Принудительно добавляем код страны
    }
};

const createUser = () => {
    form.post(route(`admin.registration.${currentModal.value.type}`));
    closeModal();
};
const updateUser = () => {
    form.patch(route(`admin.edit.${currentModal.value.type}`, { user: currentModal.value.userId }));
    closeModal();
};
</script>

<template>
    <Head title="All Users" />
    <AuthenticatedLayout :userRole="role" :notifications="props.status">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Пользователи</h2>
                <div>
                    <button @click="openModal('client')" class="add_client link-btn">Добавить клиента</button>
                    <button @click="openModal('manager')" class="add_manager link-btn">Добавить менеджера</button>
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
                        <div class="items-manager align-center" v-for="(manager, index) in managers" :key="manager.id">
                            <div class="card-item order">
                                <p class="text">{{ index + 1 }}</p>
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
                                        { label: 'Изменить', action: 'edit', url: 'admin.edit.manager' },
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
                        </div>
                    </div>
                </div>
                <div class="card">
                    <header>
                        <h2 class="title-card">Клиенты</h2>
                    </header>
                    <div class="card-content">
                        <ul class="thead-client align-center">
                            <li class="order">№</li>
                            <li>ФИО</li>
                            <li>Номер телефона</li>
                            <li>Email</li>
                            <li>Менеджер</li>
                        </ul>
                        <div
                            class="items-client align-center"
                            v-for="(client, index) in props.clients"
                            :key="client.id"
                        >
                            <div class="card-item order">
                                <p class="text">{{ index + 1 }}</p>
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
                                        { label: 'Изменить', action: 'edit', url: 'admin.edit.client' },
                                        { label: 'Сбросить пароль', action: 'resetPassword' },
                                        { label: 'Удалить клиента', action: 'delete' },
                                    ]"
                                    @select="handleDropdownSelect($event, client.id, 'client')"
                                >
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

    <BaseModal
        v-if="isModalOpen"
        :isOpen="isModalOpen"
        :title="modalTitles[currentModal.type][currentModal.action]"
        @close="closeModal"
    >
        <template #default>
            <div v-if="currentModal.type === 'manager'">
                <div v-if="currentModal.action === 'edit'">
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" v-model.trim="form.last_name" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" v-model.trim="form.first_name" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" v-model.trim="form.middle_name" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input
                                    type="tel"
                                    maxlength="12"
                                    placeholder="+7XXXXXXXXXX"
                                    id="phone"
                                    @input="addCountryCode"
                                    v-model.trim="form.phone_number"
                                />
                                <InputError :message="form.errors.phone_number" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" v-model.trim="form.email" />
                                <InputError :message="form.errors.email" />
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
                                <input type="text" id="last_name" v-model.trim="form.last_name" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" v-model.trim="form.first_name" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" v-model.trim="form.middle_name" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input
                                    type="tel"
                                    maxlength="12"
                                    placeholder="+7XXXXXXXXXX"
                                    id="phone"
                                    @input="addCountryCode"
                                    v-model.trim="form.phone_number"
                                />
                                <InputError :message="form.errors.phone_number" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" v-model.trim="form.email" />
                                <InputError :message="form.errors.email" />
                                <p class="warning">На эту почту придет письмо c ссылкой на создание пароля</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div v-if="currentModal.type === 'client'">
                <div v-if="currentModal.action === 'edit'">
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" v-model.trim="form.last_name" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" v-model.trim="form.first_name" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" v-model.trim="form.middle_name" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input
                                    type="tel"
                                    maxlength="12"
                                    placeholder="+7XXXXXXXXXX"
                                    id="phone"
                                    @input="addCountryCode"
                                    v-model.trim="form.phone_number"
                                />
                                <InputError :message="form.errors.phone_number" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" v-model.trim="form.email" />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>
                        <p class="c_data" style="margin-top: 16px">Менеджер</p>
                        <div class="input flex flex-column">
                            <label for="manager">Выберите менеджера</label>
                            <select id="manager" v-model.trim="form.manager_id">
                                <option disabled></option>
                                <option v-for="manager in props.managers" :key="manager.id" :value="manager.id">
                                    {{ manager.full_name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.manager_id" />
                        </div>
                    </form>
                </div>
                <div v-else>
                    <form class="flex flex-column r-gap">
                        <p class="c_data">Контактные данные</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="last_name">Фамилия*</label>
                                <input type="text" id="last_name" v-model.trim="form.last_name" />
                                <InputError :message="form.errors.last_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="first_name">Имя*</label>
                                <input type="text" id="first_name" v-model.trim="form.first_name" />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="middle_name">Отчество*</label>
                                <input type="text" id="middle_name" v-model.trim="form.middle_name" />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="phone">Номер телефона*</label>
                                <input
                                    type="tel"
                                    maxlength="12"
                                    placeholder="+7XXXXXXXXXX"
                                    id="phone"
                                    @input="addCountryCode"
                                    v-model.trim="form.phone_number"
                                />
                                <InputError :message="form.errors.phone_number" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="email">Email*</label>
                                <input type="email" id="email" v-model.trim="form.email" />
                                <InputError :message="form.errors.email" />
                                <p class="warning">На эту почту придет письмо c ссылкой на создание пароля</p>
                            </div>
                        </div>
                        <p class="c_data" style="margin-top: 16px">Менеджер</p>
                        <div class="input flex flex-column">
                            <label for="manager">Выберите менеджера</label>
                            <select id="manager" v-model.trim="form.manager_id">
                                <option disabled></option>
                                <option v-for="manager in props.managers" :key="manager.id" :value="manager.id">
                                    {{ manager.full_name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.manager_id" />
                        </div>
                        <p class="c_data" style="margin-top: 16px">Договор</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="contract">Номер договора*</label>
                                <input type="text" id="contract" v-model.trim="form.contract_number" />
                                <InputError :message="form.errors.contract_number" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="deadline">Срок договора*</label>
                                <select id="deadline " v-model="selectedDuration" @change="handleDeadlineChange">
                                    <option disabled></option>
                                    <option value="1 год">1 год</option>
                                    <option value="3 года">3 года</option>
                                </select>
                                <InputError :message="form.errors.deadline" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="bank">Ставка, %*</label>
                                <input type="text" id="bank" v-model.trim="form.procent" />
                                <InputError :message="form.errors.procent" />
                            </div>
                            <div class="input flex checkbox">
                                <input type="checkbox" id="checkbox" v-model="form.agree_with_terms" />
                                <label for="checkbox">Вычислить дивиденды по истечению срока</label>
                                <InputError :message="form.errors.agree_with_terms" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="date">Дата*</label>
                                <input type="date" id="date" v-model="form.create_date" />
                                <InputError :message="form.errors.create_date" />
                            </div>
                            <div v-if="!form.agree_with_terms" class="input flex flex-column">
                                <label for="deadline">Выплаты*</label>
                                <select id="deadline " v-model="form.payments">
                                    <option disabled></option>
                                    <option value="Ежеквартально">Ежеквартально</option>
                                    <option value="Ежегодно">Ежегодно</option>
                                </select>
                                <InputError :message="form.errors.payments" />
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="sum">Сумма*</label>
                                <input type="number" min="0" id="sum" v-model.trim="form.sum" />
                                <InputError :message="form.errors.sum" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button @click="updateUser" v-if="currentModal.action === 'edit'" class="btn-save">Сохранить</button>
                <button @click="createUser" v-else class="btn-save">Создать</button>
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

.checkbox {
    align-items: end;
}

.checkbox input {
    margin-bottom: 11px;
    margin-right: 12px;
    width: 24px;
    height: 24px;
}

.checkbox label {
    color: #242424;
}

.c_data {
    font-weight: 500;
}
</style>
