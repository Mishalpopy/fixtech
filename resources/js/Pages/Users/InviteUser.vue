<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { Button, Chip, Chips } from "primevue";
import Select from 'primevue/select';
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    roles: Object,
});

const form = useForm({
    emails: [], // Storing multiple emails
    role: 1,
});

const loading = ref(false);
const show_modal = ref(false);
const emailInput = ref("");

// Expose modal functions
defineExpose({
    closeModal,
    show_modal
});

function closeModal() {
    if (!loading.value) {
        form.clearErrors();
        form.reset();
        show_modal.value = false;
    }
}

function save() {
    console.log(form.emails, 'Emails to be sent');
    form.post(route('send_invitation'), {
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

// Function to add email as a chip
function addEmail(event) {
    const email = emailInput.value.trim();
    if (email && validateEmail(email)) {
        form.emails.push(email);
        emailInput.value = ""; // Clear input
    }
}

// Function to remove an email
function removeEmail(index) {
    form.emails.splice(index, 1);
}

// Email validation function
function validateEmail(email) {
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.
        test(email);
}
</script>

<template>
    <Dialog v-model:visible="show_modal" :style="{ width: '450px' }" header="Invite User" :draggable="false"
        :modal="true">
        <div class="flex flex-col gap-3">
            <!-- Display added emails as chips -->
            <div class="flex flex-wrap gap-2">
                <Chip v-for="(email, index) in form.emails" :key="index" :label="email" removable
                    @remove="removeEmail(index)" />
            </div>

            <!-- Input field for emails -->
            <div>
                <label for="email" class="block font-bold mb-1">Emails</label>
                <InputText id="email" v-model.trim="emailInput" @keyup.enter="addEmail"
                    placeholder="Enter email and press Enter" class="w-full" />
                <InputError v-if="form.errors.emails" class="text-red-500">{{ form.errors.emails }}</InputError>
            </div>

            <div>
                <label for="role" class="block font-bold mb-1">Role</label>
                <Select id="role" class="w-full" v-model="form.role" :options="props.roles" optionLabel="name"
                    optionValue="id" placeholder="Select a Role" />
                <InputError v-if="form.errors.role" class="text-red-500">{{ form.errors.role }}</InputError>
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="closeModal()" />
            <Button label="Invite" icon="pi pi-check" :loading="loading" @click="save()" />
        </template>
    </Dialog>
</template>

<style scoped>
.chip-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
</style>
