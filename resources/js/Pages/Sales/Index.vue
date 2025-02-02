<template>
    <PosLayout>
        <div class="flex flex-col lg:flex-row h-[calc(100vh-4rem)]">
            <!-- Left side: Product selection and cart -->
            <div class="flex flex-col w-full p-4 overflow-hidden lg:w-2/3">
                <!-- Barcode scanner input -->
                <div class="flex justify-start gap-4">
                    <div class="w-full mb-4">
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
                    <div class="w-full mb-4 searchable-select-wrapper">
                        <Label htmlFor="product_search">Search Products</Label>
                        <SearchableSelect2
                            v-model="selectedProduct"
                            placeholder="Search by barcode or product name..."
                            api-endpoint="http://127.0.0.1:8000/api/pos/products"
                            value-field="id"
                            :display-fields="['kode_barcode', 'nama_barang']"
                            :search-fields="['kode_barcode', 'nama_barang']"
                            :per-page="1"
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
                            <TableHeader class="sticky top-0 z-10 bg-white">
                                <TableRow>
                                    <TableHead class="w-1/4">Product</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Quantity</TableHead>
                                    <TableHead>DPP</TableHead>
                                    <TableHead>PPN</TableHead>
                                    <TableHead>Diskon</TableHead>
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
                                        <Button
                                            @click="
                                                openQuantityChangeModal(index)
                                            "
                                            variant="outline"
                                        >
                                            {{ item.quantity }}
                                        </Button>
                                    </TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.dpp)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.ppn)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.diskon)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.total)
                                    }}</TableCell>
                                    <TableCell>
                                        <Button
                                            @click="removeFromCart(index)"
                                            variant="destructive"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <!-- Right side: Payment processing and totals -->
            <form
                @submit.prevent="processPayment"
                class="flex flex-col w-full p-4 overflow-y-auto bg-gray-100 border rounded-md lg:w-1/3"
            >
                <h2 class="mb-4 text-2xl font-bold">Order Summary</h2>

                <div class="flex-grow">
                    <div class="flex justify-between mb-2">
                        <span class="text-xl">Subtotal:</span>
                        <span class="text-xl">{{
                            formatCurrency(subtotal)
                        }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-xl">Tax (11%):</span>
                        <span class="text-xl">{{ formatCurrency(tax) }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-xl">Diskon:</span>
                        <span class="text-xl text-red-500">{{
                            formatCurrency(diskon_global * -1)
                        }}</span>
                    </div>
                    <div
                        v-if="appliedVoucher"
                        class="flex justify-between mb-2"
                    >
                        <span class="text-xl"
                            >Voucher ({{ appliedVoucher.kode_voucher }}):</span
                        >
                        <span class="text-xl text-red-500">{{
                            formatCurrency(appliedVoucher.nominal * -1)
                        }}</span>
                    </div>
                    <div v-if="appliedMember" class="flex justify-between mb-2">
                        <span>Member Points ({{ appliedMember.point }}):</span>
                        <span class="text-xl text-red-500">{{
                            formatCurrency(appliedMember.point * 50 * -1)
                        }}</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span class="text-xl font-bold">Grand Total:</span>
                        <span class="text-xl font-bold">{{
                            formatCurrency(total)
                        }}</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <Label
                            htmlFor="payment_method"
                            class="text-xl font-bold"
                            >Payment Method</Label
                        >
                        <Select
                            v-model="form.payment_method"
                            class="col-span-3"
                        >
                            <SelectTrigger
                                id="paymentMethod"
                                class="mt-2 text-xl font-bold"
                            >
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

                    <div>
                        <Label htmlFor="customer_type" class="text-xl font-bold"
                            >Customer Type</Label
                        >
                        <Select v-model="form.customer_type" class="col-span-3">
                            <SelectTrigger
                                id="customerType"
                                class="mt-2 text-xl font-bold"
                            >
                                <SelectValue
                                    placeholder="Select a customer type"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="walk_in"
                                    >Walk In Customer</SelectItem
                                >
                                <SelectItem value="cafe">Cafe</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="form.payment_method === 'cash'">
                        <Label htmlFor="cash_received" class="text-xl font-bold"
                            >Cash Received</Label
                        >
                        <Input
                            v-model.number="cashReceived"
                            type="number"
                            id="cash_received"
                            class="mt-2 text-xl font-bold"
                            @input="updateChange"
                        />
                    </div>

                    <div v-if="form.payment_method === 'cash'">
                        <Label class="text-xl font-bold">Change</Label>
                        <Input
                            :value="formatCurrency(change)"
                            type="text"
                            readonly
                            class="mt-2 text-xl font-bold"
                        />
                    </div>

                    <div v-if="form.payment_method !== 'cash'">
                        <Label htmlFor="card_number" class="text-xl font-bold"
                            >Card Number</Label
                        >
                        <Input
                            v-model.number="cardNumber"
                            type="number"
                            id="card_number"
                            class="text-xl font-bold"
                            @input="updateChange"
                        />
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="!canProcessPayment || isProcessing"
                    >
                        {{ isProcessing ? "Processing..." : "Process Payment" }}
                    </Button>
                </div>
            </form>
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
            @apply-members="handleApplyMember"
            @remove-member="handleRemoveMember"
            @apply-points="handleApplyPoints"
        />
    </PosLayout>
    <QuantityChangeModal
        :isOpen="showQuantityChangeModal"
        @update:isOpen="showQuantityChangeModal = $event"
        @confirm="confirmQuantityChange"
        @cancel="cancelQuantityChange"
    />
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
import { useForm } from "@inertiajs/vue3";
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
import QuantityChangeModal from "@/components/UpdateCarQty.vue";

const form = useForm({
    id: null,
    payment_method: "cash",
    customer_type: "walk_in",
    kode_voucher: "",
    kode_member: "",
    subtotal: 0,
    tax: 0,
    total: 0,
    diskon_global: 0,
    cash_received: 0,
    change: 0,
    applied_points: 0,
    details: [
        {
            kode_barcode: "",
            nama_barang: "",
            qty: 0,
            harga: 0,
            diskon: "",
            dpp: "Pcs",
            ppn: 0,
            subtotal: 0,
        },
    ],
});

const { printReceipt: printReceiptToPrinter } = usePrinter();

const selectedProduct = ref(null);
const cart = ref([]);
const paymentMethod = ref("cash");
const customerType = ref("walk_in");
const cashReceived = ref(0);
const showPaymentSuccess = ref(false);
const barcodeInput = ref("");
const showDailySalesReport = ref(false);
const dailySalesReport = ref([]);
const showVoucherPopup = ref(false);
const appliedVoucher = ref(null);
const showMemberPopup = ref(false);
const appliedMember = ref(null);
const showDeleteConfirmation = ref(false);
const itemToDeleteIndex = ref(null);
const showQuantityChangeModal = ref(false);
const itemToChangeIndex = ref(null);
const isProcessing = ref(false); // Added isProcessing ref
const cardNumber = ref(null);

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
});

