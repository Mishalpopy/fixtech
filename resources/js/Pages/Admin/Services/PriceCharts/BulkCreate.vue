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
    { 'value': 'Bulk Create Price Charts', 'route': '' },
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
    price_charts: [
        {
            time_duration: '',
            current_price: null,
            original_price: null,
            is_urgent: false,
            order: 0,
            status: true
        }
    ]
});

function addPriceChart() {
    form.price_charts.push({
        time_duration: '',
        current_price: null,
        original_price: null,
        is_urgent: false,
        order: form.price_charts.length,
        status: true
    });
}

function removePriceChart(index) {
    if (form.price_charts.length > 1) {
        form.price_charts.splice(index, 1);
        // Update order numbers
        form.price_charts.forEach((chart, idx) => {
            chart.order = idx;
        });
    }
}

function save() {
    // Remove empty price charts
    const validCharts = form.price_charts.filter(chart => 
        chart.time_duration.trim() !== '' && chart.current_price !== null
    );
    
    if (validCharts.length === 0) {
        alert('Please add at least one price chart with time duration and current price');
        return;
    }

    loading.value = true;
    form.price_charts = validCharts;
    
    form.post(route('admin:price-charts.bulk.store'), {
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
    <Head title="Bulk Create Price Charts" />
    <AppLayout>
        <template #title>
            <span>Bulk Create Price Charts</span>
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

            <div class="mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Price Charts</h3>
                    <Button label="Add Price Chart" icon="pi pi-plus" @click="addPriceChart()" severity="success" />
                </div>

                <div v-for="(chart, index) in form.price_charts" :key="index" class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-700">Price Chart {{ index + 1 }}</h4>
                        <Button 
                            v-if="form.price_charts.length > 1"
                            icon="pi pi-trash" 
                            severity="danger" 
                            size="small" 
                            outlined
                            @click="removePriceChart(index)" 
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label :for="`time_duration_${index}`" class="block font-bold mb-1">Time Duration *</label>
                            <Dropdown 
                                :id="`time_duration_${index}`" 
                                v-model="chart.time_duration" 
                                :options="timeDurationOptions"
                                placeholder="Select Time Duration"
                                :invalid="form.errors[`price_charts.${index}.time_duration`]" 
                                fluid 
                            />
                            <small v-if="form.errors[`price_charts.${index}.time_duration`]" class="text-red-500">
                                {{ form.errors[`price_charts.${index}.time_duration`] }}
                            </small>
                        </div>

                        <div>
                            <label :for="`current_price_${index}`" class="block font-bold mb-1">Current Price (AED) *</label>
                            <InputText 
                                :id="`current_price_${index}`" 
                                type="number" 
                                v-model.number="chart.current_price" 
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :invalid="form.errors[`price_charts.${index}.current_price`]" 
                                fluid 
                            />
                            <small v-if="form.errors[`price_charts.${index}.current_price`]" class="text-red-500">
                                {{ form.errors[`price_charts.${index}.current_price`] }}
                            </small>
                        </div>

                        <div>
                            <label :for="`original_price_${index}`" class="block font-bold mb-1">Original Price (AED) - Optional</label>
                            <InputText 
                                :id="`original_price_${index}`" 
                                type="number" 
                                v-model.number="chart.original_price" 
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :invalid="form.errors[`price_charts.${index}.original_price`]" 
                                fluid 
                            />
                            <small class="text-gray-500">This will be shown as struck-through price</small>
                            <small v-if="form.errors[`price_charts.${index}.original_price`]" class="text-red-500">
                                {{ form.errors[`price_charts.${index}.original_price`] }}
                            </small>
                        </div>

                        <div class="flex items-center">
                            <Checkbox 
                                :id="`is_urgent_${index}`" 
                                v-model="chart.is_urgent" 
                                :binary="true"
                            />
                            <label :for="`is_urgent_${index}`" class="ml-2 font-bold">Urgent Required</label>
                        </div>
                    </div>
                </div>

                <div v-if="form.price_charts.length === 0" class="text-center py-8 text-gray-500">
                    <p>No price charts added. Click "Add Price Chart" to start.</p>
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-2">
                <Button label="Cancel" severity="secondary" outlined @click="router.get(route('admin:price-charts.index'))" />
                <Button label="Save All Price Charts" icon="pi pi-save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

