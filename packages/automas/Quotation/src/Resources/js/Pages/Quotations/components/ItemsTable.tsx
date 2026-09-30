import React from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { QuotationItem } from '../types';
import ProductSelector from './ProductSelector';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Plus, RefreshCw, Trash2 } from 'lucide-react';
import { formatCurrency, getCompanySetting } from '@/utils/helpers';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import RichTextEditor from '@/components/ui/rich-text-editor';

interface Props {
    items: QuotationItem[];
    onChange: (items: QuotationItem[]) => void;
    errors?: any;
    products?: Array<{ id: number; name: string; type?: string; description?: string; long_description?: string; sale_price: number; unit?: string; unit_name?: string; stock_quantity?: number; taxes?: Array<{ id: number; tax_name: string; rate: number }> }>;
    showAddButton?: boolean;
    invoiceType?: string;
    warehouseId?: string | number | null;
    onRefresh?: () => void | Promise<void>;
    isRefreshing?: boolean;
    isTaxEnabled?: boolean;
    defaultSection?: string;
    discountType?: 'percentage' | 'fixed';
    discountValue?: number;
    onDiscountTypeChange?: (type: 'percentage' | 'fixed') => void;
    onDiscountValueChange?: (value: number) => void;
}

export default function ItemsTable({
    items,
    onChange,
    errors = {},
    products = [],
    showAddButton = true,
    invoiceType = 'product',
    warehouseId,
    onRefresh,
    isRefreshing = false,
    isTaxEnabled = true,
    defaultSection = 'otc',
    discountType = 'percentage',
    discountValue = 0,
    onDiscountTypeChange,
    onDiscountValueChange,
}: Props) {
    const { t } = useTranslation();
    const pageProps = usePage().props;
    const currencyCode = getCompanySetting('defaultCurrency', pageProps) || 'BDT';

    const addItem = () => {
        const newItem: QuotationItem = {
            product_id: 0,
            section: defaultSection,
            product_type: invoiceType || 'product',
            description: '',
            quantity: 1,
            unit_price: 0,
            discount_type: discountType,
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

    const updateItem = (index: number, field: keyof QuotationItem, value: any) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], [field]: value };

        const item = newItems[index];

        if (field === 'tax_percentage' && !isTaxEnabled) {
            item.tax_percentage = 0;
            item.taxes = [];
        }

        item.quantity = Math.min(Math.max(Number(item.quantity) || 0, 0), 999999);
        item.unit_price = Number(item.unit_price) || 0;
        item.tax_percentage = isTaxEnabled ? (Number(item.tax_percentage) || 0) : 0;

        if (field === 'discount_amount') {
            item.discount_type = 'fixed';
        } else if (field === 'discount_percentage') {
            item.discount_type = 'percentage';
        } else if (!item.discount_type) {
            item.discount_type = discountType || 'percentage';
        }

        const effectiveDiscType = item.discount_type;

        const lineTotal = item.quantity * item.unit_price;
        let discountAmount = 0;
        let discountPct = 0;

        if (effectiveDiscType === 'percentage') {
            discountPct = Math.min(Math.max(Number(item.discount_percentage) || 0, 0), 100);
            discountAmount = (lineTotal * discountPct) / 100;
            item.discount_percentage = discountPct;
            item.discount_amount = Number(discountAmount.toFixed(2));
        } else {
            discountAmount = Math.min(Math.max(Number(item.discount_amount) || 0, 0), lineTotal);
            discountPct = lineTotal > 0 ? (discountAmount / lineTotal) * 100 : 0;
            item.discount_percentage = Number(discountPct.toFixed(4));
            item.discount_amount = discountAmount;
        }

        const afterDiscount = Math.max(0, lineTotal - discountAmount);
        const taxAmount = isTaxEnabled ? (afterDiscount * (Number(item.tax_percentage) || 0)) / 100 : 0;

        item.tax_amount = Number(taxAmount.toFixed(4));
        item.total_amount = Number((afterDiscount + taxAmount).toFixed(4));

        onChange(newItems);
    };

    const handleProductSelect = (index: number, productId: number, product?: any) => {
        const newItems = [...items];
        const totalTaxRate = (isTaxEnabled && product?.taxes) ? (product.taxes.reduce((sum: number, tax: any) => sum + Number(tax.rate), 0) || 0) : 0;
        const taxes = (isTaxEnabled && product?.taxes) ? (product.taxes.map((tax: any) => ({
            tax_name: tax.tax_name,
            tax_rate: tax.rate
        })) || []) : [];

        const defaultDesc = product?.long_description || product?.description || '';
        const effectiveDiscType = newItems[index].discount_type || discountType;

        newItems[index] = {
            ...newItems[index],
            product_id: productId,
            product_type: product?.type || newItems[index].product_type || 'product',
            unit_price: Number(product?.sale_price) || 0,
            tax_percentage: Number(totalTaxRate) || 0,
            taxes: taxes,
            description: defaultDesc,
            discount_type: effectiveDiscType,
            discount_percentage: newItems[index].discount_percentage || 0,
            discount_amount: newItems[index].discount_amount || 0,
        };

        const item = newItems[index];
        item.quantity = Number(item.quantity) || 1;

        const lineTotal = item.quantity * item.unit_price;
        let discountAmount = 0;
        let discountPct = 0;

        if (effectiveDiscType === 'percentage') {
            discountPct = Math.min(Math.max(Number(item.discount_percentage) || 0, 0), 100);
            discountAmount = (lineTotal * discountPct) / 100;
            item.discount_percentage = discountPct;
            item.discount_amount = Number(discountAmount.toFixed(2));
        } else {
            discountAmount = Math.min(Math.max(Number(item.discount_amount) || 0, 0), lineTotal);
            discountPct = lineTotal > 0 ? (discountAmount / lineTotal) * 100 : 0;
            item.discount_percentage = Number(discountPct.toFixed(4));
            item.discount_amount = discountAmount;
        }

        const afterDiscount = Math.max(0, lineTotal - discountAmount);
        const taxAmount = isTaxEnabled ? (afterDiscount * (Number(item.tax_percentage) || 0)) / 100 : 0;

        item.tax_amount = Number(taxAmount.toFixed(4));
        item.total_amount = Number((afterDiscount + taxAmount).toFixed(4));

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
                                {t('Items')} <span className="text-red-500">*</span>
                            </th>
                            {invoiceType === 'product' && (
                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                    {t('Qty')} <span className="text-red-500">*</span>
                                </th>
                            )}
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Unit Price')} ({currencyCode}) <span className="text-red-500">*</span>
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                <Select
                                    value={discountType}
                                    onValueChange={(val: 'percentage' | 'fixed') => {
                                        if (onDiscountTypeChange) {
                                            onDiscountTypeChange(val);
                                        }
                                        const updated = items.map(item => {
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
                                            const taxAmt = isTaxEnabled ? (afterDisc * (Number(item.tax_percentage) || 0)) / 100 : 0;
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
                                            {t('Discount')} ({discountType === 'percentage' ? '%' : currencyCode})
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
                            const matchedProduct = products.find(p => String(p.id) === String(item.product_id));
                            const availableTypes = Array.from(
                                new Set(
                                    products
                                        .map((p) => p.type)
                                        .filter((t): t is string => Boolean(t && t.trim() !== ''))
                                )
                            ).filter(Boolean);

                            const currentType = (matchedProduct?.type) || item.product_type || (item as any).item_type || (item.product as any)?.type || (selectableTypesIncludes(availableTypes, invoiceType) ? invoiceType : 'product');

                            const selectableTypes = Array.from(
                                new Set(['product', 'service', ...availableTypes, currentType].filter(Boolean))
                            );

                            function selectableTypesIncludes(types: string[], type?: string) {
                                return type && types.includes(type);
                            }

                            const filteredProducts = products.filter(p => (!p.type || p.type === currentType) || (matchedProduct && String(p.id) === String(matchedProduct.id)));

                            const formatTypeName = (typeStr: string) => {
                                if (!typeStr) return '';
                                return t(typeStr.charAt(0).toUpperCase() + typeStr.slice(1).replace(/_/g, ' '));
                            };

                            return (
                                <tr key={index} className="align-top">
                                    <td className="px-3 py-4">
                                        <div className="space-y-1.5">
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
                                                        onClick={onRefresh}
                                                        disabled={isRefreshing}
                                                        className="h-6 px-1.5 text-[10px] text-muted-foreground hover:text-foreground gap-1 w-24 justify-start"
                                                    >
                                                        <RefreshCw className={`h-3 w-3 shrink-0 ${isRefreshing ? 'animate-spin' : ''}`} />
                                                        <span className="truncate">{isRefreshing ? t('Refreshing...') : t('Refresh')}</span>
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-4 min-w-[280px]">
                                        <ProductSelector
                                            products={filteredProducts}
                                            selectedProductId={item.product_id}
                                            warehouseId={currentType === 'product' ? warehouseId : undefined}
                                            onSelect={(prodId, prod) => handleProductSelect(index, prodId, prod)}
                                            disabled={!warehouseId && currentType === 'product'}
                                            isRefreshing={isRefreshing}
                                            placeholder={!warehouseId && currentType === 'product'
                                                ? t('Select Warehouse First')
                                                : t('Select {{type}}', { type: formatTypeName(currentType) })}
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
                                    {invoiceType === 'product' && (
                                        <td className="px-4 py-4">
                                            <Input
                                                type="number"
                                                value={item.quantity}
                                                onChange={(e) => updateItem(index, 'quantity', e.target.value)}
                                                className="w-20 text-sm"
                                                min="1"
                                                required
                                            />
                                            <InputError message={errors[`items.${index}.quantity`]} />
                                        </td>
                                    )}
                                    <td className="px-4 py-4">
                                        <Input
                                            type="number"
                                            value={item.unit_price}
                                            onChange={(e) => updateItem(index, 'unit_price', e.target.value)}
                                            className="w-28 text-sm"
                                            min="0"
                                            step="0.01"
                                            required
                                        />
                                        <InputError message={errors[`items.${index}.unit_price`]} />
                                    </td>
                                    <td className="px-4 py-4">
                                        <div className="relative w-20">
                                            <Input
                                                type="number"
                                                value={(item.discount_type || discountType) === 'percentage'
                                                    ? (item.discount_percentage || 0)
                                                    : (item.discount_amount || 0)}
                                                onChange={(e) => {
                                                    const val = parseFloat(e.target.value) || 0;
                                                    if ((item.discount_type || discountType) === 'percentage') {
                                                        updateItem(index, 'discount_percentage', val);
                                                    } else {
                                                        updateItem(index, 'discount_amount', val);
                                                    }
                                                }}
                                                className="w-20 text-sm pr-6 text-right font-medium"
                                                min="0"
                                                max={(item.discount_type || discountType) === 'percentage' ? 100 : undefined}
                                                step="0.01"
                                            />
                                            <span className="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">
                                                {(item.discount_type || discountType) === 'percentage' ? '%' : '৳'}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-4">
                                        <div className="flex flex-wrap items-center gap-1.5 min-h-[32px]">
                                            {!isTaxEnabled ? (
                                                <span className="text-xs text-muted-foreground italic px-1">{t('Tax disabled')}</span>
                                            ) : item.taxes && item.taxes.length > 0 ? (
                                                item.taxes.map((tax, taxIndex) => (
                                                    <span
                                                        key={taxIndex}
                                                        className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary border border-primary/20"
                                                    >
                                                        {tax.tax_name} ({tax.tax_rate}%)
                                                    </span>
                                                ))
                                            ) : (
                                                <span className="text-xs text-muted-foreground italic px-1">{t('No tax')}</span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-4 py-4 font-semibold text-sm">
                                        {formatCurrency(item.total_amount || 0)}
                                    </td>
                                    <td className="px-4 py-4 text-center">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => removeItem(index)}
                                            className="text-red-500 hover:text-red-700 hover:bg-red-50"
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

            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-start gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                <div>
                    {showAddButton && (
                        <Button
                            type="button"
                            onClick={addItem}
                            size="sm"
                            className="gap-2"
                        >
                            <Plus className="h-4 w-4" /> {t('Add Item')}
                        </Button>
                    )}
                </div>

                {items.length > 0 && (() => {
                    const sectionSubTotal = items.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.unit_price) || 0)), 0);
                    const sectionDiscountTotal = items.reduce((sum, item) => sum + (Number(item.discount_amount) || 0), 0);
                    const sectionTaxTotal = isTaxEnabled ? items.reduce((sum, item) => sum + (Number(item.tax_amount) || 0), 0) : 0;
                    const sectionGrandTotal = items.reduce((sum, item) => sum + (Number(item.total_amount) || 0), 0);

                    return (
                        <div className="w-full sm:w-80 bg-slate-50 dark:bg-slate-900/60 rounded-lg p-4 border border-slate-200 dark:border-slate-800 text-xs space-y-3">
                            <div className="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span className="font-medium">{t('Sub Total (৳)')}</span>
                                <span className="font-semibold text-slate-900 dark:text-slate-100 text-sm">{formatCurrency(sectionSubTotal)}</span>
                            </div>

                            {sectionDiscountTotal > 0 && (
                                <div className="flex justify-between items-center text-rose-600 dark:text-rose-400 font-medium">
                                    <span>{t('Total Discount')}</span>
                                    <span>(-) {formatCurrency(sectionDiscountTotal)}</span>
                                </div>
                            )}

                            {isTaxEnabled && sectionTaxTotal > 0 && (
                                <div className="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span>{t('VAT/Tax')}</span>
                                    <span className="font-medium text-slate-900 dark:text-slate-100">(+) {formatCurrency(sectionTaxTotal)}</span>
                                </div>
                            )}

                            <div className="pt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center font-bold text-slate-900 dark:text-slate-100 text-sm">
                                <span>{t('Grand Total (৳)')}</span>
                                <span className="text-primary text-base">{formatCurrency(sectionGrandTotal)}</span>
                            </div>
                        </div>
                    );
                })()}
            </div>

            <InputError message={errors.items} />
        </div>
    );
}