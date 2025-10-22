<template>
    <div>

        <DatePicker :id="id" v-model="date" :showTime="showTime"
            :selectionMode="props.multiple == true ? 'multiple' : 'single'" :hourFormat="hourFormat" :dateFormat="dateFormat"  fluid :maxDate="maxDate"
            @value-change="handleDateSelect" :invalid="invalid" />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { DatePicker } from 'primevue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    id: {
        type: String,
        default: ''
    },
    maxDate: {
        type: String,
        default: null
    },
    showTime: {
        type: Boolean,
        default: false
    },
    invalid: {
        type: Boolean,
        default: false
    },
    dateFormat: {
        type: String,
        default: 'dd-mm-yy'
    },
    multiple: {
        type: Boolean,
        default: false
    },
    hourFormat: {
        type: String,
        default: '24' // Default to 12-hour format
    },

});

const emit = defineEmits(['update:modelValue']);

const date = ref(props.modelValue ? (props.multiple ? props.modelValue.map(el=>new Date(el)) : new Date(props.modelValue)) : null);

const lastFormatedDate = ref(null);

const handleDateSelect = (selectedDate) => {

    if (!props.multiple) {
        const formattedDate = formatLocalDate(selectedDate);
        lastFormatedDate.value = formattedDate;
        emit('update:modelValue', formattedDate);
    } else {
        if (selectedDate) {
            let dates = [];
            selectedDate.forEach(date => {
                const formattedDate = formatLocalDate(date);
                dates.push(formattedDate);
            });
            lastFormatedDate.value = dates;
            emit('update:modelValue', dates);
        }
    }
};

const formatLocalDate = (date) => {
    if (!date) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};



watch(() => props.modelValue, (newValue) => {
    if (newValue != lastFormatedDate.value) {
        if(props.multiple){
            date.value = newValue ? newValue.map(el=>new Date(el)) : null;
        }else{
            date.value = newValue ? new Date(newValue) : null;
        }
        
    }

});
</script>

<style scoped>
/* Add any custom styles here */
</style>