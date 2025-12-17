<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';
import Rating from 'primevue/rating';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    services: Array
});

const toast = useToast();

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Reviews', 'route': route('customer:reviews.index') },
    { 'value': 'Create Review', 'route': '' },
]);

const form = useForm({
    service_id: '',
    rating: 5,
    comment: ''
});

const serviceOptions = props.services.map(service => ({
    label: service.name,
    value: service.id
}));

function submit() {
    form.post(route('customer:reviews.store'), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Review created successfully', life: 3000 });
        },
        onError: (errors) => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create review', life: 3000 });
        }
    });
}
</script>

<template>
    <Head title="Create Review" />
    <AppLayout>
        <template #title>
            <span>Create Review</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <Card>
                <template #content>
                    <form @submit.prevent="submit()" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service *</label>
                            <Dropdown 
                                v-model="form.service_id" 
                                :options="serviceOptions" 
                                optionLabel="label" 
                                optionValue="value"
                                placeholder="Select a service"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.service_id }"
                            />
                            <small v-if="form.errors.service_id" class="p-error">{{ form.errors.service_id }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                            <Rating v-model="form.rating" :stars="5" :cancel="false" />
                            <small v-if="form.errors.rating" class="p-error">{{ form.errors.rating }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comment *</label>
                            <Textarea 
                                v-model="form.comment" 
                                rows="5" 
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.comment }"
                                placeholder="Write your review..."
                            />
                            <small v-if="form.errors.comment" class="p-error">{{ form.errors.comment }}</small>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" label="Submit Review" icon="pi pi-check" :loading="form.processing" />
                            <Button type="button" label="Cancel" icon="pi pi-times" severity="secondary" 
                                @click="$inertia.visit(route('customer:reviews.index'))" />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

