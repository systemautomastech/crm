import React, { useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { DateRangePicker } from "@/components/ui/date-range-picker";
import {
    Users,
    Handshake,
    BarChart3,
    Target,
    Phone,
    AlertCircle,
    AlertTriangle,
    BarChart2,
    PieChart as PieChartIcon,
    TrendingUp,
    List,
    Calendar,
    Clock,
    Sparkles,
    Activity,
    Package,
    BadgeDollarSign,
    Tag,
    Trophy,
    Tv,
    Headphones,
    Armchair,
    Shirt,
    Utensils,
    Code,
    ChevronRight,
    ChevronDown,
} from 'lucide-react';
import {
    BarChart,
    Bar,
    AreaChart,
    Area,
    PieChart,
    Pie,
    Cell,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

interface MetricStats {
    totalLeads?: number;
    monthLeads?: number;
    todayLeads?: number;
    yesterdayLeads?: number;
    thisMonthLeads?: number;
    prevMonthLeads?: number;
    avgDailyLeads?: number;
    activeDeals?: number;
    openDealValue?: number;
    wonDealAmount?: number;
    wonDealsThisMonth?: number;
    todayDeals?: number;
    yesterdayDeals?: number;
    thisMonthDeals?: number;
    prevMonthDeals?: number;
    callsToday?: number;
    connectedCallsToday?: number;
    missedCallsToday?: number;
    followupsToday?: number;
    followupLeadsToday?: number;
    followupDealsToday?: number;
    overdueTasks?: number;
}

interface LeadOverviewItem {
    id?: number | string;
    name: string;
    count: number;
}

interface DealPipelineItem {
    id?: number | string;
    name: string;
    deals: number;
    amount: number;
}

interface DailyLeadTrendItem {
    date: string;
    leads: number;
}

interface TeamMember {
    id?: number | string;
    name: string;
    avatar?: string;
    connectedCalls?: number;
    wonDeals: number;
    wonAmount: number;
    overdue?: number;
}

interface TopProduct {
    id?: number | string;
    name: string;
    wonValue: number;
}

interface SalesPerformanceItem {
    month: string;
    value: number;
}

interface LeadSourceItem {
    name: string;
    count: number;
    color: string;
}

interface WinRateStats {
    winRate: number;
    wonCount: number;
    lostCount: number;
}

interface NeedsAttentionStats {
    uncontactedLeads?: number;
    unassignedLeads?: number;
    inactiveDeals?: number;
    followupLeadsToday?: number;
    overdueLeadTasks?: number;
}

interface CategorySellerItem {
    userId: number | string;
    userName: string;
    userAvatar?: string;
    categoryId: number | string;
    categoryName: string;
    categoryColor?: string;
    productName: string;
    dealsCount: number;
    totalAmount: number;
}

interface ProductCategoryOption {
    id: number | string;
    name: string;
    color?: string;
}

interface SellerUserOption {
    id: number | string;
    name: string;
    avatar?: string;
}

interface LeaderboardSummary {
    totalProductsSold: number;
    totalSalesValue: number;
    activeSellersCount: number;
    productCategoriesCount: number;
}

interface LeaderboardSellerRow {
    id: number | string;
    rank: number;
    badgeColor: string;
    avatar: string;
    avatarColor: string;
    name: string;
    values: Record<string, number>;
    amounts: Record<string, number>;
    totalAmount: number;
    totalUnits: number;
}

interface LeaderboardCategoryLeader {
    category: string;
    leader: string;
    units: string;
    amount: number;
}

interface CategoryLeaderboardData {
    categories: string[];
    sellers: LeaderboardSellerRow[];
    leaders: LeaderboardCategoryLeader[];
    summary: LeaderboardSummary;
}

interface CompanyDashboardProps {
    stats?: MetricStats;
    leadOverview?: LeadOverviewItem[];
    dealPipeline?: DealPipelineItem[];
    dailyLeadTrend?: DailyLeadTrendItem[];
    teamPerformance?: TeamMember[];
    topProducts?: TopProduct[];
    categorySellers?: CategorySellerItem[];
    categoryLeaderboard?: CategoryLeaderboardData;
    productCategories?: ProductCategoryOption[];
    sellerUsers?: SellerUserOption[];
    salesPerformance?: SalesPerformanceItem[];
    leadSources?: LeadSourceItem[];
    winRateStats?: WinRateStats;
    needsAttention?: NeedsAttentionStats;
    message?: string;
}

function formatCompactBDT(amount: number): string {
    if (!amount || amount === 0) return 'BDT 0';
    if (amount >= 1_000_000) {
        const val = amount / 1_000_000;
        return `BDT ${val % 1 === 0 ? val.toFixed(0) : val.toFixed(1)}M`;
    }
    if (amount >= 1_000) {
        const val = amount / 1_000;
        return `BDT ${Math.round(val)}K`;
    }
    return `BDT ${amount.toLocaleString()}`;
}

const CHART_COLORS = ['#6366f1', '#14b8a6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#10b981'];

export default function CompanyDashboard({
    stats,
    leadOverview = [],
    dealPipeline = [],
    dailyLeadTrend = [],
    teamPerformance = [],
    topProducts = [],
    categorySellers = [],
    categoryLeaderboard,
    productCategories = [],
    sellerUsers = [],
    salesPerformance = [],
    leadSources = [],
    winRateStats,
    needsAttention,
}: CompanyDashboardProps) {
    const { t } = useTranslation();

    const [leadChartType, setLeadChartType] = useState<'bar' | 'pie' | 'graph' | 'list'>('bar');
    const [dealChartType, setDealChartType] = useState<'bar' | 'pie' | 'graph' | 'list'>('bar');
    const [selectedCategory, setSelectedCategory] = useState<string>('all');
    const [selectedSeller, setSelectedSeller] = useState<string>('all');

    const filteredCategorySellers = useMemo(() => {
        return (categorySellers || []).filter(item => {
            const matchesCat = selectedCategory === 'all' || String(item.categoryId) === String(selectedCategory);
            const matchesUser = selectedSeller === 'all' || String(item.userId) === String(selectedSeller);
            return matchesCat && matchesUser;
        });
    }, [categorySellers, selectedCategory, selectedSeller]);

    const matrixData = useMemo(() => {
        if (categoryLeaderboard && categoryLeaderboard.sellers && categoryLeaderboard.sellers.length > 0) {
            return {
                categories: categoryLeaderboard.categories && categoryLeaderboard.categories.length > 0
                    ? categoryLeaderboard.categories
                    : ['General'],
                sellers: categoryLeaderboard.sellers,
                summary: categoryLeaderboard.summary,
                leaders: categoryLeaderboard.leaders || [],
                isRealData: true,
            };
        }

        return {
            categories: ['Electronics', 'Accessories', 'Furniture', 'Fashion', 'Food', 'Software', 'Others'],
            sellers: [
                {
                    id: 1,
                    rank: 1,
                    badgeColor: 'bg-amber-400 text-white',
                    avatar: 'MB',
                    avatarColor: 'bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-300',
                    name: 'Md. Maruf Billah',
                    values: { Electronics: 148, Accessories: 86, Furniture: 42, Fashion: 18, Food: 72, Software: 96, Others: 34 },
                    amounts: { Electronics: 148000, Accessories: 86000, Furniture: 42000, Fashion: 18000, Food: 72000, Software: 96000, Others: 34000 },
                    totalAmount: 496000,
                    totalUnits: 496,
                },
                {
                    id: 2,
                    rank: 2,
                    badgeColor: 'bg-slate-300 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                    avatar: 'RA',
                    avatarColor: 'bg-purple-100 text-purple-600 dark:bg-purple-950 dark:text-purple-300',
                    name: 'Rahim Ahmed',
                    values: { Electronics: 91, Accessories: 58, Furniture: 126, Fashion: 54, Food: 31, Software: 48, Others: 22 },
                    amounts: { Electronics: 91000, Accessories: 58000, Furniture: 126000, Fashion: 54000, Food: 31000, Software: 48000, Others: 22000 },
                    totalAmount: 430000,
                    totalUnits: 430,
                },
                {
                    id: 3,
                    rank: 3,
                    badgeColor: 'bg-amber-600 text-white',
                    avatar: 'SH',
                    avatarColor: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300',
                    name: 'Sabbir Hossain',
                    values: { Electronics: 64, Accessories: 39, Furniture: 37, Fashion: 142, Food: 68, Software: 29, Others: 18 },
                    amounts: { Electronics: 64000, Accessories: 39000, Furniture: 37000, Fashion: 142000, Food: 68000, Software: 29000, Others: 18000 },
                    totalAmount: 397000,
                    totalUnits: 397,
                },
                {
                    id: 4,
                    rank: 4,
                    badgeColor: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    avatar: 'TH',
                    avatarColor: 'bg-sky-100 text-sky-600 dark:bg-sky-950 dark:text-sky-300',
                    name: 'Tanvir Hasan',
                    values: { Electronics: 82, Accessories: 47, Furniture: 94, Fashion: 37, Food: 134, Software: 51, Others: 26 },
                    amounts: { Electronics: 82000, Accessories: 47000, Furniture: 94000, Fashion: 37000, Food: 134000, Software: 51000, Others: 26000 },
                    totalAmount: 471000,
                    totalUnits: 471,
                },
                {
                    id: 5,
                    rank: 5,
                    badgeColor: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    avatar: 'NJ',
                    avatarColor: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
                    name: 'Nusrat Jahan',
                    values: { Electronics: 53, Accessories: 61, Furniture: 42, Fashion: 101, Food: 45, Software: 128, Others: 38 },
                    amounts: { Electronics: 53000, Accessories: 61000, Furniture: 42000, Fashion: 101000, Food: 45000, Software: 128000, Others: 38000 },
                    totalAmount: 468000,
                    totalUnits: 468,
                },
            ],
            summary: {
                totalProductsSold: 4862,
                totalSalesValue: 1248000,
                activeSellersCount: 12,
                productCategoriesCount: 8,
            },
            leaders: [
                { category: 'Electronics', leader: 'Md. Maruf Billah', units: '148 units', amount: 148000 },
                { category: 'Accessories', leader: 'Md. Maruf Billah', units: '86 units', amount: 86000 },
                { category: 'Furniture', leader: 'Rahim Ahmed', units: '126 units', amount: 126000 },
                { category: 'Fashion', leader: 'Sabbir Hossain', units: '142 units', amount: 142000 },
                { category: 'Food', leader: 'Tanvir Hasan', units: '134 units', amount: 134000 },
                { category: 'Software', leader: 'Nusrat Jahan', units: '128 units', amount: 128000 },
            ],
            isRealData: false,
        };
    }, [categoryLeaderboard]);

    const searchParams = useMemo(() => new URLSearchParams(typeof window !== 'undefined' ? window.location.search : ''), []);
    const initialStartDate = searchParams.get('start_date') || '';
    const initialEndDate = searchParams.get('end_date') || '';
    const [leaderboardDateRange, setLeaderboardDateRange] = useState<string>(
        initialStartDate && initialEndDate ? `${initialStartDate} - ${initialEndDate}` : ''
    );

    const categoryOptions = useMemo(() => {
        const list: { id: string; name: string }[] = [{ id: 'all', name: String(t('All Categories')) }];
        const added = new Set<string>(['all']);

        (productCategories || []).forEach(cat => {
            if (cat.name && !added.has(cat.name)) {
                added.add(cat.name);
                list.push({ id: cat.name, name: cat.name });
            }
        });

        if (matrixData?.categories) {
            matrixData.categories.forEach(catName => {
                if (catName && !added.has(catName)) {
                    added.add(catName);
                    list.push({ id: catName, name: catName });
                }
            });
        }

        return list;
    }, [productCategories, matrixData.categories, t]);

    const sellerOptions = useMemo(() => {
        const list: { id: string; name: string }[] = [{ id: 'all', name: String(t('All Sellers')) }];
        const added = new Set<string>(['all']);

        (sellerUsers || []).forEach(usr => {
            if (usr.name && !added.has(usr.name)) {
                added.add(usr.name);
                list.push({ id: usr.name, name: usr.name });
            }
        });

        if (matrixData?.sellers) {
            matrixData.sellers.forEach(s => {
                if (s.name && !added.has(s.name)) {
                    added.add(s.name);
                    list.push({ id: s.name, name: s.name });
                }
            });
        }

        return list;
    }, [sellerUsers, matrixData.sellers, t]);

    const filteredMatrixData = useMemo(() => {
        const activeCategories = matrixData.categories.filter(cat =>
            selectedCategory === 'all' || cat === selectedCategory || cat.toLowerCase() === selectedCategory.toLowerCase()
        );

        const activeSellers = matrixData.sellers.filter(s =>
            selectedSeller === 'all' || s.name === selectedSeller || String(s.id) === String(selectedSeller) || s.name.toLowerCase() === selectedSeller.toLowerCase()
        );

        let totalProductsSold = 0;
        let totalSalesValue = 0;
        const activeSellersSet = new Set<string | number>();

        activeSellers.forEach(s => {
            activeCategories.forEach(cat => {
                const units = s.values ? (s.values[cat] || 0) : 0;
                const amt = s.amounts ? (s.amounts[cat] || 0) : 0;
                totalProductsSold += units;
                totalSalesValue += amt;
                if (units > 0 || amt > 0) {
                    activeSellersSet.add(s.id || s.name);
                }
            });
        });

        const summary = {
            totalProductsSold: (selectedCategory === 'all' && selectedSeller === 'all') ? matrixData.summary.totalProductsSold : totalProductsSold,
            totalSalesValue: (selectedCategory === 'all' && selectedSeller === 'all') ? matrixData.summary.totalSalesValue : totalSalesValue,
            activeSellersCount: selectedSeller !== 'all' ? activeSellers.length : (selectedCategory === 'all' ? matrixData.summary.activeSellersCount : activeSellersSet.size),
            productCategoriesCount: selectedCategory !== 'all' ? activeCategories.length : matrixData.summary.productCategoriesCount,
        };

        const activeLeaders = (matrixData.leaders || []).filter(item => {
            const matchCat = selectedCategory === 'all' || item.category === selectedCategory || item.category.toLowerCase() === selectedCategory.toLowerCase();
            const matchSeller = selectedSeller === 'all' || item.leader === selectedSeller || item.leader.toLowerCase() === selectedSeller.toLowerCase();
            return matchCat && matchSeller;
        });

        return {
            categories: activeCategories.length > 0 ? activeCategories : matrixData.categories,
            sellers: activeSellers,
            summary: summary,
            leaders: activeLeaders,
        };
    }, [matrixData, selectedCategory, selectedSeller]);

    const getCategoryIconInfo = (categoryName: string) => {
        const lower = categoryName.toLowerCase();
        if (lower.includes('electr') || lower.includes('tech') || lower.includes('tv') || lower.includes('device')) {
            return { icon: Tv, iconBg: 'bg-blue-50 dark:bg-blue-950/60', iconColor: 'text-blue-500', statColor: 'text-blue-600 dark:text-blue-400' };
        }
        if (lower.includes('access') || lower.includes('headphone') || lower.includes('gadget')) {
            return { icon: Headphones, iconBg: 'bg-purple-50 dark:bg-purple-950/60', iconColor: 'text-purple-500', statColor: 'text-emerald-600 dark:text-emerald-400' };
        }
        if (lower.includes('furnit') || lower.includes('home') || lower.includes('desk') || lower.includes('chair')) {
            return { icon: Armchair, iconBg: 'bg-rose-50 dark:bg-rose-950/60', iconColor: 'text-rose-500', statColor: 'text-orange-600 dark:text-orange-400' };
        }
        if (lower.includes('fash') || lower.includes('cloth') || lower.includes('wear') || lower.includes('apparel')) {
            return { icon: Shirt, iconBg: 'bg-amber-50 dark:bg-amber-950/60', iconColor: 'text-amber-500', statColor: 'text-pink-600 dark:text-pink-400' };
        }
        if (lower.includes('food') || lower.includes('drink') || lower.includes('snack') || lower.includes('restaur')) {
            return { icon: Utensils, iconBg: 'bg-yellow-50 dark:bg-yellow-950/60', iconColor: 'text-yellow-600', statColor: 'text-rose-600 dark:text-rose-400' };
        }
        if (lower.includes('soft') || lower.includes('code') || lower.includes('app') || lower.includes('service')) {
            return { icon: Code, iconBg: 'bg-indigo-50 dark:bg-indigo-950/60', iconColor: 'text-indigo-500', statColor: 'text-purple-600 dark:text-purple-400' };
        }
        return { icon: Tag, iconBg: 'bg-teal-50 dark:bg-teal-950/60', iconColor: 'text-teal-500', statColor: 'text-teal-600 dark:text-teal-400' };
    };

    const salesChartData = salesPerformance || [];
    const sourcesData = leadSources || [];
    const totalSourcesLeads = sourcesData.reduce((acc, curr) => acc + curr.count, 0);

    const leadsData = [...leadOverview].sort((a, b) => b.count - a.count);
    const dealsData = [...dealPipeline].sort((a, b) => b.amount - a.amount);
    const leadTrendData = dailyLeadTrend || [];

    const totalLeadsOverview = leadsData.reduce((acc, curr) => acc + curr.count, 0);
    const totalDealsPipeline = dealsData.reduce((acc, curr) => acc + curr.amount, 0);

    const teamData = teamPerformance.slice(0, 5);
    const productsData = topProducts.slice(0, 5);

    const maxLeadCount = Math.max(...leadsData.map(item => item.count), 1);
    const maxDealAmount = Math.max(...dealsData.map(item => item.amount), 1);
    const maxTeamAmount = Math.max(...teamData.map(item => item.wonAmount), 1);
    const maxProductValue = Math.max(...productsData.map(item => item.wonValue), 1);

    const winRate = winRateStats?.winRate ?? 0;
    const wonCount = winRateStats?.wonCount ?? 0;
    const lostCount = winRateStats?.lostCount ?? 0;

    const uncontacted = needsAttention?.uncontactedLeads ?? 0;
    const unassigned = needsAttention?.unassignedLeads ?? 0;
    const inactive = needsAttention?.inactiveDeals ?? 0;
    const followupToday = needsAttention?.followupLeadsToday ?? stats?.followupLeadsToday ?? 0;
    const overdueFollowups = needsAttention?.overdueLeadTasks ?? stats?.overdueLeadTasks ?? 0;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('CRM') }]}
        >
            <Head title={t('CRM Overview')} />
            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/80 dark:border-slate-800 pb-4">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight text-slate-900 dark:text-white">{t('CRM Overview')}</h1>
                        <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                            {t('Leads, Deals and Contact overview for all users.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top Row: 6 PBX Glassy KPI Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {/* Card 1: Total Leads */}
                    <div className="bg-gradient-to-br from-indigo-50/90 via-indigo-50/40 to-white/70 dark:from-indigo-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-indigo-200/60 dark:border-indigo-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-indigo-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-200/60 text-indigo-600 dark:bg-indigo-500/20 dark:border-indigo-800/60 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <Users className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Total Leads')}</span>
                            <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">
                                {(stats?.totalLeads ?? 0).toLocaleString()}
                            </span>
                        </div>
                    </div>

                    {/* Card 2: Open Deals */}
                    <div className="bg-gradient-to-br from-teal-50/90 via-teal-50/40 to-white/70 dark:from-teal-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-teal-200/60 dark:border-teal-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-teal-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-200/60 text-teal-600 dark:bg-teal-500/20 dark:border-teal-800/60 dark:text-teal-400 flex items-center justify-center shrink-0">
                            <Handshake className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Open Deals')}</span>
                            <div className="flex items-baseline gap-1 mt-0.5">
                                <span className="text-xl font-bold text-slate-900 dark:text-white">{stats?.activeDeals ?? 0}</span>
                            </div>
                            <span className="text-[10px] font-bold text-teal-600 dark:text-teal-400 block">
                                {formatCompactBDT(stats?.openDealValue ?? 0)} {t('open value')}
                            </span>
                        </div>
                    </div>

                    {/* Card 3: Won Deal Value */}
                    <div className="bg-gradient-to-br from-purple-50/90 via-purple-50/40 to-white/70 dark:from-purple-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-purple-200/60 dark:border-purple-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-purple-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-200/60 text-purple-600 dark:bg-purple-500/20 dark:border-purple-800/60 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <BarChart3 className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Won Deal Value')}</span>
                            <span className="text-lg font-bold text-slate-900 dark:text-white mt-0.5 block">
                                {formatCompactBDT(stats?.wonDealAmount ?? 0)}
                            </span>
                            <span className="text-[10px] font-semibold text-purple-600 dark:text-purple-400 block">
                                {stats?.wonDealsThisMonth ?? 0} {t('deals this month')}
                            </span>
                        </div>
                    </div>

                    {/* Card 4: Win Rate */}
                    <div className="bg-gradient-to-br from-emerald-50/90 via-emerald-50/40 to-white/70 dark:from-emerald-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-emerald-200/60 dark:border-emerald-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-emerald-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-200/60 text-emerald-600 dark:bg-emerald-500/20 dark:border-emerald-800/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <Target className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Win Rate')}</span>
                            <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">{winRate}%</span>
                            <span className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 block">
                                {wonCount} {t('won')} · {lostCount} {t('lost')}
                            </span>
                        </div>
                    </div>

                    {/* Card 5: Calls Today */}
                    <div className="bg-gradient-to-br from-sky-50/90 via-sky-50/40 to-white/70 dark:from-sky-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-sky-200/60 dark:border-sky-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-sky-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-200/60 text-sky-600 dark:bg-sky-500/20 dark:border-sky-800/60 dark:text-sky-400 flex items-center justify-center shrink-0">
                            <Phone className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Calls Today')}</span>
                            <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">{stats?.callsToday ?? 0}</span>
                            <span className="text-[10px] font-semibold text-sky-600 dark:text-sky-400 block">
                                {stats?.connectedCallsToday ?? 0} {t('connected')} · {stats?.missedCallsToday ?? 0} {t('missed')}
                            </span>
                        </div>
                    </div>

                    {/* Card 6: Overdue Follow-ups */}
                    <div className="bg-gradient-to-br from-amber-50/90 via-amber-50/40 to-white/70 dark:from-amber-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-amber-200/60 dark:border-amber-900/50 backdrop-blur-md rounded-2xl p-4 shadow-sm shadow-amber-500/5 hover:shadow-md transition-all duration-300 flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-200/60 text-amber-700 dark:bg-amber-500/20 dark:border-amber-800/60 dark:text-amber-400 flex items-center justify-center shrink-0 font-bold">
                            !
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block leading-tight">{t('Overdue Follow-ups')}</span>
                            <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">{stats?.overdueTasks ?? 0}</span>
                            <span className="text-[10px] font-bold text-amber-700 dark:text-amber-400 block">{t('Needs attention')}</span>
                        </div>
                    </div>
                </div>

                {/* Two Side-by-Side Timeline Cards: Leads & Deals Breakdown (PBX Glassy Theme) */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Box 1: Lead Performance Timeline */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none" />
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-2.5">
                                    <div className="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                                        <Users className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-slate-900 dark:text-white leading-snug">{t('Lead Performance')}</h3>
                                        <p className="text-xs font-semibold text-slate-400">{t('Today, Yesterday & Monthly Lead Influx')}</p>
                                    </div>
                                </div>
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-[11px] font-bold border border-indigo-200/60 dark:border-indigo-800/60">
                                    <Sparkles className="w-3 h-3" />
                                    {t('Leads')}
                                </span>
                            </div>

                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                                {/* Today's Leads */}
                                <div className="bg-gradient-to-br from-indigo-50/70 to-indigo-100/30 dark:from-indigo-950/30 dark:to-slate-900/60 border border-indigo-100 dark:border-indigo-900/40 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Today's")}</span>
                                    <span className="text-xl font-bold text-indigo-600 dark:text-indigo-400 block">{stats?.todayLeads ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-indigo-500 dark:text-indigo-400/80 mt-1 block">{t('Leads')}</span>
                                </div>

                                {/* Yesterday's Leads */}
                                <div className="bg-gradient-to-br from-slate-50/80 to-slate-100/40 dark:from-slate-800/40 dark:to-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Yesterday's")}</span>
                                    <span className="text-xl font-bold text-slate-800 dark:text-slate-200 block">{stats?.yesterdayLeads ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 block">{t('Leads')}</span>
                                </div>

                                {/* This Month's Leads */}
                                <div className="bg-gradient-to-br from-blue-50/70 to-blue-100/30 dark:from-blue-950/30 dark:to-slate-900/60 border border-blue-100 dark:border-blue-900/40 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("This Month")}</span>
                                    <span className="text-xl font-bold text-blue-600 dark:text-blue-400 block">{stats?.thisMonthLeads ?? stats?.monthLeads ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-blue-500 dark:text-blue-400/80 mt-1 block">{t('Leads')}</span>
                                </div>

                                {/* Previous Month's Leads */}
                                <div className="bg-gradient-to-br from-slate-50/80 to-slate-100/40 dark:from-slate-800/40 dark:to-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Prev Month")}</span>
                                    <span className="text-xl font-bold text-slate-700 dark:text-slate-300 block">{stats?.prevMonthLeads ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 block">{t('Leads')}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Box 2: Deal Performance Timeline */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none" />
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-2.5">
                                    <div className="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                                        <Handshake className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-slate-900 dark:text-white leading-snug">{t('Deal Performance')}</h3>
                                        <p className="text-xs font-semibold text-slate-400">{t('Today, Yesterday & Monthly Deal Activity')}</p>
                                    </div>
                                </div>
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-[11px] font-bold border border-emerald-200/60 dark:border-emerald-800/60">
                                    <TrendingUp className="w-3 h-3" />
                                    {t('Deals')}
                                </span>
                            </div>

                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                                {/* Today's Deals */}
                                <div className="bg-gradient-to-br from-emerald-50/70 to-emerald-100/30 dark:from-emerald-950/30 dark:to-slate-900/60 border border-emerald-100 dark:border-emerald-900/40 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Today's")}</span>
                                    <span className="text-xl font-bold text-emerald-600 dark:text-emerald-400 block">{stats?.todayDeals ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-emerald-500 dark:text-emerald-400/80 mt-1 block">{t('Deals')}</span>
                                </div>

                                {/* Yesterday's Deals */}
                                <div className="bg-gradient-to-br from-slate-50/80 to-slate-100/40 dark:from-slate-800/40 dark:to-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Yesterday's")}</span>
                                    <span className="text-xl font-bold text-slate-800 dark:text-slate-200 block">{stats?.yesterdayDeals ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 block">{t('Deals')}</span>
                                </div>

                                {/* This Month's Deals */}
                                <div className="bg-gradient-to-br from-teal-50/70 to-teal-100/30 dark:from-teal-950/30 dark:to-slate-900/60 border border-teal-100 dark:border-teal-900/40 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("This Month")}</span>
                                    <span className="text-xl font-bold text-teal-600 dark:text-teal-400 block">{stats?.thisMonthDeals ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-teal-500 dark:text-teal-400/80 mt-1 block">{t('Deals')}</span>
                                </div>

                                {/* Previous Month's Deals */}
                                <div className="bg-gradient-to-br from-slate-50/80 to-slate-100/40 dark:from-slate-800/40 dark:to-slate-900/60 border border-slate-200/60 dark:border-slate-800 rounded-xl p-3.5 flex flex-col justify-between">
                                    <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">{t("Prev Month")}</span>
                                    <span className="text-xl font-bold text-slate-700 dark:text-slate-300 block">{stats?.prevMonthDeals ?? 0}</span>
                                    <span className="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1 block">{t('Deals')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 2. Second Row: Sales Performance, Lead Sources & Needs Attention Box */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {/* Sales Performance Area Chart (Col 5) */}
                    <div className="lg:col-span-5 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Sales Performance')}</h3>
                            <p className="text-xs font-semibold text-slate-400 mb-4">{t('Monthly won deal value (BDT)')}</p>

                            <div className="h-[230px] w-full">
                                {salesChartData.length > 0 ? (
                                    <ResponsiveContainer width="100%" height="100%">
                                        <AreaChart data={salesChartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                            <defs>
                                                <linearGradient id="salesGradient" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.25} />
                                                    <stop offset="95%" stopColor="#3b82f6" stopOpacity={0.0} />
                                                </linearGradient>
                                            </defs>
                                            <XAxis dataKey="month" tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                            <YAxis tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                            <Tooltip
                                                formatter={(val: any) => [`BDT ${Number(val).toLocaleString()}`, t('Won Value')]}
                                                contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }}
                                            />
                                            <Area type="monotone" dataKey="value" stroke="#3b82f6" strokeWidth={1.5} fillOpacity={1} fill="url(#salesGradient)" />
                                        </AreaChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="h-full flex items-center justify-center text-xs font-semibold text-slate-400">
                                        {t('No sales data available')}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Lead Sources Donut Chart (Col 4) */}
                    <div className="lg:col-span-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Lead Sources')}</h3>
                            <p className="text-xs font-semibold text-slate-400 mb-4">{t('Total leads by source')}</p>

                            {sourcesData.length > 0 ? (
                                <div className="grid grid-cols-1 items-center gap-4">
                                    {/* Donut Pie */}
                                    <div className="relative h-[160px] flex items-center justify-center">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={sourcesData}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={45}
                                                    outerRadius={68}
                                                    paddingAngle={3}
                                                    dataKey="count"
                                                    labelLine={false}
                                                    label={({ percent }) => (percent && percent >= 0.05 ? `${(percent * 100).toFixed(0)}%` : '')}
                                                >
                                                    {sourcesData.map((entry, idx) => (
                                                        <Cell key={`source-cell-${idx}`} fill={entry.color} />
                                                    ))}
                                                </Pie>
                                                <Tooltip formatter={(val: any) => [val, t('Leads')]} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                        <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                            <span className="text-base font-bold text-slate-900 dark:text-white leading-none">
                                                {totalSourcesLeads.toLocaleString()}
                                            </span>
                                            <span className="text-[10px] font-semibold text-slate-400 mt-0.5">{t('leads')}</span>
                                        </div>
                                    </div>

                                    {/* Legend Table */}
                                    <div className="space-y-1.5 max-h-[110px] overflow-y-auto pr-1">
                                        {sourcesData.map((item, idx) => {
                                            const pct = totalSourcesLeads > 0 ? ((item.count / totalSourcesLeads) * 100).toFixed(1) : '0';
                                            return (
                                                <div key={idx} className="flex items-center justify-between text-xs font-semibold p-1.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 backdrop-blur-sm">
                                                    <div className="flex items-center gap-2 truncate">
                                                        <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: item.color }} />
                                                        <span className="text-slate-700 dark:text-slate-300 truncate">{t(item.name)}</span>
                                                    </div>
                                                    <div className="flex items-center gap-1.5 shrink-0">
                                                        <span className="text-slate-900 dark:text-white font-extrabold">{item.count}</span>
                                                        <span className="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-200/80 dark:bg-slate-700/80 px-1.5 py-0.5 rounded-full">{pct}%</span>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            ) : (
                                <div className="h-[200px] flex items-center justify-center text-xs font-semibold text-slate-400">
                                    {t('No lead sources recorded')}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Needs Attention Box (Col 3) */}
                    <div className="lg:col-span-3 bg-gradient-to-br from-amber-50/90 via-orange-50/40 to-white/80 dark:from-amber-950/40 dark:via-slate-900/80 dark:to-slate-900 border border-amber-200/80 dark:border-amber-900/50 backdrop-blur-md rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div className="flex items-center gap-2 mb-4">
                                <div className="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-center shrink-0">
                                    <AlertTriangle className="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Needs Attention')}</h3>
                                    <p className="text-[11px] font-semibold text-slate-400">{t('Items requiring action')}</p>
                                </div>
                            </div>

                            <div className="space-y-2.5">
                                {/* Lead Follow-up Today */}
                                <div className="flex items-center justify-between p-2.5 rounded-xl bg-white/80 dark:bg-slate-800/60 border border-indigo-100 dark:border-indigo-900/40 backdrop-blur-sm shadow-2xs">
                                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300">{t('Lead follow-up today')}</span>
                                    <span className="text-sm font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/60 px-2 py-0.5 rounded-lg">
                                        {followupToday}
                                    </span>
                                </div>

                                {/* Overdue Lead Follow-up */}
                                <div className="flex items-center justify-between p-2.5 rounded-xl bg-white/80 dark:bg-slate-800/60 border border-rose-100 dark:border-rose-900/40 backdrop-blur-sm shadow-2xs">
                                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300">{t('Overdue lead follow-up')}</span>
                                    <span className={`text-sm font-black px-2 py-0.5 rounded-lg border ${overdueFollowups > 0
                                        ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 border-rose-200 dark:border-rose-800/60'
                                        : 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700'
                                        }`}>
                                        {overdueFollowups}
                                    </span>
                                </div>

                                {/* Uncontacted leads */}
                                <div className="flex items-center justify-between p-2.5 rounded-xl bg-white/80 dark:bg-slate-800/60 border border-amber-100 dark:border-amber-900/40 backdrop-blur-sm shadow-2xs">
                                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300">{t('Uncontacted leads')}</span>
                                    <span className="text-sm font-black text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/60 px-2 py-0.5 rounded-lg">
                                        {uncontacted}
                                    </span>
                                </div>

                                {/* Unassigned leads */}
                                <div className="flex items-center justify-between p-2.5 rounded-xl bg-white/80 dark:bg-slate-800/60 border border-amber-100 dark:border-amber-900/40 backdrop-blur-sm shadow-2xs">
                                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300">{t('Unassigned leads')}</span>
                                    <span className="text-sm font-black text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/60 px-2 py-0.5 rounded-lg">
                                        {unassigned}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 3. Third Row: Glassy Dynamic Lead Overview & Deal Pipeline */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Dynamic Lead Overview Card */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col min-h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Lead Overview')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Leads by pipeline')}</p>
                            </div>
                            <div className="flex items-center gap-1 bg-slate-100/80 dark:bg-slate-800/80 backdrop-blur-sm p-1 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                                <button
                                    onClick={() => setLeadChartType('bar')}
                                    title={t('Bar Chart')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${leadChartType === 'bar' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <BarChart2 className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setLeadChartType('pie')}
                                    title={t('Pie Chart')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${leadChartType === 'pie' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <PieChartIcon className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setLeadChartType('graph')}
                                    title={t('Area Graph')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${leadChartType === 'graph' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <TrendingUp className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setLeadChartType('list')}
                                    title={t('List View')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${leadChartType === 'list' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <List className="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div className="flex-1 w-full min-h-[220px]">
                            {leadsData.length > 0 ? (
                                <>
                                    {leadChartType === 'bar' && (
                                        <div className="h-[230px] w-full">
                                            <ResponsiveContainer width="100%" height="100%">
                                                <BarChart data={leadsData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                                    <XAxis dataKey="name" tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <YAxis tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <Tooltip contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    <Bar dataKey="count" name={t('Leads')} fill="#6366f1" radius={[6, 6, 0, 0]} />
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </div>
                                    )}

                                    {leadChartType === 'pie' && (
                                        <div className="grid grid-cols-1 sm:grid-cols-2 items-center gap-4 h-[230px]">
                                            <div className="h-[210px] w-full relative flex items-center justify-center">
                                                <ResponsiveContainer width="100%" height="100%">
                                                    <PieChart>
                                                        <Pie
                                                            data={leadsData}
                                                            dataKey="count"
                                                            nameKey="name"
                                                            cx="50%"
                                                            cy="50%"
                                                            innerRadius={40}
                                                            outerRadius={75}
                                                            paddingAngle={3}
                                                            labelLine={false}
                                                            label={({ percent }) => (percent && percent >= 0.05 ? `${(percent * 100).toFixed(0)}%` : '')}
                                                        >
                                                            {leadsData.map((_, idx) => (
                                                                <Cell key={`lead-cell-${idx}`} fill={CHART_COLORS[idx % CHART_COLORS.length]} />
                                                            ))}
                                                        </Pie>
                                                        <Tooltip contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    </PieChart>
                                                </ResponsiveContainer>
                                            </div>

                                            {/* Identifiers Legend Table */}
                                            <div className="space-y-2 max-h-[210px] overflow-y-auto pr-1">
                                                {leadsData.map((item, idx) => {
                                                    const pct = totalLeadsOverview > 0 ? ((item.count / totalLeadsOverview) * 100).toFixed(1) : '0';
                                                    return (
                                                        <div key={idx} className="flex items-center justify-between text-xs font-semibold p-2 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 backdrop-blur-sm">
                                                            <div className="flex items-center gap-2 truncate">
                                                                <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: CHART_COLORS[idx % CHART_COLORS.length] }} />
                                                                <span className="text-slate-700 dark:text-slate-300 truncate">{t(item.name)}</span>
                                                            </div>
                                                            <div className="flex items-center gap-2 shrink-0">
                                                                <span className="text-slate-900 dark:text-white font-extrabold">{item.count}</span>
                                                                <span className="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200/50 dark:border-indigo-800/50 px-1.5 py-0.5 rounded-full">{pct}%</span>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    )}

                                    {leadChartType === 'graph' && (
                                        <div className="h-[230px] w-full">
                                            <ResponsiveContainer width="100%" height="100%">
                                                <AreaChart data={leadsData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                                    <defs>
                                                        <linearGradient id="leadAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                                            <stop offset="5%" stopColor="#6366f1" stopOpacity={0.4} />
                                                            <stop offset="95%" stopColor="#6366f1" stopOpacity={0.0} />
                                                        </linearGradient>
                                                    </defs>
                                                    <XAxis dataKey="name" tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <YAxis tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <Tooltip contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    <Area type="monotone" dataKey="count" stroke="#6366f1" strokeWidth={1.5} fillOpacity={1} fill="url(#leadAreaGrad)" name={t('Leads')} />
                                                </AreaChart>
                                            </ResponsiveContainer>
                                        </div>
                                    )}

                                    {leadChartType === 'list' && (
                                        <div className="overflow-y-auto space-y-4 pr-1 max-h-[220px]">
                                            {leadsData.map((item, idx) => {
                                                const percentage = Math.min(Math.round((item.count / maxLeadCount) * 100), 100);
                                                return (
                                                    <div key={item.id || idx} className="space-y-1">
                                                        <div className="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                                                            <span>{t(item.name)}</span>
                                                            <span className="text-slate-900 dark:text-white font-extrabold">{item.count}</span>
                                                        </div>
                                                        <div className="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                            <div
                                                                className="bg-indigo-500 h-full rounded-full transition-all duration-500"
                                                                style={{ width: `${percentage}%` }}
                                                            />
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="h-full flex items-center justify-center text-xs font-semibold text-slate-400">
                                    {t('No lead pipeline data available')}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Dynamic Deal Pipeline Card */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col min-h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Deal Pipeline')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Open deal value by pipeline')}</p>
                            </div>
                            <div className="flex items-center gap-1 bg-slate-100/80 dark:bg-slate-800/80 backdrop-blur-sm p-1 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                                <button
                                    onClick={() => setDealChartType('bar')}
                                    title={t('Bar Chart')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${dealChartType === 'bar' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <BarChart2 className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setDealChartType('pie')}
                                    title={t('Pie Chart')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${dealChartType === 'pie' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <PieChartIcon className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setDealChartType('graph')}
                                    title={t('Area Graph')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${dealChartType === 'graph' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <TrendingUp className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => setDealChartType('list')}
                                    title={t('List View')}
                                    className={`p-1.5 rounded-lg text-xs font-bold transition-all ${dealChartType === 'list' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'}`}
                                >
                                    <List className="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div className="flex-1 w-full min-h-[220px]">
                            {dealsData.length > 0 ? (
                                <>
                                    {dealChartType === 'bar' && (
                                        <div className="h-[230px] w-full">
                                            <ResponsiveContainer width="100%" height="100%">
                                                <BarChart data={dealsData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                                    <XAxis dataKey="name" tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <YAxis tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                                    <Tooltip formatter={(val: any) => [formatCompactBDT(Number(val)), t('Open Value')]} contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    <Bar dataKey="amount" name={t('Amount')} fill="#14b8a6" radius={[6, 6, 0, 0]} />
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </div>
                                    )}

                                    {dealChartType === 'pie' && (
                                        <div className="grid grid-cols-1 sm:grid-cols-2 items-center gap-4 h-[230px]">
                                            <div className="h-[210px] w-full relative flex items-center justify-center">
                                                <ResponsiveContainer width="100%" height="100%">
                                                    <PieChart>
                                                        <Pie
                                                            data={dealsData}
                                                            dataKey="amount"
                                                            nameKey="name"
                                                            cx="50%"
                                                            cy="50%"
                                                            innerRadius={40}
                                                            outerRadius={75}
                                                            paddingAngle={3}
                                                            labelLine={false}
                                                            label={({ percent }) => (percent && percent >= 0.05 ? `${(percent * 100).toFixed(0)}%` : '')}
                                                        >
                                                            {dealsData.map((_, idx) => (
                                                                <Cell key={`deal-cell-${idx}`} fill={CHART_COLORS[(idx + 1) % CHART_COLORS.length]} />
                                                            ))}
                                                        </Pie>
                                                        <Tooltip formatter={(val: any) => [formatCompactBDT(Number(val)), t('Open Value')]} contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    </PieChart>
                                                </ResponsiveContainer>
                                            </div>

                                            {/* Identifiers Legend Table */}
                                            <div className="space-y-2 max-h-[210px] overflow-y-auto pr-1">
                                                {dealsData.map((item, idx) => {
                                                    const pct = totalDealsPipeline > 0 ? ((item.amount / totalDealsPipeline) * 100).toFixed(1) : '0';
                                                    return (
                                                        <div key={idx} className="flex items-center justify-between text-xs font-semibold p-2 rounded-xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 backdrop-blur-sm">
                                                            <div className="flex items-center gap-2 truncate">
                                                                <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: CHART_COLORS[(idx + 1) % CHART_COLORS.length] }} />
                                                                <span className="text-slate-700 dark:text-slate-300 truncate">{t(item.name)}</span>
                                                            </div>
                                                            <div className="flex items-center gap-2 shrink-0">
                                                                <span className="text-slate-900 dark:text-white font-extrabold">{formatCompactBDT(item.amount)}</span>
                                                                <span className="text-[10px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/50 border border-teal-200/50 dark:border-teal-800/50 px-1.5 py-0.5 rounded-full">{pct}%</span>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    )}

                                    {dealChartType === 'graph' && (
                                        <div className="h-[230px] w-full">
                                            <ResponsiveContainer width="100%" height="100%">
                                                <AreaChart data={dealsData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                                    <defs>
                                                        <linearGradient id="dealAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                                            <stop offset="5%" stopColor="#14b8a6" stopOpacity={0.4} />
                                                            <stop offset="95%" stopColor="#14b8a6" stopOpacity={0.0} />
                                                        </linearGradient>
                                                    </defs>
                                                    <XAxis dataKey="name" tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} />
                                                    <YAxis tick={{ fontSize: 11, fill: '#94a3b8' }} axisLine={false} tickLine={false} tickFormatter={(v) => `${v / 1000}K`} />
                                                    <Tooltip formatter={(val: any) => [formatCompactBDT(Number(val)), t('Open Value')]} contentStyle={{ backgroundColor: '#ffffff', borderRadius: '10px', border: '1px solid #e2e8f0', fontSize: '12px' }} />
                                                    <Area type="monotone" dataKey="amount" stroke="#14b8a6" strokeWidth={1.5} fillOpacity={1} fill="url(#dealAreaGrad)" name={t('Amount')} />
                                                </AreaChart>
                                            </ResponsiveContainer>
                                        </div>
                                    )}

                                    {dealChartType === 'list' && (
                                        <div className="overflow-y-auto space-y-4 pr-1 max-h-[220px]">
                                            {dealsData.map((item, idx) => {
                                                const percentage = Math.min(Math.round((item.amount / maxDealAmount) * 100), 100);
                                                return (
                                                    <div key={item.id || idx} className="space-y-1">
                                                        <div className="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                                                            <span>{t(item.name)}</span>
                                                            <span className="text-slate-900 dark:text-white font-extrabold">
                                                                {formatCompactBDT(item.amount)} · {item.deals} {t('deals')}
                                                            </span>
                                                        </div>
                                                        <div className="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                            <div
                                                                className="bg-teal-500 h-full rounded-full transition-all duration-500"
                                                                style={{ width: `${percentage}%` }}
                                                            />
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="h-full flex items-center justify-center text-xs font-semibold text-slate-400">
                                    {t('No deal pipeline data available')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* 4. Fourth Row: Top Performers & Top Products (Glassy Styling) */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Top Performers */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-[280px]">
                        <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Top Performers')}</h3>
                        <p className="text-xs font-semibold text-slate-400 mb-4">{t('Won deals and value this month')}</p>

                        <div className="flex-1 overflow-y-auto space-y-4 pr-1">
                            {teamData.length > 0 ? (
                                teamData.map((member, idx) => {
                                    const rank = idx + 1;
                                    let badgeBg = 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
                                    if (rank === 1) badgeBg = 'bg-amber-400 text-white';
                                    else if (rank === 2) badgeBg = 'bg-slate-300 text-slate-700';
                                    else if (rank === 3) badgeBg = 'bg-amber-600 text-white';

                                    const percentage = Math.min(Math.round((member.wonAmount / maxTeamAmount) * 100), 100);

                                    return (
                                        <div key={member.id || idx} className="flex items-center gap-3">
                                            <span className={`w-6 h-6 rounded-full flex items-center justify-center font-black text-xs shrink-0 ${badgeBg}`}>
                                                {rank}
                                            </span>
                                            <span className="w-32 text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{member.name}</span>
                                            <div className="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                <div
                                                    className="bg-indigo-500 h-full rounded-full transition-all duration-500"
                                                    style={{ width: `${percentage}%` }}
                                                />
                                            </div>
                                            <span className="text-xs font-semibold text-slate-500 dark:text-slate-400 shrink-0">{member.wonDeals} {t('deals')}</span>
                                            <span className="text-xs font-bold text-slate-900 dark:text-white shrink-0">{formatCompactBDT(member.wonAmount)}</span>
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="h-full flex items-center justify-center text-xs font-semibold text-slate-400">
                                    {t('No team performance records')}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Top Products */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-[280px]">
                        <h3 className="text-base font-bold text-slate-900 dark:text-white">{t('Top Products')}</h3>
                        <p className="text-xs font-semibold text-slate-400 mb-4">{t('Won deal value this month')}</p>

                        <div className="flex-1 overflow-y-auto space-y-4 pr-1">
                            {productsData.length > 0 ? (
                                productsData.map((item, idx) => {
                                    const percentage = Math.min(Math.round((item.wonValue / maxProductValue) * 100), 100);
                                    return (
                                        <div key={item.id || idx} className="flex items-center gap-3">
                                            <span className="w-36 text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{item.name}</span>
                                            <div className="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                                <div
                                                    className="bg-purple-400 h-full rounded-full transition-all duration-500"
                                                    style={{ width: `${percentage}%` }}
                                                />
                                            </div>
                                            <span className="text-xs font-bold text-slate-900 dark:text-white shrink-0">{formatCompactBDT(item.wonValue)}</span>
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="h-full flex items-center justify-center text-xs font-semibold text-slate-400">
                                    {t('No product performance records')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* 5. Fifth Row: Top Sellers in Product Category (Exact Design Match) */}
                <div className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300">
                    {/* Header & Controls */}
                    <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 className="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{t('Top Sellers in Product Category')}</h2>
                            <p className="text-xs font-semibold text-slate-400 mt-0.5">{t('Which category item was sold by whom')}</p>
                        </div>

                        {/* Filters & Date Picker */}
                        <div className="flex flex-wrap items-center gap-3">
                            {/* Category Filter */}
                            <div className="flex items-center gap-2">
                                <span className="text-slate-400 font-bold text-xs">{t('Category')}:</span>
                                <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                                    <SelectTrigger className="h-9 min-w-[160px] bg-slate-50/80 dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/80 text-xs font-bold rounded-xl focus:ring-0">
                                        <SelectValue placeholder={t('All Categories')} />
                                    </SelectTrigger>
                                    <SelectContent searchable searchPlaceholder={t('Search Category...')} className="text-xs">
                                        {categoryOptions.map((opt) => (
                                            <SelectItem key={opt.id} value={opt.id} className="text-xs font-medium">
                                                {opt.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Seller Filter */}
                            <div className="flex items-center gap-2">
                                <span className="text-slate-400 font-bold text-xs">{t('Seller')}:</span>
                                <Select value={selectedSeller} onValueChange={setSelectedSeller}>
                                    <SelectTrigger className="h-9 min-w-[160px] bg-slate-50/80 dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/80 text-xs font-bold rounded-xl focus:ring-0">
                                        <SelectValue placeholder={t('All Sellers')} />
                                    </SelectTrigger>
                                    <SelectContent searchable searchPlaceholder={t('Search Seller...')} className="text-xs">
                                        {sellerOptions.map((opt) => (
                                            <SelectItem key={opt.id} value={opt.id} className="text-xs font-medium">
                                                {opt.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Date Range Picker */}
                            <div className="flex items-center">
                                <DateRangePicker
                                    value={leaderboardDateRange}
                                    onChange={(val) => {
                                        setLeaderboardDateRange(val);
                                        if (val && val.includes(' - ')) {
                                            const [start, end] = val.split(' - ');
                                            router.get(
                                                window.location.pathname,
                                                { period: 'custom', start_date: start, end_date: end },
                                                { preserveState: true, preserveScroll: true, replace: true }
                                            );
                                        }
                                    }}
                                    placeholder={t('Select Date Range')}
                                    className="h-9 min-w-[210px] bg-slate-50/80 dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/80 text-xs font-bold rounded-xl"
                                />
                            </div>
                        </div>
                    </div>

                    {/* 4 KPI Summary Stats Cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        {/* Card 1: Total Products Sold */}
                        <div className="bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-11 h-11 rounded-2xl bg-emerald-100/70 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <Package className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="text-[11px] font-bold text-slate-400 block leading-tight">{t('Total Products Sold')}</span>
                                    <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">
                                        {filteredMatrixData.summary.totalProductsSold.toLocaleString()}
                                    </span>
                                </div>
                            </div>
                            <div className="text-right">
                                <span className="inline-flex items-center gap-0.5 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    <span className="text-[10px]">▲</span> 12%
                                </span>
                                <span className="text-[10px] font-semibold text-slate-400 block">{t('vs last month')}</span>
                            </div>
                        </div>

                        {/* Card 2: Total Sales Value */}
                        <div className="bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-11 h-11 rounded-2xl bg-blue-100/70 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
                                    <BadgeDollarSign className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="text-[11px] font-bold text-slate-400 block leading-tight">{t('Total Sales Value')}</span>
                                    <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">
                                        {formatCompactBDT(filteredMatrixData.summary.totalSalesValue)}
                                    </span>
                                </div>
                            </div>
                            <div className="text-right">
                                <span className="inline-flex items-center gap-0.5 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    <span className="text-[10px]">▲</span> 18%
                                </span>
                                <span className="text-[10px] font-semibold text-slate-400 block">{t('vs last month')}</span>
                            </div>
                        </div>

                        {/* Card 3: Active Sellers */}
                        <div className="bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-11 h-11 rounded-2xl bg-purple-100/70 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center shrink-0">
                                    <Users className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="text-[11px] font-bold text-slate-400 block leading-tight">{t('Active Sellers')}</span>
                                    <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">
                                        {filteredMatrixData.summary.activeSellersCount}
                                    </span>
                                </div>
                            </div>
                            <div className="text-right">
                                <span className="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 block">+2</span>
                                <span className="text-[10px] font-semibold text-slate-400 block">{t('vs last month')}</span>
                            </div>
                        </div>

                        {/* Card 4: Product Categories */}
                        <div className="bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-4 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-11 h-11 rounded-2xl bg-orange-100/70 text-orange-600 dark:bg-orange-950/60 dark:text-orange-400 flex items-center justify-center shrink-0">
                                    <Tag className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="text-[11px] font-bold text-slate-400 block leading-tight">{t('Product Categories')}</span>
                                    <span className="text-xl font-bold text-slate-900 dark:text-white mt-0.5 block">
                                        {filteredMatrixData.summary.productCategoriesCount}
                                    </span>
                                </div>
                            </div>
                            <div className="text-right">
                                <span className="text-xs font-extrabold text-slate-400 block">0%</span>
                                <span className="text-[10px] font-semibold text-slate-400 block">{t('vs last month')}</span>
                            </div>
                        </div>
                    </div>

                    {/* Heatmap Matrix Table (Left ~75%) & Category Leaders (Right ~25%) */}
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {/* Heatmap Matrix Table */}
                        <div className="lg:col-span-8 overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
                            {filteredMatrixData.sellers.length > 0 ? (
                                <table className="w-full text-left border-collapse min-w-[650px]">
                                    <thead>
                                        <tr className="border-b border-slate-200/60 dark:border-slate-800 text-[11px] font-bold text-slate-400">
                                            <th className="py-3.5 px-3 text-center w-10">#</th>
                                            <th className="py-3.5 px-4 text-left">{t('Seller')}</th>
                                            {filteredMatrixData.categories.map((cat) => (
                                                <th key={cat} className="py-3.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                                    {t(cat)}
                                                </th>
                                            ))}
                                            <th className="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200">{t('Total')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                        {filteredMatrixData.sellers.map((sellerRow) => {
                                            const visibleCats = filteredMatrixData.categories;
                                            const valuesArr = visibleCats.map(cat => sellerRow.values ? (sellerRow.values[cat] || 0) : 0);
                                            const maxValInRow = Math.max(...valuesArr, 0);

                                            return (
                                                <tr key={sellerRow.rank || sellerRow.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                                    {/* Rank Badge */}
                                                    <td className="py-3.5 px-3 text-center">
                                                        <span className={`w-6 h-6 rounded-full flex items-center justify-center font-black text-xs mx-auto ${sellerRow.badgeColor}`}>
                                                            {sellerRow.rank}
                                                        </span>
                                                    </td>

                                                    {/* Seller Avatar & Name */}
                                                    <td className="py-3.5 px-4">
                                                        <div className="flex items-center gap-2.5">
                                                            <div className={`w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0 ${sellerRow.avatarColor}`}>
                                                                {sellerRow.avatar}
                                                            </div>
                                                            <span className="font-bold text-slate-900 dark:text-white whitespace-nowrap">{sellerRow.name}</span>
                                                        </div>
                                                    </td>

                                                    {/* Category Matrix Heatmap Cells */}
                                                    {visibleCats.map((catName) => {
                                                        const cellVal = sellerRow.values ? (sellerRow.values[catName] || 0) : 0;
                                                        const cellAmount = sellerRow.amounts ? (sellerRow.amounts[catName] || 0) : 0;
                                                        const isPeak = cellVal === maxValInRow && cellVal > 0;
                                                        const ratio = maxValInRow > 0 ? cellVal / maxValInRow : 0;

                                                        let bgStyle = '';
                                                        if (isPeak) {
                                                            bgStyle = 'bg-[#2563eb] text-white font-black shadow-2xs';
                                                        } else if (ratio >= 0.7) {
                                                            bgStyle = 'bg-blue-200/90 text-slate-900 font-bold dark:bg-blue-900/40 dark:text-blue-200';
                                                        } else if (ratio >= 0.4) {
                                                            bgStyle = 'bg-blue-100/80 text-slate-800 font-semibold dark:bg-blue-950/40 dark:text-blue-300';
                                                        } else if (ratio > 0) {
                                                            bgStyle = 'bg-blue-50/70 text-slate-700 font-medium dark:bg-slate-800/60 dark:text-slate-300';
                                                        } else {
                                                            bgStyle = 'bg-slate-50/40 text-slate-400 dark:bg-slate-900/30';
                                                        }

                                                        return (
                                                            <td key={catName} className="p-1 text-center" title={cellAmount ? `Amount: ${formatCompactBDT(cellAmount)}` : ''}>
                                                                <div className={`w-full py-2.5 px-2 rounded-lg text-center transition-all ${bgStyle}`}>
                                                                    {cellVal}
                                                                </div>
                                                            </td>
                                                        );
                                                    })}

                                                    {/* Row Total */}
                                                    <td className="py-3.5 px-4 text-center font-bold text-slate-900 dark:text-white text-xs">
                                                        {sellerRow.totalAmount ? formatCompactBDT(sellerRow.totalAmount) : (sellerRow.totalUnits || 0)}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            ) : (
                                <div className="py-12 text-center text-xs font-semibold text-slate-400">
                                    {t('No won deal category sales records found')}
                                </div>
                            )}
                        </div>

                        {/* Category Leaders Widget */}
                        <div className="lg:col-span-4 bg-slate-50/40 dark:bg-slate-800/30 rounded-2xl p-5 border border-slate-100 dark:border-slate-800/80">
                            {/* Widget Header */}
                            <div className="flex items-center justify-between mb-4 pb-2 border-b border-slate-200/50 dark:border-slate-800">
                                <div className="flex items-center gap-2">
                                    <Trophy className="w-4 h-4 text-amber-500 fill-amber-500" />
                                    <h3 className="text-sm font-bold text-slate-900 dark:text-white">{t('Category Leaders')}</h3>
                                </div>
                                <button className="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                    {t('View All')}
                                </button>
                            </div>

                            {/* Leaders List */}
                            <div className="space-y-3.5">
                                {filteredMatrixData.leaders.length > 0 ? (
                                    filteredMatrixData.leaders.map((item, idx) => {
                                        const iconInfo = getCategoryIconInfo(item.category);
                                        const IconComp = iconInfo.icon;
                                        return (
                                            <div key={idx} className="flex items-center justify-between text-xs font-semibold gap-2 group cursor-pointer hover:bg-white dark:hover:bg-slate-800/80 p-1.5 rounded-xl transition-all">
                                                {/* Left: Icon + Category Name */}
                                                <div className="flex items-center gap-2.5 w-32 shrink-0">
                                                    <div className={`w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ${iconInfo.iconBg}`}>
                                                        <IconComp className={`w-3.5 h-3.5 ${iconInfo.iconColor}`} />
                                                    </div>
                                                    <span className="font-bold text-slate-800 dark:text-slate-200 truncate">{t(item.category)}</span>
                                                </div>

                                                {/* Middle: Leader Name */}
                                                <span className="text-slate-400 dark:text-slate-500 text-[11px] font-semibold truncate text-left flex-1">
                                                    {item.leader}
                                                </span>

                                                {/* Right: Units Sold Stat + Chevron */}
                                                <div className={`flex items-center gap-1 font-bold shrink-0 ${iconInfo.statColor}`}>
                                                    <span>{item.units}</span>
                                                    <ChevronRight className="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" />
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="py-8 text-center text-xs font-semibold text-slate-400">
                                        {t('No category leaders yet')}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
