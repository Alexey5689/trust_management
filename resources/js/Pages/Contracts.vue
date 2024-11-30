<script setup>
import { computed, ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Inertia } from '@inertiajs/inertia';
import { format } from 'date-fns';
import { formatDate, getYearDifference } from '@/helpers.js';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({
    contracts: {
        type: Array,
        required: true,
    },
    role: {
        type: Object,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
});

const deleteContract = (contractId) => {
    if (confirm('Вы точно хотите удалить договор?')) {
        Inertia.delete(route('delete.contract', { contract: contractId }));
    }
};

const nowDate = format(new Date(), 'yyyy-MM-dd');
// Вычисление основной суммы
const sum = computed(() => {
    return props.contracts.reduce((total, contract) => total + contract.sum, 0);
});

const dividents = ref(null);

// Вычисление дивидендов
const getDividends = (rate) => {
    dividents.value = 0;
    props.contracts.forEach((contract) => {
        const dailyRate = (contract.sum * (contract.procent / 100)) / rate;
        dividents.value += dailyRate;
    });
};
</script>
<template>

    <Head title="Contracts" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Договоры</h2>
                <ResponsiveNavLink v-if="props.role === 'admin' || props.role === 'manager'"
                    :href="route(`${props.role}.add.contract`)" class="add_contracts">
                    Добавить договор
                </ResponsiveNavLink>
            </div>
        </template>
        <!-- {{ props.contracts }} -->
        <template #main>
            <div class="card">
                <header>
                    <h2 class="title-card">Договоры</h2>
                </header>
                <div class="client">
                    <ul class="thead-contracts align-center">
                        <li class="order">Клиент</li>
                        <li>Договор</li>
                        <li>Дата</li>
                        <li>Ставка %</li>
                        <li>Срок</li>
                        <li>Выплаты</li>
                        <li>Сумма</li>
                    </ul>
                    <div class="title" v-if="props.contracts.length == 0">Договоров нет</div>
                    <div v-else class="contracts align-center" v-for="contract in props.contracts" :key="contract.id">
                        <div class="order" v-if="props.role === 'admin' || props.role === 'manager'">
                            <p>{{ contract.user.full_name }}</p>
                        </div>
                        <div>
                            <p>{{ contract.contract_number }}</p>
                        </div>
                        <div>
                            <p>{{ formatDate(contract.create_date) }}</p>
                        </div>
                        <div>
                            <p>{{ contract.procent }}</p>
                        </div>
                        <div>
                            <p>
                                {{
                                    getYearDifference(contract.create_date, contract.deadline) === 1
                                        ? getYearDifference(contract.create_date, contract.deadline) + ' год'
                                        : getYearDifference(contract.create_date, contract.deadline) + ' года'
                                }}
                            </p>
                        </div>
                        <div v-if="(role === 'admin' || role === 'manager') && contract.payments">
                            <p>{{ contract.payments }}</p>
                        </div>
                        <div>
                            <p>{{ contract.sum }}</p>
                        </div>


                        <!-- <Dropdown v-if="role === 'admin'" align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
<template #content>
                                        <DropdownLink :href="route(`edit.contract`, {
                                            contract: contract.id,
                                        })
                                            " as="button">
                                            Изменить
                                        </DropdownLink>
                                        <DropdownLink @click="deleteContract(contract.id)" as="button">
                                            Удалить
                                        </DropdownLink>
                                    </template>
</Dropdown> -->


                    </div>
                </div>
            </div>

            <!-- <div v-if="role === 'client'" class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">График начислений</h2>
                            </header>
                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton @click="getDividends(365)" class="mt-4"> День </PrimaryButton>
                                <PrimaryButton @click="getDividends(12)" class="mt-4"> Месяц </PrimaryButton>
                                <PrimaryButton @click="getDividends(1)" class="mt-4"> Год </PrimaryButton>
                                <PrimaryButton @click="getDividends(52)" class="mt-4"> Неделя </PrimaryButton>
                            </div>
                            <div class="flex gap-9">
                                {{ sum }}
                                {{ Math.round(dividents) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->


        </template>
    </AuthenticatedLayout>
</template>
<style scoped>
.client {
    padding: 20px 32px 32px 32px;
}

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
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    border-bottom: 1px solid #F3F5F6;
    padding: 24px 32px 20px 32px;
}

.thead-contracts {
    height: 55px;
    display: grid;
    grid-template-columns: 1.1fr 0.5fr 0.7fr 0.5fr 0.4fr 0.8fr 0.7fr;
    border-bottom: 1px solid #F3F5F6;
}

.contracts {
    padding: 16px 0;
    display: grid;
    grid-template-columns: 1.1fr 0.5fr 0.7fr 0.5fr 0.4fr 0.8fr 0.7fr;
    border-bottom: 1px solid #F3F5F6;
}

.thead-contracts li {
    font-size: 16px;
    font-weight: 600;
    line-height: 23.2px;
    letter-spacing: 0.01em;
    color: #969BA0;
}

.link-btn {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
}

.add_contracts {
    background: #4E9F7D;
    color: #fff;
    margin-right: 16px;
    transition: 0.3s;
}

.add_contracts:hover {
    background: #428569;
}

.order {
    padding-left: 12px;
}
</style>
