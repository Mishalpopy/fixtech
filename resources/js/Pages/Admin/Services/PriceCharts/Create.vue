<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Dropdown, Checkbox } from 'primevue';

const props = defineProps({
    subServices: Array,
    subServiceId: [Number, String]
});

const loading = ref(false);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Price Charts', 'route': route('admin:price-charts.index') },
    { 'value': 'Create Price Chart', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

// Predefined time duration options based on Figma design
const timeDurationOptions = [
    'First 1 Hr',
    'Upto 1.5 Hrs',
    'Upto 2 Hrs',
    'Upto 2.5 Hrs',
    'Upto 3 Hrs',
    'Upto 3.5 Hrs',
    'Upto 4 Hrs'
];

const form = useForm({
    sub_service_id: props.subServiceId || null,
    time_duration: '',
    current_price: null,
    original_price: null,
    is_urgent: false,
    order: 0,
    status: true
});

function save() {
    loading.value = true;
    form.post(route('admin:price-charts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:price-charts.index', { sub_service_id: form.sub_service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Create Price Chart" />
    <AppLayout>
        <template #title>
            <span>Create Price Chart</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="sub_service_id" class="block font-bold mb-1">Sub Service *</label>
                    <Dropdown id="sub_service_id" v-model="form.sub_service_id" :options="subServiceOptions" optionLabel="label" 
                        optionValue="value" placeholder="Select Sub Service" :invalid="form.errors.sub_service_id" fluid />
                    <small v-if="form.errors.sub_service_id" class="text-red-500">{{ form.errors.sub_service_id }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="time_duration" class="block font-bold mb-1">Time Duration *</label>
                    <Dropdown id="time_duration" v-model="form.time_duration" :options="timeDurationOptions"
                        placeholder="Select Time Duration" :invalid="form.errors.time_duration" fluid />
                    <small v-if="form.errors.time_duration" class="text-red-500">{{ form.errors.time_duration }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="current_price" class="block font-bold mb-1">Current Price (AED) *</label>
                    <InputText id="current_price" type="number" v-model.number="form.current_price" step="0.01" min="0"
                        placeholder="0.00" :invalid="form.errors.current_price" fluid />
                    <small v-if="form.errors.current_price" class="text-red-500">{{ form.errors.current_price }}</small>
                </div>
                <div class="w-full">
                    <label for="original_price" class="block font-bold mb-1">Original Price (AED) - Optional</label>
                    <InputText id="original_price" type="number" v-model.number="form.original_price" step="0.01" min="0"
                        placeholder="0.00" :invalid="form.errors.original_price" fluid />
                    <small class="text-gray-500">This will be shown as struck-through price</small>
                    <small v-if="form.errors.original_price" class="text-red-500">{{ form.errors.original_price }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="order" class="block font-bold mb-1">Order</label>
                    <InputText id="order" type="number" v-model.number="form.order" min="0"
                        :invalid="form.errors.order" fluid />
                    <small v-if="form.errors.order" class="text-red-500">{{ form.errors.order }}</small>
                    <small class="text-gray-500">Lower numbers appear first</small>
                </div>
                <div class="w-full flex items-center">
                    <Checkbox id="is_urgent" v-model="form.is_urgent" :binary="true" />
                    <label for="is_urgent" class="ml-2 font-bold">Urgent Required</label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <Button label="Save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

