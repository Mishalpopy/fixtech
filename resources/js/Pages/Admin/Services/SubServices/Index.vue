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
    subServices: Object,
    services: Array,
    serviceId: [Number, String]
});

const deleteModal = ref(null);
const page = usePage();
const serviceId = ref(props.serviceId);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Services', 'route': route('admin:services.index') },
    { 'value': 'Sub Services', 'route': '' },
]);

const serviceOptions = props.services.map(s => ({ label: s.name, value: s.id }));

function deleteSubService(data) {
    deleteModal.value.showModal(route('admin:sub-services.destroy', [data.id]));
}

function createSubService() {
    const params = props.serviceId ? { service_id: props.serviceId } : {};
    router.get(route('admin:sub-services.create'), params);
}

function editSubService(data) {
    router.get(route('admin:sub-services.edit', [data.id]));
}

function showSubServiceDetails(data) {
    router.get(route('admin:sub-services.show', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:sub-services.change_status", { subService: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function viewServiceItems(data) {
    router.get(route('admin:service-items.index', { sub_service_id: data.id }));
}

function viewProcesses(data) {
    router.get(route('admin:processes.index', { sub_service_id: data.id }));
}

function viewPriceCharts(data) {
    router.get(route('admin:price-charts.index', { sub_service_id: data.id }));
}

function viewFaqs(data) {
    router.get(route('admin:faqs.index', { sub_service_id: data.id }));
}

function filterByService() {
    if (serviceId.value) {
        router.get(route('admin:sub-services.index', { service_id: serviceId.value }));
    } else {
        router.get(route('admin:sub-services.index'));
    }
}

const loading = ref(false);
</script>

<template>
    <Head title="Sub Services" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Sub Services</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <div class="mb-4">
                <label class="block font-bold mb-2">Filter by Service</label>
                <Dropdown v-model="serviceId" :options="serviceOptions" optionLabel="label" optionValue="value" 
                    placeholder="All Services" @change="filterByService" class="w-full md:w-64" />
            </div>
            <DataTable ref="dt" :value="props.subServices" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="New Sub Service" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createSubService()" />
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
                        <img v-if="data.image" :src="`/storage/${data.image}`" alt="Sub Service Image" class="w-16 h-16 object-cover rounded" />
                        <span v-else class="text-gray-400">No Image</span>
                    </template>
                </Column>
                <Column field="service.name" header="Service" sortable>
                    <template #body="{ data }">
                        {{ data.service?.name || 'N/A' }}
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
                        <Button icon="pi pi-list" variant="text" severity="info" size="medium" v-tooltip="'View Items'"
                            @click="viewServiceItems(data)" />
                        <Button icon="pi pi-cog" variant="text" severity="info" size="medium" v-tooltip="'View Processes'"
                            @click="viewProcesses(data)" />
                        <Button icon="pi pi-dollar" variant="text" severity="success" size="medium" v-tooltip="'View Price Charts'"
                            @click="viewPriceCharts(data)" />
                        <Button icon="pi pi-question-circle" variant="text" severity="warning" size="medium" v-tooltip="'View FAQs'"
                            @click="viewFaqs(data)" />
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showSubServiceDetails(data)" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editSubService(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteSubService(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

