import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import { CreditCard, DollarSign, TrendingDown, Receipt, FileText } from 'lucide-react';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { Badge } from '@/components/ui/badge';

interface VendorProps {
    stats: {
        total_payments: number;
        total_expenses: number;
        payment_count: number;
    };
    monthlyPayments?: Array<{ month: string; payments: number }>;
    recentReturnInvoices: Array<{
        id: number;
        invoice_number: string;
        amount: number;
        date: string;
        status: string;
    }>;
    recentDebitNotes: Array<{
        id: number;
        debit_note_number: string;
        amount: number;
        date: string;
        status: string;
    }>;
    vendor?: {
        name: string;
    };
}

export default function VendorDashboard({ stats, monthlyPayments = [], recentReturnInvoices = [], recentDebitNotes = [], vendor }: VendorProps) {
    const { t } = useTranslation();

    const totalPayments = stats?.total_payments ?? 0;
    const totalExpenses = stats?.total_expenses ?? 0;
    const paymentCount = stats?.payment_count ?? 0;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Account') }, { label: t('Vendor Dashboard') }]}
        >
            <Head title={t('Vendor Portal')} />

            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">
                            {vendor?.name ? `${vendor.name} - ${t('Vendor Portal')}` : t('Vendor Portal')}
                        </h1>
                        <p className="text-xs font-semibold text-slate-500 mt-0.5">
                            {t('Summary of vendor payments received, bills, and debit notes.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top KPI Row (3 Executive Soft Gradient Cards) */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Card 1: Total Payments Received */}
                    <div className="bg-gradient-to-br from-blue-50/90 to-indigo-50/80 border border-blue-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Payments Received')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {formatCurrency(totalPayments)}
                            </span>
                            <span className="text-[11px] font-semibold text-blue-600 block mt-0.5">{t('Total received from company')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <DollarSign className="w-6 h-6" />
                        </div>
                    </div>

                    {/* Card 2: Total Vendor Billed Expense */}
                    <div className="bg-gradient-to-br from-rose-50/90 to-pink-50/80 border border-rose-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Total Billed Amount')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {formatCurrency(totalExpenses)}
                            </span>
                            <span className="text-[11px] font-semibold text-rose-600 block mt-0.5">{t('Total purchase order expenses')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <TrendingDown className="w-6 h-6" />
                        </div>
                    </div>

                    {/* Card 3: Total Transactions Count */}
                    <div className="bg-gradient-to-br from-purple-50/90 to-fuchsia-50/80 border border-purple-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Payment Transactions')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {paymentCount}
                            </span>
                            <span className="text-[11px] font-semibold text-purple-600 block mt-0.5">{t('Verified payment disbursements')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                            <CreditCard className="w-6 h-6" />
                        </div>
                    </div>
                </div>

                {/* 2. Content Grids: Recent Return Purchase Invoices & Debit Notes */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Return Purchase Invoices */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Recent Return Purchase Invoices')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Vendor goods returns and adjustments')}</p>
                            </div>
                            <div className="p-2 bg-slate-100 rounded-xl">
                                <Receipt className="h-5 w-5 text-slate-600" />
                            </div>
                        </div>

                        <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                            {recentReturnInvoices.length > 0 ? (
                                recentReturnInvoices.map((invoice) => (
                                    <div key={invoice.id} className="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 font-bold text-xs">
                                                RET
                                            </div>
                                            <div>
                                                <span className="font-bold text-xs text-slate-900 block">{invoice.invoice_number}</span>
                                                <span className="text-[11px] font-semibold text-slate-400 block">{invoice.date}</span>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <span className="text-xs font-black text-slate-900 block">{formatCurrency(invoice.amount)}</span>
                                            <Badge variant="outline" className="text-[10px] uppercase font-bold text-slate-500 border-slate-200">
                                                {invoice.status}
                                            </Badge>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="h-full flex flex-col items-center justify-center text-slate-400 text-xs font-semibold">
                                    <Receipt className="h-10 w-10 mb-2 opacity-40" />
                                    <span>{t('No purchase return invoices')}</span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Debit Notes */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Recent Debit Notes')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Vendor debit adjustments and balances')}</p>
                            </div>
                            <div className="p-2 bg-slate-100 rounded-xl">
                                <FileText className="h-5 w-5 text-slate-600" />
                            </div>
                        </div>

                        <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                            {recentDebitNotes.length > 0 ? (
                                recentDebitNotes.map((note) => (
                                    <div key={note.id} className="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0 font-bold text-xs">
                                                DR
                                            </div>
                                            <div>
                                                <span className="font-bold text-xs text-slate-900 block">{note.debit_note_number}</span>
                                                <span className="text-[11px] font-semibold text-slate-400 block">{note.date}</span>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <span className="text-xs font-black text-slate-900 block">{formatCurrency(note.amount)}</span>
                                            <Badge variant="outline" className="text-[10px] uppercase font-bold text-slate-500 border-slate-200">
                                                {note.status}
                                            </Badge>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="h-full flex flex-col items-center justify-center text-slate-400 text-xs font-semibold">
                                    <FileText className="h-10 w-10 mb-2 opacity-40" />
                                    <span>{t('No debit notes found')}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
