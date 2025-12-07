<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    subService: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Sub Services', 'route': route('admin:sub-services.index') },
    { 'value': 'Sub Service Details', 'route': '' },
]);

function editSubService() {
    router.get(route('admin:sub-services.edit', [props.subService.id]));
}

function viewServiceItems() {
    router.get(route('admin:service-items.index', { sub_service_id: props.subService.id }));
}

function viewProcesses() {
    router.get(route('admin:processes.index', { sub_service_id: props.subService.id }));
}

function viewPriceCharts() {
    router.get(route('admin:price-charts.index', { sub_service_id: props.subService.id }));
}

function viewFaqs() {
    router.get(route('admin:faqs.index', { sub_service_id: props.subService.id }));
}
</script>

<template>
    <Head title="Sub Service Details" />
    <AppLayout>
        <template #title>
            <span>Sub Service Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex justify-end gap-2 mb-4">
                <Button label="View Items" icon="pi pi-list" @click="viewServiceItems()" />
                <Button label="View Processes" icon="pi pi-cog" @click="viewProcesses()" />
                <Button label="View Price Charts" icon="pi pi-dollar" @click="viewPriceCharts()" />
                <Button label="View FAQs" icon="pi pi-question-circle" @click="viewFaqs()" />
                <Button label="Edit" icon="pi pi-pencil" @click="editSubService()" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Service</label>
                    <p class="text-gray-700">{{ subService.service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Name</label>
                    <p class="text-gray-700">{{ subService.name }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <p class="text-gray-700">
                        <span :class="subService.status ? 'text-green-600' : 'text-red-600'">
                            {{ subService.status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold mb-1">Description</label>
                    <p class="text-gray-700">{{ subService.description || 'N/A' }}</p>
                </div>
                <div v-if="subService.image" class="md:col-span-2">
                    <label class="block font-bold mb-1">Image</label>
                    <img :src="`/storage/${subService.image}`" alt="Sub Service Image" class="w-64 h-64 object-cover rounded border" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Created At</label>
                    <p class="text-gray-700">{{ subService.formatted_created_at }}</p>
                </div>
            </div>
            <div v-if="subService.service_items && subService.service_items.length > 0" class="mt-6">
                <h3 class="text-lg font-bold mb-4">Service Items ({{ subService.service_items.length }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card v-for="item in subService.service_items" :key="item.id" class="p-4">
                        <template #content>
                            <div class="text-center">
                                <img v-if="item.image" :src="`/storage/${item.image}`" alt="Item Image" 
                                    class="w-full h-32 object-cover rounded mb-2" />
                                <h4 class="font-bold">{{ item.name }}</h4>
                                <p class="text-sm text-gray-600 mt-2">{{ item.description || 'No description' }}</p>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
            <div v-if="subService.processes && subService.processes.length > 0" class="mt-6">
                <h3 class="text-lg font-bold mb-4">Processes ({{ subService.processes.length }})</h3>
                <div class="space-y-4">
                    <Card v-for="process in subService.processes" :key="process.id" class="p-4">
                        <template #content>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600">
                                    {{ process.order }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-lg">{{ process.title }}</h4>
                                    <p class="text-gray-600 mt-2">{{ process.description || 'No description' }}</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
            <div v-if="subService.price_charts && subService.price_charts.length > 0" class="mt-6">
                <h3 class="text-lg font-bold mb-4">Price Charts ({{ subService.price_charts.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-3 text-left font-bold">Time Duration</th>
                                <th class="border p-3 text-left font-bold">Current Price</th>
                                <th class="border p-3 text-left font-bold">Original Price</th>
                                <th class="border p-3 text-left font-bold">Urgent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="chart in subService.price_charts" :key="chart.id" class="hover:bg-gray-50">
                                <td class="border p-3">{{ chart.time_duration }}</td>
                                <td class="border p-3">
                                    <span class="font-bold text-green-600">AED {{ parseFloat(chart.current_price).toFixed(2) }}</span>
                                </td>
                                <td class="border p-3">
                                    <span v-if="chart.original_price" class="text-gray-400 line-through">
                                        AED {{ parseFloat(chart.original_price).toFixed(2) }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="border p-3">
                                    <Tag v-if="chart.is_urgent" value="Urgent" severity="danger" />
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="subService.faqs && subService.faqs.length > 0" class="mt-6">
                <h3 class="text-lg font-bold mb-4">FAQs ({{ subService.faqs.length }})</h3>
                <div class="space-y-4">
                    <Card v-for="faq in subService.faqs" :key="faq.id" class="p-4">
                        <template #content>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center font-bold text-yellow-600">
                                    Q
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-lg mb-2">{{ faq.title }}</h4>
                                    <p class="text-gray-600 whitespace-pre-wrap">{{ faq.description }}</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

