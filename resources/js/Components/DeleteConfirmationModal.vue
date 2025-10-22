<script setup>
import {  ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Dialog from 'primevue/dialog';
import { Button } from "primevue";



const show_modal = ref(false);
const url = ref(null);
const loading = ref(false);

defineExpose({
    showModal,
    closeModal
})

const emit = defineEmits(['completed']);




function showModal(urlData) {
    url.value = urlData;
    show_modal.value =true;
}

function closeModal() {

    show_modal.value =false;
}

function deleteData() {
    loading.value = true;
    router.delete(url.value, {
        onSuccess: (res) => {

            loading.value = false;
            closeModal();
            emit('completed');
        },
        onError: (err) => {
            loading.value = false;
            closeModal();
        },
    })
}

function close() {



    closeModal();
}


</script>

<template>

        <Dialog v-model:visible="show_modal" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4 justify-center">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span  class="text-md"
                    >Are you sure you want to delete ?</span
                >
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="close()" />
                <Button label="Yes" icon="pi pi-check" :loading="loading" @click="deleteData()" />
            </template>
        </Dialog>

</template>
