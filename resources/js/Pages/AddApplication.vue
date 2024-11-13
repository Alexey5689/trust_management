<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Radio from '@/Components/Radio.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format } from 'date-fns';
import { ref, computed } from 'vue';

const props = defineProps({
    role:{
        type: String,
        required: true
    },
    clients:{
        type: Array,
        required: true
    },
})


const selectedValue = ref('');
const userContract = ref({});
const sum = ref(null);
const dividends = ref(null);
const create_date = ref('');
const term = ref('');
const procent = ref(null);


const handleGetClient = (id) => {
    userContract.value = props.clients.find((client) => client.id === id);
}
const getYearDifference =(startDate, endDate)=> {
      return differenceInYears(parseISO(endDate), parseISO(startDate)); // Разница в годах
}
const handleGetContract = (contract_number) => {
    sum.value = userContract.value.user_contracts.find((contract) => contract.contract_number === contract_number).sum;
    let tmpCreate = userContract.value.user_contracts.find((contract) => contract.contract_number === contract_number).create_date;
    let tmpDeadline = userContract.value.user_contracts.find((contract) => contract.contract_number === contract_number).deadline;
    procent.value = userContract.value.user_contracts.find((contract) => contract.contract_number === contract_number).procent;

    if (getYearDifference(tmpCreate, tmpDeadline) === 2){ dividends.value = ((sum.value*(procent.value/100))/12)*24}
    else if (getYearDifference(tmpCreate, tmpDeadline) === 1) {dividends.value = ((sum.value*(procent.value/100))/12)*12}
    else{ dividends.value = ((sum.value*(procent.value/100))/12)*36}

    create_date.value = format(parseISO(tmpCreate), 'dd/MM/yyyy');
    term.value = getYearDifference(tmpCreate, tmpDeadline) === 1 ? getYearDifference(tmpCreate, tmpDeadline) + ' год' : getYearDifference(tmpCreate, tmpDeadline) + ' года';
}


const getInfo = (tmp) =>{
    form.condition = tmp;
    if(tmp === 'Раньше срока'){
        form.type_of_processing = 'Основная сумма';
    }
}



const form = useForm({
    create_date: new Date().toISOString().substr(0, 10),
    user_id: '',
    contract_number: null,
    condition: '',
    status: 'В обработке',
    type_of_processing:'',
    date_of_payments: '',
});

const submit =()=>{
    form.post(route(`${props.role}.add.application`));
}

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
                            <div class="mt-4" >
                                    {{ props.clients }}
                                    <InputLabel for="manager" value="Выберите клиента*" />
                                    <select id="manager" v-model="form.user_id"
                                        @change="handleGetClient(form.user_id)"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"required>
                                        <option value="" disabled>Выберите клиента</option>
                                        <!-- Выводим список менеджеров -->
                                        <option v-for="client in props.clients" :key="client.id" :value="client.id" >
                                             {{ client.full_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.user_id" />
                            </div>
                            <div class="mt-4" >
                                    <InputLabel for="contract_number" value="Выберите номер договора*" />
                                    <select id="contract_number" v-model="form.contract_number"
                                        @change="handleGetContract(form.contract_number)"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="" disabled> Выберите номер договора</option>
                                        <option v-for="contract in userContract.user_contracts" :key="contract.id" :value="contract.contract_number">
                                             {{ contract.contract_number }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.contract_number" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Дата заключения*" />
                                <p id="sum" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ create_date }}</p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Срок договора" />
                                <p id="sum" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ term }}</p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Ставка" />
                                <p id="sum" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ procent }}%</p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Основная сумма*" />
                                <p id="sum" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ sum }}</p>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="dividends" value="Дивиденды*" />
                                <p id="dividends" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ dividends }}</p>
                            </div>
                            <div class="mt-4">
                                <p>Условия списания</p>
                                <!-- <Radio  :checked="selectedValue" @click="getInfo(value)" value="Раньше срока" @update:checked="selectedValue = $event" /> Раньше срока
                                <Radio  :checked="selectedValue" @click="getInfo(value)" value="В срок" @update:checked="selectedValue = $event" /> В срок -->
                                <PrimaryButton  @click="getInfo('Раньше срока')"   class="mt-4" :class="{ 'opacity-25': form.processing }" >
                                    Раньше срока
                                </PrimaryButton>
                                <PrimaryButton @click="getInfo('В срок')" class="mt-4" :class="{ 'opacity-25': form.processing }">
                                        В срок
                                </PrimaryButton>
                            </div>
                            <div v-if="form.condition === 'Раньше срока'" class="mt-4">
                                <p>Вывод средств</p>
                                <div class="mt-4">
                                    <InputLabel for="sum" value="Сумма списания" />
                                    <p id="sum" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ sum }}</p>
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="create_date" value="Дата планируемой выплаты" />
                                    <input id="create_date" type="date" v-model="form.date_of_payments" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <InputError class="mt-2" :message="form.errors.date_of_payments" />
                                </div>
                                <p>Комиссия за вывод раньше срока 30% {{ sum * 0.3 }}</p>
                            </div>
                            <div v-if="form.condition === 'В срок'" class="mt-4">
                                <p>Вывод средств</p>
                            </div>
                            {{ selectedValue }}
                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton  @click="save" class="mt-4" :class="{ 'opacity-25': form.processing }" >
                                    Сохранить
                                </PrimaryButton>
                                <PrimaryButton @click="cancel" class="mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
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
