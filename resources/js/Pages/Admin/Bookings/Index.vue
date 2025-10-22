<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { onMounted, ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import ToggleSwitch from 'primevue/toggleswitch';
import CreateModal from '@/Pages/Users/Create.vue';
import InviteUserModal from '@/Pages/Users/InviteUser.vue';
import ResetPasswordModal from '@/Pages/Users/ResetPassword.vue';
import EditModal from '@/Pages/Users/Edit.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';

import BreadCrumb from '@/Components/BreadCrumb.vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Button from 'primevue/button';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

import InputText from 'primevue/inputtext';
import { OverlayBadge, Tag, Toolbar } from 'primevue';
import { functionsIn } from 'lodash';


const props = defineProps(
    {
        bookings: Object
    }
);
const deleteModal = ref(null);
const page = usePage()

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});


const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Bookings', 'route': '' },

])

function deleteUser(data) {

    deleteModal.value.showModal(route('users.destroy', [data.id]));
}

function updateStatus(data) {

    router.post(
        route("users.change_status", { user: data.id }),
        {
            onSuccess: (res) => {

            },
            onError: (err) => { },
        }
    );
}


function createBooking() {
    router.get(route('admin:bookings.create'));
}

function editBooking(data){
    router.get(route('admin:bookings.edit',[data.id]));
    
}

function showBookingDetails(data){
    router.get(route('admin:bookings.show',[data.id]));

}

function downloadInvoice(data) {
    window.open(route('admin:bookings.invoice', [data.id]), '_blank');
}



// function resetPassword(data) {
//     resetPasswordModal.value.showModal(data);
// }


const loading = ref(false)

</script>

<template>

    <Head title="Bookings" />

    <AppLayout>

        <CreateModal ref="createModal" :roles="props.roles" :site_groups="site_groups" :sites="props.sites" />
        <InviteUserModal ref="inviteUserModal" :roles="props.roles" />
        <ResetPasswordModal ref="resetPasswordModal" />
        <EditModal ref="editModal" :roles="props.roles" :site_groups="site_groups" :sites="props.sites" />
        <DeleteConfirmationModal ref="deleteModal" />

        <template #title>
            <span>Bookings</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-2">


            <DataTable ref="dt" :value="props.bookings" dataKey="id" :paginator="true" :rows="10" :filters="filters"
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
                            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2"
                                @click="createBooking()" v-if="page.props.auth.permissions.includes('Create Users')" />
                            <!-- <Button label="Invite User" icon="pi pi-plus" severity="secondary" class="mr-2" @click="createUser()" /> -->
                            <!-- <Button label="Invite User" icon="pi pi-user" @click="inviteUser()" v-if="page.props.auth.permissions.includes('Invite Users')" /> -->
                        </div>
                    </div>
                </template>

                <Column field="#" header="#">
                    <template #body="{ data, index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="booking_number" header="Booking No" sortable >
                    <template #body="{ data }">
                        <div class="flex gap-2 items-center">
                            {{ data.booking_number }}
                            <Tag severity="success" value="Invited User" class="mb-3" v-if="data.is_invited"></Tag>
                        </div>

                    </template>
                </Column>
                <Column field="company" header="Company" sortable >
                    <template #body="{ data }">
                        {{ data.hotel?.name }}
                    </template>
                </Column>
                <Column field="passenger_name" header="Passenger Name" sortable >
                    <template #body="{ data }">
                        {{ data.passenger_name }}
                    </template>
                </Column>
                <Column field="passengers" header="Passengers" sortable >
                    <template #body="{ data }">
                        {{ data.passengers_count }}
                    </template>
                </Column>
                <Column field="amount" header="Amount" sortable >
                    <template #body="{ data }">
                        {{ data.total_amount }}
                    </template>
                </Column>
                <Column field="created" header="Created" sortable >
                    <template #body="{ data }">
                        {{ data.formatted_created_at }}
                    </template>
                </Column>
                <!-- <Column field="status" header="Status" >
                    <template #body="{ data }">
                        <ToggleSwitch v-model="data.status" @change="updateStatus(data)" :disabled="!page.props.auth.permissions.includes('Change Users Status')"/>
                    </template>
                </Column> -->

                <Column :exportable="false" field="actions" header="Actions" >
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" variant="text" severity="info" size="medium"
                            @click="showBookingDetails(data)" v-if="page.props.auth.permissions.includes('Delete Users')" />
                        <Button icon="pi pi-file-pdf" variant="text" severity="warn" size="medium"
                            @click="downloadInvoice(data)" />
                        <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editBooking(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

    </AppLayout>
</template>
