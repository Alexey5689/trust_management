<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Icon_logo from '@/Components/Icon/Logo.vue';
import Icon_logo_name from '@/Components/Icon/LogoName.vue';
import { ref, watch, inject } from 'vue';

const route = inject('route');

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
        required: false,
    },
});

const toastMessage = ref(props.status);

watch(
    () => props.status,
    (newVal) => {
        toastMessage.value = newVal;
        setTimeout(() => {
            toastMessage.value = null;
        }, 4000);
    },
    { immediate: true },
);

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />
        <div class="login-container">
            <h1>Авторизация</h1>
            <form @submit.prevent="submit">
                <div class="flex flex-column">
                    <label for="login">Логин</label>
                    <input id="login" type="email" @input="form.email = $event.target.value.toLowerCase()" v-model="form.email" required autofocus autocomplete="username" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="flex flex-column">
                    <label for="password">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>
                <div>
                    <button>Войти</button>
                </div>
            </form>
        </div>
        <div class="logo-container flex align-center justify-center">
            <Icon_logo style="margin-right: 12.5px" />
            <Icon_logo_name />
        </div>
    </GuestLayout>
    <div class="toast flex flex-column" v-if="toastMessage">
        <h3>{{ props.status[0] }}</h3>
        <p>{{ props.status[1] }}</p>
    </div>
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
.toast {
    position: fixed;
    width: 550px;
    min-height: 90px;
    background: #f9fafa;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
    z-index: 100;
    padding: 16px 20px;
    border-radius: 24px;
    top: 124px;
    right: 0;
}

.toast h3 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    margin-bottom: 8px;
}
</style>
