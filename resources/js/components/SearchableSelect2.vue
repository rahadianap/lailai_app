<template>
    <div class="relative">
        <input
            type="text"
            v-model="searchQuery"
            @focus="handleFocus"
            @blur="handleBlur"
            @input="handleInput"
            :placeholder="placeholder"
            class="flex w-full px-3 py-1 text-sm transition-colors bg-transparent border rounded-md shadow-sm h-9 border-input file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
        />
        <div
            v-if="isOpen"
            class="absolute z-10 w-full mt-1 bg-white border rounded shadow-lg max-h-60 overflow-auto"
        >
            <div v-if="loading" class="p-2 text-gray-500">
                {{ loadingText }}
            </div>
            <div v-else-if="options.length === 0" class="p-2 text-gray-500">
                {{ noResultsText }}
            </div>
            <div
                v-else
                v-for="option in options"
                :key="option[valueField]"
                @mousedown="selectOption(option)"
                class="p-2 hover:bg-gray-100 cursor-pointer"
            >
                {{ formatOptionDisplay(option) }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
//import debounce from 'lodash/debounce';

const props = defineProps({
    modelValue: [String, Number, Object],
    apiEndpoint: String,
    valueField: String,
    displayFields: {
        type: Array,
        default: () => ["kode_barcode", "nama_barang"],
    },
    searchFields: {
        type: Array,
        default: () => ["kode_barcode", "nama_barang"],
    },
    perPage: Number,
    //debounceTime: Number,
    placeholder: String,
    loadingText: String,
    noResultsText: String,
    defaultOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "select"]);

const isOpen = ref(props.defaultOpen);
const options = ref([]);
const loading = ref(false);
const searchQuery = ref("");

const displayValue = computed(() => {
    if (props.modelValue && typeof props.modelValue === "object") {
        return formatOptionDisplay(props.modelValue);
    }
    return searchQuery.value;
});

const formatOptionDisplay = (option) => {
    return props.displayFields.map((field) => option[field]).join(" - ");
};

const fetchOptions = async () => {
    if (!searchQuery.value) {
        options.value = [];
        return;
    }

    loading.value = true;
    try {
        const searchParams = new URLSearchParams();
        props.searchFields.forEach((field) => {
            searchParams.append(`search[${field}]`, searchQuery.value);
        });
        searchParams.append("per_page", props.perPage);

        const response = await fetch(
            `${props.apiEndpoint}?${searchParams.toString()}`,
        );
        const data = await response.json();
        options.value = data.data; // Access the 'data' property of the paginated response
    } catch (error) {
        console.error("Error fetching options:", error);
        options.value = [];
    } finally {
        loading.value = false;
    }
};

const handleInput = (event) => {
    searchQuery.value = event.target.value;
    isOpen.value = true;
    fetchOptions();
};

const handleFocus = () => {
    if (searchQuery.value) {
        isOpen.value = true;
    }
};

const handleBlur = () => {
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

const selectOption = (option) => {
    emit("update:modelValue", option[props.valueField]);
    emit("select", option);
    searchQuery.value = formatOptionDisplay(option);
    isOpen.value = false;
};

watch(
    () => props.modelValue,
    (newValue) => {
        if (!newValue) {
            searchQuery.value = "";
        }
    },
);
</script>
