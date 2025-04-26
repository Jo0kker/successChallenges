<template>
    <textarea
        :id="id"
        :value="modelValue"
        class="mt-1 block w-full border-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:bg-gray-800 dark:text-gray-300"
        :class="{ 'border-red-500 dark:border-red-500': error }"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
    ></textarea>
</template>

<script setup>
defineProps({
    id: {
        type: String,
        required: true
    },
    modelValue: {
        type: String,
        default: ''
    },
    error: {
        type: Boolean,
        default: false
    }
});

defineEmits(['update:modelValue']);
</script>
