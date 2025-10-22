<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { Button, MultiSelect } from "primevue";
import Select from 'primevue/select';
import Treeselect from 'vue3-treeselect'
import 'vue3-treeselect/dist/vue3-treeselect.css'

const props = defineProps(
    {
        roles: Object,
        site_groups: Object,
        sites: Object,
    }
);

const form = useForm({
    name: '',
    email: '',
    role: '',
    site_groups: [],
    sites: [],
});

const loading = ref(false);
const show_modal = ref(false);

defineExpose({
    closeModal,
    showModal
});

function showModal(data) {

    form.id = data.id;
    form.name = data.name,
        form.email = data.email,
        form.role = data.role,
        
        form.site_groups = [...data.site_groups.map(el=>'site_group_'+el.id),...data.sites.map(el=>'site_'+el.id)] ;

        show_modal.value = true;



}

function site_normalizer(node) {
    return {
        ...node,
        original_id: node.id,
        id: node.type == 'site_group' ? 'site_group_' + node.id : 'site_' + node.id,
    };
}




function closeModal() {
    if (!loading.value) {
        form.clearErrors();
        form.reset();
        show_modal.value = false
    }
}

function save() {
    loading.value = true;
    form.put(route('users.update', [form.id]), {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            closeModal();
        },
        onError: () => {
            loading.value = false;
        },
    });
}
</script>



<template>
    <Dialog v-model:visible="show_modal" :style="{ width: '450px' }" header="Edit User" :draggable="false"
        :modal="true">
        <div class="flex flex-col gap-3">

            <div>
                <label for="name" class="block font-bold mb-1">Name</label>
                <InputText id="name" v-model.trim="form.name" required="true" autofocus :invalid="form.errors.name"
                    fluid />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div>
                <label for="email" class="block font-bold mb-1">Email</label>
                <InputText id="email" v-model.trim="form.email" required="true" :invalid="form.errors.email" fluid />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.email }}</small>
            </div>

            <div>
                <label for="email" class="block font-bold mb-1">Role</label>
                <Select id="inventoryStatus" v-model="form.role" :options="props.roles" optionLabel="name"
                    optionValue="id" placeholder="Select a Role" fluid></Select>
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.email }}</small>
            </div>



            <div>
                <label for="email" class="block font-bold mb-1">Site Groups</label>
                <treeselect v-model="form.site_groups" :multiple="true" value="temp_id"  :options="site_groups"  :normalizer="site_normalizer" :default-expand-level="1" :flat="true"  />
                <small v-if="form.errors.site_groups" class="text-red-500">{{ form.errors.sites }}</small>
            </div>

        


        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="closeModal()" />
            <Button label="Save" icon="pi pi-check" :loading="loading" @click="save()" />
        </template>
    </Dialog>
</template>
