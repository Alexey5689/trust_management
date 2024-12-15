<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate, getYearDifference } from '@/helpers.js';
import Icon_contract from '@/Components/Icon/Contract.vue';

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
});
</script>

<template>

    <Head title="ContractsClient" />
    <AuthenticatedLayout :userRole="role" :notifications="props.status">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Договоры</h2>
            </div>
        </template>
        <template #main>



            <!-- {{ contracts }} -->
            <div class="contracts_client flex">
                <div class="contract_item flex flex-column" v-for="contract in props.contracts" :key="contract.id">
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
                        <p>3 года</p>
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
    column-gap: 24px;
    overflow-x: scroll;
    scrollbar-width: none;
    scrollbar-color: transparent transparent;
}

.contracts_client::-webkit-scrollbar {
    width: 0px;
}

.contracts_client::-webkit-scrollbar-thumb {
    background: transparent;
}

.contract_item {
    flex: 0 0 auto;
    height: 265px;
    width: 450px;
    background: #4E9F7D;
    border-radius: 24px;
    padding: 24px;
    row-gap: 8px;
}

.icon_contract {
    background: #fff;
    width: 48px;
    height: 48px;
    border-radius: 100%;
}

.icon_contract svg {
    fill: #4E9F7D
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
    color: #F2F2F2;
    width: 193px;
}

.contract_item {
    color: #fff;
}
</style>
