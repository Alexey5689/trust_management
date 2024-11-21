<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDate, getYearDifference, calculateDeadlineDate } from '@/helpers.js';
import { ref } from 'vue';

const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    contract: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        required: true,
    },
});

const selectedDuration = ref('');
const form = useForm({
    user_id: props.contract.user_id,
    contract_number: props.contract.contract_number,
    sum: props.contract.sum,
    deadline: props.contract.deadline, // Срок договора
    procent: props.contract.procent, // Процентная ставка
    agree_with_terms: props.contract.agree_with_terms, // Для чекбокса
    create_date: props.contract.create_date, // Дата заключения
    contract_status: true,
    payments: props.contract.payments, // Выплаты
});

// Обработчик изменения срока договора
const handleDeadlineChange = (event) => {
    const selectedDuration = event.target.value;
    if (selectedDuration === '1 год') {
        form.deadline = calculateDeadlineDate(1, props.contract.create_date);
    } else {
        form.deadline = calculateDeadlineDate(3, props.contract.create_date);
    }
};

const submit = () => {
    form.patch(route('edit.contract', { contract: props.contract.id }), {
        onFinish: () =>
            form.reset(
                'contract_number',
                'sum',
                'deadline',
                'selectedDuration',
                'procent',
                'agree_with_terms',
                'create_date',
                'payments',
            ),
    });

    // console.log(form);
};
</script>

<template>
    <Head title="Register" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Изменение договора</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <form @submit.prevent="submit">
                                <div class="mt-4">
                                    {{ clients }}
                                    {{ contract }}
                                    <InputLabel for="clients" value="Выберите клиента*" />
                                    <select
                                        id="clients"
                                        v-model="form.user_id"
                                        required
                                        @change="handleGetManagers"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="" disabled>Выберите клиента</option>
                                        <!-- Выводим список менеджеров -->
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.full_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.user_id" />
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="contract_number" value="Номер договора*" />
                                    <TextInput
                                        id="contract_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.contract_number"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.contract_number" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="sum" value="Сумма*" />
                                    <TextInput
                                        id="sum"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.sum"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.sum" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="create_date" value="Дата заключения договора*" />
                                    <input
                                        id="create_date"
                                        type="date"
                                        v-model="form.create_date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError class="mt-2" :message="form.errors.create_date" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="deadline" value="Срок договора*" />
                                    <select
                                        id="deadline"
                                        v-model="selectedDuration"
                                        @change="handleDeadlineChange"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="" disabled>
                                            {{
                                                getYearDifference(form.create_date, form.deadline) === 1
                                                    ? getYearDifference(form.create_date, form.deadline) + ' год'
                                                    : getYearDifference(form.create_date, form.deadline) + ' года'
                                            }}
                                        </option>
                                        <option value="1 год">1 год</option>
                                        <option value="3 года">3 года</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.deadline" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="procent" value="Ставка, %*" />
                                    <TextInput
                                        id="procent"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.procent"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.procent" />
                                </div>

                                <div class="mt-4">
                                    <label class="inline-flex items-center">
                                        <input
                                            id="agree_with_terms"
                                            type="checkbox"
                                            v-model="form.agree_with_terms"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600"
                                            >Вычислить дивиденды по истечению срока</span
                                        >
                                    </label>
                                    <InputError class="mt-2" :message="form.errors.agree_with_terms" />
                                </div>

                                <div v-if="!form.agree_with_terms" class="mt-4">
                                    <InputLabel for="payments" value="Выплаты*" />
                                    <select
                                        id="payments"
                                        v-model="form.payments"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="" disabled>Выберите интервал выплат</option>
                                        <option value="Ежеквартально">Ежеквартально</option>
                                        <option value="Ежегодно">Ежегодно</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.payments" />
                                </div>
                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton
                                        class="mt-4"
                                        :class="{
                                            'opacity-25': form.processing,
                                        }"
                                        :disabled="form.processing"
                                    >
                                        Сохранить
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
