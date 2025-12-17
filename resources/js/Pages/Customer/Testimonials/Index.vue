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
    testimonials: Array
});

const deleteModal = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('customer:dashboard') },
    { 'value': 'My Testimonials', 'route': '' },
]);

function createTestimonial() {
    router.get(route('customer:testimonials.create'));
}

function editTestimonial(data) {
    router.get(route('customer:testimonials.edit', [data.id]));
}

function deleteTestimonial(data) {
    deleteModal.value.showModal(route('customer:testimonials.destroy', [data.id]));
}

function getStatusSeverity(status) {
    switch(status) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}
</script>

<template>
    <Head title="My Testimonials" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>My Testimonials</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-2">
            <DataTable ref="dt" :value="props.testimonials" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="New Testimonial" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createTestimonial()" />
                        </div>
                    </div>
                </template>
                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="title" header="Title" sortable>
                    <template #body="{ data }">
                        {{ data.title || 'N/A' }}
                    </template>
                </Column>
                <Column field="description" header="Description">
                    <template #body="{ data }">
                        <span class="truncate max-w-xs">{{ data.description || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="photo" header="Photo">
                    <template #body="{ data }">
                        <img v-if="data.photo" :src="`/storage/${data.photo}`" alt="Testimonial Photo" class="w-16 h-16 object-cover rounded" />
                        <span v-else>No Photo</span>
                    </template>
                </Column>
                <Column field="video" header="Video">
                    <template #body="{ data }">
                        <span v-if="data.video">Yes</span>
                        <span v-else>No</span>
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
                        <Button v-if="data.status === 'pending'" icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editTestimonial(data)" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteTestimonial(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

