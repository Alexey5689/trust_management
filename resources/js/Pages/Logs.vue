<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { formatDate } from '@/helpers.js';
const props = defineProps({
    logs: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
});
</script>
<template>
    <Head title="Logs" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="title">Логи</h2>
        </template>
        <template #main>
            <div class="card">
                <header>
                    <h2 class="title-card">Логи</h2>
                </header>
                <!-- {{ props.logs }} -->
                <div class="card-content flex" v-for="log in props.logs" :key="log.id">
                    <div class="card-item">
                        <InputLabel for="number" value="№" />
                        <p class="text">{{ log.id }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="creator" value="Создатель" />
                        <p class="text">{{ log.creator.full_name }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="change" value="Что изменено" />
                        <p class="text">{{ log.change }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="old_value" value="Старое значение" />
                        <p class="text">{{ log.old_value }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="new_value" value="Новое значение" />
                        <p class="text">{{ log.new_value }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="target" value="Цель" />
                        <p class="text">{{ log.target.full_name }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="action" value="Действие" />
                        <p class="text">{{ log.action }}</p>
                    </div>
                    <div class="card-item">
                        <InputLabel for="date" value="Дата изменения" />
                        <p class="text">{{ formatDate(log.created_at) }}</p>
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
    padding: 32px;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
}

.card-content {
    margin-top: 32px;
}

.card-item {
    width: 33.33%;
}

.text {
    margin-bottom: 16px;
}
</style>
