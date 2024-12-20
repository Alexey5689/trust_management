import { reactive, computed } from 'vue';

export function useUserInfo() {
    const storedUserInfo = localStorage.getItem('userInfo');
    const state = reactive({
        userInfo: JSON.parse(storedUserInfo),
    });
    // Геттеры
    const user_Info = computed(() => state.userInfo);
    // const manager = computed(() => state.userInfo.manager ?? '');
    // const managerEmail = computed(() => state.userInfo.managerEmail ?? '');
    // const main_sum = computed(() => state.userInfo.main_sum ?? null);

    // Методы
    return { user_Info };
}
