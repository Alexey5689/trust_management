<script setup>
import Dashboard from '@/Pages/Dashboard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format } from 'date-fns';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
defineProps({
    contracts:{
        type: Array,
        required: true
    },
    role:{
        type: Object,
        required: true
    }
});

const formatDate = (date) => {
      return format(parseISO(date), 'MM/dd/yyyy'); // Форматируем дату
}
const getYearDifference =(startDate, endDate)=> {
      return differenceInYears(parseISO(endDate), parseISO(startDate)); // Разница в годах
}
</script>
<template>
    <Head title="Contracts" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Договоры</h2>
            <ResponsiveNavLink v-if="role === 'Admin'|| role === 'Manager'" :href="route(`${role}.add.contract`)"> Добавить договор </ResponsiveNavLink>
        </template>
        <template #main>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div v-if="contracts.length == 0">Договоров нет</div>
                                <div v-else class="client" v-for ="contract in contracts" :key="contract.id">
                                    <div v-if="role === 'Admin'|| role === 'Manager' ">
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
                                        <InputLabel for="contract_number" value="Ставка %" />
                                        <p>{{ contract.procent }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Срок договора" />
                                        <p> {{getYearDifference(contract.create_date,contract.deadline) === 1 ? getYearDifference(contract.create_date,contract.deadline) + ' год' : getYearDifference(contract.create_date,contract.deadline) + ' года'}} </p>
                                    </div>
                                    <div v-if="(role === 'Admin'|| role === 'Manager') && contract.payments ">
                                        <InputLabel for="contract_number" value="Выплаты" />
                                        <p>{{ contract.payments }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="contract_number" value="Сумма" />
                                        <p>{{ contract.sum }}</p>
                                    </div>
                                    <Dropdown v-if="role === 'Admin'|| role === 'Manager' "  align="right" width="48">
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
                                    <template #content>
                                        <!-- <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink> -->
                                        <DropdownLink    as="button">
                                            Изменить
                                        </DropdownLink>
                                        <DropdownLink  as="button">
                                            Удалить
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
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
