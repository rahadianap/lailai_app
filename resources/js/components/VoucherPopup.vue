<template>
    <Dialog :open="isOpen" @update:open="closePopup">
        <DialogContent class="max-w-[600px] sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Apply Voucher</DialogTitle>
                <DialogDescription>
                    Select a voucher to apply to your purchase.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div v-if="isLoading" class="text-center">
                    Loading vouchers...
                </div>
                <div v-else-if="error" class="text-center text-red-500">
                    {{ error }}
                </div>
                <!-- <div v-else-if="vouchers.data.length === 0" class="text-center">
                    No vouchers available
                </div> -->
                <div v-else class="flex flex-col items-start gap-2">
                    <Label htmlFor="voucher" class="text-right">
                        Voucher
                    </Label>
                    <Select
                        v-model="selectedVoucher"
                        @update:modelValue="applyVoucher"
                        class="col-span-3"
                    >
                        <SelectTrigger id="voucher">
                            <SelectValue placeholder="Select a voucher" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-if="vouchers.data.length === 0"
                                class="text-center"
                            >
                                No vouchers available
                            </SelectItem>
                            <SelectItem
                                v-for="voucher in vouchers.data"
                                :key="voucher?.id"
                                :value="voucher"
                            >
                                {{ voucher?.kode_voucher }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter>
                <Button
                    @click="removeVoucher"
                    variant="outline"
                    v-if="selectedVoucher"
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

const emit = defineEmits(["update:isOpen", "applyVoucher", "removeVoucher"]);

const vouchers = ref([]);
const selectedVoucher = ref(null);
const isLoading = ref(false);
const error = ref(null);

onMounted(async () => {
    await fetchVouchers();
});

watch(
    () => props.isOpen,
    async (newValue) => {
        if (newValue) {
            await fetchVouchers();
            selectedVoucher.value = props.appliedVoucher;
        }
    },
);

async function fetchVouchers() {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await fetch("http://127.0.0.1:8000/api/pos/vouchers");
        if (!response.ok) throw new Error("Failed to fetch vouchers");
        vouchers.value = await response.json();
    } catch (err) {
        console.error("Error fetching vouchers:", err);
        error.value = "Failed to load vouchers. Please try again.";
    } finally {
        isLoading.value = false;
    }
}

const applyVoucher = () => {
    if (selectedVoucher.value) {
        emit("applyVoucher", selectedVoucher.value);
    }
};

const removeVoucher = () => {
    selectedVoucher.value = null;
    emit("removeVoucher");
};

const closePopup = () => {
    emit("update:isOpen", false);
    selectedVoucher.value = null;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};
</script>
