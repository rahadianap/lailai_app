<script setup>
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal } from "lucide-vue-next";

const props = defineProps({
    product: Object,
})

const emit = defineEmits(['expand', 'edit', 'delete'])

</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button class="w-8 h-8 p-0" variant="ghost">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem @click="$emit('expand')">
                Expand
            </DropdownMenuItem>
            <DropdownMenuItem @click="$emit('edit')">
                Edit
            </DropdownMenuItem>
            <DropdownMenuItem @click="$emit('delete')">
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
