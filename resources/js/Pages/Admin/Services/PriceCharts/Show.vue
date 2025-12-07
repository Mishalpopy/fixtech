<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const props = defineProps({
    priceChart: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Price Charts', 'route': route('admin:price-charts.index') },
    { 'value': 'Price Chart Details', 'route': '' },
]);

function editPriceChart() {
    router.get(route('admin:price-charts.edit', [props.priceChart.id]));
}
</script>

<template>
    <Head title="Price Chart Details" />
    <AppLayout>
        <template #title>
            <span>Price Chart Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex justify-end gap-2 mb-4">
                <Button label="Edit" icon="pi pi-pencil" @click="editPriceChart()" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Service</label>
                    <p class="text-gray-700">{{ priceChart.sub_service?.service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Sub Service</label>
                    <p class="text-gray-700">{{ priceChart.sub_service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Time Duration</label>
                    <p class="text-gray-700">{{ priceChart.time_duration }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Current Price</label>
                    <p class="text-gray-700 font-bold text-green-600 text-lg">
                        AED {{ parseFloat(priceChart.current_price).toFixed(2) }}
                    </p>
                </div>
                <div v-if="priceChart.original_price">
                    <label class="block font-bold mb-1">Original Price</label>
                    <p class="text-gray-400 line-through text-lg">
                        AED {{ parseFloat(priceChart.original_price).toFixed(2) }}
                    </p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Urgent</label>
                    <p class="text-gray-700">
                        <Tag v-if="priceChart.is_urgent" value="Urgent" severity="danger" />
                        <span v-else>No</span>
                    </p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Order</label>
                    <p class="text-gray-700">{{ priceChart.order }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <p class="text-gray-700">
                        <span :class="priceChart.status ? 'text-green-600' : 'text-red-600'">
                            {{ priceChart.status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Created At</label>
                    <p class="text-gray-700">{{ priceChart.formatted_created_at }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

