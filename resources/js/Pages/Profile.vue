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
                <h2 class="">Личный кабинет</h2>
            </template>
            <template #main>
                <div class="">
                    <div class="">
                        <div class="">
                            <div class="">
                                <header>
                                    <h2 class="">Контактные данные</h2>
                                </header>
                                <div>
                                    <p v-if="showStatusMessage" class="">
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
                            <div class="">
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
