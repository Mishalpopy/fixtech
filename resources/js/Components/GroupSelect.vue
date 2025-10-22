<script setup>
import { Select } from 'primevue';
import { ref, useSlots } from 'vue';
// import { trans } from 'laravel-vue-i18n'

const slots = useSlots()
const props = defineProps({
    modelValue: {


    },
    options: {
        type: Object,
    },

    validation: {
        type: Boolean
    },
    optionLabel: {
        type: String
    },
    optionGroupLabel: {
        type: String
    },
    valueField: {
        type: String
    },

    disabled: {
        type: Boolean
    },

    allOption: {
        type: Boolean
    },
    showSelectOption: {
        type: Boolean
    },
    validationFailed: {
        type: Boolean
    },
    showNullValue: {
        type: Boolean
    },

    selectedGroupLabel: {
        type: String
    },

    groupLabel: {
        type: String
    },

    selectedItems: {
        type: Object
    },

    allItems: {
        type: Object
    },

    optionGroupChildren: {
        type: String
    },

    placeholder: {
        type: String
    },
    filter:{
        type: Boolean
    }


});





const items = ref([
    {
        label: props.selectedGroupLabel,
        code: 'IFS',
        items: props.selectedItems ?? 'No Items Found'
    },
    {
        label: props.groupLabel,
        code: 'AI',
        items: props.allItems ?? 'No Items Found'
    },

]);



const emit = defineEmits(['update:modelValue', 'change']);
const input = ref(null);
const classes = ref(' border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500');
const errorClasses = ref('bg-red-50 border border-red-500 text-red-900 placeholder-red-700 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-red-100 dark:border-red-400');

function change(event) {

    emit('update:modelValue', event.target.value)
    emit('change', event.target.value)
}

function setLabel(item) {
    if (props.translationPath) {
        if (props.label) return trans(`${props.translationPath}.${item[props.label]}`)
        else return trans(`${props.translationPath}.${item.name}`) ?? trans(`${props.translationPath}.${item.label}`)
    } else {

        if (props.label) return item[props.label]
        else return item.name ?? item.label
    }
}

function setValue(item) {
    if (props.valueField) return item[props.valueField]
    else return item.id ?? item.value
}

</script>

<template>

    <!-- <select id="type" :value="modelValue" @change="change($event)" ref="input" :disabled="disabled"
    :class="validationFailed?errorClasses:classes">
        <option v-if="allOption" value="all" selected>All</option>
        <option v-if="showSelectOption" selected disabled value="">Select</option>
        <option v-if="showNullValue" value=""></option>
        <option v-for="(item, item_index) in options" :value=" setValue(item)" :key="item_index"
            :disabled="disabled ">{{setLabel(item)}}</option>
    </select> -->

    <Select :value="modelValue" :options="items" :optionLabel="props.optionLabel" :filter="filter"  @change="change($event)"
        :optionGroupLabel="props.optionGroupLabel" optionGroupChildren="items"
        :placeholder="props.placeholder" class="w-full md:w-56">
        <template #optiongroup="{ option, option_index }">

            <div class="flex items-center">
                <div class="text-sm text-black" v-if="option.code == 'IFS'">{{ option.label }}</div>
                <div class="text-sm text-black" v-if="option.code == 'AI'">{{ option.label }}</div>
            </div>
            <div class="flex justify-center">
                {{ !option.items.length ? 'No Resource Found' : '' }}
            </div>
        </template>
    </Select>

</template>
