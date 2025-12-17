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
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    testimonials: Array
});

const deleteModal = ref(null);
const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Testimonials', 'route': '' },
]);

function deleteTestimonial(data) {
    deleteModal.value.showModal(route('admin:testimonials.destroy', [data.id]));
}

function showTestimonialDetails(data) {
    router.get(route('admin:testimonials.show', [data.id]));
}

function approveTestimonial(data) {
    router.post(
        route("admin:testimonials.approve", { testimonial: data.id }),
        {},
        {
            onSuccess: (res) => {
                toast.add({ severity: 'success', summary: 'Success', detail: 'Testimonial approved successfully', life: 3000 });
                router.reload({ only: ['testimonials'] });
            },
            onError: (err) => {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to approve testimonial', life: 3000 });
            },
        }
    );
}

function rejectTestimonial(data) {
    router.post(
        route("admin:testimonials.reject", { testimonial: data.id }),
        {},
        {
            onSuccess: (res) => {
                toast.add({ severity: 'success', summary: 'Success', detail: 'Testimonial rejected successfully', life: 3000 });
                router.reload({ only: ['testimonials'] });
            },
            onError: (err) => {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to reject testimonial', life: 3000 });
            },
        }
    );
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
    <Head title="Testimonials" />
    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />
        <template #title>
            <span>Testimonials</span>
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
                        <span v-else class="text-gray-400">No Photo</span>
                    </template>
                </Column>
                <Column field="video" header="Video">
                    <template #body="{ data }">
                        <span v-if="data.video" class="text-green-600">
                            <i class="pi pi-video"></i> Available
                        </span>
                        <span v-else class="text-gray-400">No Video</span>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
                <Column field="approved_by" header="Approved By">
                    <template #body="{ data }">
                        {{ data.approved_by_user?.name || 'N/A' }}
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
                            @click="showTestimonialDetails(data)" />
                        <Button icon="pi pi-check" variant="text" severity="success" size="medium" 
                            v-if="data.status === 'pending'"
                            @click="approveTestimonial(data)" v-tooltip="'Approve'" />
                        <Button icon="pi pi-times" variant="text" severity="danger" size="medium" 
                            v-if="data.status === 'pending'"
                            @click="rejectTestimonial(data)" v-tooltip="'Reject'" />
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium" @click="deleteTestimonial(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

