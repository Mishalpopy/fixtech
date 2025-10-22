<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Partner/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, Tag } from 'primevue';

const props = defineProps({
    partner: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('partner:dashboard') },
    { 'value': 'Dashboard', 'route': '' },
]);

function viewAttachment(attachment) {
    if (attachment && attachment.file_url) {
        window.open(attachment.file_url, '_blank');
    }
}

</script>

<template>
    <Head title="Partner Dashboard" />

    <AppLayout>
        <template #title>
            <span>Dashboard</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <div class="flex justify-between items-center mb-6">
                <div class="text-3xl font-bold">Welcome, {{ partner.name }}!</div>
                <Tag v-if="partner.approval_status === 'approved'" severity="success" value="Approved" />
                <Tag v-else-if="partner.approval_status === 'pending'" severity="warning" value="Pending Approval" />
                <Tag v-else severity="danger" value="Rejected" />
            </div>
            
            <div v-if="partner.approval_status === 'pending'" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex items-center">
                    <i class="pi pi-info-circle text-yellow-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Account Pending Approval</p>
                        <p class="text-yellow-700">Your account is currently under review. You will be notified once approved.</p>
                    </div>
                </div>
            </div>

            <div v-if="partner.approval_status === 'rejected'" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex items-center">
                    <i class="pi pi-times-circle text-red-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-red-800">Account Rejected</p>
                        <p class="text-red-700">{{ partner.rejection_reason }}</p>
                        <p class="text-red-600 text-sm mt-2">Please contact support for more information.</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a :href="route('partner:profile')" class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors cursor-pointer">
                    <i class="pi pi-user text-4xl text-blue-600 dark:text-blue-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-blue-900 dark:text-blue-100">Profile</div>
                    <div class="text-blue-700 dark:text-blue-200 mt-2">Complete your profile and upload documents</div>
                </a>

                <a :href="route('partner:profile')" class="bg-green-100 dark:bg-green-900 p-6 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors cursor-pointer">
                    <i class="pi pi-file text-4xl text-green-600 dark:text-green-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-green-900 dark:text-green-100">Documents</div>
                    <div class="text-green-700 dark:text-green-200 mt-2">Upload required documents</div>
                </a>

                <div class="bg-purple-100 dark:bg-purple-900 p-6 rounded-lg">
                    <i class="pi pi-cog text-4xl text-purple-600 dark:text-purple-300 mb-3"></i>
                    <div class="text-2xl font-semibold text-purple-900 dark:text-purple-100">Settings</div>
                    <div class="text-purple-700 dark:text-purple-200 mt-2">Configure your account</div>
                </div>
            </div>

            <div class="mt-8">
                <div class="text-xl font-bold mb-4">Account Information</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Name:</label>
                        <p class="text-lg mt-1">{{ partner.name }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Email:</label>
                        <p class="text-lg mt-1">{{ partner.email }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Phone:</label>
                        <p class="text-lg mt-1">{{ partner.phone }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Company:</label>
                        <p class="text-lg mt-1">{{ partner.company_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Trade License:</label>
                        <p class="text-lg mt-1">{{ partner.trade_license_no || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600 dark:text-gray-300">Status:</label>
                        <p class="text-lg mt-1">
                            <span v-if="partner.status" class="text-green-600 font-semibold">Active</span>
                            <span v-else class="text-red-600 font-semibold">Inactive</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pending Documents Alert -->
            <div v-if="partner.approval_status === 'pending'" class="mt-8">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="pi pi-exclamation-triangle text-yellow-600 text-xl mr-3"></i>
                        <div>
                            <p class="font-semibold text-yellow-800">Complete Your Profile</p>
                            <p class="text-yellow-700">Upload all required documents to speed up the approval process.</p>
                            <a :href="route('partner:profile')" class="inline-block mt-2 bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition-colors">
                                Complete Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <div class="text-xl font-bold">Document Status</div>
                    <a :href="route('partner:profile')" class="text-blue-600 hover:text-blue-800 font-medium">
                        Manage Documents →
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">Emirates ID</label>
                        <div v-if="partner.emirates_id_attachment">
                            <p class="text-sm mb-2 text-green-600">✓ Uploaded</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.emirates_id_attachment)" />
                        </div>
                        <p v-else class="text-red-400">Not uploaded</p>
                    </div>

                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">Visa</label>
                        <div v-if="partner.visa_attachment">
                            <p class="text-sm mb-2 text-green-600">✓ Uploaded</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.visa_attachment)" />
                        </div>
                        <p v-else class="text-orange-400">Upload from profile</p>
                    </div>

                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">NOC</label>
                        <div v-if="partner.noc_attachment">
                            <p class="text-sm mb-2 text-green-600">✓ Uploaded</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.noc_attachment)" />
                        </div>
                        <p v-else class="text-orange-400">Upload from profile</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

