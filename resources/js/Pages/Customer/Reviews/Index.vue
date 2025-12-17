<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { FilterMatchMode } from '@primevue/core/api';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Customer/AppLayout.vue';
import Button from 'primevue/button';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';

const props = defineProps({
    reviews: Array
});

const deleteModal = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Reviews', 'route': '' },
]);

function createReview() {
    router.get(route('customer:reviews.create'));
}

function editReview(data) {
    router.get(route('customer:reviews.edit', [data.id]));
}

function deleteReview(data) {
    deleteModal.value.showModal(route('customer:reviews.destroy', [data.id]));
}

function getStatusSeverity(status) {
    return status === 'active' ? 'success' : 'secondary';
}
</script>

<template>
    <Head title="My Reviews" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>My Reviews</span>
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
                        <div>
                            <Button label="New Review" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createReview()" />
                        </div>
                    </div>
                </template>
                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
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
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editReview(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteReview(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

