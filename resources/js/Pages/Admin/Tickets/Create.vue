<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import ToggleSwitch from 'primevue/toggleswitch';
import axios from 'axios';

const props = defineProps({
    customers: Array,
    partners: Array,
    services: Array
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Complaints', 'route': route('admin:tickets.index') },
    { 'value': 'Create New', 'route': '' },
]);

// Form setup
const form = useForm({
    customer_id: '',
    service_id: '',
    sub_service_id: '',
    title: '',
    description: '',
    items: [],
    is_urgent: false,
    scheduled_date_time: null,
    address: '',
    location: '',
    latitude: null,
    longitude: null,
    total_amount: null,
    payment_method: '',
    category: '',
    priority: 'medium',
    assigned_partner_id: '',
    admin_notes: '',
    attachments: []
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

// Service options
const serviceOptions = props.services.map(service => ({
    label: service.name,
    value: service.id
}));

// Dynamic data
const subServices = ref([]);
const serviceItems = ref([]);
const loadingSubServices = ref(false);
const loadingServiceItems = ref(false);

// Watch for service changes
watch(() => form.service_id, async (newServiceId) => {
    form.sub_service_id = '';
    form.items = [];
    serviceItems.value = [];
    
    if (newServiceId) {
        loadingSubServices.value = true;
        try {
            const response = await axios.get('/admin/tickets/get-sub-services', {
                params: { service_id: newServiceId }
            });
            subServices.value = response.data.map(sub => ({
                label: sub.name,
                value: sub.id
            }));
        } catch (error) {
            console.error('Error fetching sub-services:', error);
        } finally {
            loadingSubServices.value = false;
        }
    } else {
        subServices.value = [];
    }
});

// Watch for sub-service changes
watch(() => form.sub_service_id, async (newSubServiceId) => {
    form.items = [];
    
    if (newSubServiceId) {
        loadingServiceItems.value = true;
        try {
            const response = await axios.get('/admin/tickets/get-service-items', {
                params: { sub_service_id: newSubServiceId }
            });
            serviceItems.value = response.data.map(item => ({
                label: item.name,
                value: item.id,
                price: item.price
            }));
        } catch (error) {
            console.error('Error fetching service items:', error);
        } finally {
            loadingServiceItems.value = false;
        }
    } else {
        serviceItems.value = [];
    }
});

// Item management
const addItem = () => {
    form.items.push({
        service_item_id: '',
        quantity: 1
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

// File handling
const selectedFiles = ref([]);
const fileInput = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    selectedFiles.value = [...selectedFiles.value, ...files];
    form.attachments = selectedFiles.value;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.attachments = selectedFiles.value;
};

// Get item price for display
const getItemPrice = (itemId) => {
    const item = serviceItems.value.find(i => i.value === itemId);
    return item ? item.price : null;
};

const submit = () => {
    form.post(route('admin:tickets.store'), {
        forceFormData: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Create New Complaint" />
    <AppLayout>
        <template #title>
            <span>Create New Complaint</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        
        <div class="mt-4 mx-6">
            <div class="max-w-5xl mx-auto">
                <Card class="mb-6">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <i class="pi pi-exclamation-triangle text-orange-500 text-2xl"></i>
                            <span>Create New Complaint</span>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-gray-600 mb-0">
                            Create a new complaint on behalf of a customer. Fill in all required information to proceed.
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

                            <!-- Service Selection -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-wrench text-green-500"></i>
                                    Service Selection
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Select Service *
                                        </label>
                                        <Dropdown
                                            id="service_id"
                                            v-model="form.service_id"
                                            :options="serviceOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Choose a service"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.service_id }"
                                            :loading="loadingSubServices"
                                            required
                                        />
                                        <small v-if="form.errors.service_id" class="p-error">{{ form.errors.service_id }}</small>
                                    </div>

                                    <div>
                                        <label for="sub_service_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Select Sub Service *
                                        </label>
                                        <Dropdown
                                            id="sub_service_id"
                                            v-model="form.sub_service_id"
                                            :options="subServices"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Choose a sub service"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.sub_service_id }"
                                            :disabled="!form.service_id || loadingSubServices"
                                            :loading="loadingSubServices"
                                            required
                                        />
                                        <small v-if="form.errors.sub_service_id" class="p-error">{{ form.errors.sub_service_id }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- Service Items -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-box text-purple-500"></i>
                                    Service Items (Optional)
                                </h3>
                                
                                <div v-for="(item, index) in form.items" :key="index" class="mb-4 p-4 border border-gray-200 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <label :for="`item_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Item *
                                            </label>
                                            <Dropdown
                                                :id="`item_${index}`"
                                                v-model="item.service_item_id"
                                                :options="serviceItems"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Select an item"
                                                class="w-full"
                                                :disabled="!form.sub_service_id || loadingServiceItems"
                                            />
                                        </div>
                                        <div>
                                            <label :for="`quantity_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Quantity *
                                            </label>
                                            <InputNumber
                                                :id="`quantity_${index}`"
                                                v-model="item.quantity"
                                                :min="1"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                    <div v-if="getItemPrice(item.service_item_id)" class="mt-2 text-sm text-gray-600">
                                        Price: ₹{{ getItemPrice(item.service_item_id) }}
                                    </div>
                                    <Button
                                        type="button"
                                        @click="removeItem(index)"
                                        icon="pi pi-times"
                                        severity="danger"
                                        text
                                        rounded
                                        size="small"
                                        class="mt-2"
                                        label="Remove"
                                    />
                                </div>

                                <Button
                                    type="button"
                                    @click="addItem"
                                    icon="pi pi-plus"
                                    label="Add Item"
                                    severity="secondary"
                                    outlined
                                    :disabled="!form.sub_service_id || loadingServiceItems"
                                />
                            </div>

                            <Divider />

                            <!-- Complaint Details -->
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

                                <div class="mb-4">
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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                            Category
                                        </label>
                                        <Dropdown
                                            id="category"
                                            v-model="form.category"
                                            :options="categories"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Select a category (optional)"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.category }"
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
                                            :disabled="form.is_urgent"
                                            required
                                        />
                                        <small v-if="form.errors.priority" class="p-error">{{ form.errors.priority }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- Scheduling & Urgency -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-calendar text-indigo-500"></i>
                                    Scheduling & Urgency
                                </h3>
                                
                                <div class="mb-4">
                                    <div class="flex items-center gap-3 mb-4">
                                        <ToggleSwitch v-model="form.is_urgent" />
                                        <label class="text-sm font-medium text-gray-700">
                                            Urgently Required
                                        </label>
                                    </div>
                                    <small class="text-gray-500 block mb-4">
                                        <i class="pi pi-info-circle mr-1"></i>
                                        If checked, the complaint will be marked as urgent and priority will be set to urgent automatically.
                                    </small>
                                </div>

                                <div v-if="!form.is_urgent" class="mb-4">
                                    <label for="scheduled_date_time" class="block text-sm font-medium text-gray-700 mb-2">
                                        Scheduled Date & Time
                                    </label>
                                    <Calendar
                                        id="scheduled_date_time"
                                        v-model="form.scheduled_date_time"
                                        showTime
                                        hourFormat="12"
                                        dateFormat="dd/mm/yy"
                                        placeholder="Select date and time"
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.scheduled_date_time }"
                                    />
                                    <small v-if="form.errors.scheduled_date_time" class="p-error">{{ form.errors.scheduled_date_time }}</small>
                                </div>
                            </div>

                            <Divider />

                            <!-- Address & Location -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-map-marker text-red-500"></i>
                                    Address & Location
                                </h3>
                                
                                <div class="mb-4">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                        Address *
                                    </label>
                                    <Textarea
                                        id="address"
                                        v-model="form.address"
                                        rows="3"
                                        placeholder="Enter the complete address"
                                        class="w-full"
                                        :class="{ 'p-invalid': form.errors.address }"
                                        required
                                    />
                                    <small v-if="form.errors.address" class="p-error">{{ form.errors.address }}</small>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                            Location Name
                                        </label>
                                        <InputText
                                            id="location"
                                            v-model="form.location"
                                            placeholder="e.g., Office, Home, etc."
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.location }"
                                        />
                                        <small v-if="form.errors.location" class="p-error">{{ form.errors.location }}</small>
                                    </div>

                                    <div>
                                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                            Latitude
                                        </label>
                                        <InputNumber
                                            id="latitude"
                                            v-model="form.latitude"
                                            :min="-90"
                                            :max="90"
                                            :minFractionDigits="6"
                                            :maxFractionDigits="8"
                                            placeholder="Latitude"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.latitude }"
                                        />
                                        <small v-if="form.errors.latitude" class="p-error">{{ form.errors.latitude }}</small>
                                    </div>

                                    <div>
                                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                            Longitude
                                        </label>
                                        <InputNumber
                                            id="longitude"
                                            v-model="form.longitude"
                                            :min="-180"
                                            :max="180"
                                            :minFractionDigits="6"
                                            :maxFractionDigits="8"
                                            placeholder="Longitude"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.longitude }"
                                        />
                                        <small v-if="form.errors.longitude" class="p-error">{{ form.errors.longitude }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- Payment Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-money-bill text-green-500"></i>
                                    Payment Information
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                            Total Amount
                                        </label>
                                        <InputNumber
                                            id="total_amount"
                                            v-model="form.total_amount"
                                            :min="0"
                                            :minFractionDigits="2"
                                            :maxFractionDigits="2"
                                            placeholder="Enter total amount"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.total_amount }"
                                        />
                                        <small v-if="form.errors.total_amount" class="p-error">{{ form.errors.total_amount }}</small>
                                    </div>

                                    <div>
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                            Payment Method
                                        </label>
                                        <Dropdown
                                            id="payment_method"
                                            v-model="form.payment_method"
                                            :options="[
                                                { label: 'Wallet', value: 'WALLET' },
                                                { label: 'Cash on Delivery', value: 'COD' }
                                            ]"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Select payment method"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors.payment_method }"
                                        />
                                        <small v-if="form.errors.payment_method" class="p-error">{{ form.errors.payment_method }}</small>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <!-- File Attachments -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="pi pi-paperclip text-indigo-500"></i>
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
                                        @click="fileInput.click()"
                                        icon="pi pi-upload"
                                        label="Choose Files"
                                        severity="secondary"
                                        outlined
                                        class="mb-3"
                                    />
                                    <p class="text-sm text-gray-500 mb-0">
                                        <i class="pi pi-info-circle mr-1"></i>
                                        Upload photos or documents related to the complaint. You can upload up to 5 files (images, PDFs, documents). Max 10MB per file.
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
                                    This complaint will be created and can be managed from the complaints list
                                </div>
                                
                                <div class="flex gap-3">
                                    <Link :href="route('admin:tickets.index')">
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
                                        label="Create Complaint"
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
