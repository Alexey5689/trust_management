<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import InputLabel from '@/Components/InputLabel.vue';

import { Head } from '@inertiajs/vue3';

import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
    clients:{
        type: Array,
        required: true
    }
});

</script>
<template>
    <Head title="Clients" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Клиенты</h2>
            <ResponsiveNavLink :href="route('registration.client')"> Добавить клиента </ResponsiveNavLink>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                            <h2 class="text-lg font-medium text-gray-900">Клиенты</h2>
                            <div class="flex items-center gap-4 p-6">
                        </div>
                        <div class="client" v-for ="client in clients" :key="client.id">
                            <div>
                                <InputLabel for="last_name" value="ID" />
                                 {{ client.id }}

                            </div>
                            <div>
                                <InputLabel for="last_name" value="ФИО" />
                                 {{ client.last_name }} {{ client.first_name }} {{ client.middle_name }}

                            </div>
                            <div>
                                <InputLabel for="last_name" value="Номер телефона" />
                                 {{ client.phone_number }}
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Email" />
                                 {{ client.email }}
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Договор" />
                                 <div v-for ="contract in client.user_contracts" :key="contract.id">{{ contract.contract_number }}</div>
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
