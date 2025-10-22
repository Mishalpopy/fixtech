<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Message from 'primevue/message';

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Complaints', 'route': route('customer:tickets.index') },
    { 'value': 'Raise Complaint', 'route': '' },
]);

const form = useForm({
    title: '',
    description: '',
    category: '',
    priority: 'medium',
    attachments: []
});

const fileInput = ref(null);
const selectedFiles = ref([]);

const categories = [
    { value: 'plumbing', label: 'Plumbing' },
    { value: 'electrical', label: 'Electrical' },
    { value: 'hvac', label: 'HVAC' },
    { value: 'appliance', label: 'Appliance' },
    { value: 'general', label: 'General' },
    { value: 'other', label: 'Other' }
];

const priorities = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' }
];

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    if (files.length + selectedFiles.value.length > 5) {
        alert('You can upload a maximum of 5 files.');
        return;
    }
    
    selectedFiles.value = [...selectedFiles.value, ...files];
    form.attachments = selectedFiles.value;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.attachments = selectedFiles.value;
};

const submit = () => {
    form.post(route('customer:tickets.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            selectedFiles.value = [];
        }
    });
};
</script>

<template>
    <Head title="Raise New Complaint" />

    <AppLayout>
        <template #title>
            <span>Raise New Complaint</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="mt-4 mx-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header Card -->
                <Card class="mb-6">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <i class="pi pi-exclamation-triangle text-orange-500 text-2xl"></i>
                            <span>Raise a Complaint</span>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-gray-600 mb-0">
                            Report an issue that requires attention from our service team. Please provide detailed information to help us assist you better.
                        </p>
                    </template>
                </Card>

                <!-- Main Form Card -->
                <Card>
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information Section -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-info-circle text-blue-500"></i>
                                    Basic Information
                                </h3>
                                
                                <!-- Title -->
                                <div class="mb-4">
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                        Complaint Title *
                                    </label>
                                    <InputText
                                        id="title"
                                        v-model="form.title"
                                        placeholder="Brief description of the complaint"
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.title }"
                                        required
                                    />
                                    <small v-if="form.errors.title" class="p-error">{{ form.errors.title }}</small>
                                </div>

                                <!-- Category and Priority Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                            Category *
                                        </label>
                                        <Dropdown
                                            id="category"
                                            v-model="form.category"
                                            :options="categories"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Select a category"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.category }"
                                            required
                                        />
                                        <small v-if="form.errors.category" class="p-error">{{ form.errors.category }}</small>
                                    </div>

                                    <div>
                                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                            Priority Level *
                                        </label>
                                        <Dropdown
                                            id="priority"
                                            v-model="form.priority"
                                            :options="priorities"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Select priority"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.priority }"
                                            required
                                        />
                                        <small v-if="form.errors.priority" class="p-error">{{ form.errors.priority }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- Description Section -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-file-edit text-green-500"></i>
                                    Complaint Details
                                </h3>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Detailed Description *
                                    </label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="6"
                                        placeholder="Please provide a detailed description of the issue, including when it occurred, what you were doing, and any relevant details..."
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.description }"
                                        required
                                    />
                                    <small v-if="form.errors.description" class="p-error">{{ form.errors.description }}</small>
                                    <small class="text-gray-500 mt-1 block">
                                        <i class="pi pi-lightbulb mr-1"></i>
                                        Tip: The more details you provide, the better we can assist you.
                                    </small>
                                </div>
                            </div>

                            <Divider />

                            <!-- File Attachments Section -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-paperclip text-purple-500"></i>
                                    Supporting Documents (Optional)
                                </h3>
                                
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                        @change="handleFileSelect"
                                        class="hidden"
                                    />
                                    <Button
                                        type="button"
                                        @click="$refs.fileInput.click()"
                                        icon="pi pi-upload"
                                        label="Choose Files"
                                        severity="secondary"
                                        outlined
                                        class="mb-3"
                                    />
                                    <p class="text-sm text-gray-500 mb-0">
                                        <i class="pi pi-info-circle mr-1"></i>
                                        Upload photos or documents related to your complaint. You can upload up to 5 files (images, PDFs, documents). Max 10MB per file.
                                    </p>
                                </div>

                                <!-- Selected Files -->
                                <div v-if="selectedFiles.length > 0" class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Selected Files:</h4>
                                    <div class="space-y-2">
                                        <div v-for="(file, index) in selectedFiles" :key="index" 
                                             class="flex items-center justify-between bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <i class="pi pi-file text-blue-500"></i>
                                                <div>
                                                    <span class="text-sm font-medium text-gray-700">{{ file.name }}</span>
                                                    <span class="text-xs text-gray-500 ml-2">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                @click="removeFile(index)"
                                                icon="pi pi-times"
                                                severity="danger"
                                                text
                                                rounded
                                                size="small"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <small v-if="form.errors.attachments" class="p-error block mt-2">{{ form.errors.attachments }}</small>
                            </div>

                            <Divider />

                            <!-- Submit Section -->
                            <div class="flex justify-between items-center pt-4">
                                <div class="text-sm text-gray-500">
                                    <i class="pi pi-shield mr-1"></i>
                                    Your complaint will be reviewed by our service team
                                </div>
                                
                                <div class="flex gap-3">
                                    <Link :href="route('customer:tickets.index')">
                                        <Button 
                                            label="Cancel" 
                                            severity="secondary" 
                                            outlined
                                        />
                                    </Link>
                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        :loading="form.processing"
                                        label="Submit Complaint"
                                        icon="pi pi-send"
                                        severity="success"
                                    />
                                </div>
                            </div>
                        </form>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
