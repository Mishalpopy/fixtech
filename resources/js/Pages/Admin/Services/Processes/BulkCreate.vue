<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import BreadCrumb from '@/Components/BreadCrumb.vue';
import { ref } from 'vue';
import { Button, InputText, Textarea, Dropdown } from 'primevue';

const props = defineProps({
    subServices: Array,
    subServiceId: [Number, String]
});

const loading = ref(false);

const breadcrumbs = ref([
    { 'value': 'Home', 'route': route('admin:dashboard') },
    { 'value': 'Processes', 'route': route('admin:processes.index') },
    { 'value': 'Bulk Create Processes', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

const form = useForm({
    sub_service_id: props.subServiceId || null,
    processes: [
        {
            title: '',
            description: '',
            order: 0,
            status: true
        }
    ]
});

function addProcess() {
    form.processes.push({
        title: '',
        description: '',
        order: form.processes.length,
        status: true
    });
}

function removeProcess(index) {
    if (form.processes.length > 1) {
        form.processes.splice(index, 1);
        // Update order numbers
        form.processes.forEach((process, idx) => {
            process.order = idx;
        });
    }
}

function save() {
    // Remove empty processes
    const validProcesses = form.processes.filter(p => p.title.trim() !== '');
    
    if (validProcesses.length === 0) {
        alert('Please add at least one process with a title');
        return;
    }

    loading.value = true;
    form.processes = validProcesses;
    
    form.post(route('admin:processes.bulk.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:processes.index', { sub_service_id: form.sub_service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Bulk Create Processes" />
    <AppLayout>
        <template #title>
            <span>Bulk Create Processes</span>
        </template>
        <template #breadcrumb>
            <BreadCrumb :data="breadcrumbs" class="me-7" />
        </template>
        <div class="card mt-4 mx-6">
            <div class="flex gap-4 mt-4">
                <div class="w-full">
                    <label for="sub_service_id" class="block font-bold mb-1">Sub Service *</label>
                    <Dropdown id="sub_service_id" v-model="form.sub_service_id" :options="subServiceOptions" optionLabel="label" 
                        optionValue="value" placeholder="Select Sub Service" :invalid="form.errors.sub_service_id" fluid />
                    <small v-if="form.errors.sub_service_id" class="text-red-500">{{ form.errors.sub_service_id }}</small>
                </div>
            </div>

            <div class="mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Processes</h3>
                    <Button label="Add Process" icon="pi pi-plus" @click="addProcess()" severity="success" />
                </div>

                <div v-for="(process, index) in form.processes" :key="index" class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-700">Process {{ index + 1 }}</h4>
                        <Button 
                            v-if="form.processes.length > 1"
                            icon="pi pi-trash" 
                            severity="danger" 
                            size="small" 
                            outlined
                            @click="removeProcess(index)" 
                        />
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="w-full">
                            <label :for="`title_${index}`" class="block font-bold mb-1">Title *</label>
                            <InputText 
                                :id="`title_${index}`" 
                                v-model.trim="process.title" 
                                required="true"
                                :invalid="form.errors[`processes.${index}.title`]" 
                                fluid 
                            />
                            <small v-if="form.errors[`processes.${index}.title`]" class="text-red-500">
                                {{ form.errors[`processes.${index}.title`] }}
                            </small>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="w-full">
                            <label :for="`description_${index}`" class="block font-bold mb-1">Description</label>
                            <Textarea 
                                :id="`description_${index}`" 
                                v-model.trim="process.description" 
                                :invalid="form.errors[`processes.${index}.description`]" 
                                fluid 
                                rows="3" 
                            />
                            <small v-if="form.errors[`processes.${index}.description`]" class="text-red-500">
                                {{ form.errors[`processes.${index}.description`] }}
                            </small>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="w-full">
                            <label :for="`order_${index}`" class="block font-bold mb-1">Order</label>
                            <InputText 
                                :id="`order_${index}`" 
                                type="number" 
                                v-model.number="process.order" 
                                min="0"
                                :invalid="form.errors[`processes.${index}.order`]" 
                                fluid 
                            />
                            <small v-if="form.errors[`processes.${index}.order`]" class="text-red-500">
                                {{ form.errors[`processes.${index}.order`] }}
                            </small>
                            <small class="text-gray-500">Lower numbers appear first</small>
                        </div>
                    </div>
                </div>

                <div v-if="form.processes.length === 0" class="text-center py-8 text-gray-500">
                    <p>No processes added. Click "Add Process" to start.</p>
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-2">
                <Button label="Cancel" severity="secondary" outlined @click="router.get(route('admin:processes.index'))" />
                <Button label="Save All Processes" icon="pi pi-save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

