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
import SearchableSelect from "../../components/SearchableSelect.vue";
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
import { Trash2 } from "lucide-vue-next";
import { Textarea } from "@/components/ui/textarea";

const props = defineProps({
    data: Object,
    permissions: Object,
});

const canViewPO = computed(() => props.permissions.po_view);
const canCreatePO = computed(() => props.permissions.po_create);
const canEditPO = computed(() => props.permissions.po_edit);
const canDeletePO = computed(() => props.permissions.po_delete);

const data = props.data.data;

const showCreate = ref(false);

const showDialogCreate = () => {
    if (canCreatePO.value) {
        showCreate.value = true;
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to create products.",
            icon: "error",
        });
    }
};

const selectedSupplier = ref(null);

const selectedProduct = ref(null);

const onSupplierSelect = (supplier) => {
    console.log(supplier);
    selectedSupplier.value = supplier;
};

const onProductSelect = async (product) => {
    selectedProduct.value = product;
    try {
        const response = await fetch(
            `http://127.0.0.1:8000/api/purchase-order/products/${product.kode_barcode}`,
        );
        if (!response.ok) {
            throw new Error("Failed to fetch product details");
        }
        const productDetails = await response.json();

        // Update the current detail with the fetched product information
        const currentDetail = form.details[form.details.length - 1];
        currentDetail.nama_barang = productDetails.nama_barang;
        currentDetail.nama_satuan = productDetails.nama_satuan;
        currentDetail.isi_barang = productDetails.isi_barang;
        currentDetail.harga = productDetails.harga_beli_karton;

        // Set default values for other fields
        currentDetail.qty = 1;
        currentDetail.diskon = 0;
        currentDetail.diskon_global = 0;
        currentDetail.jumlah = productDetails.jumlah;
        calculateJumlah(currentDetail);
    } catch (error) {
        console.error("Error fetching product details:", error);
        // Optionally, show an error message to the user
        Swal.fire({
            title: "Error",
            text: "Failed to fetch product details",
            icon: "error",
            timer: 1000,
        });
    }
};

