<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
    role:{
        type: String,
        required: true
    }
});
const page = usePage();

const form = useForm({
    password: '',
    password_confirmation: '',
});

const change = () => {
    form.patch(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
<template>
    <Head title="Edit" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Изменение пароля</h2>
        </template>
        <template #main>
            <div class="py-12">
                <div v-if="page.props.flash && page.props.flash.success" class="alert alert-success">
                {{ page.props.flash.success }}
            </div>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="p-6 text-gray-900">
                                {{ user }}
                                <form @submit.prevent="change">
                                    <div class="mt-4">
                                        <InputLabel for="password" value="Password" />
                                        <TextInput
                                        id="password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="form.password" required
                                        autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="password_confirmation" value="Confirm Password" />

                                        <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="form.password_confirmation"
                                        required autocomplete="new-password"
                                        />

                                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                    </div>
                                    <div class="flex items-center justify-end mt-4">
                                        <PrimaryButton class="mt-4" :class="{ 'opacity-25': form.processing }" >
                                            Сменить
                                        </PrimaryButton>
                                        <PrimaryButton class="mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Отмена
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
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
