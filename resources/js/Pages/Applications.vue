<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { formatDate, getYearDifference } from '@/helpers.js';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';
import InputError from '@/Components/InputError.vue';
import { fetchData } from '@/helpers';

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    applications: {
        type: Array,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
    clients: {
        type: Array,
        required: true,
    },
});

const isModalOpen = ref(false);
const currentModal = ref(null);
const selectedOffTime = ref(null);
const selectedPartlyOption = ref(null);
const userContract = ref({});
const procent = ref('');
const sum = ref(null);
const dividends = ref(null);
const create_date = ref('');
const term = ref('');
const applicationData = ref({});
const error = ref('');
const userInfo = ref({});
const contractInfo = ref({});
const payments = ref('');
const partOfDividends = ref(null);
const count_of_applications = ref(null);
const number_of_payments = ref(null);
const x = ref(true);

const activeApplication = computed(() =>
    props.applications.filter((application) => application.status !== 'Отменена' && application.status !== 'Исполнена'),
);
const noactiveCApplication = computed(() =>
    props.applications.filter((application) => application.status === 'Отменена' || application.status === 'Исполнена'),
);
const modalTitles = ref({
    add: 'Новая заявка',
    information: '',
    edit: 'Название заявки',
});
const applicationStatuses = ref(['В обработке', 'Согласована', 'Исполнена', 'Отменена']);

// const calcDividends = (sum, procent, term) => {
//     return (sum * (procent / 100) * term).toFixed(2);
// };

const getInfo = async (url, applicationId) => {
    try {
        const data = await fetchData(url, { application: applicationId }); // Ожидаем завершения запроса
        applicationData.value = data.application;
        userInfo.value = applicationData.value.user ?? '';
        contractInfo.value = applicationData.value.contract ?? '';
        modalTitles.value.information = formatDate(applicationData.value.create_date) ?? '';
        selectedOffTime.value = applicationData.value.condition ?? '';
        selectedPartlyOption.value = applicationData.value.type_of_processing ?? '';
        formStatus.status = applicationData.value.status;
    } catch (err) {
        error.value = err; // Сохраняем ошибку
    } finally {
    }
};

const handleGetClient = (id) => {
    userContract.value = props.clients.find((client) => client.id === id);
};
const handleGetContract = (contract_id) => {
    sum.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).sum;
    procent.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).procent + '%';
    form.manager_id = userContract.value.user_contracts.find((contract) => contract.id === contract_id).manager_id;
    create_date.value = formatDate(
        userContract.value.user_contracts.find((contract) => contract.id === contract_id).create_date,
    );
    term.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).term;
    dividends.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).dividends;
    number_of_payments.value = userContract.value.user_contracts.find(
        (contract) => contract.id === contract_id,
    ).number_of_payments;
    count_of_applications.value = props.applications.reduce((total, application) => {
        return application.contract_number === contrNumb ? total + 1 : total;
    }, 0);
    number_of_payments.value - 1 > count_of_applications.value ? (x.value = true) : (x.value = false);
};

const offTime = () => {
    form.sum = null;
    form.condition = 'Раньше срока';
    form.type_of_processing = 'Основная сумма';
    form.sum = Number(sum.value - sum.value * 0.3);
};
const onTime = () => {
    form.sum = null;
    form.condition = 'В срок';
};
const takeEverythin = () => {
    form.sum = null;
    form.dividends = null;
    form.type_of_processing = 'Забрать дивиденды и сумму';
    form.sum = sum.value;
    form.dividends = dividends.value;
};
const takeDividends = () => {
    form.dividends = null;
    form.sum = null;
    form.type_of_processing = 'Забрать дивиденды целиком';
    form.dividends = dividends.value;
};

const takePartlyDividends = () => {
    form.dividends = null;
    form.type_of_processing = 'Забрать дивиденды частично';
};

// const handleDropdownSelect = (option, applicationId, type) => {
//     switch (option.action) {
//         case 'information':
//             openModal('information', applicationId, 'information');
//             break;
//         case 'edit':
//             openModal('edit', applicationId, 'edit');
//             break;
//         default:
//             console.error('Неизвестное действие:', option.action);
//     }
// };

const handleDropdownSelect = (option, applicationId, type, url) => {
    // console.log(option, applicationId, type, url);

    getInfo(option.url, applicationId);
    openModal(type, applicationId, type);
};

