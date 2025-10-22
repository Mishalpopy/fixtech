<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea, Select, FileUpload } from 'primevue';

const loading = ref(false);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Partners', 'route': route('admin:partners.index') },
    { 'value': 'Create Partner', 'route': '' },
]);

const approvalStatuses = ref([
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' }
]);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    company_name: '',
    trade_license_no: '',
    status: true,
    approval_status: 'pending',
    emirates_id: null
});

function onEmiratesIdSelect(event) {
    form.emirates_id = event.files[0];
}

function save() {
    loading.value = true;
    form.post(route('admin:partners.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:partners.index'));
        },
        onError: () => {
            loading.value = false;
        },
    });
}

</script>

<template>
    <Head title="Create Partner" />

    <AppLayout>
        <template #title>
            <span>Create Partner</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="name" class="block font-bold mb-1">Name *</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="form.errors.name" fluid />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>
                <div class="w-full">
                    <label for="email" class="block font-bold mb-1">Email *</label>
                    <InputText id="email" v-model.trim="form.email" required="true"
                        :invalid="form.errors.email" fluid />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>
            </div>

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="password" class="block font-bold mb-1">Password *</label>
                    <InputText id="password" type="password" v-model.trim="form.password" required="true"
                        :invalid="form.errors.password" fluid />
                    <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                </div>
                <div class="w-full">
                    <label for="password_confirmation" class="block font-bold mb-1">Confirm Password *</label>
                    <InputText id="password_confirmation" type="password" v-model.trim="form.password_confirmation" 
                        required="true" :invalid="form.errors.password_confirmation" fluid />
                    <small v-if="form.errors.password_confirmation" class="text-red-500">{{ form.errors.password_confirmation }}</small>
                </div>
            </div>

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="phone" class="block font-bold mb-1">Phone *</label>
                    <InputText id="phone" v-model.trim="form.phone" required="true"
                        :invalid="form.errors.phone" fluid />
                    <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                </div>
                <div class="w-full">
                    <label for="company_name" class="block font-bold mb-1">Company Name</label>
                    <InputText id="company_name" v-model.trim="form.company_name"
                        :invalid="form.errors.company_name" fluid />
                    <small v-if="form.errors.company_name" class="text-red-500">{{ form.errors.company_name }}</small>
                </div>
            </div>

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="trade_license_no" class="block font-bold mb-1">Trade License No</label>
                    <InputText id="trade_license_no" v-model.trim="form.trade_license_no"
                        :invalid="form.errors.trade_license_no" fluid />
                    <small v-if="form.errors.trade_license_no" class="text-red-500">{{ form.errors.trade_license_no }}</small>
                </div>
                <div class="w-full">
                    <label for="approval_status" class="block font-bold mb-1">Approval Status</label>
                    <Select v-model="form.approval_status" :options="approvalStatuses" optionLabel="label" 
                        optionValue="value" placeholder="Select Status" class="w-full" />
                    <small v-if="form.errors.approval_status" class="text-red-500">{{ form.errors.approval_status }}</small>
                </div>
            </div>

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="address" class="block font-bold mb-1">Address *</label>
                    <Textarea id="address" v-model.trim="form.address" required="true"
                        :invalid="form.errors.address" fluid rows="3" />
                    <small v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                </div>
            </div>

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="emirates_id" class="block font-bold mb-1">Emirates ID (Required) *</label>
                    <FileUpload mode="basic" accept="image/*,application/pdf" :maxFileSize="5242880"
                        @select="onEmiratesIdSelect" chooseLabel="Choose File" class="w-full" />
                    <small class="text-gray-500">Accepted formats: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                    <small v-if="form.errors.emirates_id" class="block text-red-500">{{ form.errors.emirates_id }}</small>
                </div>
            </div>

            <div class="flex justify-end mt-4 gap-2">
                <Button label="Cancel" severity="secondary" @click="router.get(route('admin:partners.index'))" />
                <Button label="Save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

