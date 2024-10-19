<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import InputLabel from '@/Components/InputLabel.vue';

import { Head } from '@inertiajs/vue3';

import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
    contracts:{
        type: Array,
        required: true
    }
});

</script>
<template>
    <Head title="Contracts" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Договоры</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- {{ contracts }} -->
                        <div class="client" v-for ="contract in contracts" :key="contract.id">
                            <div>
                                <InputLabel for="contract_number" value="№" />
                                <p>{{ contract.id }}</p>
                            </div>
                            <div>
                                <InputLabel for="contract_number" value="Номер договора" />
                                <p>{{ contract.contract_number }}</p>
                            </div>
                            <div>
                                <InputLabel for="contract_number" value="Дата договора" />
                                <p>{{ contract.create_date }}</p>
                            </div>
                            <div>
                                <InputLabel for="contract_number" value="Сумма по договору" />
                                <p>{{ contract.sum }}</p>
                            </div>
                            <div>
                                <InputLabel for="contract_number" value="Срок договора" />
                                <p>{{ contract.deadline }}</p>
                            </div>
                            <div>
                                <InputLabel for="contract_number" value="Срок договора" />
                                <p>{{ contract.procent }}</p>
                            </div>
                        </div>


                        <!--
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

                        </div> -->
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
