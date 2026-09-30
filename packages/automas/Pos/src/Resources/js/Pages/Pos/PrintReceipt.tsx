import { formatCurrency, formatDate, formatTime } from '@/utils/helpers';

interface PrintReceiptProps {
    completedSale: any;
    globalSettings: any;
}

export const printReceipt = (completedSale: any, globalSettings: any) => {
    const receiptHTML = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>Receipt - ${completedSale.pos_number}</title>
        <style>
            @page {
                size: 80mm auto;
                margin: 0;
            }
            @media print {
                body { 
                    width: 80mm;
                    margin: 0;
                    padding: 0;
                }
            }
            body { 
                font-family: 'Courier New', monospace; 
                width: 80mm;
                margin: 0; 
                padding: 0;
                font-size: 12px;
                line-height: 1.3;
                color: #000;
            }
            .receipt { 
                width: 100%;
                text-align: center;
                padding: 5mm;
                margin: 0;
                box-sizing: border-box;
            }
            .header {
                margin-bottom: 8px;
            }
            .company-name {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 3px;
                letter-spacing: 0.5px;
            }
            .company-info {
                font-size: 11px;
                line-height: 1.4;
                margin-bottom: 5px;
            }
            .separator {
                border-top: 2px dashed #000;
                margin: 8px 0;
            }
            .receipt-info {
                text-align: left;
                margin-bottom: 6px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
                font-size: 12px;
            }
            .items-section {
                text-align: left;
                margin-bottom: 6px;
            }
            .item {
                margin-bottom: 10px;
                border-bottom: 1px dotted #000;
                padding-bottom: 5px;
            }
            .item-name {
                font-weight: bold;
                font-size: 13px;
                margin-bottom: 3px;
            }
            .item-details {
                font-size: 11px;
            }
            .item-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }
            .totals {
                text-align: left;
                margin-bottom: 6px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                font-size: 12px;
            }
            .final-total {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                font-size: 16px;
                border-top: 2px solid #000;
                padding-top: 5px;
                margin-top: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 10px;
                font-size: 11px;
            }
            .thank-you {
                font-weight: bold;
                margin-bottom: 3px;
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <div class="company-name">${globalSettings?.company_name || 'COMPANY NAME'}</div>
                <div class="company-info">
                    ${globalSettings?.company_address || 'Company Address'}<br>
                    ${globalSettings?.company_city || 'City'}, ${globalSettings?.company_state || 'State'}<br>
                    ${globalSettings?.company_country || 'Country'} - ${globalSettings?.company_zipcode || 'Zipcode'}
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span>Receipt No:</span>
                    <span>${completedSale.pos_number}</span>
                </div>
                <div class="info-row">
                    <span>Date:</span>
                    <span>${formatDate(new Date(), { companyAllSetting: globalSettings })}</span>
                </div>
                <div class="info-row">
                    <span>Time:</span>
                    <span>${formatTime(new Date().toLocaleTimeString(), { companyAllSetting: globalSettings })}</span>
                </div>
                <div class="info-row">
                    <span>Customer:</span>
                    <span>${completedSale.customer?.name || 'Walk-in Customer'}</span>
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="items-section">
                ${completedSale.items.map((item: any) => {
                    const itemSubtotal = item.price * item.quantity;
                    const itemDiscount = item.item_discount_amount || 0;
                    const discountedSubtotal = itemSubtotal - itemDiscount;
                    let itemTaxAmount = 0;
                    const itemTaxRate = item.taxes && item.taxes.length > 0 ? item.taxes.reduce((sum: number, tax: any) => sum + tax.rate, 0) : 0;
                    if (item.taxes && item.taxes.length > 0) {
                        item.taxes.forEach((tax: any) => {
                            itemTaxAmount += (discountedSubtotal * tax.rate) / 100;
                        });
                    }
                    return `
                        <div class="item">
                            <div class="item-name">${item.name}</div>
                            <div class="item-details">
                                <div class="item-row">
                                    <span>Qty:</span>
                                    <span>${item.quantity}</span>
                                </div>
                                <div class="item-row">
                                    <span>Price:</span>
                                    <span>${formatCurrency(item.price, { companyAllSetting: globalSettings })}</span>
                                </div>
                                <div class="item-row">
                                    <span>Subtotal:</span>
                                    <span>${formatCurrency(itemSubtotal, { companyAllSetting: globalSettings })}</span>
                                </div>
                                ${itemDiscount > 0 ? `
                                <div class="item-row" style="color: #16a34a;">
                                    <span>Discount:</span>
                                    <span>-${formatCurrency(itemDiscount, { companyAllSetting: globalSettings })}</span>
                                </div>
                                ` : ''}
                                <div class="item-row">
                                    <span>Tax (${itemTaxRate}%):</span>
                                    <span>${formatCurrency(itemTaxAmount, { companyAllSetting: globalSettings })}</span>
                                </div>
                                <div class="item-row" style="font-weight: bold;">
                                    <span>Total:</span>
                                    <span>${formatCurrency(discountedSubtotal + itemTaxAmount, { companyAllSetting: globalSettings })}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
            
            <div class="totals">
                <div class="final-total">
                    <span>TOTAL:</span>
                    <span>${formatCurrency(completedSale.total, { companyAllSetting: globalSettings })}</span>
                </div>
            </div>
            
            <div class="footer">
                <div class="thank-you">*** THANK YOU ***</div>
                <div>Visit Again!</div>
            </div>
        </div>
    </body>
    </html>
    `;
    
    const printFrame = document.createElement('iframe');
    printFrame.style.display = 'none';
    document.body.appendChild(printFrame);
    
    const frameDoc = printFrame.contentDocument || printFrame.contentWindow?.document;
    if (frameDoc) {
        frameDoc.write(receiptHTML);
        frameDoc.close();
        
        printFrame.contentWindow?.focus();
        printFrame.contentWindow?.print();
        
        setTimeout(() => {
            document.body.removeChild(printFrame);
        }, 1000);
    }
};