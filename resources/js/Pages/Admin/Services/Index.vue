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

const props = defineProps({
    services: Object
});

const deleteModal = ref(null);
const page = usePage();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': '' },
]);

function deleteService(data) {
    deleteModal.value.showModal(route('admin:services.destroy', [data.id]));
}

function createService() {
    router.get(route('admin:services.create'));
}

function editService(data) {
    router.get(route('admin:services.edit', [data.id]));
}

function showServiceDetails(data) {
    router.get(route('admin:services.show', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:services.change_status", { service: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function viewSubServices(data) {
    router.get(route('admin:sub-services.index', { service_id: data.id }));
}

const loading = ref(false);
</script>

<template>
    <Head title="Services" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Services</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <DataTable ref="dt" :value="props.services" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="New Service" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createService()" />
                        </div>
                    </div>
                </template>
                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="image" header="Image">
                    <template #body="{ data }">
                        <img v-if="data.image" :src="`/storage/${data.image}`" alt="Service Image" class="w-16 h-16 object-cover rounded" />
                        <span v-else class="text-gray-400">No Image</span>
                    </template>
                </Column>
                <Column field="name" header="Name" sortable>
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="description" header="Description">
                    <template #body="{ data }">
                        <span class="truncate max-w-xs">{{ data.description || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="created" header="Created" sortable>
                    <template #body="{ data }">
                        {{ data.formatted_created_at }}
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <ToggleSwitch v-model="data.status" @change="updateStatus(data)" />
                    </template>
                </Column>
                <Column :exportable="false" field="actions" header="Actions">
                    <template #body="{ data }">
                        <Button icon="pi pi-list" variant="text" severity="info" size="medium" v-tooltip="'View Sub Services'"
                            @click="viewSubServices(data)" />
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showServiceDetails(data)" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editService(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteService(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

