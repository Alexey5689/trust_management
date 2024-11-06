<script setup>
import Dashboard from '@/Pages/Dashboard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { parseISO, differenceInYears, format } from 'date-fns';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Inertia } from '@inertiajs/inertia';
 const props = defineProps({
    role:{
        type: Object,
        required: true
    },
    applications:{
        type: Array,
        required: true
    }
});


</script>
<template>
    <Head title="Applications" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Заявки</h2>
            <ResponsiveNavLink v-if="role === 'admin' || role === 'manager'" :href="route('manager.add.application')"> Добавить заявку </ResponsiveNavLink>

        </template>
        <template #main>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div v-if="props.applications.length === 0">Заявок нет</div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
    </AuthenticatedLayout>
</template>
<style scoped>
.client{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style>
