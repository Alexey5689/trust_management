<script setup>
import Dashboard from '@/Pages/Dashboard.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


const props = defineProps({
    managers: Array, // Менеджеры, которые переданы из контроллера
    role: {
        type: String,
        required: true
    }
});

const form = useForm({
    last_name: '',
    first_name: '',
    middle_name: '',
    phone_number: '',
    email: '',
    contract_number: null,
    sum: null,
    deadline: '', // Срок договора
    selectedDuration: '',  // новое поле для хранения выбранного срока
    procent: '', // Процентная ставка
    agree_with_terms: false, // Для чекбокса
    create_date: new Date().toISOString().substr(0, 10), // Дата заключения
    payments: '', // Выплаты
    role_id: 3,
    manager_id: '', // Новое поле для выбора менеджера

});

// Функция для вычисления даты окончания договора на основе даты подписания
const calculateDeadlineDate = (years, createDate) => {
    const date = new Date(createDate);
    console.log('Дата ебать', date);

    // Сохраняем день и месяц из даты подписания
    const day = date.getDate();
    const month = date.getMonth();

    // Прибавляем годы
    date.setFullYear(date.getFullYear() + years);

    // Проверяем, чтобы месяц и день совпадали после изменения года
    // Если дата сместилась (например, 29 февраля в невисокосном году), мы устанавливаем исходный день
    if (date.getMonth() !== month) {
        date.setDate(0); // Устанавливаем последний день предыдущего месяца
    } else {
        date.setDate(day); // Восстанавливаем день
    }

    return date.toISOString().substr(0, 10); // Преобразуем в формат yyyy-mm-dd
};

// Обработчик изменения срока договора
const handleDeadlineChange = (event) => {
    const selectedDuration = event.target.value;
    form.selectedDuration = selectedDuration;
    if (selectedDuration === "1 год") {
        form.deadline = calculateDeadlineDate(1, form.create_date);
    } else if (selectedDuration === "2 года") {
        form.deadline = calculateDeadlineDate(2, form.create_date);
    } else if (selectedDuration === "3 года") {
        form.deadline = calculateDeadlineDate(3, form.create_date);
    }
};

const submit = () => {
    // let routeName;
    // if (props.role === 'admin') {
    //     routeName = 'admin.registration.client';
    // } else if (props.role === 'manager') {
    //     routeName = 'manager.registration.client';
    // }
    form.post(route(`${props.role}.registration.client`), {
        onFinish: () => form.reset(
            'last_name',
            'first_name',
            'middle_name',
            'phone_number',
            'email',
            'contract_number',
            'sum',
            'deadline',
            'selectedDuration',
            'procent',
            'agree_with_terms',
            'create_date',
            'payments', 'role_id',
            'manager_id'
        ),
    });

    console.log(form);
};
</script>

<template>

    <Head title="Register" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Регитсрация клиента</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <form @submit.prevent="submit">
                                <div>
                                    <InputLabel for="last_name" value="Фамилия*" />
                                    <TextInput id="last_name" type="text" class="mt-1 block w-full"
                                        v-model="form.last_name" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.last_name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="first_name" value="Имя*" />
                                    <TextInput id="first_name" type="text" class="mt-1 block w-full"
                                        v-model="form.first_name" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.first_name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="middle_name" value="Отчество*" />
                                    <TextInput id="middle_name" type="text" class="mt-1 block w-full"
                                        v-model="form.middle_name" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.middle_name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="phone_number" value="Номер телефона*" />
                                    <TextInput id="phone_number" type="tel" class="mt-1 block w-full"
                                        v-model="form.phone_number" required />
                                    <InputError class="mt-2" :message="form.errors.phone_number" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="email" value="Email*" />
                                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="contract_number" value="Номер договора*" />
                                    <TextInput id="contract_number" type="text" class="mt-1 block w-full"
                                        v-model="form.contract_number" required />
                                    <InputError class="mt-2" :message="form.errors.contract_number" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="sum" value="Сумма*" />
                                    <TextInput id="sum" type="tel" class="mt-1 block w-full" v-model="form.sum"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.sum" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="create_date" value="Дата заключения договора*" />
                                    <input id="create_date" type="date" v-model="form.create_date" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <InputError class="mt-2" :message="form.errors.create_date" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="deadline" value="Срок договора*" />


                                    <!-- <select id="deadline" v-model="form.deadline" @change="handleDeadlineChange" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Выберите срок договора</option>
                                        <option value="1 год">1 год</option>
                                        <option value="2 года">2 года</option>
                                        <option value="3 года">3 года</option>
                                    </select> -->


                                    <select id="deadline" v-model="form.selectedDuration" @change="handleDeadlineChange" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Выберите срок договора</option>
                                        <option value="1 год">1 год</option>
                                        <option value="2 года">2 года</option>
                                        <option value="3 года">3 года</option>
                                    </select>







                                    <InputError class="mt-2" :message="form.errors.deadline" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="procent" value="Ставка, %*" />
                                    <TextInput id="procent" type="number" class="mt-1 block w-full"
                                        v-model="form.procent" required autofocus />
                                    <InputError class="mt-2" :message="form.errors.procent" />
                                </div>

                                <div class="mt-4">
                                    <label class="inline-flex items-center">
                                        <input id="agree_with_terms" type="checkbox" v-model="form.agree_with_terms"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-600">Вычислить дивиденды по истечению
                                            срока</span>
                                    </label>
                                    <InputError class="mt-2" :message="form.errors.agree_with_terms" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="payments" value="Выплаты*" />
                                    <select id="payments" v-model="form.payments" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Выберите интервал выплат</option>
                                        <option value="Ежемесячно">Ежемесячно</option>
                                        <option value="Ежеквартально">Ежеквартально</option>
                                        <option value="Ежегодно">Ежегодно</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.payments" />
                                </div>

                                <div class="mt-4" v-if="role === 'admin'">
                                    <InputLabel for="manager" value="Выберите менеджера*" />
                                    <select id="manager" v-model="form.manager_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="" disabled>Выберите менеджера</option>
                                        <!-- Выводим список менеджеров -->
                                        <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                            {{ manager.last_name }} {{ manager.first_name }} {{ manager.middle_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.manager_id" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton class="mt-4" :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing">
                                        Register
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
