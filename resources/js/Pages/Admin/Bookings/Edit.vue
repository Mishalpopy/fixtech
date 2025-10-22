<script setup>

import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { computed, onMounted, ref } from 'vue';
import { Button, Column, DataTable, DatePicker, Divider, InputText, Select, Textarea } from 'primevue';
import 'vue3-treeselect/dist/vue3-treeselect.css'
import moment from 'moment';

const props = defineProps({

    flight_types: Object,
    payment_status: Object,
    booking_status: Object,
    agents: Object,
    hotels: Object,
    countries: Object,
    booking: Object
});

const loading = ref(false);



const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Bookings', 'route': route('admin:bookings.index') },
    { 'value': 'Create Booking', 'route': '' },

])

console.log(props.booking,'props booking')

const form = useForm({
    agent: props.booking?.agent_id,
    flight_type: props.booking?.flight_type_id,
    hotel: props.booking?.hotel_id,
    payment_status: props.booking?.payment_status_id,
    passenger_name: props.booking?.passenger_name,
    email: props.booking?.email,
    passenger_number: props.booking?.passenger_number,
    agent_number: props.booking?.agent_number,
    pickup_address: props.booking?.pickup_address,
    adults: props.booking?.adults,
    childrens: props.booking?.childrens,
    adult_price: props.booking?.adult_price,
    child_price: props.booking?.child_price,
    flight_date: props.booking?.flight_date,
    pickup_time: props.booking?.pickup_time,
    status: props.booking?.status,
    deposit: props.booking?.deposit,
    voucher_code: props.booking?.voucher_code,
    redemption_code: props.booking?.redemption_code,
    comments: props.booking?.comments,
    passengers: props.booking?.passengers,
    additional_services: props.booking?.additional_services,
    total_amount : props.booking?.total_amount
});


function addAdditionalServices() {

    console.log('hhhhh')

    form.additional_services.push({ name: '', description: '', price: '' })
}

function removeAdditionalServices(index) {

    form.additional_services.splice(index, 1);
}

function addOrRemovePassengers() {



    form.passengers = [];

    const childrenCount = Number(form.childrens);
    const adultCount = Number(form.adults);
    const totalPassengers = childrenCount + adultCount;
    console.log(totalPassengers, 'total passengers')



    for (let i = 0; i < totalPassengers; i++) {


        console.log('from inside for loop')

        form.passengers.push(({
            id: i + 1,
            name: '',
            country_id: '',
            year_of_birth: '',
            weight: '',
            id_status: '',

        }))

    }
}


function save() {


    loading.value = true;
    form.put(route('admin:bookings.update',[props.booking]), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:bookings.index'))
        },
        onError: () => {
            loading.value = false;
        },
    });
}





</script>

