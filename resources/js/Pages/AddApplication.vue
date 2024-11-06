<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

import { ref } from 'vue';

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

const form = useForm({
    user_id: '',
    contract_number: null,
    // sum: null,
    // deadline: '', // Срок договора
    // procent: '', // Процентная ставка
    // agree_with_terms: false, // Для чекбокса
    // create_date: new Date().toISOString().substr(0, 10), // Дата заключения
    // contract_status:true,
    // payments:'', // Выплаты
});
const userContract = ref({});

const handleGetInfo = (id) => {
    userContract.value = props.clients.find((client) => client.id === id);
    console.log(userContract.value.user_contracts);
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
                                    <select id="manager" v-model="form.user_id" required
                                        @change="handleGetInfo(form.user_id)"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                    <select id="contract_number" v-model="form.contract_number" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Выберите номер договора</option>
                                        <!-- Выводим список менеджеров -->
                                        <option v-for="contract in userContract.user_contracts" :key="contract.id" :value="contract.contract_number">
                                             {{ contract.contract_number }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.contract_number" />
                            </div>
                        </form>
                        {{ userContract.value }}
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
