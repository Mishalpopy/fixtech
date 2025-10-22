<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import Dialog from 'primevue/dialog';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    tickets: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Complaints', 'route': '' },
]);

const confirm = useConfirm();

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

const getPrioritySeverity = (priority) => {
    const severities = {
        'low': 'success',
        'medium': 'info',
        'high': 'warning',
        'urgent': 'danger'
    };
    return severities[priority] || 'info';
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

const getPriorityLabel = (priority) => {
    return priority.toUpperCase();
};

const deleteComplaint = (complaint) => {
    confirm.require({
        message: 'Are you sure you want to delete this complaint?',
        header: 'Confirm Deletion',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route('customer:tickets.destroy', complaint.id));
        }
    });
};

const actionBodyTemplate = (rowData) => {
    return `
        <div class="flex gap-2">
            <a href="${route('customer:tickets.show', rowData.id)}" 
               class="p-button p-button-sm p-button-outlined p-button-info">
                <i class="pi pi-eye"></i>
            </a>
            ${rowData.status === 'open' ? `
                <a href="${route('customer:tickets.edit', rowData.id)}" 
                   class="p-button p-button-sm p-button-outlined p-button-success">
                    <i class="pi pi-pencil"></i>
                </a>
                <button onclick="deleteComplaint(${rowData.id})" 
                        class="p-button p-button-sm p-button-outlined p-button-danger">
                    <i class="pi pi-trash"></i>
                </button>
            ` : ''}
        </div>
    `;
};

const statusBodyTemplate = (rowData) => {
    return `<p-tag :value="'${getStatusLabel(rowData.status)}'" :severity="'${getStatusSeverity(rowData.status)}'"></p-tag>`;
};

const priorityBodyTemplate = (rowData) => {
    return `<p-tag :value="'${getPriorityLabel(rowData.priority)}'" :severity="'${getPrioritySeverity(rowData.priority)}'"></p-tag>`;
};

const attachmentsBodyTemplate = (rowData) => {
    if (rowData.attachments && rowData.attachments.length > 0) {
        return `<p-badge :value="${rowData.attachments.length}" severity="info"></p-badge>`;
    }
    return '-';
};
</script>

<template>
    <Head title="My Complaints" />

    <AppLayout>
        <template #title>
            <span>My Complaints</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <Toolbar>
                <template #start>
                    <h2 class="text-2xl font-bold text-gray-800">My Complaints</h2>
                </template>
                <template #end>
                    <Link :href="route('customer:tickets.create')">
                        <Button 
                            label="Raise New Complaint" 
                            icon="pi pi-plus" 
                            class="p-button-success"
                            severity="success"
                        />
                    </Link>
                </template>
            </Toolbar>

            <div v-if="tickets.data.length === 0" class="text-center py-12">
                <i class="pi pi-exclamation-triangle text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No complaints found</h3>
                <p class="text-gray-500 mb-6">You haven't raised any complaints yet.</p>
                <Link :href="route('customer:tickets.create')">
                    <Button 
                        label="Raise Your First Complaint" 
                        icon="pi pi-plus" 
                        class="p-button-success"
                        severity="success"
                    />
                </Link>
            </div>

            <div v-else>
                <DataTable 
                    :value="tickets.data" 
                    :paginator="true" 
                    :rows="10"
                    :totalRecords="tickets.total"
                    :lazy="true"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5,10,25,50]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} complaints"
                    responsiveLayout="scroll"
                    class="p-datatable-sm"
                >
                    <Column field="ticket_number" header="Complaint #" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            <span class="font-mono text-sm">{{ slotProps.data.ticket_number }}</span>
                        </template>
                    </Column>

                    <Column field="title" header="Title" :sortable="true" style="min-width: 200px">
                        <template #body="slotProps">
                            <div class="font-semibold text-gray-900">{{ slotProps.data.title }}</div>
                            <div class="text-sm text-gray-500 mt-1 line-clamp-2">{{ slotProps.data.description }}</div>
                        </template>
                    </Column>

                    <Column field="category" header="Category" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            <Tag :value="getCategoryLabel(slotProps.data.category)" severity="info" />
                        </template>
                    </Column>

                    <Column field="priority" header="Priority" :sortable="true" style="min-width: 100px">
                        <template #body="slotProps">
                            <Tag 
                                :value="getPriorityLabel(slotProps.data.priority)" 
                                :severity="getPrioritySeverity(slotProps.data.priority)" 
                            />
                        </template>
                    </Column>

                    <Column field="status" header="Status" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            <Tag 
                                :value="getStatusLabel(slotProps.data.status)" 
                                :severity="getStatusSeverity(slotProps.data.status)" 
                            />
                        </template>
                    </Column>

                    <Column field="attachments" header="Files" style="min-width: 80px">
                        <template #body="slotProps">
                            <span 
                                v-if="slotProps.data.attachments && slotProps.data.attachments.length > 0"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                {{ slotProps.data.attachments.length }} file(s)
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>

                    <Column field="formatted_created_at" header="Created" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            <span class="text-sm">{{ slotProps.data.formatted_created_at }}</span>
                        </template>
                    </Column>

                    <Column header="Actions" style="min-width: 150px">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Link :href="route('customer:tickets.show', slotProps.data.id)">
                                    <Button 
                                        icon="pi pi-eye" 
                                        class="p-button-sm p-button-outlined p-button-info"
                                        title="View Details"
                                    />
                                </Link>
                                
                                <Link v-if="slotProps.data.status === 'open'" :href="route('customer:tickets.edit', slotProps.data.id)">
                                    <Button 
                                        icon="pi pi-pencil" 
                                        class="p-button-sm p-button-outlined p-button-success"
                                        title="Edit Complaint"
                                    />
                                </Link>
                                
                                <Button 
                                    v-if="slotProps.data.status === 'open'"
                                    icon="pi pi-trash" 
                                    class="p-button-sm p-button-outlined p-button-danger"
                                    title="Delete Complaint"
                                    @click="deleteComplaint(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <ConfirmDialog />
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
