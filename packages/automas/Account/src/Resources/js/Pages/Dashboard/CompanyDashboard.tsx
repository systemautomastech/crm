import React, { useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import {
    UserCheck,
    Building2,
    ArrowDownCircle,
    ArrowUpCircle,
    DollarSign,
    Package,
    TrendingUp,
    TrendingDown,
} from 'lucide-react';
import {
    AreaChart,
    Area,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { Badge } from '@/components/ui/badge';

interface AccountProps {
    message?: string;
    stats?: {
        total_clients?: number;
        total_vendors?: number;
        total_customer_payment?: number;
        total_vendor_payment?: number;
        total_revenue?: number;
        total_expense?: number;
        net_profit?: number;
    };
    monthlyVendorPayments?: Array<{ month: string; vendor_payments: number }>;
    monthlyCustomerPayments?: Array<{ month: string; customer_payments: number }>;
    recentRevenues?: Array<{ id: number; title: string; description: string; amount: number; date: string }>;
    recentExpenses?: Array<{ id: number; title: string; description: string; amount: number; date: string }>;
}

export default function AccountIndex({
    stats,
    monthlyVendorPayments = [],
    monthlyCustomerPayments = [],
    recentRevenues = [],
    recentExpenses = [],
}: AccountProps) {
    const { t } = useTranslation();

    const customerChartData = monthlyCustomerPayments || [];
    const vendorChartData = monthlyVendorPayments || [];

    const totalCustomerPayment = stats?.total_customer_payment ?? 0;
    const totalVendorPayment = stats?.total_vendor_payment ?? 0;
    const netProfit = stats?.net_profit ?? (totalCustomerPayment - totalVendorPayment);

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Account') }]}
        >
            <Head title={t('Financial Overview')} />

            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('Financial Overview')}</h1>
                        <p className="text-xs font-semibold text-slate-500 mt-0.5">
                            {t('Company income, vendor expenses, cashflow analytics, and payment records.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top KPI Row (6 Soft Colorful Cards) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {/* Card 1: Total Revenue */}
                    <div className="bg-gradient-to-br from-emerald-50/80 to-teal-50/60 border border-emerald-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <ArrowDownCircle className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Revenue')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">
                                {formatCurrency(stats?.total_revenue ?? 0)}
                            </span>
                            <span className="text-[10px] font-semibold text-emerald-700 block">{t('Recorded income')}</span>
                        </div>
                    </div>

                    {/* Card 2: Total Expense */}
                    <div className="bg-gradient-to-br from-rose-50/80 to-pink-50/60 border border-rose-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <ArrowUpCircle className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Expense')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">
                                {formatCurrency(stats?.total_expense ?? 0)}
                            </span>
                            <span className="text-[10px] font-semibold text-rose-700 block">{t('Recorded expenses')}</span>
                        </div>
                    </div>

                    {/* Card 3: Received Customer Payments */}
                    <div className="bg-gradient-to-br from-blue-50/80 to-indigo-50/60 border border-blue-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <DollarSign className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Customer Payments')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">
                                {formatCurrency(totalCustomerPayment)}
                            </span>
                            <span className="text-[10px] font-semibold text-blue-700 block">{t('Total received')}</span>
                        </div>
                    </div>

                    {/* Card 4: Vendor Payments */}
                    <div className="bg-gradient-to-br from-purple-50/80 to-fuchsia-50/60 border border-purple-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                            <TrendingDown className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Vendor Payments')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">
                                {formatCurrency(totalVendorPayment)}
                            </span>
                            <span className="text-[10px] font-semibold text-purple-700 block">{t('Paid to vendors')}</span>
                        </div>
                    </div>

                    {/* Card 5: Net Profit */}
                    <div className="bg-gradient-to-br from-amber-50/80 to-orange-50/60 border border-amber-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <TrendingUp className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Net Profit')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">
                                {formatCurrency(netProfit)}
                            </span>
                            <span className="text-[10px] font-semibold text-amber-700 block">{netProfit >= 0 ? t('Net positive') : t('Net negative')}</span>
                        </div>
                    </div>

                    {/* Card 6: Total Clients & Vendors */}
                    <div className="bg-gradient-to-br from-teal-50/80 to-cyan-50/60 border border-teal-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                            <UserCheck className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Clients / Vendors')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{stats?.total_clients ?? 0} / {stats?.total_vendors ?? 0}</span>
                            <span className="text-[10px] font-semibold text-teal-700 block">{t('Active accounts')}</span>
                        </div>
                    </div>
                </div>

                {/* 2. Monthly Payment Charts Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Monthly Customer Payments Chart */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <TrendingUp className="h-5 w-5 text-emerald-600" />
                                    {t('Monthly Customer Payments')}
                                </h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Income trend over recent months')}</p>
                            </div>
                            <Badge className="bg-emerald-500/15 text-emerald-600 border-none font-bold text-xs">{t('Revenue')}</Badge>
                        </div>
                        <div className="h-[240px] w-full">
                            {customerChartData.length > 0 ? (
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={customerChartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                        <defs>
                                            <linearGradient id="customerGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#10b981" stopOpacity={0.3} />
                                                <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                                            </linearGradient>
                                        </defs>
                                        <XAxis dataKey="month" stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} />
                                        <YAxis stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                        <Tooltip contentStyle={{ backgroundColor: '#fff', borderRadius: '8px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                        <Area type="monotone" dataKey="customer_payments" name={t('Customer Payments')} stroke="#10b981" strokeWidth={2.5} fillOpacity={1} fill="url(#customerGrad)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex items-center justify-center h-full text-slate-400 text-xs font-medium">
                                    {t('No customer payment history available')}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Monthly Vendor Payments Chart */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <TrendingDown className="h-5 w-5 text-rose-600" />
                                    {t('Monthly Vendor Payments')}
                                </h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Expense trend over recent months')}</p>
                            </div>
                            <Badge className="bg-rose-500/15 text-rose-600 border-none font-bold text-xs">{t('Expense')}</Badge>
                        </div>
                        <div className="h-[240px] w-full">
                            {vendorChartData.length > 0 ? (
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={vendorChartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                        <defs>
                                            <linearGradient id="vendorGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#ef4444" stopOpacity={0.3} />
                                                <stop offset="95%" stopColor="#ef4444" stopOpacity={0.0} />
                                            </linearGradient>
                                        </defs>
                                        <XAxis dataKey="month" stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} />
                                        <YAxis stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                        <Tooltip contentStyle={{ backgroundColor: '#fff', borderRadius: '8px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                        <Area type="monotone" dataKey="vendor_payments" name={t('Vendor Payments')} stroke="#ef4444" strokeWidth={2.5} fillOpacity={1} fill="url(#vendorGrad)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex items-center justify-center h-full text-slate-400 text-xs font-medium">
                                    {t('No vendor payment history available')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* 3. Recent Revenue & Recent Expenses Lists Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Revenue */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[360px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Recent Revenue')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Latest incoming customer transactions')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-emerald-50 text-emerald-700 border-emerald-200">
                                {recentRevenues.length} {t('Transactions')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {recentRevenues && recentRevenues.length > 0 ? (
                                recentRevenues.map((rev) => (
                                    <div key={rev.id} className="flex justify-between items-center py-3 hover:bg-slate-50/80 rounded-lg px-2 transition-colors">
                                        <div>
                                            <p className="font-bold text-xs text-slate-800">{rev.title}</p>
                                            <p className="text-[11px] text-slate-500 font-medium mt-0.5">{rev.description}</p>
                                            <p className="text-[10px] text-slate-400 font-semibold mt-1">{formatDate(rev.date)}</p>
                                        </div>
                                        <span className="text-emerald-600 font-black text-xs shrink-0">{formatCurrency(rev.amount)}</span>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <p>{t('No recent revenue transactions')}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Recent Expenses */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[360px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Recent Expenses')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Latest vendor payments & operational expenses')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-rose-50 text-rose-700 border-rose-200">
                                {recentExpenses.length} {t('Expenses')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {recentExpenses && recentExpenses.length > 0 ? (
                                recentExpenses.map((exp) => (
                                    <div key={exp.id} className="flex justify-between items-center py-3 hover:bg-slate-50/80 rounded-lg px-2 transition-colors">
                                        <div>
                                            <p className="font-bold text-xs text-slate-800">{exp.title}</p>
                                            <p className="text-[11px] text-slate-500 font-medium mt-0.5">{exp.description}</p>
                                            <p className="text-[10px] text-slate-400 font-semibold mt-1">{formatDate(exp.date)}</p>
                                        </div>
                                        <span className="text-rose-600 font-black text-xs shrink-0">{formatCurrency(exp.amount)}</span>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <p>{t('No recent expense transactions')}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
