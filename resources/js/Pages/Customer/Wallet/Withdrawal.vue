<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';

const props = defineProps({
    wallet: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Wallet', 'route': route('customer:wallet.index') },
    { 'value': 'Withdraw', 'route': '' },
]);

const form = useForm({
    amount: null,
    bank_account: '',
    bank_name: '',
    account_holder_name: '',
    description: ''
});

const submit = () => {
    form.post(route('customer:wallet.withdrawal.process'));
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: props.wallet.currency || 'AED'
    }).format(amount);
};
</script>

<template>
    <Head title="Withdraw from Wallet" />

    <AppLayout>
        <template #title>
            <span>Withdraw from Wallet</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mx-6 mt-4">
            <!-- Withdrawal Form -->
            <Card class="col-span-1">
                <template #header>
                    <div class="p-4 text-xl font-bold">Withdrawal Request</div>
                </template>
                <template #content>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Amount ({{ wallet.currency }})</label>
                            <InputNumber 
                                v-model="form.amount" 
                                :min="1" 
                                :max="wallet.available_balance"
                                :useGrouping="false"
                                :minFractionDigits="2"
                                :maxFractionDigits="2"
                                placeholder="Enter amount"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.amount }"
                            />
                            <small v-if="form.errors.amount" class="p-error">{{ form.errors.amount }}</small>
                            <small class="text-gray-600 dark:text-gray-400">
                                Available: {{ formatCurrency(wallet.available_balance) }}
                            </small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Bank Name *</label>
                            <InputText 
                                v-model="form.bank_name" 
                                placeholder="Enter bank name"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.bank_name }"
                            />
                            <small v-if="form.errors.bank_name" class="p-error">{{ form.errors.bank_name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Account Number *</label>
                            <InputText 
                                v-model="form.bank_account" 
                                placeholder="Enter account number"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.bank_account }"
                            />
                            <small v-if="form.errors.bank_account" class="p-error">{{ form.errors.bank_account }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Account Holder Name *</label>
                            <InputText 
                                v-model="form.account_holder_name" 
                                placeholder="Enter account holder name"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.account_holder_name }"
                            />
                            <small v-if="form.errors.account_holder_name" class="p-error">{{ form.errors.account_holder_name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Description (Optional)</label>
                            <Textarea 
                                v-model="form.description" 
                                placeholder="Additional notes"
                                rows="3"
                                class="w-full"
                            />
                        </div>

                        <div class="pt-4">
                            <Button 
                                type="submit" 
                                label="Submit Withdrawal Request" 
                                icon="pi pi-send" 
                                class="w-full"
                                :loading="form.processing"
                                :disabled="!form.amount || form.amount > wallet.available_balance"
                            />
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Wallet Info -->
            <Card class="col-span-1">
                <template #header>
                    <div class="p-4 text-xl font-bold">Wallet Information</div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Current Balance</div>
                            <div class="text-3xl font-bold text-primary">
                                {{ formatCurrency(wallet.balance) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Available Balance</div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(wallet.available_balance) }}
                            </div>
                        </div>
                        <div class="pt-4 border-t">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Processing Time</div>
                            <p class="text-sm">Withdrawal requests are typically processed within 1-2 business days.</p>
                        </div>
                        <div class="pt-4 border-t">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Important Notes</div>
                            <ul class="text-sm list-disc list-inside space-y-1">
                                <li>Ensure bank account details are correct</li>
                                <li>Withdrawal amount cannot exceed available balance</li>
                                <li>You will receive a confirmation once processed</li>
                            </ul>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

