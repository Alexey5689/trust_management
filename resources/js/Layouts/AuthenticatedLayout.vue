<script setup>
import { ref } from 'vue';
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

const props = defineProps({
    userRole: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="flex">
        <div class="sidebar_box">
            <div></div>
            <nav class="flex flex-column nav_box">
                <NavLink
                    :href="route(`${props.userRole}.profile`)"
                    :active="route().current(`${props.userRole}.profile`)"
                >
                    <Icon_personal_account />
                    Личный кабинет
                </NavLink>
                <NavLink
                    v-if="props.userRole === 'admin' || props.userRole === 'manager'"
                    :href="route(`${props.userRole}.clients`)"
                    :active="route().current(`${props.userRole}.clients`)"
                >
                    <Icon_users />
                    Клиенты
                </NavLink>
                <NavLink
                    v-if="props.userRole === 'admin'"
                    :href="route(`${props.userRole}.managers`)"
                    :active="route().current(`${props.userRole}.managers`)"
                >
                    <Icon_users />
                    Менеджеры
                </NavLink>
                <NavLink
                    :href="route(`${props.userRole}.contracts`)"
                    :active="route().current(`${props.userRole}.contracts`)"
                >
                    <Icon_contract />
                    Договоры
                </NavLink>
                <NavLink
                    :href="route(`${props.userRole}.applications`)"
                    :active="route().current(`${props.userRole}.applications`)"
                >
                    <Icon_applications />
                    Заявки
                </NavLink>
                <NavLink v-if="props.userRole === 'client'" :href="route('client.balanceTransactions')">
                    Баланс и транзакции
                </NavLink>
            </nav>
        </div>

        <div class="flex flex-column content_box">
            <header class="" v-if="$slots.header">
                <div class="flex align-center justify-end">
                    {{ props.userRole }}
                    <Icon_notifications />
                    <ResponsiveNavLink
                        :href="route('logout')"
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
    width: 332px;
    height: 100vh;
    padding: 16px;
}

.content_box {
    background: #f3f5f6;
    width: calc(100vw - 332px);
    height: 100vh;
    padding: 22px 32px;
}

.nav_box {
    margin: 0 auto;
    width: 300px;
    row-gap: 4px;
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
}

.nav_box .active,
.nav_box a:hover {
    background: #4e9f7d1a;
    color: #4e9f7d;
    border-radius: 24px;
}

.nav_box .active svg {
    fill: #4e9f7d;
}

.btn {
    width: 111px;
    height: 48px;
    background: none;
    margin-left: 16px;
}

.btn svg {
    margin-right: 4px;
}
</style>
