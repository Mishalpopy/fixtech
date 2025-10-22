<script setup>

import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button } from 'primevue';

const props = defineProps({
    customer: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Customers', 'route': route('admin:customers.index') },
    { 'value': 'Customer Details', 'route': '' },
])

function goBack() {
    router.get(route('admin:customers.index'));
}

function editCustomer() {
    router.get(route('admin:customers.edit', [props.customer.id]));
}

</script>

<template>

    <Head title="Customer Details" />

    <AppLayout>

        <template #title>
            <span>Customer Details</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Name</label>
                    <p class="text-lg">{{ customer.name }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Email</label>
                    <p class="text-lg">{{ customer.email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Phone</label>
                    <p class="text-lg">{{ customer.phone }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Status</label>
                    <p class="text-lg">
                        <span v-if="customer.status" class="text-green-600 font-semibold">Active</span>
                        <span v-else class="text-red-600 font-semibold">Inactive</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Email Verified</label>
                    <p class="text-lg">
                        <span v-if="customer.email_verified_at" class="text-green-600">{{ customer.email_verified_at }}</span>
                        <span v-else class="text-orange-600">Not Verified</span>
                    </p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Created Date</label>
                    <p class="text-lg">{{ customer.formatted_created_at }}</p>
                </div>
            </div>

            <div class="mt-4">
                <label class="block font-bold mb-1 text-gray-600">Address</label>
                <p class="text-lg">{{ customer.address }}</p>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <Button label="Back" severity="secondary" @click="goBack()" />
                <Button label="Edit" icon="pi pi-pencil" @click="editCustomer()" />
            </div>

        </div>
    </AppLayout>
</template>

