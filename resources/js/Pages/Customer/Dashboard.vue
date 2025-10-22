<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';

const props = defineProps({
    customer: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'Dashboard', 'route': '' },
])

</script>

<template>

    <Head title="Customer Dashboard" />

    <AppLayout>

        <template #title>
            <span>Dashboard</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <div class="text-3xl font-bold mb-6">Welcome, {{ customer.name }}!</div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="pi pi-user text-4xl text-blue-600 dark:text-blue-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-blue-900 dark:text-blue-100">Profile</div>
                    <div class="text-blue-700 dark:text-blue-200 mt-2">Manage your account</div>
                </div>

                <Link :href="route('customer:tickets.index')" 
                      class="bg-orange-100 dark:bg-orange-900 p-6 rounded-lg hover:shadow-lg transition-shadow cursor-pointer block">
                    <i class="pi pi-exclamation-triangle text-4xl text-orange-600 dark:text-orange-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-orange-900 dark:text-orange-100">Raise Complaint</div>
                    <div class="text-orange-700 dark:text-orange-200 mt-2">Report issues and request service</div>
                </Link>

                <div class="bg-purple-100 dark:bg-purple-900 p-6 rounded-lg hover:shadow-lg transition-shadow cursor-pointer">
                    <i class="pi pi-cog text-4xl text-purple-600 dark:text-purple-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-purple-900 dark:text-purple-100">Settings</div>
                    <div class="text-purple-700 dark:text-purple-200 mt-2">Configure preferences</div>
                </div>
            </div>

            <div class="mt-8">
                <div class="text-xl font-bold mb-4">Account Information</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Name:</label>
                        <p class="text-lg mt-1">{{ customer.name }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Email:</label>
                        <p class="text-lg mt-1">{{ customer.email }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Phone:</label>
                        <p class="text-lg mt-1">{{ customer.phone }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Status:</label>
                        <p class="text-lg mt-1">
                            <span v-if="customer.status" class="text-green-600 font-semibold">Active</span>
                            <span v-else class="text-red-600 font-semibold">Inactive</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

