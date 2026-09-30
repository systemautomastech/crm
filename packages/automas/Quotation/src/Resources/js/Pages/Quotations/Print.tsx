import React, { useMemo } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import PreviewModal, { ProposalPreviewSection as QuotationPreviewSection } from '@/components/PreviewModal';
import { QuotationDefaultPage, SalesQuotation } from './types';

interface PrintProps {
    quotation: SalesQuotation;
    customers?: Array<{
        id: number;
        name: string;
        email: string;
        address?: string;
        mobile_no?: string;
        phone?: string;
    }>;
    warehouses?: Array<{
        id: number;
        name: string;
        address?: string;
    }>;
    defaultPages?: QuotationDefaultPage[];
    quotationSetting?: any;
    autoPrint?: boolean;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const pageProps = usePage<PrintProps>().props;
    const {
        quotation,
        customers = [],
        warehouses = [],
        defaultPages = [],
        quotationSetting,
        autoPrint = false,
    } = pageProps;

    const sections = useMemo<QuotationPreviewSection[]>(() => {
        let pages: any[] = [];

        /* 1. Quotation Contents Relation */
        if (Array.isArray(quotation?.contents) && quotation.contents.length > 0) {
            pages = quotation.contents.map((item: any, index: number) => {
                const rawContent = item.content || item.quotation_content || '';
                let parsed: any = null;

                if (rawContent) {
                    try {
                        parsed = typeof rawContent === 'string' ? JSON.parse(rawContent) : rawContent;
                    } catch {
                        parsed = null;
                    }
                }

                if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                    return {
                        id: String(item.id || `content-${index}`),
                        title: parsed.title || item.title || '',
                        content: parsed.content || rawContent || '',
                        page_type: parsed.page_type || item.page_type || 'general',
                        background_image: parsed.background_image || item.background_image || '',
                        order: parsed.order ?? item.order ?? index + 1,
                    };
                }

                return {
                    id: String(item.id || `content-${index}`),
                    title: item.title || '',
                    content: rawContent,
                    page_type: item.page_type || 'general',
                    background_image: item.background_image || '',
                    order: item.order ?? index + 1,
                };
            });
        }

        console.log(pages, "page from quotation content");

        /* 2. Old Proposal / Quotation Content Format */
        if (pages.length === 0) {
            const rawContent = quotation?.quotation_content || quotation?.others;
            if (typeof rawContent === 'string') {
                try {
                    const parsed = JSON.parse(rawContent);
                    if (Array.isArray(parsed)) {
                        pages = parsed;
                    }
                } catch {
                    pages = [];
                }
            } else if (Array.isArray(rawContent)) {
                pages = rawContent;
            }
        }

        /* 3. Default Pages Fallback */
        if (pages.length === 0 && defaultPages.length > 0) {
            pages = defaultPages.map((page, index) => ({
                id: String(page.id),
                title: page.title,
                content: page.content || '',
                page_type: page.page_type || 'general',
                background_image: page.background_image || '',
                order: Number(page.sort_order) || index + 1,
            }));
        }

        /* Sort Pages */
        pages.sort((a: any, b: any) => Number(a.order || 0) - Number(b.order || 0));

        /* Normalize Final Data */
        return pages.map((item: any, index: number): QuotationPreviewSection => {
            const pageType = item.page_type || 'general';
            return {
                id: String(item.id || `section-${index}`),
                title: item.title || getDefaultSectionTitle(pageType, index, t),
                content: item.content || '',
                page_type: pageType,
                background_image: item.background_image || '',
                order: Number(item.order) || index + 1,
            };
        });
    }, [quotation, defaultPages, t]);

    const formattedCustomers = useMemo(() => {
        if (quotation?.customer) {
            const customer = quotation.customer;
            return [
                {
                    id: customer.id,
                    name: customer.name || quotation.customer_name || '',
                    email: customer.email || quotation.customer_email || '',
                    mobile_no: customer.mobile_no || customer.phone || quotation.customer_phone || '',
                    phone: customer.mobile_no || customer.phone || quotation.customer_phone || '',
                    address: customer.address || quotation.customer_address || '',
                },
            ];
        }
        if (quotation?.customer_name || quotation?.customer_email || quotation?.customer_phone || quotation?.customer_address) {
            return [
                {
                    id: 0,
                    name: quotation.customer_name || '',
                    email: quotation.customer_email || '',
                    mobile_no: quotation.customer_phone || '',
                    phone: quotation.customer_phone || '',
                    address: quotation.customer_address || '',
                },
            ];
        }
        return customers;
    }, [quotation, customers]);

    const totals = useMemo(() => {
        const items = quotation?.items || [];

        const otcItems = items.filter(
            (item: any) => item.section === 'otc' || item.section === 'general' || !item.section
        );
        const mrcItems = items.filter((item: any) => item.section === 'mrc');

        const calculateSubtotal = (list: any[]) => {
            return list.reduce((total: number, item: any) => {
                const quantity = Number(item.quantity || 1);
                const price = Number(item.unit_price || 0);
                return total + quantity * price;
            }, 0);
        };

        const otcSubtotal = calculateSubtotal(otcItems);
        const mrcSubtotal = calculateSubtotal(mrcItems);

        const calculateDiscount = (subtotal: number, type: string, value: any) => {
            const discountValue = Math.max(Number(value) || 0, 0);
            if (type === 'percentage') {
                return (subtotal * Math.min(discountValue, 100)) / 100;
            }
            return Math.min(discountValue, subtotal);
        };

        const otcDiscount = calculateDiscount(
            otcSubtotal,
            quotation?.otc_discount_type,
            quotation?.otc_discount_value
        );

        const mrcDiscount = calculateDiscount(
            mrcSubtotal,
            quotation?.mrc_discount_type,
            quotation?.mrc_discount_value
        );

        const calculateTax = (list: any[]) => {
            return list.reduce((total: number, item: any) => total + Number(item.tax_amount || 0), 0);
        };

        const otcTax = calculateTax(otcItems);
        const mrcTax = calculateTax(mrcItems);

        const calculatedSubtotal = otcSubtotal + mrcSubtotal;
        const calculatedDiscount = otcDiscount + mrcDiscount;
        const calculatedTax = otcTax + mrcTax;

        const subtotal = Number(quotation?.subtotal) || calculatedSubtotal;
        const discountAmount = Number(quotation?.discount_amount) || calculatedDiscount;
        const taxAmount = Number(quotation?.tax_amount) || calculatedTax;
        const calculatedTotal = Math.max(0, subtotal - discountAmount + taxAmount);
        const total = Number(quotation?.total_amount) || calculatedTotal;

        return {
            subtotal,
            discount_amount: discountAmount,
            discountAmount,
            tax_amount: taxAmount,
            taxAmount,
            total_amount: total,
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
    }, [quotation]);

    const formData = useMemo(() => {
        return {
            ...quotation,
            id: quotation?.id,
            proposal_id: quotation?.id,
            proposal_number: quotation?.quotation_number || '',
            quotation_number: quotation?.quotation_number || '',
            subject: quotation?.subject || '',
            invoice_date: quotation?.quotation_date || quotation?.invoice_date || '',
            due_date: quotation?.due_date || '',
            customer_id: quotation?.customer_id ?? quotation?.customer?.id,
            warehouse_id: quotation?.warehouse_id,
            payment_terms: quotation?.payment_terms || '',
            notes: quotation?.notes || '',
            other_details: quotation?.other_details || '',
            otc_discount_type: quotation?.otc_discount_type,
            otc_discount_value: quotation?.otc_discount_value,
            mrc_discount_type: quotation?.mrc_discount_type,
            mrc_discount_value: quotation?.mrc_discount_value,
            items: (quotation?.items || []).map((item: any) => {
                const quantity = Number(item.quantity || 1);
                const unitPrice = Number(item.unit_price || 0);

                return {
                    id: item.id,
                    product_id: item.product_id,
                    product_name: item.product?.name || item.product_name || item.name || '',
                    name: item.name || item.product_name || item.product?.name || '',
                    description: item.description || item.product_description || item.product?.description || '',
                    product_description: item.product_description || item.description || item.product?.description || '',
                    quantity,
                    unit_price: unitPrice,
                    discount_type: item.discount_type || 'percentage',
                    discount_percentage: Number(item.discount_percentage || 0),
                    discount_amount: Number(item.discount_amount || 0),
                    tax_amount: Number(item.tax_amount || 0),
                    total_amount: item.total_amount !== undefined ? Number(item.total_amount) : quantity * unitPrice,
                    section: item.section || 'otc',
                    product: item.product,
                    unit: item.unit,
                    unit_name: item.unit_name,
                };
            }),
        };
    }, [quotation]);

    return (
        <>
            <Head
                title={(() => {
                    const subject = quotation?.subject || '';
                    const customerName = quotation?.customer?.name || quotation?.customer_name || formattedCustomers[0]?.name || '';
                    const parts = [subject, customerName].filter(Boolean);
                    if (parts.length > 0) return parts.join('_');
                    return quotation?.quotation_number || t('Sales Quotation');
                })()}
            />
            <PreviewModal
                inline
                autoPrint={autoPrint || true}
                hideHeaderBar
                title={(() => {
                    const subject = quotation?.subject || '';
                    const customerName = quotation?.customer?.name || quotation?.customer_name || formattedCustomers[0]?.name || '';
                    const parts = [subject, customerName].filter(Boolean);
                    return parts.length > 0 ? parts.join('_') : (quotation?.quotation_number || '');
                })()}
                formData={formData}
                sections={sections}
                customers={formattedCustomers}
                warehouses={warehouses}
                totals={totals}
                proposalSetting={quotationSetting}
                other_details={quotation?.other_details}
                pageProps={pageProps}
            />
        </>
    );
}

function getDefaultSectionTitle(
    pageType: string,
    index: number,
    t: (key: string) => string
): string {
    switch (pageType) {
        case 'otc':
            return t('One-Time Charges (OTC)');
        case 'mrc':
            return t('Monthly Recurring Charges (MRC)');
        case 'other-details':
            return t('Other Details');
        default:
            return `${t('Page')} ${index + 1}`;
    }
}
