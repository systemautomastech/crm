import React, { useState, useEffect, useMemo, useRef, useCallback } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, Legend, ResponsiveContainer } from 'recharts';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar, CheckCircle2, Clock, XCircle, FilterX, ChevronDown, ChevronRight, ChevronLeft, Search, User as UserIcon, BarChart3, List, ArrowLeft } from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatDate, getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface FilterOptions {
    employees: { value: number; label: string }[];
    branches: { value: number; label: string }[];
    departments: { value: number; label: string; branch_id: number }[];
    leave_types: { value: number; label: string }[];
    statuses: { value: string; label: string }[];
}

interface LeaveRecord {
    employee_id: string;
    employee_name: string;
    employee_user_id: number;
    employee_avatar?: string;
    department_name: string;
    leave_type_name: string;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: string;
}

interface Summary {
    total_records: number;
    total_pending: number;
    total_approved: number;
    total_rejected: number;
    total_days: number;
    approved_days: number;
}

const LeaveReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [dateRange, setDateRange] = useState<string>('');
    const [selectedEmployees, setSelectedEmployees] = useState<number[]>([]);
    const [selectedBranch, setSelectedBranch] = useState<string>('all');
    const [selectedDepartments, setSelectedDepartments] = useState<number[]>([]);
    const [selectedStatuses, setSelectedStatuses] = useState<string[]>([]);
    const [selectedLeaveTypes, setSelectedLeaveTypes] = useState<number[]>([]);
    const [reportData, setReportData] = useState<LeaveRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [currentPage, setCurrentPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [expandedEmployees, setExpandedEmployees] = useState<Set<string>>(new Set());
    const [viewMode, setViewMode] = useState<'table' | 'chart' | 'timeline'>('timeline');
    const [timelineMonth, setTimelineMonth] = useState(new Date());
    const leftTimelineRef = useRef<HTMLDivElement>(null);
    const rightTimelineRef = useRef<HTMLDivElement>(null);
    const isTimelineScrolling = useRef(false);

    useEffect(() => {
        fetchFilterOptions();

        const firstDayOfMonth = new Date();
        firstDayOfMonth.setDate(1);

        const lastDayOfMonth = new Date();
        lastDayOfMonth.setMonth(lastDayOfMonth.getMonth() + 1);
        lastDayOfMonth.setDate(0);

        const formatDateHelper = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const autoDateRange = `${formatDateHelper(firstDayOfMonth)} - ${formatDateHelper(lastDayOfMonth)}`;
        setDateRange(autoDateRange);

        setTimeout(() => {
            const [startDate, endDate] = autoDateRange.split(' - ');

            axios.post(route('smart-reports.leave.generate'), {
                start_date: startDate,
                end_date: endDate,
                employee_ids: null,
                department_ids: null,
                status: null,
                leave_type_ids: null,
                export_type: 'view',
            }).then(response => {
                if (response.data.success) {
                    const reportResult = response.data.data;
                    setReportData(reportResult.data || []);
                    setSummary(reportResult.summary || null);
                }
            }).catch(error => {
                console.error('Auto-generate error:', error);
            });
        }, 500);
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const response = await axios.get(route('smart-reports.leave.filter-options'));
            if (response.data.success) {
                setFilterOptions(response.data.data);
            }
        } catch (error: any) {
            console.error('Filter options error:', error);
            toast.error(error.response?.data?.message || t('Failed to load filter options'));
        }
    };

    const handleGenerateReport = async (exportType: 'view') => {
        if (!dateRange || !dateRange.includes(' - ')) {
            toast.error(t('Please select date range'));
            return;
        }

        const [startDate, endDate] = dateRange.split(' - ');
        if (!startDate || !endDate) {
            toast.error(t('Please select valid date range'));
            return;
        }

        try {
            const response = await axios.post(route('smart-reports.leave.generate'), {
                start_date: startDate,
                end_date: endDate,
                employee_ids: selectedEmployees.length > 0 ? selectedEmployees : null,
                department_ids: selectedDepartments.length > 0 ? selectedDepartments : null,
                branch_ids: selectedBranch !== 'all' ? [parseInt(selectedBranch)] : null,
                status: selectedStatuses.length > 0 ? selectedStatuses : null,
                leave_type_ids: selectedLeaveTypes.length > 0 ? selectedLeaveTypes : null,
                export_type: 'view',
            });

            if (response.data.success) {
                const reportResult = response.data.data;
                setReportData(reportResult.data || []);
                setSummary(reportResult.summary || null);
                toast.success(t('Report generated successfully'));
            }
        } catch (error: any) {
            console.error('Export error:', error);
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        }
    };

    const getStatusBadge = (status: string) => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800'
        };
        const statusClass = statusColors[status.toLowerCase() as keyof typeof statusColors] || 'bg-gray-100 text-gray-800';
        const statusText = t(status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown');
        return <span className={`px-2 py-1 rounded-full text-sm font-medium ${statusClass}`}>{statusText}</span>;
    };

    function StackedColumnChart({ data }: { data: LeaveRecord[] }) {
        const chartData = useMemo(() => {
            const groups: Record<string, { name: string; Approved: number; Pending: number; Rejected: number }> = {};
            data.forEach(record => {
                const key = record.employee_name;
                if (!groups[key]) groups[key] = { name: key, Approved: 0, Pending: 0, Rejected: 0 };
                const days = parseFloat(record.total_days as any) || 0;
                if (record.status === 'approved') groups[key].Approved += days;
                if (record.status === 'pending') groups[key].Pending += days;
                if (record.status === 'rejected') groups[key].Rejected += days;
            });
            return Object.values(groups);
        }, [data]);

        // Dynamically calculate chart width: ~150px per employee, min 100% (responsive)
        const chartWidth = Math.max(chartData.length * 150, 600);

        return (
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-xl">
                        <BarChart3 className="h-5 w-5" />
                        {t('Leave Days by Employee')}
                    </CardTitle>
                </CardHeader>
                <CardContent className="pb-2">
                    <div className="overflow-x-auto" style={{ overflowX: 'auto' }}>
                        <div style={{ width: chartWidth, minWidth: '100%' }}>
                            <ResponsiveContainer width="100%" height={380} debounce={1}>
                                <BarChart data={chartData} margin={{ top: 10, right: 20, left: 0, bottom: 10 }} barCategoryGap="20%" barGap={4}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                    <XAxis
                                        dataKey="name"
                                        tick={{ fontSize: 11 }}
                                        interval={0}
                                        height={60}
                                        tickLine={false}
                                    />
                                    <YAxis tick={{ fontSize: 12 }} label={{ value: t('Days'), angle: -90, position: 'insideLeft', offset: 10 }} />
                                    <RechartsTooltip formatter={(value: number, name: string) => [`${value} ${t('days')}`, t(name)]} />
                                    <Bar dataKey="Approved" fill="#22c55e" radius={[4, 4, 0, 0]} maxBarSize={32} />
                                    <Bar dataKey="Pending" fill="#eab308" radius={[4, 4, 0, 0]} maxBarSize={32} />
                                    <Bar dataKey="Rejected" fill="#ef4444" radius={[4, 4, 0, 0]} maxBarSize={32} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>
                    {/* Static Legend outside scroll */}
                    <div className="flex items-center justify-center gap-6 pt-2 pb-1">
                        <div className="flex items-center gap-1.5">
                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#22c55e]"></span>
                            <span className="text-sm font-medium text-muted-foreground">{t('Approved')}</span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#eab308]"></span>
                            <span className="text-sm font-medium text-muted-foreground">{t('Pending')}</span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#ef4444]"></span>
                            <span className="text-sm font-medium text-muted-foreground">{t('Rejected')}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        );
    }

    function LeaveTimeline({ data }: { data: LeaveRecord[] }) {
        const year = timelineMonth.getFullYear();
        const month = timelineMonth.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const days = useMemo(() => Array.from({ length: daysInMonth }, (_, i) => new Date(year, month, i + 1)), [year, month, daysInMonth]);

        const employees = useMemo(() => {
            const map: Record<string, { name: string; emp_id: string; department: string; avatar?: string; leaves: LeaveRecord[] }> = {};
            data.forEach(r => {
                const key = `${r.employee_user_id}_${r.employee_name}`;
                if (!map[key]) map[key] = { name: r.employee_name, emp_id: r.employee_id, department: r.department_name, avatar: r.employee_avatar, leaves: [] };
                map[key].leaves.push(r);
            });
            return Object.values(map);
        }, [data]);

        const syncScroll = useCallback((source: 'left' | 'right') => {
            if (isTimelineScrolling.current) return;
            isTimelineScrolling.current = true;
            requestAnimationFrame(() => {
                if (source === 'left' && rightTimelineRef.current && leftTimelineRef.current)
                    rightTimelineRef.current.scrollTop = leftTimelineRef.current.scrollTop;
                else if (source === 'right' && leftTimelineRef.current && rightTimelineRef.current)
                    leftTimelineRef.current.scrollTop = rightTimelineRef.current.scrollTop;
                isTimelineScrolling.current = false;
            });
        }, []);

        const statusColor: Record<string, string> = {
            approved: '#22c55e',
            pending: '#eab308',
            rejected: '#ef4444',
        };

        const COL_W = 44;
        const ROW_H = 54;
        const BAR_H = 22;
        const minDate = new Date(year, month, 1);

        const getDaysOffset = (dateStr: string) => {
            // Parse as local date (avoid UTC→local timezone shift)
            const parts = dateStr.substring(0, 10).split('-');
            const date = new Date(+parts[0], +parts[1] - 1, +parts[2]);
            return Math.round((date.getTime() - minDate.getTime()) / (1000 * 60 * 60 * 24));
        };

        const getLeaveBar = (leave: LeaveRecord) => {
            const startOffset = getDaysOffset(leave.start_date);
            const endOffset = getDaysOffset(leave.end_date);
            if (startOffset >= daysInMonth || endOffset < 0) return null;
            const clampedStart = Math.max(0, startOffset);
            const clampedEnd = Math.min(daysInMonth - 1, endOffset);
            const spanDays = Math.max(1, clampedEnd - clampedStart + 1);
            return {
                left: clampedStart * COL_W,
                width: spanDays * COL_W,
                color: statusColor[leave.status.toLowerCase()] || '#6b7280',
            };
        };

        // visible leaves per employee (only those in this month)
        const employeesWithBars = employees.map(emp => ({
            ...emp,
            visibleLeaves: emp.leaves.map(l => ({ leave: l, bar: getLeaveBar(l) })).filter(x => x.bar !== null),
        }));

        return (
            <Card className="overflow-hidden border shadow-sm">
                <CardHeader className="border-b bg-gray-50/80 flex flex-row items-center justify-between py-3 px-5">
                    <CardTitle className="text-base font-bold flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-blue-600" />
                        {t('Leave Timeline')}
                    </CardTitle>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" onClick={() => setTimelineMonth(new Date(year, month - 1))}>
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <span className="text-sm font-semibold min-w-[120px] text-center">
                            {timelineMonth.toLocaleString('default', { month: 'long', year: 'numeric' })}
                        </span>
                        <Button variant="outline" size="sm" onClick={() => setTimelineMonth(new Date(year, month + 1))}>
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="flex border-t" style={{ maxHeight: 600, overflow: 'hidden' }}>
                        {/* Left: Employee Names */}
                        <div className="shrink-0 border-r bg-white flex flex-col z-20" style={{ width: 220 }}>
                            <div className="h-12 border-b bg-gray-50/80 flex items-center px-4">
                                <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{t('Employee')}</span>
                            </div>
                            <div ref={leftTimelineRef} onScroll={() => syncScroll('left')} className="flex-1 overflow-y-auto overflow-x-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                {employeesWithBars.map((emp, i) => (
                                    <TooltipProvider key={i}>
                                        <Tooltip delayDuration={200}>
                                            <TooltipTrigger asChild>
                                                <div className="border-b border-gray-100 flex items-center gap-2 px-3 hover:bg-blue-50/40 transition-colors cursor-default" style={{ height: ROW_H }}>
                                                    <div className="h-7 w-7 rounded-full overflow-hidden bg-gray-100 border flex items-center justify-center flex-shrink-0">
                                                        {emp.avatar ? <img src={getImagePath(emp.avatar)} alt={emp.name} className="w-full h-full object-cover" /> : <UserIcon className="w-3.5 h-3.5 text-gray-400" />}
                                                    </div>
                                                    <div className="min-w-0">
                                                        <div className="text-sm font-semibold text-gray-800 truncate leading-tight">{emp.name}</div>
                                                    </div>
                                                </div>
                                            </TooltipTrigger>
                                            <TooltipContent side="right" className="p-3">
                                                <div className="space-y-1 text-xs">
                                                    <p className="font-semibold text-sm">{emp.name}</p>
                                                    <p className="text-gray-500">{t('ID')}: <span className="font-medium text-gray-700">{emp.emp_id}</span></p>
                                                    <p className="text-gray-500">{t('Department')}: <span className="font-medium text-gray-700">{emp.department || '-'}</span></p>
                                                </div>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                ))}
                            </div>
                        </div>

                        {/* Right: Leave Bars */}
                        <div ref={rightTimelineRef} onScroll={() => syncScroll('right')} className="flex-1 bg-white overflow-auto [scrollbar-width:thin]">
                            <div style={{ width: Math.max(daysInMonth * COL_W, 100), minWidth: '100%' }}>
                                {/* Day headers - sticky */}
                                <div className="flex h-12 border-b bg-gray-50/80 sticky top-0 z-30">
                                    {days.map((day, i) => {
                                        const isWeekend = day.getDay() === 0 || day.getDay() === 6;
                                        const isToday = day.toDateString() === new Date().toDateString();
                                        return (
                                            <div key={i} style={{ minWidth: COL_W, width: COL_W }} className={`border-r flex flex-col items-center justify-center ${isWeekend ? 'bg-gray-100/60' : ''} ${isToday ? 'bg-blue-50' : ''}`}>
                                                <span className={`text-xs font-bold leading-none ${isToday ? 'text-blue-600' : 'text-gray-700'}`}>{day.getDate()}</span>
                                                <span className={`text-[9px] uppercase font-semibold mt-0.5 ${isToday ? 'text-blue-500' : 'text-gray-400'}`}>
                                                    {day.toLocaleDateString('en-US', { weekday: 'narrow' })}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>

                                <div className="relative">
                                    {/* Weekend column backgrounds */}
                                    <div className="absolute inset-0 flex z-0 pointer-events-none">
                                        {days.map((day, i) => (
                                            <div key={i} style={{ minWidth: COL_W, width: COL_W }} className={`border-r border-gray-100 ${day.getDay() === 0 || day.getDay() === 6 ? 'bg-gray-50/60' : ''}`} />
                                        ))}
                                    </div>

                                    {/* Today line */}
                                    {(() => {
                                        const today = new Date();
                                        if (today.getFullYear() !== year || today.getMonth() !== month) return null;
                                        return (
                                            <div className="absolute top-0 bottom-0 z-20 pointer-events-none flex flex-col items-center" style={{ left: (today.getDate() - 1) * COL_W }}>
                                                <div className="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-b shadow-sm whitespace-nowrap">{t('Today')}</div>
                                                <div className="w-px flex-1 border-l border-dashed border-red-400" />
                                            </div>
                                        );
                                    })()}

                                    {/* Employee rows - single fixed height, all leaves in same row */}
                                    {employeesWithBars.map((emp, i) => (
                                        <div key={i} className="relative border-b border-gray-100 hover:bg-blue-50/20 transition-colors" style={{ height: ROW_H }}>
                                            {emp.visibleLeaves.map(({ leave, bar }, j) => {
                                                    const topOffset = (ROW_H - BAR_H) / 2;
                                                    return (
                                                        <TooltipProvider key={j}>
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <div
                                                                        className="absolute flex items-center px-3 overflow-hidden z-10 cursor-pointer shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200"
                                                                        style={{
                                                                            left: bar!.left + 2,
                                                                            width: Math.max(bar!.width - 4, 8),
                                                                            top: topOffset,
                                                                            height: BAR_H,
                                                                            backgroundColor: `${bar!.color}18`,
                                                                            borderLeft: `3px solid ${bar!.color}`,
                                                                            borderRadius: '999px',
                                                                        }}
                                                                    >
                                                                        <span className="text-[11px] font-semibold truncate" style={{ color: bar!.color }}>
                                                                            {leave.leave_type_name}
                                                                        </span>
                                                                    </div>
                                                                </TooltipTrigger>
                                                                <TooltipContent side="top" className="max-w-xs p-3">
                                                                    <div className="space-y-1.5 text-xs">
                                                                        <p className="font-semibold text-sm">{leave.leave_type_name}</p>
                                                                        <p className="text-gray-500">{formatDate(leave.start_date)} – {formatDate(leave.end_date)}</p>
                                                                        <p>{t('Days')}: <span className="font-semibold">{leave.total_days}</span></p>
                                                                        <div>{getStatusBadge(leave.status)}</div>
                                                                        {leave.reason && <p className="text-gray-400 line-clamp-2">{leave.reason}</p>}
                                                                    </div>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        </TooltipProvider>
                                                    );
                                                })}
                                            </div>
                                        ))}
                                </div>
                            </div>
                        </div>
                    </div>
                    {/* Legend */}
                    <div className="flex items-center justify-center gap-6 py-2 border-t bg-gray-50/30">
                        {[['#22c55e', t('Approved')], ['#eab308', t('Pending')], ['#ef4444', t('Rejected')]].map(([color, label]) => (
                            <div key={label} className="flex items-center gap-1.5">
                                <span className="inline-block h-2.5 w-2.5 rounded-sm" style={{ backgroundColor: color }}></span>
                                <span className="text-sm font-medium text-muted-foreground">{label}</span>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        );
    }

    function DataTable({ data }: { data: LeaveRecord[] }) {
        // Group data by employee
        const groupedData = useMemo(() => {
            const groups: Record<string, {
                employee_name: string;
                employee_id: string;
                department_name: string;
                employee_avatar?: string;
                records: LeaveRecord[];
                stats: {
                    total: number;
                    pending: number;
                    approved: number;
                    rejected: number;
                    total_days: number;
                };
            }> = {};

            data.forEach(record => {
                const key = `${record.employee_user_id}_${record.employee_name}`;
                if (!groups[key]) {
                    groups[key] = {
                        employee_name: record.employee_name,
                        employee_id: record.employee_id,
                        department_name: record.department_name,
                        employee_avatar: record.employee_avatar,
                        records: [],
                        stats: {
                            total: 0,
                            pending: 0,
                            approved: 0,
                            rejected: 0,
                            total_days: 0
                        }
                    };
                }
                groups[key].records.push(record);
                groups[key].stats.total++;
                if (record.status === 'pending') groups[key].stats.pending++;
                if (record.status === 'approved') groups[key].stats.approved++;
                if (record.status === 'rejected') groups[key].stats.rejected++;
                groups[key].stats.total_days += parseFloat(record.total_days as any) || 0;
            });

            return Object.entries(groups).map(([key, value]) => ({ key, ...value }));
        }, [data]);

        // Filter by search query
        const filteredData = useMemo(() => {
            if (!searchQuery.trim()) return groupedData;
            const query = searchQuery.toLowerCase();
            return groupedData.filter(group =>
                group.employee_name.toLowerCase().includes(query) ||
                group.employee_id.toLowerCase().includes(query) ||
                group.department_name?.toLowerCase().includes(query)
            );
        }, [groupedData, searchQuery]);

        // Pagination
        const totalPages = Math.ceil(filteredData.length / perPage);
        const paginatedData = useMemo(() => {
            const startIndex = (currentPage - 1) * perPage;
            return filteredData.slice(startIndex, startIndex + perPage);
        }, [filteredData, currentPage, perPage]);

        const toggleEmployee = (key: string) => {
            setExpandedEmployees(prev => {
                const newSet = new Set(prev);
                if (newSet.has(key)) {
                    newSet.delete(key);
                } else {
                    newSet.add(key);
                }
                return newSet;
            });
        };

        const handlePageChange = (page: number) => {
            setCurrentPage(page);
        };

        return (
            <Card>
                {/* Search and Pagination Controls */}
                <CardContent className="p-4 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder={t('Search by employee name, ID, or department...')}
                                    value={searchQuery}
                                    onChange={(e) => {
                                        setSearchQuery(e.target.value);
                                        setCurrentPage(1);
                                    }}
                                    className="pl-9"
                                />
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <PerPageSelector
                                routeName="smart-reports.leave.index"
                                defaultValue={perPage.toString()}
                                onPageChange={(val) => { setPerPage(Number(val)); setCurrentPage(1); }}
                            />
                        </div>
                    </div>
                </CardContent>

                {/* Table */}
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 sticky top-0">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium w-12"></th>
                                <th className="px-4 py-3 text-left font-medium">{t('Employee')}</th>
                                <th className="px-4 py-3 text-left font-medium">{t('Department')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Total Applications')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Pending Applications')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Approved Applications')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Rejected Applications')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Total Leave Days')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {paginatedData.length === 0 ? (
                                <tr>
                                    <td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">
                                        <Calendar className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>{t('No records found')}</p>
                                    </td>
                                </tr>
                            ) : (
                                paginatedData.map((group) => (
                                    <React.Fragment key={group.key}>
                                        {/* Employee Summary Row */}
                                        <tr className="hover:bg-muted/50 transition-colors cursor-pointer" onClick={() => toggleEmployee(group.key)}>
                                            <td className="px-4 py-3">
                                                {expandedEmployees.has(group.key) ? (
                                                    <ChevronDown className="h-4 w-4" />
                                                ) : (
                                                    <ChevronRight className="h-4 w-4" />
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex items-center gap-2">
                                                    <div className="h-8 w-8 rounded-full overflow-hidden bg-gray-100 border flex items-center justify-center flex-shrink-0">
                                                        {group.employee_avatar ? (
                                                            <img
                                                                src={getImagePath(group.employee_avatar)}
                                                                alt={group.employee_name}
                                                                className="w-full h-full object-cover"
                                                            />
                                                        ) : (
                                                            <UserIcon className="w-4 h-4 text-gray-400" />
                                                        )}
                                                    </div>
                                                    <div>
                                                        <div className="font-medium">{group.employee_name}</div>
                                                        <div className="text-xs text-muted-foreground">{group.employee_id}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">{group.department_name || '-'}</td>
                                            <td className="px-4 py-3 text-center font-semibold">{group.stats.total}</td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {group.stats.pending}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                                    {group.stats.approved}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">
                                                    {group.stats.rejected}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center font-semibold">{group.stats.total_days.toFixed(1)}</td>
                                        </tr>

                                        {/* Expanded Details */}
                                        {expandedEmployees.has(group.key) && (
                                            <tr>
                                                <td colSpan={8} className="bg-gray-50/50 p-0">
                                                    <div className="px-8 py-4">
                                                        <table className="w-full text-sm">
                                                            <thead className="bg-white">
                                                                <tr className="border-b">
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Leave Type')}</th>
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Start Date')}</th>
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('End Date')}</th>
                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Days')}</th>
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Reason')}</th>
                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Status')}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="divide-y">
                                                                {group.records.map((record, idx) => (
                                                                    <tr key={idx} className="hover:bg-white transition-colors">
                                                                        <td className="px-3 py-2">{record.leave_type_name}</td>
                                                                        <td className="px-3 py-2 whitespace-nowrap">{formatDate(record.start_date)}</td>
                                                                        <td className="px-3 py-2 whitespace-nowrap">{formatDate(record.end_date)}</td>
                                                                        <td className="px-3 py-2 text-center font-semibold">{record.total_days}</td>
                                                                        <td className="px-3 py-2 max-w-xs truncate" title={record.reason}>{record.reason || '-'}</td>
                                                                        <td className="px-3 py-2 text-center">{getStatusBadge(record.status)}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </React.Fragment>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={{ current_page: currentPage, last_page: totalPages, per_page: perPage, total: filteredData.length, from: ((currentPage - 1) * perPage) + 1, to: Math.min(currentPage * perPage, filteredData.length) }}
                        onPageChange={handlePageChange}
                    />
                </CardContent>
            </Card>
        );
    }



    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Smart Reports'), url: route('smart-reports.index') },
                { label: t('Leave Report') }
            ]}
            pageTitle={t('Leave Report')}
            pageActions={
                <div className="flex items-center gap-2">
                    <ExportButton
                        data={reportData || []}
                        columns={[
                            { key: 'employee_name', header: t('Employee') },
                            { key: 'leave_type_name', header: t('Leave Type') },
                            { key: 'start_date', header: t('Start Date'), type: 'date' },
                            { key: 'status', header: t('Status'), type: 'status' },
                        ]}
                        filename={`leave-report-${dateRange.replace(' - ', '-to-')}`}
                        title={t('Leave Report')}
                        groupedConfig={{
                            sheetName: 'Leave Report',
                            groupKey: (r) => `${r.employee_user_id}_${r.employee_name}`,
                            summaryColumns: [
                                { key: 'employee_name',   header: t('Employee') },
                                { key: 'employee_id',     header: t('Employee ID') },
                                { key: 'department_name', header: t('Department') },
                                { key: 'total_apps',      header: t('Total Applications'), type: 'number' },
                                { key: 'pending',         header: t('Pending'),   type: 'number' },
                                { key: 'approved',        header: t('Approved'),  type: 'number' },
                                { key: 'rejected',        header: t('Rejected'),  type: 'number' },
                                { key: 'total_days',      header: t('Total Days'), type: 'number' },
                            ],
                            detailColumns: [
                                { key: 'leave_type_name', header: t('Leave Type') },
                                { key: 'start_date',      header: t('Start Date'), type: 'date' },
                                { key: 'end_date',        header: t('End Date'),   type: 'date' },
                                { key: 'total_days',      header: t('Days'),       type: 'number' },
                                { key: 'status',          header: t('Status'),     type: 'status' },
                                { key: 'reason',          header: t('Reason') },
                            ],
                            summaryBuilder: (rows) => ({
                                employee_name:   rows[0].employee_name,
                                employee_id:     rows[0].employee_id,
                                department_name: rows[0].department_name || '',
                                total_apps:      rows.length,
                                pending:         rows.filter(r => r.status === 'pending').length,
                                approved:        rows.filter(r => r.status === 'approved').length,
                                rejected:        rows.filter(r => r.status === 'rejected').length,
                                total_days:      rows.reduce((s, r) => s + (parseFloat(r.total_days) || 0), 0).toFixed(1),
                            }),
                        }}
                    />
                    <Button variant="outline" size="sm" onClick={() => router.visit(route('smart-reports.index'))}>
                        <ArrowLeft className="h-4 w-4" />
                        {t('Back')}
                    </Button>
                </div>
            }
        >
            <Head title={t('Leave Report')} />

            <div className="space-y-6">
                {(
                    <Card>
                        <CardContent className="px-4 py-3">
                            {/* xl+: all in one flex row | lg: 2-col grid | <lg: 1-col grid */}
                            <div className="hidden xl:flex items-center gap-2">
                                <div className="flex-1 min-w-0">
                                    <DateRangePicker value={dateRange} onChange={(value) => setDateRange(value)} placeholder={t('Select date range')} />
                                </div>
                                {filterOptions && (
                                    <>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'} onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Employee')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((emp) => <SelectItem key={emp.value} value={emp.value.toString()}>{emp.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedBranch} onValueChange={v => { setSelectedBranch(v); setSelectedDepartments([]); }}>
                                                <SelectTrigger><SelectValue placeholder={t('Branch')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Branches')}</SelectItem>
                                                    {filterOptions.branches?.map((b) => <SelectItem key={b.value} value={b.value.toString()}>{b.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'} onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])} disabled={selectedBranch === 'all'}>
                                                <SelectTrigger><SelectValue placeholder={selectedBranch === 'all' ? t('Select Branch first') : t('Department')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                    {filterOptions.departments.filter(d => d.branch_id === parseInt(selectedBranch)).map((dept) => <SelectItem key={dept.value} value={dept.value.toString()}>{dept.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedLeaveTypes.length > 0 ? selectedLeaveTypes[0].toString() : 'all'} onValueChange={(value) => setSelectedLeaveTypes(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Leave Type')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Leave Types')}</SelectItem>
                                                    {filterOptions.leave_types.map((type) => <SelectItem key={type.value} value={type.value.toString()}>{type.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedStatuses.length > 0 ? selectedStatuses[0] : 'all'} onValueChange={(value) => setSelectedStatuses(value === 'all' ? [] : [value])}>
                                                <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                                    {filterOptions.statuses.map((status) => <SelectItem key={status.value} value={status.value}>{status.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </>
                                )}
                                <div className="flex items-center gap-2 shrink-0">
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                        const now = new Date();
                                        const start = new Date(now.getFullYear(), now.getMonth(), 1);
                                        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                                        const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                                        setDateRange(`${fmt(start)} - ${fmt(end)}`);
                                        setSelectedEmployees([]); setSelectedBranch('all'); setSelectedDepartments([]); setSelectedStatuses([]); setSelectedLeaveTypes([]);
                                        axios.post(route('smart-reports.leave.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, status: null, leave_type_ids: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                    }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                                            </div>
                            </div>
                            {/* lg (1024px): 2-col grid, buttons on last row right | <lg: 1-col */}
                            <div className="xl:hidden grid grid-cols-1 lg:grid-cols-2 gap-2">
                                <div>
                                    <DateRangePicker value={dateRange} onChange={(value) => setDateRange(value)} placeholder={t('Select date range')} />
                                </div>
                                {filterOptions && (
                                    <>
                                        <div>
                                            <Select value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'} onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Employee')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((emp) => <SelectItem key={emp.value} value={emp.value.toString()}>{emp.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select value={selectedBranch} onValueChange={v => { setSelectedBranch(v); setSelectedDepartments([]); }}>
                                                <SelectTrigger><SelectValue placeholder={t('Branch')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Branches')}</SelectItem>
                                                    {filterOptions.branches?.map((b) => <SelectItem key={b.value} value={b.value.toString()}>{b.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'} onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])} disabled={selectedBranch === 'all'}>
                                                <SelectTrigger><SelectValue placeholder={selectedBranch === 'all' ? t('Select Branch first') : t('Department')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                    {filterOptions.departments.filter(d => d.branch_id === parseInt(selectedBranch)).map((dept) => <SelectItem key={dept.value} value={dept.value.toString()}>{dept.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select value={selectedLeaveTypes.length > 0 ? selectedLeaveTypes[0].toString() : 'all'} onValueChange={(value) => setSelectedLeaveTypes(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Leave Type')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Leave Types')}</SelectItem>
                                                    {filterOptions.leave_types.map((type) => <SelectItem key={type.value} value={type.value.toString()}>{type.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        {/* Status + buttons share last lg row */}
                                        <div className="flex items-center gap-2 lg:col-span-1">
                                            <div className="flex-1">
                                                <Select value={selectedStatuses.length > 0 ? selectedStatuses[0] : 'all'} onValueChange={(value) => setSelectedStatuses(value === 'all' ? [] : [value])}>
                                                    <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                                        {filterOptions.statuses.map((status) => <SelectItem key={status.value} value={status.value}>{status.label}</SelectItem>)}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="hidden lg:flex items-center gap-2 shrink-0">
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                                    const now = new Date();
                                                    const start = new Date(now.getFullYear(), now.getMonth(), 1);
                                                    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                                                    const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                                                    setDateRange(`${fmt(start)} - ${fmt(end)}`);
                                                    setSelectedEmployees([]); setSelectedBranch('all'); setSelectedDepartments([]); setSelectedStatuses([]); setSelectedLeaveTypes([]);
                                                    axios.post(route('smart-reports.leave.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, status: null, leave_type_ids: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                                }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                            {/* Buttons for <lg only */}
                            <div className="flex lg:hidden items-center justify-end gap-2 mt-2">
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                    const now = new Date();
                                    const start = new Date(now.getFullYear(), now.getMonth(), 1);
                                    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                                    const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                                    setDateRange(`${fmt(start)} - ${fmt(end)}`);
                                    setSelectedEmployees([]); setSelectedBranch('all'); setSelectedDepartments([]); setSelectedStatuses([]); setSelectedLeaveTypes([]);
                                    axios.post(route('smart-reports.leave.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, status: null, leave_type_ids: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {summary && reportData && reportData.length > 0 && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <span className="text-sm font-medium text-blue-700 truncate">{t('Total Applications')}</span>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_records}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-yellow-50/50 border-yellow-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Clock className="h-4 w-4 text-yellow-600 shrink-0" />
                                        <span className="text-sm font-medium text-yellow-700 truncate">{t('Pending')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-yellow-900 shrink-0">{summary.total_pending}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-green-50/50 border-green-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <CheckCircle2 className="h-4 w-4 text-green-600 shrink-0" />
                                        <span className="text-sm font-medium text-green-700 truncate">{t('Approved')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-green-900 shrink-0">{summary.total_approved}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-red-50/50 border-red-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <XCircle className="h-4 w-4 text-red-600 shrink-0" />
                                        <span className="text-sm font-medium text-red-700 truncate">{t('Rejected')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-red-900 shrink-0">{summary.total_rejected}</div>
                                </div>
                            </CardContent>
                        </Card>
                        {reportData && reportData.length > 0 && (
                            <div className="flex items-center justify-end w-full xl:w-auto shrink-0">
                                <div className="flex border rounded-lg">
                                    <Button variant={viewMode === 'timeline' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('timeline')} className="rounded-r-none">
                                        <Calendar className="h-4 w-4" />
                                    </Button>
                                    <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-none border-x">
                                        <BarChart3 className="h-4 w-4" />
                                    </Button>
                                    <Button variant={viewMode === 'table' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('table')} className="rounded-l-none">
                                        <List className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {reportData && reportData.length > 0 && (
                    <div>
                        {viewMode === 'table' ? <DataTable data={reportData} /> : viewMode === 'timeline' ? <LeaveTimeline data={reportData} /> : <StackedColumnChart data={reportData} />}
                    </div>
                )}

                {reportData !== null && reportData.length === 0 && (
                    <Card>
                        <CardContent className="text-center py-12">
                            <Calendar className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p className="text-lg font-medium text-muted-foreground">
                                {t('No leave records found.')}
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
};

export default LeaveReport;
