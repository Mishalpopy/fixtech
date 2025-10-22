<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { Button } from "primevue";
import Select from 'primevue/select';


const props = defineProps(
    {
        selected_user: Object
    }
);

console.log(props.selected_user, 'props.selected_user')

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    confirm_password: '',
});

const loading = ref(false);
const show_modal = ref(false);

defineExpose({
    closeModal,
    showModal
});


function showModal(data) {
    form.id = data.id;
    show_modal.value = true;
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
    form.post(route('users.update_password',[form.id]), {
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


    <Dialog v-model:visible="show_modal" :style="{ width: '450px' }" header="Update User Password" :draggable="false"
        :modal="true">
        <div class="flex flex-col gap-3">
            <div>
                <label for="password" class="block font-bold mb-1">Password</label>
                <InputText id="password" v-model.trim="form.password" required="true" autofocus
                    :invalid="form.errors.password" fluid />
                <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
            </div>

            <div>
                <label for="confirm_password" class="block font-bold mb-1">Confirm Password</label>
                <InputText id="confirm_password" v-model.trim="form.confirm_password" required="true" autofocus
                    :invalid="form.errors.confirm_password" fluid />
                <small v-if="form.errors.confirm_password" class="text-red-500">{{ form.errors.confirm_password
                    }}</small>
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="closeModal()" />
            <Button label="Save" icon="pi pi-check" :loading="loading" @click="save()" />
        </template>
    </Dialog>

</template>
