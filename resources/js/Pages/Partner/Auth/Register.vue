<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button, InputText, Password, Textarea, FileUpload } from 'primevue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    company_name: '',
    trade_license_no: '',
    emirates_id: null
});

function onEmiratesIdSelect(event) {
    form.emirates_id = event.files[0];
}

const submit = () => {
    form.post(route('partner:register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen overflow-hidden py-8">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="sm:max-w-3xl w-50rem bg-white bg-surface-0 dark:bg-surface-900 py-10 px-5 sm:px-10" style="border-radius: 53px">
                    <div class="text-center mb-4">
                        <svg viewBox="0 0 54 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-4 w-16 shrink-0 mx-auto">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M17.1637 19.2467C17.1566 19.4033 17.1529 19.561 17.1529 19.7194C17.1529 25.3503 21.7203 29.915 27.3546 29.915C32.9887 29.915 37.5561 25.3503 37.5561 19.7194C37.5561 19.5572 37.5524 19.3959 37.5449 19.2355C38.5617 19.0801 39.5759 18.9013 40.5867 18.6994L40.6926 18.6782C40.7191 19.0218 40.7326 19.369 40.7326 19.7194C40.7326 27.1036 34.743 33.0896 27.3546 33.0896C19.966 33.0896 13.9765 27.1036 13.9765 19.7194C13.9765 19.374 13.9896 19.0316 14.0154 18.6927L14.0486 18.6994C15.0837 18.9062 16.1223 19.0886 17.1637 19.2467ZM33.3284 11.4538C31.6493 10.2396 29.5855 9.52381 27.3546 9.52381C25.1195 9.52381 23.0524 10.2421 21.3717 11.4603C20.0078 11.3232 18.6475 11.1387 17.2933 10.907C19.7453 8.11308 23.3438 6.34921 27.3546 6.34921C31.36 6.34921 34.9543 8.10844 37.4061 10.896C36.0521 11.1292 34.692 11.3152 33.3284 11.4538ZM43.826 18.0518C43.881 18.6003 43.9091 19.1566 43.9091 19.7194C43.9091 28.8568 36.4973 36.2642 27.3546 36.2642C18.2117 36.2642 10.8 28.8568 10.8 19.7194C10.8 19.1615 10.8276 18.61 10.8816 18.0663L7.75383 17.4411C7.66775 18.1886 7.62354 18.9488 7.62354 19.7194C7.62354 30.6102 16.4574 39.4388 27.3546 39.4388C38.2517 39.4388 47.0855 30.6102 47.0855 19.7194C47.0855 18.9439 47.0407 18.1789 46.9536 17.4267L43.826 18.0518ZM44.2613 9.54743L40.9084 10.2176C37.9134 5.95821 32.9593 3.1746 27.3546 3.1746C21.7442 3.1746 16.7856 5.96385 13.7915 10.2305L10.4399 9.56057C13.892 3.83178 20.1756 0 27.3546 0C34.5281 0 40.8075 3.82591 44.2613 9.54743Z"
                                fill="var(--primary-color)" />
                        </svg>
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Partner Registration</div>
                        <span class="text-muted-color font-medium">Create your partner account</span>
                    </div>

                    <div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Full Name *</label>
                                <InputText id="name" type="text" placeholder="Full Name" class="w-full" v-model="form.name" />
                                <span v-if="form.errors.name" class="!text-red-500 text-sm">{{ form.errors.name }}</span>
                            </div>
                            <div>
                                <label for="email" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Email *</label>
                                <InputText id="email" type="email" placeholder="Email" class="w-full" v-model="form.email" />
                                <span v-if="form.errors.email" class="!text-red-500 text-sm">{{ form.errors.email }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="password" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Password *</label>
                                <Password id="password" v-model="form.password" placeholder="Password" :toggleMask="true" class="w-full" fluid :feedback="false"></Password>
                                <span v-if="form.errors.password" class="!text-red-500 text-sm">{{ form.errors.password }}</span>
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Confirm Password *</label>
                                <Password id="password_confirmation" v-model="form.password_confirmation" placeholder="Confirm Password" :toggleMask="true" class="w-full" fluid :feedback="false"></Password>
                                <span v-if="form.errors.password_confirmation" class="!text-red-500 text-sm">{{ form.errors.password_confirmation }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="phone" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Phone *</label>
                                <InputText id="phone" type="text" placeholder="Phone Number" class="w-full" v-model="form.phone" />
                                <span v-if="form.errors.phone" class="!text-red-500 text-sm">{{ form.errors.phone }}</span>
                            </div>
                            <div>
                                <label for="company_name" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Company Name</label>
                                <InputText id="company_name" type="text" placeholder="Company Name" class="w-full" v-model="form.company_name" />
                                <span v-if="form.errors.company_name" class="!text-red-500 text-sm">{{ form.errors.company_name }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="trade_license_no" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Trade License No</label>
                            <InputText id="trade_license_no" type="text" placeholder="Trade License Number" class="w-full" v-model="form.trade_license_no" />
                            <span v-if="form.errors.trade_license_no" class="!text-red-500 text-sm">{{ form.errors.trade_license_no }}</span>
                        </div>

                        <div class="mt-4">
                            <label for="address" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Address *</label>
                            <Textarea id="address" v-model="form.address" rows="3" placeholder="Full Address" class="w-full" />
                            <span v-if="form.errors.address" class="!text-red-500 text-sm">{{ form.errors.address }}</span>
                        </div>

                        <div class="mt-4">
                            <label for="emirates_id" class="block text-surface-900 dark:text-surface-0 font-medium mb-2">Emirates ID (Required) *</label>
                            <FileUpload mode="basic" accept="image/*,application/pdf" :maxFileSize="5242880" @select="onEmiratesIdSelect" 
                                chooseLabel="Choose Emirates ID File" class="w-full" />
                            <small class="text-gray-500">Accepted formats: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            <span v-if="form.errors.emirates_id" class="block !text-red-500 text-sm mt-1">{{ form.errors.emirates_id }}</span>
                        </div>

                        <Button :loading="form.processing" label="Register" class="w-full mt-6" @click="submit()"></Button>

                        <div class="text-center mt-4">
                            <span class="text-muted-color">Already have an account? </span>
                            <Link :href="route('partner:login')" class="font-medium no-underline cursor-pointer text-primary">
                                Login here
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

