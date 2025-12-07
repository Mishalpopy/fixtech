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
    { 'value': 'FAQs', 'route': route('admin:faqs.index') },
    { 'value': 'Bulk Create FAQs', 'route': '' },
]);

const subServiceOptions = props.subServices.map(s => ({ 
    label: `${s.service?.name || 'N/A'} - ${s.name}`, 
    value: s.id 
}));

const form = useForm({
    sub_service_id: props.subServiceId || null,
    faqs: [
        {
            title: '',
            description: '',
            order: 0,
            status: true
        }
    ]
});

function addFaq() {
    form.faqs.push({
        title: '',
        description: '',
        order: form.faqs.length,
        status: true
    });
}

function removeFaq(index) {
    if (form.faqs.length > 1) {
        form.faqs.splice(index, 1);
        // Update order numbers
        form.faqs.forEach((faq, idx) => {
            faq.order = idx;
        });
    }
}

function save() {
    // Remove empty FAQs
    const validFaqs = form.faqs.filter(faq => 
        faq.title.trim() !== '' && faq.description.trim() !== ''
    );
    
    if (validFaqs.length === 0) {
        alert('Please add at least one FAQ with title and description');
        return;
    }

    loading.value = true;
    form.faqs = validFaqs;
    
    form.post(route('admin:faqs.bulk.store'), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            router.get(route('admin:faqs.index', { sub_service_id: form.sub_service_id }));
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Bulk Create FAQs" />
    <AppLayout>
        <template #title>
            <span>Bulk Create FAQs</span>
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
                    <h3 class="text-lg font-bold">FAQs</h3>
                    <Button label="Add FAQ" icon="pi pi-plus" @click="addFaq()" severity="success" />
                </div>

                <div v-for="(faq, index) in form.faqs" :key="index" class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-700">FAQ {{ index + 1 }}</h4>
                        <Button 
                            v-if="form.faqs.length > 1"
                            icon="pi pi-trash" 
                            severity="danger" 
                            size="small" 
                            outlined
                            @click="removeFaq(index)" 
                        />
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="w-full">
                            <label :for="`title_${index}`" class="block font-bold mb-1">Title *</label>
                            <InputText 
                                :id="`title_${index}`" 
                                v-model.trim="faq.title" 
                                required="true"
                                :invalid="form.errors[`faqs.${index}.title`]" 
                                fluid 
                            />
                            <small v-if="form.errors[`faqs.${index}.title`]" class="text-red-500">
                                {{ form.errors[`faqs.${index}.title`] }}
                            </small>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="w-full">
                            <label :for="`description_${index}`" class="block font-bold mb-1">Description *</label>
                            <Textarea 
                                :id="`description_${index}`" 
                                v-model.trim="faq.description" 
                                required="true"
                                :invalid="form.errors[`faqs.${index}.description`]" 
                                fluid 
                                rows="4" 
                            />
                            <small v-if="form.errors[`faqs.${index}.description`]" class="text-red-500">
                                {{ form.errors[`faqs.${index}.description`] }}
                            </small>
                        </div>
                    </div>
                </div>

                <div v-if="form.faqs.length === 0" class="text-center py-8 text-gray-500">
                    <p>No FAQs added. Click "Add FAQ" to start.</p>
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-2">
                <Button label="Cancel" severity="secondary" outlined @click="router.get(route('admin:faqs.index'))" />
                <Button label="Save All FAQs" icon="pi pi-save" @click="save()" :loading="loading" />
            </div>
        </div>
    </AppLayout>
</template>

