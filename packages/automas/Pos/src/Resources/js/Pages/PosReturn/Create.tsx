import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { CalendarDays, Package, RotateCcw, Trash2 } from 'lucide-react';
import { formatCurrency } from '@/utils/helpers';
import { Pos, PosItem } from './types';

interface CreateProps {
    posSales: Pos[];
    warehouses: Array<{id: number; name: string}>;
    [key: string]: any;
}

interface ReturnItem {
    product_id: number;
    original_pos_item_id: number;
    return_quantity: number;
    unit_price: number;
    reason: string;
    total_amount: number;
    taxes?: Array<{
        id: number;
        name: string;
        rate: number;
    }>;
    discountAmount: number;
    return_discount: number;
}

export default function Create() {
    const { t } = useTranslation();
    const { posSales, warehouses } = usePage<CreateProps>().props;

    useFlashMessages();

    const [selectedPos, setSelectedPos] = useState<Pos | null>(null);
    const [returnItems, setReturnItems] = useState<ReturnItem[]>([]);

    const { data, setData, post, processing, errors } = useForm({
        return_date: new Date().toISOString().split('T')[0],
        customer_id: '',
        warehouse_id: '',
        original_pos_id: '',
        reason: 'defective',
        notes: '',
        items: [] as any[]
    });

    const handlePosSelect = (posId: string) => {
        const pos = posSales.find(p => p.id.toString() === posId);
        if (pos) {
            setSelectedPos(pos);
            setData({
                ...data,
                customer_id: pos.customer?.id?.toString() ?? '',
                warehouse_id: pos.warehouse?.id?.toString() ?? '',
                original_pos_id: posId
            });
            setReturnItems([]);
        }
    };

    const getTaxAmount = (item: PosItem, afterDiscount: number): number => {
        if (!item.taxes || item.taxes.length === 0) return 0;
        let totalTax = 0;
        item.taxes.forEach(tax => {
            totalTax += (afterDiscount * tax.rate) / 100;
        });
        return totalTax;
    };

    const getTaxDisplay = (item: PosItem): string => {
        if (!item.taxes || item.taxes.length === 0) return '-';
        return item.taxes.map(tax => `${tax.name} (${tax.rate}%)`).join(', ');
    };

    const getProportionalDiscount = (item: PosItem, returnQty: number): number => {
        // Use item-level discount (new system)
        if (item.item_discount_amount && item.item_discount_amount > 0) {
            return (item.item_discount_amount / item.quantity) * returnQty;
        }
        return 0;
    };

    const addReturnItem = (item: PosItem) => {
        const alreadyAdded = returnItems.some(ri => ri.original_pos_item_id === item.id);
        if (alreadyAdded) return;

        const lineTotal = 1 * (item.price || 0);
        const discountAmount = getProportionalDiscount(item, 1);
        const afterDiscount = lineTotal - discountAmount;
        const taxAmount = getTaxAmount(item, afterDiscount);
        const totalAmount = afterDiscount + taxAmount;

        setReturnItems([...returnItems, {
            product_id: item.product_id,
            original_pos_item_id: item.id,
            return_quantity: 1,
            unit_price: item.price || 0,
            reason: '',
            total_amount: totalAmount,
            taxes: item.taxes || [],
            discountAmount,
            return_discount: discountAmount
        }]);
    };

    const updateReturnItem = (originalPosItemId: number, field: string, value: any) => {
        setReturnItems(returnItems.map(item => {
            if (item.original_pos_item_id === originalPosItemId) {
                const updated = { ...item, [field]: value };
                if (field === 'return_quantity' || field === 'unit_price') {
                    const originalItem = selectedPos?.items?.find(i => i.id === originalPosItemId);
                    if (originalItem) {
                        updated.discountAmount = getProportionalDiscount(originalItem, updated.return_quantity);
                        const lineTotal = updated.return_quantity * (updated.unit_price || 0);
                        const afterDiscount = lineTotal - (updated.discountAmount || 0);
                        const taxAmount = getTaxAmount(originalItem, afterDiscount);
                        updated.total_amount = afterDiscount + taxAmount;
                    }
                }
                return updated;
            }
            return item;
        }));
    };

    const removeReturnItem = (originalPosItemId: number) => {
        setReturnItems(returnItems.filter(item => item.original_pos_item_id !== originalPosItemId));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('pos.returns.store'));
    };

    React.useEffect(() => {
        setData('items', returnItems.map(({ taxes, discountAmount, ...item }) => item));
    }, [returnItems]);

    const totals = {
        subtotal: returnItems.reduce((sum, item) => sum + (item.return_quantity || 0) * (item.unit_price || 0), 0),
        discount: returnItems.reduce((sum, item) => sum + (item.discountAmount || 0), 0),
        taxAmount: returnItems.reduce((sum, item) => {
            const lineTotal = (item.return_quantity || 0) * (item.unit_price || 0);
            const afterDiscount = lineTotal - (item.discountAmount || 0);
            let itemTax = 0;
            if (item.taxes && item.taxes.length > 0) {
                item.taxes.forEach(tax => {
                    itemTax += (afterDiscount * tax.rate) / 100;
                });
            }
            return sum + itemTax;
        }, 0),
        total: returnItems.reduce((sum, item) => sum + (item.total_amount || 0), 0)
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('POS Returns'), url: route('pos.returns.index')},
                {label: t('Create POS Return')}
            ]}
            pageTitle={t('Create POS Return')}
        >
            <Head title={t('Create POS Return')} />

            <div>
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Return Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CalendarDays className="h-5 w-5" />
                                {t('POS Return Details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <Label htmlFor="return_date" required>
                                        {t('Return Date')}
                                    </Label>
                                    <DatePicker
                                        id="return_date"
                                        value={data.return_date}
                                        onChange={(value) => setData('return_date', value)}
                                        required
                                    />
                                    <InputError message={errors.return_date} />
                                </div>

                                <div>
                                    <Label htmlFor="original_pos_id" required>
                                        {t('Original POS Order')}
                                    </Label>
                                    <Select value={data.original_pos_id} onValueChange={handlePosSelect}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select POS Order')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            {posSales.map((pos) => (
                                                <SelectItem key={pos.id} value={pos.id.toString()}>
                                                    {pos.sale_number}{pos.customer?.name ? ` - ${pos.customer.name}` : ''}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.original_pos_id} />
                                </div>

                                <div>
                                    <Label htmlFor="warehouse_id">
                                        {t('Warehouse')}
                                    </Label>
                                    <Select value={data.warehouse_id} onValueChange={(value) => setData('warehouse_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Warehouse')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {warehouses.map((warehouse) => (
                                                <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                    {warehouse.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.warehouse_id} />
                                </div>

                                <div>
                                    <Label htmlFor="reason">
                                        {t('Return Reason')}
                                    </Label>
                                    <Select value={data.reason} onValueChange={(value) => setData('reason', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Reason')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="defective">{t('Defective')}</SelectItem>
                                            <SelectItem value="wrong_item">{t('Wrong Item')}</SelectItem>
                                            <SelectItem value="damaged">{t('Damaged')}</SelectItem>
                                            <SelectItem value="excess_quantity">{t('Excess Quantity')}</SelectItem>
                                            <SelectItem value="other">{t('Other')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.reason} />
                                </div>
                            </div>

                            <div className="mt-4">
                                <Label htmlFor="notes">
                                    {t('Notes')}
                                </Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows={2}
                                    placeholder={t('Additional notes...')}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Available Items */}
                    {selectedPos && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Package className="h-5 w-5" />
                                    {t('Available Items from POS Order')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full">
                                        <thead>
                                            <tr className="border-b border-border">
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Product')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Available Qty')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Unit Price')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Discount')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Tax')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Total')}</th>
                                                <th className="px-4 py-3 text-center text-sm font-semibold text-foreground">{t('Action')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {selectedPos.items?.map((item) => {
                                                const availableQty = item.available_quantity ?? item.quantity;
                                                const lineTotal = availableQty * item.price;
                                                const discountAmount = getProportionalDiscount(item, availableQty);
                                                const afterDiscount = lineTotal - discountAmount;
                                                const taxAmount = getTaxAmount(item, afterDiscount);
                                                const total = afterDiscount + taxAmount;
                                                const taxDisplay = getTaxDisplay(item);
                                                return (
                                                    <tr key={item.id}>
                                                        <td className="px-4 py-4">
                                                            <div>
                                                                <h4 className="font-medium">{item.product?.name}</h4>
                                                                <p className="text-xs text-muted-foreground">{item.product?.sku || ''}</p>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <span className={`text-sm font-medium ${availableQty <= 0 ? 'text-red-600' : ''}`}>
                                                                {availableQty}
                                                            </span>
                                                            {availableQty <= 0 && (
                                                                <div className="text-xs text-red-600 mt-1">{t('No items available for return')}</div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <span className="text-sm">{formatCurrency(item.price)}</span>
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            {discountAmount > 0 ? (
                                                                <span className="text-sm text-green-600">-{formatCurrency(discountAmount)}</span>
                                                            ) : (
                                                                <span className="text-sm text-muted-foreground">-</span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            {item.taxes && item.taxes.length > 0 ? (
                                                                <div className="text-sm">
                                                                    <span className="text-xs">{taxDisplay}</span>
                                                                    <div className="text-xs text-muted-foreground mt-1">({formatCurrency(taxAmount)})</div>
                                                                </div>
                                                            ) : (
                                                                <span className="text-sm text-muted-foreground">-</span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <span className="text-sm font-medium">{formatCurrency(total)}</span>
                                                        </td>
                                                        <td className="px-4 py-4 text-center">
                                                            <Button
                                                                type="button"
                                                                onClick={() => addReturnItem(item)}
                                                                disabled={returnItems.some(ri => ri.original_pos_item_id === item.id) || availableQty <= 0}
                                                                size="sm"
                                                            >
                                                                {returnItems.some(ri => ri.original_pos_item_id === item.id) ? t('Added') : t('Add to Return')}
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Return Items */}
                    {returnItems.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <RotateCcw className="h-5 w-5" />
                                    {t('Return Items')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full">
                                        <thead>
                                            <tr className="border-b border-border">
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Product')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Return Qty')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Unit Price')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Discount')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Tax')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Total')}</th>
                                                <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">{t('Reason')}</th>
                                                <th className="px-4 py-3 text-center text-sm font-semibold text-foreground">{t('Action')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {returnItems.map((item) => {
                                                const originalItem = selectedPos?.items?.find(i => i.id === item.original_pos_item_id);
                                                const lineTotal = item.return_quantity * item.unit_price;
                                                const afterDiscount = lineTotal - item.discountAmount;
                                                let taxAmount = 0;
                                                if (item.taxes && item.taxes.length > 0) {
                                                    item.taxes.forEach(tax => {
                                                        taxAmount += (afterDiscount * tax.rate) / 100;
                                                    });
                                                }
                                                const taxDisplay = item.taxes && item.taxes.length > 0
                                                    ? item.taxes.map(tax => `${tax.name} (${tax.rate}%)`).join(', ')
                                                    : '-';
                                                return (
                                                    <tr key={item.original_pos_item_id}>
                                                        <td className="px-4 py-4">
                                                            <div>
                                                                <p className="font-medium">{originalItem?.product?.name}</p>
                                                                <p className="text-xs text-muted-foreground">{originalItem?.product?.sku || ''}</p>
                                                                {originalItem?.item_discount_amount && originalItem.item_discount_amount > 0 && (
                                                                    <div className="mt-2 space-y-1 text-xs">
                                                                        <div className="flex justify-between text-muted-foreground">
                                                                            <span>{t('Original Discount')}:</span>
                                                                            <span>{formatCurrency(originalItem.item_discount_amount)}</span>
                                                                        </div>
                                                                        <div className="flex justify-between text-green-600">
                                                                            <span>{t('Return Discount')}:</span>
                                                                            <span>-{formatCurrency(getProportionalDiscount(originalItem, item.return_quantity))}</span>
                                                                        </div>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <Input
                                                                type="number"
                                                                min="1"
                                                                max={originalItem?.available_quantity ?? originalItem?.quantity}
                                                                value={item.return_quantity}
                                                                onChange={(e) => updateReturnItem(item.original_pos_item_id, 'return_quantity', parseInt(e.target.value) || 1)}
                                                                className="w-20 text-sm"
                                                            />
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <span className="text-sm">{formatCurrency(item.unit_price)}</span>
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            {item.discountAmount > 0 ? (
                                                                <span className="text-sm text-green-600">-{formatCurrency(item.discountAmount)}</span>
                                                            ) : (
                                                                <span className="text-sm text-muted-foreground">-</span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            {item.taxes && item.taxes.length > 0 ? (
                                                                <div className="text-sm">
                                                                    <span className="text-xs">{taxDisplay}</span>
                                                                    <div className="text-xs text-muted-foreground mt-1">
                                                                        {formatCurrency(taxAmount)}
                                                                    </div>
                                                                </div>
                                                            ) : (
                                                                <span className="text-sm text-muted-foreground">-</span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <span className="text-sm font-medium">{formatCurrency(item.total_amount)}</span>
                                                        </td>
                                                        <td className="px-4 py-4">
                                                            <Input
                                                                value={item.reason}
                                                                onChange={(e) => updateReturnItem(item.original_pos_item_id, 'reason', e.target.value)}
                                                                placeholder={t('Optional reason')}
                                                                className="text-sm"
                                                            />
                                                        </td>
                                                        <td className="px-4 py-4 text-center">
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => removeReturnItem(item.original_pos_item_id)}
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

                                {/* Return Summary */}
                                <div className="mt-6 flex justify-end">
                                    <div className="w-80 bg-muted/30 rounded-lg p-4">
                                        <h3 className="font-semibold mb-3">{t('Return Summary')}</h3>
                                        <div>
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">{t('Subtotal')}</span>
                                                <span className="font-medium">{formatCurrency(totals.subtotal)}</span>
                                            </div>
                                            {totals.discount > 0 && (
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-muted-foreground">{t('Discount')}</span>
                                                    <span className="font-medium text-green-600">-{formatCurrency(totals.discount)}</span>
                                                </div>
                                            )}
                                            {totals.taxAmount > 0 && (
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-muted-foreground">{t('Tax')}</span>
                                                    <span className="font-medium">{formatCurrency(totals.taxAmount)}</span>
                                                </div>
                                            )}
                                            <div className="border-t pt-3">
                                                <div className="flex justify-between">
                                                    <span className="font-semibold">{t('Total Return Amount')}</span>
                                                    <span className="font-bold text-lg">{formatCurrency(totals.total)}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Actions */}
                    <div className="flex justify-between items-center">
                        <div className="text-sm text-muted-foreground">
                            {returnItems.length} {t('items selected for return')}
                        </div>
                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => window.history.back()}
                            >
                                {t('Cancel')}
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing || returnItems.length === 0}
                            >
                                {processing ? t('Creating...') : t('Create')}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
