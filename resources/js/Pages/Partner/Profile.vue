<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Partner/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref, computed } from 'vue';
import { Button, InputText, Textarea, FileUpload, Message, Divider, Tag } from 'primevue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    partner: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('partner:dashboard') },
    { 'value': 'Profile', 'route': '' },
]);

const form = useForm({
    name: props.partner.name,
    email: props.partner.email,
    phone: props.partner.phone,
    address: props.partner.address,
    company_name: props.partner.company_name,
    trade_license_no: props.partner.trade_license_no,
});

const uploadForm = useForm({
    emirates_id: null,
    visa: null,
    noc: null,
});

const uploading = ref(false);
const uploadProgress = ref(0);

const documentTypes = [
    { key: 'emirates_id', label: 'Emirates ID', required: true },
    { key: 'visa', label: 'Visa', required: true },
    { key: 'noc', label: 'NOC (No Objection Certificate)', required: true },
];

const getDocumentStatus = (type) => {
    const attachment = props.partner.attachments?.find(att => att.type === type);
    return {
        uploaded: !!attachment,
        attachment: attachment
    };
};

const getProfileCompletionPercentage = () => {
    let completed = 0;
    let total = 0;
    
    // Basic info fields
    const basicFields = ['name', 'email', 'phone', 'address', 'company_name', 'trade_license_no'];
    basicFields.forEach(field => {
        total++;
        if (props.partner[field]) completed++;
    });
    
    // Document attachments
    documentTypes.forEach(doc => {
        total++;
        if (getDocumentStatus(doc.key).uploaded) completed++;
    });
    
    return Math.round((completed / total) * 100);
};

const profileCompletion = computed(() => getProfileCompletionPercentage());

const submitProfile = () => {
    form.put(route('partner:profile.update'), {
        onSuccess: () => {
            // Show success message
        }
    });
};

const onFileSelect = (event, type) => {
    const file = event.files[0];
    if (file) {
        uploadForm[type] = file;
    }
};

const uploadDocument = (type) => {
    if (!uploadForm[type]) return;
    
    uploading.value = true;
    uploadProgress.value = 0;
    
    const formData = new FormData();
    formData.append(type, uploadForm[type]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            uploadProgress.value = Math.round((e.loaded / e.total) * 100);
        }
    });
    
    xhr.onload = () => {
        uploading.value = false;
        uploadProgress.value = 0;
        uploadForm[type] = null;
        
        if (xhr.status === 200) {
            router.reload({ only: ['partner'] });
        } else {
            // Handle error
        }
    };
    
    xhr.open('POST', route('partner:documents.upload', { type }));
    xhr.send(formData);
};

const viewDocument = (attachment) => {
    if (attachment && attachment.file_url) {
        window.open(attachment.file_url, '_blank');
    }
};

const deleteDocument = (type) => {
    if (confirm('Are you sure you want to delete this document?')) {
        router.delete(route('partner:documents.delete', { type }), {
            onSuccess: () => {
                router.reload({ only: ['partner'] });
            }
        });
    }
};
</script>

