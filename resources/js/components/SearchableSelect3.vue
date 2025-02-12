<template>
    <div class="inertia-searchable-select" @click.stop>
        <input
            :id="id"
            :placeholder="placeholder"
            v-model="displayValue"
            @focus="onFocus"
            @input="onInput"
            class="search-input"
        />
        <div v-if="showOptions" class="options-container">
            <div v-if="loading" class="loading-text">{{ loadingText }}</div>
            <div v-else>
                <div v-if="options.length === 0" class="no-results-text">
                    {{ noResultsText }}
                </div>
                <ul>
                    <li
                        v-for="option in options"
                        :key="option[valueField]"
                        @click="selectOption(option)"
                        class="option-item"
                    >
                        {{ displayOptionFields(option) }}
                    </li>
                </ul>
                <div
                    v-if="pagination.current_page < pagination.last_page"
                    class="load-more-text"
                    @click="loadMore"
                >
                    {{ loadMoreText }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { debounce } from "lodash-es";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    modelValue: {
        type: [String, Number, Object],
        default: null,
    },
    placeholder: {
        type: String,
        default: "Search...",
    },
    id: {
        type: String,
        default: "inertia-searchable-select",
    },
    apiEndpoint: {
        type: String,
        required: true,
    },
    valueField: {
        type: String,
        default: "id",
    },
    displayFields: {
        type: Array,
        default: () => ["name"],
    },
    displaySeparator: {
        type: String,
        default: " - ",
    },
    searchParam: {
        type: String,
        default: "search",
    },
    perPage: {
        type: Number,
        default: 10,
    },
    debounceTime: {
        type: Number,
        default: 300,
    },
    loadingText: {
        type: String,
        default: "Loading...",
    },
    noResultsText: {
        type: String,
        default: "No results found",
    },
    loadMoreText: {
        type: String,
        default: "Load more",
    },
});

const emit = defineEmits(["update:modelValue", "select"]);

const search = ref("");
const options = ref([]);
const selectedOption = ref(null);
const showOptions = ref(false);
const loading = ref(false);
const pagination = ref({
    current_page: 0,
    last_page: 1,
    total: 0,
});

const displayOptionFields = (option) => {
    return props.displayFields
        .map((field) => option[field])
        .filter(Boolean)
        .join(props.displaySeparator);
};

const displayValue = computed({
    get: () =>
        selectedOption.value
            ? displayOptionFields(selectedOption.value)
            : search.value,
    set: (value) => {
        if (
            !selectedOption.value ||
            value !== displayOptionFields(selectedOption.value)
        ) {
            search.value = value;
            selectedOption.value = null;
            emit("update:modelValue", null);
        }
    },
});

const debouncedSearch = debounce(() => {
    fetchOptions(true);
}, props.debounceTime);

const fetchOptions = async (reset = false) => {
    loading.value = true;
    if (reset) {
        pagination.value.current_page = 0;
        options.value = [];
    }
    Inertia.get(
        props.apiEndpoint,
        {
            [props.searchParam]: search.value,
            page: pagination.value.current_page + 1,
            per_page: props.perPage,
        },
        {
            onSuccess: (response) => {
                options.value = [...options.value, ...response.props.data];
                pagination.value = {
                    current_page: response.props.current_page,
                    last_page: response.props.last_page,
                    total: response.props.total,
                };
                loading.value = false;
            },
            onError: (error) => {
                console.error("Error fetching options:", error);
                loading.value = false;
            },
        },
    );
};

const fetchSelectedOption = async (value) => {
    loading.value = true;
    try {
        const response = await Inertia.get(`${props.apiEndpoint}/${value}`);
        if (response.props && response.props.data) {
            selectedOption.value = response.props.data;
        } else {
            console.error("Unexpected response format:", response);
        }
        loading.value = false;
    } catch (error) {
        console.error("Error fetching selected option:", error);
        loading.value = false;
    }
};

const loadMore = () => {
    if (
        !loading.value &&
        pagination.value.current_page < pagination.last_page
    ) {
        fetchOptions();
    }
};

const selectOption = (option) => {
    selectedOption.value = option;
    showOptions.value = false;
    emit("update:modelValue", option[props.valueField]);
    emit("select", option);
};

const onFocus = () => {
    showOptions.value = true;
};

const onInput = () => {
    debouncedSearch();
};

const toggleOptions = () => {
    showOptions.value = !showOptions.value;
    if (showOptions.value) {
        fetchOptions(true);
    }
};

const handleClickOutside = (event) => {
    if (!event.target.closest(".inertia-searchable-select")) {
        showOptions.value = false;
    }
};

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue === null) {
            selectedOption.value = null;
            search.value = "";
        } else if (
            !selectedOption.value ||
            newValue != selectedOption.value[props.valueField]
        ) {
            fetchSelectedOption(newValue);
        }
    },
    { immediate: true },
);

watch(selectedOption, (newValue) => {
    if (newValue) {
        search.value = displayOptionFields(newValue);
    }
});

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.inertia-searchable-select {
    position: relative;
    width: 100%;
    max-width: 300px;
}

.search-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.options-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
}

.option-item {
    padding: 8px;
    cursor: pointer;
}

.option-item:hover {
    background: #f0f0f0;
}

.loading-text,
.no-results-text,
.load-more-text {
    padding: 8px;
    text-align: center;
    cursor: pointer;
}
</style>
