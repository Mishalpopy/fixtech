<script setup>
setTimeout(() => {
    initFlowbite()
}, 200);
const props = defineProps({
    id: {
        type: String,
    },
    tooltip_text: {
        type: String
    }
});

</script>
<template>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a3bef5"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
        <path d="M3 6h18" />
        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
        <line x1="10" x2="10" y1="11" y2="17" />
        <line x1="14" x2="14" y1="11" y2="17" />
    </svg>
    <div :id="id" role="tooltip"
        class="absolute z-100 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
        {{ tooltip_text }}
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
</template>
