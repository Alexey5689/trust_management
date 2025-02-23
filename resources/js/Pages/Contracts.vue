<script setup>
import { ref, watch, computed, inject } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDate, getYearDifference, calculateDeadlineDate } from '@/helpers.js';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';
import { fetchData, filterNegativeNumbers } from '@/helpers';
import InputError from '@/Components/InputError.vue';
import Loader from '@/Components/Loader.vue';

const route = inject('route');

const props = defineProps({
    contracts: {
        type: Array,
        required: true,
    },
    clients: {
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
    user: {
        type: Array,
        required: true,
    },
    notifications: {
        type: Array,
        required: false,
    },
});

const isModalOpen = ref(false);
const currentModal = ref(null);
const error = ref(null);
const contractData = ref({});
const selectedDuration = ref('');
const loading = ref(false);
const activeClient = computed(() => props.clients.filter((client) => client.active));
const activeContract = computed(() =>
    props.contracts
        .filter((contract) => contract.contract_status === true)
        .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at)),
);
const noActiveContract = computed(() => props.contracts.filter((contract) => contract.contract_status === false));

const getInfo = async (url, contractId) => {
    loading.value = true;
    try {
        const data = await fetchData(url, { contract: contractId }); // Ожидаем завершения запроса
        contractData.value = data.contract;
    } catch (err) {
        error.value = err; // Сохраняем ошибку
    } finally {
        loading.value = false;
    }
};

const handleDropdownSelect = (option, contractId, type) => {
    switch (option.action) {
        case 'edit':
            openModal(type, contractId, 'edit', option.url);
            break;
        case 'delete':
            if (confirm('Вы уверены, что хотите удалить договор?')) {
                loading.value = true;
                form.delete(route('delete.contract', { contract: contractId }), {
                    onSuccess: () => {},
                    onError: (error) => {
                        console.error(error);
                    },
                    onFinish: () => {
                        loading.value = false;
                    },
                });
            }
            break;
        default:
            console.error('Неизвестное действие:', option.action);
    }
};

const form = useForm({
    user_id: '',
    contract_number: null,
    sum: null,
    deadline: '', // Срок договора
    procent: null, // Процентная ставка // Для чекбокса
    create_date: new Date().toISOString().substr(0, 10), // Дата заключения
    contract_status: true,
    payments: '', // Выплаты
    agree_with_terms: false,
});

watch(
    contractData,
    (newData) => {
        form.user_id = newData.user_id;
        form.contract_number = newData.contract_number;
        form.sum = newData.sum;
        form.deadline = newData.deadline;
        form.procent = newData.procent;
        form.payments = newData.payments;
        form.agree_with_terms = newData.agree_with_terms;
        form.create_date = newData.create_date;
    },
    { immediate: true },
);

const modalTitles = {
    add: 'Добавление договора',
    edit: 'Изменение договора',
};

