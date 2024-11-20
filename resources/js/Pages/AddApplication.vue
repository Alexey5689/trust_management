<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format } from 'date-fns';
import { ru } from 'date-fns/locale';
import { ref, computed } from 'vue';

const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    clients: {
        type: Array,
        required: true,
    },
});

const procent = ref('');
const userContract = ref({});
const sum = ref(null);
const dividends = ref(null);
const create_date = ref('');
const term = ref('');
const condition = ref('');
const processing = ref('');

const conditionsType = ref([
    { type: 'Раньше срока', value: false },
    { type: 'В срок', value: true },
]);
const typeOfProcessing = ref([
    { type: 'Забрать дивиденды частично', value: 0 },
    { type: 'Забрать дивиденды целиком', value: 1 },
    { type: 'Забрать дивиденды и сумму', value: 2 },
]);

const conditionRadio = (tmp) => {
    if (tmp === false) {
        form.type_of_processing = 'Основная сумма';
        form.sum = sum.value - sum.value * 0.3;
        condition.value = false;
    } else {
        condition.value = true;
        form.sum = dividends.value;
    }
};
const formatDate = (date) => format(parseISO(date), 'd MMMM yyyy', { locale: ru }); // Форматируем дату

const processingRadio = (kind) => {
    processing.value = kind;
};

const handleGetClient = (id) => {
    userContract.value = props.clients.find((client) => client.id === id);
};
const getYearDifference = (startDate, endDate) => {
    return differenceInYears(parseISO(endDate), parseISO(startDate)); // Разница в годах
};
const handleGetContract = (contract_id) => {
    sum.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).sum;
    let tmpCreate = userContract.value.user_contracts.find((contract) => contract.id === contract_id).create_date;
    let tmpDeadline = userContract.value.user_contracts.find((contract) => contract.id === contract_id).deadline;
    procent.value = userContract.value.user_contracts.find((contract) => contract.id === contract_id).procent;
    form.manager_id = userContract.value.user_contracts.find((contract) => contract.id === contract_id).manager_id;

    const termYears = getYearDifference(tmpCreate, tmpDeadline);

    dividends.value = sum.value * (procent.value / 100) * termYears;
    create_date.value = formatDate(tmpCreate);
    term.value = termYears === 1 ? termYears + ' год' : termYears + ' года';
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
});

const submit = () => {
    form.post(route('add.application'));
};
</script>
<template>
    <Head title="newApplication" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Новая заявка</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mt-4">
                                {{ props.clients }}
                                <InputLabel for="manager" value="Выберите клиента*" />
                                <select
                                    id="manager"
                                    v-model="form.user_id"
                                    @change="handleGetClient(form.user_id)"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="" disabled>Выберите клиента</option>
                                    <!-- Выводим список менеджеров -->
                                    <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                        {{ client.full_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.user_id" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="contract_number" value="Выберите номер договора*" />
                                <select
                                    id="contract_number"
                                    v-model="form.contract_id"
                                    @change="handleGetContract(form.contract_id)"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
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
                                <InputError class="mt-2" :message="form.errors.contract_number" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Дата заключения*" />
                                <p
                                    id="sum"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {{ create_date }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Срок договора" />
                                <p
                                    id="sum"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {{ term }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Ставка" />
                                <p
                                    id="sum"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {{ procent }}%
                                </p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Основная сумма*" />
                                <p
                                    id="sum"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {{ sum }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="dividends" value="Дивиденды*" />
                                <p
                                    id="dividends"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {{ dividends }}
                                </p>
                            </div>
                            <div class="mt-4">
                                <p>Условия списания</p>
                                <div v-for="(condition, index) in conditionsType" :key="index">
                                    <input
                                        type="radio"
                                        id="dewey"
                                        name="drone"
                                        v-model="form.condition"
                                        :value="condition.type"
                                        @click="conditionRadio(condition.value)"
                                    />
                                    <label for="dewey">{{ condition.type }}</label>
                                </div>
                            </div>

                            <div v-if="condition === false" class="mt-4">
                                <p>Вывод средств</p>
                                <div class="mt-4">
                                    <InputLabel for="sum" value="Сумма списания" />
                                    <p
                                        id="sum"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {{ sum }}
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="create_date" value="Дата планируемой выплаты" />
                                    <input
                                        id="create_date"
                                        type="date"
                                        v-model="form.date_of_payments"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError class="mt-2" :message="form.errors.date_of_payments" />
                                </div>
                                <p>
                                    Комиссия за вывод раньше срока 30%
                                    {{ sum * 0.3 }}
                                </p>
                            </div>
                            <div v-if="condition === true" class="mt-4">
                                <div class="mt-4">
                                    <h1>Варианты списания</h1>
                                    <div v-for="(processing, index) in typeOfProcessing" :key="index">
                                        <input
                                            type="radio"
                                            :id="'test' + `${index}`"
                                            :name="'test' + `${index}`"
                                            v-model="form.type_of_processing"
                                            :value="processing.type"
                                            @click="processingRadio(processing.value)"
                                        />
                                        <label :for="'test' + `${index}`">{{ processing.type }}</label>
                                    </div>
                                    <p>Вывод средств</p>
                                    <div v-if="processing === 0 || processing === 1">
                                        <div class="mt-4">
                                            <InputLabel for="dividends" value="Дивиденды" />
                                            <TextInput
                                                id="dividends"
                                                :disabled="processing === 1"
                                                v-model="form.sum"
                                                type="text"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div class="mt-4">
                                            <InputLabel for="create_date" value="Дата планируемой выплаты" />
                                            <TextInput
                                                id="create_date"
                                                type="date"
                                                v-model="form.date_of_payments"
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <InputError class="mt-2" :message="form.errors.date_of_payments" />
                                        </div>
                                    </div>
                                    <div v-if="processing === 2">
                                        <div class="mt-4">
                                            <InputLabel for="sum" value="Основная сумма*" />
                                            <TextInput
                                                id="sum"
                                                type="text"
                                                disabled
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :placeholder="sum"
                                            />
                                        </div>
                                        <div class="mt-4">
                                            <InputLabel for="dividends" value="Дивиденды*" />
                                            <TextInput
                                                id="dividends"
                                                type="text"
                                                disabled
                                                :placeholder="dividends"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div class="mt-4">
                                            <InputLabel for="create_date" value="Дата планируемой выплаты" />
                                            <TextInput
                                                id="create_date"
                                                type="date"
                                                v-model="form.date_of_payments"
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <InputError class="mt-2" :message="form.errors.date_of_payments" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton @click="save" class="mt-4" :class="{ 'opacity-25': form.processing }">
                                    Сохранить
                                </PrimaryButton>
                                <PrimaryButton
                                    @click="cancel"
                                    class="mt-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Отмена
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
