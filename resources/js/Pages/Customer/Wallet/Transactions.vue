<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';

const props = defineProps({
    wallet: Object,
    transactions: Object,
    filters: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Wallet', 'route': route('customer:wallet.index') },
    { 'value': 'Transactions', 'route': '' },
]);

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Credit', value: 'credit' },
    { label: 'Debit', value: 'debit' },
];

const categoryOptions = [
    { label: 'All Categories', value: null },
    { label: 'Deposit', value: 'deposit' },
    { label: 'Withdrawal', value: 'withdrawal' },
    { label: 'Payment', value: 'payment' },
    { label: 'Refund', value: 'refund' },
];

const filters = ref({
    type: props.filters?.type || null,
    category: props.filters?.category || null,
    date_from: props.filters?.date_from ? new Date(props.filters.date_from) : null,
    date_to: props.filters?.date_to ? new Date(props.filters.date_to) : null,
});

const applyFilters = () => {
    router.get(route('customer:wallet.transactions'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        type: null,
        category: null,
        date_from: null,
        date_to: null,
    };
    applyFilters();
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: props.wallet.currency || 'AED'
    }).format(amount);
};

const getTypeSeverity = (type) => {
    return type === 'credit' ? 'success' : 'danger';
};

const getCategoryLabel = (category) => {
    const labels = {
        'deposit': 'Deposit',
        'withdrawal': 'Withdrawal',
        'payment': 'Payment',
        'refund': 'Refund',
        'bonus': 'Bonus',
    };
    return labels[category] || category;
};
</script>

<template>
    <Head title="Wallet Transactions" />

    <AppLayout>
        <template #title>
            <span>Wallet Transactions</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <Card class="mx-6 mt-4">
            <template #header>
                <div class="p-4">
                    <div class="text-xl font-bold mb-4">Filter Transactions</div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Type</label>
                            <Dropdown 
                                v-model="filters.type" 
                                :options="typeOptions" 
                                optionLabel="label" 
                                optionValue="value"
                                placeholder="Select Type"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Category</label>
                            <Dropdown 
                                v-model="filters.category" 
                                :options="categoryOptions" 
                                optionLabel="label" 
                                optionValue="value"
                                placeholder="Select Category"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Date From</label>
                            <Calendar 
                                v-model="filters.date_from" 
                                dateFormat="yy-mm-dd"
                                placeholder="Select Date"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Date To</label>
                            <Calendar 
                                v-model="filters.date_to" 
                                dateFormat="yy-mm-dd"
                                placeholder="Select Date"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <Button label="Apply Filters" icon="pi pi-filter" @click="applyFilters" />
                        <Button label="Clear" icon="pi pi-times" class="p-button-outlined" @click="clearFilters" />
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable :value="transactions.data" :paginator="true" :rows="transactions.per_page" 
                          :totalRecords="transactions.total" :lazy="true"
                          @page="(event) => router.get(transactions.links[event.page + 1].url, {}, { preserveState: true })">
                    <Column field="transaction_id" header="Transaction ID" sortable>
                        <template #body="slotProps">
                            <span class="font-mono text-sm">{{ slotProps.data.transaction_id }}</span>
                        </template>
                    </Column>
                    <Column field="type" header="Type" sortable>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.type.toUpperCase()" 
                                 :severity="getTypeSeverity(slotProps.data.type)" />
                        </template>
                    </Column>
                    <Column field="category" header="Category" sortable>
                        <template #body="slotProps">
                            {{ getCategoryLabel(slotProps.data.category) }}
                        </template>
                    </Column>
                    <Column field="amount" header="Amount" sortable>
                        <template #body="slotProps">
                            <span :class="slotProps.data.type === 'credit' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                                {{ slotProps.data.type === 'credit' ? '+' : '-' }}{{ formatCurrency(slotProps.data.amount) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="balance_after" header="Balance After" sortable>
                        <template #body="slotProps">
                            {{ formatCurrency(slotProps.data.balance_after) }}
                        </template>
                    </Column>
                    <Column field="description" header="Description" />
                    <Column field="created_at" header="Date" sortable>
                        <template #body="slotProps">
                            {{ new Date(slotProps.data.created_at).toLocaleString() }}
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppLayout>
</template>

