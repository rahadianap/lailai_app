<script setup>
import Layout from "../../Layout/App.vue";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
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
import { PlusCircledIcon } from "@radix-icons/vue";
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
    data: Object,
    permissions: Object,
    message: Object,
});

const data = props.data.data;

const showCreate = ref(false);

const canViewSuppliers = computed(() => props.permissions.suppliers_view);
const canCreateSuppliers = computed(() => props.permissions.suppliers_create);
const canEditSuppliers = computed(() => props.permissions.suppliers_edit);
const canDeleteSuppliers = computed(() => props.permissions.suppliers_delete);

const showDialogCreate = () => {
    if (canCreateSuppliers.value) {
        showCreate.value = true;
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to create suppliers.",
            icon: "error",
        });
    }
};

const columns = [
    {
        accessorKey: "kode_supplier",
        header: () => h("div", { class: "text-left ml-4" }, "Kode Supplier"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium ml-4" },
                row.getValue("kode_supplier"),
            );
        },
    },
    {
        accessorKey: "nama_supplier",
        header: () => h("div", { class: "text-left" }, "Nama Supplier"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_supplier"),
            );
        },
    },
    {
        accessorKey: "alamat",
        header: () => h("div", { class: "text-left" }, "Alamat"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("alamat"),
            );
        },
    },
    {
        accessorKey: "no_hp1",
        header: () => h("div", { class: "text-left" }, "Nomor HP"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("no_hp1"),
            );
        },
    },
    {
        accessorKey: "is_retur",
        header: () => h("div", { class: "text-center" }, "Returable"),
        cell: ({ row }) => {
            const status = row.getValue("is_retur");
            if (status == true) {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "Retur"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "Non-Retur"),
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
            const supplier = row.original;

            return h(
                "div",
                { class: "relative text-right mr-4" },
                h(DropdownAction, {
                    supplier,
                    permissions: props.permissions,
                    onEdit: () => onEdit(supplier.id),
                    onExpand: row.toggleExpanded,
                    onDelete: () => onDelete(supplier.id),
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
            "/suppliers",
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
        get expanded() {
            return expanded.value;
        },
        get pagination() {
            return pagination.value;
        },
    },
});

const errors = ref({});

const form = useForm({
    id: null,
    no_ktp: "",
    npwp: "",
    nama_supplier: "",
    alamat: "",
    tgl_lahir: "",
    no_hp1: "",
    no_hp2: "",
    email: "",
    keterangan: "",
    is_retur: false,
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.no_ktp = "";
    form.npwp = "";
    form.nama_supplier = "";
    form.alamat = "";
    form.tgl_lahir = "";
    form.no_hp1 = "";
    form.no_hp2 = "";
    form.email = "";
    form.keterangan = "";
    form.is_retur = false;
};

const submit = () => {
    const url = form.id ? `/suppliers/${form.id}` : "/suppliers";
    const method = form.id ? "put" : "post";
    form[method](url, {
        preserveState: true,
        onError: (error) => {
            errors.value = error;
            Swal.fire({
                title: "Oops!",
                text: message.error,
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
            }, 1000);
        },
    });
};

const onEdit = async (id) => {
    if (canEditSuppliers.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/suppliers/${id}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!res.ok) {
                console.error("Error ");
            }
            const data = await res.json();
            // Set to form
            form.id = data.data.id;
            form.nama_supplier = data.data.nama_supplier;
            form.no_ktp = data.data.no_ktp;
            form.npwp = data.data.npwp;
            form.alamat = data.data.alamat;
            form.tgl_lahir = data.data.tgl_lahir;
            form.no_hp1 = data.data.no_hp1;
            form.no_hp2 = data.data.no_hp2;
            form.email = data.data.email;
            form.keterangan = data.data.keterangan;
            form.is_retur = data.data.is_retur === "1" ? true : false;
        } catch (error) {
            console.error(error);
        }
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to edit suppliers.",
            icon: "error",
        });
    }
};