<template>
    <Head title="Partner Profile" />

    <AppLayout>
        <template #title>
            <span>Profile</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4 mx-6">
            <!-- Profile Completion Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="pi pi-user text-blue-600"></i>
                        <span class="text-lg font-semibold">Profile Completion</span>
                    </div>
                    <div>
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ profileCompletion }}%</div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: profileCompletion + '%' }"></div>
                            </div>
                            <p class="text-sm text-gray-600">
                                Complete your profile to get approved faster
                            </p>
                        </div>
                        
                        <div class="space-y-2">
                            <div v-for="doc in documentTypes" :key="doc.key" class="flex items-center justify-between text-sm">
                                <span>{{ doc.label }}</span>
                                <Tag 
                                    :value="getDocumentStatus(doc.key).uploaded ? 'Uploaded' : 'Missing'" 
                                    :severity="getDocumentStatus(doc.key).uploaded ? 'success' : 'danger'"
                                    size="small"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="pi pi-info-circle text-green-600"></i>
                        <span class="text-lg font-semibold">Basic Information</span>
                    </div>
                    <div>
                        <form @submit.prevent="submitProfile" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Name *</label>
                                    <InputText v-model="form.name" class="w-full" :class="{ 'p-invalid': form.errors.name }" />
                                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Email *</label>
                                    <InputText v-model="form.email" type="email" class="w-full" :class="{ 'p-invalid': form.errors.email }" />
                                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Phone *</label>
                                    <InputText v-model="form.phone" class="w-full" :class="{ 'p-invalid': form.errors.phone }" />
                                    <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Company Name *</label>
                                    <InputText v-model="form.company_name" class="w-full" :class="{ 'p-invalid': form.errors.company_name }" />
                                    <small v-if="form.errors.company_name" class="text-red-500">{{ form.errors.company_name }}</small>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Trade License No *</label>
                                    <InputText v-model="form.trade_license_no" class="w-full" :class="{ 'p-invalid': form.errors.trade_license_no }" />
                                    <small v-if="form.errors.trade_license_no" class="text-red-500">{{ form.errors.trade_license_no }}</small>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2">Address</label>
                                <Textarea v-model="form.address" rows="3" class="w-full" :class="{ 'p-invalid': form.errors.address }" />
                                <small v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                            </div>
                            
                            <div class="flex justify-end">
                                <Button 
                                    type="submit" 
                                    label="Update Profile" 
                                    icon="pi pi-save" 
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="pi pi-file text-purple-600"></i>
                        <span class="text-lg font-semibold">Required Documents</span>
                    </div>
                    <div>
                        <div class="space-y-6">
                            <Message severity="info" :closable="false">
                                Please upload clear, high-quality images or PDFs of all required documents. 
                                Documents will be reviewed by our team before approval.
                            </Message>
                            
                            <div v-for="doc in documentTypes" :key="doc.key" class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="font-semibold">{{ doc.label }}</h4>
                                        <p class="text-sm text-gray-600">Required document</p>
                                    </div>
                                    <Tag 
                                        :value="getDocumentStatus(doc.key).uploaded ? 'Uploaded' : 'Missing'" 
                                        :severity="getDocumentStatus(doc.key).uploaded ? 'success' : 'danger'"
                                    />
                                </div>
                                
                                <div v-if="getDocumentStatus(doc.key).uploaded" class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-check-circle text-green-600"></i>
                                        <span class="text-sm text-green-600">Document uploaded successfully</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button 
                                            label="View Document" 
                                            icon="pi pi-eye" 
                                            size="small" 
                                            @click="viewDocument(getDocumentStatus(doc.key).attachment)"
                                        />
                                        <Button 
                                            label="Delete" 
                                            icon="pi pi-trash" 
                                            severity="danger" 
                                            size="small" 
                                            @click="deleteDocument(doc.key)"
                                        />
                                    </div>
                                </div>
                                
                                <div v-else>
                                    <FileUpload
                                        mode="basic"
                                        :name="doc.key"
                                        :accept="'image/*,.pdf'"
                                        :maxFileSize="10000000"
                                        @select="onFileSelect($event, doc.key)"
                                        :auto="false"
                                        chooseLabel="Choose File"
                                        class="mb-3"
                                    />
                                    <div v-if="uploadForm[doc.key]" class="flex items-center gap-2 mb-3">
                                        <i class="pi pi-file text-blue-600"></i>
                                        <span class="text-sm">{{ uploadForm[doc.key].name }}</span>
                                        <Button 
                                            label="Upload" 
                                            icon="pi pi-upload" 
                                            size="small" 
                                            @click="uploadDocument(doc.key)"
                                            :loading="uploading"
                                        />
                                    </div>
                                    <div v-if="uploading" class="mt-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                                        </div>
                                        <small class="text-gray-600">Uploading...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
