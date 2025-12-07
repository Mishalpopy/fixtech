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
const imagePreview = ref(null);
const imageInput = ref(null);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Service Items', 'route': route('admin:service-items.index') },
    { 'value': 'Create Service Item', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

const form = useForm({
    sub_service_id: props.subServiceId || null,
    name: '',
    description: '',
    price: null,
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
    form.post(route('admin:service-items.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:service-items.index', { sub_service_id: form.sub_service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Create Service Item" />
    <AppLayout>
        <template #title>
            <span>Create Service Item</span>
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
                    <label for="price" class="block font-bold mb-1">Price</label>
                    <InputText id="price" type="number" v-model.number="form.price" step="0.01" min="0"
                        :invalid="form.errors.price" fluid />
                    <small v-if="form.errors.price" class="text-red-500">{{ form.errors.price }}</small>
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

