<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { parseISO, differenceInYears, format } from 'date-fns';
import { ru } from 'date-fns/locale';
import { ref, computed } from 'vue';
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    application: {
        type: Object,
        required: true,
    },
});
const conditionsType = ref([
    { type: 'Раньше срока', value: false },
    { type: 'В срок', value: true },
]);
const typeOfProcessing = ref([
    { type: 'Забрать дивиденды частично', value: 0 },
    { type: 'Забрать дивиденды целиком', value: 1 },
    { type: 'Забрать дивиденды и сумму', value: 2 },
]);
const getYearDifference = (startDate, endDate) => {
    return differenceInYears(parseISO(endDate), parseISO(startDate)); // Разница в годах
};

const application_create_date = ref(format(parseISO(props.application.create_date), 'd MMMM yyyy', { locale: ru }));
const client = ref(props.application.user);
const contract_number = ref(props.application.contract.contract_number);
const contract_create_date = ref(
    format(parseISO(props.application.contract.create_date), 'd MMMM yyyy', { locale: ru }),
); // Дата создания контракта преобразуем в 1 ноября 2024
const term = ref(
    getYearDifference(props.application.contract.create_date, props.application.contract.deadline) === 1
        ? getYearDifference(props.application.contract.create_date, props.application.contract.deadline)
        : getYearDifference(props.application.contract.create_date, props.application.contract.deadline),
);

const procent = ref(props.application.contract.procent);
const sum = ref(props.application.contract.sum);

const dividends = computed(() => {
    return sum.value * (procent.value / 100) * term.value;
});

const conditionType = ref(props.application.condition);
const processingTYpe = ref(props.application.type_of_processing);
</script>
<template>
    <Head title="showApplication" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ application_create_date }}</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            {{ application }}
                            <div class="mt-4">
                                <InputLabel for="full_name" value="Клиент" />
                                <TextInput
                                    v-model="client.full_name"
                                    id="full_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="contract_number" value="Договор" />
                                <TextInput
                                    id="contract_number"
                                    v-model="contract_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="create_date" value="Дата заключения" />
                                <TextInput
                                    id="create_date"
                                    v-model="contract_create_date"
                                    type="text"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="term" value="Срок договора" />
                                <TextInput id="term" v-model="term" type="number" class="mt-1 block w-full" disabled />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="procent" value="Процент" />
                                <TextInput
                                    id="procent"
                                    v-model="procent"
                                    type="number"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="sum" value="Основная сумма" />
                                <TextInput id="sum" v-model="sum" type="text" class="mt-1 block w-full" disabled />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="dividends" value="Дивиденды" />
                                <TextInput
                                    id="dividends"
                                    v-model="dividends"
                                    type="text"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <p>Условия списания</p>
                                <div v-for="(condition, index) in conditionsType" :key="index">
                                    <input
                                        type="radio"
                                        v-model="conditionType"
                                        :id="'condition-' + index"
                                        name="condition"
                                        :value="condition.type"
                                        disabled
                                    />
                                    <label :for="'condition-' + index">{{ condition.type }}</label>
                                </div>
                            </div>
                            <div v-if="conditionType !== 'Раньше срока'" class="mt-4">
                                <div v-for="(processing, index) in typeOfProcessing" :key="index">
                                    <input
                                        type="radio"
                                        v-model="processingTYpe"
                                        :id="'processing' + index"
                                        name="processing"
                                        :value="processing.type"
                                        disabled
                                    />
                                    <label :for="'processing' + index">{{ processing.type }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped></style>
