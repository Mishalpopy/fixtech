<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Complaints', 'route': route('customer:tickets.index') },
    { 'value': `Complaint #${props.ticket.ticket_number}`, 'route': route('customer:tickets.show', props.ticket.id) },
    { 'value': 'Edit', 'route': '' },
]);

const form = useForm({
    title: props.ticket.title,
    description: props.ticket.description,
    category: props.ticket.category,
    priority: props.ticket.priority,
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
    form.put(route('customer:tickets.update', props.ticket.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            selectedFiles.value = [];
        }
    });
};
</script>

<template>
    <Head title="Edit Complaint" />

    <AppLayout>
        <template #title>
            <span>Edit Complaint #{{ ticket.ticket_number }}</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold mb-6">Edit Complaint</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Complaint Title *
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Brief description of the issue"
                            required
                        />
                        <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <!-- Category and Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category *
                            </label>
                            <select
                                id="category"
                                v-model="form.category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category.value" :value="category.value">
                                    {{ category.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-600 text-sm mt-1">
                                {{ form.errors.category }}
                            </div>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                Priority *
                            </label>
                            <select
                                id="priority"
                                v-model="form.priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                                    {{ priority.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.priority" class="text-red-600 text-sm mt-1">
                                {{ form.errors.priority }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description *
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Please provide a detailed description of the issue..."
                            required
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <!-- Existing Attachments -->
                    <div v-if="ticket.attachments.length > 0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Current Attachments
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="attachment in ticket.attachments" :key="attachment.id" 
                                 class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <i v-if="attachment.file_type.startsWith('image/')" class="pi pi-image text-2xl text-blue-600"></i>
                                        <i v-else-if="attachment.file_type === 'application/pdf'" class="pi pi-file-pdf text-2xl text-red-600"></i>
                                        <i v-else class="pi pi-file text-2xl text-gray-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.original_name }}</p>
                                        <p class="text-xs text-gray-500">{{ attachment.formatted_file_size }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New File Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Add New Attachments (Optional)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                            <button
                                type="button"
                                @click="$refs.fileInput.click()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                            >
                                <i class="pi pi-upload mr-2"></i>
                                Choose Files
                            </button>
                            <p class="text-sm text-gray-500 mt-2">
                                You can upload up to 5 files (images, PDFs, documents). Max 10MB per file.
                            </p>
                        </div>

                        <!-- Selected Files -->
                        <div v-if="selectedFiles.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">New Files to Upload:</h4>
                            <div class="space-y-2">
                                <div v-for="(file, index) in selectedFiles" :key="index" 
                                     class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-file text-gray-500"></i>
                                        <span class="text-sm text-gray-700">{{ file.name }}</span>
                                        <span class="text-xs text-gray-500">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.errors.attachments" class="text-red-600 text-sm mt-1">
                            {{ form.errors.attachments }}
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-4 pt-6 border-t">
                        <Link :href="route('customer:tickets.show', ticket.id)" 
                              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Complaint</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
