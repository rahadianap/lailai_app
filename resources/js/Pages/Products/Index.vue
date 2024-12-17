<script setup>
import Layout from "../../Layout/App.vue";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
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
import { h, ref } from "vue";
import DropdownAction from "../Products/DataTableDemoColumn.vue";
import { Badge } from "@/components/ui/badge";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import {
    Form,
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/components/ui/form";
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

const props = defineProps({
    data: Array,
});

const data = props.data.data;

const showDialog = ref(false);

const showDialogCreate = () => {
    showDialog.value = true;
};

const selectedCategoryId = ref(null);
const selectedCategory = ref(null);

const selectedUnitId = ref(null);
const selectedUnit = ref(null);

const onCategorySelect = (category) => {
    selectedCategory.value = category;
    console.log("Selected category:", category.nama_kategori);
};

const onUnitSelect = (unit) => {
    selectedUnit.value = unit;
    console.log("Selected unit:", unit.nama_kategori);
};

const columns = [
    {
        id: "select",
        header: ({ table }) =>
            h(Checkbox, {
                checked:
                    table.getIsAllPageRowsSelected() ||
                    (table.getIsSomePageRowsSelected() && "indeterminate"),
                "onUpdate:checked": (value) =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: "Select all",
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                checked: row.getIsSelected(),
                "onUpdate:checked": (value) => row.toggleSelected(!!value),
                ariaLabel: "Select row",
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: "kode_barang",
        header: () => h("div", { class: "text-left" }, "Kode Barang"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_barang"),
            );
        },
    },
    {
        accessorKey: "kode_barcode",
        header: () => h("div", { class: "text-left" }, "Kode Barcode"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("kode_barcode"),
            );
        },
    },
    {
        accessorKey: "nama_barang",
        header: () => h("div", { class: "text-left" }, "Nama Barang"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_barang"),
            );
        },
    },
    {
        accessorKey: "nama_satuan",
        header: () => h("div", { class: "text-left" }, "Satuan"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_satuan"),
            );
        },
    },
    {
        accessorKey: "nama_kategori",
        header: () => h("div", { class: "text-left" }, "Kategori"),
        cell: ({ row }) => {
            return h(
                "div",
                { class: "text-left font-medium" },
                row.getValue("nama_kategori"),
            );
        },
    },
    {
        accessorKey: "isi_barang",
        header: () => h("div", { class: "text-center" }, "Isi Barang"),
        cell: ({ row }) => {
            const amount = Number.parseInt(row.getValue("isi_barang"));

            return h("div", { class: "text-center font-medium" }, amount);
        },
    },
    {
        accessorKey: "is_taxable",
        header: () => h("div", { class: "text-center" }, "BKP"),
        cell: ({ row }) => {
            const taxable = row.getValue("is_taxable");
            if (taxable == true) {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, "BKP"),
                );
            } else {
                return h(
                    "div",
                    { class: "text-center font-medium" },
                    h(Badge, { variant: "outline" }, "Non-BKP"),
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
            const payment = row.original;

            return h(DropdownAction, {
                payment,
                onExpand: row.toggleExpanded,
            });
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
    manualSorting: true,
    manualFiltering: true,
    onPaginationChange: (updater) => {
        if (typeof updater === "function") {
            pagination.value = updater(pagination.value);
        } else {
            pagination.value = updater;
        }
        router.get(
            "/products",
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
    kode_barcode: "",
    nama_barang: "",
    nama_satuan: "",
    nama_kategori: "",
    isi_barang: 0,
    is_taxable: 0,
    details: {
        saldo_awal: 0,
        harga_jual_karton: 0,
        harga_jual_eceran: 0,
        harga_beli_karton: 0,
        harga_beli_eceran: 0,
        hpp_avg_karton: 0,
        hpp_avg_eceran: 0,
        current_stock: 0,
        nilai_akhir: 0,
    },
});

const submit = () => {
    form.nama_satuan = selectedUnitId;
    form.nama_kategori = selectedCategoryId;
    form.post("/products", {
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
            showDialog.value = false;
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
</script>

<template>
    <Layout>
        <div class="flex items-center">
            <h1 class="text-lg font-semibold md:text-2xl">Products</h1>
        </div>
        <div class="w-full">
            <div class="flex items-center py-4">
                <Input
                    :model-value="
                        table.getColumn('kode_barcode')?.getFilterValue()
                    "
                    class="max-w-sm"
                    placeholder="Filter kode barcode..."
                    @update:model-value="
                        table.getColumn('kode_barcode')?.setFilterValue($event)
                    "
                />
                <Button class="ml-4" variant="outline" @click="showDialogCreate"
                    ><PlusCircledIcon class="h-5 w-5"></PlusCircledIcon>Create
                    New</Button
                >
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button class="ml-auto" variant="outline">
                            Columns
                            <ChevronDownIcon class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuCheckboxItem
                            v-for="column in table
                                .getAllColumns()
                                .filter((column) => column.getCanHide())"
                            :key="column.id"
                            :checked="column.getIsVisible()"
                            class="capitalize"
                            @update:checked="
                                (value) => {
                                    column.toggleVisibility(!!value);
                                }
                            "
                        >
                            {{ column.id }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            <div class="rounded-md border">
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
                                        {{ JSON.stringify(row.original) }}
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

            <div class="flex items-center justify-end space-x-2 py-4">
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
                            class="hidden h-8 w-8 p-0 lg:flex"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.setPageIndex(0)"
                        >
                            <DoubleArrowLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="h-8 w-8 p-0"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            <ChevronLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="h-8 w-8 p-0"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            <ChevronRightIcon class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            class="hidden h-8 w-8 p-0 lg:flex"
                            :disabled="!table.getCanNextPage()"
                            @click="
                                table.setPageIndex(table.getPageCount() - 1)
                            "
                        >
                            <DoubleArrowRightIcon class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
        <Dialog v-model:open="showDialog">
            <Form>
                <DialogContent class="w-[1500px]">
                    <DialogHeader>
                        <DialogTitle>Data Master Barang</DialogTitle>
                        <DialogDescription>
                            Data master barang
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-5 gap-4">
                        <div>
                            <Label for="kode_barcode"> Kode Barcode </Label>
                            <Input
                                id="kode_barcode"
                                v-model="form.kode_barcode"
                                class="col-span-3"
                                required
                            />
                            <span
                                v-if="errors?.kode_barcode"
                                class="text-red-500 text-sm"
                                >{{ errors.kode_barcode }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_barang"> Nama Barang </Label>
                            <Input
                                id="nama_barang"
                                v-model="form.nama_barang"
                                class="col-span-3"
                                required
                            />
                            <span
                                v-if="errors?.nama_barang"
                                class="text-red-500 text-sm"
                                >{{ errors.nama_barang }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_satuan"> Satuan </Label>
                            <SearchableSelect
                                required
                                v-model="selectedUnitId"
                                placeholder="Search units..."
                                api-endpoint="http://127.0.0.1:8000/api/products/units"
                                value-field="nama_satuan"
                                display-field="nama_satuan"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading units..."
                                no-results-text="No units found"
                                load-more-text="Load more units"
                                @select="onUnitSelect"
                            />
                            <span
                                v-if="errors?.selectedUnitId"
                                class="text-red-500 text-sm"
                                >{{ errors.selectedUnitId }}</span
                            >
                        </div>
                        <div>
                            <Label for="nama_kategori"> Kategori Barang </Label>
                            <SearchableSelect
                                v-model="selectedCategoryId"
                                placeholder="Search categories..."
                                api-endpoint="http://127.0.0.1:8000/api/products/categories"
                                value-field="nama_kategori"
                                display-field="nama_kategori"
                                search-param="search"
                                :per-page="10"
                                :debounce-time="300"
                                loading-text="Loading categories..."
                                no-results-text="No categories found"
                                load-more-text="Load more categories"
                                @select="onCategorySelect"
                            />
                            <span
                                v-if="errors?.selectedCategoryId"
                                class="text-red-500 text-sm"
                                >{{ errors.selectedCategoryId }}</span
                            >
                        </div>
                        <div>
                            <Label for="isi_barang"> Isi Barang </Label>
                            <Input
                                id="isi_barang"
                                v-model="form.isi_barang"
                                type="number"
                                class="col-span-3"
                                required
                            />
                            <span
                                v-if="errors?.isi_barang"
                                class="text-red-500 text-sm"
                                >{{ errors.isi_barang }}</span
                            >
                        </div>
                        <div class="flex items-center space-x-2">
                            <FormField
                                v-slot="{ value, handleChange }"
                                v-model="form.is_taxable"
                                type="checkbox"
                                name="is_taxable"
                            >
                                <FormItem
                                    class="flex flex-row items-start gap-x-3 space-y-0 rounded-md border p-4"
                                >
                                    <FormControl>
                                        <Checkbox
                                            :checked="value"
                                            @update:checked="handleChange"
                                        />
                                    </FormControl>
                                    <div class="space-y-1 leading-none">
                                        <FormLabel>Barang Kena Pajak</FormLabel>
                                        <FormMessage />
                                    </div>
                                </FormItem>
                            </FormField>
                        </div>
                    </div>
                    <DialogHeader class="mt-4">
                        <DialogTitle>Data Detail Barang</DialogTitle>
                        <DialogDescription>
                            Data detail barang
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-5 gap-4">
                        <div>
                            <Label for="saldo_awal"> Saldo Awal </Label>
                            <Input
                                id="saldo_awal"
                                v-model="form.details['saldo_awal']"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="harga_jual_karton">
                                Harga Jual (Karton)
                            </Label>
                            <Input
                                id="harga_jual_karton"
                                v-model="form.details.harga_jual_karton"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="harga_jual_eceran">
                                Harga Jual (Eceran)
                            </Label>
                            <Input
                                id="harga_jual_eceran"
                                v-model="form.details.harga_jual_eceran"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="harga_beli_karton">
                                Harga Beli (Karton)
                            </Label>
                            <Input
                                id="harga_beli_karton"
                                v-model="form.details.harga_beli_karton"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="harga_beli_eceran">
                                Harga Beli (Eceran)
                            </Label>
                            <Input
                                id="harga_beli_eceran"
                                v-model="form.details.harga_beli_eceran"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="hpp_avg_karton"> HPP (Karton) </Label>
                            <Input
                                id="hpp_avg_karton"
                                v-model="form.details.hpp_avg_karton"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="hpp_avg_eceran"> HPP (Eceran) </Label>
                            <Input
                                id="hpp_avg_eceran"
                                v-model="form.details.hpp_avg_eceran"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="current_stock"> Current Stock </Label>
                            <Input
                                id="current_stock"
                                v-model="form.details.current_stock"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                        <div>
                            <Label for="nilai_akhir">
                                Nilai Akhir Persediaan
                            </Label>
                            <Input
                                id="nilai_akhir"
                                v-model="form.details.nilai_akhir"
                                type="number"
                                class="col-span-3"
                                required
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button @click="submit"> Save changes </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>
