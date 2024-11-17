<script setup>
import Dashboard from '@/Pages/Dashboard.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    role: {
        type: String,
        required: true,
    },
});

const form = useForm({
    last_name: '',
    first_name: '',
    middle_name: '',
    email: '',
    phone_number: '',
    role_id: 2,
});

const submit = () => {
    form.post(route('admin.registration.manager'), {
        // onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Регитсрация менеджера</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <form @submit.prevent="submit">
                                <div>
                                    <InputLabel for="last_name" value="Фамилия" />

                                    <TextInput
                                        id="last_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.last_name"
                                        required
                                        autofocus
                                    />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="first_name" value="Имя" />

                                    <TextInput
                                        id="first_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.first_name"
                                        required
                                        autofocus
                                    />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="middle_name" value="Отчество" />

                                    <TextInput
                                        id="middle_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.middle_name"
                                        required
                                        autofocus
                                    />

                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="email" value="Email" />

                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                    />

                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="phone_number" value="Телефон" />

                                    <TextInput
                                        id="phone_number"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.phone_number"
                                        required
                                    />

                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton
                                        class="mt-4"
                                        :class="{
                                            'opacity-25': form.processing,
                                        }"
                                        :disabled="form.processing"
                                    >
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
