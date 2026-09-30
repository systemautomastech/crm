import React, { useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import {
    Users,
    BarChart3,
    Trophy,
    Phone,
    Calendar,
    CheckCircle2,
    Clock,
    Target,
    TrendingUp,
} from 'lucide-react';
import CustomEventCalendar, { CalendarEventItem } from '@/components/custom-event-calendar';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/utils/helpers';

interface UserDashboardProps {
    message?: string;
    stats?: {
        todayLeads?: number;
        yesterdayLeads?: number;
        avgDailyLeads?: number;
        monthlyLeads?: number;
        totalLeads?: number;
        convertedDeals?: number;
        activeDeals?: number;
        wonDeals?: number;
        lostDeals?: number;
        todayCalls?: number;
        yesterdayCalls?: number;
        monthlyCalls?: number;
        totalCalls?: number;
        completedTasks?: number;
        pendingTasks?: number;
        totalAmount?: number;
    };
    recentDeals?: any[];
    recentLeads?: any[];
    calendarEvents?: CalendarEventItem[];
    followupEvents?: CalendarEventItem[];
    taskStatusChart?: any[];
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

export default function UserDashboard({
    stats,
    recentDeals = [],
    recentLeads = [],
    calendarEvents = [],
    followupEvents = [],
}: UserDashboardProps) {
    const { t } = useTranslation();

    const todayStr = useMemo(() => new Date().toISOString().split('T')[0], []);

    const effectiveFollowupEvents = followupEvents || [];
    const effectiveTaskEvents = calendarEvents || [];
    const effectiveRecentDeals = recentDeals || [];
    const effectiveRecentLeads = recentLeads || [];

    const totalTasksCount = (stats?.completedTasks || 0) + (stats?.pendingTasks || 0);
    const completionRate = totalTasksCount > 0 ? Math.round(((stats?.completedTasks || 0) / totalTasksCount) * 100) : 0;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('My Dashboard') }]}
            pageTitle={t('User Dashboard')}
        >
            <Head title={t('User Dashboard')} />

            <div className="space-y-6 pb-8">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('User Dashboard')}</h1>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top Metrics Cards (6 Soft Colorful Cards) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {/* Card 1: Assigned Leads */}
                    <div className="bg-gradient-to-br from-indigo-50/90 to-blue-50/80 border border-indigo-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-indigo-700/80 uppercase tracking-wider">{t('Assigned Leads')}</span>
                                <div className="p-1.5 bg-indigo-500/10 rounded-lg">
                                    <Users className="h-4 w-4 text-indigo-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{stats?.totalLeads ?? 0}</h2>
                        </div>
                        <p className="text-[11px] font-semibold text-indigo-600/90 mt-2 flex items-center gap-1">
                            <span>+{stats?.todayLeads ?? 0} {t('today')} · {stats?.monthlyLeads ?? 0} {t('this month')}</span>
                        </p>
                    </div>

                    {/* Card 2: Active Deals */}
                    <div className="bg-gradient-to-br from-purple-50/90 to-fuchsia-50/80 border border-purple-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-purple-700/80 uppercase tracking-wider">{t('Active Deals')}</span>
                                <div className="p-1.5 bg-purple-500/10 rounded-lg">
                                    <BarChart3 className="h-4 w-4 text-purple-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{stats?.activeDeals ?? 0}</h2>
                        </div>
                        <p className="text-[11px] font-semibold text-purple-600/90 mt-2 flex items-center gap-1">
                            <span>{stats?.convertedDeals ?? 0} {t('converted')}</span>
                        </p>
                    </div>

                    {/* Card 3: Won Deals */}
                    <div className="bg-gradient-to-br from-emerald-50/90 to-teal-50/80 border border-emerald-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-emerald-700/80 uppercase tracking-wider">{t('Won Deals')}</span>
                                <div className="p-1.5 bg-emerald-500/10 rounded-lg">
                                    <Trophy className="h-4 w-4 text-emerald-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{stats?.wonDeals ?? 0}</h2>
                        </div>
                        <p className="text-[11px] font-semibold text-emerald-600/90 mt-2 flex items-center gap-1">
                            <span>{stats?.lostDeals ?? 0} {t('lost')}</span>
                        </p>
                    </div>

                    {/* Card 4: Calls Today */}
                    <div className="bg-gradient-to-br from-amber-50/90 to-orange-50/80 border border-amber-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-amber-700/80 uppercase tracking-wider">{t('Calls Today')}</span>
                                <div className="p-1.5 bg-amber-500/10 rounded-lg">
                                    <Phone className="h-4 w-4 text-amber-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{stats?.todayCalls ?? 0}</h2>
                        </div>
                        <p className="text-[11px] font-semibold text-amber-700/90 mt-2 flex items-center gap-1">
                            <span>{stats?.monthlyCalls ?? 0} {t('this month')}</span>
                        </p>
                    </div>

                    {/* Card 5: Completed Tasks */}
                    <div className="bg-gradient-to-br from-sky-50/90 to-cyan-50/80 border border-sky-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-sky-700/80 uppercase tracking-wider">{t('Completed Tasks')}</span>
                                <div className="p-1.5 bg-sky-500/10 rounded-lg">
                                    <CheckCircle2 className="h-4 w-4 text-sky-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{stats?.completedTasks ?? 0}</h2>
                        </div>
                        <p className="text-[11px] font-semibold text-sky-600/90 mt-2 flex items-center gap-1">
                            <span>{stats?.pendingTasks ?? 0} {t('pending')}</span>
                        </p>
                    </div>

                    {/* Card 6: Task Completion Rate */}
                    <div className="bg-gradient-to-br from-rose-50/90 to-pink-50/80 border border-rose-100/90 rounded-xl p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-bold text-rose-700/80 uppercase tracking-wider">{t('Completion Rate')}</span>
                                <div className="p-1.5 bg-rose-500/10 rounded-lg">
                                    <Target className="h-4 w-4 text-rose-600" />
                                </div>
                            </div>
                            <h2 className="text-xl font-bold text-slate-800">{completionRate}%</h2>
                        </div>
                        <div className="w-full bg-rose-200/60 rounded-full h-1.5 mt-2 overflow-hidden">
                            <div className="bg-rose-500 h-full rounded-full transition-all duration-500" style={{ width: `${completionRate}%` }} />
                        </div>
                    </div>
                </div>

                {/* 2. Calendars Section (Cards stacked one after another) */}
                <div className="space-y-6">
                    {/* Calendar 1: Upcoming Follow-ups Calendar */}
                    <CustomEventCalendar
                        title="Upcoming Follow-ups Calendar"
                        description="Scheduled follow-up calls & meetings for your leads and deals"
                        headerIcon={<Calendar className="h-5 w-5 text-blue-600" />}
                        events={effectiveFollowupEvents}
                        dotColor="bg-blue-500"
                        selectedDateTitlePrefix={t('Follow-ups on')}
                        eventsSummaryText={t('scheduled follow-ups')}
                        emptyText={t('No follow-up events scheduled on this date.')}
                    />

                    {/* Calendar 2: Tasks Calendar */}
                    <CustomEventCalendar
                        title="Tasks Calendar"
                        description="Assigned tasks schedule and completion status"
                        headerIcon={<CheckCircle2 className="h-5 w-5 text-emerald-600" />}
                        events={effectiveTaskEvents}
                        dotColor="bg-emerald-500"
                        selectedDateTitlePrefix={t('Tasks on')}
                        eventsSummaryText={t('tasks scheduled or due')}
                        emptyText={t('No task events scheduled on this date.')}
                    />
                </div>

                {/* 3. Bottom Row: Recent Assigned Deals & Recent Assigned Leads */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Assigned Deals Card */}
                    <div className="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <div className="flex items-center justify-between mb-2">
                            <div>
                                <h3 className="text-base font-bold text-slate-800 flex items-center gap-2">
                                    <Clock className="h-5 w-5 text-purple-600" />
                                    {t('Recent Assigned Deals')}
                                </h3>
                                <p className="text-xs text-slate-500 font-medium mt-0.5">{t('Latest deals assigned to your portfolio')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs text-slate-600 font-semibold bg-purple-50 border-purple-200">
                                {effectiveRecentDeals.length} {t('Deals')}
                            </Badge>
                        </div>

                        <div className="flex-1 min-h-0 flex flex-col mt-2">
                            <div className="grid grid-cols-12 text-[11px] font-bold text-slate-400 border-b border-slate-100 pb-2 mb-1 uppercase tracking-wider">
                                <span className="col-span-5">{t('Deal Name')}</span>
                                <span className="col-span-3">{t('Stage')}</span>
                                <span className="col-span-4 text-right">{t('Value & Date')}</span>
                            </div>

                            <div className="flex-1 overflow-y-auto pr-1 divide-y divide-slate-100">
                                {effectiveRecentDeals.map((deal, idx) => (
                                    <div key={deal.id || idx} className="grid grid-cols-12 gap-2 items-center py-3 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div className="col-span-5 flex items-center gap-2.5 min-w-0 pr-1">
                                            <div className="w-7 h-7 rounded-full bg-purple-100 text-purple-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {deal.name ? deal.name.charAt(0).toUpperCase() : 'D'}
                                            </div>
                                            <div className="truncate min-w-0">
                                                <p className="font-bold text-xs text-slate-800 truncate">{deal.name}</p>
                                            </div>
                                        </div>
                                        <div className="col-span-3">
                                            <Badge variant="outline" className="text-[10px] font-semibold text-purple-700 bg-purple-50/70 border-purple-200 truncate max-w-full">
                                                {deal.stage?.name || t('Active')}
                                            </Badge>
                                        </div>
                                        <div className="col-span-4 text-right">
                                            <p className="font-extrabold text-xs text-slate-800">{formatCompactBDT(deal.price || 0)}</p>
                                            <p className="text-[10px] text-slate-400 font-medium mt-0.5">{formatDate(deal.created_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Recent Assigned Leads Card */}
                    <div className="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[380px]">
                        <div className="flex items-center justify-between mb-2">
                            <div>
                                <h3 className="text-base font-bold text-slate-800 flex items-center gap-2">
                                    <TrendingUp className="h-5 w-5 text-indigo-600" />
                                    {t('Recent Assigned Leads')}
                                </h3>
                                <p className="text-xs text-slate-500 font-medium mt-0.5">{t('Latest leads assigned for follow-up')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs text-slate-600 font-semibold bg-indigo-50 border-indigo-200">
                                {effectiveRecentLeads.length} {t('Leads')}
                            </Badge>
                        </div>

                        <div className="flex-1 min-h-0 flex flex-col mt-2">
                            <div className="grid grid-cols-12 text-[11px] font-bold text-slate-400 border-b border-slate-100 pb-2 mb-1 uppercase tracking-wider">
                                <span className="col-span-5">{t('Lead Name')}</span>
                                <span className="col-span-4">{t('Subject / Inquiry')}</span>
                                <span className="col-span-3 text-right">{t('Assigned Date')}</span>
                            </div>

                            <div className="flex-1 overflow-y-auto pr-1 divide-y divide-slate-100">
                                {effectiveRecentLeads.map((lead, idx) => (
                                    <div key={lead.id || idx} className="grid grid-cols-12 gap-2 items-center py-3 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div className="col-span-5 flex items-center gap-2.5 min-w-0 pr-1">
                                            <div className="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {lead.name ? lead.name.charAt(0).toUpperCase() : 'L'}
                                            </div>
                                            <div className="truncate min-w-0">
                                                <p className="font-bold text-xs text-slate-800 truncate">{lead.name}</p>
                                            </div>
                                        </div>
                                        <div className="col-span-4 min-w-0 pr-1">
                                            <p className="text-xs text-slate-600 font-medium truncate">{lead.subject || t('Lead Inquiry')}</p>
                                        </div>
                                        <div className="col-span-3 text-right">
                                            <Badge variant="outline" className="text-[10px] font-semibold text-indigo-700 bg-indigo-50/70 border-indigo-200 mb-0.5">
                                                {t('Assigned')}
                                            </Badge>
                                            <p className="text-[10px] text-slate-400 font-medium">{formatDate(lead.created_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
