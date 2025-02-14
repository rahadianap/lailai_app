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

const canViewMutasiKeluar = computed(
    () => props.permissions.mutasi_keluar_view,
);
const canCreateMutasiKeluar = computed(
    () => props.permissions.mutasi_keluar_create,
);
const canEditMutasiKeluar = computed(
    () => props.permissions.mutasi_keluar_edit,
);
const canDeleteMutasiKeluar = computed(
    () => props.permissions.mutasi_keluar_delete,
);
const canApproveMutasiKeluar = computed(
    () => props.permissions.mutasi_keluar_approve,
);

const data = props.data.data;

const showCreate = ref(false);

const showDialogCreate = () => {
    if (canCreateMutasiKeluar.value) {
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
const selectedTujuan = ref(null);

const onTujuanSelect = (tujuan) => {
    selectedTujuan.value = tujuan.tujuan_gudang;
};

const onApprove = (id) => {
    if (canApproveMutasiKeluar.value) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, approve it!",
        }).then((result) => {
            if (result.isConfirmed) {
                const form = useForm({});
                form.put(`/mutasi-keluar/approve/${id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            "Approved!",
                            "Your po has been approved.",
                            "success",
                        );
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "There was a problem approving the po.",
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

const onProductSelect = async (product) => {
    // selectedProduct.value = product;
    try {
        const response = await fetch(
            `http://127.0.0.1:8000/api/mutasi-keluar/products/${product.kode_barcode}`,
        );
        if (!response.ok) {
            throw new Error("Failed to fetch product details");
        }
        const productDetails = await response.json();

        // Update the current detail with the fetched product information
        const currentDetail = form.details[form.details.length - 1];
        currentDetail.nama_barang = productDetails.nama_barang;
        currentDetail.qty = 0;
        currentDetail.nama_satuan = "Pcs";
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
        accessorKey: "kode_mutasi_keluar",
        header: () => h("div", { class: "text-left" }, "Kode Mutasi Keluar"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_mutasi_keluar"),
            );
        },
    },
    {
        accessorKey: "tujuan_gudang",
        header: () => h("div", { class: "text-left" }, "Tujuan"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("tujuan_gudang"),
            );
        },
    },
    {
        accessorKey: "keterangan",
        header: () => h("div", { class: "text-left" }, "Keterangan"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("keterangan"),
            );
        },
    },
    {
        accessorKey: "status",
        header: () => h("div", { class: "text-center" }, "Status"),
        cell: ({ row }) => {
            const status = row.getValue("status");
            if (status === "APPROVED") {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "APPROVED"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "CREATED"),
                );
            }
        },
    },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row }) => {
            const mutasikeluar = row.original;

            return h(
                "div",
                { class: "relative text-right" },
                h(DropdownAction, {
                    mutasikeluar,
                    permissions: props.permissions,
                    onEdit: () => onEdit(mutasikeluar.id),
                    onDelete: () => onDelete(mutasikeluar.id),
                    onApprove: () => onApprove(mutasikeluar.id),
                    onPrint: () => onPrint(mutasikeluar.id),
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
            "/mutasi-keluar",
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
    tujuan_gudang: "",
    keterangan: "",
    details: [
        {
            kode_barcode: "",
            nama_barang: "",
            qty: 0,
            nama_satuan: "Pcs",
        },
    ],
});

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
    (form.tujuan_gudang = ""),
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
    // Validate all required fields
    form.details.forEach((detail, index) => {
        if (!detail.qty || detail.qty < 0) {
            validationErrors.value[index] =
                "Qty is required and cannot be less than 0.";
        }
    });

    // Check if there are any validation errors
    if (Object.values(validationErrors.value).some((error) => error !== "")) {
        Swal.fire({
            title: "Validation Error",
            text: "Please fix the validation errors before submitting.",
            icon: "error",
        });
        return;
    }

    const url = form.id ? `/mutasi-keluar/${form.id}` : "/mutasi-keluar";
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
    if (canEditMutasiKeluar.value) {
        showCreate.value = true;
        try {
            const res = await fetch(`/mutasi-keluar/${id}`, {
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
            form.tujuan_gudang = data.data.tujuan_gudang;
            form.keterangan = data.data.keterangan;
            form.kode_pembelian = data.data.kode_pembelian || null;
            form.details = [];

            // Use forEach to populate details
            data.data.details.forEach((detail) => {
                form.details.push({
                    id: detail.id,
                    kode_barcode: detail.kode_barcode,
                    nama_barang: detail.nama_barang,
                    qty: detail.qty,
                    nama_satuan: detail.nama_satuan,
                    qty_retur: detail.qty_retur,
                    nama_satuan_retur: detail.nama_satuan_retur,
                    harga: detail.harga,
                    jumlah: detail.jumlah,
                });
            });
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
    if (canDeleteMutasiKeluar.value) {
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
                form.delete(`/mutasi-keluar/${id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Your data has been deleted.",
                            "success",
                        );
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "There was a problem deleting the data.",
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

const onPrint = async (id) => {
    axios({
        url: `http://127.0.0.1:8000/api/mutasi-keluar/print/${id}`,
        method: "GET",
        headers: {
            "Content-Type": "multipart/form-data",
        },
        responseType: "blob",
    }).then((response) => {
        var fileURL = window.open(
            URL.createObjectURL(
                new Blob([response.data], { type: "application/pdf" }),
            ),
            "name",
            "width=500,height=500,resizable=yes,scrollbars=yes",
        );
        if (window.focus) {
            fileURL.focus();
            var fileLink = document.createElement("a");
            fileLink.href = fileURL;
            fileLink.setAttribute("stream", "ReturBeli.pdf");
            document.body.appendChild(fileLink);
            return false;
        }
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(price);
};

const newDetailInput = ref({
    kode_barcode: "",
    nama_barang: "",
    qty: 0,
    nama_satuan: 0,
});

const isItemExist = (newItem) => {
    return form.details.some(
        (item) => item.kode_barcode === newItem.kode_barcode,
    );
};

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

const validationErrors = ref({});

const validateQty = (detail, index) => {
    if (detail.qty < 0) {
        validationErrors.value[index] = "Qty cannot be less than 0.";
    } else {
        validationErrors.value[index] = "";
    }
};
</script>

<template>
    <Layout>
        <div class="flex items-center">
            <h1 class="text-lg font-semibold md:text-2xl">Mutasi Keluar</h1>
        </div>
        <div v-if="canViewMutasiKeluar" class="w-full">
            <div class="flex items-center justify-between py-4">
                <Input
                    :model-value="
                        table.getColumn('kode_mutasi_keluar')?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter kode mutasi..."
                    @update:model-value="
                        table
                            .getColumn('kode_mutasi_keluar')
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
                                                        >Kode Barcode</TableHead
                                                    >
                                                    <TableHead
                                                        >Nama Barang</TableHead
                                                    >
                                                    <TableHead>Qty</TableHead>
                                                    <TableHead
                                                        >Satuan</TableHead
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
                                                        class="font-medium"
                                                        >{{
                                                            detail.kode_barcode
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            detail.nama_barang
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            detail.qty
                                                        }}</TableCell
                                                    >
                                                    <TableCell
                                                        class="font-medium"
                                                        >{{
                                                            detail.nama_satuan
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
                            >{{ form.id ? "Edit" : "Create" }} Data Mutasi
                            Keluar</DialogTitle
                        >
                        <DialogDescription>
                            Data master mutasi keluar
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex flex-row justify-start gap-4">
                        <div>
                            <Label for="tujuan_gudang"> Gudang Tujuan </Label>
                            <SearchableSelect
                                class="mt-2 editable-input"
                                v-model="form.tujuan_gudang"
                                placeholder="Search tujuan..."
                                api-endpoint="http://127.0.0.1:8000/api/mutasi-keluar/tujuan"
                                value-field="nama_toko"
                                :display-fields="['nama_toko']"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading tujuan..."
                                no-results-text="No tujuan found"
                                load-more-text="Load more tujuan"
                                @select="onTujuanSelect"
                            />
                            <p
                                v-if="form.errors.tujuan_gudang"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.tujuan_gudang }}
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
                        <DialogTitle>Data Detail Mutasi Keluar</DialogTitle>
                        <DialogDescription>
                            Data detail mutasi keluar
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex-grow">
                        <div
                            class="h-full max-h-[calc(90vh-300px)] overflow-y-auto border rounded-md relative"
                        >
                            <Table>
                                <TableHeader class="sticky top-0 z-10 bg-white">
                                    <TableRow>
                                        <TableHead>Kode Barcode</TableHead>
                                        <TableHead>Nama Barang</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Satuan</TableHead>
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
                                                api-endpoint="http://127.0.0.1:8000/api/mutasi-keluar/products"
                                                value-field="kode_barcode"
                                                :display-fields="[
                                                    'kode_barcode',
                                                    'nama_barang',
                                                ]"
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
                                                id="qty"
                                                v-model="detail.qty"
                                                type="number"
                                                class="col-span-3 editable-input"
                                                required
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
                                            <p
                                                v-if="validationErrors[index]"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ validationErrors[index] }}
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
