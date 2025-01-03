<template>
    <Dialog :open="isOpen" @update:open="closePopup">
        <DialogContent class="max-w-[600px] sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Select member</DialogTitle>
                <DialogDescription>
                    Select a member to apply points discount to your purchase.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div v-if="isLoading" class="text-center">
                    Loading members...
                </div>
                <div v-else-if="error" class="text-center text-red-500">
                    {{ error }}
                </div>
                <div v-else class="flex flex-col items-start gap-2">
                    <Label htmlFor="member" class="text-right">
                        Nomor Member
                    </Label>
                    <Select
                        v-model="selectedMember"
                        @update:modelValue="applyPoint"
                        class="col-span-3"
                    >
                        <SelectTrigger id="member">
                            <SelectValue placeholder="Select a member" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-if="members.data.length === 0"
                                class="text-center"
                            >
                                No members available
                            </SelectItem>
                            <SelectItem
                                v-for="member in members.data"
                                :key="member?.id"
                                :value="member"
                            >
                                {{ member?.kode_member }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter>
                <Button
                    @click="removePoint"
                    variant="outline"
                    v-if="selectedMember"
                    >Remove Voucher</Button
                >
                <Button @click="closePopup">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";

const props = defineProps({
    isOpen: Boolean,
    appliedVoucher: Object,
});

const emit = defineEmits(["update:isOpen", "applyPoint", "removePoint"]);

const members = ref([]);
const selectedMember = ref(null);
const isLoading = ref(false);
const error = ref(null);

onMounted(async () => {
    await fetchMembers();
});

watch(
    () => props.isOpen,
    async (newValue) => {
        if (newValue) {
            await fetchMembers();
            selectedMember.value = props.appliedVoucher;
        }
    },
);

async function fetchMembers() {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await fetch("http://127.0.0.1:8000/api/pos/members");
        if (!response.ok) throw new Error("Failed to fetch members");
        members.value = await response.json();
    } catch (err) {
        console.error("Error fetching members:", err);
        error.value = "Failed to load members. Please try again.";
    } finally {
        isLoading.value = false;
    }
}

const applyPoint = () => {
    if (selectedMember.value) {
        emit("applyPoint", selectedMember.value);
    }
};

const removePoint = () => {
    selectedMember.value = null;
    emit("removePoint");
};

const closePopup = () => {
    emit("update:isOpen", false);
    selectedMember.value = null;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};
</script>
