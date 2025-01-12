<script setup>
import Layout from "../../Layout/App.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
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

const props = defineProps({
    data: Array,
    permissions: Object,
});

const data = props.data.data;

const canViewKelompokAccount = computed(
    () => props.permissions.kelompok_account_view,
);
const canCreateKelompokAccount = computed(
    () => props.permissions.kelompok_account_create,
);
const canEditKelompokAccount = computed(
    () => props.permissions.kelompok_account_edit,
);
const canDeleteKelompokAccount = computed(
    () => props.permissions.kelompok_account_delete,
);

const selectedGroup = ref(null);

const selectedJenis = ref(null);

const onGroupSelect = (group) => {
    selectedGroup.value = group;
    console.log(group);
};

const onJenisSelect = (jenis) => {
    selectedJenis.value = jenis;
    console.log(jenis);
};

const showCreate = ref(false);

const showDialogCreate = () => {
    if (canCreateKelompokAccount.value) {
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
        accessorKey: "kode_kelompok_account",
        header: () => h("div", { class: "text-left" }, "Kode Group"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_kelompok_account"),
            );
        },
    },
    {
        accessorKey: "kelompok",
        header: () => h("div", { class: "text-left" }, "Group"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kelompok"),
            );
        },
    },
    {
        accessorKey: "nama_kelompok_account",
        header: () => h("div", { class: "text-left" }, "Nama Group"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_kelompok_account"),
            );
        },
    },
    {
        accessorKey: "jenis_kelompok_account",
        header: () => h("div", { class: "text-center" }, "Jenis Group"),
        cell: ({ row }) => {
            const jenis = row.getValue("jenis_kelompok_account");
            if (jenis == 1) {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "Debit"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "Kredit"),
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
            "/account-group",
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
    kode_kelompok_account: "",
    kelompok: "",
    nama_kelompok_account: "",
    jenis_kelompok_account: "",
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.kode_kelompok_account = "";
    form.kelompok = "";
    form.nama_kelompok_account = "";
    form.jenis_kelompok_account = "";
};

const submit = () => {
    const url = form.id ? `/account-group/${form.id}` : "/account-group";
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
    if (canEditKelompokAccount.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/account-group/${id}`, {
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
            form.kode_kelompok_account = data.data.kode_kelompok_account;
            form.kelompok = data.data.kelompok;
            form.nama_kelompok_account = data.data.nama_kelompok_account;
            form.jenis_kelompok_account = data.data.jenis_kelompok_account;
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
    if (canDeleteKelompokAccount.value) {
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
                form.delete(`/account-group/${id}`, {
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
            <h1 class="text-lg font-semibold md:text-2xl">Account Group</h1>
        </div>
        <div v-if="canViewKelompokAccount" class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="
                        table
                            .getColumn('kode_kelompok_account')
                            ?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter kode kelompok..."
                    @update:model-value="
                        table
                            .getColumn('kode_kelompok_account')
                            ?.setFilterValue($event)
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
                            Account Group</DialogTitle
                        >
                        <DialogDescription>
                            Data master account group
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <Label for="kelompok"> Group </Label>
                            <Select
                                id="kelompok"
                                v-model="form.kelompok"
                                type="text"
                                required
                                @select="onGroupSelect"
                            >
                                <SelectTrigger
                                    class="border rounded-md border-gray-900 required:border-blue-500"
                                >
                                    <SelectValue placeholder="Select a group" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="AKTIVA">
                                            AKTIVA
                                        </SelectItem>
                                        <SelectItem value="PASSIVA">
                                            PASSIVA
                                        </SelectItem>
                                        <SelectItem value="MODAL">
                                            MODAL
                                        </SelectItem>
                                        <SelectItem value="PENDAPATAN">
                                            PENDAPATAN
                                        </SelectItem>
                                        <SelectItem value="RETUR">
                                            RETUR
                                        </SelectItem>
                                        <SelectItem value="BIAYA">
                                            BIAYA
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <span
                                v-if="errors?.kelompok"
                                class="text-sm text-red-500"
                                >{{ errors.kelompok }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_kelompok_account">
                                Nama Group
                            </Label>
                            <Input
                                id="nama_kelompok_account"
                                v-model="form.nama_kelompok_account"
                                type="text"
                                required
                            />
                            <span
                                v-if="errors?.nama_kelompok_account"
                                class="text-sm text-red-500"
                                >{{ errors.nama_kelompok_account }}</span
                            >
                        </div>
                        <div>
                            <Label for="jenis_kelompok_account">
                                Jenis Group
                            </Label>
                            <Select
                                id="jenis_kelompok_account"
                                v-model="form.jenis_kelompok_account"
                                type="text"
                                required
                                @select="onJenisSelect"
                            >
                                <SelectTrigger
                                    class="border rounded-md border-gray-900 required:border-blue-500"
                                >
                                    <SelectValue placeholder="Select a group" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="1">
                                            Debit
                                        </SelectItem>
                                        <SelectItem value="0">
                                            Kredit
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <span
                                v-if="errors?.jenis_kelompok_account"
                                class="text-sm text-red-500"
                                >{{ errors.jenis_kelompok_account }}</span
                            >
                        </div>
                    </div>
                    <DialogFooter>
                        <Button @click="submit"> Save </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>
