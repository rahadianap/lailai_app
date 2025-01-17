<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <tbody
        :class="cn('[&_tr:last-child]:border-0 overflow-y-auto', props.class)"
    >
        <slot />
    </tbody>
</template>
