<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { onMounted, ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import ToggleSwitch from 'primevue/toggleswitch';

import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';

import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

import InputText from 'primevue/inputtext';
import { Toolbar } from 'primevue';


const props = defineProps(
    {
        roles: Object,
    }
);

const page = usePage()
const deleteModal = ref(null);

function createRole() {
  router.visit(route('roles.create'));
}

function editRole(data) {
    router.visit(route('roles.edit',[data.id]));
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});


const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Roles', 'route': '' },

])

function deleteRole(data) {

    deleteModal.value.showModal(route('roles.destroy', [data.id]));
}




const loading = ref(false)

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>

   
        <DeleteConfirmationModal ref="deleteModal" />

        <template #title>
           <span>Roles</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-2">

            <DataTable ref="dt" :value="props.roles" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                        <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="createRole()" v-if="page.props.auth.permissions.includes('Create Roles')"/>
                    </div>
                </template>

                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Name" sortable style="min-width: 24rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
            

                <Column :exportable="false" field="actions" header="Actions" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" variant="text"  class="mr-2" size="medium" @click="editRole(data)" v-if="page.props.auth.permissions.includes('Edit Roles')"/>
                        <Button icon="pi pi-trash" variant="text"  severity="danger" size="medium" @click="deleteRole(data)" v-if="page.props.auth.permissions.includes('Delete Roles')"/>
                    </template>
                </Column>
            </DataTable>
        </div>

    </AppLayout>
</template>
