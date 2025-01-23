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
import { h, ref, watch, computed } from "vue";
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
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
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

const canViewPurchasing = computed(() => props.permissions.purchasing_view);
const canCreatePurchasing = computed(() => props.permissions.purchasing_create);
const canEditPurchasing = computed(() => props.permissions.purchasing_edit);
const canDeletePurchasing = computed(() => props.permissions.purchasing_delete);

const data = props.data.data;

const showCreate = ref(false);

const showDialogCreate = () => {
    if (canCreatePurchasing.value) {
        showCreate.value = true;
    } else {
        Swal.fire({
            title: "Permission Denied",
            text: "You don't have permission to create products.",
            icon: "error",
        });
    }
};

const selectedProduct = ref(null);
const selectedSupplier = ref(null);

const onSupplierSelect = (supplier) => {
    selectedSupplier.value = supplier.nama_supplier;
};

const onPOSelect = async (po) => {
    try {
        const response = await fetch(
            `http://127.0.0.1:8000/api/purchasing/po/${po.id}`,
        );
        if (!response.ok) {
            throw new Error("Failed to fetch PO details");
        }
        const poDetails = await response.json();
        console.log(poDetails);

        // Update form with PO details
        form.nama_supplier = poDetails[0].nama_supplier;
        form.keterangan = poDetails[0].keterangan;
        form.details = poDetails.map((detail) => ({
            ...detail,
            isi_barang: detail.isi,
            harga: 0,
            diskon: 0,
            diskon_global: 0,
            dpp: 0,
            ppn: 0,
            harga_jual: 0,
            is_taxable: detail.is_taxable === "1" ? true : false,
            jumlah:
                detail.qty * detail.harga -
                (detail.diskon + detail.diskon_global),
        }));

        calculateTotals();
    } catch (error) {
        console.error("Error fetching PO details:", error);
        Swal.fire({
            title: "Error",
            text: "Failed to fetch PO details",
            icon: "error",
        });
    }
};

