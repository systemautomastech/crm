import React, { useState, useMemo, useEffect } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useFormFields } from '@/hooks/useFormFields';
import { PurchaseInvoice, PurchaseInvoiceItem } from './types';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import InvoiceItemsTable from './components/InvoiceItemsTable';
import { useTaxCalculator, calculateLineItemAmounts } from './components/TaxCalculator';
import { formatCurrency } from '@/utils/helpers';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { CalendarDays, Building2, User, UserPlus, Users, X, Package } from 'lucide-react';
import RichTextEditor from '@/components/ui/rich-text-editor';

interface EditProps {
    invoice: PurchaseInvoice;
    vendors: Array<{ id: number; name: string; email: string }>;
    products: Array<{ id: number; name: string; sku: string; description?: string; long_description?: string; purchase_price: number; unit: string; type: string; taxes: Array<{ id: number; tax_name: string; rate: number }> }>;
    warehouses: Array<{ id: number; name: string; address: string }>;
    [key: string]: any;
}

export default function Edit() {
    const { t } = useTranslation();
    const { invoice, vendors, products, warehouses } = usePage<EditProps>().props;
    const [availableProducts, setAvailableProducts] = useState<any[]>(Array.isArray(products) ? products : []);
    const [isRefreshing, setIsRefreshing] = useState(false);

    const fetchWarehouseProducts = async (warehouseId: string) => {
        if (!warehouseId) {
            setAvailableProducts([]);
            return;
        }

        try {
            setIsRefreshing(true);
            setAvailableProducts([]);
            const response = await fetch(route('purchase-invoices.warehouse.products') + `?warehouse_id=${warehouseId}`);
            if (!response.ok) throw new Error('Failed to fetch products');
            const data = await response.json();
            setAvailableProducts(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error('Error fetching warehouse products:', error);
            setAvailableProducts([]);
        } finally {
            setIsRefreshing(false);
        }
    };

    const handleRefresh = () => {
        if (data.warehouse_id) {
            fetchWarehouseProducts(data.warehouse_id);
        } else {
            setIsRefreshing(true);
            router.reload({
                only: ['products'],
                onFinish: () => setIsRefreshing(false)
            });
        }
    };

    useFlashMessages();

    const { data, setData, put, processing, errors } = useForm({
        invoice_date: invoice.invoice_date,
        due_date: invoice.due_date,
        vendor_id: invoice.vendor_id ? invoice.vendor_id.toString() : '',
        warehouse_id: invoice.warehouse_id?.toString() || '',
        payment_terms: invoice.payment_terms || '',
        notes: invoice.notes || '',
        items: (invoice.items || []).map(item => {
            const calculations = calculateLineItemAmounts(
                item.quantity,
                item.unit_price,
                item.discount_percentage,
                item.tax_percentage
            );
            return {
                ...item,
                product_type: item.product_type || item.product?.type || 'product',
                description: item.description || item.product?.long_description || '',
                taxes: item.taxes || [],
                discount_amount: calculations.discountAmount,
                tax_amount: calculations.taxAmount,
                total_amount: calculations.totalAmount
            };
        }) as PurchaseInvoiceItem[]
    });

    useEffect(() => {
        if (data.warehouse_id) {
            fetchWarehouseProducts(data.warehouse_id);
        } else {
            setAvailableProducts([]);
        }
    }, [data.warehouse_id]);

    const selectedVendor = useMemo(() => {
        return vendors?.find((v) => v && v.id !== null && v.id !== undefined && String(v.id) === data.vendor_id) || null;
    }, [data.vendor_id, vendors]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('purchase-invoices.update', invoice.id));
    };

    const totals = useTaxCalculator(data.items);

    // Recurring fields hook
    const recurringFields = useFormFields('purchaseInvoiceEditFields', data, setData, errors, 'edit', invoice);

    // Custom fields hook
    const customFields = useFormFields('getCustomFields', { ...data, module: 'General', sub_module: 'Purchase Invoice', id: invoice.id }, setData, errors, 'edit', t);
    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Purchase'), url: route('purchase-invoices.index') },
                { label: t('Edit Purchase Invoice') }
            ]}
            pageTitle={t('Edit Purchase Invoice')}
        >
            <Head title={t('Edit Purchase Invoice')} />

            <div>
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Invoice Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CalendarDays className="h-5 w-5" />
                                {t('Purchase Invoice Details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <Label htmlFor="vendor_id" required className="mb-0">
                                            {t('Vendor')}
                                        </Label>
                                        <Link
                                            href={route('account.vendors.index')}
                                            className="text-[11px] font-semibold text-primary flex items-center gap-1 cursor-pointer transition-colors hover:underline"
                                        >
                                            <UserPlus className="h-3 w-3" />
                                            {t('New Vendor')}
                                        </Link>
                                    </div>

                                    <Select value={data.vendor_id} onValueChange={(value) => setData('vendor_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Vendor')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            {Array.isArray(vendors) && vendors.map((vendor: any) => vendor && vendor.id !== undefined && vendor.id !== null ? (
                                                <SelectItem key={vendor.id} value={String(vendor.id)}>
                                                    {vendor.name || 'Vendor'} - {vendor.email || '-'}
                                                </SelectItem>
                                            ) : null)}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.vendor_id} />

                                    {/* Selected Vendor Card directly below vendor select */}
                                    {selectedVendor && (
                                        <div className="mt-2 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/80 dark:bg-slate-900/40 text-xs space-y-1">
                                            <div className="flex items-center justify-between gap-1.5 pb-1 border-b border-slate-200/60 dark:border-slate-800/60">
                                                <div className="flex items-center gap-1.5 min-w-0">
                                                    <div className="w-4 h-4 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[9px] font-bold shrink-0">
                                                        <User className="w-2.5 h-2.5" />
                                                    </div>
                                                    <span className="font-semibold text-slate-900 dark:text-slate-100 text-xs truncate">
                                                        {selectedVendor.name}
                                                    </span>
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="text-red-500 hover:text-red-700 hover:bg-red-50 h-4 px-1 text-[10px] font-medium gap-0.5 shrink-0"
                                                    onClick={() => setData('vendor_id', '')}
                                                >
                                                    <X className="w-2.5 h-2.5" />
                                                    {t('Clear')}
                                                </Button>
                                            </div>
                                            <div className="text-[11px] text-slate-500 dark:text-slate-400 space-y-0.5 truncate">
                                                <div className="truncate">{selectedVendor.email || '-'}</div>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <Label htmlFor="invoice_date" required>
                                        {t('Invoice Date')}
                                    </Label>
                                    <DatePicker
                                        id="invoice_date"
                                        value={data.invoice_date}
                                        onChange={(value) => setData('invoice_date', value)}
                                        required
                                    />
                                    <InputError message={errors.invoice_date} />
                                </div>

                                <div>
                                    <Label htmlFor="due_date" required>
                                        {t('Due Date')}
                                    </Label>
                                    <DatePicker
                                        id="due_date"
                                        value={data.due_date}
                                        onChange={(value) => setData('due_date', value)}
                                        required
                                    />
                                    <InputError message={errors.due_date} />
                                </div>

                                <div>
                                    <Label htmlFor="warehouse_id" required>
                                        {t('Warehouse')}
                                    </Label>
                                    <Select
                                        value={data.warehouse_id}
                                        onValueChange={(value) => {
                                            setData((prev) => ({
                                                ...prev,
                                                warehouse_id: value,
                                                items: prev.items.map((item) => ({
                                                    ...item,
                                                    product_id: 0,
                                                    unit_price: 0,
                                                    description: '',
                                                    tax_percentage: 0,
                                                    taxes: [],
                                                    tax_amount: 0,
                                                    discount_amount: 0,
                                                    total_amount: 0,
                                                }))
                                            }));
                                            fetchWarehouseProducts(value);
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Warehouse')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {warehouses.map((warehouse) => (
                                                <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                    {warehouse.name} - {warehouse.address}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.warehouse_id} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div className="space-y-2">
                                    <Label htmlFor="payment_terms">
                                        {t('Payment Terms')}
                                    </Label>
                                    <RichTextEditor
                                        content={data.payment_terms}
                                        onChange={(val) => setData('payment_terms', val)}
                                        placeholder={t('Enter invoice payment terms...')}
                                        minimal={true}
                                    />
                                    <InputError message={errors.payment_terms} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">
                                        {t('Notes')}
                                    </Label>
                                    <Textarea
                                        id="notes"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        rows={4}
                                        placeholder={t('Additional notes...')}
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                            </div>

                            {/* Recurring Purchase Invoice */}
                            <div className="mt-6">
                                {recurringFields.map((field) => (
                                    <div key={field.id} className="mb-4">{field.component}</div>
                                ))}
                            </div>

                            {/* Custom Fields */}
                            {customFields.length > 0 && (
                                <div className="mt-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {customFields.map((field) => (
                                            <div key={field.id}>
                                                {field.component}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Invoice Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Package className="h-5 w-5" />
                                {t('Purchase Invoice Items')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <InvoiceItemsTable
                                items={data.items}
                                onChange={(items) => setData('items', items)}
                                errors={errors}
                                products={availableProducts}
                                showAddButton={true}
                                onRefresh={handleRefresh}
                                isRefreshing={isRefreshing}
                                warehouseId={data.warehouse_id}
                            />

                            {/* Invoice Summary */}
                            <div className="mt-6 flex justify-end">
                                <div className="w-80 bg-muted/30 rounded-lg p-4">
                                    <h3 className="font-semibold mb-3">{t('Invoice Summary')}</h3>
                                    <div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Subtotal')}</span>
                                            <span className="font-medium">{formatCurrency(totals.subtotal)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Discount')}</span>
                                            <span className="font-medium text-red-600">-{formatCurrency(totals.discountAmount)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Tax')}</span>
                                            <span className="font-medium">{formatCurrency(totals.taxAmount)}</span>
                                        </div>
                                        <Separator className="my-2" />
                                        <div className="flex justify-between">
                                            <span className="font-semibold">{t('Total')}</span>
                                            <span className="font-bold text-lg">{formatCurrency(totals.total)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex justify-between items-center">
                        <div className="text-sm text-muted-foreground">
                            {data.items.length} {t('items added')}
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
                                disabled={processing || data.items.length === 0}
                            >
                                {processing ? t('Updating...') : t('Update')}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
