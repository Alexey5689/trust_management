<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format, differenceInDays } from 'date-fns';
import { ru } from 'date-fns/locale';
import Icon_output from '@/Components/Icon/Output.vue';
import Icon_input from '@/Components/Icon/Input.vue';

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
});

const nowDate = format(new Date(), 'yyyy-MM-dd');

// Получаем компоненты даты отдельно
const date = new Date();
const day = date.getDate();
const month = date.toLocaleDateString('ru-RU', { month: 'long' });
const year = date.getFullYear();

// Собираем строку и делаем месяц с заглавной буквы
const currentDate = `${day} ${month.charAt(0).toUpperCase() + month.slice(1)} ${year}`;

// Функция для форматирования дат
const formatDate = (date) => format(parseISO(date), 'dd/MM/yyyy');

// Вычисление основной суммы
const sum = computed(() => {
    return props.contracts.length ? props.contracts.reduce((total, contract) => total + contract.sum, 0) : null;
});

// Вычисление дивидендов
const dividends = computed(() => {
    let totalDividends = 0;

    const contracts = Array.isArray(props.contracts) ? props.contracts : [];

    // props.contracts.forEach((contract) => {
    //     const termYears = differenceInYears(parseISO(contract.deadline), parseISO(contract.create_date));
    //     //console.log(termYears);

    //     const dailyRate = (contract.sum * (contract.procent / 100)) / 365;
    //     //console.log(dailyRate);

    //     const daysSinceStart = differenceInDays(parseISO(nowDate), parseISO(contract.create_date));
    //     //console.log(daysSinceStart);

    //     const dividendsForContract = dailyRate * Math.min(daysSinceStart, termYears * 365);
    //     //console.log(dividendsForContract);

    //     totalDividends += dividendsForContract;
    // });
    contracts.forEach((contract) => {
        const termYears = differenceInYears(parseISO(contract.deadline), parseISO(contract.create_date));
        const dailyRate = (contract.sum * (contract.procent / 100)) / 365;
        const daysSinceStart = differenceInDays(parseISO(nowDate), parseISO(contract.create_date));
        const dividendsForContract = dailyRate * Math.min(daysSinceStart, termYears * 365);
        totalDividends += dividendsForContract;
    });

    return Math.round(totalDividends);
});

// Разделяет цифру на тысячные
function formatNumber(num) {
    return Number(num)
        .toLocaleString('ru-RU', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
        .replace(',', '.');
}
</script>

<template>
    <Head title="Transaction" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Баланс и транзакции</h2>
            </div>
        </template>
        <!-- {{ props.transactions }} -->
        <template #main>
            <div class="card" style="width: 354px">
                <p class="info_date">Актуальный на {{ currentDate }}</p>
                <h1 class="title-card">Баланс</h1>
                <div class="client">
                    <div class="client_info">
                        <p style="font-weight: 500">Основная сумма</p>
                        <p>{{ formatNumber(props.balance.main_sum) }} ₽</p>
                    </div>
                    <div class="client_info">
                        <p style="font-weight: 500">Дивиденды</p>
                        <p>{{ formatNumber(props.balance.dividends) }} ₽</p>
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
                                {{ formatDate(transaction.date_transition) }}
                            </p>
                            <span class="month_year">{{
                                format(parseISO(transaction.date_transition), 'LLLL yyyy', { locale: ru }).replace(
                                    /^./,
                                    (str) => str.toUpperCase(),
                                )
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
