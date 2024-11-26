<script setup>
import { onMounted, ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// import Modal from '@/Components/Modal/ModelTest.vue';

import BaseModal from '@/Components/Modal/BaseModal.vue';

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
});

const showStatusMessage = ref(false);

const status = ref('');

onMounted(() => {
    localStorage.setItem('userInfo', JSON.stringify({ full_name: props.user.full_name, email: props.user.email }));
    if (props.status) {
        status.value = props.status;
        showStatusMessage.value = true;
        setTimeout(() => {
            showStatusMessage.value = false;
        }, 3000); // 10000 мс = 10 секунд
    }
});

const isModalOpen = ref(false);
const currentModal = ref(null);

const modalTitles = {
    contacts: 'Изменение контактных данных',
    password: 'Изменение пароля',
    email: 'Изменение почты',
};

const openModal = (type) => {
    currentModal.value = type;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
};
</script>

<template>

    <Head title="Profile" />
    <AuthenticatedLayout :userInfo="props.user" :userRole="props.role">
        <template #header>
            <h2 class="title">Личный кабинет</h2>
        </template>
        <template #main>
            <div class="card">
                <header>
                    <h2 class="title-card">Контактные данные</h2>
                </header>
                <div class="card-content flex">
                    <!-- <div>
                        <p v-if="showStatusMessage" class="">
                            {{ status }}
                        </p>
                    </div> -->

                    <div class="card-item">
                        <InputLabel for="last_name" value="ФИО" />
                        <p class="text">{{ props.user.full_name }}</p>
                        <ResponsiveNavLink :href="route(`profile.edit`)">
                            Изменить контактные данные
                        </ResponsiveNavLink>
                    </div>

                    <div class="card-item">
                        <InputLabel for="last_name" value="Пароль" />
                        <p class="text">********</p>
                        <ResponsiveNavLink :href="route(`password.edit`)"> Изменить пароль </ResponsiveNavLink>
                    </div>

                    <div class="card-item">
                        <InputLabel for="last_name" value="Почта" />
                        <p class="text">{{ props.user.email }}</p>
                        <ResponsiveNavLink v-if="props.role === 'admin'" :href="route(`email.edit`)">
                            Изменить почту
                        </ResponsiveNavLink>
                    </div>

                    <!-- <div>
                        {{ props.user }}
                    </div> -->


                </div>

            </div>
            <button @click="openModal('contacts')">Изменить контактные данные</button>
            <button @click="openModal('password')">Изменить пароль</button>
            <button @click="openModal('email')">Изменить почту</button>
        </template>
    </AuthenticatedLayout>





    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal]" @close="closeModal">
        <template #default>
            <p>Содержимое для модального окна с заголовком "{{ modalTitles[currentModal] }}".</p>
        </template>
        <template #footer>
            <button @click="closeModal" class="btn-cancel">Отменить</button>
            <button class="btn-save">Сохранить</button>
        </template>
    </BaseModal>








    <!-- <Modal /> -->
</template>

<style scoped>
.title {
    margin-top: 54px;
    line-height: 42px;
    font-size: 30px;
    font-weight: 600;
    color: #000;
    margin-bottom: 32px;
}

.card {
    background: #fff;
    padding: 32px;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
}

.card-content {
    margin-top: 32px;
}

.card-item {
    width: 33.33%;
}

.text {
    margin-bottom: 16px;
}

.btn-cancel {
    margin-right: 10px;
}
</style>
