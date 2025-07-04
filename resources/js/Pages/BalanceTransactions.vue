<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Icon_output from '@/Components/Icon/Output.vue';
import Icon_input from '@/Components/Icon/Input.vue';
import {
    formatDateBalanceTransactions,
    formatNumberBalanceTransactions,
    formatDateClientContract,
    formatNumber,
} from '@/helpers.js';
const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    transactions: {
        type: Array,
        required: true,
    },
    contracts: {
        type: Array,
        required: true,
    },
    balance: {
        type: Array,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
    user: {
        type: Array,
        required: true,
    },
    notifications: {
        type: Array,
        required: false,
    },
});

// Получаем компоненты даты отдельно
const date = new Date();
const day = date.getDate();
const month = date.toLocaleDateString('ru-RU', { month: 'long' });
const year = date.getFullYear();

// Собираем строку и делаем месяц с заглавной буквы
const currentDate = `${day} ${month.charAt(0).toUpperCase() + month.slice(1)} ${year}`;
// Вычисление основной суммы
const sum = computed(() => {
    return props.contracts.length ? props.contracts.reduce((total, contract) => total + contract.sum, 0) : null;
});

const transactions = computed(() => props.transactions.sort((a, b) => new Date(b.date_transition) - new Date(a.date_transition)));
</script>

<template>
    <Head title="Баланс и транзакции" />
    <AuthenticatedLayout
        :userInfo="props.user"
        :userRole="props.role"
        :toast="props.status"
        :notifications="props.notifications"
    >
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Баланс и транзакции</h2>
            </div>
        </template>
        <template #main>
            <div class="card" style="width: 354px">
                <p class="info_date">Актуальный на {{ currentDate }}</p>
                <h1 class="title-card">Баланс</h1>
                <div class="client">
                    <div class="client_info">
                        <p style="font-weight: 500">Основная сумма</p>
                        <p>{{ formatNumberBalanceTransactions(props.balance.main_sum) }} ₽</p>
                    </div>
                    <div class="client_info">
                        <p style="font-weight: 500">Дивиденды</p>
                        <p>
                            {{ formatNumberBalanceTransactions(props.balance.dividends) }}
                            ₽
                        </p>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top: 32px">
                <h1 class="title-card">Транзакции</h1>

                <div class="flex flex-column" style="row-gap: 28px">
                    <div
                        class="flex align-center justify-between"
                        v-for="transaction in transactions"
                        :key="transaction.id"
                    >
                        <div style="width: 164px">
                            <div v-if="transaction.sourse === 'Договор'" class="input flex justify-center align-center">
                                <Icon_input />
                            </div>
                            <div v-else class="output flex justify-center align-center">
                                <Icon_output />
                            </div>
                        </div>
                        <div style="width: 164px">
                            <p>
                                {{ formatDateClientContract(transaction.date_transition) }}
                            </p>
                            <span class="month_year">{{
                                formatDateBalanceTransactions(transaction.date_transition)
                            }}</span>
                        </div>
                        <div style="width: 164px; font-weight: 500">
                            <p>
                                {{
                                    transaction.sourse === 'Договор'
                                        ? '+' + formatNumber(transaction.sum_transition)
                                        : '-' + formatNumber(transaction.sum_transition)
                                }}
                            </p>
                        </div>
                        <div style="width: 164px; text-align: center; color: #969ba0">
                            {{ transaction.sourse }}
                        </div>
                        <div style="width: 164px" class="flex justify-end">
                            <p
                                class="flex align-center justify-center"
                                :class="transaction.sourse === 'Договор' ? 'green' : 'red'"
                            >
                                {{ transaction.sourse === 'Договор' ? 'Пополнение' : 'Вывод' }}
                            </p>
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
    padding: 32px;
    background: #fff;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.info_date {
    color: #a7adb2;
    margin-bottom: 4px;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    margin-bottom: 16px;
}

.client {
    display: flex;
    column-gap: 16px;
}

.client_info {
    background: #f3f5f6;
    padding: 16px 20px;
    border-radius: 24px;
}

.input {
    background: #23b347;
    height: 44px;
    width: 44px;
    border-radius: 100px;
}

.output {
    background: #c4c6c7;
    height: 44px;
    width: 44px;
    border-radius: 100px;
}

.month_year {
    color: #969ba0;
    margin-top: 6px;
    display: inline-block;
}

.red,
.green {
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    line-height: 23.2px;
    height: 43px;
    border-radius: 100px;
}

.red {
    background: #f14444;
    width: 84px;
}

.green {
    background: #23b347;
    width: 130px;
}
</style>
