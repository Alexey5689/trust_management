<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
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
const conditionsType = ref([
    { type: 'Раньше срока', value: false },
    { type: 'В срок', value: true },
])
const typeOfProcessing = ref([
    { type: 'Забрать дивиденды частично', value: 0 },
    { type: 'Забрать дивиденды целиком', value: 1 },
    { type: 'Забрать дивиденды и сумму', value: 2 },
])
</script>
<template>
    <Head title="showApplication" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Заявка
            </h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            {{ application }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped></style>
