<script setup>
import { watch, ref, onMounted } from 'vue';
import Close from '@/Components/Icon/Close.vue';
import { fetchData } from '@/helpers';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    url: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close']);

const isVisible = ref(false);
const error = ref(null);

watch(() => props.isOpen, (newVal) => {
    isVisible.value = newVal;
}, { immediate: true },);

onMounted(async () => {
    try {
        const data = await fetchData(props.url); // Ожидаем завершения запроса
        emit('response', data.user ? data.user : data);
    } catch (err) {
        error.value = err; // Сохраняем ошибку
    } finally {
    }
});

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <div v-if="isVisible" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h2 class="title-2">{{ title }}</h2>
                <button class="close-btn" @click="closeModal">
                    <Close />
                </button>
            </div>
            <div class="modal-body">
                <slot></slot>
            </div>
            <div class="modal-footer">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 32px;
    padding: 32px;
    width: 600px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    margin: 32px 0 24px 0;
}

.title-2 {
    font-size: 20px;
    font-weight: 600;
    line-height: 29px;
}

.close-btn {
    height: 32px;
    width: 32px;
    background: #f3f5f6;
    border-radius: 100%;
    transition: 0.3s;
    cursor: pointer;
}

.close-btn:hover {
    background: #dfe4e7;
}
</style>
