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
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    serviceItems: Object,
    subServices: Array,
    subServiceId: [Number, String]
});

const deleteModal = ref(null);
const page = usePage();
const subServiceId = ref(props.subServiceId);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Service Items', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

function deleteServiceItem(data) {
    deleteModal.value.showModal(route('admin:service-items.destroy', [data.id]));
}

function createServiceItem() {
    const params = props.subServiceId ? { sub_service_id: props.subServiceId } : {};
    router.get(route('admin:service-items.create'), params);
}

function editServiceItem(data) {
    router.get(route('admin:service-items.edit', [data.id]));
}

function showServiceItemDetails(data) {
    router.get(route('admin:service-items.show', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:service-items.change_status", { serviceItem: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function filterBySubService() {
    if (subServiceId.value) {
        router.get(route('admin:service-items.index', { sub_service_id: subServiceId.value }));
    } else {
        router.get(route('admin:service-items.index'));
    }
}

const loading = ref(false);
</script>

<template>
    <Head title="Service Items" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Service Items</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <div class="mb-4">
                <label class="block font-bold mb-2">Filter by Sub Service</label>
                <Dropdown v-model="subServiceId" :options="subServiceOptions" optionLabel="label" optionValue="value" 
                    placeholder="All Sub Services" @change="filterBySubService" class="w-full md:w-64" />
            </div>
            <DataTable ref="dt" :value="props.serviceItems" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="New Service Item" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createServiceItem()" />
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
                        <img v-if="data.image" :src="`/storage/${data.image}`" alt="Service Item Image" class="w-16 h-16 object-cover rounded" />
                        <span v-else class="text-gray-400">No Image</span>
                    </template>
                </Column>
                <Column field="sub_service.service.name" header="Service" sortable>
                    <template #body="{ data }">
                        {{ data.sub_service?.service?.name || 'N/A' }}
                    </template>
                </Column>
                <Column field="sub_service.name" header="Sub Service" sortable>
                    <template #body="{ data }">
                        {{ data.sub_service?.name || 'N/A' }}
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
                <Column field="price" header="Price" sortable>
                    <template #body="{ data }">
                        {{ data.price ? `AED ${parseFloat(data.price).toFixed(2)}` : 'N/A' }}
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
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showServiceItemDetails(data)" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editServiceItem(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteServiceItem(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

