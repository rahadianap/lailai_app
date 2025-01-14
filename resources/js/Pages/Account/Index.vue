<script setup>
import Layout from "../../Layout/App.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { valueUpdater } from "@/lib/utils";
import { PlusCircledIcon, ChevronDownIcon } from "@radix-icons/vue";
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from "@tanstack/vue-table";
import { h, ref, computed } from "vue";
import DropdownAction from "./components/Table.vue";
import { Badge } from "@/components/ui/badge";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Form } from "@/components/ui/form";
import { Label } from "@/components/ui/label";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { router } from "@inertiajs/vue3";
import {
    ChevronRightIcon,
    ChevronLeftIcon,
    DoubleArrowLeftIcon,
    DoubleArrowRightIcon,
} from "@radix-icons/vue";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import SearchableSelect from "../../components/SearchableSelect.vue";

const props = defineProps({
    data: Array,
    permissions: Object,
});

const data = props.data.data;

const canViewAccount = computed(() => props.permissions.account_view);
const canCreateAccount = computed(() => props.permissions.account_create);
const canEditAccount = computed(() => props.permissions.account_edit);
const canDeleteAccount = computed(() => props.permissions.account_delete);

const selectedTipe = ref(null);

const selectedLevel = ref(null);

const selectedGroup = ref(null);

const onLevelSelect = (level) => {
    selectedLevel.value = level;
    console.log(level);
};

const onTipeSelect = (tipe) => {
    selectedTipe.value = tipe;
    console.log(tipe);
};

const onGroupSelect = (group) => {
    selectedGroup.value = group;
    console.log(group);
};

const showCreate = ref(false);

const showDialogCreate = () => {
    if (canCreateAccount.value) {
        showCreate.value = true;
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to create data.",
            icon: "error",
        });
    }
};

const columns = [
    {
        accessorKey: "nomor_account",
        header: () => h("div", { class: "text-left" }, "Nomor Account"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nomor_account"),
            );
        },
    },
    {
        accessorKey: "nama_account",
        header: () => h("div", { class: "text-left" }, "Nama Account"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_account"),
            );
        },
    },
    {
        accessorKey: "nama_kelompok_account",
        header: () => h("div", { class: "text-left" }, "Group"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_kelompok_account"),
            );
        },
    },
    {
        accessorKey: "tipe_account",
        header: () => h("div", { class: "text-center" }, "Tipe Account"),
        cell: ({ row }) => {
            const jenis = row.getValue("tipe_account");
            if (jenis === "INDUK") {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "INDUK"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "ANAK"),
                );
            }
        },
    },
    {
        accessorKey: "is_aktif",
        header: () => h("div", { class: "text-center" }, "Status"),
        cell: ({ row }) => {
            const status = row.getValue("is_aktif");
            if (status == true) {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "Active"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "Inactive"),
                );
            }
        },
    },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row }) => {
            const group = row.original;

            return h(
                "div",
                { class: "relative text-right mr-4" },
                h(DropdownAction, {
                    group,
                    permissions: props.permissions,
                    onEdit: () => onEdit(group.id),
                    onDelete: () => onDelete(group.id),
                }),
            );
        },
    },
];

const sorting = ref([]);
const columnFilters = ref([]);
const columnVisibility = ref({});
const rowSelection = ref({});
const expanded = ref({});
const pageSizes = [5, 10, 25, 50];
const pagination = ref({
    pageIndex: props.data.current_page - 1,
    pageSize: props.data.per_page,
});

const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    pageCount: props.data.last_page,
    manualPagination: true,
    onPaginationChange: (updater) => {
        if (typeof updater === "function") {
            pagination.value = updater(pagination.value);
        } else {
            pagination.value = updater;
        }
        router.get(
            "/accounts",
            {
                page: pagination.value.pageIndex + 1,
                per_page: pagination.value.pageSize,
                sort_field: sorting.value[0]?.id,
                sort_direction:
                    sorting.value.length == 0
                        ? undefined
                        : sorting.value[0]?.desc
                          ? "desc"
                          : "asc",
            },
            { preserveState: false, preserveScroll: true },
        );
    },
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
        get pagination() {
            return pagination.value;
        },
    },
});

const errors = ref({});