<template>

    <Head title="Create Booking" />

    <AppLayout>


        <template #title>
            <span>Create Booking</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-4 mx-6">


            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="passenger_name" class="block font-bold mb-1">Passenger Name</label>
                    <InputText id="passenger_name" v-model.trim="form.passenger_name" required="true" autofocus
                        :invalid="form.errors.passenger_name" fluid />
                    <small v-if="form.errors.passenger_name" class="text-red-500">{{ form.errors.passenger_name
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="email" class="block font-bold mb-1">Email</label>
                    <InputText id="email" v-model.trim="form.email" required="true" autofocus
                        :invalid="form.errors.email" fluid />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="passenger_number" class="block font-bold mb-1">Passenger Number</label>
                    <InputText id="passenger_number" v-model.trim="form.passenger_number" required="true" autofocus
                        :invalid="form.errors.passenger_number" fluid />
                    <small v-if="form.errors.passenger_number" class="text-red-500">{{ form.errors.passenger_number
                        }}</small>
                </div>
                <div class="w-full">
                    <label for="agent_number" class="block font-bold mb-1">Agent Number</label>
                    <InputText id="agent_number" v-model.trim="form.agent_number" required="true" autofocus
                        :invalid="form.errors.agent_number" fluid />
                    <small v-if="form.errors.agent_number" class="text-red-500">{{ form.errors.agent_number }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="hotel" class="block font-bold mb-1">Pickup Location</label>
                    <!-- <InputText id="hotel" v-model.trim="form.hotel" required="true" autofocus
                        :invalid="form.errors.hotel" fluid /> -->
                    <Select v-model="form.hotel" :options="hotels" optionLabel="name" optionValue="id"
                        placeholder="Select a Pickup Location" class="w-full md:w-56" />
                    <small v-if="form.errors.hotel" class="text-red-500">{{ form.errors.hotel
                        }}</small>
                </div>
                <div class="w-full">
                    <label for="pickup_address" class="block font-bold mb-1">Pickup Address</label>
                    <InputText id="pickup_address" v-model.trim="form.pickup_address" required="true" autofocus
                        :invalid="form.errors.pickup_address" fluid />
                    <small v-if="form.errors.pickup_address" class="text-red-500">{{ form.errors.pickup_address
                        }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="agent" class="block font-bold mb-1">Company</label>
                    <!-- <InputText id="agent" v-model.trim="form.agent" required="true" autofocus
                        :invalid="form.errors.agent" fluid /> -->
                    <Select v-model="form.agent" :options="agents" optionLabel="name" placeholder="Select a Company"
                        class="w-full md:w-56" optionValue="id" />
                    <small v-if="form.errors.agent" class="text-red-500">{{ form.errors.agent }}</small>
                </div>
                <div class="w-full">
                    <label for="adults" class="block font-bold mb-1">Adults</label>
                    <InputText id="adults" v-model.trim="form.adults" required="true" autofocus
                        @keyup="addOrRemovePassengers()" :invalid="form.errors.adults" fluid />
                    <small v-if="form.errors.adults" class="text-red-500">{{ form.errors.adults }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="childrens" class="block font-bold mb-1">Children</label>
                    <InputText id="childrens" v-model.trim="form.childrens" required="true" autofocus
                        @keyup="addOrRemovePassengers()" :invalid="form.errors.childrens" fluid />

                    <small v-if="form.errors.childrens" class="text-red-500">{{ form.errors.childrens
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="adult_price" class="block font-bold mb-1">Adult Price</label>
                    <InputText id="adult_price" v-model.trim="form.adult_price" required="true" autofocus
                        :invalid="form.errors.adult_price" fluid />
                    <small v-if="form.errors.adult_price" class="text-red-500">{{ form.errors.adult_price }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="child_price" class="block font-bold mb-1">Child Price</label>
                    <InputText id="child_price" v-model.trim="form.child_price" required="true" autofocus
                        :invalid="form.errors.child_price" fluid />

                    <small v-if="form.errors.child_price" class="text-red-500">{{ form.errors.child_price
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="flight_date" class="block font-bold mb-1">Flight Date</label>
                    <DatePicker v-model="form.flight_date" fluid />
                    <small v-if="form.errors.flight_date" class="text-red-500">{{ form.errors.flight_date }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="pickup_time" class="block font-bold mb-1">Pickup Time</label>
                    <DatePicker id="datepicker-timeonly" v-model="form.pickup_time" timeOnly fluid />

                    <small v-if="form.errors.pickup_time" class="text-red-500">{{ form.errors.pickup_time
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="flight_type" class="block font-bold mb-1">Flight Type</label>
                    <Select v-model="form.flight_type" :options="flight_types" optionLabel="name"
                        placeholder="Select a City" class="w-full md:w-56" optionValue="id" />
                    <small v-if="form.errors.flight_type" class="text-red-500">{{ form.errors.flight_type
                        }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="payment_status" class="block font-bold mb-1">Payment Status</label>
                    <Select v-model="form.payment_status" :options="payment_status" optionLabel="name" optionValue="id"
                        placeholder="Select a Payment Status" class="w-full md:w-56" />

                    <small v-if="form.errors.payment_status" class="text-red-500">{{ form.errors.payment_status
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="total_amount" class="block font-bold mb-1">Total Amount</label>
                    <InputText id="total_amount" v-model.trim="form.total_amount" required="true" autofocus
                        :invalid="form.errors.total_amount" fluid />
                    <small v-if="form.errors.total_amount" class="text-red-500">{{ form.errors.total_amount }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="deposit" class="block font-bold mb-1">Deposit</label>
                    <InputText id="deposit" v-model.trim="form.deposit" required="true" autofocus
                        :invalid="form.errors.deposit" fluid />

                    <small v-if="form.errors.deposit" class="text-red-500">{{ form.errors.deposit
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="status" class="block font-bold mb-1">Status</label>
                    <Select v-model="form.status" :options="booking_status" placeholder="Select a Payment Status"
                        class="w-full md:w-56" />
                    <small v-if="form.errors.status" class="text-red-500">{{ form.errors.status }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="voucher_code" class="block font-bold mb-1">Voucher Code</label>
                    <InputText id="voucher_code" v-model.trim="form.voucher_code" required="true" autofocus
                        :invalid="form.errors.voucher_code" fluid />

                    <small v-if="form.errors.voucher_code" class="text-red-500">{{ form.errors.voucher_code
                    }}</small>
                </div>
                <div class="w-full">
                    <label for="redemption_code" class="block font-bold mb-1">Redemption Code</label>
                    <InputText id="redemption_code" v-model.trim="form.redemption_code" required="true" autofocus
                        :invalid="form.errors.redemption_code" fluid />
                    <small v-if="form.errors.redemption_code" class="text-red-500">{{ form.errors.redemption_code
                        }}</small>
                </div>

            </div>
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="comments" class="block font-bold mb-1">Comments</label>
                    <Textarea v-model="form.comments" rows="5" cols="30" />

                    <small v-if="form.errors.comments" class="text-red-500">{{ form.errors.comments
                    }}</small>
                </div>
            </div>




            <!-- passengers end -->
            <h6 class="text-lg font-bold dark:text-white">Passengers</h6>
            <Divider />


            <DataTable ref="dt" :value="form.passengers" dataKey="id">




                <Column field="name" header="Name">
                    <template #body="{ data }">
                        <div class="flex gap-2 items-center">
                            <InputText v-model.trim="data.name" required="true" autofocus :invalid="form.errors.name"
                                fluid />
                        </div>

                    </template>
                </Column>
                <Column field="nationality" header="Nationality">
                    <template #body="{ data }">
                        <Select v-model="data.country_id" :options="countries" optionLabel="name" optionValue="id"
                            placeholder="Select Nationality" class="w-full md:w-56" />
                    </template>
                </Column>
                <Column field="year_of_birth" header="Year Of Birth">
                    <template #body="{ data }">
                        <DatePicker v-model="data.year_of_birth" fluid />
                    </template>
                </Column>
                <Column field="weight" header="weight">
                    <template #body="{ data }">
                        <InputText v-model.trim="data.weight" required="true" autofocus :invalid="form.errors.weight"
                            fluid />
                    </template>
                </Column>
                <Column field="id_status" header="ID Status">
                    <template #body="{ data }">
                        <InputText v-model.trim="data.id_status" required="true" autofocus
                            :invalid="form.errors.id_status" fluid />
                    </template>
                </Column>


                <!-- <Column :exportable="false" field="actions" header="Actions">
                    <template #body="{ data,index }">
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium"
                            @click="removePassenger(data,index)" />

                    </template>
                </Column> -->
            </DataTable>


            <!-- passengers end -->


            <!-- additional services start-->
            <h6 class="text-lg font-bold dark:text-white">Additional Services</h6>
            <Divider />


            <DataTable ref="dt" :value="form.additional_services" dataKey="id">




                <Column field="name" header="Name">
                    <template #body="{ data }">
                        <div class="flex gap-2 items-center">
                            <InputText v-model.trim="data.name" required="true" autofocus :invalid="form.errors.name"
                                fluid />
                        </div>

                    </template>
                </Column>
                <Column field="description" header="Description">
                    <template #body="{ data }">
                        <div class="flex gap-2 items-center">
                            <InputText v-model.trim="data.description" required="true" autofocus
                                :invalid="form.errors.description" fluid />
                        </div>
                    </template>
                </Column>
                <Column field="price" header="Price">
                    <template #body="{ data }">
                        <div class="flex gap-2 items-center">
                            <InputText v-model.trim="data.price" required="true" autofocus :invalid="form.errors.price"
                                fluid />
                        </div>
                    </template>
                </Column>


                <Column :exportable="false" field="actions" header="Actions">
                    <template #body="{ data, index }">
                        <!-- <Button icon="pi pi-pencil" variant="text" class="mr-2" size="medium" @click="editUser(data)" /> -->
                        <Button icon="pi pi-trash" variant="text" severity="danger" size="medium"
                            @click="removeAdditionalServices(index)" />
                    </template>
                </Column>
            </DataTable>
            <!-- additional services end -->

            <Button label="Add Service" class="mt-2" icon="pi pi-plus" severity="secondary" raised
                @click="addAdditionalServices()" />

            <div class="flex justify-end mt-2">
                <Button label="Save" @click="save()" />
            </div>

        </div>
    </AppLayout>
</template>