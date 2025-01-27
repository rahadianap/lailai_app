<template>
    <PosLayout>
        <div class="flex flex-col lg:flex-row h-[calc(100vh-4rem)]">
            <!-- Left side: Product selection and cart -->
            <div class="w-full lg:w-2/3 p-4 flex flex-col overflow-hidden">
                <!-- Barcode scanner input -->
                <div class="flex justify-start gap-4">
                    <div class="mb-4 w-full">
                        <Label htmlFor="barcode_input">Scan Barcode</Label>
                        <Input
                            v-model="barcodeInput"
                            @keydown.enter="onBarcodeEnter"
                            placeholder="Scan or enter barcode"
                            autofocus
                            class="editable-input"
                        />
                    </div>
                    <!-- Product search -->
                    <div class="searchable-select-wrapper mb-4 w-full">
                        <Label htmlFor="product_search">Search Products</Label>
                        <SearchableSelect2
                            v-model="selectedProduct"
                            placeholder="Search by barcode or product name..."
                            api-endpoint="http://127.0.0.1:8000/api/pos/products"
                            value-field="id"
                            :display-fields="['kode_barcode', 'nama_barang']"
                            :search-fields="['kode_barcode', 'nama_barang']"
                            :per-page="10"
                            :debounce-time="300"
                            loading-text="Loading products..."
                            no-results-text="No products found"
                            @select="onProductSelect"
                            class="relative z-20"
                            :default-open="false"
                        />
                    </div>
                </div>
                <!-- Shopping cart -->
                <div class="flex-grow overflow-hidden border rounded-md">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader class="sticky top-0 bg-white z-10">
                                <TableRow>
                                    <TableHead class="w-1/4">Product</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Quantity</TableHead>
                                    <TableHead>DPP</TableHead>
                                    <TableHead>PPN</TableHead>
                                    <TableHead>Taxable</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(item, index) in cart"
                                    :key="index"
                                >
                                    <TableCell class="w-1/4">{{
                                        item.nama_barang
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.harga_jual_eceran)
                                    }}</TableCell>
                                    <TableCell>
                                        <Input
                                            v-model.number="item.quantity"
                                            type="number"
                                            class="w-40"
                                            @input="updateCartItem(index)"
                                            min="0"
                                        />
                                    </TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.dpp)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.ppn)
                                    }}</TableCell>
                                    <TableCell>
                                        <Checkbox
                                            disabled
                                            v-model.number="item.is_taxable"
                                            :checked="item.is_taxable"
                                            @update:checked="
                                                item.is_taxable = $event
                                            "
                                        />
                                        <!-- <Checkbox
                                            disabled
                                            :checked="item.is_taxable"
                                            @update:checked="
                                                item.is_taxable = $event
                                            "
                                        /> -->
                                    </TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.total)
                                    }}</TableCell>
                                    <TableCell>
                                        <Button
                                            @click="removeFromCart(index)"
                                            variant="destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <!-- Right side: Payment processing and totals -->
            <div
                class="w-full lg:w-1/3 p-4 bg-gray-100 flex flex-col overflow-y-auto border rounded-md"
            >
                <h2 class="text-2xl font-bold mb-4">Order Summary</h2>

                <div class="flex-grow">
                    <div class="flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span>{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Tax (11%):</span>
                        <span>{{ formatCurrency(tax) }}</span>
                    </div>
                    <div
                        v-if="appliedVoucher"
                        class="flex justify-between mb-2 text-green-600"
                    >
                        <span
                            >Voucher ({{ appliedVoucher.kode_voucher }}):</span
                        >
                        <span>{{
                            formatCurrency(appliedVoucher.nominal * -1)
                        }}</span>
                    </div>
                    <div
                        v-if="appliedMember"
                        class="flex justify-between mb-2 text-green-600"
                    >
                        <span>Member Points ({{ appliedMember.point }}):</span>
                        <span>{{
                            formatCurrency(appliedMember.point * 50 * -1)
                        }}</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span class="text-lg font-bold">Grand Total:</span>
                        <span class="text-lg font-bold">{{
                            formatCurrency(total)
                        }}</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <Label htmlFor="payment_method">Payment Method</Label>
                        <Select
                            v-model="paymentMethod"
                            @update:modelValue="applyMember"
                            class="col-span-3"
                        >
                            <SelectTrigger id="paymentMethod">
                                <SelectValue
                                    placeholder="Select a payment method"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="card">Card</SelectItem>
                                <SelectItem value="edc_bca">EDC BCA</SelectItem>
                                <SelectItem value="edc_mandiri"
                                    >EDC Mandiri</SelectItem
                                >
                                <SelectItem value="edc_uob">EDC UOB</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="paymentMethod === 'cash'">
                        <Label htmlFor="cash_received">Cash Received</Label>
                        <Input
                            v-model.number="cashReceived"
                            type="number"
                            id="cash_received"
                            class="editable-input"
                            @input="updateChange"
                            min="0"
                            step="0.01"
                        />
                    </div>

                    <div v-if="paymentMethod === 'cash'">
                        <Label>Change</Label>
                        <Input
                            :value="formatCurrency(change)"
                            type="text"
                            readonly
                            class="readonly-input"
                        />
                    </div>

                    <div v-if="paymentMethod !== 'cash'">
                        <Label htmlFor="card_number">Card Number</Label>
                        <Input
                            v-model.number="cardNumber"
                            type="number"
                            id="card_number"
                            class="editable-input"
                            @input="updateChange"
                        />
                    </div>

                    <Button
                        @click="processPayment"
                        class="w-full"
                        :disabled="!canProcessPayment"
                    >
                        Process Payment
                    </Button>
                </div>
            </div>
        </div>

        <!-- Payment success dialog -->
        <Dialog
            :open="showPaymentSuccess"
            @update:open="showPaymentSuccess = $event"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Payment Successful</DialogTitle>
                </DialogHeader>
                <p>Your payment has been processed successfully.</p>
                <DialogFooter>
                    <Button @click="resetTransaction">New Transaction</Button>
                    <Button @click="printReceipt" variant="outline"
                        >Print Receipt</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <!-- Daily Sales Report Dialog -->
        <Dialog
            :open="showDailySalesReport"
            @update:open="showDailySalesReport = $event"
        >
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Daily Sales Report</DialogTitle>
                </DialogHeader>
                <div class="mt-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Total Sales</TableHead>
                                <TableHead>Number of Transactions</TableHead>
                                <TableHead>Average Transaction Value</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(report, index) in dailySalesReport"
                                :key="index"
                            >
                                <TableCell>{{ report.date }}</TableCell>
                                <TableCell>{{
                                    formatCurrency(report.totalSales)
                                }}</TableCell>
                                <TableCell>{{
                                    report.transactionCount
                                }}</TableCell>
                                <TableCell>{{
                                    formatCurrency(
                                        report.averageTransactionValue,
                                    )
                                }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <DialogFooter>
                    <Button @click="showDailySalesReport = false">Close</Button>
                    <Button @click="exportDailySalesReport" variant="outline"
                        >Export Report</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <VoucherPopup
            :isOpen="showVoucherPopup"
            :appliedVoucher="appliedVoucher"
            @update:isOpen="showVoucherPopup = $event"
            @applyVoucher="handleApplyVoucher"
            @removeVoucher="removeVoucher"
        />
        <MemberPopup
            :isOpen="showMemberPopup"
            :appliedMember="appliedMember"
            @update:isOpen="showMemberPopup = $event"
            @applyMember="handleApplyMember"
            @removeMember="removeMember"
        />
    </PosLayout>
    <DeleteConfirmationDialog
        :isOpen="showDeleteConfirmation"
        @update:isOpen="showDeleteConfirmation = $event"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
    />
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import PosLayout from "../../Layout/POSLayout.vue";
import DeleteConfirmationDialog from "@/components/DeleteCart.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import SearchableSelect2 from "../../components/SearchableSelect2.vue";
import { Trash2 } from "lucide-vue-next";
import { usePrinter } from "@/composables/usePrinter";
import VoucherPopup from "@/components/VoucherPopup.vue";
import MemberPopup from "@/components/MemberPopup.vue";
import { Checkbox } from "@/components/ui/checkbox";

const { printReceipt: printReceiptToPrinter } = usePrinter();

const selectedProduct = ref(null);
const cart = ref([]);
const paymentMethod = ref("cash");
const cashReceived = ref(0);
const showPaymentSuccess = ref(false);
const barcodeInput = ref("");
const showDailySalesReport = ref(false);
const dailySalesReport = ref([]);
const showVoucherPopup = ref(false);
const appliedVoucher = ref(null);
const showMemberPopup = ref(false);
const appliedMember = ref(null);

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
});

