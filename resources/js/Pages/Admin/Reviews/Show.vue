<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    review: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Reviews', 'route': route('admin:reviews.index') },
    { 'value': 'Review Details', 'route': '' },
]);

function goBack() {
    router.get(route('admin:reviews.index'));
}

function getStatusSeverity(status) {
    return status === 'active' ? 'success' : 'secondary';
}
</script>

<template>
    <Head title="Review Details" />
    <AppLayout>
        <template #title>
            <span>Review Details</span>
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
                            <p class="text-gray-900">{{ review.customer?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                            <p class="text-gray-900">{{ review.service?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Number</label>
                            <p class="text-gray-900 font-mono">{{ review.ticket?.ticket_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <div class="flex items-center gap-1">
                                <i v-for="i in 5" :key="i" 
                                   :class="i <= review.rating ? 'pi pi-star-fill text-yellow-500' : 'pi pi-star text-gray-300'">
                                </i>
                                <span class="ml-2">({{ review.rating }}/5)</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <Tag :value="review.status" :severity="getStatusSeverity(review.status)" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                            <p class="text-gray-900">{{ review.formatted_created_at }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ review.comment || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-2">
                        <Button label="Back" icon="pi pi-arrow-left" @click="goBack()" />
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

