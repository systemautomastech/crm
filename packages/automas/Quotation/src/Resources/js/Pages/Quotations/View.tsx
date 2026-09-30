import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft, ShoppingCart, FileText, Edit, Printer, Send } from 'lucide-react';
import { useFormFields } from '@/hooks/useFormFields';
import ConvertToSalesOrderModal from './components/ConvertToSalesOrderModal';

interface SalesQuotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    due_date: string;
    customer_id?: number | null;
    customer_name?: string | null;
    customer_email?: string | null;
    customer_phone?: string | null;
    customer_address?: string | null;
    customer?: { id: number; name: string; email: string; phone?: string; address?: string } | null;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    status: string;
    converted_to_invoice: boolean;
    invoice_id?: number;
    sales_order_id?: number | null;
    notes?: string;
    payment_terms?: string;
    warehouse?: { id: number; name: string };
    items?: Array<{
        id: number;
        product_id: number;
        description?: string;
        product_description?: string;
        quantity: number;
        unit_price: number;
        discount_percentage: number;
        discount_amount: number;
        tax_percentage: number;
        tax_amount: number;
        total_amount: number;
        section?: 'otc' | 'mrc' | null;
        product?: {
            id: number;
            name: string;
            sku: string;
            description?: string;
            sale_price?: number;
            unit?: string;
            unitRelation?: { unit_name: string };
        };
    }>;
}

interface ViewProps {
    quotation: SalesQuotation;
    auth: any;
    isSalesOrderActive?: boolean;
    customers?: Array<{ id: number; name: string; email?: string }>;
    users?: Array<{ id: number; name: string }>;
    userGroups?: Array<{ id: number; name: string }>;
    [key: string]: any;
}

