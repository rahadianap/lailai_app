<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Change Quantity</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid items-center grid-cols-4 gap-4">
                    <Label for="new-quantity" class="text-right">
                        New Quantity
                    </Label>
                    <Input
                        id="new-quantity"
                        v-model="newQuantity"
                        type="number"
                        class="col-span-3"
                        min="1"
                    />
                </div>
                <div class="grid items-center grid-cols-4 gap-4">
                    <Label for="password" class="text-right"> Password </Label>
                    <Input
                        id="password"
                        v-model="password"
                        type="password"
                        class="col-span-3"
                        placeholder="Enter password to confirm"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button @click="cancel">Cancel</Button>
                <Button @click="confirm" variant="default" :disabled="!password"
                    >Confirm</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, watch } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(["update:isOpen", "confirm", "cancel"]);

const newQuantity = ref(1);
const password = ref("");

const confirm = () => {
    emit("confirm", {
        newQuantity: newQuantity.value,
        password: password.value,
    });
    password.value = ""; // Clear the password after confirmation
};

const cancel = () => {
    emit("cancel");
    password.value = ""; // Clear the password after cancellation
};
</script>
