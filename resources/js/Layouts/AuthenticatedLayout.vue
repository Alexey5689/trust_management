<script setup>
import { ref, computed  } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import Icon_exit from '@/Components/Icon/Exit.vue';
import Icon_notifications from '@/Components/Icon/Notifications.vue';
import Icon_personal_account from '@/Components/Icon/PersonalAccount.vue';
import Icon_users from '@/Components/Icon/Users.vue';
import Icon_contract from '@/Components/Icon/Contract.vue';
import Icon_applications from '@/Components/Icon/Applications.vue';
import Icon_logo from '@/Components/Icon/Logo.vue';
import Icon_logo_name from '@/Components/Icon/LogoName.vue';
import Icon_logs from '@/Components/Icon/Logs.vue';
import Icon_arrow from '@/Components/Icon/Arrow.vue';
import profileImage from '../../img/profile.png';

const props = defineProps({
    userRole: {
        type: String,
        required: true,
    },
});

const isCollapsed = ref(false);

const sidebarWidth = computed(() => (isCollapsed.value ? '92px' : '332px'));

const contentWidth = computed(() => `calc(100vw - ${isCollapsed.value ? '92px' : '332px'})`);

const arrowOpacity = computed(() => (isCollapsed.value ? 1 : 0));

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed;
};
</script>

<template>
    <div class="flex">
        <div class="sidebar_box flex flex-column" :style="{ width: sidebarWidth }">
            <div class="logo_hamb flex align-center justify-between">
                <div class="flex align-center">
                    <Icon_logo style="margin-right: 12.5px;" />
                    <Icon_logo_name />
                </div>
                <div class="hamb_box flex flex-column" @click="isCollapsed = !isCollapsed">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
            <div class="profile flex flex-column align-center">
                <div class="profile_img flex align-center justify-center">
                    <img :src="profileImage" alt="profile">
                </div>
                <p class="profile_name">Сергей Демидов</p>
                <span class="profile_mail">demidov23232399@icloud.com</span>
            </div>
            <nav class="flex flex-column nav_box">
                <NavLink :href="route(`${props.userRole}.profile`)"
                    :active="route().current(`${props.userRole}.profile`)">
                    <Icon_personal_account />
                    Личный кабинет
                </NavLink>
                <NavLink v-if="props.userRole === 'admin'" :href="route('admin.users')"
                    :active="route().current('admin.users')">
                    <Icon_users />
                    Пользователи
                </NavLink>
                <NavLink v-if="props.userRole === 'manager'" :href="route(`${props.userRole}.clients`)"
                    :active="route().current(`${props.userRole}.clients`)">
                    <Icon_users />
                    Клиенты
                </NavLink>
                <NavLink :href="route(`${props.userRole}.contracts`)"
                    :active="route().current(`${props.userRole}.contracts`)">
                    <Icon_contract />
                    Договоры
                </NavLink>
                <NavLink :href="route(`${props.userRole}.applications`)"
                    :active="route().current(`${props.userRole}.applications`)">
                    <Icon_applications />
                    Заявки
                </NavLink>
                <NavLink v-if="props.userRole === 'client'" :href="route('client.balance.ransactions')">
                    Баланс и транзакции
                </NavLink>
                <NavLink class="logs" v-if="props.userRole === 'admin'">
                    <Icon_logs />
                    Логи
                </NavLink>
            </nav>
        </div>

        <div class="flex flex-column content_box" :style="{ width: contentWidth }">
            <header class="" v-if="$slots.header">
                <div class="flex align-center justify-end">
                    <div class="btn_arrow" :style="{ opacity: arrowOpacity }" @click="toggleSidebar">
                        <Icon_arrow />
                    </div>
                    {{ props.userRole }}
                    <Icon_notifications />
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                        class="flex align-center justify-center btn">
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
    /* width: 332px; */
    height: 100vh;
    padding: 16px;
    row-gap: 32px;
    transition: width 0.3s ease;
}

.content_box {
    background: #f3f5f6;
    /* width: calc(100vw - 332px); */
    height: 100vh;
    padding: 22px 32px;
    transition: width 0.3s ease;
}

.nav_box {
    margin: 0 auto;
    width: 300px;
    row-gap: 4px;
    height: 100%;
}

.nav_box a {
    height: 68px;
    width: 100%;
    padding-left: 32px;
    -webkit-column-gap: 20px;
    -moz-column-gap: 20px;
    column-gap: 20px;
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

.hamb_box {
    row-gap: 4px;
    cursor: pointer;
}

.hamb_box:hover .bar:last-child {
    width: 14px;
}

.bar {
    display: block;
    width: 19px;
    height: 2px;
    background: #242424;
    border-radius: 2px;
    transition: width 0.3s;
}

.profile_img {
    width: 90px;
    height: 90px;
    background: #F3F5F6;
    border-radius: 205px;
}

.profile_name {
    margin-top: 16px;
    font-weight: 500;
}

.profile_mail {
    margin-top: 4px;
    color: #6D757D;
}

.logs {
    margin-top: auto;
}

.btn_arrow {
    height: 44px;
    width: 44px;
    margin-right: auto;
    transition: opacity 0.3s ease;
    cursor: pointer;
}
</style>
