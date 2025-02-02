<template>
    <Dialog :open="isOpen" @update:open="closePopup">
        <DialogContent class="max-w-[600px] sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Select Member</DialogTitle>
                <DialogDescription>
                    Choose a member and optionally apply their points to the
                    purchase.
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
                    <Select v-model="selectedMember" class="col-span-3">
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
                <div v-if="selectedMember" class="flex flex-col gap-2">
                    <p>Selected Member: {{ selectedMember.nama_member }}</p>
                    <p>Member Code: {{ selectedMember.kode_member }}</p>
                    <p>
                        Available Points:
                        {{ formatPoints(selectedMember.point) }}
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button
                    @click="removeMember"
                    variant="outline"
                    v-if="selectedMember"
                >
                    Remove Member
                </Button>
                <Button @click="applyMembers" :disabled="!selectedMember">
                    Apply Member
                </Button>
                <Button
                    @click="applyPoints"
                    :disabled="!selectedMember || selectedMember.point === 0"
                >
                    Apply Points
                </Button>
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

const emit = defineEmits([
    "update:isOpen",
    "removeMember",
    "applyPoints",
    "applyMembers",
]);

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
            selectedMember.value = props.appliedMember || null;
        }
    },
);

watch(
    () => props.appliedMember,
    (newValue) => {
        selectedMember.value = newValue || null;
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

const removeMember = () => {
    selectedMember.value = null;
    emit("removeMember");
};

const applyMembers = () => {
    if (selectedMember.value) {
        emit("applyMembers", {
            kode_member: selectedMember.value.kode_member,
            point: selectedMember.value.point,
        });
    }
};

const applyPoints = () => {
    if (selectedMember.value && selectedMember.value.point > 0) {
        emit("applyPoints", {
            kode_member: selectedMember.value.kode_member,
            point: selectedMember.value.point,
        });
    }
};

const closePopup = () => {
    emit("update:isOpen", false);
};

const formatPoints = (points) => {
    return new Intl.NumberFormat("id-ID").format(Number(points));
};
</script>
