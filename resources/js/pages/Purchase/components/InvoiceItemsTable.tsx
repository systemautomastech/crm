import React from 'react';
import { useTranslation } from 'react-i18next';
import { PurchaseInvoiceItem } from '../types';
import ProductSelector from './ProductSelector';
import { calculateLineItemAmounts } from './TaxCalculator';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Plus, RefreshCw, Trash2 } from 'lucide-react';
import { formatCurrency } from '@/utils/helpers';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import RichTextEditor from '@/components/ui/rich-text-editor';

interface Props {
    items: PurchaseInvoiceItem[];
    onChange: (items: PurchaseInvoiceItem[]) => void;
    errors: any;
    products?: Array<{
        id: number;
        name: string;
        type?: string;
        description?: string;
        long_description?: string;
        purchase_price: number;
        unit?: string;
        unit_name?: string;
        taxes?: Array<{ id: number; tax_name: string; rate: number }>;
    }>;
    showAddButton?: boolean;
    onRefresh?: () => void | Promise<void>;
    isRefreshing?: boolean;
    warehouseId?: string | number;
}

export default function InvoiceItemsTable({ items, onChange, errors, products = [], showAddButton = true, onRefresh, isRefreshing = false, warehouseId }: Props) {
    const { t } = useTranslation();

    const addItem = () => {
        const newItem: PurchaseInvoiceItem = {
            product_id: 0,
            product_type: 'product',
            description: '',
            quantity: 1,
            unit_price: 0,
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

    const updateItem = (index: number, field: keyof PurchaseInvoiceItem, value: any) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], [field]: value };

        const item = newItems[index];

        if (field === 'unit_price' || field === 'quantity' || field === 'discount_percentage' || field === 'tax_percentage') {
            item.quantity = Math.min(Math.max(Number(item.quantity) || 0, 0), 999999);
            item.unit_price = Number(item.unit_price) || 0;
            item.discount_percentage = Number(item.discount_percentage) || 0;
            item.tax_percentage = Number(item.tax_percentage) || 0;
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
            item.tax_percentage
        );

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

        const defaultDesc = product?.long_description || '';

        newItems[index] = {
            ...newItems[index],
            product_id: productId,
            unit_price: Number(product?.purchase_price) || 0,
            tax_percentage: Number(totalTaxRate) || 0,
            taxes: taxes,
            description: defaultDesc,
        };

        const item = newItems[index];
        item.quantity = Number(item.quantity) || 1;
        item.discount_percentage = Number(item.discount_percentage) || 0;

        const calculations = calculateLineItemAmounts(
            item.quantity,
            item.unit_price,
            item.discount_percentage,
            item.tax_percentage
        );

        item.discount_amount = Number(calculations.discountAmount) || 0;
        item.tax_amount = Number(calculations.taxAmount) || 0;
        item.total_amount = Number(calculations.totalAmount) || 0;

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
                                {t('Unit Price')} <span className="text-red-500">*</span>
                            </th>
                            <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                {t('Discount')} %
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
                            // Extract distinct types ONLY from existing products that actually exist in the list
                            const selectableTypes = Array.from(
                                new Set(
                                    products
                                        .map((p) => p.type || 'product')
                                        .filter((t): t is string => Boolean(t && t.trim() !== ''))
                                )
                            );

                            const currentType = item.product_type && selectableTypes.includes(item.product_type)
                                ? item.product_type
                                : (selectableTypes[0] || 'product');

                            // Filter products strictly by the available current type
                            const filteredProducts = products.filter(p => {
                                const prodType = p.type || 'product';
                                return prodType.toLowerCase() === currentType.toLowerCase();
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
                                                    const val = parseInt(e.target.value) || 0;
                                                    updateItem(index, 'quantity', Math.min(Math.max(val, 0), 999999));
                                                }}
                                                className="w-20 text-sm"
                                                min="1"
                                                max="999999"
                                                step="1"
                                                required
                                            />
                                            {unitDisplay ? (
                                                <span className="text-xs font-medium text-muted-foreground px-2 py-1 bg-muted/60 border border-border rounded h-9 inline-flex items-center min-w-[36px] justify-center whitespace-nowrap">
                                                    {unitDisplay}
                                                </span>
                                            ) : null}
                                        </div>
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
                                        <Input
                                            type="number"
                                            value={item.discount_percentage}
                                            onChange={(e) => updateItem(index, 'discount_percentage', parseFloat(e.target.value) || 0)}
                                            className="w-20 text-sm"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                        />
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

            <InputError message={errors.items} />
        </div>
    );
}