const form = useForm({
    id: null,
    nomor_account: "",
    nama_account: "",
    nama_kelompok_account: "",
    level: "",
    kas_bank: false,
    tipe_account: "",
    saldo_awal: 0,
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.nomor_account = "";
    form.nama_account = "";
    form.nama_kelompok_account = "";
    form.level = "";
    form.kas_bank = false;
    form.tipe_account = "";
    form.saldo_awal = 0;
};

const submit = () => {
    const url = form.id ? `/accounts/${form.id}` : "/accounts";
    const method = form.id ? "put" : "post";
    form[method](url, {
        preserveState: true,
        onError: (error) => {
            errors.value = error;
            Swal.fire({
                title: "Oops!",
                text: "Something went wrong",
                icon: "error",
            });
        },
        onSuccess: () => {
            showCreate.value = false;
            Swal.fire({
                title: "Yeay!",
                text: "Your work has been saved",
                icon: "success",
                showConfirmButton: false,
            });
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        },
    });
};

const onEdit = async (id) => {
    if (canEditAccount.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/accounts/${id}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!res.ok) {
                console.error(res);
            }
            const data = await res.json();
            // Set to form
            form.id = data.data.id;
            form.nomor_account = data.data.nomor_account;
            form.nama_account = data.data.nama_account;
            form.nama_kelompok_account = data.data.nama_kelompok_account;
            form.level = data.data.level;
            form.kas_bank = data.data.kas_bank === "1" ? true : false;
            form.tipe_account = data.data.tipe_account;
            form.saldo_awal = data.data.saldo_awal;
        } catch (error) {
            console.error(error);
        }
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to edit data.",
            icon: "error",
        });
    }
};

