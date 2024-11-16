<script setup>
import Dashboard from './Dashboard.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head } from '@inertiajs/vue3'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted, ref } from 'vue'
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
})

const showStatusMessage = ref(false)

const status = ref('')

onMounted(() => {
    if (props.status) {
        status.value = props.status
        showStatusMessage.value = true
        setTimeout(() => {
            showStatusMessage.value = false
        }, 3000) // 10000 мс = 10 секунд
    }
})
</script>

<template>
    <Head title="Profile" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Личный кабинет</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Контактные данные</h2>
                            </header>
                            <div>
                                <p v-if="showStatusMessage" class="text-red-600">
                                    {{ status }}
                                </p>
                            </div>
                            <div>
                                <InputLabel for="last_name" value="ФИО" />
                                <p>{{ props.user.full_name }}</p>
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Пароль" />
                                <p>{{ props.user.password }}</p>
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Email" />
                                <p>{{ props.user.email }}</p>
                            </div>

                            <div>
                                {{ props.user }}
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-6">
                            <!-- <PrimaryButton>Изменить пароль</PrimaryButton> -->
                            <!-- <PrimaryButton>Изменить контактные данные</PrimaryButton> -->
                            <ResponsiveNavLink :href="route(`password.edit`)"> Изменить пароль </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route(`profile.edit`)">
                                Изменить контактные данные
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="props.role === 'admin'" :href="route(`email.edit`)">
                                Изменить почту
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
