<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { parseISO, differenceInYears, format } from 'date-fns'
import { ru } from 'date-fns/locale'
import { ref, computed } from 'vue'
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    application: {
        type: Object,
        required: true,
    },
})
const conditionsType = ref([
    { type: 'Раньше срока', value: false },
    { type: 'В срок', value: true },
])
const typeOfProcessing = ref([
    { type: 'Забрать дивиденды частично', value: 0 },
    { type: 'Забрать дивиденды целиком', value: 1 },
    { type: 'Забрать дивиденды и сумму', value: 2 },
])
const getYearDifference = (startDate, endDate) => {
    return differenceInYears(parseISO(endDate), parseISO(startDate)) // Разница в годах
}

const client = ref(props.application.user)
const contract_number = ref(props.application.contract.contract_number)
const create_date = ref(format(parseISO(props.application.contract.create_date), 'd MMMM yyyy', { locale: ru })) // Дата создания контракта преобразуем в 1 ноября 2024
const term = ref(
    getYearDifference(props.application.contract.create_date, props.application.contract.deadline) === 1
        ? getYearDifference(props.application.contract.create_date, props.application.contract.deadline) + ' год'
        : getYearDifference(props.application.contract.create_date, props.application.contract.deadline) + ' года',
)

const procent = ref(props.application.contract.procent + '%')
const sum = ref(props.application.contract.sum)

const dividends = computed(() => {
    if (term.value === '2 года') {
        return sum.value * (procent.value / 100) * 2
    } else if (term.value === '1 год') {
        return sum.value * (procent.value / 100) * 1
    } else {
        return sum.value * (procent.value / 100) * 3
    }
}) //ошибка исправить
</script>
<template>
    <Head title="showApplication" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Заявка</h2>
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
                                    v-model="create_date"
                                    type="text"
                                    class="mt-1 block w-full"
                                    disabled
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="term" value="Срок договора" />
                                <TextInput id="term" v-model="term" type="text" class="mt-1 block w-full" disabled />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="procent" value="Процент" />
                                <TextInput
                                    id="procent"
                                    v-model="procent"
                                    type="text"
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
                            {{}}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped></style>