const showDeleteConfirmation = ref(false);
const itemToDeleteIndex = ref(null);

// const removeFromCart = (index) => {
//     itemToDeleteIndex.value = index;
//     showDeleteConfirmation.value = true;
// };

const confirmDelete = (password) => {
    // Here you would typically verify the password with your backend
    // For this example, we'll use a hardcoded password "admin123"
    if (password === "admin123") {
        if (itemToDeleteIndex.value !== null) {
            cart.value.splice(itemToDeleteIndex.value, 1);
            itemToDeleteIndex.value = null;
        }
        showDeleteConfirmation.value = false;
    } else {
        alert("Incorrect password. Deletion cancelled.");
    }
};

const cancelDelete = () => {
    itemToDeleteIndex.value = null;
    showDeleteConfirmation.value = false;
};

const handleKeyDown = (event) => {
    // Ignore if focus is on an input element
    if (
        event.target instanceof HTMLInputElement ||
        event.target instanceof HTMLTextAreaElement
    ) {
        return;
    }

    if (
        (event.key === "v" || event.key === "V") &&
        (event.ctrlKey || event.metaKey)
    ) {
        event.preventDefault();
        showVoucherPopup.value = true;
    } else if (
        (event.key === "m" || event.key === "M") &&
        (event.ctrlKey || event.metaKey)
    ) {
        event.preventDefault();
        showMemberPopup.value = true;
    }
};

