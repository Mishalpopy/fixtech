<script setup>
import { ref, onBeforeMount, watch, Transition } from 'vue';
import { useLayout } from '@/Layouts/Admin/composables/layout';
import { router, usePage } from '@inertiajs/vue3';


const { layoutConfig, layoutState, setActiveMenuItem, onMenuToggle } = useLayout();

const page = usePage();

const props = defineProps({
    item: {
        type: Object,
        default: () => ({})
    },
    index: {
        type: Number,
        default: 0
    },
    root: {
        type: Boolean,
        default: true
    },
    parentItemKey: {
        type: String,
        default: null
    }
});

const isActiveMenu = ref(false);
const itemKey = ref(null);

onBeforeMount(() => {

    let active = false;
    if(props.item.items){
         active = props.item.items.find(el=>page.url.startsWith(`/${el.starts_with}`)) ? true: false;


    }
    if(active) setActiveMenuItem(props.item.key);
    isActiveMenu.value = active;

});

watch(
    () => layoutConfig.activeMenuItem.value,
    (newVal) => {

        isActiveMenu.value = props.item.key===newVal;
    }
);
const itemClick = (event, item) => {
    if (item.disabled) {
        event.preventDefault();
        return;
    }

    const { overlayMenuActive, staticMenuMobileActive } = layoutState;

    if ((item.to || item.url) && (staticMenuMobileActive.value || overlayMenuActive.value)) {
        onMenuToggle();
    }



    const foundItemKey = item.items ? (layoutConfig.activeMenuItem.value==item.key ? null: item.key) : null;
   
    setActiveMenuItem(foundItemKey);
};

function goToPage(route) {
    router.visit(route);
}



</script>

<template>
    <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActiveMenu }">
        <div v-if="root && item.visible !== false" class="layout-menuitem-root-text">{{ item.label }}</div>
        <a v-if="(!item.to || item.items) && item.visible !== false" @click="itemClick($event, item, index)"
            :class="item.class" :target="item.target" tabindex="0">
            <i :class="item.icon" class="layout-menuitem-icon"></i>
            <span class="layout-menuitem-text">{{ item.label }} </span>
            <i class="pi pi-fw pi-angle-down layout-submenu-toggler" v-if="item.items"></i>
        </a>




        <a v-if="item.to && !item.items && item.visible !== false"
            :class="[item.class, { 'active-route': page.url.startsWith(`/${item.starts_with}`) }]"
            @click.prevent="goToPage(item.to)">
            <i :class="item.icon" class="layout-menuitem-icon"></i>
            <span class="layout-menuitem-text">{{ item.label }} </span>

            <i class="pi pi-fw pi-angle-down layout-submenu-toggler" v-if="item.items"></i>
        </a>
        <Transition v-if="item.items && item.visible !== false" name="layout-submenu">
            <ul v-show="root ? true : isActiveMenu" class="layout-submenu">
                <app-menu-item v-for="(child, i) in item.items" :key="child" :index="i" :item="child"
                    :parentItemKey="itemKey" :root="false"></app-menu-item>
            </ul>
        </Transition>
    </li>
</template>

<style lang="scss" scoped></style>
