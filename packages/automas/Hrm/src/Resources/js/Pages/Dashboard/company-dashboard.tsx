import React, { useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import CustomEventCalendar, { CalendarEventItem } from "@/components/custom-event-calendar";
import {
    Users,
    UserCheck,
    UserX,
    Clock,
    Calendar,
    TrendingUp,
    TrendingDown,
    AlertTriangle,
    Building,
    Briefcase,
    CalendarDays,
    CreditCard,
} from 'lucide-react';
import { getImagePath } from '@/utils/helpers';

interface HrmProps {
    message?: string;
    stats?: {
        total_employees?: number;
        present_today?: number;
        absent_today?: number;
        absent_yesterday?: number;
        on_leave?: number;
        pending_leaves?: number;
        total_branches?: number;
        total_departments?: number;
        total_promotions?: number;
        terminations?: number;
        department_distribution?: Array<{
            name: string;
            value: number;
        }>;
        calendar_events?: Array<{
            id: number | string;
            title: string;
            startDate: string;
            endDate?: string;
            time?: string;
            description?: string;
            type?: string;
            color?: string;
        }>;
        recent_leave_applications?: Array<{
            id: number;
            employee_name: string;
            leave_type: string;
            start_date: string;
            end_date: string;
            total_days: number;
            status: string;
            created_at: string;
        }>;
        employees_on_leave_today?: Array<{
            name: string;
            leave_type: string;
            days: number;
            profile?: string;
        }>;
        employees_without_attendance?: Array<{
            name: string;
            department?: string;
            employee_id?: string;
            profile?: string;
        }>;
    };
}

export default function HrmIndex({ stats }: HrmProps) {
    const { t } = useTranslation();

    const totalEmployees = stats?.total_employees || 0;
    const presentToday = stats?.present_today || 0;
    const absentToday = stats?.absent_today || 0;
    const onLeave = stats?.on_leave || 0;
    const totalBranches = stats?.total_branches || 0;
    const totalDepartments = stats?.total_departments || 0;

    const attendanceRate = totalEmployees > 0 ? ((presentToday / totalEmployees) * 100).toFixed(1) : '0';

    const hrmCalendarEvents: CalendarEventItem[] = useMemo(() => {
        if (!stats?.calendar_events) return [];
        return stats.calendar_events.map(evt => ({
            id: evt.id,
            title: evt.title,
            startDate: evt.startDate,
            time: evt.time || '09:00 AM',
            type: evt.type || 'HR Event',
            subtitle: evt.description || evt.title,
        }));
    }, [stats?.calendar_events]);

    const deptDistribution = stats?.department_distribution || [];
    const maxVal = deptDistribution.length > 0 ? Math.max(...deptDistribution.map(d => d.value)) : 1;

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('HRM') }]}
        >
            <Head title={t('HRM Dashboard')} />

            <div className="space-y-6 pb-8 rounded-2xl">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('HRM Overview')}</h1>
                        <p className="text-xs font-semibold text-slate-500 mt-0.5">
                            {t('Workforce metrics, attendance, department distribution, and HR operations.')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>

                {/* 1. Top KPI Row (6 Soft Colorful Cards) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {/* Card 1: Total Employees */}
                    <div onClick={() => router.visit(route('hrm.employees.index'))} className="bg-gradient-to-br from-blue-50/80 to-indigo-50/60 border border-blue-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                            <Users className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Employees')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{totalEmployees}</span>
                            <span className="text-[10px] font-semibold text-blue-700 block">{t('Active workforce')}</span>
                        </div>
                    </div>

                    {/* Card 2: Present Today */}
                    <div onClick={() => router.visit(route('hrm.attendances.index'))} className="bg-gradient-to-br from-emerald-50/80 to-teal-50/60 border border-emerald-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <UserCheck className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Present Today')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{presentToday}</span>
                            <span className="text-[10px] font-semibold text-emerald-700 block">{attendanceRate}% {t('rate')}</span>
                        </div>
                    </div>

                    {/* Card 3: Absent Today */}
                    <div onClick={() => router.visit(route('hrm.attendances.index'))} className="bg-gradient-to-br from-rose-50/80 to-pink-50/60 border border-rose-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <UserX className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Absent Today')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{absentToday}</span>
                            <span className="text-[10px] font-semibold text-rose-700 block">{t('Unexcused')}</span>
                        </div>
                    </div>

                    {/* Card 4: On Leave */}
                    <div onClick={() => router.visit(route('hrm.leave-applications.index'))} className="bg-gradient-to-br from-purple-50/80 to-fuchsia-50/60 border border-purple-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                            <Calendar className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('On Leave')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{onLeave}</span>
                            <span className="text-[10px] font-semibold text-purple-700 block">{stats?.pending_leaves ?? 0} {t('pending')}</span>
                        </div>
                    </div>

                    {/* Card 5: Total Branches */}
                    <div onClick={() => router.visit(route('hrm.branches.index'))} className="bg-gradient-to-br from-teal-50/80 to-cyan-50/60 border border-teal-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                            <Building className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Branches')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{totalBranches}</span>
                            <span className="text-[10px] font-semibold text-teal-700 block">{t('Active locations')}</span>
                        </div>
                    </div>

                    {/* Card 6: Total Departments */}
                    <div onClick={() => router.visit(route('hrm.departments.index'))} className="bg-gradient-to-br from-indigo-50/80 to-sky-50/60 border border-indigo-100/90 rounded-2xl p-4 shadow-sm flex items-center gap-3 cursor-pointer hover:shadow-md transition-all">
                        <div className="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <Briefcase className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[11px] font-bold text-slate-500 block leading-tight">{t('Total Departments')}</span>
                            <span className="text-xl font-bold text-slate-900 mt-0.5 block">{totalDepartments}</span>
                            <span className="text-[10px] font-semibold text-indigo-700 block">{t('Functional units')}</span>
                        </div>
                    </div>
                </div>

                {/* 2. Department Distribution & Quick Actions Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Department Distribution */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Department Distribution')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Headcount per department')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-blue-50 text-blue-700 border-blue-200">
                                {deptDistribution.length} {t('Departments')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-4 pr-1">
                            {deptDistribution.length > 0 ? (
                                deptDistribution.map((dept, index) => {
                                    const pct = maxVal > 0 ? (dept.value / maxVal) * 100 : 0;
                                    return (
                                        <div key={index} className="space-y-1">
                                            <div className="flex justify-between items-center text-xs font-bold text-slate-700">
                                                <span>{dept.name}</span>
                                                <span className="text-slate-900 font-extrabold">{dept.value} {t('employees')}</span>
                                            </div>
                                            <div className="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                                                <div className="bg-blue-500 h-full rounded-full transition-all duration-500" style={{ width: `${pct}%` }} />
                                            </div>
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <p>{t('No department distribution data available')}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Quick HR Actions Grid */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Quick HR Actions')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Shortcuts for daily management')}</p>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-3 overflow-y-auto pr-1 flex-1">
                            {[
                                { label: t('Add Employee'), icon: Users, route: 'hrm.employees.create', color: 'bg-blue-500/10 text-blue-600' },
                                { label: t('Mark Attendance'), icon: Clock, route: 'hrm.attendances.index', color: 'bg-emerald-500/10 text-emerald-600' },
                                { label: t('Apply Leave'), icon: Calendar, route: 'hrm.leave-applications.index', color: 'bg-purple-500/10 text-purple-600' },
                                { label: t('Process Payroll'), icon: CreditCard, route: 'hrm.payrolls.index', color: 'bg-amber-500/10 text-amber-600' },
                                { label: t('Promotions'), icon: TrendingUp, route: 'hrm.promotions.index', color: 'bg-teal-500/10 text-teal-600' },
                                { label: t('Terminations'), icon: TrendingDown, route: 'hrm.terminations.index', color: 'bg-rose-500/10 text-rose-600' },
                            ].map((act, idx) => {
                                const Icon = act.icon;
                                return (
                                    <div
                                        key={idx}
                                        onClick={() => router.visit(route(act.route))}
                                        className="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-slate-300 hover:bg-slate-50/80 bg-white cursor-pointer transition-all shadow-2xs group"
                                    >
                                        <div className={`w-9 h-9 rounded-lg ${act.color} flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform`}>
                                            <Icon className="h-4 w-4" />
                                        </div>
                                        <span className="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">{act.label}</span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* 3. Employees Status Cards Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Employees on Leave Today */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Employees on Leave Today')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Approved leave schedules')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-purple-50 text-purple-700 border-purple-200">
                                {stats?.employees_on_leave_today?.length || 0} {t('On Leave')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {stats?.employees_on_leave_today && stats.employees_on_leave_today.length > 0 ? (
                                stats.employees_on_leave_today.map((emp, idx) => (
                                    <div key={idx} className="flex items-center justify-between py-2.5 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {emp.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <p className="text-xs font-bold text-slate-800">{emp.name}</p>
                                                <p className="text-[11px] font-medium text-slate-500 mt-0.5">{emp.leave_type}</p>
                                            </div>
                                        </div>
                                        <Badge className="bg-purple-500/15 text-purple-600 border-none font-bold text-[10px]">
                                            {emp.days} {t('days')}
                                        </Badge>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <p>{t('No employees on leave today')}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Missing Attendance Today */}
                    <div className="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col h-[340px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-slate-900">{t('Missing Attendance Today')}</h3>
                                <p className="text-xs font-semibold text-slate-400">{t('Employees with unrecorded check-in')}</p>
                            </div>
                            <Badge variant="outline" className="text-xs font-semibold bg-rose-50 text-rose-700 border-rose-200">
                                {stats?.employees_without_attendance?.length || 0} {t('Unrecorded')}
                            </Badge>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1 divide-y divide-slate-100">
                            {stats?.employees_without_attendance && stats.employees_without_attendance.length > 0 ? (
                                stats.employees_without_attendance.map((emp, idx) => (
                                    <div key={idx} className="flex items-center justify-between py-2.5 hover:bg-slate-50/80 rounded-lg px-1 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-full bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {emp.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <p className="text-xs font-bold text-slate-800">{emp.name}</p>
                                                <p className="text-[11px] font-medium text-slate-500 mt-0.5">{emp.department || t('Department')}</p>
                                            </div>
                                        </div>
                                        <Badge className="bg-rose-500/15 text-rose-600 border-none font-bold text-[10px]">
                                            {t('Absent')}
                                        </Badge>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                                    <p>{t('All active employees recorded attendance')}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* 4. Events & Holidays Calendar */}
                <CustomEventCalendar
                    title="Events & Holidays Calendar"
                    description="Company holidays, employee birthdays, and scheduled townhalls"
                    headerIcon={<CalendarDays className="h-5 w-5 text-blue-600" />}
                    events={hrmCalendarEvents}
                    selectedDateTitlePrefix={t('Events on')}
                    eventsSummaryText={t('scheduled events or holidays')}
                    emptyText={t('No HR events scheduled on this date.')}
                />
            </div>
        </AuthenticatedLayout>
    );
}