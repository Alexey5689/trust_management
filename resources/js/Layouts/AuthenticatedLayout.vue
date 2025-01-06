<script setup>
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Icon_exit from '@/Components/Icon/Exit.vue';
import Icon_notifications from '@/Components/Icon/Notifications.vue';
import Icon_personal_account from '@/Components/Icon/PersonalAccount.vue';
import Icon_users from '@/Components/Icon/Users.vue';
import Icon_contract from '@/Components/Icon/Contract.vue';
import Icon_applications from '@/Components/Icon/Applications.vue';
import Icon_logo from '@/Components/Icon/Logo.vue';
import Icon_logo_name from '@/Components/Icon/LogoName.vue';
import Icon_logs from '@/Components/Icon/Logs.vue';
import Icon_balance from '@/Components/Icon/Balance.vue';
import { watch, ref } from 'vue';
const props = defineProps({
    userRole: {
        type: String,
        required: true,
    },
    userInfo: {
        type: Array,
        required: false,
    },
    toast: {
        type: Array,
        required: false,
    },
    notifications: {
        type: Array,
        required: false,
    },
});

const toastMessage = ref(props.toast);

watch(
    () => props.toast,
    (newVal) => {
        toastMessage.value = newVal;
        setTimeout(() => {
            toastMessage.value = null;
        }, 2000);
    },
    { immediate: true }, // Запуск сразу после монтирования
);
</script>

<template>
    <div class="flex">
        <div class="sidebar_box flex flex-column">
            <div class="logo_hamb flex align-center justify-between">
                <div class="flex align-center">
                    <Icon_logo style="margin-right: 12.5px" />
                    <Icon_logo_name />
                </div>
            </div>
            <div class="profile flex flex-column align-center">
                <!-- {{ props.notifications }} -->
                <transition name="fade-height">
                    <div class="flex flex-column align-center w-100">
                        <p class="profile_name">
                            {{ props.userInfo?.full_name }}
                        </p>
                        <span class="profile_mail"> {{ props.userInfo?.email }}</span>
                    </div>
                </transition>
            </div>
            <nav class="flex flex-column nav_box">
                <NavLink
                    :href="route(`${props.userRole}.profile`)"
                    :active="route().current(`${props.userRole}.profile`)"
                >
                    <Icon_personal_account />
                    <span>Личный кабинет</span>
                </NavLink>
                <NavLink
                    v-if="props.userRole === 'admin'"
                    :href="route('admin.users')"
                    :active="route().current('admin.users')"
                >
                    <Icon_users />
                    <span>Пользователи</span>
                </NavLink>
                <NavLink
                    v-if="props.userRole === 'manager'"
                    :href="route(`${props.userRole}.clients`)"
                    :active="route().current(`${props.userRole}.clients`)"
                >
                    <Icon_users />
                    <span>Клиенты</span>
                </NavLink>
                <NavLink
                    :href="route(`${props.userRole}.contracts`)"
                    :active="route().current(`${props.userRole}.contracts`)"
                >
                    <Icon_contract />
                    <span>Договоры</span>
                </NavLink>
                <NavLink
                    v-if="props.userRole === 'client'"
                    :href="route('client.balance-transactions')"
                    :active="route().current('client.balance-transactions')"
                >
                    <Icon_balance />
                    <span>Баланс и транзакции</span>
                </NavLink>
                <NavLink
                    :href="route(`${props.userRole}.applications`)"
                    :active="route().current(`${props.userRole}.applications`)"
                >
                    <Icon_applications />
                    <span>Заявки</span>
                </NavLink>
                <NavLink
                    class="logs"
                    v-if="props.userRole === 'admin'"
                    :href="route('admin.logs')"
                    :active="route().current('admin.logs')"
                >
                    <Icon_logs />
                    <span>Логи</span>
                </NavLink>
            </nav>
        </div>

        <div class="flex flex-column content_box">
            <header v-if="$slots.header">
                <div class="flex align-center justify-end" style="position: relative">
                    <div v-if="props.userRole === 'client'" class="info_client flex align-center justify-end w-100">
                        <div class="your_manager flex align-center">
                            <p>Ваш менеджер</p>
                            <Icon_personal_account />
                            <div>
                                <p>{{ props.userInfo?.manager }}</p>
                                <span>{{ props.userInfo?.managerEmail }}</span>
                            </div>
                        </div>
                        {{ noRead }}
                        <Icon_balance />
                        <div style="margin-right: 40px">
                            <p class="fw">Дивиденты</p>
                            <p>{{ props.userInfo?.dividends }} ₽</p>
                        </div>
                        <div style="margin-right: 20px">
                            <p class="fw">Основная сумма</p>
                            <p>{{ props.userInfo?.main_sum }} ₽</p>
                        </div>
                    </div>
                    <div class="toast flex flex-column" v-if="toastMessage">
                        <h3>{{ props.toast[0] }}</h3>
                        <p>{{ props.toast[1] }}</p>
                    </div>

                    <NavLink
                        style="display: block; height: 44px; position: relative"
                        v-if="props.userRole === 'client' || props.userRole === 'manager'"
                        :href="route(`${props.userRole}.notification`)"
                    >
                        <div v-if="props.notifications.length > 0" class="red_point flex align-center justify-center">
                            {{ props.notifications.length }}
                        </div>
                        <Icon_notifications />
                    </NavLink>
                    <ResponsiveNavLink
                        :href="route('logout')"
                        @click="remote()"
                        method="post"
                        as="button"
                        class="flex align-center justify-center btn"
                    >
                        <Icon_exit />
                        Выйти
                    </ResponsiveNavLink>
                </div>
                <div class="">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot name="main" />
            </main>
        </div>
    </div>
