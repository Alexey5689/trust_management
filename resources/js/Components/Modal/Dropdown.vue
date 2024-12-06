<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    position: {
        type: String,
        default: 'right', // 'left' или 'right' для начальной позиции
    },
});

const emit = defineEmits(['select']);
const isOpen = ref(false);
const dropdownRef = ref(null);
const buttonRef = ref(null);
const dropdownPosition = ref(props.position); // Используем props.position как начальное значение

const toggleDropdown = async () => {
    if (!isOpen.value) {
        closeAllDropdowns(); // Закрыть все другие Dropdown
    }
    isOpen.value = !isOpen.value;

    if (isOpen.value) {
        await nextTick(); // Подождать, пока элемент появится
        adjustDropdownPosition();
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target) &&
        buttonRef.value &&
        !buttonRef.value.contains(event.target)
    ) {
        closeDropdown();
    }
};

// Отправить событие закрытия всех Dropdown
const closeAllDropdowns = () => {
    document.dispatchEvent(new CustomEvent('close-all-dropdowns'));
};

const handleCloseAllDropdowns = () => {
    closeDropdown();
};

// Динамическая логика для смены позиции
const adjustDropdownPosition = () => {
    const dropdownEl = dropdownRef.value;
    const buttonEl = buttonRef.value;

    if (dropdownEl && buttonEl) {
        const dropdownRect = dropdownEl.getBoundingClientRect();
        const buttonRect = buttonEl.getBoundingClientRect();

        // Проверяем, если справа не хватает места, то переключаем позицию влево
        if (dropdownRect.right > window.innerWidth) {
            dropdownPosition.value = 'left';
        } else if (dropdownRect.left < 0) {
            dropdownPosition.value = 'right';
        }
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('close-all-dropdowns', handleCloseAllDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('close-all-dropdowns', handleCloseAllDropdowns);
});
</script>

<template>
    <div class="relative">
        <!-- Кнопка триггера -->
        <button ref="buttonRef" class="ellipsis-btn" @click="toggleDropdown">
            <slot name="trigger" />
        </button>

        <!-- Выпадающее меню -->
        <div v-if="isOpen" ref="dropdownRef" class="dropdown-menu" :class="dropdownPosition">
            <ul>
                <li v-for="(option, index) in options" :key="index" @click="$emit('select', option); closeDropdown()"
                    class="dropdown-item">
                    <p>{{ option.label }}</p>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.relative {
    position: relative;
}

 .ellipsis-btn {
    border-radius: 100%;
    height: 28px;
    width: 28px;
    background: #4E9F7D1A;
    transition: 0.3s;
}

.ellipsis-btn:hover {
    background: #d3e9e0
}

.dropdown-menu {
    position: absolute;
    top: 0;
    background: white;
    border: 1px solid #F3F5F6;
    border-radius: 24px;
    z-index: 1000;
    width: 200px;
    padding: 14px 0;
    box-shadow: 0px 0px 4px 0px #5C5C5C0A;
    box-shadow: 0px 0px 8px 0px #5C5C5C14;
    box-shadow: 0px 4px 12px 0px #5C5C5C14;
}

.dropdown-menu.right {
    right: 0;
}

.dropdown-menu.left {
    left: 0;
}

.dropdown-item {
    color: #A7ADB2;
    cursor: pointer;
}

.dropdown-item:last-child {
    color: #F5768D;
    border-top: 1px solid #F3F5F6;;
}

.dropdown-item p {
    height: 37px;
    display: flex;
    align-items: center;
    padding: 0 32px;
}

.dropdown-item:nth-child(2) p {
    margin-bottom: 8px;
}

.dropdown-item:last-child p {
    margin-top: 8px;
}

</style>
