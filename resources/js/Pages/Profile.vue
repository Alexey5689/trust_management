<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
defineProps({
    user:{
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Личный кабинет</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <header>
                                <h2 class="text-lg font-medium text-gray-900">Контактные данные</h2>
                        </header>
                        <div>
                            <InputLabel for="last_name" value="Фамилия" />
                            <p>{{user.last_name}}</p>
                        </div>
                        <div>
                            <InputLabel for="first_name" value="Имя" />
                            <p>{{user.first_name}}</p>
                        </div>
                        <div>
                            <InputLabel for="middle_name" value="Отчество" />
                            <p>{{user.middle_name}}</p>
                        </div>
                        <div>
                            <InputLabel for="middle_name" value="Номер телефона" />
                            <p>{{user.phone_number}}</p>
                        </div>
                        <div>
                            {{ user }}
                        </div>

                    </div>
                    <div class="flex items-center gap-4 p-6">
                            <PrimaryButton>Изменить пароль</PrimaryButton>
                            <PrimaryButton>Изменить контактные данные</PrimaryButton>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
