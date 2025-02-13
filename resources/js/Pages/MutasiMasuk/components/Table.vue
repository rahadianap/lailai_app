<script setup>
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal } from "lucide-vue-next";

const props = defineProps({
    mutasimasuk: Object,
    permissions: Object,
});

const emit = defineEmits(["expand", "edit", "delete", "approve", "print"]);
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
            <DropdownMenuItem
                v-if="
                    permissions.mutasi_masuk_edit &&
                    props.mutasimasuk.status === 'CREATED'
                "
                @click="$emit('edit')"
            >
                Edit
            </DropdownMenuItem>
            <DropdownMenuItem
                v-if="
                    permissions.mutasi_masuk_delete &&
                    props.mutasimasuk.status === 'CREATED'
                "
                @click="$emit('delete')"
            >
                Delete
            </DropdownMenuItem>
            <DropdownMenuItem
                v-if="
                    props.permissions.mutasi_masuk_approve &&
                    props.mutasimasuk.status === 'CREATED'
                "
                @click="$emit('approve')"
            >
                Approve
            </DropdownMenuItem>
            <DropdownMenuItem @click="$emit('print')"> Print </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
