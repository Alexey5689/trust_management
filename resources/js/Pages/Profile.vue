<script setup>
import { onMounted, ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// import Modal from '@/Components/Modal/ModelTest.vue';

import BaseModal from '@/Components/Modal/BaseModal.vue';

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
    localStorage.setItem('userInfo', JSON.stringify({ full_name: props.user.full_name, email: props.user.email }));
});

const modalTitles = {
    contacts: 'Изменение контактных данных',
    password: 'Изменение пароля',
    email: 'Изменение почты',
};
const urls = {
    contacts: 'profile-edit',
    password: 'password-edit',
    email: 'email-edit',
};

// Данные формы

const openModal = (type) => {
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
                <div class="card-content flex">
                    <div class="card-item">
                        <InputLabel for="last_name" value="ФИО" />
                        <p class="text">{{ props.user.full_name }}</p>
                        <button class="link-btn" @click="openModal('contacts')">Изменить контактные данные</button>
                    </div>

                    <div class="card-item">
                        <InputLabel for="last_name" value="Пароль" />
                        <p class="text">********</p>
                        <button class="link-btn" @click="openModal('password')">Изменить пароль</button>
                    </div>

                    <div class="card-item">
                        <InputLabel for="last_name" value="Почта" />
                        <p class="text">{{ props.user.email }}</p>
                        <button v-if="props.role === 'admin'" class="link-btn" @click="openModal('email')">
                            Изменить почту
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>

    <BaseModal
        v-if="isModalOpen"
        :isOpen="isModalOpen"
        :title="modalTitles[currentModal]"
        :url="urls[currentModal]"
        @close="closeModal"
        @response="userData = $event"
    >
        <template #default>
            <div v-if="currentModal === 'contacts'">
                <form class="flex">
                    <div class="input flex flex-column">
                        <label for="last_name">Фамилия</label>
                        <input type="text" id="last_name" v-model="form.last_name" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="first_name">Имя</label>
                        <input type="text" id="first_name" v-model="form.first_name" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="middle_name">Отчество</label>
                        <input type="text" id="middle_name" v-model="form.middle_name" />
                    </div>
                </form>
            </div>
            <div v-else-if="currentModal === 'password'">
                <form class="flex">
                    <div class="input flex flex-column">
                        <label for="password">Новый пароль</label>
                        <input type="password" id="password" v-model="form.password" />
                    </div>
                    <div class="input flex flex-column">
                        <label for="confirm_password">Подтвердите пароль</label>
                        <input type="password" id="confirm_password" v-model="form.password_confirmation" />
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

    <!-- <Modal /> -->
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
</style>
