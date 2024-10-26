<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props =  defineProps({
    userEmail:{
        type: String,
        required: true
    },
    role:{
        type: String,
        required: true
    }
});
const page = usePage();

const form = useForm({
    email: props.userEmail,
});

const save = () => {
    form.patch(route('email.update'), {
        // onFinish: () => form.reset('password', 'password_confirmation'),
    });


};
</script>
<template>
    <Head title="Edit" />
    <AuthenticatedLayout :userRole="props.role">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Изменение почты</h2>
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
                            <form @submit.prevent="save">
                                <div class="mt-4">
                                    <InputLabel for="email" value="Email" />

                                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                        required />

                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton class="mt-4" :class="{ 'opacity-25': form.processing }" >
                                        Сохранить
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