const handleApplyVoucher = (voucher) => {
    if (voucher) {
        appliedVoucher.value = voucher;
        showVoucherPopup.value = false;
    } else {
        console.error("Invalid voucher selected");
        // Optionally, show an error message to the user
    }
};

const handleApplyMember = (member) => {
    if (member) {
        appliedMember.value = member;
        // showMemberPopup.value = false;
    } else {
        console.error("Invalid member selected");
        // Optionally, show an error message to the user
    }
};

const updateChange = () => {
    // Force reactivity update
    cashReceived.value = Number(cashReceived.value);
};

const onProductSelect = (product) => {
    addProductToCart(product);
};

const onBarcodeEnter = async () => {
    try {
        const response = await fetch(
            `http://127.0.0.1:8000/api/pos/products/barcode/${barcodeInput.value}`,
        );
        if (!response.ok) throw new Error("Product not found");
        const product = await response.json();
        addProductToCart2(product);
        barcodeInput.value = "";
    } catch (error) {
        console.error("Error fetching product by barcode:", error);
        // Show error message to user
    }
};

const addProductToCart2 = (product) => {
    cart.value.push({
        id: product.id,
        kode_barcode: product.kode_barcode,
        nama_barang: product.nama_barang,
        harga_jual_eceran: Number(product.harga_jual_eceran),
        quantity: 1,
        total: Number(product.harga_jual_eceran),
    });
    selectedProduct.value = null;
    // const existingItem = cart.value.find((item) => item.id === product.id);
    // if (existingItem) {
    //     existingItem.quantity += 1;
    //     existingItem.total = Number(existingItem.harga) * existingItem.quantity;
    // } else {
    //     cart.value.push({
    //         id: product.id,
    //         kode_barcode: product.kode_barcode,
    //         nama_barang: product.nama_barang,
    //         harga: Number(product.details.harga_jual_eceran),
    //         quantity: 1,
    //         total: Number(product.details.harga_jual_eceran),
    //     });
    // }
    // selectedProduct.value = null;
};

const addProductToCart = (product) => {
    cart.value.push({
        id: product.id,
        kode_barcode: product.kode_barcode,
        nama_barang: product.nama_barang,
        harga_jual_eceran: Number(product.details.harga_jual_eceran),
        quantity: 1,
        dpp:
            product.is_taxable === "1"
                ? Number(product.details.harga_jual_eceran) * (100 / 111)
                : 0,
        ppn:
            product.is_taxable === "1"
                ? Number(product.details.harga_jual_eceran) * 0.11
                : 0,
        is_taxable: product.is_taxable === "1" ? true : false,
        total: Number(product.details.harga_jual_eceran),
    });
    selectedProduct.value = null;
    // const existingItem = cart.value.find((item) => item.id === product.id);
    // if (existingItem) {
    //     existingItem.quantity += 1;
    //     existingItem.total = Number(existingItem.harga) * existingItem.quantity;
    // } else {
    //     cart.value.push({
    //         id: product.id,
    //         kode_barcode: product.kode_barcode,
    //         nama_barang: product.nama_barang,
    //         harga: Number(product.details.harga_jual_eceran),
    //         quantity: 1,
    //         total: Number(product.details.harga_jual_eceran),
    //     });
    // }
    // selectedProduct.value = null;
};

