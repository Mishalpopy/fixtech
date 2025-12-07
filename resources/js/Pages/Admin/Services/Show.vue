<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';

const props = defineProps({
    service: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Service Details', 'route': '' },
]);

function editService() {
    router.get(route('admin:services.edit', [props.service.id]));
}

function viewSubServices() {
    router.get(route('admin:sub-services.index', { service_id: props.service.id }));
}
</script>

<template>
    <Head title="Service Details" />
    <AppLayout>
        <template #title>
            <span>Service Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex justify-end gap-2 mb-4">
                <Button label="View Sub Services" icon="pi pi-list" @click="viewSubServices()" />
                <Button label="Edit" icon="pi pi-pencil" @click="editService()" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Name</label>
                    <p class="text-gray-700">{{ service.name }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <p class="text-gray-700">
                        <span :class="service.status ? 'text-green-600' : 'text-red-600'">
                            {{ service.status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold mb-1">Description</label>
                    <p class="text-gray-700">{{ service.description || 'N/A' }}</p>
                </div>
                <div v-if="service.image" class="md:col-span-2">
                    <label class="block font-bold mb-1">Image</label>
                    <img :src="`/storage/${service.image}`" alt="Service Image" class="w-64 h-64 object-cover rounded border" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Created At</label>
                    <p class="text-gray-700">{{ service.formatted_created_at }}</p>
                </div>
            </div>
            <div v-if="service.sub_services && service.sub_services.length > 0" class="mt-6">
                <h3 class="text-lg font-bold mb-4">Sub Services ({{ service.sub_services.length }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card v-for="subService in service.sub_services" :key="subService.id" class="p-4">
                        <template #content>
                            <div class="text-center">
                                <img v-if="subService.image" :src="`/storage/${subService.image}`" alt="Sub Service Image" 
                                    class="w-full h-32 object-cover rounded mb-2" />
                                <h4 class="font-bold">{{ subService.name }}</h4>
                                <p class="text-sm text-gray-600 mt-2">{{ subService.description || 'No description' }}</p>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

