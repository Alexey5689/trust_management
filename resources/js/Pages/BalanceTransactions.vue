<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format, differenceInDays } from 'date-fns';
import { computed, ref } from 'vue';

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
});

const nowDate = format(new Date(), 'yyyy-MM-dd');
const currentDate = format(new Date(), 'dd/MM/yyyy');

// Функция для форматирования дат
const formatDate = (date) => format(parseISO(date), 'dd/MM/yyyy');

// Вычисление основной суммы
const sum = computed(() => {
    return props.contracts.reduce((total, contract) => total + contract.sum, 0);
});

// Вычисление дивидендов
const dividends = computed(() => {
    let totalDividends = 0;

    props.contracts.forEach((contract) => {
        const termYears = differenceInYears(parseISO(contract.deadline), parseISO(contract.create_date));
        //console.log(termYears);

        const dailyRate = (contract.sum * (contract.procent / 100)) / 365;
        //console.log(dailyRate);

        const daysSinceStart = differenceInDays(parseISO(nowDate), parseISO(contract.create_date));
        //console.log(daysSinceStart);

        const dividendsForContract = dailyRate * Math.min(daysSinceStart, termYears * 365);
        //console.log(dividendsForContract);

        totalDividends += dividendsForContract;
    });

    return Math.round(totalDividends);
});
</script>

<template>
    <Head title="Transaction" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Баланс и транзакции</h2>
        </template>
        <template #main>
            <div class="py-12">
                {{ props.transactions }}
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <p>Актуальный на {{ currentDate }}</p>
                            <h1>Баланс</h1>
                            <div class="client">
                                <div>
                                    <p>Основная сумма: {{ sum }}</p>
                                </div>
                                <div>
                                    <p>Дивиденды: {{ dividends }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 text-gray-900">
                            <h1>Транзакции</h1>
                            <div class="client" v-for="transaction in transactions" :key="transaction.id">
                                <div>
                                    {{ transaction.sourse === 'Договор' ? '->' : '<-' }}
                                </div>
                                <div>
                                    <InputLabel for="contract_number" value="Дата" />
                                    <p>
                                        {{ formatDate(transaction.date_transition) }}
                                    </p>
                                </div>
                                <div>
                                    <InputLabel for="contract_number" value="Сумма" />
                                    <p>
                                        {{
                                            transaction.sourse === 'Договор'
                                                ? '+' + transaction.sum_transition
                                                : '-' + transaction.sum_transition
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <InputLabel for="sourse" value="Источник" />
                                    {{ transaction.sourse }}
                                </div>
                                <div :class="transaction.sourse === 'Договор' ? 'text-green-600' : 'text-red-600'">
                                    {{ transaction.sourse === 'Договор' ? 'Пополнение' : 'Списание' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>

<style scoped>
.client {
    display: flex;
    flex-direction: row;
    gap: 20px;
}
</style>
