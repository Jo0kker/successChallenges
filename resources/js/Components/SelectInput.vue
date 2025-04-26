<template>
    <select
        :id="id"
        :value="modelValue"
        class="border-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:bg-gray-800 dark:text-gray-300"
        @input="$emit('update:modelValue', $event.target.value)"
    >
        <slot></slot>
    </select>
</template>

<script setup>
defineProps({
    id: {
        type: String,
        required: true
    },
    modelValue: {
        type: [String, Number],
        required: true
    }
});

defineEmits(['update:modelValue']);
</script>
