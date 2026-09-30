import React from 'react';

interface TaxCalculation {
    subtotal: number;
    taxAmount: number;
    discountAmount: number;
    total: number;
}

interface Props {
    items: Array<{
        quantity: number;
        unit_price: number;
        discount_amount: number;
        tax_amount: number;
        total_amount: number;
    }>;
}

export function useTaxCalculator(items: Props['items']): TaxCalculation {
    return React.useMemo(() => {
        const subtotal = items.reduce((sum, item) => {
            return sum + (item.quantity * item.unit_price);
        }, 0);
        
        const discountAmount = items.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
        const taxAmount = items.reduce((sum, item) => sum + (item.tax_amount || 0), 0);
        const total = items.reduce((sum, item) => sum + (item.total_amount || 0), 0);

        return {
            subtotal,
            taxAmount,
            discountAmount,
            total
        };
    }, [items]);
}

export function calculateLineItemAmounts(
    quantity: number,
    unitPrice: number,
    discountPercentage: number = 0,
    taxPercentage: number = 0,
    discountType: 'percentage' | 'fixed' = 'percentage',
    discountAmountVal: number = 0
) {
    const lineTotal = quantity * unitPrice;
    let discountAmount = 0;
    let effectivePct = 0;

    if (discountType === 'fixed') {
        discountAmount = Math.min(Math.max(Number(discountAmountVal) || 0, 0), lineTotal);
        effectivePct = lineTotal > 0 ? (discountAmount / lineTotal) * 100 : 0;
    } else {
        effectivePct = Math.min(Math.max(Number(discountPercentage) || 0, 0), 100);
        discountAmount = (lineTotal * effectivePct) / 100;
    }

    const afterDiscount = Math.max(0, lineTotal - discountAmount);
    const taxAmount = (afterDiscount * taxPercentage) / 100;
    const totalAmount = afterDiscount + taxAmount;

    return {
        discountAmount,
        discountPercentage: effectivePct,
        taxAmount,
        totalAmount
    };
}