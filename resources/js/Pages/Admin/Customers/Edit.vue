<script setup>

import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea } from 'primevue';

const props = defineProps({
    customer: Object
});

const loading = ref(false);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Customers', 'route': route('admin:customers.index') },
    { 'value': 'Edit Customer', 'route': '' },

])

const form = useForm({
    name: props.customer.name,
    email: props.customer.email,
    password: '',
    password_confirmation: '',
    phone: props.customer.phone,
    address: props.customer.address,
    status: props.customer.status
});


function update() {

    loading.value = true;
    form.put(route('admin:customers.update', [props.customer.id]), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:customers.index'))
        },
        onError: () => {
            loading.value = false;
        },
    });
}

</script>

<template>

    <Head title="Edit Customer" />

    <AppLayout>


        <template #title>
            <span>Edit Customer</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">

            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="name" class="block font-bold mb-1">Name</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="form.errors.name" fluid />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name
                        }}</small>
                </div>
                <div class="w-full">
                    <label for="email" class="block font-bold mb-1">Email</label>
                    <InputText id="email" v-model.trim="form.email" required="true" autofocus
                        :invalid="form.errors.email" fluid />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="password" class="block font-bold mb-1">Password (Leave blank to keep current)</label>
                    <InputText id="password" type="password" v-model.trim="form.password"
                        :invalid="form.errors.password" fluid />
                    <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                </div>
                <div class="w-full">
                    <label for="password_confirmation" class="block font-bold mb-1">Confirm Password</label>
                    <InputText id="password_confirmation" type="password" v-model.trim="form.password_confirmation"
                        :invalid="form.errors.password_confirmation" fluid />
                    <small v-if="form.errors.password_confirmation" class="text-red-500">{{ form.errors.password_confirmation }}</small>
                </div>
            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="phone" class="block font-bold mb-1">Phone</label>
                    <InputText id="phone" v-model.trim="form.phone" required="true" autofocus
                        :invalid="form.errors.phone" fluid />
                    <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="address" class="block font-bold mb-1">Address</label>
                    <Textarea id="address" v-model.trim="form.address" required="true" autofocus
                        :invalid="form.errors.address" fluid rows="3" />
                    <small v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                </div>

            </div>

            <div class="flex justify-end mt-4">
                <Button label="Update" @click="update()" :loading="loading" />
            </div>

        </div>
    </AppLayout>
</template>

