<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="red">
            {{ message }}
        </p>
    </div>
</template>

<style scoped>
.red {
    color: red;
    margin-top: 5px;
    text-align: start;
}
</style>