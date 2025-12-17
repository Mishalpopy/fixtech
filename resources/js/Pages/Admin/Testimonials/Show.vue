<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    testimonial: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Testimonials', 'route': route('admin:testimonials.index') },
    { 'value': 'Testimonial Details', 'route': '' },
]);

function goBack() {
    router.get(route('admin:testimonials.index'));
}

function approveTestimonial() {
    router.post(
        route("admin:testimonials.approve", { testimonial: props.testimonial.id }),
        {},
        {
            onSuccess: (res) => {
                router.reload();
            },
            onError: (err) => {},
        }
    );
}

function rejectTestimonial() {
    router.post(
        route("admin:testimonials.reject", { testimonial: props.testimonial.id }),
        {},
        {
            onSuccess: (res) => {
                router.reload();
            },
            onError: (err) => {},
        }
    );
}

function getStatusSeverity(status) {
    switch(status) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}
</script>

<template>
    <Head title="Testimonial Details" />
    <AppLayout>
        <template #title>
            <span>Testimonial Details</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <Card>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                            <p class="text-gray-900">{{ testimonial.customer?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Number</label>
                            <p class="text-gray-900 font-mono">{{ testimonial.ticket?.ticket_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <p class="text-gray-900">{{ testimonial.title || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <Tag :value="testimonial.status" :severity="getStatusSeverity(testimonial.status)" />
                        </div>
                        <div v-if="testimonial.photo" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                            <img :src="`/storage/${testimonial.photo}`" alt="Testimonial Photo" class="max-w-md rounded" />
                        </div>
                        <div v-if="testimonial.video" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Video</label>
                            <video :src="`/storage/${testimonial.video}`" controls class="max-w-md rounded"></video>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Approved By</label>
                            <p class="text-gray-900">{{ testimonial.approved_by_user?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Approved At</label>
                            <p class="text-gray-900">{{ testimonial.formatted_approved_at || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                            <p class="text-gray-900">{{ testimonial.formatted_created_at }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ testimonial.description || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-2" v-if="testimonial.status === 'pending'">
                        <Button label="Approve" icon="pi pi-check" severity="success" @click="approveTestimonial()" />
                        <Button label="Reject" icon="pi pi-times" severity="danger" @click="rejectTestimonial()" />
                        <Button label="Back" icon="pi pi-arrow-left" @click="goBack()" />
                    </div>
                    <div class="mt-6 flex gap-2" v-else>
                        <Button label="Back" icon="pi pi-arrow-left" @click="goBack()" />
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

