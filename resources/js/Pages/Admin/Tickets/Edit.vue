<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';
import Divider from 'primevue/divider';

const props = defineProps({
    ticket: Object,
    customers: Array,
    partners: Array
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Complaints', 'route': route('admin:tickets.index') },
    { 'value': `Complaint #${props.ticket.ticket_number}`, 'route': route('admin:tickets.show', props.ticket.id) },
    { 'value': 'Edit', 'route': '' },
]);

// Form setup
const form = useForm({
    customer_id: props.ticket.customer_id,
    title: props.ticket.title,
    description: props.ticket.description,
    category: props.ticket.category,
    priority: props.ticket.priority,
    status: props.ticket.status,
    assigned_partner_id: props.ticket.assigned_partner_id || '',
    admin_notes: props.ticket.admin_notes || ''
});

// Options
const categories = [
    { label: 'Plumbing', value: 'plumbing' },
    { label: 'Electrical', value: 'electrical' },
    { label: 'HVAC', value: 'hvac' },
    { label: 'Appliance', value: 'appliance' },
    { label: 'General', value: 'general' },
    { label: 'Other', value: 'other' }
];

const priorities = [
    { label: 'Low', value: 'low' },
    { label: 'Medium', value: 'medium' },
    { label: 'High', value: 'high' },
    { label: 'Urgent', value: 'urgent' }
];

const statusOptions = [
    { label: 'Open', value: 'open' },
    { label: 'Assigned', value: 'assigned' },
    { label: 'In Progress', value: 'in_progress' },
    { label: 'Resolved', value: 'resolved' },
    { label: 'Closed', value: 'closed' },
    { label: 'Cancelled', value: 'cancelled' }
];

// Customer options
const customerOptions = props.customers.map(customer => ({
    label: `${customer.name} (${customer.email})`,
    value: customer.id
}));

// Partner options
const partnerOptions = [
    { label: 'Not Assigned', value: '' },
    ...props.partners.map(partner => ({
        label: `${partner.name} (${partner.email})`,
        value: partner.id
    }))
];

const submit = () => {
    form.put(route('admin:tickets.update', props.ticket.id));
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
        
        <div class="mt-4 mx-6">
            <div class="max-w-4xl mx-auto">
                <Card class="mb-6">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <i class="pi pi-pencil text-blue-500 text-2xl"></i>
                            <span>Edit Complaint</span>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-gray-600 mb-0">
                            Update the complaint details, assignment, and status. All changes will be logged.
                        </p>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Customer Selection -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-user text-blue-500"></i>
                                    Customer Selection
                                </h3>
                                
                                <div class="mb-4">
                                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Select Customer *
                                    </label>
                                    <Dropdown
                                        id="customer_id"
                                        v-model="form.customer_id"
                                        :options="customerOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Choose a customer"
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.customer_id }"
                                        required
                                    />
                                    <small v-if="form.errors.customer_id" class="p-error">{{ form.errors.customer_id }}</small>
                                </div>
                            </div>

                            <Divider />

                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-info-circle text-green-500"></i>
                                    Complaint Details
                                </h3>
                                
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

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                            Status *
                                        </label>
                                        <Dropdown
                                            id="status"
                                            v-model="form.status"
                                            :options="statusOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Select status"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.status }"
                                            required
                                        />
                                        <small v-if="form.errors.status" class="p-error">{{ form.errors.status }}</small>
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Detailed Description *
                                    </label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="6"
                                        placeholder="Please provide a detailed description of the complaint..."
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.description }"
                                        required
                                    />
                                    <small v-if="form.errors.description" class="p-error">{{ form.errors.description }}</small>
                                </div>
                            </div>

                            <Divider />

                            <!-- Assignment & Notes -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-users text-purple-500"></i>
                                    Assignment & Notes
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="assigned_partner_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Assign to Partner
                                        </label>
                                        <Dropdown
                                            id="assigned_partner_id"
                                            v-model="form.assigned_partner_id"
                                            :options="partnerOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Choose a partner (optional)"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.assigned_partner_id }"
                                        />
                                        <small v-if="form.errors.assigned_partner_id" class="p-error">{{ form.errors.assigned_partner_id }}</small>
                                        <small class="text-gray-500 mt-1 block">
                                            <i class="pi pi-info-circle mr-1"></i>
                                            Assign this complaint to a partner or leave it unassigned.
                                        </small>
                                    </div>

                                    <div>
                                        <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                            Admin Notes
                                        </label>
                                        <Textarea
                                            id="admin_notes"
                                            v-model="form.admin_notes"
                                            rows="3"
                                            placeholder="Add any internal notes about this complaint..."
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.admin_notes }"
                                        />
                                        <small v-if="form.errors.admin_notes" class="p-error">{{ form.errors.admin_notes }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- Submit Section -->
                            <div class="flex justify-between items-center pt-4">
                                <div class="text-sm text-gray-500">
                                    <i class="pi pi-shield mr-1"></i>
                                    All changes will be saved and logged
                                </div>
                                
                                <div class="flex gap-3">
                                    <Link :href="route('admin:tickets.show', ticket.id)">
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
                                        label="Update Complaint"
                                        icon="pi pi-save"
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