const openQuantityChangeModal = (index) => {
    itemToChangeIndex.value = index;
    showQuantityChangeModal.value = true;
};

const confirmQuantityChange = ({ newQuantity, password }) => {
    if (password === "admin123") {
        if (itemToChangeIndex.value !== null) {
            const item = cart.value[itemToChangeIndex.value];
            item.quantity = newQuantity;
            updateCartItem(itemToChangeIndex.value);
            itemToChangeIndex.value = null;
        }
        showQuantityChangeModal.value = false;
    } else {
        alert("Incorrect password. Quantity change cancelled.");
    }
};

const cancelQuantityChange = () => {
    itemToChangeIndex.value = null;
    showQuantityChangeModal.value = false;
};

// const removeFromCart = (index) => {
//     itemToDeleteIndex.value = index;
//     showDeleteConfirmation.value = true;
// };

const confirmDelete = (password) => {
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

const handleRemoveMember = () => {
    appliedMember.value = null;
    console.log("Member removed");
    // Reset any member-related state here
};

const handleApplyMember = (memberData) => {
    appliedMember.value = {
        kode_member: memberData.kode_member,
        point: 0,
    };
    console.log(
        `Applying ${memberData.point} points for member ${memberData.kode_member}`,
    );
    // Here you would implement the logic to cut the total price
    // For example:
    total.value -= 0;
    if (total.value < 0) total.value = 0;
    console.log(`New total price after applying points: ${total.value}`);
};

const handleApplyPoints = (memberData) => {
    appliedMember.value = {
        kode_member: memberData.kode_member,
        point: memberData.point,
    };
    console.log(
        `Applying ${memberData.point} points for member ${memberData.kode_member}`,
    );
    // Here you would implement the logic to cut the total price
    // For example:
    total.value -= memberData.point;
    if (total.value < 0) total.value = 0;
    console.log(`New total price after applying points: ${total.value}`);
};

const updateChange = () => {
    // Force reactivity update
    cashReceived.value = Number(cashReceived.value);
};

const onProductSelect = (product) => {
    addProductToCart(product);
    selectedProduct.value = "";
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
        diskon: Number(product.details.diskon),
        dpp: Number(product.details.harga_jual_eceran) * (11 / 12),
        ppn: Number(product.details.harga_jual_eceran) * 0.11,
        total:
            Number(product.details.harga_jual_eceran) +
            Number(product.details.harga_jual_eceran) * 0.11,
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
    item.dpp =
        Number(item.harga_jual_eceran) *
        (customerType.value === "cafe" ? 1 : 11 / 12) *
        item.quantity;
    item.ppn =
        customerType.value === "cafe"
            ? 0
            : Number(item.harga_jual_eceran) * 0.11 * item.quantity;
    item.total = Number(item.harga_jual_eceran) * item.quantity + item.ppn;
};

watch(customerType, () => {
    cart.value.forEach((item, index) => updateCartItem(index));
});

const removeFromCart = (index) => {
    // cart.value.splice(index, 1);
    itemToDeleteIndex.value = index;
    showDeleteConfirmation.value = true;
};

const subtotal = computed(() => {
    return cart.value.reduce(
        (sum, item) => sum + Number(item.harga_jual_eceran) * item.quantity,
        0,
    );
});

const diskon_global = computed(() => {
    return cart.value.reduce((sum, item) => sum + Number(item.diskon), 0);
});

const tax = computed(() => {
    if (customerType.value === "cafe") {
        return 0;
    }
    return cart.value.reduce((sum, item) => sum + item.ppn, 0);
});

const total = computed(() => {
    const totalBeforeVoucher = subtotal.value + tax.value;
    const voucherDiscount = appliedVoucher.value
        ? appliedVoucher.value.nominal
        : 0;
    const memberDiscount = appliedMember.value
        ? appliedMember.value.point * 50
        : 0;
    const discount = diskon_global.value;
    return Math.max(
        0,
        totalBeforeVoucher - voucherDiscount - memberDiscount - discount,
    );
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
    if (isProcessing.value) return;
    isProcessing.value = true;
    try {
        form.subtotal = subtotal;
        form.kode_voucher = appliedVoucher.value
            ? appliedVoucher.value.kode_voucher
            : null;
        form.kode_member = appliedMember.value
            ? appliedMember.value.kode_member
            : null;
        form.diskon_global = diskon_global;
        form.tax = tax;
        form.total = total;
        form.cash_received = cashReceived;
        form.change = change;
        form.applied_points = appliedMember.value
            ? appliedMember.value.point
            : null;
        form.details = cart;
        form.post("http://127.0.0.1:8000/pos", {
            onError: (error) => {
                console.error("Error processing payment:", error);
                alert(
                    error.message ||
                        "Error processing payment. Please try again.",
                );
            },
            onSuccess: () => {
                showPaymentSuccess.value = true;
                // resetTransaction();
            },
        });
    } catch (error) {
        console.error("Error processing payment:", error);
        alert(error.message || "Error processing payment. Please try again.");
    } finally {
        isProcessing.value = false;
    }
};

const resetTransaction = () => {
    cart.value = [];
    paymentMethod.value = "cash";
    customerType.value = "walk_in";
    cashReceived.value = 0;
    showPaymentSuccess.value = false;
    selectedProduct.value = null;
    appliedVoucher.value = null;
    appliedMember.value = null;
    cardNumber.value = null;
};

const printReceipt = () => {
    const receiptData = {
        items: cart.value,
        subtotal: subtotal.value,
        tax: tax.value,
        total: total.value,
        paymentMethod: paymentMethod.value,
        customerType: customerType.value,
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
