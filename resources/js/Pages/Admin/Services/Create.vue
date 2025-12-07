<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea } from 'primevue';

const loading = ref(false);
const imagePreview = ref(null);
const imageInput = ref(null);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Create Service', 'route': '' },
]);

const form = useForm({
    name: '',
    description: '',
    image: null,
    status: true
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
    imagePreview.value = null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
}

function save() {
    loading.value = true;
    form.post(route('admin:services.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:services.index'));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Create Service" />
    <AppLayout>
        <template #title>
            <span>Create Service</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
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
                        <Button v-if="imagePreview || form.image" label="Remove" icon="pi pi-times" @click="removeImage" severity="danger" outlined />
                    </div>
                    <div v-if="imagePreview" class="mt-4">
                        <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded border" />
                    </div>
                    <small v-if="form.errors.image" class="text-red-500">{{ form.errors.image }}</small>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <Button label="Save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

