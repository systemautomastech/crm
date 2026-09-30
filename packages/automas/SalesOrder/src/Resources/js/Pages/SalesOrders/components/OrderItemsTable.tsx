import React from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { InputError } from '@/components/ui/input-error';
import { Trash2 } from 'lucide-react';
import { formatCurrency, getCurrencySymbol, getCompanySetting } from '@/utils/helpers';
import { SalesOrderItem } from '../types';
import ProductSelector from './ProductSelector';
import RichTextEditor from '@/components/ui/rich-text-editor';
import { calculateLineItemAmounts } from './TaxCalculator';

interface OrderItemsTableProps {
    items: SalesOrderItem[];
    onChange: (items: SalesOrderItem[]) => void;
    errors: any;
    products: any[];
    warehouseId?: string | number | null;
    showAddButton?: boolean;
    discountType?: 'percentage' | 'fixed';
    onDiscountTypeChange?: (type: 'percentage' | 'fixed') => void;
}

export default function OrderItemsTable({
    items,
    onChange,
    errors = {},
    products = [],
    warehouseId,
    showAddButton = true,
    discountType: parentDiscountType,
    onDiscountTypeChange
}: OrderItemsTableProps) {
    const { t } = useTranslation();
    const pageProps = usePage().props;
    const currencySymbol = getCurrencySymbol(pageProps);
    const currencyCode = getCompanySetting('defaultCurrency', pageProps) || 'BDT';

    const [tableDiscountType, setTableDiscountType] = React.useState<'percentage' | 'fixed'>('percentage');
    const effectiveTableDiscountType = parentDiscountType || tableDiscountType;

    const updateItem = (index: number, field: keyof SalesOrderItem, value: any) => {
        const updatedItems = [...items];
        updatedItems[index] = { ...updatedItems[index], [field]: value };

        const item = updatedItems[index];

        if (field === 'unit_price' || field === 'quantity' || field === 'discount_percentage' || field === 'discount_amount' || field === 'tax_percentage') {
            item.quantity = Math.min(Math.max(Number(item.quantity) || 0, 0), 999999);
            item.unit_price = Number(item.unit_price) || 0;
            item.tax_percentage = Number(item.tax_percentage) || 0;
        }

        if (field === 'discount_amount') {
            item.discount_type = 'fixed';
        } else if (field === 'discount_percentage') {
            item.discount_type = 'percentage';
        } else if (!item.discount_type) {
            item.discount_type = effectiveTableDiscountType;
        }

        // If tax_percentage is 0 but product has taxes, recalculate tax_percentage and taxes array
        if ((item.tax_percentage === 0 || !item.taxes || item.taxes.length === 0) && item.product_id > 0) {
            const product = products.find(p => p.id === item.product_id);
            if (product?.taxes?.length) {
                const productTaxes = product.taxes.map((tax: any) => ({
                    tax_name: tax.tax_name || tax.name || 'Tax',
                    tax_rate: Number(tax.tax_rate ?? tax.rate ?? 0)
                }));
                item.tax_percentage = productTaxes.reduce((sum: number, tax: any) => sum + tax.tax_rate, 0);
                item.taxes = productTaxes;
            }
        }

        const curItem = updatedItems[index];
        const calculations = calculateLineItemAmounts(
            Number(curItem.quantity) || 1,
            Number(curItem.unit_price) || 0,
            Number(curItem.discount_percentage) || 0,
            Number(curItem.tax_percentage) || 0,
            curItem.discount_type || effectiveTableDiscountType,
            Number(curItem.discount_amount) || 0
        );

        curItem.discount_percentage = calculations.discountPercentage;
        curItem.discount_amount = calculations.discountAmount;
        curItem.tax_amount = calculations.taxAmount;
        curItem.total_amount = calculations.totalAmount;

        onChange(updatedItems);
    };

    const handleProductSelect = (index: number, productId: number, product?: any) => {
        const updatedItems = [...items];
        const selectedProd = product || products.find(p => p.id === productId);

        const totalTaxRate = selectedProd?.taxes?.reduce((sum: number, tax: any) => sum + Number(tax.rate ?? tax.tax_rate ?? 0), 0) || 0;
        const taxes = selectedProd?.taxes?.map((tax: any) => ({
            tax_name: tax.tax_name || tax.name || 'Tax',
            tax_rate: Number(tax.rate ?? tax.tax_rate ?? 0)
        })) || [];

        const defaultDesc = selectedProd?.long_description || selectedProd?.description || '';
        const unitVal = selectedProd?.unit_name || selectedProd?.unit || '';

        updatedItems[index] = {
            ...updatedItems[index],
            product_id: productId,
            product_name: selectedProd?.name || updatedItems[index]?.product_name || '',
            product: selectedProd || updatedItems[index]?.product,
            product_type: selectedProd?.type || updatedItems[index]?.product_type || 'product',
            unit_price: Number(selectedProd?.sale_price) || 0,
            unit: unitVal,
            tax_percentage: Number(totalTaxRate) || 0,
            taxes: taxes,
            description: defaultDesc,
            discount_type: updatedItems[index]?.discount_type || effectiveTableDiscountType,
        };

        const item = updatedItems[index];
        item.quantity = Number(item.quantity) || 1;

        const calculations = calculateLineItemAmounts(
            item.quantity,
            item.unit_price,
            item.discount_percentage || 0,
            item.tax_percentage,
            item.discount_type || 'percentage',
            item.discount_amount || 0
        );

        item.discount_percentage = calculations.discountPercentage;
        item.discount_amount = calculations.discountAmount;
        item.tax_amount = calculations.taxAmount;
        item.total_amount = calculations.totalAmount;

        onChange(updatedItems);
    };

    const removeItem = (index: number) => {
        const updatedItems = items.filter((_, i) => i !== index);
        onChange(updatedItems);
    };

    const addItem = () => {
        const newItem: SalesOrderItem = {
            product_id: 0,
            product_type: 'product',
            quantity: 1,
            unit_price: 0,
            discount_type: effectiveTableDiscountType,
            discount_percentage: 0,
            discount_amount: 0,
            tax_percentage: 0,
            tax_amount: 0,
            total_amount: 0,
            taxes: []
        };
        onChange([...items, newItem]);
    };

    const selectableTypes = Array.from(
        new Set(
            products
                .map((p) => p.type || 'product')
                .filter((t): t is string => Boolean(t && t.trim() !== ''))
        )
    );
    const availableTypes = selectableTypes.length > 0 ? selectableTypes : ['product', 'service'];

    const formatTypeName = (typeStr: string) => {
        if (!typeStr) return '';
        return t(typeStr.charAt(0).toUpperCase() + typeStr.slice(1).replace(/_/g, ' '));
    };

    return (
        <div className="space-y-4">
            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead>
                        <tr className="border-b border-border">
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-28">
                                {t('Type')}
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground min-w-[200px]">
                                {t('Items')} <span className="text-red-500">*</span>
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-28">
                                {t('Qty')} <span className="text-red-500">*</span>
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-32">
                                {t('Unit Price')} ({currencyCode}) <span className="text-red-500">*</span>
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-32">
                                <Select
                                    value={effectiveTableDiscountType}
                                    onValueChange={(val: 'percentage' | 'fixed') => {
                                        if (onDiscountTypeChange) {
                                            onDiscountTypeChange(val);
                                        } else {
                                            setTableDiscountType(val);
                                        }
                                        const updated = items.map((item) => {
                                            const lineTotal = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
                                            let discAmount = 0;
                                            let discPct = 0;

                                            if (val === 'percentage') {
                                                discAmount = Number(item.discount_amount) || 0;
                                                discPct = lineTotal > 0 ? (discAmount / lineTotal) * 100 : (Number(item.discount_percentage) || 0);
                                                discAmount = (lineTotal * discPct) / 100;
                                            } else {
                                                discAmount = Number(item.discount_amount) || ((lineTotal * (Number(item.discount_percentage) || 0)) / 100);
                                                discAmount = Math.min(Math.max(discAmount, 0), lineTotal);
                                                discPct = lineTotal > 0 ? (discAmount / lineTotal) * 100 : 0;
                                            }

                                            const afterDisc = Math.max(0, lineTotal - discAmount);
                                            const taxAmt = (afterDisc * (Number(item.tax_percentage) || 0)) / 100;
                                            return {
                                                ...item,
                                                discount_type: val,
                                                discount_percentage: Number(discPct.toFixed(4)),
                                                discount_amount: Math.round(discAmount * 100) / 100,
                                                tax_amount: Number(taxAmt.toFixed(4)),
                                                total_amount: Number((afterDisc + taxAmt).toFixed(4))
                                            };
                                        });
                                        onChange(updated);
                                    }}
                                >
                                    <SelectTrigger className="h-8 text-xs font-semibold border-none shadow-none p-0 focus:ring-0 text-foreground bg-transparent flex items-center gap-1 hover:text-primary transition-colors cursor-pointer w-auto [&>svg]:opacity-70">
                                        <span>
                                            {t('Discount')} ({effectiveTableDiscountType === 'percentage' ? '%' : currencyCode})
                                        </span>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="percentage" className="text-xs">
                                            {t('Percentage')} (%)
                                        </SelectItem>
                                        <SelectItem value="fixed" className="text-xs">
                                            {t('Fixed')} ({currencyCode})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-32">
                                {t('Tax')}
                            </th>
                            <th className="px-3 py-3 text-left text-xs font-semibold text-foreground w-28">
                                {t('Total')}
                            </th>
                            <th className="px-3 py-3 text-center text-xs font-semibold text-foreground w-16">
                                {t('Action')}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border">
                        {items.map((item, index) => {
                            const currentType = item.product_type && availableTypes.includes(item.product_type)
                                ? item.product_type
                                : (availableTypes.includes('product') ? 'product' : (availableTypes[0] || 'product'));

                            const filteredProducts = products.filter(p => {
                                const prodType = p.type || 'product';
                                return prodType.toLowerCase() === currentType.toLowerCase();
                            });

                            const product = products.find(p => p.id === item.product_id);
                            const unitDisplay = item.unit || product?.unit_name || (!isNaN(Number(product?.unit)) ? '' : (product?.unit || ''));

                            return (
                                <tr key={`item-${index}-${item.product_id}`} className="align-top">
                                    <td className="px-3 py-3 align-top">
                                        <Select
                                            value={currentType}
                                            onValueChange={(val) => {
                                                const newItems = [...items];
                                                newItems[index] = {
                                                    ...newItems[index],
                                                    product_type: val,
                                                    product_id: 0,
                                                    unit_price: 0,
                                                    description: '',
                                                    tax_percentage: 0,
                                                    taxes: [],
                                                    tax_amount: 0,
                                                    discount_amount: 0,
                                                    total_amount: 0,
                                                };
                                                onChange(newItems);
                                            }}
                                        >
                                            <SelectTrigger className="h-9 text-xs capitalize">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableTypes.map((typeOption) => (
                                                    <SelectItem key={typeOption} value={typeOption} className="capitalize">
                                                        {formatTypeName(typeOption)}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </td>
                                    <td className="px-3 py-3 align-top space-y-1.5 min-w-[200px]">
                                        <ProductSelector
                                            products={filteredProducts}
                                            value={item.product_id}
                                            productName={item.product_name || item.product?.name || product?.name}
                                            warehouseId={warehouseId}
                                            placeholder={t('Select {{type}}', { type: formatTypeName(currentType) })}
                                            onChange={(productId, prod) => handleProductSelect(index, productId, prod)}
                                        />
                                        <div className="mt-1">
                                            <RichTextEditor
                                                content={item.description || ''}
                                                onChange={(desc) => updateItem(index, 'description', desc)}
                                                placeholder={t('Item description...')}
                                                minimal={true}
                                            />
                                        </div>
                                        {errors[`items.${index}.product_id`] && (
                                            <div className="text-red-500 text-xs mt-1">{errors[`items.${index}.product_id`]}</div>
                                        )}
                                    </td>
                                    <td className="px-3 py-3 align-top">
                                        <div className="flex items-center gap-1">
                                            <Input
                                                type="number"
                                                value={item.quantity}
                                                onChange={(e) => {
                                                    let val = parseInt(e.target.value) || 0;
                                                    const maxStock = (product && product.type !== 'service' && product.stock_quantity !== undefined)
                                                        ? product.stock_quantity
                                                        : 999999;
                                                    
                                                    if (maxStock !== undefined && val > maxStock) {
                                                        val = maxStock;
                                                    }
                                                    updateItem(index, 'quantity', Math.min(Math.max(val, 0), maxStock));
                                                }}
                                                className="h-9 text-xs w-20"
                                                min="1"
                                                max={product && product.type !== 'service' && product.stock_quantity !== undefined ? product.stock_quantity : 999999}
                                                step="1"
                                                required
                                            />
                                            {unitDisplay ? (
                                                <span className="text-xs font-medium text-muted-foreground px-2 py-1 bg-muted/60 border border-border rounded h-9 inline-flex items-center min-w-[36px] justify-center whitespace-nowrap">
                                                    {unitDisplay}
                                                </span>
                                            ) : null}
                                        </div>
                                        {product && product.type !== 'service' && product.stock_quantity !== undefined && (
                                            <div className="mt-1">
                                                <span className={`text-[11px] font-medium ${product.stock_quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}`}>
                                                    {t('Stock')}: {product.stock_quantity} {unitDisplay}
                                                </span>
                                                {product.stock_quantity === 0 && (
                                                    <p className="text-[10px] text-rose-600 font-medium">{t('Out of stock')}</p>
                                                )}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-3 py-3 align-top">
                                        <Input
                                            type="number"
                                            value={item.unit_price}
                                            onChange={(e) => updateItem(index, 'unit_price', parseFloat(e.target.value) || 0)}
                                            className="h-9 text-xs w-full"
                                            min="0"
                                            step="0.01"
                                            required
                                        />
                                    </td>
                                    <td className="px-3 py-3 align-top">
                                        <div className="relative w-28">
                                            <Input
                                                type="number"
                                                value={(item.discount_type || effectiveTableDiscountType) === 'percentage'
                                                    ? (item.discount_percentage || 0)
                                                    : (item.discount_amount || 0)}
                                                onChange={(e) => {
                                                    const val = parseFloat(e.target.value) || 0;
                                                    if ((item.discount_type || effectiveTableDiscountType) === 'percentage') {
                                                        updateItem(index, 'discount_percentage', val);
                                                    } else {
                                                        updateItem(index, 'discount_amount', val);
                                                    }
                                                }}
                                                className="h-9 text-xs w-full pr-6 text-right font-medium"
                                                min="0"
                                                max={(item.discount_type || effectiveTableDiscountType) === 'percentage' ? 100 : undefined}
                                                step="0.01"
                                            />
                                            <span className="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">
                                                {(item.discount_type || effectiveTableDiscountType) === 'percentage' ? '%' : currencySymbol}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-3 py-3 align-top">
                                        <div className="flex flex-wrap items-center gap-1.5 min-h-[36px]">
                                            {item.taxes && item.taxes.length > 0 ? (
                                                item.taxes.map((tax, taxIndex) => (
                                                    <span key={taxIndex} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800 whitespace-nowrap">
                                                        {tax.tax_name} ({Number(tax.tax_rate ?? tax.rate ?? 0).toFixed(2)}%)
                                                    </span>
                                                ))
                                            ) : Number(item.tax_percentage) > 0 ? (
                                                <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800 whitespace-nowrap">
                                                    {t('Tax')} ({Number(item.tax_percentage).toFixed(2)}%)
                                                </span>
                                            ) : (
                                                <span className="text-xs text-muted-foreground italic px-1">{t('No tax')}</span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-3 py-3 align-top pt-4">
                                        <span className="text-xs font-semibold">
                                            {formatCurrency(item.total_amount, pageProps)}
                                        </span>
                                    </td>
                                    <td className="px-3 py-3 text-center align-top pt-3">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeItem(index)}
                                            className="text-red-600 hover:text-red-800 h-8 w-8 p-0"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {showAddButton && (
                <div className="flex justify-start">
                    <Button
                        type="button"
                        onClick={addItem}
                        variant="default"
                        size="sm"
                    >
                        + {t('Add Item')}
                    </Button>
                </div>
            )}
        </div>
    );
}
