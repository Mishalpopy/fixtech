<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Partner/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';

const props = defineProps({
    tickets: Object,
    filters: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('partner:dashboard') },
    { 'value': 'My Assigned Complaints', 'route': '' },
]);

// Status and Priority functions
const getStatusSeverity = (status) => {
    const severities = {
        'open': 'info',
        'assigned': 'warning',
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

const getStatusLabel = (status) => {
    return status.replace('_', ' ').toUpperCase();
};

const getPriorityLabel = (priority) => {
    return priority.toUpperCase();
};

const getCategoryLabel = (category) => {
    const categories = {
        'plumbing': 'Plumbing',
        'electrical': 'Electrical',
        'hvac': 'HVAC',
        'appliance': 'Appliance',
        'general': 'General',
        'other': 'Other'
    };
    return categories[category] || category;
};

// Filter options
const statusOptions = [
    { label: 'All Status', value: '' },
    { label: 'Open', value: 'open' },
    { label: 'Assigned', value: 'assigned' },
    { label: 'In Progress', value: 'in_progress' },
    { label: 'Resolved', value: 'resolved' },
    { label: 'Closed', value: 'closed' },
    { label: 'Cancelled', value: 'cancelled' }
];

const priorityOptions = [
    { label: 'All Priority', value: '' },
    { label: 'Low', value: 'low' },
    { label: 'Medium', value: 'medium' },
    { label: 'High', value: 'high' },
    { label: 'Urgent', value: 'urgent' }
];

const categoryOptions = [
    { label: 'All Categories', value: '' },
    { label: 'Plumbing', value: 'plumbing' },
    { label: 'Electrical', value: 'electrical' },
    { label: 'HVAC', value: 'hvac' },
    { label: 'Appliance', value: 'appliance' },
    { label: 'General', value: 'general' },
    { label: 'Other', value: 'other' }
];

// Filter state
const filters = ref({
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    category: props.filters?.category || '',
    search: props.filters?.search || ''
});

// Apply filters
const applyFilters = () => {
    router.get(route('partner:tickets.index'), {
        status: filters.value.status,
        priority: filters.value.priority,
        category: filters.value.category,
        search: filters.value.search
    }, {
        preserveState: true,
        replace: true
    });
};

// Clear filters
const clearFilters = () => {
    filters.value = {
        status: '',
        priority: '',
        category: '',
        search: ''
    };
    router.get(route('partner:tickets.index'), {}, {
        preserveState: true,
        replace: true
    });
};
</script>

<template>
    <Head title="My Assigned Complaints" />
    <AppLayout>
        <template #title>
            <span>My Assigned Complaints</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        
        <div class="card mt-4 mx-6">
            <Toolbar>
                <template #start>
                    <h2 class="text-2xl font-bold text-gray-800">My Assigned Complaints</h2>
                </template>
                <template #end>
                    <div class="text-sm text-gray-500">
                        <i class="pi pi-info-circle mr-1"></i>
                        {{ tickets.total }} complaint(s) assigned to you
                    </div>
                </template>
            </Toolbar>

            <!-- Filters -->
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <InputText
                            v-model="filters.search"
                            placeholder="Search complaints..."
                            class="w-full"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <Dropdown
                            v-model="filters.status"
                            :options="statusOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="All Status"
                            class="w-full"
                            @change="applyFilters"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <Dropdown
                            v-model="filters.priority"
                            :options="priorityOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="All Priority"
                            class="w-full"
                            @change="applyFilters"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <Dropdown
                            v-model="filters.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="All Categories"
                            class="w-full"
                            @change="applyFilters"
                        />
                    </div>
                    
                    <div class="flex items-end gap-2">
                        <Button 
                            label="Apply" 
                            icon="pi pi-filter" 
                            @click="applyFilters"
                            severity="info"
                            outlined
                        />
                        <Button 
                            label="Clear" 
                            icon="pi pi-times" 
                            @click="clearFilters"
                            severity="secondary"
                            outlined
                        />
                    </div>
                </div>
            </div>

            <div v-if="tickets.data.length === 0" class="text-center py-12">
                <i class="pi pi-exclamation-triangle text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No complaints assigned</h3>
                <p class="text-gray-500 mb-6">You don't have any complaints assigned to you yet.</p>
                <div class="text-sm text-gray-400">
                    <i class="pi pi-info-circle mr-1"></i>
                    Complaints will appear here once they are assigned to you by an admin.
                </div>
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
                            <span class="font-mono text-sm font-semibold">{{ slotProps.data.ticket_number }}</span>
                        </template>
                    </Column>

                    <Column field="title" header="Title" :sortable="true" style="min-width: 200px">
                        <template #body="slotProps">
                            <div class="font-semibold text-gray-900">{{ slotProps.data.title }}</div>
                            <div class="text-sm text-gray-500 mt-1 line-clamp-2">{{ slotProps.data.description }}</div>
                        </template>
                    </Column>

                    <Column field="customer.name" header="Customer" :sortable="true" style="min-width: 150px">
                        <template #body="slotProps">
                            <div class="font-medium text-gray-900">{{ slotProps.data.customer.name }}</div>
                            <div class="text-sm text-gray-500">{{ slotProps.data.customer.email }}</div>
                            <div v-if="slotProps.data.customer.phone" class="text-xs text-gray-400">
                                <i class="pi pi-phone mr-1"></i>{{ slotProps.data.customer.phone }}
                            </div>
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

                    <Column field="formatted_created_at" header="Assigned" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            <span class="text-sm">{{ slotProps.data.formatted_created_at }}</span>
                        </template>
                    </Column>

                    <Column header="Actions" style="min-width: 120px">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Link :href="route('partner:tickets.show', slotProps.data.id)">
                                    <Button 
                                        icon="pi pi-eye" 
                                        class="p-button-sm p-button-outlined p-button-info"
                                        title="View Details"
                                    />
                                </Link>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
