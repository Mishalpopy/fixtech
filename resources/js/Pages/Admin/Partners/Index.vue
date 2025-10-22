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
import { Tag, Dialog, Textarea } from 'primevue';

const props = defineProps({
    partners: Object
});

const deleteModal = ref(null);
const rejectDialog = ref(false);
const selectedPartner = ref(null);
const rejectionReason = ref('');
const page = usePage();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Partners', 'route': '' },
]);

function deletePartner(data) {
    deleteModal.value.showModal(route('admin:partners.destroy', [data.id]));
}

function updateStatus(data) {
    router.post(
        route("admin:partners.change_status", { partner: data.id }),
        {},
        {
            onSuccess: (res) => {},
            onError: (err) => {},
        }
    );
}

function createPartner() {
    router.get(route('admin:partners.create'));
}

function editPartner(data) {
    router.get(route('admin:partners.edit', [data.id]));
}

function showPartnerDetails(data) {
    router.get(route('admin:partners.show', [data.id]));
}

function approvePartner(data) {
    router.post(route('admin:partners.approve', [data.id]), {}, {
        onSuccess: () => {},
        onError: () => {}
    });
}

function openRejectDialog(data) {
    selectedPartner.value = data;
    rejectionReason.value = '';
    rejectDialog.value = true;
}

function rejectPartner() {
    if (!rejectionReason.value) {
        return;
    }
    
    router.post(route('admin:partners.reject', [selectedPartner.value.id]), {
        rejection_reason: rejectionReason.value
    }, {
        onSuccess: () => {
            rejectDialog.value = false;
            selectedPartner.value = null;
            rejectionReason.value = '';
        },
        onError: () => {}
    });
}

function viewPendingApprovals() {
    router.get(route('admin:partners.pending'));
}

const loading = ref(false);

</script>

<template>
    <Head title="Partners" />

    <AppLayout>
        <DeleteConfirmationModal ref="deleteModal" />

        <Dialog v-model:visible="rejectDialog" modal header="Reject Partner" :style="{ width: '30rem' }">
            <div class="flex flex-col gap-4">
                <label for="rejection_reason" class="font-semibold">Rejection Reason</label>
                <Textarea id="rejection_reason" v-model="rejectionReason" rows="4" fluid 
                    placeholder="Please provide a reason for rejection..." />
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" @click="rejectDialog = false" />
                <Button label="Reject" severity="danger" @click="rejectPartner" :disabled="!rejectionReason" />
            </template>
        </Dialog>

        <template #title>
            <span>Partners</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-2">
            <DataTable ref="dt" :value="props.partners" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
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
                        <div class="flex gap-2">
                            <Button label="Pending Approvals" icon="pi pi-clock" severity="warning" 
                                @click="viewPendingApprovals()" />
                            <Button label="New" icon="pi pi-plus" severity="secondary" 
                                @click="createPartner()" v-if="page.props.auth.permissions.includes('Create Partners')" />
                        </div>
                    </div>
                </template>

                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Name" sortable>
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>

                <Column field="email" header="Email" sortable>
                    <template #body="{ data }">
                        {{ data.email }}
                    </template>
                </Column>

                <Column field="company_name" header="Company" sortable>
                    <template #body="{ data }">
                        {{ data.company_name || 'N/A' }}
                    </template>
                </Column>

                <Column field="phone" header="Phone" sortable>
                    <template #body="{ data }">
                        {{ data.phone }}
                    </template>
                </Column>

                <Column field="approval_status" header="Approval Status" sortable>
                    <template #body="{ data }">
                        <Tag v-if="data.approval_status === 'approved'" severity="success" value="Approved" />
                        <Tag v-else-if="data.approval_status === 'pending'" severity="warning" value="Pending" />
                        <Tag v-else severity="danger" value="Rejected" />
                    </template>
                </Column>

                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <ToggleSwitch v-model="data.status" @change="updateStatus(data)" 
                            :disabled="!page.props.auth.permissions.includes('Edit Partners')" />
                    </template>
                </Column>

                <Column field="created" header="Created" sortable>
                    <template #body="{ data }">
                        {{ data.formatted_created_at }}
                    </template>
                </Column>

                <Column :exportable="false" field="actions" header="Actions">
                    <template #body="{ data }">
                        <Button icon="pi pi-check" v-tooltip="'Approve'" variant="text" severity="success" size="medium"
                            @click="approvePartner(data)" 
                            v-if="data.approval_status === 'pending' && page.props.auth.permissions.includes('Approve Partners')" />
                        <Button icon="pi pi-times" v-tooltip="'Reject'" variant="text" severity="danger" size="medium"
                            @click="openRejectDialog(data)" 
                            v-if="data.approval_status === 'pending' && page.props.auth.permissions.includes('Approve Partners')" />
                        <Button icon="pi pi-eye" v-tooltip="'View'" variant="text" severity="info" size="medium"
                            @click="showPartnerDetails(data)" 
                            v-if="page.props.auth.permissions.includes('View Partners')" />
                        <Button icon="pi pi-pencil" v-tooltip="'Edit'" variant="text" class="mr-2" size="medium" 
                            @click="editPartner(data)"
                            v-if="page.props.auth.permissions.includes('Edit Partners')" />
                        <Button icon="pi pi-trash" v-tooltip="'Delete'" variant="text" severity="danger" size="medium" 
                            @click="deletePartner(data)"
                            v-if="page.props.auth.permissions.includes('Delete Partners')" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