const onDelete = (id) => {
    if (canDeleteSuppliers.value) {
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
                form.delete(`/suppliers/${id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Your supplier has been deleted.",
                            "success",
                        );
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "There was a problem deleting the supplier.",
                            "error",
                        );
                    },
                });
            }
        });
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to delete suppliers.",
            icon: "error",
        });
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(price);
};
</script>

<template>
    <Layout>
        <div class="flex items-center">
            <h1 class="text-lg font-semibold md:text-2xl">Suppliers</h1>
        </div>
        <div v-if="canViewSuppliers" class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="
                        table.getColumn('nama_supplier')?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter supplier..."
                    @update:model-value="
                        table.getColumn('nama_supplier')?.setFilterValue($event)
                    "
                />
                <Button
                    v-if="canCreateSuppliers"
                    class="ml-4"
                    variant="outline"
                    @click="showDialogCreate"
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
        <div v-else class="text-center py-4">
            You don't have permission to view suppliers.
        </div>
        <Dialog
            v-if="canCreateSuppliers || canEditSuppliers"
            v-model:open="showCreate"
            @update:open="
                (val) => {
                    if (!val) resetForm();
                    showCreate = val;
                }
            "
        >
            <Form>
                <DialogContent class="w-[1500px]">
                    <DialogHeader>
                        <DialogTitle
                            >{{ form.id ? "Edit" : "Create" }} Data Master
                            Supplier</DialogTitle
                        >
                        <DialogDescription>
                            Data master supplier
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <Label for="no_ktp"> Nomor KTP </Label>
                            <Input
                                id="no_ktp"
                                v-model="form.no_ktp"
                                class="mt-2"
                            />
                            <span
                                v-if="errors?.no_ktp"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.no_ktp }}</span
                            >
                        </div>
                        <div>
                            <Label for="npwp"> NPWP </Label>
                            <Input id="npwp" v-model="form.npwp" class="mt-2" />
                            <span
                                v-if="errors?.npwp"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.npwp }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_supplier"> Nama Supplier </Label>
                            <Input
                                id="nama_supplier"
                                v-model="form.nama_supplier"
                                class="mt-2"
                                required
                            />
                            <span
                                v-if="errors?.nama_supplier"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.nama_supplier }}</span
                            >
                        </div>

                        <div>
                            <Label for="alamat"> Alamat </Label>
                            <Input
                                id="alamat"
                                v-model="form.alamat"
                                class="mt-2"
                            />
                            <span
                                v-if="errors?.alamat"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.alamat }}</span
                            >
                        </div>
                        <div>
                            <Label for="tgl_lahir"> Tanggal Lahir </Label>
                            <Input
                                id="tgl_lahir"
                                type="date"
                                v-model="form.tgl_lahir"
                                class="mt-2"
                            />
                            <span
                                v-if="errors?.tgl_lahir"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.tgl_lahir }}</span
                            >
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="no_hp1"> Nomor HP (1) </Label>
                                <Input
                                    id="no_hp1"
                                    v-model="form.no_hp1"
                                    class="mt-2"
                                />
                                <span
                                    v-if="errors?.no_hp1"
                                    class="text-sm text-red-500 mt-2"
                                    >{{ errors.no_hp1 }}</span
                                >
                            </div>
                            <div>
                                <Label for="no_hp2"> Nomor HP (2) </Label>
                                <Input
                                    id="no_hp2"
                                    v-model="form.no_hp2"
                                    class="mt-2"
                                />
                                <span
                                    v-if="errors?.no_hp2"
                                    class="text-sm text-red-500 mt-2"
                                    >{{ errors.no_hp2 }}</span
                                >
                            </div>
                        </div>
                        <div>
                            <Label for="email"> Email </Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="mt-2"
                            />
                            <span
                                v-if="errors?.email"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.email }}</span
                            >
                        </div>
                        <div class="col-span-2">
                            <Label for="keterangan"> Keterangan </Label>
                            <Textarea
                                id="keterangan"
                                v-model="form.keterangan"
                                class="w-full shrink editable-input rounded-md border border-gray-900 p-2 mt-2 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <span
                                v-if="errors?.keterangan"
                                class="text-sm text-red-500 mt-2"
                                >{{ errors.keterangan }}</span
                            >
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_retur"
                                :checked="form.is_retur"
                                @update:checked="form.is_retur = $event"
                                required
                            />
                            <Label for="is_retur">Returable</Label>
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
