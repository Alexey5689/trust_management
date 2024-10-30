<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: {
        type: String,
        required: true
    },
    client:{
        type: Object,
        required: true
    },
    managers:{
        type:Array,
        required:true
    },
    assignedManager:{
        type:String,
        required:true
    }
});

const form = useForm({
    last_name: props.client.last_name,
    first_name: props.client.first_name,
    middle_name: props.client.middle_name,
    email: props.client.email,
    phone_number: props.client.phone_number,
    manager_id: props.assignedManager,
});

const submit = () => {
    form.patch(route(`${props.role}.edit.client`, { client: props.client.id }), {
        // onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="EditManager" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Изменени клиента</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <form @submit.prevent="submit">
                                <div>
                                    <InputLabel for="last_name" value="Фамилия" />

                                    <TextInput id="last_name" type="text" class="mt-1 block w-full"
                                        v-model="form.last_name" required autofocus />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="first_name" value="Имя" />

                                    <TextInput id="first_name" type="text" class="mt-1 block w-full"
                                        v-model="form.first_name" required autofocus />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="middle_name" value="Отчество" />

                                    <TextInput id="middle_name" type="text" class="mt-1 block w-full"
                                        v-model="form.middle_name" required autofocus />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="email" value="Email" />

                                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                        required />

                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="phone_number" value="Телефон" />

                                    <TextInput id="phone_number" type="tel" class="mt-1 block w-full"
                                        v-model="form.phone_number" required />

                                    <InputError class="mt-2" :message="form.errors.email" />
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
