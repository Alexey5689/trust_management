<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
const props = defineProps({
    user: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
})
const page = usePage()

const form = useForm({
    last_name: props.user.last_name,
    first_name: props.user.first_name,
    middle_name: props.user.middle_name,
})

const save = () => {
    form.patch(route('profile.update'), {
        onFinish: () => form.reset('last_name', 'first_name', 'middle_name'),
    })
}
const cancel = () => {
    window.history.back()
}
</script>
<template>
    <Head title="Edit" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Изменение контактных данных
            </h2>
        </template>
        <template #main>
            <div class="py-12">
                <div
                    v-if="page.props.flash && page.props.flash.success"
                    class="alert alert-success"
                >
                    {{ page.props.flash.success }}
                </div>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6 text-gray-900">
                            <div class="p-6 text-gray-900">
                                {{ user }}
                                <form @submit.prevent>
                                    <div>
                                        <InputLabel
                                            for="last_name"
                                            value="Фамилия"
                                        />
                                        <TextInput
                                            id="last_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.last_name"
                                            required
                                            autofocus
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="form.errors.name"
                                        />
                                    </div>
                                    <div class="mt-4">
                                        <InputLabel
                                            for="first_name"
                                            value="Имя"
                                        />
                                        <TextInput
                                            id="first_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.first_name"
                                            required
                                            autofocus
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="form.errors.name"
                                        />
                                    </div>
                                    <div class="mt-4">
                                        <InputLabel
                                            for="middle_name"
                                            value="Отчество"
                                        />
                                        <TextInput
                                            id="middle_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.middle_name"
                                            required
                                            autofocus
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="form.errors.name"
                                        />
                                    </div>
                                </form>
                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton
                                        @click="save"
                                        class="mt-4"
                                        :class="{
                                            'opacity-25': form.processing,
                                        }"
                                    >
                                        Сохранить
                                    </PrimaryButton>
                                    <PrimaryButton
                                        @click="cancel"
                                        class="mt-4"
                                        :class="{
                                            'opacity-25': form.processing,
                                        }"
                                        :disabled="form.processing"
                                    >
                                        Отмена
                                    </PrimaryButton>
                                </div>
                                <!-- @submit.prevent="save"? -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped>
.client {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style>
