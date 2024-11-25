import { reactive, computed } from 'vue';

export function useUserInfo() {
    const storedUserInfo = localStorage.getItem('userInfo');
    const state = reactive({
        userInfo: JSON.parse(storedUserInfo),
    });
    // Геттеры
    const user_Name_Email = computed(() => state.userInfo);
    // Методы
    return { user_Name_Email };
}
