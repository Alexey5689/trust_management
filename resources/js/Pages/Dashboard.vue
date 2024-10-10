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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <header>
                                <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                        </header>
                        <div>
                            <InputLabel for="last_name" value="Фамилия" />
                            <TextInput
                                id="last_name"
                                type="text"
                                class="mt-1 block w-md"
                                v-model="user.last_name"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="user.last_name"
                            />
                        </div>
                        <div>
                            <InputLabel for="first_name" value="Имя" />
                            <TextInput
                                id="first_name"
                                type="text"
                                class="mt-1 block w-md"
                                v-model="user.first_name"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="user.first_name"
                            />
                        </div>
                        <div>
                            <InputLabel for="middle_name" value="Отчество" />
                            <TextInput
                                id="middle_name"
                                type="text"
                                class="mt-1 block w-md"
                                v-model="user.middle_name"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="user.middle_name"
                            />
                        </div>
                        <!-- <div>
                            <InputLabel for="password" value="Пароль" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-md"
                                v-model="user.password"
                                :placeholder="user.password"
                            />
                        </div> -->
                        <div>
                            {{ user }}
                        </div>

                    </div>
                    <div class="flex items-center gap-4 p-6">
                            <PrimaryButton >Сменить пароль</PrimaryButton>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
