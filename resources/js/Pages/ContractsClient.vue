<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate, formatDateClientContract, formatDateClientContractRus } from '@/helpers.js';
import Icon_contract from '@/Components/Icon/Contract.vue';
import Icon_schedule from '@/Components/Icon/Schedule.vue';

const props = defineProps({
    contracts: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
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

const activeTab = ref('months');
const yearAccruals = ref([]);
const monthAccruals = ref([]);
const weekAccruals = ref([]);
const dayAccruals = ref([]);

// Храним id выбранного договора
const openContractId = ref(null);

// При клике устанавливаем (или сбрасываем) id выбранного договора
function handleContractClick(contractId) {
    openContractId.value = props.contracts.find((contract) => contract.id === contractId);
    dayAccruals.value = openContractId.value.dayDividends;
    weekAccruals.value = openContractId.value.weekDividends;
    monthAccruals.value = openContractId.value.monthDividends;
    yearAccruals.value = openContractId.value.anualDividends;
}

// При монтировании (или сразу в setup) выберем первый договор, если список не пуст
onMounted(() => {
    if (props.contracts.length > 0) {
        openContractId.value = props.contracts[0];
        dayAccruals.value = openContractId.value.dayDividends;
        weekAccruals.value = openContractId.value.weekDividends;
        monthAccruals.value = openContractId.value.monthDividends;
        yearAccruals.value = openContractId.value.anualDividends;
    }
});
</script>

<template>
    <Head title="ContractsClient" />
    <AuthenticatedLayout
        :userInfo="props.user"
        :userRole="role"
        :toast="props.status"
        :notifications="props.notifications"
    >
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Договоры</h2>
            </div>
        </template>
        <template #main>
            <!-- Список договоров -->
            <div class="contracts_client flex">
                <div
                    class="contract_item flex flex-column"
                    v-for="contract in props.contracts"
                    :key="contract.id"
                    @click="handleContractClick(contract.id)"
                    :class="{ active: openContractId && openContractId.id === contract.id }"
                >
                    <div class="icon_contract flex justify-center align-center">
                        <Icon_contract />
                    </div>
                    <div>
                        <h2>{{ contract.contract_number }}</h2>
                    </div>
                    <div class="flex">
                        <span>Дата договора</span>
                        <p>{{ formatDate(contract.create_date) }}</p>
                    </div>
                    <div class="flex">
                        <span>Срок договора</span>
                        <p>{{ contract.term === 1 ? '1 год' : contract.term + ' года' }}</p>
                    </div>
                    <div class="flex">
                        <span>% ставка по договору</span>
                        <p>{{ contract.procent }}</p>
                    </div>
                    <div class="flex">
                        <span>Сумма по договору</span>
                        <p>{{ contract.sum }} ₽</p>
                    </div>
                </div>
            </div>

            <!-- Единый блок Графика: показываем, если есть выбранный договор -->
            <div class="accruals card" v-if="openContractId">
                <div class="accruals_title flex justify-between align-center">
                    <h3>График начислений для договора #{{ openContractId.contract_number }}</h3>
                    <ul class="accruals_tab flex justify-between">
                        <li
                            class="flex align-center years"
                            :class="{ active: activeTab === 'years' }"
                            @click.stop="activeTab = 'years'"
                        >
                            По годам
                        </li>
                        <li
                            class="flex align-center months"
                            :class="{ active: activeTab === 'months' }"
                            @click.stop="activeTab = 'months'"
                        >
                            По месяцам
                        </li>
                        <li
                            class="flex align-center weeks"
                            :class="{ active: activeTab === 'weeks' }"
                            @click.stop="activeTab = 'weeks'"
                        >
                            По неделям
                        </li>
                        <li
                            class="flex align-center days"
                            :class="{ active: activeTab === 'days' }"
                            @click.stop="activeTab = 'days'"
                        >
                            По дням
                        </li>
                    </ul>
                </div>

                <!-- Таб "По годам" -->
                <div class="accruals_schedule years" v-show="activeTab === 'years'">
                    <div
                        v-for="accrual in yearAccruals"
                        :key="accrual.id"
                        class="schedule_item flex justify-between align-center"
                    >
                        <div>
                            <Icon_schedule />
                        </div>
                        <div style="width: 150px">
                            <p>{{ formatDateClientContract(accrual.date) }}</p>
                            <span>{{ formatDateClientContractRus(accrual.date) }}</span>
                        </div>
                        <div>
                            <p style="font-weight: 500">+{{ accrual.dividend }}</p>
                        </div>
                    </div>
                </div>

                <!-- Таб "По месяцам" -->
                <div class="accruals_schedule months" v-show="activeTab === 'months'">
                    <div
                        v-for="accrual in monthAccruals"
                        :key="accrual.id"
                        class="schedule_item flex justify-between align-center"
                    >
                        <div>
                            <Icon_schedule />
                        </div>
                        <div style="width: 150px">
                            <p>{{ formatDateClientContract(accrual.date) }}</p>
                            <span>{{ formatDateClientContractRus(accrual.date) }}</span>
                        </div>
                        <div>
                            <p style="font-weight: 500">+{{ accrual.dividend }}</p>
                        </div>
                    </div>
                </div>

                <!-- Таб "По неделям" -->
                <div class="accruals_schedule weeks" v-show="activeTab === 'weeks'">
                    <div
                        v-for="accrual in weekAccruals"
                        :key="accrual.id"
                        class="schedule_item flex justify-between align-center"
                    >
                        <div>
                            <Icon_schedule />
                        </div>
                        <div style="width: 150px">
                            <p>{{ formatDateClientContract(accrual.date) }}</p>
                            <span>{{ formatDateClientContractRus(accrual.date) }}</span>
                        </div>
                        <div>
                            <p style="font-weight: 500">+{{ accrual.dividend }}</p>
                        </div>
                    </div>
                </div>

                <!-- Таб "По дням" -->
                <div class="accruals_schedule days" v-show="activeTab === 'days'">
                    <div
                        v-for="accrual in dayAccruals"
                        :key="accrual.id"
                        class="schedule_item flex justify-between align-center"
                    >
                        <div>
                            <Icon_schedule />
                        </div>
                        <div style="width: 150px">
                            <p>{{ formatDateClientContract(accrual.date) }}</p>
                            <span>{{ formatDateClientContractRus(accrual.date) }}</span>
                        </div>
                        <div>
                            <p style="font-weight: 500">+{{ accrual.dividend }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /График -->
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

.contracts_client {
    flex-wrap: nowrap;
    column-gap: 16px;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: #bbb #f0f0f0;
}

.contracts_client::-webkit-scrollbar {
    width: 5px;
}

.contracts_client::-webkit-scrollbar-thumb {
    background-color: #bbb;
}

.contract_item {
    flex: 0 0 auto;
    height: 265px;
    width: 450px;
    background: #bcc4cc;
    border-radius: 24px;
    padding: 24px;
    row-gap: 8px;
    cursor: pointer;
    transition: 0.3s;
}

.contract_item:hover,
.contract_item.active {
    background: #4e9f7d;
}

.icon_contract {
    background: #fff;
    width: 48px;
    height: 48px;
    border-radius: 100%;
}

.icon_contract svg {
    fill: #bcc4cc;
    transition: 0.3s;
}

.contract_item:hover .icon_contract svg,
.active .icon_contract svg {
    fill: #4e9f7d;
}

.contract_item h2 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    color: #fff;
    margin-top: 8px;
    margin-bottom: 8px;
}

.contract_item span {
    color: #f2f2f2;
    width: 193px;
}

.contract_item {
    color: #fff;
}

.card {
    margin-top: 16px;
    padding: 24px 32px;
    background: #fff;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.accruals_title {
    height: 45px;
}

.accruals_title h3 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
}

.accruals_tab {
    border: 1px solid #f3f5f6;
    border-radius: 100px;
    height: 100%;
    column-gap: 4px;
}

.accruals_tab li {
    height: 100%;
    padding: 0 20px;
    font-weight: 500;
    color: #969ba0;
    transition: 0.3s;
    cursor: pointer;
}

.accruals_tab li:hover,
.accruals_tab .active {
    background: #4e9f7d;
    color: #fff;
    border-radius: 100px;
}

.accruals_schedule {
    margin-top: 32px;
}

.schedule_item {
    height: 76px;
    padding: 0 20px;
}

.schedule_item span {
    color: #969ba0;
}
</style>
