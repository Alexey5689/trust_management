<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Icon_logo from '@/Components/Icon/Logo.vue';
import Icon_logo_name from '@/Components/Icon/LogoName.vue';
import { inject } from "vue";

const route = inject('route');

const page = usePage();
const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: page.props.email,
    token: page.props.token,
    password: '',
    password_confirmation: '',
    remember: false,
});

const submit = () => {
    form.patch(route('password.create'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
const passwordPattern = 'Минимум 8 символов в верхнем и нижнем регистре, 1 заглавная буква, 1 цифра, 1 спецсимвол(!, @, #, *)';
</script>

<template>
    <GuestLayout>
        <Head title="Создание пароля" />
        <div class="login-container">
            <h1>Создание пароля</h1>
            <form @submit.prevent="submit">
                <div class="flex flex-column">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" v-bind:title="passwordPattern" v-model="form.password" required autofocus />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="flex flex-column">
                    <label for="password">Подтверждение пароля</label>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="current-password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
                <div>
                    <button>Создать</button>
                </div>
            </form>
        </div>
        <div class="logo-container flex align-center justify-center">
            <Icon_logo style="margin-right: 12.5px" />
            <Icon_logo_name />
        </div>
    </GuestLayout>
</template>
<style scoped>
.login-container {
    background-color: #fff;
    border-radius: 32px;
    width: 500px;
    padding: 24px 32px 32px 32px;
    text-align: center;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.login-container h1 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    letter-spacing: 0.01em;
    color: #000;
    margin-bottom: 32px;
}

.login-container form {
    display: flex;
    flex-direction: column;
    row-gap: 16px;
}

.login-container label {
    text-align: start;
    color: #969ba0;
    margin-bottom: 8px;
}

.login-container input {
    height: 40px;
    padding: 8px;
    border: 1px solid #e8eaeb;
    border-radius: 12px;
    font-size: 16px;
    width: 100%;
}

.login-container button {
    height: 45px;
    width: 100%;
    background-color: #4e9f7d;
    color: #fff;
    font-size: 14px;
    line-height: 21px;
    border: none;
    border-radius: 12px;
    margin-top: 8px;
    transition: 0.3s;
}

.login-container button:hover {
    background-color: #428569;
}

.logo-container {
    margin-top: 100px;
}
</style>
