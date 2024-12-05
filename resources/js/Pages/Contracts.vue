<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
// import { Inertia } from '@inertiajs/inertia';
// import { format } from 'date-fns';
import { formatDate, getYearDifference } from '@/helpers.js';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';

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

const isModalOpen = ref(false);
const currentModal = ref(null);

const handleDropdownSelect = (option, userId, type) => {
    switch (option.action) {
        case 'edit':
            openModal(type, userId, 'edit');
            break;
        case 'delete':
            if (confirm('Вы уверены, что хотите удалить пользователя?')) {
                router.delete(route('delete.user', { user: userId }));
            }
            break;
        default:
            console.error('Неизвестное действие:', option.action);
    }
};

const modalTitles = {
    add: 'Добавление договора',
    edit: 'Изменение договора'
};

const openModal = (type, userId, action = 'add') => {
    currentModal.value = { type, userId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
};

// const deleteContract = (contractId) => {
//     if (confirm('Вы точно хотите удалить договор?')) {
//         Inertia.delete(route('delete.contract', { contract: contractId }));
//     }
// };

// const nowDate = format(new Date(), 'yyyy-MM-dd');
// // Вычисление основной суммы
// const sum = computed(() => {
//     return props.contracts.reduce((total, contract) => total + contract.sum, 0);
// });

// const dividents = ref(null);

// Вычисление дивидендов
// const getDividends = (rate) => {
//     dividents.value = 0;
//     props.contracts.forEach((contract) => {
//         const dailyRate = (contract.sum * (contract.procent / 100)) / rate;
//         dividents.value += dailyRate;
//     });
// };
</script>

<template>

    <Head title="Contracts" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Договоры</h2>
                <!-- <ResponsiveNavLink v-if="props.role === 'admin' || props.role === 'manager'"
                    :href="route(`${props.role}.add.contract`)" class="add_contracts">
                    Добавить договор
                </ResponsiveNavLink> -->
                <button class="add_contracts link-btn" @click="openModal('add')"
                    v-if="props.role === 'admin' || props.role === 'manager'">
                    Добавить договор
                </button>
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
                        <div style="padding-left: 30px;">
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
                        <div class="card-item ellipsis">
                            <Dropdown :options="[
                                { label: 'Изменить', action: 'edit' },
                                { label: 'Удалить', action: 'delete' },
                            ]" @select="handleDropdownSelect($event, contract.id, 'contract')">
                                <template #trigger>
                                    <Ellipsis />
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal?.action]" @close="closeModal">
        <template #default>
            <div v-if="currentModal.type === 'add'">
                <form class="flex flex-column r-gap">
                    <div class="input flex flex-column">
                        <label for="client">Клиент</label>
                        <select id="client"></select>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="first_name">Номер договора*</label>
                            <input type="text" id="first_name" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="deadline">Срок договора*</label>
                            <select id="deadline"></select>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="bank">Ставка, %*</label>
                            <input type="text" id="bank" />
                        </div>
                        <div class="input flex checkbox">
                            <input type="checkbox" id="checkbox" />
                            <label for="checkbox">Вычислить дивиденды по истечению срока</label>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="date">Дата*</label>
                            <input type="date" id="date" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="deadline">Выплаты*</label>
                            <select id="deadline"></select>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="sum">Сумма*</label>
                            <input type="number" min="0" id="sum" />
                        </div>
                    </div>
                </form>
            </div>
            <div v-else>
                <form class="flex flex-column r-gap">
                    <div class="input flex flex-column">
                        <label for="client">Клиент</label>
                        <select id="client"></select>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="first_name">Номер договора*</label>
                            <input type="text" id="first_name" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="deadline">Срок договора*</label>
                            <select id="deadline"></select>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="bank">Ставка, %*</label>
                            <input type="text" id="bank" />
                        </div>
                        <div class="input flex checkbox">
                            <input type="checkbox" id="checkbox" />
                            <label for="checkbox">Вычислить дивиденды по истечению срока</label>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="date">Дата*</label>
                            <input type="date" id="date" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="deadline">Выплаты*</label>
                            <select id="deadline"></select>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="sum">Сумма*</label>
                            <input type="number" min="0" id="sum" />
                        </div>
                    </div>
                </form>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button @click="" v-if="currentModal.type === 'add'" class="btn-save">Создать</button>
                <button @click="" v-else class="btn-save">Сохранить</button>
            </div>
        </template>
    </BaseModal>
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
    grid-template-columns: 2.26fr 1.29fr 1.29fr 1.29fr 0.97fr 1.39fr 1.29fr 0.32fr;
    border-bottom: 1px solid #F3F5F6;
}

.contracts {
    padding: 16px 0;
    display: grid;
    grid-template-columns: 2.26fr 1.29fr 1.29fr 1.29fr 0.97fr 1.39fr 1.29fr 0.32fr;
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
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
}

.add_contracts {
    background: #4E9F7D;
    color: #fff;
    transition: 0.3s;
}

.add_contracts:hover {
    background: #428569;
}

.order {
    padding-left: 12px;
}

.btn-cancel,
.btn-save {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
}

.btn-save {
    background: #4e9f7d;
    color: #fff;
    transition: 0.3s;
}

.btn-save:hover {
    background: #428569;
}

.btn-cancel {
    margin-right: 10px;
    color: #242424;
    background: #f3f5f6;
    transition: 0.3s;
}

.btn-cancel:hover {
    background: #dfe4e7;
}

.input {
    width: 100%;
    row-gap: 8px;
}

.input label,
.warning {
    color: #969ba0;
}

.input input,
.input select {
    border: 1px solid #e8eaeb;
    height: 45px;
    border-radius: 12px;
    width: 100%;
    padding: 0 20px;
}

.checkbox {
    align-items: end;
}

.checkbox input {
    margin-bottom: 11px;
    margin-right: 12px;
    width: 24px;
    height: 24px;
}

.checkbox label {
    color: #242424;
}

.c_data {
    font-weight: 500;
}
</style>