</template>

<style scoped>
.sidebar_box {
    background: #fff;
    height: 100vh;
    width: 250px;
    padding: 16px;
    row-gap: 32px;
}

.content_box {
    background: #f3f5f6;
    min-height: 100%;
    width: calc(100vw - 250px);
    padding: 22px 32px;
    overflow-y: auto;
    height: 100vh;
    overflow-x: hidden;
    width: 100wh;
}

.nav_box {
    margin: 0 auto;
    row-gap: 4px;
    height: 100%;
    width: 100%;
}

.nav_box a {
    height: 60px;
    width: 100%;
    padding: 0 18px;
    -webkit-column-gap: 20px;
    -moz-column-gap: 20px;
    column-gap: 10px;
    -webkit-transition: 0.3s;
    -o-transition: 0.3s;
    transition: 0.3s;
}

.nav_box a svg {
    fill: #242424;
    -webkit-transition: 0.3s;
    -o-transition: 0.3s;
    transition: 0.3s;
}

.nav_box .active,
.nav_box a:hover {
    background: #4e9f7d1a;
    color: #4e9f7d;
    border-radius: 24px;
}

.nav_box a:hover svg,
.nav_box .active svg {
    fill: #4e9f7d;
}

.btn {
    width: 111px;
    height: 48px;
    background: none;
    margin-left: 10px;
}

.btn svg {
    margin-right: 4px;
}

.profile_name {
    margin-top: 16px;
    font-weight: 500;
    text-align: center;
}

.profile_mail {
    margin-top: 4px;
    color: #6d757d;
    width: 100%;
    text-align: center;
}

.logs {
    margin-top: auto;
}

.info_client svg {
    fill: #242424;
    margin-right: 18px;
}

.fw {
    font-weight: 500;
}

.your_manager {
    color: #969ba0;
    column-gap: 18px;
    margin-right: auto;
}

.your_manager svg {
    fill: #969ba0;
    margin-right: 0;
}

.your_manager p {
    font-weight: 500;
}

.red_point {
    height: 16px;
    width: 16px;
    background: red;
    border-radius: 100%;
    position: absolute;
    color: #fff;
    font-size: 11px;
    top: 6px;
    right: 7px;
}

.toast {
    position: absolute;
    width: 550px;
    min-height: 90px;
    background: #fff;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
    z-index: 10;
    padding: 16px 20px;
    border-radius: 24px;
    top: 61px;
}

.toast h3 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    margin-bottom: 8px;
}
</style>
