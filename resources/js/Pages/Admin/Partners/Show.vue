<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, Tag } from 'primevue';

const props = defineProps({
    partner: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Partners', 'route': route('admin:partners.index') },
    { 'value': 'Partner Details', 'route': '' },
]);

function goBack() {
    router.get(route('admin:partners.index'));
}

function editPartner() {
    router.get(route('admin:partners.edit', [props.partner.id]));
}

function viewAttachment(attachment) {
    if (attachment && attachment.file_url) {
        window.open(attachment.file_url, '_blank');
    }
}

</script>

<template>
    <Head title="Partner Details" />

    <AppLayout>
        <template #title>
            <span>Partner Details</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ partner.name }}</h2>
                <Tag v-if="partner.approval_status === 'approved'" severity="success" value="Approved" />
                <Tag v-else-if="partner.approval_status === 'pending'" severity="warning" value="Pending Approval" />
                <Tag v-else severity="danger" value="Rejected" />
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Email</label>
                    <p class="text-lg">{{ partner.email }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Phone</label>
                    <p class="text-lg">{{ partner.phone }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Company Name</label>
                    <p class="text-lg">{{ partner.company_name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Trade License No</label>
                    <p class="text-lg">{{ partner.trade_license_no || 'N/A' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Status</label>
                    <p class="text-lg">
                        <span v-if="partner.status" class="text-green-600 font-semibold">Active</span>
                        <span v-else class="text-red-600 font-semibold">Inactive</span>
                    </p>
                </div>
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Email Verified</label>
                    <p class="text-lg">
                        <span v-if="partner.email_verified_at" class="text-green-600">{{ partner.email_verified_at }}</span>
                        <span v-else class="text-orange-600">Not Verified</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block font-bold mb-1 text-gray-600">Created Date</label>
                    <p class="text-lg">{{ partner.formatted_created_at }}</p>
                </div>
                <div v-if="partner.approved_at">
                    <label class="block font-bold mb-1 text-gray-600">Approved Date</label>
                    <p class="text-lg">{{ partner.approved_at }}</p>
                </div>
            </div>

            <div v-if="partner.approved_by" class="mt-4">
                <label class="block font-bold mb-1 text-gray-600">Approved By</label>
                <p class="text-lg">{{ partner.approved_by.name }}</p>
            </div>

            <div v-if="partner.rejection_reason" class="mt-4 bg-red-50 p-4 rounded">
                <label class="block font-bold mb-1 text-red-600">Rejection Reason</label>
                <p class="text-lg">{{ partner.rejection_reason }}</p>
            </div>

            <div class="mt-4">
                <label class="block font-bold mb-1 text-gray-600">Address</label>
                <p class="text-lg">{{ partner.address }}</p>
            </div>

            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4">Attachments</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">Emirates ID</label>
                        <div v-if="partner.emirates_id_attachment">
                            <p class="text-sm mb-2">{{ partner.emirates_id_attachment.file_name }}</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.emirates_id_attachment)" />
                        </div>
                        <p v-else class="text-gray-400">Not uploaded</p>
                    </div>

                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">Visa</label>
                        <div v-if="partner.visa_attachment">
                            <p class="text-sm mb-2">{{ partner.visa_attachment.file_name }}</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.visa_attachment)" />
                        </div>
                        <p v-else class="text-gray-400">Not uploaded</p>
                    </div>

                    <div class="border rounded p-4">
                        <label class="block font-bold mb-2 text-gray-600">NOC</label>
                        <div v-if="partner.noc_attachment">
                            <p class="text-sm mb-2">{{ partner.noc_attachment.file_name }}</p>
                            <Button label="View" icon="pi pi-eye" size="small" @click="viewAttachment(partner.noc_attachment)" />
                        </div>
                        <p v-else class="text-gray-400">Not uploaded</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <Button label="Back" severity="secondary" @click="goBack()" />
                <Button label="Edit" icon="pi pi-pencil" @click="editPartner()" />
            </div>
        </div>
    </AppLayout>
</template>

