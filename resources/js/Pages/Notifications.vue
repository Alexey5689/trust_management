<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { formatDateNotificztion, formatTimeNotificztion } from '@/helpers.js';
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    notifications: {
        type: Array,
        required: true,
    },
    user: {
        type: Array,
        required: true,
    },
});

const notifications = [
    {
        id: 1,
        title: 'Окончание договора',
        body: '30 Сентября 2024 будет закончен срок Договора №2402',
        date: '02.12.2024',
        time: '1:10',
    },
    {
        id: 2,
        title: 'Окончание договора',
        body: '30 Сентября 2024 будет закончен срок Договора №2402 с Ивановым Иваном Ивановичем',
        date: '03.12.2024',
        time: '1:20',
    },
];
</script>
<template>
    <Head title="Notifications" />
    <AuthenticatedLayout :userInfo="props.user" :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Уведомления</h2>
            </div>
        </template>
        <template #main>
            {{ props.notifications }}
            <div class="flex flex-column r-gap" style="width: 550px">
                <div class="card flex flex-column" v-for="notification in props.notifications" :key="notification.id">
                    <h3 class="card_title">{{ notification.title }}</h3>
                    <p class="card_body">{{ notification.content }}</p>
                    <div class="card_date flex justify-between">
                        <span>{{ formatDateNotificztion(notification.created_at) }}</span>
                        <span>{{ formatTimeNotificztion(notification.created_at) }}</span>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
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
    padding: 16px 20px;
    border-radius: 24px;
    row-gap: 8px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.card_title {
    font-weight: 500;
}

.card_date {
    color: #969ba0;
}
</style>
