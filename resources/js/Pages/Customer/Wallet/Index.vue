<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const props = defineProps({
    wallet: Object,
    recentTransactions: Array,
    summary: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Wallet', 'route': '' },
]);

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
    <Head title="My Wallet" />

    <AppLayout>
        <template #title>
            <span>My Wallet</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mx-6 mt-4">
            <!-- Balance Card -->
            <Card class="col-span-1">
                <template #content>
                    <div class="text-center">
                        <i class="pi pi-wallet text-5xl text-primary mb-3"></i>
                        <div class="text-2xl font-bold mb-2">Current Balance</div>
                        <div class="text-4xl font-extrabold text-primary mb-4">
                            {{ formatCurrency(wallet.balance) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Available: {{ formatCurrency(wallet.available_balance) }}
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Quick Actions -->
            <Card class="col-span-2">
                <template #content>
                    <div class="text-xl font-bold mb-4">Quick Actions</div>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('customer:wallet.deposit')">
                            <Button label="Add Money" icon="pi pi-plus" class="p-button-success" />
                        </Link>
                        <Link :href="route('customer:wallet.withdrawal')">
                            <Button label="Withdraw" icon="pi pi-arrow-up" class="p-button-warning" />
                        </Link>
                        <Link :href="route('customer:wallet.transactions')">
                            <Button label="View Transactions" icon="pi pi-list" class="p-button-info" />
                        </Link>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mx-6 mt-4">
            <Card>
                <template #content>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Credits (30 days)</div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ formatCurrency(summary.total_credits) }}
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Debits (30 days)</div>
                        <div class="text-2xl font-bold text-red-600">
                            {{ formatCurrency(summary.total_debits) }}
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Net Change (30 days)</div>
                        <div class="text-2xl font-bold" :class="summary.net_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(summary.net_change) }}
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Recent Transactions -->
        <Card class="mx-6 mt-4">
            <template #header>
                <div class="flex justify-between items-center p-4">
                    <div class="text-xl font-bold">Recent Transactions</div>
                    <Link :href="route('customer:wallet.transactions')">
                        <Button label="View All" icon="pi pi-arrow-right" class="p-button-text" />
                    </Link>
                </div>
            </template>
            <template #content>
                <DataTable :value="recentTransactions" :paginator="false" class="p-datatable-sm">
                    <Column field="transaction_id" header="Transaction ID">
                        <template #body="slotProps">
                            <span class="font-mono text-sm">{{ slotProps.data.transaction_id }}</span>
                        </template>
                    </Column>
                    <Column field="type" header="Type">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.type.toUpperCase()" 
                                 :severity="getTypeSeverity(slotProps.data.type)" />
                        </template>
                    </Column>
                    <Column field="category" header="Category">
                        <template #body="slotProps">
                            {{ getCategoryLabel(slotProps.data.category) }}
                        </template>
                    </Column>
                    <Column field="amount" header="Amount">
                        <template #body="slotProps">
                            <span :class="slotProps.data.type === 'credit' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                                {{ slotProps.data.type === 'credit' ? '+' : '-' }}{{ formatCurrency(slotProps.data.amount) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="description" header="Description" />
                    <Column field="created_at" header="Date">
                        <template #body="slotProps">
                            {{ new Date(slotProps.data.created_at).toLocaleDateString() }}
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AppLayout>
</template>

