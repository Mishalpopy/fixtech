<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import ToggleSwitch from 'primevue/toggleswitch';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';

import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

import InputText from 'primevue/inputtext';


const props = defineProps(
    {
        customers: Object
    }
);
const deleteModal = ref(null);
const page = usePage()

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});


const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Customers', 'route': '' },

])

function deleteCustomer(data) {
    deleteModal.value.showModal(route('admin:customers.destroy', [data.id]));
}


function createCustomer() {
    router.get(route('admin:customers.create'));
}

function editCustomer(data){
    router.get(route('admin:customers.edit',[data.id]));
    
}

function showCustomerDetails(data){
    router.get(route('admin:customers.show',[data.id]));

}

function updateStatus(data) {
    router.post(
        route("admin:customers.change_status", { customer: data.id }),
        {},
        {
            onSuccess: (res) => {

            },
            onError: (err) => { },
        }
    );
}


const loading = ref(false)

</script>

<template>

    <Head title="Customers" />

    <AppLayout>

        <DeleteConfirmationModal ref="deleteModal" />

        <template #title>
            <span>Customers</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-2">


            <DataTable ref="dt" :value="props.customers" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink  RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} Records">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">

                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                        <div>
                            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createCustomer()" v-if="page.props.auth.permissions.includes('Create Customers')" />
                        </div>
                    </div>
                </template>

                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Name" sortable >
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="email" header="Email" sortable >
                    <template #body="{ data }">
                        {{ data.email }}
                    </template>
                </Column>
                <Column field="phone" header="Phone" sortable >
                    <template #body="{ data }">
                        {{ data.phone }}
                    </template>
                </Column>
                <Column field="address" header="Address" sortable >
                    <template #body="{ data }">
                        {{ data.address }}
                    </template>
                </Column>
                <Column field="created" header="Created" sortable >
                    <template #body="{ data }">
                        {{ data.formatted_created_at }}
                    </template>
                </Column>
                <Column field="status" header="Status" >
                    <template #body="{ data }">
                        <ToggleSwitch v-model="data.status" @change="updateStatus(data)" :disabled="!page.props.auth.permissions.includes('Edit Customers')"/>
                    </template>
                </Column>

                <Column :exportable="false" field="actions" header="Actions" >
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showCustomerDetails(data)" v-if="page.props.auth.permissions.includes('View Customers')" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editCustomer(data)" 
                            v-if="page.props.auth.permissions.includes('Edit Customers')" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteCustomer(data)" 
                            v-if="page.props.auth.permissions.includes('Delete Customers')" />
                    </template>
                </Column>
            </DataTable>
        </div>

    </AppLayout>
</template>

