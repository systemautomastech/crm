import React, { useState, useEffect, useMemo } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { route } from 'ziggy-js';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { QuotationItem, QuotationDefaultPage } from './types';
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
import axios from 'axios';
import { toast } from 'sonner';
import { CalendarDays, FileText, Eye, User, Users, UserPlus, X, Plus, Loader2, Save, Tag, Search, Check, ChevronsUpDown } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { replaceUserShortcodes } from './utils/quotationShortcodes';


interface CreateProps {
    customers: Array<{ id: number; name: string; email: string;[key: string]: any }>;
    warehouses: Array<{ id: number; name: string; address: string }>;
    defaultPages?: QuotationDefaultPage[];
    defaultTerms?: string | null;
    quotationSetting?: any;
    QuotationSetting?: any;
    [key: string]: any;
}

export default function Create() {
    const { t } = useTranslation();
    const pageProps = usePage<CreateProps>().props;
    const { customers, warehouses, defaultPages = [], subjects = [], defaultTerms, quotationSetting, QuotationSetting } = pageProps;
    const authUser = (pageProps as any)?.auth?.user;
    const activeSetting = quotationSetting || QuotationSetting;
    const [availableProducts, setAvailableProducts] = useState<any[]>([]);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const [subjectList, setSubjectList] = useState<Array<{ id: number; name: string }>>(subjects);

    useEffect(() => {
        if (subjects) {
            setSubjectList(subjects);
        }
    }, [subjects]);

    const [subjectSearchQuery, setSubjectSearchQuery] = useState('');
    const [isSubjectPopoverOpen, setIsSubjectPopoverOpen] = useState(false);

    const filteredSubjects = useMemo(() => {
        if (!subjectSearchQuery.trim()) return subjectList;
        const q = subjectSearchQuery.toLowerCase();
        return subjectList.filter((s) => s.name.toLowerCase().includes(q));
    }, [subjectList, subjectSearchQuery]);


    // Helper to build active sections strictly following defaultPages order rule
    const buildSectionsFromDefaultPages = (itemsList: QuotationItem[], otherDetailsContent: string) => {
        const hasOtc = itemsList.some(
            (i) => (i.section === 'otc' || i.section === 'general' || !i.section) && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
        );
        const hasMrc = itemsList.some(
            (i) => i.section === 'mrc' && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
        );
        const hasOther = Boolean(otherDetailsContent && otherDetailsContent.trim() !== '' && otherDetailsContent !== '<p></p>');

        if (!defaultPages || defaultPages.length === 0) return [];

        const activePages = defaultPages.filter((p) => {
            if (p.page_type === 'otc') return hasOtc;
            if (p.page_type === 'mrc') return hasMrc;
            if (p.page_type === 'other-details') return hasOther;
            return true;
        });

        activePages.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));

        let res = activePages.map((p, idx) => ({
            id: `sec-${p.page_type || 'page'}-${p.id || idx}`,
            default_page_id: p.id,
            title: p.title || (p.page_type === 'otc' ? 'One-Time Charges (OTC)' : p.page_type === 'mrc' ? 'Monthly Recurring Charges (MRC)' : p.page_type === 'other-details' ? 'Other Details' : `Page ${idx + 1}`),
            content: p.page_type === 'otc' ? '[OTC_CHARGES_TABLE]' : (p.page_type === 'mrc' ? '[MRC_CHARGES_TABLE]' : (p.page_type === 'other-details' ? '[OTHER_DETAILS_CONTENT]' : (replaceUserShortcodes(p.content, authUser) || ''))),
            page_type: p.page_type || 'general',
            background_image: p.background_image || '',
            order: idx + 1,
            isExpanded: false,
        }));

        if (hasOther && !res.some((s) => s.page_type === 'other-details')) {
            res.push({
                id: `sec-other-details-custom`,
                default_page_id: undefined as any,
                title: 'Other Details',
                content: '[OTHER_DETAILS_CONTENT]',
                page_type: 'other-details',
                background_image: '',
                order: res.length + 1,
                isExpanded: false,
            });
        }

        return res;
    };

    // Initialize Quotation sections from default pages
    const [sections, setSections] = useState<Array<{ id: string; title: string; content: string; page_type?: string; background_image?: string; order: number; isExpanded: boolean; default_page_id?: number }>>(() => {
        return buildSectionsFromDefaultPages([], '');
    });

    useFlashMessages();
    const { data, setData, post, processing, errors, transform } = useForm({
        quotation_id: '',
        reference: '',
        subject: '',
        quotation_date: new Date().toISOString().split('T')[0],
        due_date: '',
        customer_type: 'existing' as 'existing' | 'new',
        customer_id: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        customer_address: '',
        warehouse_id: '',
        is_tax_enabled: true,
        is_prepaid: false,
        otc_discount_type: 'percentage' as 'percentage' | 'fixed',
        otc_discount_value: 0,
        mrc_discount_type: 'percentage' as 'percentage' | 'fixed',
        mrc_discount_value: 0,
        payment_terms: '',
        notes: '',
        items: [] as QuotationItem[],
        other_details: '',
    });

    // Auto-sync OTC, MRC, and Other Details section cards into Page Order list strictly following defaultPages order rules
    useEffect(() => {
        setSections((prev) => {
            const hasOtc = data.items.some(
                (i) => (i.section === 'otc' || i.section === 'general' || !i.section) && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
            );
            const hasMrc = data.items.some(
                (i) => i.section === 'mrc' && (Number(i.product_id) > 0 || (typeof i.product_description === 'string' && i.product_description.trim() !== '') || (typeof i.description === 'string' && i.description.trim() !== '') || Number(i.unit_price) > 0)
            );
            const hasOther = Boolean(data.other_details && data.other_details.trim() !== '' && data.other_details !== '<p></p>');

            const currentHasOtc = prev.some((s) => s.page_type === 'otc');
            const currentHasMrc = prev.some((s) => s.page_type === 'mrc');
            const currentHasOther = prev.some((s) => s.page_type === 'other-details');

            if (hasOtc === currentHasOtc && hasMrc === currentHasMrc && hasOther === currentHasOther) {
                return prev;
            }

            const defaultOtc = defaultPages?.find((p) => p.page_type === 'otc');
            const defaultMrc = defaultPages?.find((p) => p.page_type === 'mrc');
            const defaultOther = defaultPages?.find((p) => p.page_type === 'other-details');

            let nextSections = [...prev];

            // 1. Remove sections if items no longer exist
            if (!hasOtc && currentHasOtc) {
                nextSections = nextSections.filter((s) => s.page_type !== 'otc');
            }
            if (!hasMrc && currentHasMrc) {
                nextSections = nextSections.filter((s) => s.page_type !== 'mrc');
            }
            if (!hasOther && currentHasOther) {
                nextSections = nextSections.filter((s) => s.page_type !== 'other-details');
            }

            // 2. Add OTC if items exist and not present
            if (hasOtc && !currentHasOtc) {
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
            if (hasMrc && !currentHasMrc) {
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
            if (hasOther && !currentHasOther) {
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

            // 5. Strictly sort all sections based on their defaultPages sort_order
            const getDefOrder = (sec: any) => {
                const def = defaultPages?.find((dp) => 
                    (sec.default_page_id && dp.id === sec.default_page_id) ||
                    (sec.page_type && ['otc', 'mrc', 'other-details'].includes(sec.page_type) && dp.page_type === sec.page_type) ||
                    (dp.title && sec.title && dp.title.trim().toLowerCase() === sec.title.trim().toLowerCase())
                );
                return def?.sort_order !== undefined ? Number(def.sort_order) : 1000 + (Number(sec.order) || 0);
            };

            nextSections.sort((a, b) => getDefOrder(a) - getDefOrder(b));
            return nextSections.map((s, idx) => ({ ...s, order: idx + 1 }));
        });
    }, [data.items, data.other_details, defaultPages]);

    // Fetch warehouse products
    const [isRefreshingProducts, setIsRefreshingProducts] = useState(false);
    const fetchWarehouseProducts = async (warehouseId: string) => {
        if (!warehouseId) {
            setAvailableProducts([]);
            return;
        }

        try {
            setIsRefreshingProducts(true);
            setAvailableProducts([]);
            const response = await fetch(route('quotations.warehouse.products') + `?warehouse_id=${warehouseId}`);
            if (!response.ok) throw new Error('Failed to fetch products');
            const data = await response.json();
            setAvailableProducts(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error('Error fetching warehouse products:', error);
            setAvailableProducts([]);
        } finally {
            setIsRefreshingProducts(false);
        }
    };

    const refreshProducts = () => {
        if (data.warehouse_id) {
            fetchWarehouseProducts(data.warehouse_id);
        }
    };

    useEffect(() => {
        if (data.warehouse_id) {
            fetchWarehouseProducts(data.warehouse_id);
        }
    }, [data.warehouse_id]);

    const handleWarehouseChange = (value: string) => {
        setData((prev) => ({
            ...prev,
            warehouse_id: value,
            items: prev.items.map((item) => ({
                ...item,
                product_id: 0,
                unit_price: 0,
                total_amount: 0,
            })),
        }));
    };

    const handleTaxToggle = (checked: boolean) => {
        setData('is_tax_enabled', checked);
        const updatedItems = data.items.map((item) => {
            const qty = Number(item.quantity) || 0;
            const price = Number(item.unit_price) || 0;
            const lineTotal = qty * price;

            if (!checked) {
                return {
                    ...item,
                    discount_percentage: 0,
                    discount_amount: 0,
                    tax_percentage: 0,
                    tax_amount: 0,
                    total_amount: lineTotal,
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
            const taxAmount = (lineTotal * taxRate) / 100;

            return {
                ...item,
                discount_percentage: 0,
                discount_amount: 0,
                tax_percentage: taxRate,
                tax_amount: taxAmount,
                total_amount: lineTotal + taxAmount,
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
            quotation_date: formData.quotation_date,
            contents: sections.map((item, index) => ({
                title: replaceUserShortcodes(item.title, authUser),
                content: replaceUserShortcodes(
                    item.page_type === 'other-details' ? (data.other_details || item.content || '') : item.content,
                    authUser
                ),
                page_type: item.page_type || 'content',
                background_image: item.background_image || '',
                sort_order: index + 1,
            })),
        }));

        post(route('quotations.store'));
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
        if (!data.customer_id || !Array.isArray(customers)) return null;
        return customers.find((c: any) => c && c.id !== undefined && c.id !== null && String(c.id) === String(data.customer_id)) || null;
    }, [data.customer_type, data.customer_id, data.customer_name, data.customer_email, data.customer_phone, data.customer_address, customers]);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales Quotation'), url: route('quotations.index') },
                { label: t('Create') }
            ]}
            pageTitle={t('Create Sales Quotation')}
        >
            <Head title={t('Create Sales Quotation')} />

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
                                        value={`${activeSetting?.quotation_prefix ?? 'QT-'}${activeSetting?.quotation_starting_number ?? '1001'}-${data.quotation_date || new Date().toISOString().split('T')[0]}`}
                                        readOnly
                                        className="bg-muted cursor-not-allowed font-mono text-xs"
                                    />
                                </div>

                                <div className="w-full flex-1">
                                    <Label htmlFor="subject" required className="mb-1.5">
                                        {t('Subject')}
                                    </Label>

                                    <Select
                                        value={data.subject}
                                        onValueChange={(value) => setData('subject', value)}
                                    >
                                        <SelectTrigger id="subject" className="w-full">
                                            <SelectValue placeholder={t('Select Quotation Subject')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            {subjectList.map((subj) => (
                                                <SelectItem key={subj.id} value={subj.name}>
                                                    {subj.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
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
                                            className="text-[11px] font-semibold text-primary flex items-center gap-1 cursor-pointer transition-colors"
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
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center justify-end gap-3">
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
                        <Button type="submit" disabled={processing}>
                            {t('Create')}
                        </Button>
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
