<script setup>
import Dashboard from '@/Pages/Dashboard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Inertia } from '@inertiajs/inertia';
import { parseISO, differenceInYears, format, differenceInDays } from 'date-fns';
import { computed, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({
    contracts:{
        type: Array,
        required: true
    },
    role:{
        type: Object,
        required: true
    }
});

const formatDate = date => format(parseISO(date), 'dd/MM/yyyy'); // Форматируем дату
const getYearDifference =(startDate, endDate)=> {
      return differenceInYears(parseISO(endDate), parseISO(startDate)); // Разница в годах
}
const deleteContract = (contractId) => {
    if(confirm("Вы точно хотите удалить договор?")){
        Inertia.delete(route('admin.delete.contract', { contract: contractId }));
    }
};
const nowDate = format(new Date(), 'yyyy-MM-dd');
// Вычисление основной суммы
const sum = computed(() => {
    return props.contracts.reduce((total, contract) => total + contract.sum, 0);
});

const dividents =ref(null);

// Вычисление дивидендов
const getDividends = (rate) => {
    dividents.value = 0;
    props.contracts.forEach(contract => {
        const termYears = differenceInYears(parseISO(contract.deadline), parseISO(contract.create_date));

        const dailyRate = (contract.sum * (contract.procent / 100)/rate) ;
       dividents.value += dailyRate;
    });
};






</script>
<template>
    <Head title="Contracts" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Договоры</h2>
            <ResponsiveNavLink v-if="props.role === 'admin' || props.role === 'manager'" :href="route(`${props.role}.add.contract`)"> Добавить договор </ResponsiveNavLink>
        </template>
        <template #main>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div v-if="props.contracts.length == 0">Договоров нет</div>
                                <div v-else class="client" v-for ="contract in props.contracts" :key="contract.id">
                                    <div v-if="props.role === 'admin' || props.role === 'manager'">
                                        <InputLabel for="contract_number" value="Клиент" />
                                        <p>{{ contract.user.last_name}} {{contract.user.first_name}} {{contract.user.middle_name }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Договор" />
                                        <p>{{ contract.contract_number }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Дата" />
                                        <p>{{ formatDate(contract.create_date) }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Срок договора" />
                                        <p> {{getYearDifference(contract.create_date,contract.deadline) === 1 ? getYearDifference(contract.create_date,contract.deadline) + ' год' : getYearDifference(contract.create_date,contract.deadline) + ' года'}} </p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Ставка %" />
                                        <p>{{ contract.procent }}</p>
                                    </div>

                                    <div v-if="(role === 'admin'|| role === 'manager') && contract.payments ">
                                        <InputLabel for="contract_number" value="Выплаты" />
                                        <p>{{ contract.payments }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Сумма" />
                                        <p>{{ contract.sum }}</p>
                                    </div>
                                    <Dropdown v-if="role === 'admin'"  align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template  #content  >
                                        <DropdownLink :href="route(`admin.edit.contract`,{ contract: contract.id })"as="button">
                                            Изменить
                                        </DropdownLink>
                                        <DropdownLink  @click="deleteContract(contract.id)"  as="button">
                                            Удалить
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if=" role === 'client'" class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <header>
                                    <h2 class="text-lg font-medium text-gray-900">График начислений</h2>
                                </header>
                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton @click="getDividends(365)" class="mt-4">
                                        День
                                    </PrimaryButton>
                                    <PrimaryButton @click="getDividends(12)" class="mt-4">
                                        Месяц
                                    </PrimaryButton>
                                    <PrimaryButton @click="getDividends(1)" class="mt-4">
                                        Год
                                    </PrimaryButton>
                                    <PrimaryButton @click="getDividends(52)" class="mt-4">
                                        Неделя
                                    </PrimaryButton>
                                </div>
                                <div class="flex gap-9">
                                    {{ sum }}
                                    {{  Math.round(dividents) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

    </AuthenticatedLayout>
</template>
<style scoped>
.client{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style>
