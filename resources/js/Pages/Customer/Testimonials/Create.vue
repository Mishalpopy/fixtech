<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';
import FileUpload from 'primevue/fileupload';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Testimonials', 'route': route('customer:testimonials.index') },
    { 'value': 'Create Testimonial', 'route': '' },
]);

const form = useForm({
    title: '',
    description: '',
    photo: null,
    video: null
});

const photoFile = ref(null);
const videoFile = ref(null);

function onPhotoSelect(event) {
    if (event.files && event.files.length > 0) {
        form.photo = event.files[0];
        photoFile.value = event.files[0];
    }
}

function onVideoSelect(event) {
    if (event.files && event.files.length > 0) {
        form.video = event.files[0];
        videoFile.value = event.files[0];
    }
}

function submit() {
    form.post(route('customer:testimonials.store'), {
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Testimonial created successfully. It will be visible after admin approval.', life: 3000 });
        },
        onError: (errors) => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create testimonial', life: 3000 });
        }
    });
}
</script>

<template>
    <Head title="Create Testimonial" />
    <AppLayout>
        <template #title>
            <span>Create Testimonial</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <Card>
                <template #content>
                    <form @submit.prevent="submit()" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title (Optional)</label>
                            <InputText 
                                v-model="form.title" 
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.title }"
                                placeholder="Enter testimonial title"
                            />
                            <small v-if="form.errors.title" class="p-error">{{ form.errors.title }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <Textarea 
                                v-model="form.description" 
                                rows="5" 
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.description }"
                                placeholder="Write your testimonial..."
                            />
                            <small v-if="form.errors.description" class="p-error">{{ form.errors.description }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Photo (Optional)</label>
                            <FileUpload 
                                mode="basic" 
                                accept="image/*" 
                                :maxFileSize="5120000"
                                @select="onPhotoSelect"
                                chooseLabel="Choose Photo"
                                class="w-full"
                            />
                            <small v-if="form.errors.photo" class="p-error">{{ form.errors.photo }}</small>
                            <small class="text-gray-500">Max file size: 5MB. Supported formats: JPEG, PNG, JPG, GIF</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Video (Optional)</label>
                            <FileUpload 
                                mode="basic" 
                                accept="video/*" 
                                :maxFileSize="10240000"
                                @select="onVideoSelect"
                                chooseLabel="Choose Video"
                                class="w-full"
                            />
                            <small v-if="form.errors.video" class="p-error">{{ form.errors.video }}</small>
                            <small class="text-gray-500">Max file size: 10MB. Supported formats: MP4, AVI, MOV, WMV</small>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" label="Submit Testimonial" icon="pi pi-check" :loading="form.processing" />
                            <Button type="button" label="Cancel" icon="pi pi-times" severity="secondary" 
                                @click="$inertia.visit(route('customer:testimonials.index'))" />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

