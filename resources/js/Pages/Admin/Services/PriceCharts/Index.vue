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
import Tag from 'primevue/tag';

const props = defineProps({
    priceCharts: Object,
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
    { 'value': 'Price Charts', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

function deletePriceChart(data) {
    deleteModal.value.showModal(route('admin:price-charts.destroy', [data.id]));
}

function createPriceChart() {
    const params = props.subServiceId ? { sub_service_id: props.subServiceId } : {};
    router.get(route('admin:price-charts.create'), params);
}

function bulkCreatePriceChart() {
    const params = props.subServiceId ? { sub_service_id: props.subServiceId } : {};
    router.get(route('admin:price-charts.bulk.create'), params);
}

function editPriceChart(data) {
    router.get(route('admin:price-charts.edit', [data.id]));
}

function showPriceChartDetails(data) {
    router.get(route('admin:price-charts.show', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:price-charts.change_status", { priceChart: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function filterBySubService() {
    if (subServiceId.value) {
        router.get(route('admin:price-charts.index', { sub_service_id: subServiceId.value }));
    } else {
        router.get(route('admin:price-charts.index'));
    }
}

const loading = ref(false);
</script>

<template>
    <Head title="Price Charts" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Price Charts</span>
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
            <DataTable ref="dt" :value="props.priceCharts" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="Bulk Create" icon="pi pi-plus-circle" severity="success" class="mr-2"
                                @click="bulkCreatePriceChart()" />
                            <Button label="New Price Chart" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createPriceChart()" />
                        </div>
                    </div>
                </template>
                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="order" header="Order" sortable>
                    <template #body="{ data }">
                        {{ data.order }}
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
                <Column field="time_duration" header="Time Duration" sortable>
                    <template #body="{ data }">
                        {{ data.time_duration }}
                    </template>
                </Column>
                <Column field="current_price" header="Current Price" sortable>
                    <template #body="{ data }">
                        <span class="font-bold text-green-600">AED {{ parseFloat(data.current_price).toFixed(2) }}</span>
                        <span v-if="data.original_price" class="ml-2 text-gray-400 line-through">
                            AED {{ parseFloat(data.original_price).toFixed(2) }}
                        </span>
                    </template>
                </Column>
                <Column field="is_urgent" header="Urgent">
                    <template #body="{ data }">
                        <Tag v-if="data.is_urgent" value="Urgent" severity="danger" />
                        <span v-else class="text-gray-400">-</span>
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
                            @click="showPriceChartDetails(data)" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editPriceChart(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deletePriceChart(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