const onDelete = (id) => {
    if (canDeleteAccount.value) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                const form = useForm({});
                form.delete(`/accounts/${id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Your product has been deleted.",
                            "success",
                        );
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "There was a problem deleting the product.",
                            "error",
                        );
                    },
                });
            }
        });
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to delete data.",
            icon: "error",
        });
    }
};
</script>

<template>
    <Layout>
        <div class="flex items-center">
            <h1 class="text-lg font-semibold md:text-2xl">Chart of Account</h1>
        </div>
        <div v-if="canViewAccount" class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="
                        table.getColumn('nomor_account')?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter nomor account..."
                    @update:model-value="
                        table.getColumn('nomor_account')?.setFilterValue($event)
                    "
                />
                <Button class="ml-4" variant="outline" @click="showDialogCreate"
                    ><PlusCircledIcon class="w-5 h-5"></PlusCircledIcon>Create
                    New</Button
                >
            </div>
            <div class="border rounded-md">
                <Table>
                    <TableHeader>
                        <TableRow
                            v-for="headerGroup in table.getHeaderGroups()"
                            :key="headerGroup.id"
                        >
                            <TableHead
                                v-for="header in headerGroup.headers"
                                :key="header.id"
                            >
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :props="header.getContext()"
                                    :render="header.column.columnDef.header"
                                />
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="table.getRowModel().rows?.length">
                            <template
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                            >
                                <TableRow
                                    :data-state="
                                        row.getIsSelected() && 'selected'
                                    "
                                >
                                    <TableCell
                                        v-for="cell in row.getVisibleCells()"
                                        :key="cell.id"
                                    >
                                        <FlexRender
                                            :props="cell.getContext()"
                                            :render="cell.column.columnDef.cell"
                                        />
                                    </TableCell>
                                </TableRow>
                            </template>
                        </template>

                        <TableRow v-else>
                            <TableCell
                                :colspan="columns.length"
                                class="h-24 text-center"
                            >
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex items-center justify-end py-4 space-x-2">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">Rows per page</p>
                    <Select
                        :model-value="
                            table.getState().pagination.pageSize.toString()
                        "
                        @update:model-value="
                            (value) => table.setPageSize(Number(value))
                        "
                    >
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue
                                :placeholder="
                                    table
                                        .getState()
                                        .pagination.pageSize.toString()
                                "
                            />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem
                                v-for="pageSize in pageSizes"
                                :key="pageSize"
                                :value="pageSize.toString()"
                            >
                                {{ pageSize }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div
                    class="flex w-[200px] items-center justify-center text-sm font-medium"
                >
                    Page {{ table.getState().pagination.pageIndex + 1 }} of
                    {{ table.getPageCount() }}
                </div>
                <div class="space-x-2">
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            class="hidden w-8 h-8 p-0 lg:flex"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.setPageIndex(0)"
                        >
                            <DoubleArrowLeftIcon class="w-4 h-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="w-8 h-8 p-0"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            <ChevronLeftIcon class="w-4 h-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="w-8 h-8 p-0"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            <ChevronRightIcon class="w-4 h-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="hidden w-8 h-8 p-0 lg:flex"
                            :disabled="!table.getCanNextPage()"
                            @click="
                                table.setPageIndex(table.getPageCount() - 1)
                            "
                        >
                            <DoubleArrowRightIcon class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
        <Dialog
            v-model:open="showCreate"
            @update:open="
                (val) => {
                    if (!val) resetForm();
                    showCreate = val;
                }
            "
        >
            <Form>
                <DialogContent class="w-[1000px]">
                    <DialogHeader>
                        <DialogTitle
                            >{{ form.id ? "Edit" : "Create" }} Data Master
                            CoA</DialogTitle
                        >
                        <DialogDescription> Data master CoA </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <Label for="nomor_account"> Nomor Account </Label>
                            <Input
                                id="nomor_account"
                                v-model="form.nomor_account"
                                type="text"
                                required
                            />
                            <span
                                v-if="errors?.nomor_account"
                                class="text-sm text-red-500"
                                >{{ errors.nomor_account }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_account"> Nama Account </Label>
                            <Input
                                id="nama_account"
                                v-model="form.nama_account"
                                type="text"
                                required
                            />
                            <span
                                v-if="errors?.nama_account"
                                class="text-sm text-red-500"
                                >{{ errors.nama_account }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_kelompok_account"> Group </Label>
                            <SearchableSelect
                                required
                                v-model="form.nama_kelompok_account"
                                placeholder="Search groups..."
                                api-endpoint="http://127.0.0.1:8000/api/coa/groups"
                                value-field="nama_kelompok_account"
                                display-field="nama_kelompok_account"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading groups..."
                                no-results-text="No groups found"
                                load-more-text="Load more groups"
                                @select="onGroupSelect"
                            />
                            <span
                                v-if="errors?.nama_kelompok_account"
                                class="text-sm text-red-500"
                                >{{ errors.nama_kelompok_account }}</span
                            >
                        </div>
                        <div>
                            <Label for="level"> Level </Label>
                            <Select
                                id="level"
                                v-model="form.level"
                                type="text"
                                required
                                @select="onLevelSelect"
                            >
                                <SelectTrigger
                                    class="border rounded-md border-gray-900 required:border-blue-500"
                                >
                                    <SelectValue
                                        placeholder="Select account level"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="1"> 1 </SelectItem>
                                        <SelectItem value="2"> 2 </SelectItem>
                                        <SelectItem value="3"> 3 </SelectItem>
                                        <SelectItem value="4"> 4 </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <span
                                v-if="errors?.level"
                                class="text-sm text-red-500"
                                >{{ errors.level }}</span
                            >
                        </div>
                        <div>
                            <Label for="tipe_account"> Tipe Account </Label>
                            <Select
                                id="tipe_account"
                                v-model="form.tipe_account"
                                type="text"
                                required
                                @select="onTipeSelect"
                            >
                                <SelectTrigger
                                    class="border rounded-md border-gray-900 required:border-blue-500"
                                >
                                    <SelectValue placeholder="Select a group" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="INDUK">
                                            INDUK
                                        </SelectItem>
                                        <SelectItem value="ANAK">
                                            ANAK
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <span
                                v-if="errors?.tipe_account"
                                class="text-sm text-red-500"
                                >{{ errors.tipe_account }}</span
                            >
                        </div>
                        <div>
                            <Label for="saldo_awal"> Saldo Awal </Label>
                            <Input
                                id="saldo_awal"
                                v-model="form.saldo_awal"
                                type="number"
                                min="0"
                                required
                            />
                            <span
                                v-if="errors?.saldo_awal"
                                class="text-sm text-red-500"
                                >{{ errors.saldo_awal }}</span
                            >
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-4">
                        <Checkbox v-model="form.kas_bank" />
                        <label
                            for="terms"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Kas Bank
                        </label>
                        <span
                            v-if="errors?.kas_bank"
                            class="text-sm text-red-500"
                            >{{ errors.kas_bank }}</span
                        >
                    </div>
                    <DialogFooter>
                        <Button @click="submit"> Save </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>