const onProductSelect = async (product) => {
    selectedProduct.value = product;
    try {
        const response = await fetch(
            `http://127.0.0.1:8000/api/purchasing/products/${product.kode_barcode}`,
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
        currentDetail.jumlah = productDetails.harga_beli_karton;
        currentDetail.is_taxable = productDetails.is_taxable === "1" ? true : false;
        currentDetail.exp_date = ""; // You might want to set a default date here
        calculateJumlah(currentDetail);
    } catch (error) {
        console.error("Error fetching product details:", error);
        // Optionally, show an error message to the user
        Swal.fire({
            title: "Error",
            text: "Failed to fetch product details",
            icon: "error",
        });
    }
};

const columns = [
    {
        accessorKey: "kode_pembelian",
        header: () => h("div", { class: "text-left" }, "Kode Pembelian"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_pembelian"),
            );
        },
    },
    {
        accessorKey: "nama_supplier",
        header: () => h("div", { class: "text-left" }, "Supplier"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_supplier"),
            );
        },
    },
    {
        accessorKey: "kode_po",
        header: () => h("div", { class: "text-left" }, "Kode PO"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_po"),
            );
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
            const purchasing = row.original;

            return h(
                "div",
                { class: "relative text-right" },
                h(DropdownAction, {
                    purchasing,
                    permissions: props.permissions,
                    onEdit: () => onEdit(purchasing.id),
                    onDelete: () => onDelete(purchasing.id),
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
            "/purchasing",
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
    kode_po: "",
    keterangan: "",
    purchase_type: "ppn",
    rebate: 0,
    diskon_total: 0,
    subtotal: 0,
    dpp_total: 0,
    ppn_total: 0,
    total: 0,
    grand_total: 0,
    details: [
        {
            kode_barcode: "",
            nama_barang: "",
            exp_date: "",
            qty: 0,
            nama_satuan: 0,
            isi_barang: 0,
            harga: 0,
            diskon: 0,
            diskon_global: 0,
            jumlah: 0,
            dpp: 0,
            ppn: 0,
            harga_jual: 0,
            taxable: false,
        },
    ],
});

const newDetailInput = ref({
    kode_barcode: "",
    nama_barang: "",
    exp_date: "",
    qty: 0,
    nama_satuan: 0,
    isi_barang: 0,
    harga: 0,
    diskon: 0,
    diskon_global: 0,
    jumlah: 0,
    dpp: 0,
    ppn: 0,
    harga_jual: 0,
    taxable: false,
});

const addNewDetailFromInput = () => {
    if (isItemExist(newDetailInput.value)) {
        Swal.fire({
            title: "Item Already Exists",
            text: "This item is already in the details table.",
            icon: "warning",
            showConfirmButton: false,
            timer: 2000,
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

const totalDiskon = computed(() => {
    return form.details.reduce(
        (sum, detail) => sum + (detail.diskon_global || 0),
        0,
    );
});

const totalSub = computed(() => {
    return form.details.reduce((sum, detail) => sum + (detail.jumlah || 0), 0);
});

const calculateJumlah = (detail) => {
    detail.jumlah = detail.qty * detail.harga - detail.diskon;
    form.subtotal = totalSub;
};

const setDiskonGlobal = (detail) => {
    detail.jumlah = detail.qty * detail.harga - detail.diskon;
    form.subtotal = totalSub;
    form.diskon_total = totalDiskon;
    form.dpp_total = form.subtotal - form.diskon_total;
    form.total = form.subtotal - form.diskon_total;
    form.ppn_total = form.total * 0.11;
    form.grand_total = form.dpp_total + form.ppn_total;
};

const calculateTotals = () => {
    // Calculate subtotal
    // form.subtotal = form.details.reduce(
    //     (sum, detail) => sum + (detail.jumlah || 0),
    //     0,
    // );
    // // Calculate discount in Rupiah
    // const discountPercentage = form.diskon_total || 0;
    // form.diskon_rupiah = (form.subtotal * discountPercentage) / 100;
    // // Calculate DPP (Dasar Pengenaan Pajak)
    // form.dpp_total = form.subtotal - form.diskon_rupiah - (form.rebate || 0);
    // // Calculate PPN (Pajak Pertambahan Nilai)
    // form.ppn_total = form.details.reduce((sum, detail) => {
    //     if (detail.is_taxable === "1" || detail.is_taxable === true) {
    //         return sum + form.dpp_total * 0.11;
    //     }
    //     return sum;
    // }, 0);
    // // Calculate total
    // form.total = form.dpp_total + form.ppn_total;
    // // Ensure all values are rounded to 2 decimal places
    // form.subtotal = parseFloat(form.subtotal.toFixed(2));
    // form.diskon_rupiah = parseFloat(form.diskon_rupiah.toFixed(2));
    // form.dpp_total = parseFloat(form.dpp_total.toFixed(2));
    // form.ppn_total = parseFloat(form.ppn_total.toFixed(2))
    // form.total = parseFloat(form.total.toFixed(2));
};

watch(
    [() => form.details, () => form.diskon_total, () => form.rebate],
    calculateTotals,
    { deep: true },
);

// Add watch effect to recalculate totals when details change
watch(() => form.details, calculateTotals, { deep: true });

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
                kode_barcode: 0,
                harga_jual_karton: 0,
                harga_jual_eceran: 0,
                harga_beli_karton: 0,
                harga_beli_eceran: 0,
                hpp_avg_karton: 0,
                hpp_avg_eceran: 0,
                current_stock: 0,
            },
        ]);
};

const submit = () => {
    const url = form.id ? `/purchasing/${form.id}` : "/purchasing";
    const method = form.id ? "put" : "post";
    form[method](url, {
        preserveState: true,
        onError: (error) => {
            errors.value = error;
            Swal.fire({
                title: "Oops!",
                text: errors.value["error"],
                icon: "error",
                timer: 2000,
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
    if (canEditPurchasing.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/purchasing/${id}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!res.ok) {
                console.error("Error ");
            }
            const data = await res.json();
            console.log(data.data);
            // Set to form
            form.id = data.data.id;
            form.nama_supplier = data.data.nama_supplier;
            form.keterangan = data.data.keterangan;
            form.kode_po = data.data.kode_po || null;
            form.purchase_type = data.data.purchase_type;
            form.rebate = data.data.rebate;
            form.diskon_total = data.data.diskon_total;
            form.subtotal = data.data.subtotal;
            form.dpp_total = data.data.dpp_total;
            form.ppn_total = data.data.ppn_total;
            form.total = data.data.total;
            form.grand_total = data.data.grand_total;
            form.details = [];

            // Use forEach to populate details
            data.data.details.forEach((detail) => {
                form.details.push({
                    id: detail.id,
                    kode_barcode: detail.kode_barcode,
                    nama_barang: detail.nama_barang,
                    exp_date: detail.exp_date,
                    qty: detail.qty,
                    nama_satuan: detail.nama_satuan,
                    isi_barang: detail.isi,
                    harga: detail.harga,
                    jumlah: detail.jumlah,
                    harga_satuan_kecil: detail.harga_satuan_kecil,
                    hpp_avg_satuan: detail.hpp_avg_satuan,
                    hpp_avg_perbiji: detail.hpp_avg_perbiji,
                    dpp: detail.nilai_dpp,
                    ppn: detail.nilai_ppn,
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
    if (canDeletePurchasing.value) {
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
                form.delete(`/purchasing/${id}`, {
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
            <h1 class="text-lg font-semibold md:text-2xl">Purchasing</h1>
        </div>
        <div class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="
                        table.getColumn('kode_pembelian')?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter kode pembelian..."
                    @update:model-value="
                        table
                            .getColumn('kode_pembelian')
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
                                <TableRow v-if="row.getIsExpanded()">
                                    <TableCell
                                        :colspan="row.getAllCells().length"
                                    >
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead
                                                        >Saldo Awal</TableHead
                                                    >
                                                    <TableHead
                                                        >Harga Jual
                                                        (Karton)</TableHead
                                                    >
                                                    <TableHead
                                                        >Harga Jual
                                                        (Eceran)</TableHead
                                                    >
                                                    <TableHead
                                                        >Harga Beli
                                                        (Karton)</TableHead
                                                    >
                                                    <TableHead
                                                        >Harga Beli
                                                        (Eceran)</TableHead
                                                    >
                                                    <TableHead
                                                        >HPP (Karton)</TableHead
                                                    >
                                                    <TableHead
                                                        >HPP (Eceran)</TableHead
                                                    >
                                                    <TableHead
                                                        >Current
                                                        Stock</TableHead
                                                    >
                                                    <TableHead
                                                        >Nilai Akhir</TableHead
                                                    >
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow>
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            row.original
                                                                .details[
                                                                "kode_barcode"
                                                            ]
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "harga_jual_karton"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "harga_jual_eceran"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "harga_beli_karton"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "harga_beli_eceran"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "hpp_avg_karton"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "hpp_avg_eceran"
                                                                ],
                                                            )
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            row.original
                                                                .details[
                                                                "current_stock"
                                                            ]
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            formatPrice(
                                                                row.original
                                                                    .details[
                                                                    "nilai_akhir"
                                                                ],
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
                    class="flex w-[100px] items-center justify-center text-sm font-medium"
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
                    class="flex flex-col w-full h-full max-w-full max-h-full p-4 dialog-content"
                >
                    <DialogHeader>
                        <DialogTitle
                            >{{ form.id ? "Edit" : "Create" }} Data
                            Pembelian</DialogTitle
                        >
                        <DialogDescription>
                            Data master pembelian
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex flex-row justify-start gap-4">
                        <div>
                            <Label for="nama_supplier"> Supplier </Label>
                            <SearchableSelect
                                class="mt-2 editable-input"
                                required
                                v-model="form.nama_supplier"
                                placeholder="Search suppliers..."
                                api-endpoint="http://127.0.0.1:8000/api/purchasing/suppliers"
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
                            <p
                                v-if="form.errors.nama_supplier"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.nama_supplier }}
                            </p>
                        </div>
                        <div>
                            <Label for="kode_po"> Nomor PO </Label>
                            <SearchableSelect
                                class="mt-2 editable-input"
                                v-model="form.kode_po"
                                placeholder="Search PO..."
                                api-endpoint="http://127.0.0.1:8000/api/purchasing/po"
                                value-field="kode_po"
                                :display-fields="['kode_po']"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading PO..."
                                no-results-text="No PO found"
                                load-more-text="Load more PO"
                                @select="onPOSelect"
                            />
                            <p
                                v-if="form.errors.kode_po"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.kode_po }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <Label for="keterangan"> Keterangan </Label>
                        <Textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            class="w-full mt-2 shrink editable-input"
                            required
                        />
                        <p
                            v-if="form.errors.keterangan"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.keterangan }}
                        </p>
                    </div>
                    <!-- </div> -->
                    <DialogHeader class="mt-4">
                        <DialogTitle>Data Detail Pembelian</DialogTitle>
                        <DialogDescription>
                            Data detail pembelian
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex flex-row justify-end gap-4">
                        <RadioGroup
                            v-model="form.purchase_type"
                            class="flex mt-2 space-x-4"
                        >
                            <div class="flex items-center space-x-2">
                                <RadioGroupItem id="ppn" value="ppn" />
                                <Label for="ppn" class="text-xs">PPN</Label>
                            </div>
                            <div class="flex items-center space-x-2">
                                <RadioGroupItem id="inc_ppn" value="inc_ppn" />
                                <Label for="inc_ppn" class="text-xs"
                                    >Inc.PPN</Label
                                >
                            </div>
                            <div class="flex items-center space-x-2">
                                <RadioGroupItem id="no_ppn" value="type3" />
                                <Label for="no_ppn" class="text-xs"
                                    >No PPn</Label
                                >
                            </div>
                        </RadioGroup>
                    </div>
                    <div class="flex-grow">
                        <div
                            class="h-full max-h-[calc(90vh-300px)] overflow-y-auto border rounded-md relative"
                        >
                            <Table>
                                <TableHeader class="sticky top-0 z-10 bg-white">
                                    <TableRow>
                                        <TableHead>Kode Barcode</TableHead>
                                        <TableHead>Nama Barang</TableHead>
                                        <TableHead>Expired Date</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Satuan</TableHead>
                                        <TableHead>Isi</TableHead>
                                        <TableHead>Harga Beli</TableHead>
                                        <TableHead>Diskon</TableHead>
                                        <TableHead>Diskon Global</TableHead>
                                        <TableHead>Jumlah</TableHead>
                                        <TableHead>DPP</TableHead>
                                        <TableHead>PPN</TableHead>
                                        <TableHead>Harga Jual</TableHead>
                                        <TableHead>Taxable</TableHead>
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
                                                class="editable-input"
                                                required
                                                v-model="detail.kode_barcode"
                                                placeholder="Search..."
                                                api-endpoint="http://127.0.0.1:8000/api/purchasing/products"
                                                value-field="kode_barcode"
                                                :display-fields="['kode_barcode']"
                                                search-param="search"
                                                :per-page="10"
                                                :debounce-time="300"
                                                loading-text="Loading products..."
                                                no-results-text="No products found"
                                                load-more-text="Load more products"
                                                @select="onProductSelect"
                                                :append-to-body="true"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.kode_barcode`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.kode_barcode`
                                                    ]
                                                }}
                                            </p>
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
                                                id="exp_date"
                                                v-model="detail.exp_date"
                                                type="date"
                                                class="col-span-3 editable-input"
                                                required
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.exp_date`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.exp_date`
                                                    ]
                                                }}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="qty"
                                                v-model="detail.qty"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                required
                                                @input="setDiskonGlobal(detail)"
                                                min="0"
                                                step="0"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.qty`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.qty`
                                                    ]
                                                }}
                                            </p>
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
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="harga"
                                                v-model="detail.harga"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                required
                                                @input="setDiskonGlobal(detail)"
                                                min="0"
                                                step="0"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.harga`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.harga`
                                                    ]
                                                }}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="diskon"
                                                v-model="detail.diskon"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                @input="calculateJumlah(detail)"
                                                required
                                                min="0"
                                                step="0"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.diskon`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.diskon`
                                                    ]
                                                }}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="diskon_global"
                                                v-model="detail.diskon_global"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                @input="setDiskonGlobal(detail)"
                                                required
                                                min="0"
                                                step="0"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.diskon_global`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.diskon_global`
                                                    ]
                                                }}
                                            </p>
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
                                            <Input
                                                id="dpp"
                                                v-model="detail.dpp"
                                                type="number"
                                                class="col-span-3"
                                                required
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="ppn"
                                                v-model="detail.ppn"
                                                type="number"
                                                class="col-span-3"
                                                required
                                                readonly
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                id="harga_jual"
                                                v-model="detail.harga_jual"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                required
                                                min="0"
                                                step="0"
                                            />
                                            <p
                                                v-if="
                                                    form.errors[
                                                        `details.${index}.harga_jual`
                                                    ]
                                                "
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{
                                                    form.errors[
                                                        `details.${index}.harga_jual`
                                                    ]
                                                }}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <TableCell>
                                                <Checkbox
                                                    disabled
                                                    :checked="detail.is_taxable"
                                                    @update:checked="form.is_taxable = $event"
                                                />
                                            </TableCell>
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
                    <div class="p-4 mt-4 border rounded-lg bg-gray-50">
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Left Column -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >Rebate</span
                                    >
                                    <div class="flex items-center gap-2">
                                        <Input
                                            v-model="form.rebate"
                                            type="number"
                                            class="w-32 text-right"
                                            @input="calculateTotals"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >Diskon Rp</span
                                    >
                                    <div class="flex items-center gap-2">
                                        <Input
                                            v-model="form.diskon_total"
                                            type="number"
                                            class="w-32 text-right"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >Subtotal</span
                                    >
                                    <div class="flex items-center gap-2">
                                        <Input
                                            v-model="form.subtotal"
                                            type="number"
                                            class="w-32 text-right"
                                            readonly
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Column -->
                            <div class="col-span-1">
                                <!-- Placeholder for middle column if needed -->
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">DPP</span>
                                    <Input
                                        v-model="form.dpp_total"
                                        type="number"
                                        class="w-32 text-right"
                                        readonly
                                    />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >PPN 11%</span
                                    >
                                    <Input
                                        v-model="form.ppn_total"
                                        type="number"
                                        class="w-32 text-right"
                                        readonly
                                    />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >Total</span
                                    >
                                    <Input
                                        v-model="form.total"
                                        type="number"
                                        class="w-32 font-bold text-right"
                                        readonly
                                    />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium"
                                        >Grand Total</span
                                    >
                                    <Input
                                        v-model="form.grand_total"
                                        type="number"
                                        class="w-32 font-bold text-right"
                                        readonly
                                    />
                                </div>
                            </div>
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

<style scoped>
.dialog-content {
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.dialog-content > * {
    flex-shrink: 0;
}

.dialog-content > .flex-grow {
    flex-shrink: 1;
}

/* Add these new styles */
:deep(.v-popper__popper) {
    z-index: 100;
}

:deep(.v-popper__popper .v-popper__wrapper) {
    max-height: 200px;
    overflow-y: auto;
}

/* Make readonly inputs look different */
.summary-card {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}

.editable-input {
    background-color: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 5px;
    border-color: blue;
    transition: border-color 0.2s ease-in-out;
}

.editable-input:hover {
    border-color: #9ca3af;
}

.editable-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    outline: none;
}

.readonly-input {
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    cursor: not-allowed;
}

.disabled-checkbox {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
