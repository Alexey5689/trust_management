<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import InputLabel from '@/Components/InputLabel.vue';

import { Head } from '@inertiajs/vue3';

import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
    managers:{
        type: Array,
        required: true
    }
});

</script>
<template>
    <Head title="Managers" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Менеджеры</h2>
            <ResponsiveNavLink :href="route('registration.manager')"> Добавить менеджера </ResponsiveNavLink>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                            <h2 class="text-lg font-medium text-gray-900">Менеджеры</h2>
                            <div class="flex items-center gap-4 p-6">
                        </div>
                        <div class="client" v-for =" manager in  managers" :key="manager.id">
                            <div>
                                <InputLabel for="last_name" value="ID" />
                                 {{ manager.id }}

                            </div>
                            <div>
                                <InputLabel for="last_name" value="ФИО" />
                                 {{ manager.last_name }} {{ manager.first_name }} {{ manager.middle_name }}

                            </div>
                            <div>
                                <InputLabel for="last_name" value="Номер телефона" />
                                 {{ manager.phone_number }}
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Email" />
                                 {{ manager.email }}
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Договор" />
                                 <div v-for ="contract in manager.manager_contracts" :key="contract.id">{{ contract.contract_number }}</div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
.client{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style>