const openModal = (type, applicationId, action = 'add') => {
    currentModal.value = { type, applicationId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
    dividends.value = null;
    sum.value = null;
    form.user_id = '';
    term.value = '';
    create_date.value = '';
    procent.value = '';
    selectedOffTime.value = null;
    selectedPartlyOption.value = null;
    count_of_applications.value = null;
    number_of_payments.value = null;
    form.reset(
        'user_id',
        'contract_id',
        'manager_id',
        'condition',
        'status',
        'type_of_processing',
        'date_of_payments',
        'sum',
        'dividends',
    );
};

const form = useForm({
    create_date: new Date().toISOString().substr(0, 10),
    user_id: '',
    contract_id: '',
    manager_id: '',
    condition: '',
    status: 'В обработке',
    type_of_processing: '',
    date_of_payments: '',
    sum: null,
    dividends: null,
    available_balance: null,
});
const formStatus = useForm({
    status: '',
});
watch(
    () => form.dividends,
    (newValue) => {
        form.available_balance = dividends.value - newValue;
    },
);

const createAplication = () => {
    form.post(route('add.application'), {
        onSuccess: () => {
            closeModal(); // Закрыть модал при успешной отправке
        },
        onError: () => {
            console.error('Ошибка:', form.errors); // Лог ошибок
        },
    });
};
const changeStatus = () => {
    formStatus.patch(route('change.status.application', applicationData.value.id), {
        onSuccess: () => {
            closeModal(); // Закрыть модал при успешной отправке
        },
        onError: () => {
            console.error('Ошибка:', formStatus.errors); // Лог ошибок
        },
    });
};
</script>

<template>
    <Head title="Applications" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Заявки</h2>
                <ResponsiveNavLink
                    class="add_application"
                    v-if="role === 'admin' || role === 'manager'"
                    :href="route('add.application')"
                >
                    Новая заявка
                </ResponsiveNavLink>
                <button
                    class="add_application link-btn"
                    @click="openModal('add')"
                    v-if="role === 'admin' || role === 'manager'"
                >
                    Новая заявка
                </button>
            </div>
        </template>
        <template #main>
            <div class="main flex flex-column">
                <div class="card">
                    <header>
                        <h2 class="title-card">Активные заявки</h2>
                    </header>
                    <div class="application">
                        <ul class="thead-application align-center">
                            <li class="order">Дата заявки</li>
                            <li>Клиент</li>
                            <li>Договор</li>
                            <li>Условие</li>
                            <li>Статус</li>
                            <li>Вид списания</li>
                            <li>Дата выплаты</li>
                            <li>Сумма</li>
                            <li>Дивиденды</li>
                        </ul>
                        <div class="title" v-if="props.applications.length === 0">Заявок нет</div>
                        <div
                            v-else
                            class="applications align-center"
                            v-for="application in props.applications"
                            :key="application.id"
                        >
                            <div class="order">
                                <p>{{ formatDate(application.create_date) }}</p>
                            </div>
                            <div>
                                <p>{{ application.full_name }}</p>
                            </div>
                            <div>
                                <p>{{ application.contract_number }}</p>
                            </div>
                            <div>
                                <p>{{ application.condition }}</p>
                            </div>
                            <div>
                                <p>{{ application.status }}</p>
                            </div>
                            <div>
                                <p>{{ application.type_of_processing }}</p>
                            </div>
                            <div>
                                <p>{{ formatDate(application.date_of_payments) }}</p>
                            </div>
                            <div>
                                <p>{{ application.sum }}</p>
                            </div>
                            <div>
                                <p>{{ application.dividends }}</p>
                            </div>
                            <div>
                                <Dropdown
                                    v-if="role === 'admin'"
                                    :options="[
                                        {
                                            label: 'Подробная информация',
                                            action: 'information',
                                            url: 'show.application',
                                        },
                                        { label: 'Изменить статус', action: 'edit', url: 'change.status.application' },
                                    ]"
                                    class="applications_dropdown"
                                    @select="handleDropdownSelect($event, application.id, $event.action)"
                                >
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                                <Dropdown
                                    v-else
                                    :options="[
                                        {
                                            label: 'Подробная информация',
                                            action: 'information',
                                            url: 'show.application',
                                        },
                                    ]"
                                    class="applications_dropdown"
                                    @select="handleDropdownSelect($event, application.id, $event.action)"
                                >
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        <!-- {{ props.applications }} -->
                    </div>
                </div>
                <div class="card">
                    <header>
                        <h2 class="title-card">Завершенные заявки</h2>
                    </header>
                    <div class="application">
                        <div class="title">Завершённых заявок нет</div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal?.action]" @close="closeModal">
        <template #default>
            <div v-if="currentModal.type === 'add'">
                <form>
                    <div class="flex flex-column r-gap">
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="client">Клиент</label>
                                <select
                                    id="client"
                                    v-model="form.user_id"
                                    @change="handleGetClient(form.user_id)"
                                    required
                                >
                                    <option value="" disabled>Выберите клиента</option>
                                    <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                        {{ client.full_name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.user_id" />
                            </div>
                            <div class="input flex flex-column">
                                <label for="contract">Договор</label>
                                <select
                                    id="contract"
                                    v-model="form.contract_id"
                                    @change="handleGetContract(form.contract_id)"
                                >
                                    <option value="" disabled>Выберите номер договора</option>
                                    <option
                                        v-for="contract in userContract.user_contracts"
                                        :key="contract.id"
                                        :value="contract.id"
                                    >
                                        {{ contract.contract_number }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="flex" style="column-gap: 8px">
                            <div class="information_contract">
                                <label>Дата заключения</label>
                                <p>{{ create_date }}</p>
                            </div>
                            <div class="information_contract">
                                <label>Срок договора</label>
                                <p>{{ term === 1 ? '1 год' : term === 3 ? '3 года' : term + '' }}</p>
                            </div>
                            <div class="information_contract">
                                <label>Ставка</label>
                                <p>{{ procent }}</p>
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="contract_sum">
                                <label>Основная сумма</label>
                                <p>{{ sum ? sum + '₽' : '' }}</p>
                            </div>
                            <div class="contract_sum">
                                <label>Дивиденды</label>
                                <p>{{ dividends ? dividends + '₽' : '' }}</p>
                            </div>
                        </div>
                    </div>
                    <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Условия списания</p>
                    <div class="radio-buttons flex c-gap">
                        <div class="input flex">
                            <input
                                type="radio"
                                id="off_time"
                                name="off_time"
                                @click="offTime"
                                value="Раньше срока"
                                v-model="selectedOffTime"
                            />
                            <label for="off_time" class="button">Раньше срока</label>
                        </div>
                        <div class="input flex">
                            <input
                                type="radio"
                                id="on_time"
                                name="on_time"
                                @click="onTime"
                                value="В срок"
                                v-model="selectedOffTime"
                            />
                            <label for="on_time" class="button">В срок</label>
                        </div>
                    </div>
                    <div class="for_off_time" v-if="selectedOffTime === 'Раньше срока'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="write_downs">Сумма списания</label>
                                <input type="text" id="write_downs" v-model.trim="sum" disabled />
                            </div>
                            <div class="input flex flex-column">
                                <label for="payment_date">Дата планируемой выплаты</label>
                                <input type="date" id="payment_date" v-model="form.date_of_payments" />
                            </div>
                        </div>
                        <p class="warning" style="margin-top: 16px">Комиссия за вывод раньше срока, 30%</p>
                        <p class="warning" style="margin-top: 4px">{{ sum * 0.3 }}₽</p>
                    </div>
                    <div class="for_on_time" v-if="selectedOffTime === 'В срок'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Варианты списания</p>
                        <div class="radio-buttons flex flex-column r-gap">
                            <div class="flex c-gap">
                                <input
                                    type="radio"
                                    id="partly"
                                    name="partly"
                                    @click="takePartlyDividends"
                                    value="Забрать дивиденды частично"
                                    v-model="selectedPartlyOption"
                                />
                                <label for="partly" class="button">Забрать дивиденды частично</label>
                                <input
                                    type="radio"
                                    id="wholly"
                                    name="wholly"
                                    @click="takeDividends"
                                    value="Забрать дивиденды целиком"
                                    v-model="selectedPartlyOption"
                                />
                                <label for="wholly" class="button">Забрать дивиденды целиком</label>
                            </div>
                            <input
                                type="radio"
                                id="take_everything"
                                name="take_everything"
                                value="Забрать дивиденды и сумму"
                                v-model="selectedPartlyOption"
                                @click="takeEverythin"
                            />
                            <label for="take_everything" class="button">Забрать дивиденды и сумму</label>
                        </div>
                        <div class="for_partly" v-if="selectedPartlyOption === 'Забрать дивиденды частично'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="dividends_partly">Дивиденты</label>
                                    <input type="number" id="dividends_partly" v-model="form.dividends" />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_partly_date">Дата планируемой выплаты</label>
                                    <input type="date" id="dividends_partly_date" v-model="form.date_of_payments" />
                                </div>
                            </div>
                        </div>
                        <div class="for_wholly" v-if="selectedPartlyOption === 'Забрать дивиденды целиком'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="dividends_wholly">Дивиденты</label>
                                    <input type="text" id="dividends_wholly" v-model="form.dividends" disabled />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_wholly_date">Дата планируемой выплаты</label>
                                    <input type="date" id="dividends_wholly_date" v-model="form.date_of_payments" />
                                </div>
                            </div>
                        </div>
                        <div class="for_take_everything" v-if="selectedPartlyOption === 'Забрать дивиденды и сумму'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="sum_take_everything">Основная сумма</label>
                                    <input type="text" id="sum_take_everything" v-model="form.sum" disabled />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_take_everything">Дивиденты</label>
                                    <input
                                        type="text"
                                        id="dividends_take_everything"
                                        v-model="form.dividends"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="input flex flex-column" style="margin-top: 16px">
                                <label for="dividends_take_everything_date">Дата планируемой выплаты</label>
                                <input
                                    type="date"
                                    id="dividends_take_everything_date"
                                    v-model="form.date_of_payments"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div v-else-if="currentModal.type === 'information'">
                <div class="flex flex-column r-gap">
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="client">Клиент</label>
                            <input id="client" :value="userInfo.full_name" disabled />
                        </div>
                        <div class="input flex flex-column">
                            <label for="contract">Договор</label>
                            <input id="contract" :value="contractInfo.contract_number" disabled />
                        </div>
                    </div>
                    <div class="flex" style="column-gap: 8px">
                        <div class="information_contract">
                            <label>Дата заключения</label>
                            <p>{{ formatDate(contractInfo.create_date) }}</p>
                        </div>
                        <div class="information_contract">
                            <label>Срок договора</label>
                            <p>
                                {{
                                    contractInfo.term === 1
                                        ? '1 год'
                                        : contractInfo.term === 3
                                        ? '3 года'
                                        : contractInfo.term + ''
                                }}
                            </p>
                        </div>
                        <div class="information_contract">
                            <label>Ставка</label>
                            <p>{{ contractInfo.procent }}%</p>
                        </div>
                    </div>
                    <div class="flex c-gap">
                        <div class="contract_sum">
                            <label>Основная сумма</label>
                            <p>{{ contractInfo.sum }}₽</p>
                        </div>
                        <div class="contract_sum">
                            <label>Дивиденды</label>
                            <p>{{ contractInfo.dividends }}₽</p>
                        </div>
                    </div>
                </div>
                <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Условия списания</p>
                <div class="radio-buttons flex c-gap">
                    <div class="input flex">
                        <input
                            type="radio"
                            id="off_time"
                            name="off_time"
                            value="Раньше срока"
                            v-model="selectedOffTime"
                        />
                        <label for="off_time" class="button">Раньше срока</label>
                    </div>
                    <div class="input flex">
                        <input type="radio" id="on_time" name="on_time" value="В срок" v-model="selectedOffTime" />
                        <label for="on_time" class="button">В срок</label>
                    </div>
                </div>
                <div class="for_off_time" v-if="selectedOffTime === 'Раньше срока'">
                    <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                    <div class="flex c-gap">
                        <div class="input flex flex-column">
                            <label for="write_downs">Сумма списания</label>
                            <input type="text" id="write_downs" :value="contractInfo.sum" disabled />
                        </div>
                        <div class="input flex flex-column">
                            <label for="payment_date">Дата планируемой выплаты</label>
                            <input type="date" id="payment_date" :value="applicationData.date_of_payments" disabled />
                        </div>
                    </div>
                    <p class="warning" style="margin-top: 16px">Комиссия за вывод раньше срока, 30%</p>
                    <p class="warning" style="margin-top: 4px">{{ contractInfo.sum * 0.3 }}</p>
                </div>
                <div class="for_on_time" v-if="selectedOffTime === 'В срок'">
                    <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Варианты списания</p>
                    <div class="radio-buttons flex flex-column r-gap">
                        <div class="flex c-gap">
                            <input
                                type="radio"
                                id="partly"
                                name="partly"
                                value="Забрать дивиденды частично"
                                v-model="selectedPartlyOption"
                            />
                            <label for="partly" class="button">Забрать дивиденды частично</label>
                            <input
                                type="radio"
                                id="wholly"
                                name="wholly"
                                value="Забрать дивиденды целиком"
                                v-model="selectedPartlyOption"
                            />
                            <label for="wholly" class="button">Забрать дивиденды целиком</label>
                        </div>
                        <input
                            type="radio"
                            id="take_everything"
                            name="take_everything"
                            value="Забрать дивиденды и сумму"
                            v-model="selectedPartlyOption"
                        />
                        <label for="take_everything" class="button">Забрать дивиденды и сумму</label>
                    </div>
                    <div class="for_partly" v-if="selectedPartlyOption === 'Забрать дивиденды частично'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="dividends_partly">Дивиденты</label>
                                <input type="text" id="dividends_partly" :value="applicationData.dividends" disabled />
                            </div>
                            <div class="input flex flex-column">
                                <label for="dividends_partly_date">Дата планируемой выплаты</label>
                                <input
                                    type="date"
                                    id="dividends_partly_date"
                                    :value="applicationData.date_of_payments"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                    <div class="for_wholly" v-if="selectedPartlyOption === 'Забрать дивиденды целиком'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="dividends_wholly">Дивиденты</label>
                                <input type="text" id="dividends_wholly" :value="applicationData.dividends" disabled />
                            </div>
                            <div class="input flex flex-column">
                                <label for="dividends_wholly_date">Дата планируемой выплаты</label>
                                <input
                                    type="date"
                                    id="dividends_wholly_date"
                                    :value="applicationData.date_of_payments"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                    <div class="for_take_everything" v-if="selectedPartlyOption === 'Забрать дивиденды и сумму'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px">Вывод средств</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="sum_take_everything">Основная сумма</label>
                                <input type="text" id="sum_take_everything" :value="applicationData.sum" disabled />
                            </div>
                            <div class="input flex flex-column">
                                <label for="dividends_take_everything">Дивиденты</label>
                                <input
                                    type="text"
                                    id="dividends_take_everything"
                                    :value="applicationData.dividends"
                                    disabled
                                />
                            </div>
                        </div>
                        <div class="input flex flex-column" style="margin-top: 16px">
                            <label for="dividends_take_everything_date">Дата планируемой выплаты</label>
                            <input
                                type="date"
                                id="dividends_take_everything_date"
                                :value="applicationData.date_of_payments"
                                disabled
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="currentModal.type === 'edit'">
                <form>
                    <div class="input flex flex-column">
                        <label for="status">Статус</label>
                        <select id="status" v-model="formStatus.status">
                            <option v-for="(status, index) in applicationStatuses" :key="index" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                </form>
            </div>
        </template>
        <template #footer>
            <div v-if="currentModal.type !== 'information'" class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button @click="createAplication" v-if="currentModal.type === 'add'" class="btn-save">Создать</button>
                <button @click="changeStatus" v-else class="btn-save">Сохранить</button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
.application {
    padding: 20px 32px 32px 32px;
    width: 1606px;
}

.main {
    row-gap: 32px;
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
    padding-bottom: 30px;
    background: #fff;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
    overflow-x: auto;
    scrollbar-width: none;
    scrollbar-color: transparent transparent;
}

.card::-webkit-scrollbar {
    width: 0px;
}

.card::-webkit-scrollbar-thumb {
    background: transparent;
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

.thead-application {
    height: 55px;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px;
    border-bottom: 1px solid #f3f5f6;
}

.applications {
    padding: 16px 0;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px;
    border-bottom: 1px solid #f3f5f6;
}

.thead-application li {
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

.add_application {
    background: #4e9f7d;
    color: #fff;
    transition: 0.3s;
}

.add_application:hover {
    background: #428569;
}

.order {
    padding-left: 12px;
}

:deep(.applications_dropdown .dropdown-menu) {
    width: 250px;
}

:deep(.applications_dropdown .dropdown-item:last-child) {
    color: #a7adb2;
    border-top: none;
}

:deep(.applications_dropdown .dropdown-item:last-child p) {
    margin-top: unset;
}

:deep(.applications_dropdown .dropdown-item:nth-child(2) p) {
    margin-bottom: unset;
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

:deep(.modal-content) {
    width: 500px;
}

.input {
    width: 100%;
    row-gap: 8px;
}

.input label,
.warning,
.information_contract label,
.information_contract p {
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

.c_data {
    font-weight: 500;
}

.information_contract {
    background: #f3f5f6;
    padding: 16px 26px;
    border-radius: 24px;
}

.contract_sum {
    width: 100%;
    background: #f3f5f6;
    border-radius: 24px;
    padding: 16px 20px;
}

.radio-buttons input[type='radio'] {
    display: none;
}

.radio-buttons .button {
    width: 100%;
    display: inline-block;
    padding: 16px 20px;
    border-radius: 24px;
    background: #f3f5f6;
    color: #969ba0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.radio-buttons .button:hover {
    background-color: #4e9f7d1a;
    color: #4e9f7d;
}

.radio-buttons input[type='radio']:checked + .button {
    background: #4e9f7d1a;
    color: #4e9f7d;
}

#write_downs {
    background: #f3f5f6;
    color: #969ba0;
}

input[disabled] {
    background-color: #f3f5f6;
}
</style>