const columns = [
    {
        accessorKey: "kode_po",
        header: () =>
            h("div", { class: "text-left text-sm" }, "Kode Pembelian"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-normal" },
                row.getValue("kode_po"),
            );
        },
    },
    {
        accessorKey: "nama_supplier",
        header: () => h("div", { class: "text-left" }, "Supplier"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-normal" },
                row.getValue("nama_supplier"),
            );
        },
    },
    {
        accessorKey: "status",
        header: () => h("div", { class: "text-left" }, "Status"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-normal" },
                row.getValue("status"),
            );
        },
    },
    {
        accessorKey: "is_aktif",
        header: () => h("div", { class: "text-center" }, "Active"),
        cell: ({ row }) => {
            const status = row.getValue("is_aktif");
            if (status == true) {
                return h(
                    "div",
                    { class: "text-center font-normal" },
                    h(Badge, "Active"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-normal" },
                    h(Badge, { variant: "outline" }, "Inactive"),
                );
            }
        },
    },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row }) => {
            const po = row.original;

            return h(
                "div",
                { class: "relative text-right" },
                h(DropdownAction, {
                    po,
                    permissions: props.permissions,
                    onEdit: () => onEdit(po.id),
                    onDelete: () => onDelete(po.id),
                    onExpand: row.toggleExpanded,
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
            "/purchase-order",
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
    nama_supplier: "",
    keterangan: "",
    details: [
        {
            kode_barcode: "",
            nama_barang: "",
            qty: 0,
            nama_satuan: "",
            isi_barang: 0,
            harga: 0,
            diskon: 0,
            diskon_global: 0,
            jumlah: 0,
        },
    ],
});

const newDetailInput = ref({
    kode_barcode: "",
    nama_barang: "",
    qty: 0,
    nama_satuan: "",
    isi_barang: 0,
    harga: 0,
    diskon: 0,
    diskon_global: 0,
    jumlah: 0,
});

const addNewDetailFromInput = () => {
    if (isItemExist(newDetailInput.value)) {
        Swal.fire({
            title: "Item Already Exists",
            text: "This item is already in the details table.",
            icon: "warning",
            showConfirmButton: false,
            timer: 1000,
        });
    } else {
        form.details.push({ ...newDetailInput.value });
        calculateJumlah(newDetail);
        // Reset the input after adding
        Object.keys(newDetailInput.value).forEach((key) => {
            newDetailInput.value[key] = 0;
        });
    }
};

const isItemExist = (newItem) => {
    return form.details.some(
        (item) => item.kode_barcode === newItem.kode_barcode,
    );
};

const calculateJumlah = (detail) => {
    detail.jumlah = detail.qty * detail.harga;
};

const removeDetail = (index) => {
    form.details.splice(index, 1);
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    (form.nama_supplier = ""),
        (form.details = [
            {
                kode_barcode: "",
                nama_barang: "",
                qty: 0,
                nama_satuan: "",
                isi_barang: 0,
                harga: 0,
                diskon: 0,
                diskon_global: 0,
                jumlah: 0,
            },
        ]);
};

const submit = () => {
    const url = form.id ? `/purchase-order/${form.id}` : "/purchase-order";
    const method = form.id ? "put" : "post";
    form[method](url, {
        preserveState: true,
        onError: (error) => {
            errors.value = error;
            Swal.fire({
                title: "Oops!",
                text: "Something went wrong",
                icon: "error",
                timer: 1000,
            });
        },
        onSuccess: () => {
            showCreate.value = false;
            Swal.fire({
                title: "Yeay!",
                text: "Your work has been saved",
                icon: "success",
                showConfirmButton: false,
                timer: 1000,
            });
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        },
    });
};

const onEdit = async (id) => {
    if (canEditPO.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/purchase-order/${id}`, {
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
            form.keterangan = data.data.keterangan;
            // Initialize form.details as an empty array
            form.details = [];

            // Use forEach to populate details
            data.data.details.forEach((detail) => {
                form.details.push({
                    id: detail.id,
                    kode_barcode: detail.kode_barcode,
                    nama_barang: detail.nama_barang,
                    qty: detail.qty,
                    nama_satuan: detail.nama_satuan,
                    isi_barang: detail.isi,
                    harga: detail.harga,
                    jumlah: detail.jumlah,
                    harga_satuan_kecil: detail.harga_satuan_kecil,
                    hpp_avg_satuan: detail.hpp_avg_satuan,
                    hpp_avg_perbiji: detail.hpp_avg_perbiji,
                    nilai_dpp: detail.nilai_dpp,
                    nilai_ppn: detail.nilai_ppn,
                    harga_jual: detail.harga_jual,
                    diskon: detail.diskon,
                    diskon_global: detail.diskon_global,
                    rebate: detail.rebate,
                    is_taxable: detail.is_taxable,
                });
            });
        } catch (error) {
            console.error(error);
        }
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to edit products.",
            icon: "error",
        });
    }
};

const onDelete = (id) => {
    if (canDeletePO.value) {
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
                form.delete(`/purchase-order/${id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Your po has been deleted.",
                            "success",
                        );
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "There was a problem deleting the po.",
                            "error",
                        );
                    },
                });
            }
        });
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to delete products.",
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
            <h1 class="text-lg font-semibold md:text-2xl">Purchase Order</h1>
        </div>
        <div v-if="canViewPO" class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="table.getColumn('kode_po')?.getFilterValue()"
                    class="max-w-sm"
                    placeholder="Filter kode PO..."
                    @update:model-value="
                        table.getColumn('kode_po')?.setFilterValue($event)
                    "
                />
                <Button
                    v-if="canCreatePO"
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
                    <TableBody class="text-xs">
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
                                <TableRow v-if="row.getIsExpanded()">
                                    <TableCell
                                        :colspan="row.getAllCells().length"
                                    >
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead
                                                        >Nomor Faktur</TableHead
                                                    >
                                                    <TableHead
                                                        >Nama Barang</TableHead
                                                    >
                                                    <TableHead>Qty</TableHead>
                                                    <TableHead
                                                        >Satuan</TableHead
                                                    >
                                                    <TableHead>Isi</TableHead>
                                                    <TableHead>Harga</TableHead>
                                                    <TableHead
                                                        >Jumlah</TableHead
                                                    >
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow
                                                    v-for="detail in row
                                                        .original.details"
                                                    :key="detail.id"
                                                >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            detail.nomor_faktur
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            detail.nama_barang
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            detail.qty
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            detail.nama_satuan
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            detail.isi
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            formatPrice(
                                                                detail.harga,
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-normal"
                                                        >{{
                                                            formatPrice(
                                                                detail.jumlah,
                                                            )
                                                        }}</TableCell
                                                    >
                                                </TableRow>
                                            </TableBody>
                                        </Table>
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
                <div class="flex-1 text-sm text-muted-foreground">
                    {{ table.getFilteredSelectedRowModel().rows.length }}
                    of
                    {{ table.getFilteredRowModel().rows.length }} row(s)
                    selected.
                </div>
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-normal">Rows per page</p>
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
                    class="flex w-[100px] items-center justify-center text-sm font-normal"
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
                <DialogContent
                    class="max-w-full w-full max-h-full h-full flex flex-col p-4 dialog-content"
                >
                    <DialogHeader>
                        <DialogTitle
                            >{{ form.id ? "Edit" : "Create" }} Data
                            Pembelian</DialogTitle
                        >
                        <DialogDescription> Data master PO </DialogDescription>
                    </DialogHeader>
                    <div class="flex flex-row justify-start gap-4">
                        <div>
                            <Label for="nama_supplier"> Supplier </Label>
                            <SearchableSelect
                                class="mt-2"
                                required
                                v-model="form.nama_supplier"
                                placeholder="Search suppliers..."
                                api-endpoint="http://127.0.0.1:8000/api/purchase-order/suppliers"
                                value-field="nama_supplier"
                                display-field="nama_supplier"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading suppliers..."
                                no-results-text="No suppliers found"
                                load-more-text="Load more suppliers"
                                @select="onSupplierSelect"
                            />
                            <span
                                v-if="errors?.nama_supplier"
                                class="text-sm text-red-500"
                                >{{ errors.nama_supplier }}</span
                            >
                        </div>
                    </div>
                    <div>
                        <Label for="keterangan"> Keterangan </Label>
                        <Textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            class="shrink w-full mt-2"
                        />
                        <span
                            v-if="errors?.keterangan"
                            class="text-sm text-red-500"
                            >{{ errors.keterangan }}</span
                        >
                    </div>
                    <!-- </div> -->
                    <DialogHeader class="mt-4">
                        <DialogTitle>Data Detail Pembelian</DialogTitle>
                        <DialogDescription> Data detail PO </DialogDescription>
                    </DialogHeader>
                    <div class="flex-grow">
                        <div
                            class="h-full max-h-[calc(90vh-300px)] border rounded-md"
                        >
                            <Table class="table-auto">
                                <TableHeader class="sticky top-0 z-10 bg-white">
                                    <TableRow>
                                        <TableHead>Kode Barcode</TableHead>
                                        <TableHead>Nama Barang</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Satuan</TableHead>
                                        <TableHead>Isi</TableHead>
                                        <TableHead>Harga</TableHead>
                                        <TableHead>Diskon</TableHead>
                                        <TableHead>Diskon Global</TableHead>
                                        <TableHead>Jumlah</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="(detail, index) in form.details"
                                        :key="index"
                                    >
                                        <TableCell>
                                            <SearchableSelect
                                                required
                                                v-model="detail.kode_barcode"
                                                placeholder="Search..."
                                                api-endpoint="http://127.0.0.1:8000/api/purchase-order/products"
                                                value-field="kode_barcode"
                                                display-field="kode_barcode"
                                                search-param="search"
                                                :per-page="10"
                                                :debounce-time="300"
                                                loading-text="Loading products..."
                                                no-results-text="No products found"
                                                load-more-text="Load more products"
                                                @select="onProductSelect"
                                                :append-to-body="true"
                                            />
                                            <span
                                                v-if="errors?.kode_barcode"
                                                class="text-sm text-red-500"
                                                >{{ errors.kode_barcode }}</span
                                            >
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="nama_barang"
                                                v-model="detail.nama_barang"
                                                class="col-span-3"
                                                required
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="qty"
                                                v-model="detail.qty"
                                                type="number"
                                                class="col-span-3"
                                                required
                                                @input="calculateJumlah(detail)"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="nama_satuan"
                                                v-model="detail.nama_satuan"
                                                class="col-span-3"
                                                required
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="isi_barang"
                                                v-model="detail.isi_barang"
                                                type="number"
                                                class="col-span-3"
                                                required
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="harga"
                                                v-model="detail.harga"
                                                type="number"
                                                class="col-span-3"
                                                required
                                                @input="calculateJumlah(detail)"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="diskon"
                                                v-model="detail.diskon"
                                                type="number"
                                                class="col-span-3"
                                                required
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="diskon_global"
                                                v-model="detail.diskon_global"
                                                type="number"
                                                class="col-span-3"
                                                required
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="jumlah"
                                                v-model="detail.jumlah"
                                                type="number"
                                                class="col-span-3"
                                                required
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Button
                                                @click="removeDetail(index)"
                                                variant="destructive"
                                                ><Trash2
                                            /></Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                    <Button @click="addNewDetailFromInput">Add</Button>
                    <DialogFooter>
                        <Button @click="submit"> Save </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>

<style scoped>
.dialog-content {
    display: block;
    flex-direction: column;
    max-height: 90vh;
}

.dialog-content table thead {
    position: sticky;
    top: 0;
    background-color: white;
    z-index: 10;
}

/* Add these new styles */
:deep(.v-popper__popper) {
    z-index: 9999;
}

:deep(.v-popper__popper .v-popper__wrapper) {
    max-height: none;
}

.sticky {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
