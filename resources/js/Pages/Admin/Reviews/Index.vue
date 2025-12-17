<script setup>
import { Head, router } from '@inertiajs/vue3';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

const props = defineProps({
    reviews: Array
});

const deleteModal = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Reviews', 'route': '' },
]);

function deleteReview(data) {
    deleteModal.value.showModal(route('admin:reviews.destroy', [data.id]));
}

function showReviewDetails(data) {
    router.get(route('admin:reviews.show', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:reviews.change_status", { review: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function getStatusSeverity(status) {
    return status === 'active' ? 'success' : 'secondary';
}

const loading = ref(false);
</script>

<template>
    <Head title="Reviews" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Reviews</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <DataTable ref="dt" :value="props.reviews" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                    </div>
                </template>
                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="customer.name" header="Customer" sortable>
                    <template #body="{ data }">
                        {{ data.customer?.name || 'N/A' }}
                    </template>
                </Column>
                <Column field="service.name" header="Service" sortable>
                    <template #body="{ data }">
                        {{ data.service?.name || 'N/A' }}
                    </template>
                </Column>
                <Column field="rating" header="Rating" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-1">
                            <i v-for="i in 5" :key="i" 
                               :class="i <= data.rating ? 'pi pi-star-fill text-yellow-500' : 'pi pi-star text-gray-300'">
                            </i>
                            <span class="ml-2">({{ data.rating }}/5)</span>
                        </div>
                    </template>
                </Column>
                <Column field="comment" header="Comment">
                    <template #body="{ data }">
                        <span class="truncate max-w-xs">{{ data.comment || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="ticket.ticket_number" header="Ticket #" sortable>
                    <template #body="{ data }">
                        {{ data.ticket?.ticket_number || 'N/A' }}
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
                <Column field="created" header="Created" sortable>
                    <template #body="{ data }">
                        {{ data.formatted_created_at }}
                    </template>
                </Column>
                <Column :exportable="false" field="actions" header="Actions">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showReviewDetails(data)" />
                        <Button icon="pi pi-check" variant="text" severity="success" size="medium" 
                            v-if="data.status === 'inactive'"
                            @click="updateStatus(data)" v-tooltip="'Activate'" />
                        <Button icon="pi pi-times" variant="text" severity="warning" size="medium" 
                            v-if="data.status === 'active'"
                            @click="updateStatus(data)" v-tooltip="'Deactivate'" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteReview(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

