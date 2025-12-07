<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea, Dropdown } from 'primevue';

const props = defineProps({
    subService: Object,
    services: Array
});

const loading = ref(false);
const imagePreview = ref(props.subService.image ? `/storage/${props.subService.image}` : null);
const imageInput = ref(null);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Sub Services', 'route': route('admin:sub-services.index') },
    { 'value': 'Edit Sub Service', 'route': '' },
]);

const serviceOptions = props.services.map(s => ({ label: s.name, value: s.id }));

const form = useForm({
    service_id: props.subService.service_id,
    name: props.subService.name,
    description: props.subService.description || '',
    image: null,
    status: props.subService.status
});

function handleImageSelect(event) {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    form.image = null;
    imagePreview.value = props.subService.image ? `/storage/${props.subService.image}` : null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
}

function update() {
    loading.value = true;
    form.post(route('admin:sub-services.update', [props.subService.id]), {
        preserveScroll: true,
        forceFormData: true,
        _method: 'PUT',
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:sub-services.index', { service_id: form.service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Edit Sub Service" />
    <AppLayout>
        <template #title>
            <span>Edit Sub Service</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="service_id" class="block font-bold mb-1">Service *</label>
                    <Dropdown id="service_id" v-model="form.service_id" :options="serviceOptions" optionLabel="label" 
                        optionValue="value" placeholder="Select Service" :invalid="form.errors.service_id" fluid />
                    <small v-if="form.errors.service_id" class="text-red-500">{{ form.errors.service_id }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="name" class="block font-bold mb-1">Name *</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="form.errors.name" fluid />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="description" class="block font-bold mb-1">Description</label>
                    <Textarea id="description" v-model.trim="form.description" :invalid="form.errors.description" fluid rows="4" />
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="image" class="block font-bold mb-1">Image</label>
                    <input ref="imageInput" type="file" accept="image/*" @change="handleImageSelect" class="hidden" />
                    <div class="flex gap-4 items-center">
                        <Button label="Choose Image" icon="pi pi-upload" @click="imageInput?.click()" severity="secondary" outlined />
                        <Button v-if="form.image" label="Remove New Image" icon="pi pi-times" @click="removeImage" severity="danger" outlined />
                    </div>
                    <div v-if="imagePreview" class="mt-4">
                        <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded border" />
                    </div>
                    <small v-if="form.errors.image" class="text-red-500">{{ form.errors.image }}</small>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <Button label="Update" @click="update()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

