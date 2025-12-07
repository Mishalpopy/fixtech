<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';

const props = defineProps({
    faq: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'FAQs', 'route': route('admin:faqs.index') },
    { 'value': 'FAQ Details', 'route': '' },
]);

function editFaq() {
    router.get(route('admin:faqs.edit', [props.faq.id]));
}
</script>

<template>
    <Head title="FAQ Details" />
    <AppLayout>
        <template #title>
            <span>FAQ Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex justify-end gap-2 mb-4">
                <Button label="Edit" icon="pi pi-pencil" @click="editFaq()" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Service</label>
                    <p class="text-gray-700">{{ faq.sub_service?.service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Sub Service</label>
                    <p class="text-gray-700">{{ faq.sub_service?.name || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Title</label>
                    <p class="text-gray-700 font-semibold text-lg">{{ faq.title }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Order</label>
                    <p class="text-gray-700">{{ faq.order }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <p class="text-gray-700">
                        <span :class="faq.status ? 'text-green-600' : 'text-red-600'">
                            {{ faq.status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold mb-1">Description</label>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ faq.description }}</p>
                </div>
                <div>
                    <label class="block font-bold mb-1">Created At</label>
                    <p class="text-gray-700">{{ faq.formatted_created_at }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