export default function View() {
    const { t } = useTranslation();
    const { quotation, auth, isSalesOrderActive, customers, users, userGroups } = usePage<ViewProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);

    useFlashMessages();

    useFormFields('getCustomFields', { ...quotation, module: 'Quotation', sub_module: 'Quotation', id: quotation.id }, () => { }, {}, 'view', t);

    const getquotationStatusColor = (status: string) => {
        switch (status?.toLowerCase()) {
            case 'draft': return 'bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-medium';
            case 'sent': return 'bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full text-xs font-medium';
            case 'accepted': return 'bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-medium';
            case 'rejected': return 'bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full text-xs font-medium';
            case 'expired': return 'bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-xs font-medium';
            default: return 'bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-medium';
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales quotation'), url: route('quotations.index') },
                { label: t('Sales quotation Details') }
            ]}
            pageTitle={`${t('Sales quotation')} #${quotation.quotation_number}`}
            pageActions={
                <div className="flex items-center gap-2">
                    {quotation.sales_order_id ? (
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.visit(route('salesorder.orders.show', quotation.sales_order_id!))}
                            className="text-blue-600 border-blue-200 hover:bg-blue-50"
                        >
                            <ShoppingCart className="h-4 w-4 mr-1.5" />
                            {t('View Sales Order')}
                        </Button>
                    ) : (
                        <>
                            {isSalesOrderActive && quotation.status === 'accepted' && (
                                <ConvertToSalesOrderModal
                                    quotation={quotation}
                                    customers={customers}
                                    users={users}
                                    userGroups={userGroups}
                                />
                            )}

                            {!quotation.converted_to_invoice && quotation.status === 'accepted' && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        if (confirm(t('Are you sure you want to convert this quotation directly to an invoice?'))) {
                                            router.post(route('quotations.convert-to-invoice', quotation.id));
                                        }
                                    }}
                                    className="text-emerald-700 border-emerald-300 hover:bg-emerald-50"
                                >
                                    <FileText className="h-4 w-4 mr-1.5" />
                                    {isSalesOrderActive ? t('Convert Directly to Invoice') : t('Convert to Invoice')}
                                </Button>
                            )}
                        </>
                    )}

                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => router.visit(route('quotations.index'))}
                    >
                        <ArrowLeft className="h-4 w-4 mr-1.5" />
                        {t('Back')}
                    </Button>
                </div>
            }
        >
            <Head title={`${t('Sales quotation')} #${quotation.quotation_number}`} />

            {isDownloading && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]">
                    <div className="bg-white p-6 rounded-lg shadow-lg">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p className="text-lg font-semibold text-gray-700">{t('Generating PDF...')}</p>
                        </div>
                    </div>
                </div>
            )}

            <div className="space-y-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex justify-between items-center mb-6">
                            <div>
                                <p className="text-lg text-muted-foreground">#{quotation.quotation_number}</p>
                            </div>
                            <div className="flex items-center gap-4">
                                <span className={getquotationStatusColor(quotation.status)}>
                                    {quotation.status?.charAt(0).toUpperCase() + quotation.status?.slice(1)}
                                </span>
                                <div className="text-right">
                                    <div className="text-2xl font-bold">{formatCurrency(quotation.total_amount)}</div>
                                    <div className="text-sm text-muted-foreground">{t('Total Amount')}</div>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h3 className="font-semibold mb-2">{t('CUSTOMER')}</h3>
                                <div className="text-sm space-y-1">
                                    <div className="font-medium">{quotation.customer?.name || quotation.customer_name || '-'}</div>
                                    <div className="text-muted-foreground">{quotation.customer?.email || quotation.customer_email || ''}</div>
                                    {(quotation.customer?.phone || quotation.customer_phone) && (
                                        <div className="text-xs text-muted-foreground">{quotation.customer?.phone || quotation.customer_phone}</div>
                                    )}
                                    {(quotation.customer?.address || quotation.customer_address) && (
                                        <div className="text-xs text-muted-foreground">{quotation.customer?.address || quotation.customer_address}</div>
                                    )}
                                </div>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">{t('DETAILS')}</h3>
                                <div className="space-y-1 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('quotation Date')}</span>
                                        <span>{formatDate(quotation.quotation_date)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Due Date')}</span>
                                        <span className={new Date(quotation.due_date) < new Date() ? 'text-red-600' : ''}>
                                            {formatDate(quotation.due_date)}
                                        </span>
                                    </div>
                                    {quotation.warehouse && (
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">{t('Warehouse')}</span>
                                            <span>{quotation.warehouse.name}</span>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4 p-3 bg-blue-50 rounded">
                                    <div className="flex justify-between items-center">
                                        <div className="flex flex-wrap gap-2">
                                            {auth?.user?.permissions?.includes('edit-quotations') && quotation.status !== 'accepted' && !quotation.converted_to_invoice && (
                                                <Button
                                                    size="sm"
                                                    onClick={() => router.visit(route('quotations.edit', quotation.id))}
                                                >
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    {t('Edit')}
                                                </Button>
                                            )}
                                            {auth?.user?.permissions?.includes('sent-quotations') && (quotation.status === 'draft' || quotation.status === 'sent') && (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() => router.post(route('quotations.sent', quotation.id))}
                                                    className="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200"
                                                >
                                                    <Send className="h-4 w-4 mr-2" />
                                                    {quotation.status === 'sent' ? t('Resend quotation') : t('Send quotation')}
                                                </Button>
                                            )}
                                            {auth?.user?.permissions?.includes('print-quotations') && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => window.open(route('quotations.print', quotation.id) + '?print=1', '_blank')}
                                                >
                                                    <Printer className="h-4 w-4 mr-2" />
                                                    {t('Print/Download')}
                                                </Button>
                                            )}
                                        </div>
                                        <div className="text-right">
                                            <div className="text-xl font-bold text-blue-600">{formatCurrency(quotation.total_amount)}</div>
                                            <div className="text-sm text-muted-foreground">{t('Quotation Amount')}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 border-t pt-6">
                            <h3 className="font-semibold mb-4">{t('ORDER ITEMS')}</h3>
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="text-xs uppercase bg-gray-50 border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Product')}</th>
                                            <th className="px-4 py-3 text-right">{t('Qty')}</th>
                                            <th className="px-4 py-3 text-right">{t('Unit Price')}</th>
                                            <th className="px-4 py-3 text-right">{t('Discount')}</th>
                                            <th className="px-4 py-3 text-right">{t('Tax')}</th>
                                            <th className="px-4 py-3 text-right">{t('Total')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {quotation.items?.map((item) => (
                                            <tr key={item.id}>
                                                <td className="px-4 py-4">
                                                    <div className="font-medium text-gray-900">
                                                        {item.product?.name || item.description || '-'}
                                                    </div>
                                                    {item.description && (
                                                        <div className="text-sm text-muted-foreground mt-1 leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-1.5 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-1.5 [&_li]:my-0.5 [&_li]:list-item [&_li_p]:inline [&_li_p]:m-0 [&_p]:my-1" dangerouslySetInnerHTML={{ __html: item.description }}></div>
                                                    )}
                                                </td>
                                                <td className="px-4 py-4 text-right">{item.quantity}</td>
                                                <td className="px-4 py-4 text-right">{formatCurrency(item.unit_price)}</td>
                                                <td className="px-4 py-4 text-right">
                                                    {item.discount_percentage > 0 ? `${item.discount_percentage}%` : '-'}
                                                </td>
                                                <td className="px-4 py-4 text-right">
                                                    {item.tax_percentage > 0 ? `${item.tax_percentage}%` : '-'}
                                                </td>
                                                <td className="px-4 py-4 text-right font-semibold">
                                                    {formatCurrency(item.total_amount)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <div className="w-80 space-y-3">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">{t('Subtotal')}</span>
                                    <span className="font-medium">{formatCurrency(quotation.subtotal)}</span>
                                </div>
                                {quotation.discount_amount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Discount')}</span>
                                        <span className="font-medium text-red-600">-{formatCurrency(quotation.discount_amount)}</span>
                                    </div>
                                )}
                                {quotation.tax_amount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Tax')}</span>
                                        <span className="font-medium">{formatCurrency(quotation.tax_amount)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-3">
                                    <div className="flex justify-between">
                                        <span className="font-semibold">{t('Total Amount')}</span>
                                        <span className="font-bold text-lg">{formatCurrency(quotation.total_amount)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
