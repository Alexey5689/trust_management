<script setup>
import { watch, ref } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close']);

const isVisible = ref(false);

watch(() => props.isOpen,
    (newVal) => {
        isVisible.value = newVal;
    },
    { immediate: true }
);

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <div v-if="isVisible" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h2>{{ title }}</h2>
                <button class="close-btn" @click="closeModal">✖</button>
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

<style>
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
    border-radius: 10px;
    padding: 20px;
    width: 400px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
}

.close-btn {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
}

.modal-body {
    margin: 20px 0;
}
</style>