<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';

const user = ref(null);

onMounted(async () => {
    try {
        const response = await axios.get('/profile/edit');
        user.value = response.data.user;
        console.log(user.value);
    } catch (error) {
        console.error('Ошибка при загрузке данных профиля:', error);
    }
});
</script>

<template>
    <h1>Привет</h1>
    <div v-if="user">
        <p>Фамилия: {{ user?.last_name }}</p>
        <p>Имя: {{ user?.first_name }}</p>
        <p>Отчество: {{ user?.middle_name }}</p>
    </div>
    <div v-else>
        <p>Загрузка данных...</p>
    </div>
</template>


