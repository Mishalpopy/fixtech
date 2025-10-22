<script setup>

import InputLabel from '@/Components/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import { Accordion, AccordionContent, AccordionHeader, AccordionPanel, Button, InputText } from 'primevue';
import Checkbox from 'primevue/checkbox';
import BreadCrumb from '@/Components/BreadCrumb.vue';


const props = defineProps({
    permissions: Object
});
const loading = ref(false);


const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Roles', 'route': '' },
    { 'value': 'Create Role', 'route': '' },

])

const form = useForm({
    name: '',
    permissions: [],
    select_all: false,
});



function selectAllPermissions() {


    if (form.select_all) {
        Object.keys(props.permissions['permission']).forEach((key) => {

            props.permissions['permission'][key].selected = true;

            props.permissions['permission'][key].forEach(element => {
                element.selected = true;
            });


        });
    } else {
        Object.keys(props.permissions['permission']).forEach((key) => {

            props.permissions['permission'][key].selected = false;

            props.permissions['permission'][key].forEach(element => {
                element.selected = false;
            });


        });
    }
}

function updateSelectAll(category) {

    if (props.permissions['permission'][category].selected) {

        props.permissions['permission'][category].forEach(el => {
            el.selected = true;
        })
    } else {
        props.permissions['permission'][category].forEach(el => {
            el.selected = false;
        })
    }

    setAllPermissions();
}

function setPermissions(category) {


    props.permissions['permission'][category].selected = props.permissions['permission'][category].filter(el => el.selected).length == props.permissions['permission'][category].length;

    setAllPermissions();
}

function setAllPermissions() {
    let selected = true;
    Object.keys(props.permissions['permission']).forEach((key) => {
        if (!props.permissions['permission'][key].selected) selected = false;
    });

    form.select_all = selected;
}




function save() {

    let selected_permissions = [];

    Object.keys(props.permissions['permission']).forEach((key) => {
        props.permissions['permission'][key].forEach(element => {
            if (element.selected) selected_permissions.push(element.name);
        });
    });

    form.permissions = selected_permissions;
    loading.value = true;
    form.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
        onError: (err) => {
            loading.value = false;
        }

    });

}
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>


        <template #title>
           <span>Create Role</span>
        </template>

        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>

        <div class="card mt-2">


            <div class="flex justify-between p-1 mt-3 ml-2 text-start">
                <div class="w-1/3">

                    <div>
                        <label for="name" class="block font-bold mb-2">Name</label>
                        <InputText id="name" v-model.trim="form.name" required="true" :invalid="form.errors.name"
                            fluid />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>


                </div>

                <div class="mt-8 my-6 text-right">
                    <div class="flex items-center mb-4">
                        <Checkbox v-model="form.select_all" binary @change="selectAllPermissions()" />
                        <InputLabel for="select_all"
                            class="ms-2 mr-4 text-sm font-medium text-gray-900 dark:text-gray-300">
                            Select All
                        </InputLabel>
                    </div>
                </div>
            </div>





            <div class="px-3">


                <Accordion value="0" >
                    <AccordionPanel v-for="(permission, permission_index) in props.permissions['permission']"
                        style="border: 1px solid #bbb;border-radius: 6px; margin-bottom: 3px; padding-bottom: 3px;"
                        :key="permission_index" :value="permission">


                        <div class="flex justify-end pl-2 pr-4 pt-2">
                            <span class="flex items-center">
                                <Checkbox v-model="permission.selected" binary
                                    @change="updateSelectAll(permission_index)" />

                                <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Select All
                                </label>


                            </span>
                        </div>



                        <AccordionHeader style="padding-top: 3px;padding-bottom:8px">
                            <span class="text-lg">
                                {{ permission_index }}
                            </span>
                        </AccordionHeader>
                        <AccordionContent>

                            <div class="w-full px-4">
                                <div class="grid gap-1 grid-cols-4 mt-4" :id="'module_' + permission_index"
                                    style="display: grid;">

                                    <div class="flex items-center mb-4" v-for="(item, item_index) in permission"
                                        :key="item_index">
                                        <Checkbox v-model="item.selected" binary
                                            @change="setPermissions(permission_index)" />

                                        <label :for="'permission_item_' + permission_index + item_index"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ item.name }}</label>


                                    </div>
                                </div>


                            </div>
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
            </div>

            <div class="flex justify-end mt-6 mr-3">
                <Button size="large" label="Save" icon="pi pi-check" :loading="loading" @click="save()" />
            </div>

        </div>
    </AppLayout>
</template>