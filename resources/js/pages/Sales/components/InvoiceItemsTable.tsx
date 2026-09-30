import React from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { SalesInvoiceItem } from '../types';
import ProductSelector from './ProductSelector';
import { calculateLineItemAmounts } from './TaxCalculator';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Plus, RefreshCw, Trash2 } from 'lucide-react';
import { formatCurrency, getCurrencySymbol, getCompanySetting } from '@/utils/helpers';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import RichTextEditor from '@/components/ui/rich-text-editor';

interface Props {
    items: SalesInvoiceItem[];
    onChange: (items: SalesInvoiceItem[]) => void;
    errors: any;
    products?: Array<{
        id: number;
        name: string;
        type?: string;
        description?: string;
        long_description?: string;
        sale_price: number;
        unit?: string;
        unit_name?: string;
        stock_quantity?: number;
        warehouse_stocks?: Record<string, number>;
        taxes?: Array<{ id: number; tax_name: string; rate: number }>;
    }>;
    showAddButton?: boolean;
    onRefresh?: () => void | Promise<void>;
    isRefreshing?: boolean;
    warehouseId?: string | number;
    discountType?: 'percentage' | 'fixed';
    onDiscountTypeChange?: (type: 'percentage' | 'fixed') => void;
}

export default function InvoiceItemsTable({
    items,
    onChange,
    errors,
    products = [],
    showAddButton = true,
    onRefresh,
    isRefreshing = false,
    warehouseId,
    discountType: parentDiscountType,
    onDiscountTypeChange
}: Props) {
    const { t } = useTranslation();
    const pageProps = usePage().props;
    const currencySymbol = getCurrencySymbol(pageProps);
    const currencyCode = getCompanySetting('defaultCurrency', pageProps) || 'BDT';

    const [tableDiscountType, setTableDiscountType] = React.useState<'percentage' | 'fixed'>('percentage');
    const effectiveTableDiscountType = parentDiscountType || tableDiscountType;

    const addItem = () => {
        const newItem: SalesInvoiceItem = {
            product_id: 0,
            product_type: 'product',
            description: '',
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

    const removeItem = (index: number) => {
        const newItems = items.filter((_, i) => i !== index);
        onChange(newItems);
    };

    const updateItem = (index: number, field: keyof SalesInvoiceItem, value: any) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], [field]: value };

        const item = newItems[index];

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

        // If tax_percentage is 0 but product has taxes, recalculate tax_percentage
        if (item.tax_percentage === 0 && item.product_id > 0) {
            const product = products.find(p => p.id === item.product_id);
            if (product?.taxes?.length) {
                item.tax_percentage = product.taxes.reduce((sum, tax) => sum + tax.rate, 0);
            }
        }

        const calculations = calculateLineItemAmounts(
            item.quantity,
            item.unit_price,
            item.discount_percentage,
            item.tax_percentage,
            item.discount_type || 'percentage',
            item.discount_amount
        );

        item.discount_percentage = calculations.discountPercentage;
        item.discount_amount = calculations.discountAmount;
        item.tax_amount = calculations.taxAmount;
        item.total_amount = calculations.totalAmount;

        onChange(newItems);
    };

    const handleProductSelect = (index: number, productId: number, product?: any) => {
        const newItems = [...items];
        const totalTaxRate = product?.taxes?.reduce((sum: number, tax: any) => sum + Number(tax.rate), 0) || 0;
        const taxes = product?.taxes?.map((tax: any) => ({
            tax_name: tax.tax_name,
            tax_rate: tax.rate
        })) || [];

        const defaultDesc = product?.long_description || product?.description || '';
        const detectedType = product?.type || newItems[index]?.product_type || 'product';

        newItems[index] = {
            ...newItems[index],
            product_id: productId,
            product_type: detectedType,
            unit_price: Number(product?.sale_price) || 0,
            tax_percentage: Number(totalTaxRate) || 0,
            taxes: taxes,
            description: defaultDesc,
            discount_type: newItems[index]?.discount_type || effectiveTableDiscountType,
        };

        const item = newItems[index];
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

        onChange(newItems);
    };

    return (
        <div className="space-y-4">
            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead>
                        <tr className="border-b border-border">
                            <th className="px-3 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Type')}
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Product')} <span className="text-red-500">*</span>
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Qty')} <span className="text-red-500">*</span>
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Unit Price')} ({currencyCode}) <span className="text-red-500">*</span>
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
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
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Tax')}
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Total')}
                            </th>
                            <th className="px-4 py-3 text-center text-sm font-semibold text-foreground">
                                {t('Action')}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border">
                        {items.map((item, index) => {
                            // Ensure standard types ('product', 'service') are always selectable, plus any custom types from products or item
                            const defaultTypes = ['product', 'service'];
                            const availableTypesFromProducts = products
                                .map((p) => p.type || 'product')
                                .filter((t): t is string => Boolean(t && t.trim() !== ''));

                            const selectableTypes = Array.from(
                                new Set([
                                    ...defaultTypes,
                                    ...availableTypesFromProducts,
                                    ...(item.product?.type ? [item.product.type] : []),
                                    ...(item.product_type ? [item.product_type] : []),
                                ])
                            );

                            const selectedProd = products.find(p => p.id === item.product_id);
                            const currentType = selectedProd?.type || item.product?.type || item.product_type || (selectableTypes.includes('product') ? 'product' : (selectableTypes[0] || 'product'));

                            // Filter products by current type, but always retain the currently selected product so it never vanishes
                            const filteredProducts = products.filter(p => {
                                const prodType = p.type || 'product';
                                return prodType.toLowerCase() === currentType.toLowerCase() || p.id === item.product_id;
                            });

                            const formatTypeName = (typeStr: string) => {
                                if (!typeStr) return '';
                                return t(typeStr.charAt(0).toUpperCase() + typeStr.slice(1).replace(/_/g, ' '));
                            };

                            const product = products.find(p => p.id === item.product_id);
                            const unitDisplay = product?.unit_name || (!isNaN(Number(product?.unit)) ? '' : (product?.unit || ''));

                            return (
                                <tr key={index} className="align-top">
                                    <td className="px-3 py-4">
                                        <div className="space-y-1.5">
                                            {selectableTypes.length > 0 ? (
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
                                                    <SelectTrigger className="w-24 text-xs capitalize">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {selectableTypes.map((typeOption) => (
                                                            <SelectItem key={typeOption} value={typeOption} className="capitalize">
                                                                {formatTypeName(typeOption)}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <div className="w-24 h-9 px-2 text-xs text-muted-foreground bg-muted/40 border border-dashed border-border rounded flex items-center justify-center">
                                                    {t('No types')}
                                                </div>
                                            )}

                                            <div className="flex flex-col gap-1">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => {
                                                        try {
                                                            window.open(route('product-service.items.create'), '_blank');
                                                        } catch (e) {
                                                            window.open('/product-service/items/create', '_blank');
                                                        }
                                                    }}
                                                    className="h-6 px-1.5 text-[10px] text-primary hover:text-primary gap-1 border-dashed w-24 justify-start"
                                                >
                                                    <Plus className="h-3 w-3 shrink-0" />
                                                    <span className="truncate">{t('Add {{type}}', { type: formatTypeName(currentType) })}</span>
                                                </Button>

                                                {onRefresh && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => onRefresh()}
                                                        disabled={isRefreshing}
                                                        className="h-6 px-1.5 text-[10px] text-muted-foreground hover:text-foreground gap-1 w-24 justify-center"
                                                        title={t('Refresh items list')}
                                                    >
                                                        <RefreshCw className={`h-2 w-2 shrink-0 ${isRefreshing ? 'animate-spin text-primary' : ''}`} style={{ height: '10px', width: '10px' }} />
                                                        <span className={isRefreshing ? 'text-primary font-medium' : ''}>{t('Refresh')}</span>
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-4 min-w-[280px]">
                                        <ProductSelector
                                            products={filteredProducts}
                                            value={item.product_id}
                                            onChange={(productId, prod) => handleProductSelect(index, productId, prod)}
                                            placeholder={t('Select {{type}}', { type: formatTypeName(currentType) })}
                                            warehouseId={warehouseId}
                                            isRefreshing={isRefreshing}
                                        />
                                        <InputError message={errors[`items.${index}.product_id`]} />

                                        {item.product_id > 0 && (
                                            <div className="mt-2 space-y-1">
                                                <RichTextEditor
                                                    content={item.description || ''}
                                                    onChange={(desc) => {
                                                        const newItems = [...items];
                                                        newItems[index] = {
                                                            ...newItems[index],
                                                            description: desc,
                                                        };
                                                        onChange(newItems);
                                                    }}
                                                    placeholder={t('Enter or edit product description...')}
                                                    minimal={true}
                                                />
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-4">
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
                                                className="w-20 text-sm"
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
                                        <InputError message={errors[`items.${index}.quantity`]} />
                                    </td>
                                    <td className="px-4 py-4">
                                        <Input
                                            type="number"
                                            value={item.unit_price}
                                            onChange={(e) => updateItem(index, 'unit_price', parseFloat(e.target.value) || 0)}
                                            className="w-24 text-sm"
                                            min="0"
                                            step="0.01"
                                            required
                                        />
                                        <InputError message={errors[`items.${index}.unit_price`]} />
                                    </td>
                                    <td className="px-4 py-4">
                                        <div className="relative w-24">
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
                                                className="w-24 text-sm pr-6 text-right font-medium"
                                                min="0"
                                                max={(item.discount_type || effectiveTableDiscountType) === 'percentage' ? 100 : undefined}
                                                step="0.01"
                                            />
                                            <span className="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">
                                                {(item.discount_type || effectiveTableDiscountType) === 'percentage' ? '%' : '৳'}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-4">
                                        <div className="flex flex-wrap items-center gap-1.5 min-h-[32px]">
                                            {item.taxes && item.taxes.length > 0 ? (
                                                item.taxes.map((tax, taxIndex) => (
                                                    <span key={taxIndex} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800 whitespace-nowrap">
                                                        {tax.tax_name} ({tax.tax_rate}%)
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
                                    <td className="px-4 py-4">
                                        <span className="text-sm font-medium">
                                            {formatCurrency(item.total_amount)}
                                        </span>
                                    </td>
                                    <td className="px-4 py-4 text-center">
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

            <div className="pt-4 border-t border-border flex items-center justify-start px-3">
                {showAddButton && (
                    <Button
                        type="button"
                        onClick={addItem}
                        variant="default"
                        size="sm"
                        className="gap-1"
                    >
                        + {t('Add Item')}
                    </Button>
                )}
            </div>

            <InputError message={errors.items} />
        </div>
    );
}