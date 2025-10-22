<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,

});

const input = ref(null);

const props = defineProps({
   
    placeholder: {
        type: String
    },
    disabled: {
        type: String
    },
    required: {
        type: String
    },
    readonly: {
        type: String
    },
    rows: {
        type: String
    },
    validationFailed: {
        type: Boolean
    },
   

});

const classes = 'border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500';
const validationClasses = 'bg-red-50 border border-red-500 text-red-900 placeholder-red-700 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-red-100 dark:border-red-400'



defineExpose({ focus: () => input.value.focus() });
</script>

<template>

    <textarea  v-model="model" id="aaa" :rows="rows" :class="validationFailed?validationClasses:classes"  :required="required"  :placeholder="placeholder" :disabled="disabled"></textarea>
</template>
