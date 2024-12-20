<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import { fetchData } from '@/helpers';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    user: {
        type: Object,
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
const userData = ref({});
const role = ref('manager');
const error = ref(null);

const getInfo = async (url) => {
    try {
        const data = await fetchData(url); // Ожидаем завершения запроса
        userData.value = data.user ? data.user : data;
    } catch (err) {
        error.value = err; // Сохраняем ошибку
    } finally {
    }
};
const form = useForm({
    last_name: '',
    first_name: '',
    middle_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

watch(
    userData,
    (newData) => {
        form.last_name = newData.last_name;
        form.first_name = newData.first_name;
        form.middle_name = newData.middle_name;
        form.email = newData.email;
    },
    { immediate: true },
);

onMounted(() => {
    localStorage.setItem(
        'userInfo',
        JSON.stringify({
            full_name: props.user.full_name,
            email: props.user.email,
            manager: props.user.manager ?? '',
            managerEmail: props.user.managerEmail ?? '',
            main_sum: props.user.main_sum ?? null,
            dividends: props.user.dividends ?? null,
        }),
    );
});

const modalTitles = {
    contacts: 'Изменение контактных данных',
    password: 'Изменение пароля',
    email: 'Изменение почты',
};

const openModal = (type, url) => {
    getInfo(url);
    currentModal.value = type;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
};

const saveChanges = () => {
    if (currentModal.value === 'contacts') form.patch(route('profile.update'));
    else if (currentModal.value === 'password') form.patch(route('password.update'));
    else form.patch(route('email.update'));
    closeModal();
};

const isGridRole = computed(() => props.role === 'manager' || props.role === 'client');
</script>

<template>
    <Head title="Profile" />
    <AuthenticatedLayout :userInfo="props.user" :userRole="props.role" :notifications="props.status">
        <template #header>
            <h2 class="title">Личный кабинет</h2>
        </template>
        <template #main>
            <div class="card">
                <header>
                    <h2 class="title-card">Контактные данные</h2>
                </header>
                <div class="card-content flex" :class="{ grid: isGridRole }">
                    <div class="card-item">
                        <InputLabel for="last_name" value="ФИО" />
                        <p class="text">{{ props.user.full_name }}</p>
                        <button class="link-btn" @click="openModal('contacts', 'profile.edit')">
                            Изменить контактные данные
                        </button>
                    </div>
                    <div class="card-item">
                        <InputLabel for="last_name" value="Пароль" />
                        <p class="text">********</p>
                        <button class="link-btn" @click="openModal('password', 'password.edit')">
                            Изменить пароль
                        </button>
                    </div>
                    <div class="card-item">
                        <InputLabel for="last_name" value="Почта" />
                        <p class="text">{{ props.user.email }}</p>
                        <button
                            v-if="props.role === 'admin'"
                            class="link-btn"
                            @click="openModal('email', 'email.edit')"
                        >
                            Изменить почту
                        </button>
                    </div>
                    <div class="card-item" v-if="props.role !== 'admin'">
                        <InputLabel for="last_name" value="Номер телефона" />
                        <p class="text">+7 (922) 857-45-65</p>
                    </div>
                </div>
                <div v-if="props.role !== 'admin'" class="div_warning">
                    <p v-if="props.role === 'client'" class="warning">
                        Для изменения номера телефона и почты обратитесь к своему менеджеру
                    </p>
                    <p v-if="props.role === 'manager'" class="warning">
                        Для изменения почты и номера телефона обратитесь к администратору
                    </p>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal]" @close="closeModal">
        <template #default>
            <div v-if="currentModal === 'contacts'">
                <form class="flex">
                    <div class="input flex flex-column">
                        <label for="last_name">Фамилия</label>
                        <input type="text" id="last_name" v-model="form.last_name" />
                        <InputError :message="form.errors.last_name" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="first_name">Имя</label>
                        <input type="text" id="first_name" v-model="form.first_name" />
                        <InputError :message="form.errors.first_name" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="middle_name">Отчество</label>
                        <input type="text" id="middle_name" v-model="form.middle_name" />
                        <InputError :message="form.errors.middle_name" />
                    </div>
                </form>
            </div>
            <div v-else-if="currentModal === 'password'">
                <form class="flex">
                    <div class="input flex flex-column">
                        <label for="password">Новый пароль</label>
                        <input type="password" id="password" v-model="form.password" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="confirm_password">Подтвердите пароль</label>
                        <input type="password" id="confirm_password" v-model="form.password_confirmation" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </form>
                <p class="warning" style="margin-top: 16px">
                    После сохранения необходима повторная авторизация с новым паролем
                </p>
            </div>
            <div v-else-if="currentModal === 'email'">
                <form class="flex">
                    <div class="input flex flex-column">
                        <label for="email">Новая почта</label>
                        <input type="email" id="email" v-model="form.email" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div style="width: 100%">
                        <p class="warning">Description что сделать после изменения почты</p>
                    </div>
                </form>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button class="btn-save" @click="saveChanges">Сохранить</button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
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

.grid .card-item {
    width: 100%;
}

.text {
    margin-bottom: 16px;
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

form {
    column-gap: 16px;
}

.input {
    width: 100%;
    row-gap: 8px;
}

.input label,
.warning {
    color: #969ba0;
}

.input input {
    border: 1px solid #e8eaeb;
    height: 45px;
    border-radius: 12px;
    width: 100%;
    padding: 0 20px;
}

.link-btn {
    background: #f3f5f6;
    height: 45px;
    display: inline-flex;
    padding: 12px 20px;
    border-radius: 12px;
    align-items: center;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
}

.div_warning {
    margin-top: 20px;
}

@media (max-width: 1500px) {
    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        row-gap: 32px;
    }

    .div_warning {
        margin-top: 0;
    }
}
</style>
