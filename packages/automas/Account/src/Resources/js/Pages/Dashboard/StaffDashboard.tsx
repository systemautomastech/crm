import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import { Users, Building, DollarSign, TrendingUp, TrendingDown, Receipt, Activity } from 'lucide-react';
import { formatCurrency } from '@/utils/helpers';
import { Badge } from '@/components/ui/badge';

interface StaffProps {
    stats: {
        total_clients: number;
        total_vendors: number;
        monthly_revenue: number;
        monthly_expense: number;
    };
    recentActivities?: Array<{
        type: string;
        title: string;
        amount: number;
        date: string;
    }>;
}

export default function StaffDashboard({ stats, recentActivities = [] }: StaffProps) {
    const { t } = useTranslation();

    const totalClients = stats?.total_clients ?? 0;
    const totalVendors = stats?.total_vendors ?? 0;
    const monthlyRevenue = stats?.monthly_revenue ?? 0;
    const monthlyExpense = stats?.monthly_expense ?? 0;
    const netMonthlyProfit = monthlyRevenue - monthlyExpense;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Account') }, { label: t('Staff Dashboard') }]}
        >
            <Head title={t('Staff Financial Overview')} />

            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('Staff Financial Dashboard')}</h1>
                        <p className="text-xs font-semibold text-slate-500 mt-0.5">
                            {t('Financial summary for assigned accounts, client balances, and monthly operations.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top KPI Row (4 Soft Colorful Cards) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Card 1: Total Clients */}
                    <div className="bg-gradient-to-br from-blue-50/90 to-indigo-50/80 border border-blue-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Active Clients')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {totalClients}
                            </span>
                            <span className="text-[11px] font-semibold text-blue-600 block mt-0.5">{t('Assigned accounts')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <Users className="w-6 h-6" />
                        </div>
                    </div>

                    {/* Card 2: Total Vendors */}
                    <div className="bg-gradient-to-br from-purple-50/90 to-fuchsia-50/80 border border-purple-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Active Vendors')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {totalVendors}
                            </span>
                            <span className="text-[11px] font-semibold text-purple-600 block mt-0.5">{t('Supplier relationships')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                            <Building className="w-6 h-6" />
                        </div>
                    </div>

                    {/* Card 3: Monthly Revenue */}
                    <div className="bg-gradient-to-br from-emerald-50/90 to-teal-50/80 border border-emerald-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Monthly Revenue')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {formatCurrency(monthlyRevenue)}
                            </span>
                            <span className="text-[11px] font-semibold text-emerald-600 block mt-0.5">{t('Current month income')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <TrendingUp className="w-6 h-6" />
                        </div>
                    </div>

                    {/* Card 4: Monthly Expense */}
                    <div className="bg-gradient-to-br from-rose-50/90 to-pink-50/80 border border-rose-100/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span className="text-xs font-bold text-slate-500 block leading-tight">{t('Monthly Expense')}</span>
                            <span className="text-2xl font-black text-slate-900 mt-1 block">
                                {formatCurrency(monthlyExpense)}
                            </span>
                            <span className="text-[11px] font-semibold text-rose-600 block mt-0.5">{t('Current month outflow')}</span>
                        </div>
                        <div className="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <TrendingDown className="w-6 h-6" />
                        </div>
                    </div>
                </div>

                {/* 2. Grid Row: Monthly Summary & Recent Financial Activities */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Monthly Financial Breakdown */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <h3 className="text-base font-bold text-slate-900 mb-1">{t('Monthly Cashflow Summary')}</h3>
                        <p className="text-xs font-semibold text-slate-400 mb-4">{t('Income vs Expense margin for current month')}</p>

                        <div className="space-y-4">
                            <div className="flex items-center justify-between p-4 bg-emerald-50/80 border border-emerald-100 rounded-xl">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 bg-emerald-500/10 rounded-lg text-emerald-600">
                                        <TrendingUp className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <span className="font-bold text-xs text-slate-900 block">{t('Monthly Revenue')}</span>
                                        <span className="text-[11px] font-semibold text-slate-500 block">{t('Inflows')}</span>
                                    </div>
                                </div>
                                <span className="text-base font-black text-emerald-700">{formatCurrency(monthlyRevenue)}</span>
                            </div>

                            <div className="flex items-center justify-between p-4 bg-rose-50/80 border border-rose-100 rounded-xl">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 bg-rose-500/10 rounded-lg text-rose-600">
                                        <TrendingDown className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <span className="font-bold text-xs text-slate-900 block">{t('Monthly Expenses')}</span>
                                        <span className="text-[11px] font-semibold text-slate-500 block">{t('Outflows')}</span>
                                    </div>
                                </div>
                                <span className="text-base font-black text-rose-700">{formatCurrency(monthlyExpense)}</span>
                            </div>

                            <div className="flex items-center justify-between p-4 bg-blue-50/80 border border-blue-100 rounded-xl">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 bg-blue-500/10 rounded-lg text-blue-600">
                                        <Activity className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <span className="font-bold text-xs text-slate-900 block">{t('Net Operating Margin')}</span>
                                        <span className="text-[11px] font-semibold text-slate-500 block">{t('Net balance')}</span>
                                    </div>
                                </div>
                                <span className={`text-base font-black ${netMonthlyProfit >= 0 ? 'text-blue-700' : 'text-rose-700'}`}>
                                    {formatCurrency(netMonthlyProfit)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Recent Activities List */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <h3 className="text-base font-bold text-slate-900 mb-1">{t('Recent Financial Activities')}</h3>
                        <p className="text-xs font-semibold text-slate-400 mb-4">{t('Latest financial transactions & ledger updates')}</p>

                        <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                            {recentActivities.length > 0 ? (
                                recentActivities.map((act, idx) => (
                                    <div key={idx} className="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs">
                                                <Receipt className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <span className="font-bold text-xs text-slate-900 block">{act.title}</span>
                                                <span className="text-[11px] font-semibold text-slate-400 block">{act.date}</span>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <span className="text-xs font-black text-slate-900 block">{formatCurrency(act.amount)}</span>
                                            <Badge variant="outline" className="text-[10px] uppercase font-bold text-slate-500 border-slate-200">
                                                {act.type}
                                            </Badge>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="h-full flex flex-col items-center justify-center text-slate-400 text-xs font-semibold">
                                    <Activity className="h-10 w-10 mb-2 opacity-40" />
                                    <span>{t('No recent activity records')}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
