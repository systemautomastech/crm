import React, { useEffect, useState, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate, getCompanySetting, getImagePath } from '@/utils/helpers';
import { CustomerPayment } from './types';

interface PrintProps {
    payment: CustomerPayment;
    salesInvoiceSetting?: {
        sales_invoice_logo?: string;
        sales_invoice_show_logo?: string;
        sales_invoice_bg_letterhead?: string;
        sales_invoice_enable_letterhead?: string;
        [key: string]: any;
    };
}

export default function Print() {
    const { t } = useTranslation();
    const pageProps = usePage<PrintProps>().props;
    const { payment, salesInvoiceSetting } = pageProps;
    const [isDownloading, setIsDownloading] = useState(false);
    const downloadInitiatedRef = useRef(false);

    const showLogo = (salesInvoiceSetting?.sales_invoice_show_logo ?? 'on') !== 'off';
    const customLogo = salesInvoiceSetting?.sales_invoice_logo || '';
    const companyLogo = getCompanySetting('company_logo') || getCompanySetting('logo_dark') || '';
    const logoToUse = customLogo || companyLogo;
    const logoUrl = (showLogo && logoToUse) ? getImagePath(logoToUse, pageProps) : '';

    const enableLetterhead = (salesInvoiceSetting?.sales_invoice_enable_letterhead ?? 'off') === 'on';
    const bgLetterhead = salesInvoiceSetting?.sales_invoice_bg_letterhead || '';
    const bgLetterheadUrl = (enableLetterhead && bgLetterhead) ? getImagePath(bgLetterhead, pageProps) : '';

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && !downloadInitiatedRef.current) {
            downloadInitiatedRef.current = true;
            setTimeout(() => downloadPDF(), 500);
        } else {
            // Automatically open the print modal when visiting the print page
            const timer = setTimeout(() => {
                window.print();
            }, 500);
            return () => clearTimeout(timer);
        }
    }, []);

    const downloadPDF = async () => {
        if (isDownloading) return;
        setIsDownloading(true);

        const printContent = document.querySelector('.payment-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `customer-payment-${payment.payment_number || payment.id}.pdf`,
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
            <Head title={`${t('Customer Payment')} - ${payment.payment_number || '#' + payment.id}`} />

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

            <div className="a4-page">
                {enableLetterhead && bgLetterheadUrl && (
                    <img
                        src={bgLetterheadUrl}
                        alt="Letterhead Background"
                        className="letterhead-bg-layer"
                    />
                )}
                
                <div className="a4-content">
                    <div>
                        {/* Header */}
                        <div className="flex justify-between items-start mb-5">
                            <div className="w-1/2">
                                {showLogo && salesInvoiceSetting?.sales_invoice_logo ? (
                                    <img
                                        src={getImagePath(salesInvoiceSetting.sales_invoice_logo, pageProps)}
                                        alt="Logo"
                                        className="max-h-14 max-w-[200px] object-contain mb-3"
                                    />
                                ) : showLogo && (getCompanySetting('company_logo') || getCompanySetting('logo_dark')) ? (
                                    <img
                                        src={getImagePath(getCompanySetting('company_logo') || getCompanySetting('logo_dark'), pageProps)}
                                        alt="Logo"
                                        className="max-h-14 max-w-[200px] object-contain mb-3"
                                    />
                                ) : getCompanySetting('company_name') ? (
                                    <h1 className="text-2xl font-bold mb-2 text-gray-900">{getCompanySetting('company_name')}</h1>
                                ) : null}
                                <div className="text-xs space-y-0.5 text-gray-600">
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
                                <h2 className="text-2xl font-bold mb-1 text-gray-900">{t('PAYMENT RECEIPT')}</h2>
                                <p className="text-base font-semibold text-gray-800">{payment.payment_number || `#${payment.id}`}</p>
                                <div className="text-xs mt-2 space-y-0.5 text-gray-600">
                                    <p>{t('Payment Date')}: {formatDate(payment.payment_date)}</p>
                                    {payment.reference_number && (
                                        <p>{t('Reference')}: {payment.reference_number}</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Customer & Bank Information */}
                        <div className="flex justify-between mb-5 pt-3 border-t border-gray-200">
                            <div className="w-1/2">
                                <h3 className="font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider">{t('RECEIVED FROM')}</h3>
                                <div className="text-xs space-y-0.5 text-gray-700">
                                    <p className="font-semibold text-gray-900">{payment.customer?.name || '-'}</p>
                                    {payment.customer?.email && <p>{payment.customer.email}</p>}
                                    {payment.customer?.contact && <p>{payment.customer.contact}</p>}
                                </div>
                            </div>
                            <div className="text-right w-1/2">
                                <h3 className="font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider">{t('DEPOSITED TO')}</h3>
                                <div className="text-xs space-y-0.5 text-gray-700">
                                    <p className="font-semibold text-gray-900">{payment.bank_account?.account_name || '-'}</p>
                                    {payment.bank_account?.account_number && (
                                        <p>{t('Account No')}: {payment.bank_account.account_number}</p>
                                    )}
                                    {payment.bank_account?.bank_name && (
                                        <p>{payment.bank_account.bank_name}</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Invoice Allocations Table */}
                        {payment.allocations && payment.allocations.length > 0 && (
                            <div className="mb-4">
                                <table style={{ width: '100%', fontSize: '10px', tableLayout: 'fixed', borderCollapse: 'collapse', border: '1px solid #94a3b8' }}>
                                    <thead> 
                                        <tr style={{ backgroundColor: '#e2e8f0', color: '#0f172a', fontWeight: 700 }}>
                                            <th style={{ padding: '6px 4px', border: '1px solid #94a3b8', textAlign: 'center', fontSize: '9.5px', width: '6%' }}>{t('SN')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'left', fontSize: '9.5px', width: '28%' }}>{t('INVOICE NUMBER')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'left', fontSize: '9.5px', width: '22%' }}>{t('INVOICE DATE')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', fontSize: '9.5px', width: '22%' }}>{t('INVOICE TOTAL')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', fontSize: '9.5px', width: '22%' }}>{t('ALLOCATED AMOUNT')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {payment.allocations.map((allocation, idx) => (
                                            <tr key={allocation.id || idx} className="page-break-inside-avoid">
                                                <td style={{ padding: '6px 4px', border: '1px solid #94a3b8', textAlign: 'center', verticalAlign: 'top', color: '#475569' }}>{idx + 1}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', verticalAlign: 'top', color: '#0f172a', fontWeight: 600 }}>{allocation.invoice?.invoice_number || '-'}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', verticalAlign: 'top', color: '#475569' }}>{formatDate(allocation.invoice?.invoice_date)}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', verticalAlign: 'top', color: '#1e293b' }}>{formatCurrency(allocation.invoice?.total_amount)}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', verticalAlign: 'top', fontWeight: 600, color: '#0f172a' }}>{formatCurrency(allocation.allocated_amount)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="page-break-inside-avoid" style={{ fontWeight: 700 }}>
                                            <td colSpan={3} style={{ border: '1px solid #94a3b8' }}></td>
                                            <td style={{ padding: '6px 8px', fontSize: '11px', color: '#0f172a', border: '1px solid #94a3b8', textAlign: 'right' }}>{t('TOTAL')}:</td>
                                            <td style={{ padding: '6px 8px', fontSize: '11px', textAlign: 'right', color: '#0f172a', border: '1px solid #94a3b8' }}>
                                                {formatCurrency(payment.payment_amount)}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}

                        {/* Credit Note History */}
                        {payment.credit_note_applications && payment.credit_note_applications.length > 0 && (
                            <div className="mb-4">
                                <div style={{ fontSize: '10px', fontWeight: 700, color: '#0f172a', marginBottom: '4px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                    {t('Credit Note History')}
                                </div>
                                <table style={{ width: '100%', fontSize: '10px', tableLayout: 'fixed', borderCollapse: 'collapse', border: '1px solid #94a3b8' }}>
                                    <thead>
                                        <tr style={{ backgroundColor: '#e2e8f0', color: '#0f172a', fontWeight: 700 }}>
                                            <th style={{ padding: '6px 4px', border: '1px solid #94a3b8', textAlign: 'center', fontSize: '9.5px', width: '6%' }}>{t('SN')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'left', fontSize: '9.5px', width: '38%' }}>{t('CREDIT NOTE NUMBER')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'left', fontSize: '9.5px', width: '28%' }}>{t('APPLICATION DATE')}</th>
                                            <th style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', fontSize: '9.5px', width: '28%' }}>{t('APPLIED AMOUNT')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {payment.credit_note_applications.map((application, idx) => (
                                            <tr key={application.id || idx} className="page-break-inside-avoid">
                                                <td style={{ padding: '6px 4px', border: '1px solid #94a3b8', textAlign: 'center', verticalAlign: 'top', color: '#475569' }}>{idx + 1}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', verticalAlign: 'top', color: '#0f172a', fontWeight: 600 }}>{application.credit_note?.credit_note_number || '-'}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', verticalAlign: 'top', color: '#475569' }}>{formatDate(application.application_date)}</td>
                                                <td style={{ padding: '6px 8px', border: '1px solid #94a3b8', textAlign: 'right', verticalAlign: 'top', fontWeight: 600, color: '#0f172a' }}>{formatCurrency(application.applied_amount)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="page-break-inside-avoid" style={{ fontWeight: 700 }}>
                                            <td colSpan={2} style={{ border: '1px solid #94a3b8' }}></td>
                                            <td style={{ padding: '6px 8px', fontSize: '11px', color: '#0f172a', border: '1px solid #94a3b8', textAlign: 'right' }}>{t('TOTAL APPLIED')}:</td>
                                            <td style={{ padding: '6px 8px', fontSize: '11px', textAlign: 'right', color: '#0f172a', border: '1px solid #94a3b8' }}>
                                                {formatCurrency(payment.credit_note_applications.reduce((sum, app) => sum + parseFloat(app.applied_amount || '0'), 0))}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div>
                        {payment.notes && (
                            <div className="pt-2 text-xs text-gray-600 page-break-inside-avoid">
                                <span className="font-semibold text-gray-800">{t('NOTES')}:</span>
                                <div className="pt-1 mb-2 text-xs text-gray-600">
                                    {payment.notes}
                                </div>
                            </div>
                        )}
                        <div className="border-t border-gray-300 pt-2 text-center text-xs text-gray-500 page-break-inside-avoid">
                            <span>{t('Thank you for your business!')}</span>
                        </div>
                    </div>
                </div>
            </div>

            <style>{`
                * {
                    box-sizing: border-box !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    font-family: 'Open Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                }

                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background-color: #ffffff;
                    color: #1e293b;
                }

                @page {
                    size: A4 portrait;
                    margin: 0;
                }

                .a4-page {
                    position: relative;
                    width: 210mm;
                    height: 297mm;
                    min-height: 297mm;
                    max-height: 297mm;
                    padding: 30mm 14mm 30mm 14mm;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    overflow: hidden;
                    page-break-after: always;
                    break-after: page;
                    page-break-inside: avoid;
                    break-inside: avoid-page;
                }

                .letterhead-bg-layer {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    z-index: 0;
                    pointer-events: none;
                }

                .a4-content {
                    position: relative;
                    z-index: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    height: 100%;
                }

                .page-break-inside-avoid {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                table {
                    table-layout: fixed !important;
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                    word-break: break-word !important;
                }

                th, td {
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                    word-break: break-word !important;
                    box-sizing: border-box !important;
                }

                @media print {
                    html, body {
                        background: #ffffff !important;
                    }

                    .a4-page {
                        width: 210mm !important;
                        height: 297mm !important;
                        margin: 0 !important;
                        padding: 30mm 14mm 30mm 14mm !important;
                        box-shadow: none !important;
                    }
                }
            `}</style>
        </div>
    );
}
