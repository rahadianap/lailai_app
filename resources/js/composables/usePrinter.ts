interface ReceiptData {
    items: Array<{
        name: string;
        quantity: number;
        price: number;
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
            receiptText += `${item.name}\n`;
            receiptText += `  ${item.quantity} x ${item.price.toFixed(2)} = ${item.total.toFixed(2)}\n`;
        });

        receiptText += "\n====================\n";
        receiptText += `Subtotal: ${data.subtotal.toFixed(2)}\n`;
        receiptText += `Tax: ${data.tax.toFixed(2)}\n`;
        receiptText += `Total: ${data.total.toFixed(2)}\n\n`;
        receiptText += `Payment Method: ${data.paymentMethod}\n`;

        if (data.paymentMethod === "cash") {
            receiptText += `Cash Received: ${data.cashReceived.toFixed(2)}\n`;
            receiptText += `Change: ${data.change.toFixed(2)}\n`;
        }

        receiptText += "\nThank you for your purchase!";

        console.log(receiptText);
    };

    return {
        printReceipt,
    };
}
