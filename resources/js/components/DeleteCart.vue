<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirm Deletion</DialogTitle>
            </DialogHeader>
            <p>Please enter your password to confirm item deletion:</p>
            <Input
                v-model="password"
                type="password"
                placeholder="Enter password"
                class="mt-2"
            />
            <DialogFooter class="mt-4">
                <Button @click="cancel">Cancel</Button>
                <Button
                    @click="confirm"
                    variant="destructive"
                    :disabled="!password"
                    >Confirm Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref } from "vue";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(["update:isOpen", "confirm", "cancel"]);

const password = ref("");

const confirm = () => {
    emit("confirm", password.value);
    password.value = "";
};

const cancel = () => {
    emit("cancel");
    password.value = "";
};
</script>
