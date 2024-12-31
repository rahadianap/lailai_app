<template>
    <Layout2>
        <div class="flex h-screen">
            <!-- Left side: Product selection and cart -->
            <div class="w-2/3 p-4 flex flex-col">
                <!-- Barcode scanner input -->
                <div class="mb-4">
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
                <div class="mb-4">
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

                <!-- Shopping cart -->
                <div class="flex-grow overflow-hidden border rounded-md">
                    <Table>
                        <TableHeader class="sticky top-0 bg-white z-10">
                            <TableRow>
                                <TableHead>Product</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Quantity</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(item, index) in cart"
                                :key="index"
                            >
                                <TableCell>{{ item.nama_barang }}</TableCell>
                                <TableCell>{{
                                    formatCurrency(item.harga_jual_eceran)
                                }}</TableCell>
                                <TableCell>
                                    <Input
                                        v-model.number="item.quantity"
                                        type="number"
                                        class="w-20 editable-input"
                                        @input="updateCartItem(index)"
                                    />
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

            <!-- Right side: Payment processing and totals -->
            <div
                class="w-1/3 p-4 bg-gray-100 flex flex-col border border-md rounded-md"
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
                        <Select v-model="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                        </Select>
                    </div>

                    <div v-if="paymentMethod === 'cash'">
                        <Label htmlFor="cash_received">Cash Received</Label>
                        <Input
                            v-model.number="cashReceived"
                            type="number"
                            id="cash_received"
                            class="editable-input"
                        />
                    </div>

                    <div
                        v-if="paymentMethod === 'cash' && cashReceived >= total"
                    >
                        <Label>Change</Label>
                        <Input
                            :value="formatCurrency(cashReceived - total)"
                            type="text"
                            readonly
                            class="readonly-input"
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
    </Layout2>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Layout2 from "../../Layout/Layout2.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
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

const { printReceipt: printReceiptToPrinter } = usePrinter();

const selectedProduct = ref(null);
const cart = ref([]);
const paymentMethod = ref("cash");
const cashReceived = ref(0);
const showPaymentSuccess = ref(false);
const barcodeInput = ref("");
const showDailySalesReport = ref(false);
const dailySalesReport = ref([]);

// onMounted(async () => {
//     await fetchDailySalesReport();
// });

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
        addProductToCart(product);
        barcodeInput.value = "";
    } catch (error) {
        console.error("Error fetching product by barcode:", error);
        // Show error message to user
    }
};

const addProductToCart = (product) => {
    const existingItem = cart.value.find((item) => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += 1;
        existingItem.total =
            existingItem.details.harga_jual_eceran * existingItem.quantity;
    } else {
        cart.value.push({
            id: product.id,
            nama_barang: product.nama_barang,
            harga_jual_eceran: product.details.harga_jual_eceran,
            quantity: 1,
            total: product.details.harga_jual_eceran,
        });
    }
    selectedProduct.value = null;
};

const updateCartItem = (index) => {
    const item = cart.value[index];
    item.total = item.harga_jual_eceran * item.quantity;
    if (item.quantity < 0) {
        removeFromCart(index);
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.total, 0);
});

const tax = computed(() => {
    return subtotal.value * 0.11;
});

const total = computed(() => {
    return subtotal.value + tax.value;
});

const canProcessPayment = computed(() => {
    if (cart.value.length === 0) return false;
    if (paymentMethod.value === "cash") {
        return cashReceived.value >= total.value;
    }
    return true;
});

const processPayment = async () => {
    try {
        const response = await fetch(
            "http://127.0.0.1:8000/api/pos/transactions",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    // Add your custom authentication header here
                    // 'Authorization': `Bearer ${yourAuthToken}`
                },
                body: JSON.stringify({
                    items: cart.value,
                    total: total.value,
                    paymentMethod: paymentMethod.value,
                    cashReceived: cashReceived.value,
                }),
            },
        );

        if (!response.ok) throw new Error("Payment processing failed");

        const result = await response.json();
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
};

const printReceipt = () => {
    const receiptData = {
        items: cart.value,
        subtotal: subtotal.value,
        tax: tax.value,
        total: total.value,
        paymentMethod: paymentMethod.value,
        cashReceived: cashReceived.value,
        change:
            paymentMethod.value === "cash"
                ? cashReceived.value - total.value
                : 0,
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
</script>

<style scoped>
.editable-input {
    background-color: #ffffff;
    border: 1px solid #d1d5db;
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
.searchable-select-wrapper {
    position: relative;
    z-index: 30;
}
</style>
