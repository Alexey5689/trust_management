<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="label">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<style scoped>
.label {
    color: #969BA0;
    font-size: 14px;
    line-height: 21px;
    margin-bottom: 8px;
    display: block;
}
</style>