const updateCartItem = (index) => {
    const item = cart.value[index];
    item.quantity = Math.max(0, item.quantity);
    item.total = Number(item.harga_jual_eceran) * item.quantity;
    // item.dpp = item.is_taxable
    //     ? Number(item.harga_jual_eceran) * (100 / 111) * item.quantity
    //     : 0;
    item.ppn = item.is_taxable
        ? Number(item.harga_jual_eceran) * 0.11 * item.quantity
        : 0;
};

const removeFromCart = (index) => {
    // cart.value.splice(index, 1);
    itemToDeleteIndex.value = index;
    showDeleteConfirmation.value = true;
};

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + Number(item.total), 0);
});

const tax = computed(() => {
    return cart.value.reduce((sum, item) => {
        return (
            sum + (item.is_taxable ? Number((item.total * 0.11).toFixed(2)) : 0)
        );
    }, 0);
});

const total = computed(() => {
    const totalBeforeVoucher = subtotal.value + tax.value;
    const voucherDiscount = appliedVoucher.value
        ? appliedVoucher.value.nominal
        : 0;
    const memberDiscount = appliedMember.value
        ? appliedMember.value.point * 50
        : 0;
    return Math.max(0, totalBeforeVoucher - voucherDiscount - memberDiscount);
});

const change = computed(() => {
    return Math.max(0, cashReceived.value - total.value);
});

const canProcessPayment = computed(() => {
    if (
        cart.value.length === 0 ||
        cart.value.some((item) => item.quantity <= 0) ||
        cashReceived.value < total.value
    ) {
        return false;
    } else {
        return true;
    }
    // if (paymentMethod.value === "cash") {
    //     return Number(cashReceived.value) >= total.value;
    // }
});

const processPayment = async () => {
    try {
        // const response = await fetch(
        //     "http://127.0.0.1:8000/api/pos/transactions",
        //     {
        //         method: "POST",
        //         headers: {
        //             "Content-Type": "application/json",
        //             // Add your custom authentication header here
        //             // 'Authorization': `Bearer ${yourAuthToken}`
        //         },
        //         body: JSON.stringify({
        //             items: cart.value,
        //             total: total.value,
        //             paymentMethod: paymentMethod.value,
        //             cashReceived: cashReceived.value,
        //         }),
        //     },
        // );

        // if (!response.ok) throw new Error("Payment processing failed");

        // const result = await response.json();
        showPaymentSuccess.value = true;
    } catch (error) {
        console.error("Error processing payment:", error);
        // Show error message to user
    }
};

const resetTransaction = () => {
    cart.value = [];
    paymentMethod.value = "cash";
    cashReceived.value = 0;
    showPaymentSuccess.value = false;
    selectedProduct.value = null;
    appliedVoucher.value = null;
    appliedMember.value = null;
};

const printReceipt = () => {
    const receiptData = {
        items: cart.value,
        subtotal: subtotal.value,
        tax: tax.value,
        total: total.value,
        paymentMethod: paymentMethod.value,
        cashReceived: cashReceived.value,
        change: change.value,
        appliedVoucher: appliedVoucher.value,
        appliedMember: appliedMember.value,
    };
    printReceiptToPrinter(receiptData);
};

// const fetchDailySalesReport = async () => {
//     try {
//         const response = await fetch(
//             "http://127.0.0.1:8000/api/pos/daily-sales-report",
//             {
//                 headers: {
//                     // Add your custom authentication header here
//                     // 'Authorization': `Bearer ${yourAuthToken}`
//                 },
//             },
//         );
//         if (!response.ok) throw new Error("Failed to fetch daily sales report");
//         dailySalesReport.value = await response.json();
//     } catch (error) {
//         console.error("Error fetching daily sales report:", error);
//         // Show error message to user
//     }
// };

// const exportDailySalesReport = () => {
//     // Implement export functionality (e.g., to CSV)
//     console.log("Exporting daily sales report...");
// };

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

// Watch for changes in the cart and update the total
watch(
    cart,
    () => {
        // The total will be automatically recalculated due to the computed property
    },
    { deep: true },
);

const removeVoucher = () => {
    appliedVoucher.value = null;
};

const removeMember = () => {
    appliedMember.value = null;
};
</script>
