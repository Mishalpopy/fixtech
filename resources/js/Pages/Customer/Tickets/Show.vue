<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Image from 'primevue/image';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    ticket: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Complaints', 'route': route('customer:tickets.index') },
    { 'value': `Complaint #${props.ticket.ticket_number}`, 'route': '' },
]);

// Attachment viewing functionality
const selectedAttachment = ref(null);
const showAttachmentDialog = ref(false);

const openAttachment = (attachment) => {
    selectedAttachment.value = attachment;
    showAttachmentDialog.value = true;
};

const closeAttachmentDialog = () => {
    showAttachmentDialog.value = false;
    selectedAttachment.value = null;
};

const getFileIcon = (fileType) => {
    if (fileType.startsWith('image/')) {
        return 'pi pi-image';
    } else if (fileType === 'application/pdf') {
        return 'pi pi-file-pdf';
    } else if (fileType.includes('word') || fileType.includes('document')) {
        return 'pi pi-file-word';
    } else {
        return 'pi pi-file';
    }
};

const getFileIconColor = (fileType) => {
    if (fileType.startsWith('image/')) {
        return 'text-blue-500';
    } else if (fileType === 'application/pdf') {
        return 'text-red-500';
    } else if (fileType.includes('word') || fileType.includes('document')) {
        return 'text-blue-600';
    } else {
        return 'text-gray-500';
    }
};

const isImageFile = (fileType) => {
    return fileType.startsWith('image/');
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'open': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-blue-100 text-blue-800',
        'high': 'bg-yellow-100 text-yellow-800',
        'urgent': 'bg-red-100 text-red-800'
    };
    return classes[priority] || 'bg-gray-100 text-gray-800';
};

const getCategoryLabel = (category) => {
    const labels = {
        'plumbing': 'Plumbing',
        'electrical': 'Electrical',
        'hvac': 'HVAC',
        'appliance': 'Appliance',
        'general': 'General',
        'other': 'Other'
    };
    return labels[category] || category;
};

const getStatusLabel = (status) => {
    return status.replace('_', ' ').toUpperCase();
};

const getStatusSeverity = (status) => {
    const severities = {
        'open': 'info',
        'in_progress': 'warning',
        'resolved': 'success',
        'closed': 'secondary',
        'cancelled': 'danger'
    };
    return severities[status] || 'secondary';
};

const getPriorityLabel = (priority) => {
    return priority.toUpperCase();
};

const getPrioritySeverity = (priority) => {
    const severities = {
        'low': 'success',
        'medium': 'info',
        'high': 'warning',
        'urgent': 'danger'
    };
    return severities[priority] || 'info';
};

const downloadAttachment = (attachment) => {
    window.open(route('customer:tickets.attachments.download', {
        ticket: props.ticket.id,
        attachment: attachment.id
    }), '_blank');
};

const deleteTicket = () => {
    if (confirm('Are you sure you want to delete this ticket?')) {
        router.delete(route('customer:tickets.destroy', props.ticket.id));
    }
};
</script>

