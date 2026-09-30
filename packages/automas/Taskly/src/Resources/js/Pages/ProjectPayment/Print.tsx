import React, { useEffect, useState, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate, getCompanySetting } from '@/utils/helpers';
import type { ProjectPayment } from './types';

interface PrintProps {
    payment: ProjectPayment;
}

export default function Print() {
    const { t } = useTranslation();
    const { payment } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const downloadInitiatedRef = useRef(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && !downloadInitiatedRef.current) {
            downloadInitiatedRef.current = true;
            setTimeout(() => downloadPDF(), 500);
        }
    }, []);

    const downloadPDF = async () => {
        if (isDownloading) return;
        setIsDownloading(true);

        const printContent = document.querySelector('.payment-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `project-payment-${payment.payment_number}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' as const }
            };

            try {
                await html2pdf().set(opt).from(printContent as HTMLElement).save();
                setTimeout(() => window.close(), 1000);
            } catch (error) {
                console.error('PDF generation failed:', error);
            }
        }

        setIsDownloading(false);
    };

    return (
        <div className="min-h-screen bg-white">
            <Head title={t('Project Payment')} />

            {isDownloading && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-lg">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p className="text-lg font-semibold text-gray-700">{t('Generating PDF...')}</p>
                        </div>
                    </div>
                </div>
            )}

            <div className="payment-container bg-white max-w-4xl mx-auto p-8">
                <div className="flex justify-between items-start mb-8">
                    <div className="w-1/2">
                        <h1 className="text-2xl font-bold mb-4">{getCompanySetting('company_name') || 'YOUR COMPANY'}</h1>
                        <div className="text-sm space-y-1">
                            {getCompanySetting('company_address') && <p>{getCompanySetting('company_address')}</p>}
                            {(getCompanySetting('company_city') || getCompanySetting('company_state') || getCompanySetting('company_zipcode')) && (
                                <p>
                                    {getCompanySetting('company_city')}{getCompanySetting('company_state') && `, ${getCompanySetting('company_state')}`} {getCompanySetting('company_zipcode')}
                                </p>
                            )}
                            {getCompanySetting('company_country') && <p>{getCompanySetting('company_country')}</p>}
                            {getCompanySetting('company_telephone') && <p>{t('Phone')}: {getCompanySetting('company_telephone')}</p>}
                            {getCompanySetting('company_email') && <p>{t('Email')}: {getCompanySetting('company_email')}</p>}
                            {getCompanySetting('registration_number') && <p>{t('Registration')}: {getCompanySetting('registration_number')}</p>}
                        </div>
                    </div>
                    <div className="text-right w-1/2">
                        <h2 className="text-2xl font-bold mb-2">{t('PROJECT PAYMENT')}</h2>
                        <p className="text-lg font-semibold">#{payment.payment_number}</p>
                        <div className="text-sm mt-2 space-y-1">
                            <p>{t('Date')}: {formatDate(payment.payment_date)}</p>
                            <p>{t('Due')}: {formatDate(payment.due_date)}</p>
                            <p className="capitalize">{t('Status')}: <span className="font-semibold">{t(payment.status.charAt(0).toUpperCase() + payment.status.slice(1))}</span></p>
                        </div>
                    </div>
                </div>

                <div className="flex justify-between mb-8">
                    <div className="w-1/2">
                        <h3 className="font-bold mb-3">{t('BILL TO')}</h3>
                        <div className="text-sm space-y-1">
                            <p className="font-semibold">{payment.customer?.name}</p>
                            <p>{payment.customer?.email}</p>
                        </div>
                    </div>
                    <div className="text-right w-1/2">
                        <h3 className="font-bold mb-3">{t('PROJECT')}</h3>
                        <div className="text-sm space-y-1">
                            <p className="font-semibold">{payment.project?.name}</p>
                        </div>
                    </div>
                </div>

                <div className="mb-8">
                    <table className="w-full table-fixed">
                        <thead>
                            <tr className="border-b border-gray-300">
                                <th className="text-left py-3 font-bold">{t('MILESTONE')}</th>
                                <th className="text-right py-3 font-bold">{t('PRICE')}</th>
                                <th className="text-right py-3 font-bold">{t('DISCOUNT')}</th>
                                <th className="text-right py-3 font-bold">{t('TOTAL')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {payment.items?.map((item, index) => (
                                <tr key={index} className="page-break-inside-avoid">
                                    <td className="py-4">
                                        <div className="font-semibold">{item.milestone?.title || '-'}</div>
                                    </td>
                                    <td className="text-right py-4">{formatCurrency(item.price)}</td>
                                    <td className="text-right py-4">
                                        {item.discount_percentage > 0 ? (
                                            <>
                                                <div className="text-sm">{item.discount_percentage}%</div>
                                                <div className="text-sm font-medium">-{formatCurrency(item.discount_amount)}</div>
                                            </>
                                        ) : (
                                            <div className="text-sm">0%</div>
                                        )}
                                    </td>
                                    <td className="text-right py-4 font-semibold">{formatCurrency(item.total_amount)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex justify-end mb-4 page-break-inside-avoid">
                    <div className="w-80 page-break-inside-avoid">
                        <div className="border border-gray-400 p-4 page-break-inside-avoid">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span>{t('Subtotal')}:</span>
                                    <span>{formatCurrency(payment.subtotal)}</span>
                                </div>
                                {payment.discount_amount > 0 && (
                                    <div className="flex justify-between">
                                        <span>{t('Discount')}:</span>
                                        <span>-{formatCurrency(payment.discount_amount)}</span>
                                    </div>
                                )}
                                <div className="border-t border-gray-400 pt-2 mt-2">
                                    <div className="flex justify-between font-bold text-lg">
                                        <span>{t('TOTAL')}:</span>
                                        <span>{formatCurrency(payment.total_amount)}</span>
                                    </div>
                                </div>
                                {payment.balance_amount !== undefined && payment.balance_amount !== payment.total_amount && (
                                    <div className="flex justify-between text-blue-600 font-semibold">
                                        <span>{t('Balance Due')}:</span>
                                        <span>{formatCurrency(payment.balance_amount)}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {(payment.payment_terms || payment.notes) && (
                    <div className="border-t border-gray-400 pt-4">
                        {payment.payment_terms && (
                            <div className="mb-4">
                                <p className="font-semibold">{t('PAYMENT TERMS')}:</p>
                                <p className="text-sm mt-1 whitespace-pre-line">{payment.payment_terms}</p>
                            </div>
                        )}
                        {payment.notes && (
                            <div>
                                <p className="font-semibold">{t('NOTES')}:</p>
                                <p className="text-sm mt-1 whitespace-pre-line">{payment.notes}</p>
                            </div>
                        )}
                    </div>
                )}

                <div className="text-center mt-8">
                    <p className="text-sm">{t('Thank you for your business!')}</p>
                </div>
            </div>

            <style>{`
                body {
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    font-family: Arial, sans-serif;
                }

                @page {
                    margin: 0.5in;
                    size: A4;
                }

                .payment-container {
                    max-width: 100%;
                    margin: 0;
                    box-shadow: none;
                }

                .page-break-inside-avoid {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                @media print {
                    body {
                        background: white;
                    }

                    .payment-container {
                        box-shadow: none;
                    }
                }
            `}</style>
        </div>
    );
}
