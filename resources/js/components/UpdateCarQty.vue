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
                <p
                    v-if="errorMessage"
                    class="text-red-500 bg-red-100 border border-red-400 rounded p-2 mt-2"
                >
                    {{ errorMessage }}
                </p>
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
                <p
                    v-if="passwordErrorMessage"
                    class="text-red-500 bg-red-100 border border-red-400 rounded p-2 mt-2"
                >
                    {{ passwordErrorMessage }}
                </p>
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
    errorMessage: String,
    passwordErrorMessage: String,
    initialQuantity: Number,
});

const emit = defineEmits(["update:isOpen", "confirm", "cancel"]);

const newQuantity = ref(props.initialQuantity);
const password = ref("");

watch(props, (newProps) => {
    if (newProps.isOpen) {
        newQuantity.value = newProps.initialQuantity;
    }
});

const confirm = () => {
    emit("confirm", {
        newQuantity: newQuantity.value,
        password: password.value,
    });
    password.value = ""; // Clear the password after confirmation
};

const cancel = () => {
    emit("cancel");
    password.value = "";
    newQuantity.value = props.initialQuantity; // Reset to initial quantity
};
</script>
