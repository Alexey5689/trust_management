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
const selectedOffTime = ref(null);
const selectedPartlyOption = ref(null);

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
                <form>
                    <div class="flex flex-column r-gap">
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="client">Клиент</label>
                                <select id="client">
                                </select>
                            </div>
                            <div class="input flex flex-column">
                                <label for="contract">Договор</label>
                                <select id="contract">
                                </select>
                            </div>
                        </div>
                        <div class="flex" style="column-gap: 8px;">
                            <div class="information_contract">
                                <label>Дата заключения</label>
                                <p>25 Марта 2024</p>
                            </div>
                            <div class="information_contract">
                                <label>Срок договора</label>
                                <p>1 год</p>
                            </div>
                            <div class="information_contract">
                                <label>Ставка</label>
                                <p>20%</p>
                            </div>
                        </div>
                        <div class="flex c-gap">
                            <div class="contract_sum">
                                <label>Основная сумма</label>
                                <p>10 000 000 ₽</p>
                            </div>
                            <div class="contract_sum">
                                <label>Дивиденды</label>
                                <p>200 000 ₽</p>
                            </div>
                        </div>
                    </div>
                    <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Условия списания</p>
                    <div class="radio-buttons flex c-gap">
                        <div class="input flex">
                            <input type="radio" id="off_time" name="off_time" value="1" v-model="selectedOffTime">
                            <label for="off_time" class="button">Раньше срока</label>
                        </div>
                        <div class="input flex">
                            <input type="radio" id="on_time" name="on_time" value="2" v-model="selectedOffTime">
                            <label for="on_time" class="button">В срок</label>
                        </div>
                    </div>
                    <div class="for_off_time" v-if="selectedOffTime === '1'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Вывод средств</p>
                        <div class="flex c-gap">
                            <div class="input flex flex-column">
                                <label for="write_downs">Сумма списания</label>
                                <input type="text" id="write_downs" disabled />
                            </div>
                            <div class="input flex flex-column">
                                <label for="payment_date">Дата планируемой выплаты</label>
                                <input type="date" id="payment_date" />
                            </div>
                        </div>
                        <p class="warning" style="margin-top: 16px;">Комиссия за вывод раньше срока, 30%</p>
                        <p class="warning" style="margin-top: 4px;">3 000 000 ₽</p>
                    </div>
                    <div class="for_on_time" v-if="selectedOffTime === '2'">
                        <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Варианты списания</p>
                        <div class="radio-buttons flex flex-column r-gap">
                            <div class="flex c-gap">
                                <input type="radio" id="partly" name="partly" value="3" v-model="selectedPartlyOption">
                                <label for="partly" class="button">Забрать дивиденды частично</label>
                                <input type="radio" id="wholly" name="wholly" value="4" v-model="selectedPartlyOption">
                                <label for="wholly" class="button">Забрать дивиденды целиком</label>
                            </div>
                            <input type="radio" id="take_everything" name="take_everything" value="5"
                                v-model="selectedPartlyOption">
                            <label for="take_everything" class="button">Забрать дивиденды и сумму</label>
                        </div>
                        <div class="for_partly" v-if="selectedPartlyOption === '3'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="dividends_partly">Дивиденты</label>
                                    <input type="text" id="dividends_partly" />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_partly_date">Дата планируемой выплаты</label>
                                    <input type="date" id="dividends_partly_date" />
                                </div>
                            </div>
                        </div>
                        <div class="for_wholly" v-if="selectedPartlyOption === '4'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="dividends_wholly">Дивиденты</label>
                                    <input type="text" id="dividends_wholly" disabled />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_wholly_date">Дата планируемой выплаты</label>
                                    <input type="date" id="dividends_wholly_date" />
                                </div>
                            </div>
                        </div>
                        <div class="for_take_everything" v-if="selectedPartlyOption === '5'">
                            <p class="c_data" style="margin-top: 32px; margin-bottom: 16px;">Вывод средств</p>
                            <div class="flex c-gap">
                                <div class="input flex flex-column">
                                    <label for="sum_take_everything">Основная сумма</label>
                                    <input type="text" id="sum_take_everything" disabled />
                                </div>
                                <div class="input flex flex-column">
                                    <label for="dividends_take_everything">Дивиденты</label>
                                    <input type="text" id="dividends_take_everything" disabled />
                                </div>
                            </div>
                            <div class="input flex flex-column" style="margin-top: 16px;">
                                <label for="dividends_take_everything_date">Дата планируемой выплаты</label>
                                <input type="date" id="dividends_take_everything_date" />
                            </div>
                        </div>
                    </div>
                </form>
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
    width: 1606px;
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
    padding-bottom: 30px;
    background: #fff;
    border-radius: 32px;
    -webkit-box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    box-shadow: 0px 0px 4px 0px #5c5c5c0a;
    -webkit-box-shadow: 0px 0px 8px 0px #5c5c5c14;
    box-shadow: 0px 0px 8px 0px #5c5c5c14;
    -webkit-box-shadow: 0px 4px 12px 0px #5c5c5c14;
    box-shadow: 0px 4px 12px 0px #5c5c5c14;
    overflow-x: auto;
    scrollbar-width: none;
    scrollbar-color: transparent transparent;
}

.card::-webkit-scrollbar {
    width: 0px;
}

.card::-webkit-scrollbar-thumb {
    background: transparent;
}

.title-card {
    color: #000;
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
    border-bottom: 1px solid #F3F5F6;
    padding: 24px 32px 20px 32px;
    width: 1606px;
}

.thead-application {
    height: 55px;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px;
    border-bottom: 1px solid #F3F5F6;
}

.applications {
    padding: 16px 0;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 170px 200px 150px 130px 140px 230px 170px 130px 130px 50px;
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

:deep(.modal-content) {
    width: 500px;
}

.input {
    width: 100%;
    row-gap: 8px;
}

.input label,
.warning,
.information_contract label,
.information_contract p {
    color: #969ba0;
}

.input input,
.input select {
    border: 1px solid #e8eaeb;
    height: 45px;
    border-radius: 12px;
    width: 100%;
    padding: 0 20px;
}

.c_data {
    font-weight: 500;
}

.information_contract {
    background: #F3F5F6;
    padding: 16px 26px;
    border-radius: 24px;
}

.contract_sum {
    width: 100%;
    background: #F3F5F6;
    border-radius: 24px;
    padding: 16px 20px;
}

.radio-buttons input[type="radio"] {
    display: none;
}

.radio-buttons .button {
    width: 100%;
    display: inline-block;
    padding: 16px 20px;
    /* margin: 5px; */
    border-radius: 24px;
    background: #F3F5F6;
    color: #969BA0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.radio-buttons .button:hover {
    background-color: #4E9F7D1A;
    color: #4E9F7D;
}

.radio-buttons input[type="radio"]:checked+.button {
    background: #4E9F7D1A;
    color: #4E9F7D;
}

#write_downs {
    background: #F3F5F6;
    color: #969BA0;
}
</style>
