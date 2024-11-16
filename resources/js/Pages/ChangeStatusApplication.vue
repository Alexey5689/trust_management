<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    application: {
        type: Object,
        required: true,
    },
})
const newStatus = ref(props.application.status)
const applicationStatuses = ref(['В обработке', 'Согласована', 'Исполнена', 'Отменена'])
const form = useForm({
    status: newStatus,
})
</script>
<template>
    <Head title="changeStatusApplication" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Статус заявки</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <form @submit.prevent="submit">
                                <div class="mt-4">
                                    <InputLabel for="status" value="Выберите статус*" />
                                    <select
                                        id="manager"
                                        v-model="newStatus"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <!-- Выводим список менеджеров -->
                                        <option
                                            v-for="(status, index) in applicationStatuses"
                                            :key="index"
                                            :value="status"
                                        >
                                            {{ status }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped></style>
