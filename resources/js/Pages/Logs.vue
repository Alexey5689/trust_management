<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { formatDateLogs } from '@/helpers.js';
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
                <div class="logs">
                    <ul class="thead-log align-center">
                        <li class="order">№</li>
                        <li>Создатель</li>
                        <li>Что изменено</li>
                        <li>Старое значение</li>
                        <li>Новое значение</li>
                        <li>Цель</li>
                        <li>Действие</li>
                        <li>Дата изменения</li>
                    </ul>
                    <div class="items flex align-center" v-for="log in props.logs" :key="log.id">
                        <div class="card-item order">
                            <p class="text">{{ log.id }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.creator.full_name }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.change }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.old_value }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.new_value }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.target.full_name }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ log.action }}</p>
                        </div>
                        <div class="card-item">
                            <p class="text">{{ formatDateLogs(log.created_at) }}</p>
                        </div>
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
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
    overflow-x: auto;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    border-bottom: 1px solid #f3f5f6;
    padding: 24px 32px 20px 32px;
    width: 1606px;
}

.logs {
    padding: 20px 32px 32px 32px;
    width: 1606px;
}

.thead-log {
    height: 55px;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 50px 250px 150px 200px 200px 270px 250px 150px;
    border-bottom: 1px solid #f3f5f6;
}

.items {
    padding: 16px 0;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 50px 250px 150px 200px 200px 270px 250px 150px;
    border-bottom: 1px solid #f3f5f6;
}

.thead-log li {
    font-size: 16px;
    font-weight: 600;
    line-height: 23.2px;
    letter-spacing: 0.01em;
    color: #969ba0;
}

.order {
    padding-left: 12px;
}
</style>
