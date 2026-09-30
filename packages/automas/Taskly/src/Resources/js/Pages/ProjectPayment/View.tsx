import React from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { ProjectPayment } from './types';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { FileText, Download } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface ViewProps {
    payment: ProjectPayment;
    auth: {
        user: {
            permissions?: string[];
        };
    };
}

export default function View() {
    const { t } = useTranslation();
    const { payment, auth } = usePage<ViewProps>().props;

    useFlashMessages();

    const getStatusBadgeClasses = (status: string) => {
        const baseClasses = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium';
        const statusClasses: Record<string, string> = {
            draft: 'bg-gray-100 text-gray-800',
            posted: 'bg-blue-100 text-blue-800',
        };
        return `${baseClasses} ${statusClasses[status] || statusClasses.draft}`;
    };

    const downloadPDF = () => {
        const printUrl = route('project-payments.print', payment.id) + '?download=pdf';
        window.open(printUrl, '_blank');
    };



    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Project'), url: route('project.dashboard.index')},
                {label: t('Project Payments'), url: route('project-payments.index')},
                {label: t('Project Payment Details')}
            ]}
            pageTitle={`${t('Project Payment')} #${payment.payment_number}`}
        >
            <Head title={`${t('Project Payment')} #${payment.payment_number}`} />

            <div className="space-y-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex justify-between items-center mb-6">
                            <div>
                                <p className="text-lg text-muted-foreground">#{payment.payment_number}</p>
                            </div>
                            <div className="flex items-center gap-4">
                                <span className={getStatusBadgeClasses(payment.status)}>
                                    {t(payment.status.toUpperCase())}
                                </span>
                                <div className="text-right">
                                    <div className="text-2xl font-bold">{formatCurrency(payment.total_amount)}</div>
                                    <div className="text-sm text-muted-foreground">{t('Total Amount')}</div>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h3 className="font-semibold mb-2">{t('PROJECT')}</h3>
                                <div className="text-sm space-y-1">
                                    <div className="font-medium">{payment.project?.name}</div>
                                </div>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">{t('CUSTOMER')}</h3>
                                <div className="text-sm space-y-1">
                                    <div className="font-medium">{payment.customer?.name}</div>
                                    <div className="text-muted-foreground">{payment.customer?.email}</div>
                                </div>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">{t('DETAILS')}</h3>
                                <div className="space-y-1 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Payment Date')}</span>
                                        <span>{formatDate(payment.payment_date)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Due Date')}</span>
                                        <span className={new Date(payment.due_date) < new Date() ? 'text-red-600' : ''}>
                                            {formatDate(payment.due_date)}
                                        </span>
                                    </div>
                                    {payment.payment_terms && (
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">{t('Terms')}</span>
                                            <span>{payment.payment_terms}</span>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4 p-3 bg-blue-50 rounded">
                                    <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                        <div className="flex flex-wrap gap-2">
                                            {auth.user?.permissions?.includes('print-project-payments') && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={downloadPDF}
                                                >
                                                    <Download className="h-4 w-4 mr-2" />
                                                    {t('Download PDF')}
                                                </Button>
                                            )}
                                            {payment.status === 'draft' && auth.user?.permissions?.includes('post-project-payments') && (
                                                <TooltipProvider>
                                                    <Tooltip delayDuration={0}>
                                                        <TooltipTrigger asChild>
                                                            <Button
                                                                size="sm"
                                                                onClick={() => router.post(route('project-payments.post', payment.id), {}, {
                                                                    onSuccess: () => {
                                                                        router.reload();
                                                                    }
                                                                })}
                                                            >
                                                                <FileText className="h-4 w-4 mr-2" />
                                                                {t('Post Payment')}
                                                            </Button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>{t('Post payment to finalize')}</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            )}
                                        </div>
                                        <div className="text-right sm:text-right">
                                            <div className="text-lg sm:text-xl font-bold text-blue-600">{formatCurrency(payment.balance_amount)}</div>
                                            <div className="text-xs sm:text-sm text-muted-foreground">{t('Balance Due')}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {payment.notes && (
                            <div className="mt-4 pt-4 border-t">
                                <span className="font-medium text-sm">{t('Notes')}:</span>
                                <span className="text-sm text-muted-foreground ml-2">{payment.notes}</span>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <h3 className="text-lg font-semibold">
                            {t('Payment Items')}
                        </h3>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-3 text-left text-sm font-semibold">{t('Milestone')}</th>
                                        <th className="px-4 py-3 text-right text-sm font-semibold">{t('Price')}</th>
                                        <th className="px-4 py-3 text-right text-sm font-semibold">{t('Discount')}</th>
                                        <th className="px-4 py-3 text-right text-sm font-semibold">{t('Total')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {payment.items?.map((item, index) => (
                                        <tr key={index}>
                                            <td className="px-4 py-4">
                                                <div className="font-medium">{item.milestone?.title || '-'}</div>
                                            </td>
                                            <td className="px-4 py-4 text-right">{formatCurrency(item.price)}</td>
                                            <td className="px-4 py-4 text-right">
                                                {item.discount_percentage > 0 ? (
                                                    <div>
                                                        <div>{item.discount_percentage}%</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            -{formatCurrency(item.discount_amount)}
                                                        </div>
                                                    </div>
                                                ) : '-'}
                                            </td>
                                            <td className="px-4 py-4 text-right font-semibold">
                                                {formatCurrency(item.total_amount)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <div className="w-80 space-y-3">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">{t('Subtotal')}</span>
                                    <span className="font-medium">{formatCurrency(payment.subtotal)}</span>
                                </div>
                                {payment.discount_amount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Discount')}</span>
                                        <span className="font-medium text-red-600">-{formatCurrency(payment.discount_amount)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-3">
                                    <div className="flex justify-between">
                                        <span className="font-semibold">{t('Total Amount')}</span>
                                        <span className="font-bold text-lg">{formatCurrency(payment.total_amount)}</span>
                                    </div>
                                </div>
                                {payment.paid_amount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Paid Amount')}</span>
                                        <span className="font-medium text-green-600">{formatCurrency(payment.paid_amount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between">
                                    <span className="font-semibold">{t('Balance Due')}</span>
                                    <span className="font-bold text-lg">{formatCurrency(payment.balance_amount)}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>


        </AuthenticatedLayout>
    );
}
