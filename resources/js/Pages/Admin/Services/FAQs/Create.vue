<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea, Dropdown } from 'primevue';

const props = defineProps({
    subServices: Array,
    subServiceId: [Number, String]
});

const loading = ref(false);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'FAQs', 'route': route('admin:faqs.index') },
    { 'value': 'Create FAQ', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

const form = useForm({
    sub_service_id: props.subServiceId || null,
    title: '',
    description: '',
    order: 0,
    status: true
});

function save() {
    loading.value = true;
    form.post(route('admin:faqs.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:faqs.index', { sub_service_id: form.sub_service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Create FAQ" />
    <AppLayout>
        <template #title>
            <span>Create FAQ</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="sub_service_id" class="block font-bold mb-1">Sub Service *</label>
                    <Dropdown id="sub_service_id" v-model="form.sub_service_id" :options="subServiceOptions" optionLabel="label" 
                        optionValue="value" placeholder="Select Sub Service" :invalid="form.errors.sub_service_id" fluid />
                    <small v-if="form.errors.sub_service_id" class="text-red-500">{{ form.errors.sub_service_id }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="title" class="block font-bold mb-1">Title *</label>
                    <InputText id="title" v-model.trim="form.title" required="true" autofocus
                        :invalid="form.errors.title" fluid />
                    <small v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="description" class="block font-bold mb-1">Description *</label>
                    <Textarea id="description" v-model.trim="form.description" required="true"
                        :invalid="form.errors.description" fluid rows="6" />
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="order" class="block font-bold mb-1">Order</label>
                    <InputText id="order" type="number" v-model.number="form.order" min="0"
                        :invalid="form.errors.order" fluid />
                    <small v-if="form.errors.order" class="text-red-500">{{ form.errors.order }}</small>
                    <small class="text-gray-500">Lower numbers appear first</small>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <Button label="Save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

