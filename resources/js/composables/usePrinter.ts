interface ReceiptData {
    items: Array<{
        nama_barang: string;
        quantity: number;
        harga_jual_eceran: number;
        total: number;
    }>;
    subtotal: number;
    tax: number;
    total: number;
    paymentMethod: string;
    cashReceived: number;
    change: number;
}

export function usePrinter() {
    const printReceipt = (data: ReceiptData) => {
        // In a real application, you would integrate with a receipt printer API
        // For this example, we'll simulate printing by logging to console
        console.log("Printing receipt:", data);

        // Format the receipt
        let receiptText = "POS RECEIPT\n";
        receiptText += "====================\n\n";

        data.items.forEach((item) => {
            receiptText += `${item.nama_barang}\n`;
            receiptText += `  ${item.quantity} x ${item.harga_jual_eceran} = ${item.total}\n`;
        });

        receiptText += "\n====================\n";
        receiptText += `Subtotal: ${data.subtotal}\n`;
        receiptText += `Tax: ${data.tax}\n`;
        receiptText += `Total: ${data.total}\n\n`;
        receiptText += `Payment Method: ${data.paymentMethod}\n`;

        if (data.paymentMethod === "cash") {
            receiptText += `Cash Received: ${data.cashReceived}\n`;
            receiptText += `Change: ${data.change}\n`;
        }

        receiptText += "\nThank you for your purchase!";

        console.log(receiptText);
    };

    return {
        printReceipt,
    };
}
