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
import {
    Form,
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

const showCreate = ref(false);

const showDialogCreate = () => {
    showCreate.value = true;
};

const selectedCategory = ref(null);

const selectedUnit = ref(null);

const onCategorySelect = (category) => {
    selectedCategory.value = category;
};

const onUnitSelect = (unit) => {
    selectedUnit.value = unit;
};

const columns = [
    {
        accessorKey: "kode_pembelian",
        header: () => h("div", { class: "text-left" }, "Kode Barang"),
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
        header: () => h("div", { class: "text-left" }, "Kode Barcode"),
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
        header: () => h("div", { class: "text-left" }, "Nama Barang"),
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

            return h('div', { class: 'relative' }, h(DropdownAction, {
                product,
                onEdit: () => onEdit(product.id),
                onExpand: row.toggleExpanded,
            }));
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

const resetForm = () => {
    form.reset();
}

const submit = () => {
    const url = form.id ? `/purchasing/${form.id}` : '/purchasing';
    const method = form.id ? 'put' : 'post';
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
  showCreate.value = true 
  try {
    const res = await fetch(`/purchasing/${id}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    })
    if (!res.ok) {
      console.error('Error ');
    }
    const data = await res.json();
    // Set to form
    form.id = data.data.id; 
    form.kode_barcode = data.data.kode_barcode;
    form.nama_barang = data.data.nama_barang;
    form.nama_satuan = data.data.nama_satuan;
    form.nama_kategori = data.data.nama_kategori;
    form.isi_barang = data.data.isi_barang;
    form.is_taxable = data.data.is_taxable === "1" ? true : false;
    form.details.saldo_awal = data.data.details['saldo_awal'];
    form.details.harga_jual_karton = data.data.details['harga_jual_karton'];
    form.details.harga_jual_eceran = data.data.details['harga_jual_eceran'];
    form.details.harga_beli_karton = data.data.details['harga_beli_karton'];
    form.details.harga_beli_eceran = data.data.details['harga_beli_eceran'];
    form.details.hpp_avg_karton = data.data.details['hpp_avg_karton'];
    form.details.hpp_avg_eceran = data.data.details['hpp_avg_eceran'];
    form.details.current_stock = data.data.details['current_stock'];
    form.details.nilai_akhir = data.data.details['nilai_akhir'];
  } catch (error) {
    console.error(error);
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(price);
};
</script>

<template>
    <Layout>
        <div class="flex items-center">
            <h1 class="text-lg font-semibold md:text-2xl">Products</h1>
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
                        table.getColumn('kode_pembelian')?.setFilterValue($event)
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
                                            <TableHead>Saldo Awal</TableHead>
                                            <TableHead>Harga Jual (Karton)</TableHead>
                                            <TableHead>Harga Jual (Eceran)</TableHead>
                                            <TableHead>Harga Beli (Karton)</TableHead>
                                            <TableHead>Harga Beli (Eceran)</TableHead>
                                            <TableHead>HPP (Karton)</TableHead>
                                            <TableHead>HPP (Eceran)</TableHead>
                                            <TableHead>Current Stock</TableHead>
                                            <TableHead>Nilai Akhir</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow>
                                            <TableCell class="font-medium">{{ row.original.details['saldo_awal'] }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['harga_jual_karton']) }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['harga_jual_eceran']) }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['harga_beli_karton']) }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['harga_beli_eceran']) }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['hpp_avg_karton']) }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['hpp_avg_eceran']) }}</TableCell>
                                            <TableCell class="font-medium">{{ row.original.details['current_stock'] }}</TableCell>
                                            <TableCell class="font-medium">{{ formatPrice(row.original.details['nilai_akhir']) }}</TableCell>
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
        <Dialog v-model:open="showCreate" @update:open="(val) => { if (!val) resetForm(); showCreate = val; }">
            <Form>
                <DialogContent class="w-[1500px]">
                    <DialogHeader>
                        <DialogTitle>{{ form.id ? 'Edit' : 'Create' }} Data Master Barang</DialogTitle>
                        <DialogDescription>
                            Data master barang
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid grid-cols-5 gap-4">
                        <div>
                            <Label for="nama_supplier"> Supplier </Label>
                            <SearchableSelect
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
                                @select="onUnitSelect"
                            />
                            <span
                                v-if="errors?.nama_supplier"
                                class="text-sm text-red-500"
                                >{{ errors.nama_supplier }}</span
                            >
                        </div>
                    </div>
                    <DialogHeader class="mt-4">
                        <DialogTitle>Data Detail Barang</DialogTitle>
                        <DialogDescription>
                            Data detail barang
                        </DialogDescription>
                    </DialogHeader>
                    <Table>
                        <TableHeader>
                        <TableRow>
                            <TableHead>Saldo Awal</TableHead>
                            <TableHead>Harga Jual (Karton)</TableHead>
                            <TableHead>Harga Jual (Eceran)</TableHead>
                            <TableHead>Harga Beli (Karton)</TableHead>
                            <TableHead>Harga Beli (Eceran)</TableHead>
                            <TableHead>HPP (Karton)</TableHead>
                            <TableHead>HPP (Eceran)</TableHead>
                            <TableHead>Current Stock</TableHead>
                            <TableHead>Nilai Akhir Persediaan</TableHead>
                        </TableRow>
                        </TableHeader>
                        <TableBody>
                        <TableRow>
                            <TableCell>
                                <Input
                                    id="saldo_awal"
                                    v-model="form.details.saldo_awal"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="harga_jual_karton"
                                    v-model="form.details.harga_jual_karton"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="harga_jual_eceran"
                                    v-model="form.details.harga_jual_eceran"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="harga_beli_karton"
                                    v-model="form.details.harga_beli_karton"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="harga_beli_eceran"
                                    v-model="form.details.harga_beli_eceran"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="hpp_avg_karton"
                                    v-model="form.details.hpp_avg_karton"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="hpp_avg_eceran"
                                    v-model="form.details.hpp_avg_eceran"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="current_stock"
                                    v-model="form.details.current_stock"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                            <TableCell>
                                <Input
                                    id="nilai_akhir"
                                    v-model="form.details.nilai_akhir"
                                    type="number"
                                    class="col-span-3"
                                    required
                                />
                            </TableCell>
                        </TableRow>
                        </TableBody>
                    </Table>
                    <DialogFooter>
                        <Button @click="submit"> Save </Button>
                    </DialogFooter>
                </DialogContent>
            </Form>
        </Dialog>
    </Layout>
</template>