const openModal = (type, contractId, action = 'add', url) => {
    if (action === 'edit') getInfo(url, contractId);
    currentModal.value = { type, contractId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
    selectedDuration.value = '';
    form.reset(
        'user_id',
        'contract_number',
        'sum',
        'deadline',
        'procent',
        'payments',
        'agree_with_terms',
        'create_date',
    );
    selectedDuration.value = '';
};

const handleDeadlineChange = (event) => {
    const selectedDuration = event.target.value;
    const duration =
        {
            '1 год': 1,
            '3 года': 3,
        }[selectedDuration] || 1;

    form.deadline = calculateDeadlineDate(duration, form.create_date ?? new Date().toISOString().substr(0, 10));
};

const createContract = () => {
    loading.value = true;
    form.post(route(`${props.role}.add.contract`), {
        onSuccess: () => {
            closeModal(); // Закрыть модал при успешной отправке
            loading.value = false;
        },
        onError: () => {
            console.error('Ошибка:', form.errors); // Лог ошибок
            loading.value = false;
        },
    });
};
const updateContract = () => {
    loading.value = true;
    form.patch(route('admin.edit.contract', { contract: currentModal.value.contractId }), {
        onSuccess: () => {
            closeModal(); // Закрыть модал при успешной отправке
            loading.value = false;
        },
        onError: () => {
            console.error('Ошибка:', form.errors); // Лог ошибок
            loading.value = false;
        },
    });
};
const handleCheckboxChange = () => {
    form.payments = 'По истечению срока';
    form.procent = 0;
};

const handleDateChange = (event) => {
    const duration =
        {
            '1 год': 1,
            '3 года': 3,
        }[selectedDuration.value] || 1;
    const create_date = event.target.value;
    form.deadline = calculateDeadlineDate(duration, create_date);
};
</script>

<template>
    <Head title="Договоры" />
    <AuthenticatedLayout
        :userInfo="props.user"
        :userRole="role"
        :toast="props.status"
        :notifications="props.notifications"
    >
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Договоры</h2>
                <button
                    class="add_contracts link-btn"
                    @click="openModal('add')"
                    v-if="props.role === 'admin' || props.role === 'manager'"
                >
                    Добавить договор
                </button>
            </div>
        </template>
        <template #main>
            <div class="main flex flex-column">
                <div class="card">
                    <header>
                        <h2 class="title-card">Договоры</h2>
                    </header>
                    <div class="client">
                        <ul class="thead-contracts align-center" v-if="props.contracts.length > 0">
                            <li class="order">Клиент</li>
                            <li>Договор</li>
                            <li>Дата</li>
                            <li>Ставка %</li>
                            <li>Срок</li>
                            <li>Выплаты</li>
                            <li>Сумма</li>
                        </ul>
                        <div class="title" v-if="props.contracts.length === 0">Договоров нет</div>
                        <div
                            v-else
                            class="contracts align-center"
                            v-for="contract in activeContract"
                            :key="contract.id"
                        >
                            <div class="order" v-if="props.role === 'admin' || props.role === 'manager'">
                                <p>{{ contract.user.full_name }}</p>
                            </div>
                            <div>
                                <p>{{ contract.contract_number }}</p>
                            </div>
                            <div>
                                <p>{{ formatDate(contract.create_date) }}</p>
                            </div>
                            <div style="padding-left: 30px">
                                <p>{{ contract.procent === 0 ? '80%/20%' : contract.procent + '%' }}</p>
                            </div>
                            <div>
                                <p>
                                    {{ contract.term === 1 ? '1 год' : contract.term + ' года' }}
                                </p>
                            </div>
                            <div v-if="(role === 'admin' || role === 'manager') && contract.payments">
                                <p>{{ contract.payments }}</p>
                            </div>
                            <div>
                                <p>{{ contract.sum + ' ₽' }}</p>
                            </div>
                            <div v-if="props.role === 'admin'" class="card-item ellipsis">
                                <Dropdown
                                    :options="[
                                        { label: 'Изменить', action: 'edit', url: 'admin.edit.contract' },
                                        { label: 'Удалить', action: 'delete' },
                                    ]"
                                    @select="handleDropdownSelect($event, contract.id, 'contract')"
                                >
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <header>
                        <h2 class="title-card">Закрытые договоры</h2>
                    </header>
                    <div class="client">
                        <ul class="thead-contracts align-center" v-if="noActiveContract.length > 0">
                            <li class="order">Клиент</li>
                            <li>Договор</li>
                            <li>Дата</li>
                            <li>Ставка %</li>
                            <li>Срок</li>
                            <li>Выплаты</li>
                            <li>Сумма</li>
                        </ul>
                        <div class="title" v-if="noActiveContract.length === 0">Закрытых договоров нет</div>
                        <div
                            v-else
                            class="contracts align-center executed_undo"
                            v-for="contract in noActiveContract"
                            :key="contract.id"
                        >
                            <div class="order" v-if="props.role === 'admin' || props.role === 'manager'">
                                <p>{{ contract.user.full_name }}</p>
                            </div>
                            <div>
                                <p>{{ contract.contract_number }}</p>
                            </div>
                            <div>
                                <p>{{ formatDate(contract.create_date) }}</p>
                            </div>
                            <div style="padding-left: 30px">
                                <p>{{ contract.procent }}</p>
                            </div>
                            <div>
                                <p>
                                    {{ contract.term === 1 ? '1 год' : contract.term + ' года' }}
                                </p>
                            </div>
                            <div v-if="(role === 'admin' || role === 'manager') && contract.payments">
                                <p>{{ contract.payments }}</p>
                            </div>
                            <div>
                                <p>{{ contract.sum }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>

    <Loader v-if="loading" />

    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal?.action]" @close="closeModal">
        <template #default>
            <div v-if="currentModal.type === 'add'">
                <form class="flex flex-column r-gap">
                    <div class="input flex flex-column">
                        <label for="client">Клиент</label>
                        <select id="client" v-model="form.user_id">
                            <option v-for="client in activeClient" :key="client.id" :value="client.id">
                                {{ client.full_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.user_id" />
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="first_name">Номер договора*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="first_name" v-model.trim="form.contract_number" />
                            <InputError :message="form.errors.contract_number" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="date">Дата*</label>
                            <input type="date" id="date" v-model.trim="form.create_date" @input="handleDateChange" />

                            <InputError :message="form.errors.create_date" />
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div v-if="!form.agree_with_terms" class="input flex flex-column">
                            <label for="bank">Ставка, %*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="bank" v-model.trim="form.procent" />
                            <InputError :message="form.errors.procent" />
                        </div>
                        <div class="input flex align-center checkbox">
                            <input
                                type="checkbox"
                                @change="handleCheckboxChange"
                                id="checkbox"
                                v-model="form.agree_with_terms"
                            />
                            <label for="checkbox">Вычислить дивиденды по истечению срока</label>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="deadline">Срок договора*</label>
                            <select id="deadline" v-model="selectedDuration" @change="handleDeadlineChange">
                                <option value="" disabled></option>
                                <option value="1 год">1 год</option>
                                <option value="3 года">3 года</option>
                            </select>
                            <InputError :message="form.errors.deadline" />
                        </div>
                        <div v-if="!form.agree_with_terms" class="input flex flex-column">
                            <label for="deadline">Выплаты*</label>
                            <select id="deadline" v-model="form.payments">
                                <option disabled></option>
                                <option value="Ежеквартально">Ежеквартально</option>
                                <option value="Ежегодно">Ежегодно</option>
                            </select>
                            <InputError :message="form.errors.payments" />
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="sum">Сумма*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="sum" v-model.trim="form.sum" />
                            <InputError :message="form.errors.sum" />
                        </div>
                    </div>
                </form>
            </div>
            <div v-else>
                <form class="flex flex-column r-gap">
                    <div class="input flex flex-column">
                        <label for="client">Клиент</label>
                        <select id="client" v-model="form.user_id">
                            <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                {{ client.full_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.user_id" />
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="first_name">Номер договора*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="first_name" v-model.trim="form.contract_number" />
                            <InputError :message="form.errors.contract_number" />
                        </div>
                        <div class="input flex flex-column">
                            <label for="date">Дата*</label>
                            <input type="date" id="date" v-model.trim="form.create_date" @input="handleDateChange" />
                            <InputError :message="form.errors.create_date" />
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div v-if="!form.agree_with_terms" class="input flex flex-column">
                            <label for="bank">Ставка, %*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="bank" v-model.trim="form.procent" />
                            <InputError :message="form.errors.procent" />
                        </div>
                        <div class="input flex align-center checkbox">
                            <input
                                type="checkbox"
                                id="checkbox"
                                @change="handleCheckboxChange"
                                v-model="form.agree_with_terms"
                            />
                            <label for="checkbox">Вычислить дивиденды по истечению срока</label>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="deadline">Срок договора*</label>
                            <select id="deadline" v-model="selectedDuration" @change="handleDeadlineChange">
                                <option value="" disabled>
                                    {{ getYearDifference(form.create_date, form.deadline) === 1 ? '1 год' : '3 года' }}
                                </option>
                                <option value="1 год">1 год</option>
                                <option value="3 года">3 года</option>
                            </select>
                            <InputError :message="form.errors.deadline" />
                        </div>
                        <div v-if="!form.agree_with_terms" class="input flex flex-column">
                            <label for="deadline">Выплаты*</label>
                            <select id="deadline" v-model="form.payments">
                                <option disabled></option>
                                <option value="Ежеквартально">Ежеквартально</option>
                                <option value="Ежегодно">Ежегодно</option>
                            </select>
                            <InputError :message="form.errors.payments" />
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="sum">Сумма*</label>
                            <input type="number" @input="filterNegativeNumbers" min="1" id="sum" v-model.trim="form.sum" />
                            <InputError :message="form.errors.sum" />
                        </div>
                    </div>
                </form>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button @click="createContract" v-if="currentModal.type === 'add'" class="btn-save">Создать</button>
                <button @click="updateContract" v-else class="btn-save">Сохранить</button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
.main {
    row-gap: 32px;
}
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
    border-bottom: 1px solid #f3f5f6;
    padding: 24px 32px 20px 32px;
}

.thead-contracts {
    height: 55px;
    display: grid;
    grid-template-columns: 2.26fr 1.29fr 1.29fr 1.29fr 0.97fr 1.39fr 1.29fr 0.32fr;
    border-bottom: 1px solid #f3f5f6;
}

.contracts {
    padding: 16px 0;
    display: grid;
    grid-template-columns: 2.26fr 1.29fr 1.29fr 1.29fr 0.97fr 1.39fr 1.29fr 0.32fr;
    border-bottom: 1px solid #f3f5f6;
}

.thead-contracts li {
    font-size: 16px;
    font-weight: 600;
    line-height: 23.2px;
    letter-spacing: 0.01em;
    color: #969ba0;
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
    background: #4e9f7d;
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
    height: 42px;
    margin-top: auto;
    column-gap: 12px;
}

.checkbox input {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.checkbox label {
    color: #242424;
}

.executed_undo p {
    color: #969ba0;
}
</style>
