<template>
    <div class="searchable-select">
        <!-- <label :for="id" class="block text-sm font-medium text-gray-700">{{
            label
        }}</label> -->
        <div class="relative">
            <input
                :id="id"
                v-model="displayValue"
                type="text"
                class="flex w-full px-3 py-1 text-sm transition-colors bg-transparent border rounded-md shadow-sm h-9 border-input file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                :placeholder="placeholder"
                @focus="onFocus"
                @input="onInput"
            />
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-2"
                @click="toggleOptions"
            >
                <svg
                    class="w-5 h-5 text-gray-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
            <div
                v-if="showOptions"
                class="absolute z-10 w-full mt-1 overflow-auto bg-white border border-gray-300 rounded-md shadow-lg max-h-60"
            >
                <ul>
                    <li
                        v-for="option in options"
                        :key="option[valueField]"
                        @click="selectOption(option)"
                        class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                    >
                        {{ option[displayField] }}
                    </li>
                </ul>
                <div v-if="loading" class="px-4 py-2 text-gray-500">
                    {{ loadingText }}
                </div>
                <div
                    v-if="!loading && options.length === 0"
                    class="px-4 py-2 text-gray-500"
                >
                    {{ noResultsText }}
                </div>
                <div
                    v-if="pagination.current_page < pagination.last_page"
                    class="px-4 py-2 text-center"
                >
                    <button
                        @click="loadMore"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        {{ loadMoreText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { debounce } from "lodash-es";

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
        default: "searchable-select",
    },
    apiEndpoint: {
        type: String,
        required: true,
    },
    valueField: {
        type: String,
        default: "id",
    },
    displayField: {
        type: String,
        default: "name",
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
const showOptions = ref(true);
const loading = ref(false);
const pagination = ref({
    current_page: 0,
    last_page: 1,
    total: 0,
});

const displayValue = computed({
    get: () =>
        selectedOption.value
            ? selectedOption.value[props.displayField]
            : search.value,
    set: (value) => {
        if (
            !selectedOption.value ||
            value !== selectedOption.value[props.displayField]
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
    try {
        const response = await fetch(
            `${props.apiEndpoint}?page=${pagination.value.current_page + 1}&${props.searchParam}=${encodeURIComponent(search.value)}&per_page=${props.perPage}`,
        );
        const data = await response.json();
        options.value = [...options.value, ...data.data];
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
    } catch (error) {
        console.error("Error fetching options:", error);
    } finally {
        loading.value = false;
    }
};

const loadMore = () => {
    if (
        !loading.value &&
        pagination.value.current_page < pagination.value.last_page
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

// Close options when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest(".searchable-select")) {
        showOptions.value = false;
    }
};

const fetchSelectedOption = async (value) => {
  try {
    const response = await fetch(`${props.apiEndpoint}?${props.valueField}=${value}`)
    const data = await response.json()
    // Assuming the API returns an array of options
    const option = data.data.find(item => item[props.valueField] == value)
    if (option) {
      selectedOption.value = option
      search.value = option[props.displayField]
    } else {
      console.error('Selected option not found in API response')
      selectedOption.value = null
      search.value = ''
    }
  } catch (error) {
    console.error('Error fetching selected option:', error)
    selectedOption.value = null
    search.value = ''
  }
}

watch(() => props.modelValue, (newValue) => {
  if (newValue === null) {
    selectedOption.value = null
    search.value = ''
  } else if (!selectedOption.value || newValue != selectedOption.value[props.valueField]) {
    fetchSelectedOption(newValue)
  }
}, { immediate: true })

watch(selectedOption, (newValue) => {
  if (newValue) {
    search.value = newValue[props.displayField]
  }
})

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.searchable-select {
    position: relative;
    width: 100%;
    max-width: 300px;
}
</style>
