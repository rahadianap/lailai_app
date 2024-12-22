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
import { h, ref } from "vue";
import DropdownAction from "../../components/DataTableDropdown.vue";
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

const props = defineProps({
    data: Array,
});

const data = props.data.data;

const showCreate = ref(false);

const showDialogCreate = () => {
    showCreate.value = true;
};

const selectedCategory = ref(null);

const selectedProduct = ref(null);

const onCategorySelect = (category) => {
    selectedCategory.value = category;
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
        currentDetail.isi = productDetails.isi_barang;
        currentDetail.harga = productDetails.harga_beli_karton;

        // Set default values for other fields
        currentDetail.qty = 1;
        currentDetail.diskon = 0;
        currentDetail.diskon_global = 0;
        currentDetail.jumlah = productDetails.harga_beli_karton;
        currentDetail.exp_date = ""; // You might want to set a default date here
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
            const product = row.original;

            return h(
                "div",
                { class: "relative" },
                h(DropdownAction, {
                    product,
                    onEdit: () => onEdit(product.id),
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
    details: [
        {
            kode_barcode: "",
            harga_jual_karton: 0,
            harga_jual_eceran: 0,
            harga_beli_karton: 0,
            harga_beli_eceran: 0,
            hpp_avg_karton: 0,
            hpp_avg_eceran: 0,
            current_stock: 0,
            nilai_akhir: 0,
        },
    ],
});

const addNewDetail = () => {
    const newDetail = {
        saldo_awal: 0,
        harga_jual_karton: 0,
        harga_jual_eceran: 0,
        harga_beli_karton: 0,
        harga_beli_eceran: 0,
        hpp_avg_karton: 0,
        hpp_avg_eceran: 0,
        current_stock: 0,
        nilai_akhir: 0,
    };
    form.details.push(newDetail);
};

const newDetailInput = ref({
    saldo_awal: 0,
    harga_jual_karton: 0,
    harga_jual_eceran: 0,
    harga_beli_karton: 0,
    harga_beli_eceran: 0,
    hpp_avg_karton: 0,
    hpp_avg_eceran: 0,
    current_stock: 0,
    nilai_akhir: 0,
});

const addNewDetailFromInput = () => {
    // if (isItemExist(newDetailInput.value)) {
    //     Swal.fire({
    //         title: "Item Already Exists",
    //         text: "This item is already in the details table.",
    //         icon: "warning",
    //         showConfirmButton: false,
    //         timer: 1000,
    //     });
    // } else {
    //     form.details.push({ ...newDetailInput.value });
    //     // Reset the input after adding
    //     Object.keys(newDetailInput.value).forEach((key) => {
    //         newDetailInput.value[key] = 0;
    //     });
    // }
    form.details.push({ ...newDetailInput.value });
    //     // Reset the input after adding
    Object.keys(newDetailInput.value).forEach((key) => {
        newDetailInput.value[key] = 0;
    });
};

const isItemExist = (newItem) => {
    return form.details.some(
        (item) =>
            item.saldo_awal === newItem.saldo_awal &&
            item.harga_jual_karton === newItem.harga_jual_karton &&
            item.harga_jual_eceran === newItem.harga_jual_eceran &&
            item.harga_beli_karton === newItem.harga_beli_karton &&
            item.harga_beli_eceran === newItem.harga_beli_eceran &&
            item.hpp_avg_karton === newItem.hpp_avg_karton &&
            item.hpp_avg_eceran === newItem.hpp_avg_eceran &&
            item.current_stock === newItem.current_stock &&
            item.nilai_akhir === newItem.nilai_akhir,
    );
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
                kode_barcode: 0,
                harga_jual_karton: 0,
                harga_jual_eceran: 0,
                harga_beli_karton: 0,
                harga_beli_eceran: 0,
                hpp_avg_karton: 0,
                hpp_avg_eceran: 0,
                current_stock: 0,
                nilai_akhir: 0,
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
    //Open Dialog
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
        // Set to form
        form.id = data.data.id;
        form.kode_barcode = data.data.kode_barcode;
        form.kode_barcode = data.data.kode_barcode;
        form.nama_satuan = data.data.nama_satuan;
        form.nama_kategori = data.data.nama_kategori;
        form.isi_barang = data.data.isi_barang;
        form.is_taxable = data.data.is_taxable === "1" ? true : false;
        form.details.kode_barcode = data.data.details["kode_barcode"];
        form.details.harga_jual_karton = data.data.details["harga_jual_karton"];
        form.details.harga_jual_eceran = data.data.details["harga_jual_eceran"];
        form.details.harga_beli_karton = data.data.details["harga_beli_karton"];
        form.details.harga_beli_eceran = data.data.details["harga_beli_eceran"];
        form.details.hpp_avg_karton = data.data.details["hpp_avg_karton"];
        form.details.hpp_avg_eceran = data.data.details["hpp_avg_eceran"];
        form.details.current_stock = data.data.details["current_stock"];
        form.details.nilai_akhir = data.data.details["nilai_akhir"];
    } catch (error) {
        console.error(error);
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
                    class="max-w-full w-full max-h-full h-full flex flex-col p-4"
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
                    <div class="grid grid-cols-5 gap-4">
                        <div>
                            <Label for="nama_supplier"> Supplier </Label>
                            <SearchableSelect
                                class="mt-2"
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
                                @select="onProductSelect"
                            />
                            <span
                                v-if="errors?.nama_supplier"
                                class="text-sm text-red-500"
                                >{{ errors.nama_supplier }}</span
                            >
                        </div>
                    </div>
                    <DialogHeader class="mt-4">
                        <DialogTitle>Data Detail Pembelian</DialogTitle>
                        <DialogDescription>
                            Data detail pembelian
                        </DialogDescription>
                    </DialogHeader>
                    <div class="max-h-screen overflow-y-auto p-2 md:p-4">
                        <Table>
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
                                    <TableHead>Expired Date</TableHead>
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
                                            v-model="form.kode_barcode"
                                            placeholder="Search..."
                                            api-endpoint="http://127.0.0.1:8000/api/purchasing/products"
                                            value-field="kode_barcode"
                                            display-field="kode_barcode"
                                            search-param="search"
                                            :per-page="10"
                                            :debounce-time="300"
                                            loading-text="Loading products..."
                                            no-results-text="No products found"
                                            load-more-text="Load more products"
                                            @select="onProductSelect"
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
                                            type="number"
                                            class="col-span-3"
                                            required
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Input
                                            id="qty"
                                            v-model="detail.qty"
                                            type="number"
                                            class="col-span-3"
                                            required
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Input
                                            id="nama_satuan"
                                            v-model="detail.nama_satuan"
                                            type="number"
                                            class="col-span-3"
                                            required
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Input
                                            id="isi"
                                            v-model="detail.isi"
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
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Input
                                            id="exp_date"
                                            v-model="detail.exp_date"
                                            type="number"
                                            class="col-span-3"
                                            required
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
                    <Button @click="addNewDetailFromInput">Add</Button>
                    <DialogFooter>
                        <Button @click="submit"> Save </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>
