import React, { useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import { Badge } from '@/components/ui/badge';
import { ShoppingCart, Package, Users, RotateCcw, Monitor, TrendingUp, AlertTriangle, DollarSign } from 'lucide-react';
import { formatDate, formatCurrency, getImagePath } from '@/utils/helpers';
import {
    AreaChart,
    Area,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

interface PosProps {
    stats: {
        total_sales: number;
        total_revenue: number;
        avg_transaction: number;
        total_products: number;
        total_customers: number;
        walk_in_sales: number;
        total_returns: number;
        returns_amount: number;
    };
    topProducts: Array<{
        name: string;
        total_quantity: number;
        total_revenue: number;
    }>;
    recentSales: Array<{
        id: number;
        sale_number: string;
        total: number;
        created_at: string;
        customer?: { name: string };
        warehouse?: { name: string };
    }>;
    recentReturns: Array<{
        id: number;
        return_number: string;
        total_amount: number;
        created_at: string;
        status: string;
        customer?: { name: string };
    }>;
    last10DaysSales: Array<{
        date: string;
        sales: number;
    }>;
    outOfStockProductsList: Array<{
        product_name: string;
        sku: string;
        warehouse_name: string;
        stock: number;
        image?: string;
    }>;
    counterWiseSales: Array<{
        counter_name: string;
        counter_code: string;
        today_sales: number;
        today_revenue: number;
    }>;
}

export default function PosIndex({
    stats,
    topProducts = [],
    recentSales = [],
    recentReturns = [],
    last10DaysSales = [],
    outOfStockProductsList = [],
    counterWiseSales = [],
}: PosProps) {
    const { t } = useTranslation();

    const salesTrendData = last10DaysSales || [];
    const totalCounterRevenue = counterWiseSales?.reduce((sum, c) => sum + Number(c.today_revenue || 0), 0) || 0;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('POS') }]}
        >
            <Head title={t('POS Overview')} />

            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('POS Overview')}</h1>
                        <p className="text-xs font-semibold text-slate-500 mt-0.5">
                            {t('Point of Sale transactions, counter performance, and inventory alerts.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top KPI Row (6 Soft Colorful Cards) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {/* Card 1: Total Sales */}
                    <div className="bg-gradient-to-br from-blue-50/80 to-indigo-50/60 border border-blue-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <ShoppingCart className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Sales')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{stats?.total_sales ?? 0}</span>
                            <span className="text-[10px] font-semibold text-blue-700 block">{formatCurrency(stats?.total_revenue ?? 0)}</span>
                        </div>
                    </div>

                    {/* Card 2: Total Revenue */}
                    <div className="bg-gradient-to-br from-emerald-50/80 to-teal-50/60 border border-emerald-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <DollarSign className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Revenue')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">{formatCurrency(stats?.total_revenue ?? 0)}</span>
                            <span className="text-[10px] font-semibold text-emerald-700 block">{stats?.walk_in_sales ?? 0} {t('walk-in')}</span>
                        </div>
                    </div>

                    {/* Card 3: Total Returns */}
                    <div className="bg-gradient-to-br from-rose-50/80 to-pink-50/60 border border-rose-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <RotateCcw className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Returns')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{stats?.total_returns ?? 0}</span>
                            <span className="text-[10px] font-semibold text-rose-700 block">{formatCurrency(stats?.returns_amount ?? 0)}</span>
                        </div>
                    </div>

                    {/* Card 4: Avg Transaction */}
                    <div className="bg-gradient-to-br from-purple-50/80 to-fuchsia-50/60 border border-purple-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                            <Users className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Avg Transaction')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">{formatCurrency(stats?.avg_transaction ?? 0)}</span>
                            <span className="text-[10px] font-semibold text-purple-700 block">{stats?.total_customers ?? 0} {t('customers')}</span>
                        </div>
                    </div>

                    {/* Card 5: Total Products */}
                    <div className="bg-gradient-to-br from-amber-50/80 to-orange-50/60 border border-amber-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <Package className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Products')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{stats?.total_products ?? 0}</span>
                            <span className="text-[10px] font-semibold text-amber-700 block">{t('POS inventory')}</span>
                        </div>
                    </div>

                    {/* Card 6: Counter Revenue */}
                    <div className="bg-gradient-to-br from-sky-50/80 to-cyan-50/60 border border-sky-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center shrink-0">
                            <Monitor className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Counter Revenue')}</span>
                            <span className="text-lg font-bold text-slate-900 mt-0.5 block">{formatCurrency(totalCounterRevenue)}</span>
                            <span className="text-[10px] font-semibold text-sky-700 block">{t('Today counters')}</span>
                        </div>
                    </div>
                </div>

                {/* 2. Sales Trend Area Chart */}
                <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                                <TrendingUp className="h-5 w-5 text-blue-600" />
                                {t('Daily Sales Trend')}
                            </h3>
                            <p className="text-xs font-semibold text-slate-400">{t('Recent POS revenue per day')}</p>
                        </div>
                        <Badge variant="outline" className="text-xs font-semibold bg-blue-50 text-blue-700 border-blue-200">
                            {t('Last 10 Days')}
                        </Badge>
                    </div>
                    <div className="h-[250px] w-full">
                        {salesTrendData.length > 0 ? (
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={salesTrendData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                    <defs>
                                        <linearGradient id="posSalesGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="#3b82f6" stopOpacity={0.0} />
                                        </linearGradient>
                                    </defs>
                                    <XAxis dataKey="date" stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} />
                                    <YAxis stroke="#94a3b8" fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                    <Tooltip contentStyle={{ backgroundColor: '#fff', borderRadius: '8px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                    <Area type="monotone" dataKey="sales" name={t('Daily Sales')} stroke="#3b82f6" strokeWidth={2.5} fillOpacity={1} fill="url(#posSalesGrad)" />
                                </AreaChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-full text-slate-400 text-xs font-medium">
                                {t('No sales trend data available for recent days')}
                            </div>
                        )}
                    </div>
                </div>

                {/* 3. Out of Stock Alerts & Counter Wise Sales Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Out of Stock Products */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <AlertTriangle className="h-5 w-5 text-rose-600" />
                                    {t('Out of Stock Alerts')}
                                </h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Products requiring urgent restock')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-rose-50 text-rose-700 border-rose-200">
                                {outOfStockProductsList.length} {t('Alerts')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {outOfStockProductsList && outOfStockProductsList.length > 0 ? (
                                outOfStockProductsList.map((item, idx) => (
                                    <div key={idx} className="flex items-center justify-between py-2.5 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div className="flex items-center gap-3 min-w-0">
                                            <div className="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                                <Package className="w-4 h-4" />
                                            </div>
                                            <div className="truncate">
                                                <p className="text-xs font-bold text-slate-800 truncate">{item.product_name}</p>
                                                <p className="text-[11px] font-medium text-slate-500 mt-0.5">SKU: {item.sku} · {item.warehouse_name}</p>
                                            </div>
                                        </div>
                                        <Badge className="bg-rose-500/15 text-rose-600 border-none font-bold text-[10px] shrink-0">
                                            {t('Out of stock')}
                                        </Badge>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <Package className="h-8 w-8 text-slate-300 mb-2" />
                                    <p>{t('All products are in stock')}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Counter Wise Sales */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <Monitor className="h-5 w-5 text-blue-600" />
                                    {t('Billing Counter Sales')}
                                </h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Today counter sales summary')}</p>
                            </div>
                            <span className="text-xs font-extrabold text-emerald-600">{formatCurrency(totalCounterRevenue)}</span>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {counterWiseSales && counterWiseSales.length > 0 ? (
                                counterWiseSales.map((counter, idx) => (
                                    <div key={idx} className="flex items-center justify-between py-2.5 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div>
                                            <p className="text-xs font-bold text-slate-800">{counter.counter_name}</p>
                                            <p className="text-[11px] font-medium text-slate-500 mt-0.5">{t('Code')}: {counter.counter_code}</p>
                                        </div>
                                        <div className="text-right shrink-0">
                                            <p className="text-xs font-black text-emerald-600">{formatCurrency(counter.today_revenue)}</p>
                                            <p className="text-[10px] font-semibold text-slate-400 mt-0.5">{counter.today_sales} {t('sales')}</p>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <Monitor className="h-8 w-8 text-slate-300 mb-2" />
                                    <p>{t('No billing counter sales recorded today')}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
