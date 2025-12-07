<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';

const props = defineProps({
    serviceItem: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Service Items', 'route': route('admin:service-items.index') },
    { 'value': 'Service Item Details', 'route': '' },
]);

function editServiceItem() {
    router.get(route('admin:service-items.edit', [props.serviceItem.id]));
}
</script>

<template>
    <Head title="Service Item Details" />
    <AppLayout>
        <template #title>
            <span>Service Item Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex justify-end gap-2 mb-4">
                <Button label="Edit" icon="pi pi-pencil" @click="editServiceItem()" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Service</label>
                    <p class="text-gray-700">{{ serviceItem.sub_service?.service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Sub Service</label>
                    <p class="text-gray-700">{{ serviceItem.sub_service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Name</label>
                    <p class="text-gray-700">{{ serviceItem.name }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Price</label>
                    <p class="text-gray-700">{{ serviceItem.price ? `AED ${parseFloat(serviceItem.price).toFixed(2)}` : 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <p class="text-gray-700">
                        <span :class="serviceItem.status ? 'text-green-600' : 'text-red-600'">
                            {{ serviceItem.status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold mb-1">Description</label>
                    <p class="text-gray-700">{{ serviceItem.description || 'N/A' }}</p>
                </div>
                <div v-if="serviceItem.image" class="md:col-span-2">
                    <label class="block font-bold mb-1">Image</label>
                    <img :src="`/storage/${serviceItem.image}`" alt="Service Item Image" class="w-64 h-64 object-cover rounded border" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Created At</label>
                    <p class="text-gray-700">{{ serviceItem.formatted_created_at }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