<template>
    <Head :title="`Complaint #${ticket.ticket_number}`" />

    <AppLayout>
        <template #title>
            <span>Complaint #{{ ticket.ticket_number }}</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="mt-4 mx-6 space-y-6">
            <!-- Complaint Header -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-orange-500"></i>
                        <span>{{ ticket.title }}</span>
                    </div>
                </template>
                <template #content>
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <Tag 
                                :value="getStatusLabel(ticket.status)" 
                                :severity="getStatusSeverity(ticket.status)"
                            />
                            <Tag 
                                :value="getPriorityLabel(ticket.priority)" 
                                :severity="getPrioritySeverity(ticket.priority)"
                            />
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <Link v-if="ticket.status === 'open'" 
                                  :href="route('customer:tickets.edit', ticket.id)">
                                <Button 
                                    label="Edit" 
                                    icon="pi pi-pencil" 
                                    severity="success" 
                                    outlined
                                />
                            </Link>
                            
                            <Button 
                                v-if="ticket.status === 'open'" 
                                @click="deleteTicket"
                                label="Delete" 
                                icon="pi pi-trash" 
                                severity="danger" 
                                outlined
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-tag text-gray-500"></i>
                            <span class="font-medium">Complaint #:</span>
                            <span class="font-mono">{{ ticket.ticket_number }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-folder text-gray-500"></i>
                            <span class="font-medium">Category:</span>
                            <span>{{ getCategoryLabel(ticket.category) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-gray-500"></i>
                            <span class="font-medium">Created:</span>
                            <span>{{ ticket.formatted_created_at }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Complaint Description -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-file-edit text-green-500"></i>
                        <span>Complaint Details</span>
                    </div>
                </template>
                <template #content>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                    </div>
                </template>
            </Card>

            <!-- Attachments -->
            <Card v-if="ticket.attachments.length > 0">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-paperclip text-purple-500"></i>
                        <span>Supporting Documents ({{ ticket.attachments.length }})</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="attachment in ticket.attachments" 
                            :key="attachment.id" 
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50"
                            @click="openAttachment(attachment)"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <i 
                                        :class="[getFileIcon(attachment.file_type), getFileIconColor(attachment.file_type), 'text-2xl']"
                                    ></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate mb-1">
                                        {{ attachment.original_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 mb-2">{{ attachment.formatted_file_size }}</p>
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            @click.stop="openAttachment(attachment)"
                                            icon="pi pi-eye" 
                                            size="small" 
                                            severity="info" 
                                            text
                                            label="View"
                                        />
                                        <Button 
                                            @click.stop="downloadAttachment(attachment)"
                                            icon="pi pi-download" 
                                            size="small" 
                                            severity="secondary" 
                                            text
                                            label="Download"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Admin Notes (if any) -->
            <Card v-if="ticket.admin_notes">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-comments text-yellow-500"></i>
                        <span>Admin Notes</span>
                    </div>
                </template>
                <template #content>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.admin_notes }}</p>
                    </div>
                </template>
            </Card>

            <!-- Resolution Info -->
            <Card v-if="ticket.resolved_at">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span>Resolution</span>
                    </div>
                </template>
                <template #content>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-green-800">
                            <strong>Resolved on:</strong> {{ ticket.formatted_resolved_at }}
                        </p>
                    </div>
                </template>
            </Card>

            <!-- Back Button -->
            <div class="flex justify-start">
                <Link :href="route('customer:tickets.index')">
                    <Button 
                        label="Back to My Complaints" 
                        icon="pi pi-arrow-left" 
                        severity="secondary" 
                        outlined
                    />
                </Link>
            </div>
        </div>

        <!-- Attachment Viewing Dialog -->
        <Dialog 
            v-model:visible="showAttachmentDialog" 
            :header="selectedAttachment?.original_name || 'Attachment'"
            :modal="true" 
            :style="{ width: '90vw', maxWidth: '800px' }"
            @hide="closeAttachmentDialog"
        >
            <div v-if="selectedAttachment" class="space-y-4">
                <!-- Image Preview -->
                <div v-if="isImageFile(selectedAttachment.file_type)" class="text-center">
                    <Image 
                        :src="selectedAttachment.file_url" 
                        :alt="selectedAttachment.original_name"
                        class="max-w-full h-auto rounded-lg shadow-lg"
                        preview
                    />
                </div>
                
                <!-- PDF or Document Preview -->
                <div v-else class="text-center">
                    <div class="bg-gray-100 rounded-lg p-8">
                        <i :class="[getFileIcon(selectedAttachment.file_type), getFileIconColor(selectedAttachment.file_type), 'text-6xl mb-4']"></i>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ selectedAttachment.original_name }}</h3>
                        <p class="text-gray-500 mb-4">{{ selectedAttachment.formatted_file_size }}</p>
                        <p class="text-sm text-gray-600 mb-4">This file cannot be previewed in the browser.</p>
                        <Button 
                            @click="downloadAttachment(selectedAttachment)"
                            label="Download File" 
                            icon="pi pi-download" 
                            severity="success"
                        />
                    </div>
                </div>
                
                <!-- File Information -->
                <Divider />
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">File Name:</span>
                        <p class="text-gray-900">{{ selectedAttachment.original_name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">File Size:</span>
                        <p class="text-gray-900">{{ selectedAttachment.formatted_file_size }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">File Type:</span>
                        <p class="text-gray-900">{{ selectedAttachment.file_type }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Uploaded:</span>
                        <p class="text-gray-900">{{ selectedAttachment.created_at }}</p>
                    </div>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>
