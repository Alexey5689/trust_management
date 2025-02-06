<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { formatDateNotificztion, formatTimeNotificztion } from '@/helpers.js';
import { useForm } from '@inertiajs/vue3';

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
    notification: {
        type: Array,
        required: true,
    },
});

const localNotifications = ref([
    ...props.notifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)),
]);

const form = useForm({
    is_read: true,
});

const isRead = (id) => {
    const notificationIndex = localNotifications.value.findIndex((n) => n.id === id);

    if (notificationIndex !== -1 && !localNotifications.value[notificationIndex].is_read) {
        form.patch(route(`${props.role}.notification.update`, { notification: id }), {
            onSuccess: () => {
                localNotifications.value[notificationIndex].is_read = true;
            },
            onFinish: () => form.reset(),
            onError: () => {
                console.error('Ошибка:', form.errors); // Лог ошибок
            },
        });
    }
};
</script>

<template>
    <Head title="Notifications" />
    <AuthenticatedLayout :userInfo="props.user" :userRole="role" :notifications="props.notification">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Уведомления</h2>
            </div>
        </template>
        <template #main>
            <div class="flex flex-column r-gap" style="width: 550px">
                <button
                    class="card flex flex-column"
                    v-for="notification in localNotifications"
                    :key="notification.id"
                    @click="isRead(notification.id)"
                >
                    <h3 class="card_title" :class="{ bold: !notification.is_read }">{{ notification.title }}</h3>
                    <p class="card_body" :class="{ bold: !notification.is_read }">{{ notification.content }}</p>
                    <div class="card_date flex justify-between w-100" :class="{ bold: !notification.is_read }">
                        <span>{{ formatDateNotificztion(notification.created_at) }}</span>
                        <span>{{ formatTimeNotificztion(notification.created_at) }}</span>
                    </div>
                </button>
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

.bold {
    font-weight: 700;
}
</style>
