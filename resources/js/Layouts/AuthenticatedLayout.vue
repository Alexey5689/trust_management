<script setup>
import { ref, computed } from 'vue';
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
import Icon_arrow from '@/Components/Icon/Arrow.vue';
import profileImage from '../../img/profile.png';
import { useUserInfo } from '@/hooks.js';

const { user_Name_Email } = useUserInfo();

const props = defineProps({
    userRole: {
        type: String,
        required: true,
    },
    userInfo: {
        type: Array,
        required: false,
    },
});

const isCollapsed = ref(false);

const sidebarWidth = computed(() => (isCollapsed.value ? '92px' : '332px'));

const contentWidth = computed(() => `calc(100vw - ${isCollapsed.value ? '92px' : '332px'})`);

const arrowOpacity = computed(() => (isCollapsed.value ? 1 : 0));

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed;
};
const remote = () => {
    localStorage.removeItem('userInfo');
};
</script>

<template>
    <div class="flex">
        <div class="sidebar_box flex flex-column" :style="{ width: sidebarWidth }">
            <div class="logo_hamb flex align-center justify-between">
                <div class="flex align-center">
                    <Icon_logo style="margin-right: 12.5px" :class="{ collapsed: isCollapsed }" />
                    <Icon_logo_name v-if="!isCollapsed" />
                </div>
                <div v-if="!isCollapsed" class="hamb_box flex flex-column" @click="isCollapsed = !isCollapsed">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
            <div class="profile flex flex-column align-center">
                <div :class="{ collapsed: isCollapsed }" class="profile_img flex align-center justify-center">
                    <img :src="profileImage" alt="profile" />
                </div>
                <!-- {{ props.userInfo?.email }}  {{ props.userInfo?.full_name }} -->
                <transition name="fade-height">
                    <div v-if="!isCollapsed">
                        <p class="profile_name">
                            {{ props.userInfo?.full_name ? props.userInfo.full_name : user_Name_Email.full_name }}
                        </p>
                        <span class="profile_mail">
                            {{ props.userInfo?.email ? props.userInfo.email : user_Name_Email.email }}</span
                        >
                    </div>
                </transition>
            </div>
            <nav class="flex flex-column nav_box" :style="{ width: isCollapsed ? '60px' : '300px' }">
                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    :href="route(`${props.userRole}.profile`)"
                    :active="route().current(`${props.userRole}.profile`)"
                >
                    <Icon_personal_account />
                    <span v-if="!isCollapsed">Личный кабинет</span>
                </NavLink>
                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    v-if="props.userRole === 'admin'"
                    :href="route('admin.users')"
                    :active="route().current('admin.users')"
                >
                    <Icon_users />
                    <span v-if="!isCollapsed">Пользователи</span>
                </NavLink>
                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    v-if="props.userRole === 'manager'"
                    :href="route(`${props.userRole}.clients`)"
                    :active="route().current(`${props.userRole}.clients`)"
                >
                    <Icon_users />
                    <span v-if="!isCollapsed">Клиенты</span>
                </NavLink>
                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    :href="route(`${props.userRole}.contracts`)"
                    :active="route().current(`${props.userRole}.contracts`)"
                >
                    <Icon_contract />
                    <span v-if="!isCollapsed">Договоры</span>
                </NavLink>
                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    :href="route(`${props.userRole}.applications`)"
                    :active="route().current(`${props.userRole}.applications`)"
                >
                    <Icon_applications />
                    <span v-if="!isCollapsed">Заявки</span>
                </NavLink>

                <NavLink
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    v-if="props.userRole === 'client'"
                    :href="route('client.balance-transactions')"
                    :active="route().current('client.balance-transactions')"
                >
                    <span v-if="!isCollapsed">Баланс и транзакции</span>
                </NavLink>

                <NavLink
                    class="logs"
                    :style="{ paddingLeft: isCollapsed ? '20px' : '32px' }"
                    v-if="props.userRole === 'admin' || props.userRole === 'manager'"
                    :href="route('admin.logs')"
                    :active="route().current('admin.logs')"
                >
                    <Icon_logs />
                    <span v-if="!isCollapsed">Логи</span>
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
                    {{ props.userInfo }}
                    <Icon_notifications />
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
    padding: 16px;
    row-gap: 32px;
    transition: width 0.3s;
}

.content_box {
    background: #f3f5f6;
    height: 100vh;
    padding: 22px 32px;
    transition: 0.3s;
}

.nav_box {
    margin: 0 auto;
    /* width: 300px; */
    row-gap: 4px;
    height: 100%;
    transition: margin-top 0.3s;
}

.nav_box a {
    height: 60px;
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
    transition: 0.3s;
}

.profile_img {
    width: 90px;
    height: 90px;
    background: #f3f5f6;
    border-radius: 205px;
}

.profile_name {
    margin-top: 16px;
    font-weight: 500;
}

.profile_mail {
    margin-top: 4px;
    color: #6d757d;
}

.logs {
    margin-top: auto;
}

.btn_arrow {
    height: 44px;
    width: 44px;
    margin-right: auto;
    transition: opacity 0.3s;
    cursor: pointer;
}

.collapsed {
    height: 60px;
    width: 60px;
    transition: 0.3s;
}

.fade-height-enter-active,
.fade-height-leave-active {
    transition: height 0.3s, opacity 0.3s;
}

.fade-height-enter-from,
.fade-height-leave-to {
    height: 0;
    opacity: 0;
    overflow: hidden;
}

.fade-height-enter-to,
.fade-height-leave-from {
    height: auto;
    opacity: 1;
}
</style>
