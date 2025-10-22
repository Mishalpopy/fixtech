<script setup>
import { computed, watch, ref, onMounted } from 'vue';
import AppFooter from './AppFooter.vue';
import AppSidebar from './AppSidebar.vue';
import { useLayout } from './composables/layout';
import { Toast, useToast } from 'primevue';
import { usePage } from '@inertiajs/vue3';

const { layoutConfig, layoutState, isSidebarActive, onMenuToggle } = useLayout();

const outsideClickListener = ref(null);
const toast = useToast();
const page = usePage();

onMounted(() => {

    if (page.props.toast) toastMessage(page.props.toast)

})




watch(isSidebarActive, (newVal) => {
    if (newVal) {
        bindOutsideClickListener();
    } else {
        unbindOutsideClickListener();
    }
});

watch(() => page.props.toast, toastData => {
    if (toastData) toastMessage(toastData)
}, { deep: true })

function toastMessage(toastData) {
    toast.add({ severity: toastData?.type, summary: toastData?.title, detail: toastData?.message, life: 3000 });
}

const containerClass = computed(() => {
    return {
        'layout-theme-light': layoutConfig.darkTheme.value === 'light',
        'layout-theme-dark': layoutConfig.darkTheme.value === 'dark',
        'layout-overlay': layoutConfig.menuMode.value === 'overlay',
        'layout-static': layoutConfig.menuMode.value === 'static',
        'layout-static-inactive': layoutState.staticMenuDesktopInactive.value && layoutConfig.menuMode.value === 'static',
        'layout-overlay-active': layoutState.overlayMenuActive.value,
        'layout-mobile-active': layoutState.staticMenuMobileActive.value,
        'p-input-filled': layoutConfig.inputStyle.value === 'filled',
        'p-ripple-disabled': !layoutConfig.ripple.value
    };
});
const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                layoutState.overlayMenuActive.value = false;
                layoutState.staticMenuMobileActive.value = false;
                layoutState.menuHoverActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    const sidebarEl = document.querySelector('.layout-sidebar');
    const topbarEl = document.querySelector('.layout-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};
</script>




<template>
    <div class="layout-wrapper" :class="containerClass">

        <div class="layout-sidebar">

            <AppSidebar></AppSidebar>
        </div>
        <div class="layout-main-container">
            <Toast />
            <div class="flex items-center justify-between">
                <div class="flex">
                    <span>
                        <button class="layout-menu-button layout-topbar-action mt-2" @click="onMenuToggle">
                            <i class="pi pi-bars" style="font-size: 1.5rem;"></i>
                        </button>
                    </span>
                    <span class="ml-5 font-bold text-2xl text-gray-400  ">
                        <slot name="title" />
                    </span>
                </div>

                <slot name="breadcrumb" />
            </div>

            <div class="layout-main">
                <slot></slot>
            </div>
            <AppFooter></AppFooter>
        </div>
        <!-- <app-config></app-config> -->
        <div class="layout-mask"></div>
    </div>
</template>

<style lang="scss" scoped></style>

