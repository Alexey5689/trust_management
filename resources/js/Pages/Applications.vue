<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { formatDate } from '@/helpers.js';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import Ellipsis from '@/Components/Icon/Ellipsis.vue';
import Dropdown from '@/Components/Modal/Dropdown.vue';

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    applications: {
        type: Array,
        required: true,
    },
    status: {
        type: String,
        required: false,
    },
});

const isModalOpen = ref(false);
const currentModal = ref(null);

// const handleDropdownSelect = (option, applicationId, type) => {
//     switch (option.action) {
//         case 'information':
//             openModal('information', applicationId, 'information');
//             break;
//         case 'edit':
//             openModal('edit', applicationId, 'edit');
//             break;
//         default:
//             console.error('Неизвестное действие:', option.action);
//     }
// };

const handleDropdownSelect = (option, applicationId, type) => {
    openModal(type, applicationId, type);
};

const modalTitles = {
    add: 'Новая заявка',
    information: 'Подробная информация',
    edit: 'Название заявки'
};

const openModal = (type, applicationId, action = 'add') => {
    currentModal.value = { type, applicationId, action };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    currentModal.value = null;
};
</script>

<template>

    <Head title="Applications" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Заявки</h2>
                <ResponsiveNavLink class="add_application" v-if="role === 'admin' || role === 'manager'"
                    :href="route('add.application')">
                    Новая заявка
                </ResponsiveNavLink>
                <button class="add_application link-btn" @click="openModal('add')"
                    v-if="role === 'admin' || role === 'manager'">
                    Новая заявка
                </button>
            </div>
        </template>
        <template #main>
            <div class="main flex flex-column">
                <div class="card">
                    <header>
                        <h2 class="title-card">Активные заявки</h2>
                    </header>

                    <div class="application">
                        <ul class="thead-application align-center">
                            <li class="order">Дата заявки</li>
                            <li>Клиент</li>
                            <li>Договор</li>
                            <li>Условие</li>
                            <li>Статус</li>
                            <li>Вид списания</li>
                            <li>Дата выплаты</li>
                            <li>Сумма</li>
                            <li>Дивиденды</li>
                        </ul>
                        <div class="title" v-if="props.applications.length === 0">Заявок нет</div>
                        <div v-else class="applications align-center" v-for="application in props.applications"
                            :key="application.id">
                            <div class="order">
                                <p>{{ formatDate(application.create_date) }}</p>
                            </div>
                            <div>
                                <p>{{ application.full_name }}</p>
                            </div>
                            <div>
                                <p>{{ application.contract_number }}</p>
                            </div>
                            <div>
                                <p>{{ application.condition }}</p>
                            </div>
                            <div>
                                <p>{{ application.status }}</p>
                            </div>
                            <div>
                                <p>{{ application.type_of_processing }}</p>
                            </div>
                            <div>
                                <p>{{ formatDate(application.date_of_payments) }}</p>
                            </div>
                            <div>
                                <p>300 000</p>
                            </div>
                            <div>
                                <p>50 000</p>
                            </div>
                            <div>
                                <Dropdown :options="[
                                    { label: 'Подробная информация', action: 'information' },
                                    { label: 'Изменить статус', action: 'edit' },
                                ]" class="applications_dropdown"
                                    @select="handleDropdownSelect($event, application.id, $event.action)">
                                    <template #trigger>
                                        <Ellipsis />
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        <!-- {{ props.applications }} -->
                    </div>
                </div>
                <div class="card">
                    <header>
                        <h2 class="title-card">Завершенные заявки</h2>
                    </header>
                    <div class="application">
                        <div class="title">Завершённых заявок нет</div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
    <BaseModal v-if="isModalOpen" :isOpen="isModalOpen" :title="modalTitles[currentModal?.action]" @close="closeModal">
        <template #default>
            <div v-if="currentModal.type === 'add'">
                Новая заявка
            </div>
            <div v-else-if="currentModal.type === 'information'">
                information
            </div>
            <div v-else-if="currentModal.type === 'edit'">
                edit
            </div>
        </template>
        <template #footer>
            <div v-if="currentModal.type !== 'information'" class="flex justify-end">
                <button @click="closeModal" class="btn-cancel">Отменить</button>
                <button @click="" v-if="currentModal.type === 'add'" class="btn-save">Создать</button>
                <button @click="" v-else class="btn-save">Сохранить</button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
.application {
    padding: 20px 32px 32px 32px;
}

.main {
    row-gap: 32px;
}

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
    border-bottom: 1px solid #F3F5F6;
    padding: 24px 32px 20px 32px;
}

.thead-application {
    height: 55px;
    display: grid;
    column-gap: 5px;
    /* grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px; */
    grid-template-columns: 1.17fr 1.38fr 1.03fr 0.9fr 0.97fr 1.59fr 1.17fr 0.9fr 0.9fr 0.34fr;
    border-bottom: 1px solid #F3F5F6;
}

.applications {
    padding: 16px 0;
    display: grid;
    column-gap: 5px;
    /* grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px; */
    grid-template-columns: 1.17fr 1.38fr 1.03fr 0.9fr 0.97fr 1.59fr 1.17fr 0.9fr 0.9fr 0.34fr;
    border-bottom: 1px solid #F3F5F6;
}

.thead-application li {
    font-size: 16px;
    font-weight: 600;
    line-height: 23.2px;
    letter-spacing: 0.01em;
    color: #969BA0;
}

.link-btn {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
}

.add_application {
    background: #4E9F7D;
    color: #fff;
    transition: 0.3s;
}

.add_application:hover {
    background: #428569;
}

.order {
    padding-left: 12px;
}

:deep(.applications_dropdown .dropdown-menu) {
    width: 250px;
}

:deep(.applications_dropdown .dropdown-item:last-child) {
    color: #A7ADB2;
    border-top: none;
}

:deep(.applications_dropdown .dropdown-item:last-child p) {
    margin-top: unset;
}

:deep(.applications_dropdown .dropdown-item:nth-child(2) p) {
    margin-bottom: unset;
}

.btn-cancel,
.btn-save {
    font-family: Onest;
    font-size: 14px;
    font-weight: 400;
    line-height: 21px;
    letter-spacing: 0.015em;
    height: 45px;
    padding: 0 20px;
    border-radius: 12px;
}

.btn-save {
    background: #4e9f7d;
    color: #fff;
    transition: 0.3s;
}

.btn-save:hover {
    background: #428569;
}

.btn-cancel {
    margin-right: 10px;
    color: #242424;
    background: #f3f5f6;
    transition: 0.3s;
}

.btn-cancel:hover {
    background: #dfe4e7;
}
</style>
