<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { formatDate } from '@/helpers.js';

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
</script>
<template>

    <Head title="Applications" />
    <AuthenticatedLayout :userRole="role">
        <template #header>
            <div class="flex align-center justify-between title">
                <h2>Заявки</h2>
                <ResponsiveNavLink class="add_application" v-if="role === 'admin' || role === 'manager'"
                    :href="route('add.application')">
                    Добавить заявку
                </ResponsiveNavLink>
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
                            <!-- <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        {{ $page.props.auth.user.name }}

                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>

<template #content>
                                <DropdownLink as="button" :href="route('show.application', {
                                    application: application.id,
                                })
                                    ">
                                    Подробная информация
                                </DropdownLink>
                                <DropdownLink v-if="role === 'admin'" :href="route('change.status.application', {
                                    application: application.id,
                                })
                                    " as="button">
                                    Изменмть статус
                                </DropdownLink>
                            </template>
</Dropdown> -->
                        </div>
                        <!-- {{ props.applications }} -->
                    </div>
                </div>
                <div class="card">
                    <header>
                        <h2 class="title-card">Завершенные заявки</h2>
                    </header>
                    <div class="application">
                        <div class="title">Нет завершённых заявок</div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
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
    grid-template-columns: 0.18fr 0.27fr 0.12fr 0.15fr 0.15fr 0.2fr 0.17fr;
    border-bottom: 1px solid #F3F5F6;
}

.applications {
    padding: 16px 0;
    display: grid;
    column-gap: 5px;
    grid-template-columns: 0.18fr 0.27fr 0.12fr 0.15fr 0.15fr 0.2fr 0.17fr;
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
}

.add_application {
    background: #4E9F7D;
    color: #fff;
    margin-right: 16px;
    transition: 0.3s;
}

.add_application:hover {
    background: #428569;
}

.order {
    padding-left: 12px;
}
</style>
