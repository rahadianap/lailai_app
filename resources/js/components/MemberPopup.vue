<template>
    <Dialog :open="isOpen" @update:open="closePopup">
        <DialogContent class="max-w-[600px] sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Select Member</DialogTitle>
                <DialogDescription>
                    Choose a member to apply their points to the purchase.
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
                    <Label htmlFor="member" class="text-right"> Member </Label>
                    <Select
                        v-model="selectedMemberId"
                        @update:modelValue="applyMember"
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
                                :key="member.id"
                                :value="member"
                            >
                                {{ member.nama_member }} -
                                {{ formatPoints(member.point) }} points
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <DialogFooter>
                <Button
                    @click="removeMember"
                    variant="outline"
                    v-if="selectedMemberId"
                    >Remove Member</Button
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
    appliedMember: Object,
});

const emit = defineEmits(["update:isOpen", "applyMember", "removeMember"]);

const members = ref([]);
const selectedMemberId = ref(null);
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
            selectedMemberId.value = props.appliedMember
                ? props.appliedMember.id
                : null;
        }
    },
);

watch(
    () => props.appliedMember,
    (newValue) => {
        selectedMemberId.value = newValue ? newValue.id : null;
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

const applyMember = () => {
    if (selectedMemberId.value) {
        emit("applyMember", selectedMemberId.value);
    }
};

const removeMember = () => {
    selectedMemberId.value = null;
    emit("removeMember");
};

const closePopup = () => {
    emit("update:isOpen", false);
};

const formatPoints = (points) => {
    return new Intl.NumberFormat("id-ID").format(Number(points));
};
</script>
