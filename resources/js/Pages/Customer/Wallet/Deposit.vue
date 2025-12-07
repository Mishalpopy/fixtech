<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref, reactive } from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    wallet: Object
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Wallet', 'route': route('customer:wallet.index') },
    { 'value': 'Add Money', 'route': '' },
]);

const form = reactive({
    amount: null,
    processing: false,
    errors: {}
});

const setError = (field, message) => {
    form.errors[field] = message;
};

const quickAmounts = [50, 100, 200, 500, 1000];

const setQuickAmount = (amount) => {
    form.amount = amount;
};

const submit = () => {
    // Clear previous errors
    form.errors = {};
    form.processing = true;

    // Use axios directly to handle the response and redirect manually
    axios.post(route('customer:wallet.deposit.process'), {
        amount: form.amount
    })
    .then(response => {
        form.processing = false;
        if (response.data.success && response.data.payment_url) {
            // Use window.location for full page redirect (bypasses Inertia/CORS)
            window.location.href = response.data.payment_url;
        } else {
            // Handle error
            setError('amount', 'Payment initiation failed. Please try again.');
        }
    })
    .catch(error => {
        form.processing = false;
        if (error.response && error.response.data) {
            if (error.response.data.message) {
                setError('amount', error.response.data.message);
            } else if (error.response.data.errors && error.response.data.errors.amount) {
                setError('amount', error.response.data.errors.amount[0]);
            } else {
                setError('amount', 'Failed to initiate payment. Please try again.');
            }
        } else {
            setError('amount', 'Failed to initiate payment. Please try again.');
        }
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AE', {
        style: 'currency',
        currency: props.wallet.currency || 'AED'
    }).format(amount);
};
</script>

<template>
    <Head title="Add Money to Wallet" />

    <AppLayout>
        <template #title>
            <span>Add Money to Wallet</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mx-6 mt-4">
            <!-- Deposit Form -->
            <Card class="col-span-1">
                <template #header>
                    <div class="p-4 text-xl font-bold">Add Money</div>
                </template>
                <template #content>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Amount ({{ wallet.currency }})</label>
                            <InputNumber 
                                v-model="form.amount" 
                                :min="1" 
                                :max="10000"
                                :useGrouping="false"
                                :minFractionDigits="2"
                                :maxFractionDigits="2"
                                placeholder="Enter amount"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.amount }"
                            />
                            <small v-if="form.errors.amount" class="p-error">{{ form.errors.amount }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Quick Select</label>
                            <div class="flex flex-wrap gap-2">
                                <Button 
                                    v-for="amount in quickAmounts" 
                                    :key="amount"
                                    :label="formatCurrency(amount)"
                                    @click="setQuickAmount(amount)"
                                    class="p-button-outlined"
                                    type="button"
                                />
                            </div>
                        </div>

                        <div class="pt-4">
                            <Button 
                                type="submit" 
                                label="Proceed to Payment" 
                                icon="pi pi-credit-card" 
                                class="w-full"
                                :loading="form.processing"
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
                        <div class="pt-4 border-t">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Payment Methods</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-credit-card text-primary"></i>
                                    <span>Credit/Debit Card</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 border-t">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Security</div>
                            <p class="text-sm">Your payment is processed securely through Paymob. We do not store your card details.</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

