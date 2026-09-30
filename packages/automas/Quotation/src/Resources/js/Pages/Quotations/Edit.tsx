import React, { useState, useEffect, useMemo } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { route } from 'ziggy-js';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useFormFields } from '@/hooks/useFormFields';
import { QuotationItem, QuotationDefaultPage, SalesQuotation } from './types';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import ItemsTable from './components/ItemsTable';
import PageOrder from './components/PageOrder';
import PreviewModal from '@/components/PreviewModal';
import { useTaxCalculator } from '@/pages/Sales/components/TaxCalculator';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { DatePicker } from '@/components/ui/date-picker';
import { Switch } from '@/components/ui/switch';
import RichTextEditor from '@/components/ui/rich-text-editor';
import { CalendarDays, FileText, Eye, User, Users, UserPlus, X } from 'lucide-react';

interface EditProps {
    quotation: SalesQuotation;
    customers: Array<{ id: number; name: string; email: string; [key: string]: any }>;
    warehouses: Array<{ id: number; name: string; address: string }>;
    products?: any[];
    defaultPages?: QuotationDefaultPage[];
    defaultTerms?: string | null;
    quotationSetting?: any;
    QuotationSetting?: any;
    [key: string]: any;
}

export default function Edit() {
    const { t } = useTranslation();
    const { quotation, customers, warehouses, products = [], defaultPages = [], defaultTerms, quotationSetting, QuotationSetting } = usePage<EditProps>().props;
    const activeSetting = quotationSetting || QuotationSetting;
    const [availableProducts, setAvailableProducts] = useState<any[]>(Array.isArray(products) ? products : []);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);

    // Initialize Quotation sections with existing contents or fallback to defaultPages
    const [sections, setSections] = useState<Array<{ id: string; title: string; content: string; page_type?: string; background_image?: string; order: number; isExpanded: boolean; default_page_id?: number }>>(() => {
        const contentsRel = (quotation as any).contents;
        let parsed: any[] = [];
        if (Array.isArray(contentsRel) && contentsRel.length > 0) {
            parsed = contentsRel.map((c: any) => {
                let dec: any = null;
                if (c.quotation_content || c.content) {
                    try {
                        dec = typeof c.quotation_content === 'string' ? JSON.parse(c.quotation_content) : (typeof c.content === 'string' ? JSON.parse(c.content) : (c.quotation_content || c.content));
                    } catch (e) { }
                }

                const title = dec?.title || c.title || '';
                const content = dec?.content || c.content || c.quotation_content || '';
                let page_type = dec?.page_type || c.page_type;

                if (!page_type || page_type === 'content') {
                    if (content === '[OTC_CHARGES_TABLE]' || title.toLowerCase().includes('one-time charges') || title.toLowerCase().includes('otc')) {
                        page_type = 'otc';
                    } else if (content === '[MRC_CHARGES_TABLE]' || title.toLowerCase().includes('monthly recurring charges') || title.toLowerCase().includes('mrc')) {
                        page_type = 'mrc';
                    } else if (content === '[OTHER_DETAILS_CONTENT]' || title.toLowerCase().includes('other details')) {
                        page_type = 'other-details';
                    } else {
                        page_type = 'general';
                    }
                }

                return {
                    title: title,
                    content: content,
                    page_type: page_type,
                    background_image: dec?.background_image || c.background_image || '',
                    order: typeof c.sort_order !== 'undefined' ? Number(c.sort_order) : (typeof c.order !== 'undefined' ? Number(c.order) : 1),
                    default_page_id: dec?.default_page_id || c.default_page_id,
                };
            });
        } else {
            const rawContent = (quotation as any).quotation_content || (quotation as any).others;
            if (typeof rawContent === 'string') {
                try {
                    parsed = JSON.parse(rawContent);
                } catch (e) {
                    parsed = [];
                }
            } else if (Array.isArray(rawContent)) {
                parsed = rawContent;
            }
        }

        if (parsed.length === 0 && defaultPages && defaultPages.length > 0) {
            parsed = defaultPages.map((p, idx) => {
                const title = p.title || '';
                const content = p.content || '';
                let page_type = p.page_type;

                if (!page_type || page_type === 'content') {
                    if (content === '[OTC_CHARGES_TABLE]' || title.toLowerCase().includes('one-time charges') || title.toLowerCase().includes('otc')) {
                        page_type = 'otc';
                    } else if (content === '[MRC_CHARGES_TABLE]' || title.toLowerCase().includes('monthly recurring charges') || title.toLowerCase().includes('mrc')) {
                        page_type = 'mrc';
                    } else if (content === '[OTHER_DETAILS_CONTENT]' || title.toLowerCase().includes('other details')) {
                        page_type = 'other-details';
                    } else {
                        page_type = 'general';
                    }
                }

                return {
                    title: title,
                    content: content,
                    page_type: page_type,
                    background_image: p.background_image || '',
                    order: Number(p.sort_order) || idx + 1,
                    default_page_id: p.id,
                };
            });
        }

        parsed.sort((a: any, b: any) => {
            const orderA = typeof a.order === 'number' ? a.order : (parseInt(a.order, 10) || 0);
            const orderB = typeof b.order === 'number' ? b.order : (parseInt(b.order, 10) || 0);
            return orderA - orderB;
        });

        return parsed.map((item: any, idx: number) => ({
            id: `sec-${idx}-${Date.now()}`,
            default_page_id: item.default_page_id,
            title: item.title || (item.page_type === 'otc' ? 'One-Time Charges (OTC)' : item.page_type === 'mrc' ? 'Monthly Recurring Charges (MRC)' : item.page_type === 'other-details' ? 'Other Details' : `Page ${idx + 1}`),
            content: item.content || '',
            page_type: item.page_type || 'general',
            background_image: item.background_image || '',
            order: typeof item.order === 'number' ? item.order : (parseInt(item.order, 10) || (idx + 1)),
            isExpanded: false,
        }));
    });

    useFlashMessages();
    const { data, setData, put, processing, errors, transform } = useForm({
        quotation_id: quotation.id ? quotation.id.toString() : '',
        reference: quotation.reference || '',
        subject: quotation.subject || '',
        quotation_date: quotation.quotation_date || new Date().toISOString().split('T')[0],
        due_date: quotation.due_date || '',
        customer_type: (!quotation.customer_id && (quotation.customer_name || quotation.customer_email) ? 'new' : 'existing') as 'existing' | 'new',
        customer_id: quotation.customer_id ? quotation.customer_id.toString() : '',
        customer_name: quotation.customer_name || '',
        customer_email: quotation.customer_email || '',
        customer_phone: quotation.customer_phone || '',
        customer_address: quotation.customer_address || '',
        warehouse_id: quotation.warehouse_id ? quotation.warehouse_id.toString() : '',
        is_tax_enabled: quotation.is_tax_enabled !== undefined ? Boolean(quotation.is_tax_enabled) : true,
        is_prepaid: quotation.is_prepaid !== undefined ? Boolean(quotation.is_prepaid) : false,
        otc_discount_type: (quotation.otc_discount_type || 'percentage') as 'percentage' | 'fixed',
        otc_discount_value: quotation.otc_discount_value || 0,
        mrc_discount_type: (quotation.mrc_discount_type || 'percentage') as 'percentage' | 'fixed',
        mrc_discount_value: quotation.mrc_discount_value || 0,
        payment_terms: quotation.payment_terms || '',
        notes: quotation.notes || '',
        items: (quotation.items && quotation.items.length > 0) ? quotation.items.map(item => ({
            ...item,
            description: item.description || item.product_description || (item.product as any)?.description || '',
            product_description: item.product_description || item.description || (item.product as any)?.description || '',
            section: item.section || 'general',
            product_type: item.product_type || (item as any).item_type || (item.product as any)?.type || quotation.type || 'product',
            quantity: item.quantity || 1,
            unit_price: item.unit_price || 0,
            discount_type: item.discount_type || (item.section === 'mrc' ? quotation.mrc_discount_type : quotation.otc_discount_type) || 'percentage',
            discount_percentage: item.discount_percentage || 0,
            discount_amount: item.discount_amount || 0,
            tax_percentage: item.tax_percentage || 0,
            tax_amount: item.tax_amount || 0,
            total_amount: item.total_amount || 0,
        })) : [{
            product_id: 0,
            section: 'general',
            product_type: 'product',
            product_description: '',
            quantity: 1,
            unit_price: 0,
            discount_percentage: 0,
            discount_amount: 0,
            tax_percentage: 0,
            tax_amount: 0,
            total_amount: 0
        }] as QuotationItem[],
        contents: [],
        other_details: quotation.other_details || (sections.find(s => s.page_type === 'other-details')?.content !== '[OTHER_DETAILS_CONTENT]' ? (sections.find(s => s.page_type === 'other-details')?.content || '') : ''),
    });

    // Auto-sync OTC, MRC, and Other Details section cards into Page Order list when products exist in those tables
    useEffect(() => {
        const hasOtcItems = data.items.some(
            (i) => (i.section === 'otc' || i.section === 'general' || !i.section) && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
        );
        const hasMrcItems = data.items.some(
            (i) => i.section === 'mrc' && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
        );
        const hasOtherDetails = Boolean(data.other_details && data.other_details.trim() !== '' && data.other_details !== '<p></p>');

        setSections((prev) => {
            const currentHasOtc = prev.some((s) => s.page_type === 'otc');
            const currentHasMrc = prev.some((s) => s.page_type === 'mrc');
            const currentHasOther = prev.some((s) => s.page_type === 'other-details');

            if (hasOtcItems === currentHasOtc && hasMrcItems === currentHasMrc && hasOtherDetails === currentHasOther) {
                return prev;
            }

            const defaultOtc = defaultPages?.find((p) => p.page_type === 'otc');
            const defaultMrc = defaultPages?.find((p) => p.page_type === 'mrc');
            const defaultOther = defaultPages?.find((p) => p.page_type === 'other-details');

            let nextSections = [...prev];

            // 1. Remove sections if items no longer exist
            if (!hasOtcItems && currentHasOtc) {
                nextSections = nextSections.filter((s) => s.page_type !== 'otc');
            }
            if (!hasMrcItems && currentHasMrc) {
                nextSections = nextSections.filter((s) => s.page_type !== 'mrc');
            }
            if (!hasOtherDetails && currentHasOther) {
                nextSections = nextSections.filter((s) => s.page_type !== 'other-details');
            }

            // 2. Add OTC if items exist and not present
            if (hasOtcItems && !currentHasOtc) {
                nextSections.push({
                    id: `sec-otc-${defaultOtc?.id || 'dynamic'}`,
                    default_page_id: defaultOtc?.id,
                    title: defaultOtc?.title || 'One-Time Charges (OTC)',
                    content: '[OTC_CHARGES_TABLE]',
                    page_type: 'otc',
                    background_image: defaultOtc?.background_image || '',
                    order: defaultOtc?.sort_order !== undefined ? Number(defaultOtc.sort_order) : 99,
                    isExpanded: false,
                });
            }

            // 3. Add MRC if items exist and not present
            if (hasMrcItems && !currentHasMrc) {
                nextSections.push({
                    id: `sec-mrc-${defaultMrc?.id || 'dynamic'}`,
                    default_page_id: defaultMrc?.id,
                    title: defaultMrc?.title || 'Monthly Recurring Charges (MRC)',
                    content: '[MRC_CHARGES_TABLE]',
                    page_type: 'mrc',
                    background_image: defaultMrc?.background_image || '',
                    order: defaultMrc?.sort_order !== undefined ? Number(defaultMrc.sort_order) : 100,
                    isExpanded: false,
                });
            }

            // 4. Add Other Details if present
            if (hasOtherDetails && !currentHasOther) {
                nextSections.push({
                    id: `sec-other-details-${defaultOther?.id || 'dynamic'}`,
                    default_page_id: defaultOther?.id,
                    title: defaultOther?.title || 'Other Details',
                    content: '[OTHER_DETAILS_CONTENT]',
                    page_type: 'other-details',
                    background_image: defaultOther?.background_image || '',
                    order: defaultOther?.sort_order !== undefined ? Number(defaultOther.sort_order) : 101,
                    isExpanded: false,
                });
            }

            return nextSections.map((s, idx) => ({ ...s, order: idx + 1 }));
        });
    }, [data.items, data.other_details, defaultPages]);

    // Selected Customer Details
    const selectedCustomer = useMemo(() => {
        if (data.customer_type === 'new') {
            if (!data.customer_name && !data.customer_email) return null;
            return {
                id: 0,
                name: data.customer_name,
                email: data.customer_email,
                mobile_no: data.customer_phone,
                phone: data.customer_phone,
                address: data.customer_address,
                type: data.customer_type,
            };
        }
        return customers.find((c) => String(c.id) === String(data.customer_id)) || null;
    }, [data.customer_id, data.customer_type, data.customer_name, data.customer_email, data.customer_phone, data.customer_address, customers]);

    // Auto-fill customer info when existing customer is selected
    useEffect(() => {
        if (data.customer_type === 'existing' && data.customer_id) {
            const cust = customers.find((c) => String(c.id) === String(data.customer_id));
            if (cust) {
                setData((prev) => ({
                    ...prev,
                    customer_name: cust.name || '',
                    customer_email: cust.email || '',
                    customer_phone: (cust as any).mobile_no || (cust as any).phone || '',
                    customer_address: (cust as any).address || '',
                }));
            }
        }
    }, [data.customer_id, data.customer_type]);

    const customFields = useFormFields('getCustomFields', { ...data, module: 'General', sub_module: 'Quotation', id: quotation.id }, setData, errors, 'edit', t);

    const [isRefreshingProducts, setIsRefreshingProducts] = useState(false);

    const refreshProducts = async () => {
        setIsRefreshingProducts(true);
        const startTime = Date.now();
        try {
            const url = data.warehouse_id
                ? route('quotations.warehouse.products') + `?warehouse_id=${data.warehouse_id}`
                : route('quotations.warehouse.products');
            const response = await fetch(url);
            const warehouseProducts = await response.json();
            setAvailableProducts(Array.isArray(warehouseProducts) ? warehouseProducts : []);
        } catch (error) {
            console.error('Failed to refresh products:', error);
        } finally {
            const elapsed = Date.now() - startTime;
            if (elapsed < 500) {
                setTimeout(() => setIsRefreshingProducts(false), 500 - elapsed);
            } else {
                setIsRefreshingProducts(false);
            }
        }
    };

    useEffect(() => {
        if (data.warehouse_id) {
            handleWarehouseChange(data.warehouse_id);
        }
    }, []);

    const handleWarehouseChange = async (warehouseId: string) => {
        setData('warehouse_id', warehouseId);

        if (warehouseId) {
            try {
                setIsRefreshingProducts(true);
                setAvailableProducts([]);
                const response = await fetch(route('quotations.warehouse.products') + `?warehouse_id=${warehouseId}`);
                const warehouseProducts = await response.json();
                setAvailableProducts(Array.isArray(warehouseProducts) ? warehouseProducts : []);
            } catch (error) {
                console.error('Failed to fetch warehouse products:', error);
                setAvailableProducts([]);
            } finally {
                setIsRefreshingProducts(false);
            }
        } else {
            setAvailableProducts([]);
        }
    };

    const handleTaxToggle = (checked: boolean) => {
        setData('is_tax_enabled', checked);
        const updatedItems = data.items.map((item) => {
            const qty = Number(item.quantity) || 0;
            const price = Number(item.unit_price) || 0;
            const disc = Number(item.discount_percentage) || 0;
            const lineTotal = qty * price;
            const discountAmount = (lineTotal * disc) / 100;
            const discountedTotal = lineTotal - discountAmount;

            if (!checked) {
                return {
                    ...item,
                    tax_percentage: 0,
                    tax_amount: 0,
                    total_amount: discountedTotal,
                    taxes: [],
                };
            }

            const product = availableProducts.find((p) => String(p.id) === String(item.product_id));
            const prodTaxes = product?.taxes || [];
            const totalTaxRate = prodTaxes.reduce((sum: number, tax: any) => sum + (Number(tax.rate) || 0), 0);
            const taxes = prodTaxes.map((tax: any) => ({
                tax_name: tax.tax_name,
                tax_rate: tax.rate,
            }));
            const taxRate = totalTaxRate > 0 ? totalTaxRate : (Number(item.tax_percentage) || 0);
            const taxAmount = (discountedTotal * taxRate) / 100;

            return {
                ...item,
                tax_percentage: taxRate,
                tax_amount: taxAmount,
                total_amount: discountedTotal + taxAmount,
                taxes: taxes.length > 0 ? taxes : (item.taxes || []),
            };
        });
        setData('items', updatedItems);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        transform((formData) => ({
            ...formData,
            items: formData.items.map((item) => ({
                ...item,
                item_type: item.product_type || (item as any).item_type || 'product',
                product_type: item.product_type || (item as any).item_type || 'product',
            })),
            contents: sections.map((item, index) => ({
                title: item.title,
                content: item.page_type === 'other-details' ? (data.other_details || item.content || '') : item.content,
                page_type: item.page_type || 'content',
                background_image: item.background_image || '',
                sort_order: index + 1,
            })),
        }));

        put(route('quotations.update', quotation.id));
    };

    const totals = useMemo(() => {
        const otcItems = data.items.filter(i => i.section === 'otc' || i.section === 'general' || !i.section);
        const mrcItems = data.items.filter(i => i.section === 'mrc');

        const otcSubtotal = otcItems.reduce((acc, i) => acc + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0), 0);
        const mrcSubtotal = mrcItems.reduce((acc, i) => acc + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0), 0);

        let otcDiscount = 0;
        if (data.otc_discount_type === 'percentage') {
            otcDiscount = (otcSubtotal * Math.min(Math.max(Number(data.otc_discount_value) || 0, 0), 100)) / 100;
        } else {
            otcDiscount = Math.min(Math.max(Number(data.otc_discount_value) || 0, 0), otcSubtotal);
        }

        let mrcDiscount = 0;
        if (data.mrc_discount_type === 'percentage') {
            mrcDiscount = (mrcSubtotal * Math.min(Math.max(Number(data.mrc_discount_value) || 0, 0), 100)) / 100;
        } else {
            mrcDiscount = Math.min(Math.max(Number(data.mrc_discount_value) || 0, 0), mrcSubtotal);
        }

        const otcTax = otcItems.reduce((acc, i) => acc + (Number(i.tax_amount) || 0), 0);
        const mrcTax = mrcItems.reduce((acc, i) => acc + (Number(i.tax_amount) || 0), 0);

        const subtotal = otcSubtotal + mrcSubtotal;
        const discountAmount = otcDiscount + mrcDiscount;
        const taxAmount = otcTax + mrcTax;
        const total = Math.max(0, subtotal - discountAmount + taxAmount);

        return {
            subtotal,
            discountAmount,
            taxAmount,
            total,
            otcSubtotal,
            otcDiscount,
            otcTax,
            otcTotal: Math.max(0, otcSubtotal - otcDiscount + otcTax),
            mrcSubtotal,
            mrcDiscount,
            mrcTax,
            mrcTotal: Math.max(0, mrcSubtotal - mrcDiscount + mrcTax),
        };
    }, [data.items, data.otc_discount_type, data.otc_discount_value, data.mrc_discount_type, data.mrc_discount_value]);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales Quotation'), url: route('quotations.index') },
                { label: t('Edit') }
            ]}
            pageTitle={t('Edit Sales Quotation')}
        >
            <Head title={t('Edit Sales Quotation')} />

            <div>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CalendarDays className="h-5 w-5" />
                                {t('Sales Quotation Details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            {/* Row 1: Quotation Number & Subject */}
                            <div className="flex flex-col md:flex-row gap-4 border-b pb-4 items-start">
                                <div className="w-full md:w-64 shrink-0">
                                    <Label htmlFor="quotation_number">{t('Quotation Number')}</Label>
                                    <Input
                                        id="quotation_number"
                                        value={quotation.quotation_number || `QT-${quotation.id}`}
                                        readOnly
                                        className="bg-muted cursor-not-allowed font-mono text-xs"
                                    />
                                </div>

                                <div className="w-full flex-1">
                                    <Label htmlFor="subject">{t('Subject')}</Label>
                                    <Input
                                        id="subject"
                                        value={data.subject}
                                        onChange={(e) => setData('subject', e.target.value)}
                                        placeholder={t('e.g., Quotation for IP PABX Service')}
                                    />
                                    <InputError message={errors.subject} />
                                </div>
                            </div>

                            {/* Row 2: Customer, Quotation Date, Due Date, Warehouse */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <Label htmlFor="customer_id" required className="mb-0">
                                            {t('Customer')}
                                        </Label>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                const nextMode = data.customer_type === 'new' ? 'existing' : 'new';
                                                setData('customer_type', nextMode);
                                            }}
                                            className="text-[11px] font-semibold text-primary hover:underline flex items-center gap-1 cursor-pointer transition-colors"
                                        >
                                            {data.customer_type === 'new' ? (
                                                <>
                                                    <Users className="h-3 w-3" />
                                                    {t('Select Existing')}
                                                </>
                                            ) : (
                                                <>
                                                    <UserPlus className="h-3 w-3" />
                                                    {t('New Customer')}
                                                </>
                                            )}
                                        </button>
                                    </div>

                                    {data.customer_type === 'existing' ? (
                                        <>
                                            <Select value={data.customer_id} onValueChange={(value) => setData('customer_id', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Select Customer')} />
                                                </SelectTrigger>
                                                <SelectContent searchable>
                                                    {customers?.map((customer) => (
                                                        <SelectItem key={customer.id} value={customer.id.toString()}>
                                                            {customer.name} - {customer.email}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.customer_id} />

                                            {/* Selected Customer Card directly below customer select */}
                                            {selectedCustomer && (
                                                <div className="mt-2 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/80 dark:bg-slate-900/40 text-xs space-y-1">
                                                    <div className="flex items-center justify-between gap-1.5 pb-1 border-b border-slate-200/60 dark:border-slate-800/60">
                                                        <div className="flex items-center gap-1.5 min-w-0">
                                                            <div className="w-4 h-4 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[9px] font-bold shrink-0">
                                                                <User className="w-2.5 h-2.5" />
                                                            </div>
                                                            <span className="font-semibold text-slate-900 dark:text-slate-100 text-xs truncate">
                                                                {selectedCustomer.name}
                                                            </span>
                                                        </div>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            className="text-red-500 hover:text-red-700 hover:bg-red-50 h-4 px-1 text-[10px] font-medium gap-0.5 shrink-0"
                                                            onClick={() => setData('customer_id', '')}
                                                        >
                                                            <X className="w-2.5 h-2.5" />
                                                            {t('Clear')}
                                                        </Button>
                                                    </div>
                                                    <div className="text-[11px] text-slate-500 dark:text-slate-400 space-y-0.5 truncate">
                                                        <div className="truncate">{selectedCustomer.email || '-'}</div>
                                                        {(selectedCustomer.mobile_no || selectedCustomer.phone) && (
                                                            <div className="truncate">{selectedCustomer.mobile_no || selectedCustomer.phone}</div>
                                                        )}
                                                        {selectedCustomer.address && (
                                                            <div className="text-slate-600 dark:text-slate-300 truncate" title={selectedCustomer.address}>
                                                                {selectedCustomer.address}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </>
                                    ) : (
                                        <div className="h-10 px-3 py-1 rounded-md border border-dashed border-primary/50 bg-primary/5 text-primary text-xs font-medium flex items-center justify-between">
                                            <span className="flex items-center gap-1.5">
                                                <UserPlus className="h-3.5 w-3.5" />
                                                {data.customer_name || t('New Customer Mode')}
                                            </span>
                                            <Badge variant="secondary" className="text-[10px] h-4 px-1">
                                                {t('New')}
                                            </Badge>
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <Label htmlFor="quotation_date" required>
                                        {t('Quotation Date')}
                                    </Label>
                                    <DatePicker
                                        id="quotation_date"
                                        value={data.quotation_date}
                                        onChange={(value) => setData('quotation_date', value)}
                                        required
                                    />
                                    <InputError message={errors.quotation_date} />
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
                                    <Select value={data.warehouse_id} onValueChange={handleWarehouseChange}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Warehouse')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            {warehouses?.map((warehouse) => (
                                                <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                    {warehouse.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.warehouse_id} />
                                </div>
                            </div>

                            {/* New Customer Form Row when New Mode is Active */}
                            {data.customer_type === 'new' && (
                                <div className="p-3 rounded-xl border border-primary/20 bg-primary/[0.02] dark:bg-primary/[0.04] space-y-2.5 animate-in fade-in-50 duration-200">
                                    <div className="flex items-center justify-between pb-1 border-b border-primary/10">
                                        <div className="text-xs font-semibold text-primary flex items-center gap-1.5">
                                            <UserPlus className="h-3.5 w-3.5" />
                                            {t('New Customer Details')}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                                        {/* Name */}
                                        <div className="space-y-1">
                                            <Label htmlFor="customer_name" required className="text-xs">
                                                {t('Customer Name')}
                                            </Label>
                                            <Input
                                                id="customer_name"
                                                value={data.customer_name}
                                                onChange={(e) => setData('customer_name', e.target.value)}
                                                placeholder={t('Name')}
                                                className="h-8 text-xs"
                                                required
                                            />
                                            <InputError message={errors.customer_name} />
                                        </div>

                                        {/* Email */}
                                        <div className="space-y-1">
                                            <Label htmlFor="customer_email" required className="text-xs">
                                                {t('Email')}
                                            </Label>
                                            <Input
                                                id="customer_email"
                                                type="email"
                                                value={data.customer_email}
                                                onChange={(e) => setData('customer_email', e.target.value)}
                                                placeholder="email@example.com"
                                                className="h-8 text-xs"
                                                required
                                            />
                                            <InputError message={errors.customer_email} />
                                        </div>

                                        {/* Phone */}
                                        <div className="space-y-1">
                                            <Label htmlFor="customer_phone" required className="text-xs">
                                                {t('Phone')}
                                            </Label>
                                            <Input
                                                id="customer_phone"
                                                value={data.customer_phone}
                                                onChange={(e) => setData('customer_phone', e.target.value)}
                                                placeholder={t('Phone')}
                                                className="h-8 text-xs"
                                                required
                                            />
                                            <InputError message={errors.customer_phone} />
                                        </div>

                                        {/* Address */}
                                        <div className="space-y-1">
                                            <Label htmlFor="customer_address" required className="text-xs">
                                                {t('Address')}
                                            </Label>
                                            <Input
                                                id="customer_address"
                                                value={data.customer_address}
                                                onChange={(e) => setData('customer_address', e.target.value)}
                                                placeholder={t('Address')}
                                                className="h-8 text-xs"
                                                required
                                            />
                                            <InputError message={errors.customer_address} />
                                        </div>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* 1. One-Time Charge (OTC) Card */}
                    <Card id="otc-section" className="transition-all duration-300">
                        <CardHeader className="flex flex-row items-center justify-between pb-3">
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                <FileText className="h-5 w-5" />
                                {t('One-Time Charges (OTC)')}
                            </CardTitle>
                            <div className="flex items-center gap-2">
                                <Label htmlFor="enable-tax-toggle" className="text-xs cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                    {t('Enable VAT/Tax')}
                                </Label>
                                <Switch
                                    id="enable-tax-toggle"
                                    size="sm"
                                    checked={data.is_tax_enabled}
                                    onCheckedChange={handleTaxToggle}
                                />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <ItemsTable
                                items={data.items.filter(i => i.section === 'otc' || i.section === 'general' || !i.section)}
                                products={availableProducts}
                                warehouseId={data.warehouse_id}
                                onChange={(updatedOtcItems) => {
                                    const formattedOtc = updatedOtcItems.map(i => ({ ...i, section: 'otc' }));
                                    const mrcItems = data.items.filter(i => i.section === 'mrc');
                                    setData('items', [...formattedOtc, ...mrcItems]);
                                }}
                                errors={errors}
                                onRefresh={refreshProducts}
                                isRefreshing={isRefreshingProducts}
                                isTaxEnabled={data.is_tax_enabled}
                                defaultSection="otc"
                                discountType={data.otc_discount_type}
                                discountValue={data.otc_discount_value}
                                onDiscountTypeChange={(val) => setData('otc_discount_type', val)}
                                onDiscountValueChange={(val) => setData('otc_discount_value', val)}
                            />
                        </CardContent>
                    </Card>

                    {/* 2. Monthly Recurring Charge (MRC) Card */}
                    <Card id="mrc-section" className="transition-all duration-300">
                        <CardHeader className="flex flex-row items-center justify-between pb-3">
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                <FileText className="h-5 w-5" />
                                {t('Monthly Recurring Charges (MRC)')}
                            </CardTitle>
                            <div className="flex items-center gap-2">
                                <Label htmlFor="prepaid-toggle" className="text-xs cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                                    {t('Prepaid')}
                                </Label>
                                <Switch
                                    id="prepaid-toggle"
                                    size="sm"
                                    checked={data.is_prepaid}
                                    onCheckedChange={(checked) => setData('is_prepaid', checked)}
                                />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <ItemsTable
                                items={data.items.filter(i => i.section === 'mrc')}
                                products={availableProducts}
                                warehouseId={data.warehouse_id}
                                onChange={(updatedMrcItems) => {
                                    const formattedMrc = updatedMrcItems.map(i => ({ ...i, section: 'mrc' }));
                                    const otcItems = data.items.filter(i => i.section !== 'mrc');
                                    setData('items', [...otcItems, ...formattedMrc]);
                                }}
                                errors={errors}
                                onRefresh={refreshProducts}
                                isRefreshing={isRefreshingProducts}
                                isTaxEnabled={data.is_tax_enabled}
                                defaultSection="mrc"
                                discountType={data.mrc_discount_type}
                                discountValue={data.mrc_discount_value}
                                onDiscountTypeChange={(val) => setData('mrc_discount_type', val)}
                                onDiscountValueChange={(val) => setData('mrc_discount_value', val)}
                            />
                        </CardContent>
                    </Card>

                    {/* Other Details Card */}
                    <Card id="other-details-section">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <FileText className="h-5 w-5" />
                                {t('Other Details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <RichTextEditor
                                content={data.other_details || ''}
                                onChange={(val) => setData('other_details', val)}
                                placeholder={t('Enter other details...')}
                            />
                        </CardContent>
                    </Card>

                    {/* Page Order Section */}
                    <PageOrder
                        sections={sections as any}
                        setSections={setSections as any}
                        defaultPages={defaultPages}
                        quotationSetting={activeSetting}
                    />

                    {/* Additional Notes Section */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">{t('Notes')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder={t('Enter additional notes...')}
                                    rows={4}
                                />
                                <InputError message={errors.notes} />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Render Custom Fields */}
                    {customFields && customFields.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">{t('Custom Fields')}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {customFields.map((field) => (
                                        <div key={field.id} className="space-y-2">
                                            <Label htmlFor={field.id}>{(field as any).name || (field as any).label || 'Custom Field'}</Label>
                                            {field.component}
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex items-center justify-between">
                        <div className="text-sm text-muted-foreground">
                            {data.items.length} {t('items')}
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
                                type="button"
                                variant="outline"
                                onClick={() => setIsPreviewOpen(true)}
                                className="flex items-center gap-1.5"
                            >
                                <Eye className="h-4 w-4" />
                                {t('Preview')}
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                            >
                                {processing ? t('Updating...') : t('Update')}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>

            <PreviewModal
                open={isPreviewOpen}
                onOpenChange={setIsPreviewOpen}
                formData={data as any}
                sections={sections as any}
                customers={customers}
                warehouses={warehouses}
                availableProducts={availableProducts}
                proposalSetting={activeSetting}
                totals={totals}
                other_details={data.other_details}
                showPrintButton={false}
            />
        </AuthenticatedLayout>
    );
}
