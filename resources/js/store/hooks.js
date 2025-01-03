import { reactive, computed } from 'vue';

export function userNotification() {
    const state = reactive({
        notification: 0,
    });
    // Геттеры
    const user_Notification = computed(() => state.notification);
    // Методы
    return { user_Notification };
}
