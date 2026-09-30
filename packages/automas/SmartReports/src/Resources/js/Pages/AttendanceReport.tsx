import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Card, CardContent } from '@/components/ui/card';
import { Users, UserCheck, UserX, Calendar, List, LayoutGrid, User as UserIcon, ChevronDown, ChevronRight, Search, Check, X, Minus, Plane, FilterX, Umbrella, ArrowLeft } from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import axios from 'axios';
import { formatDate, formatTime, getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';

interface FilterOptions {
    branches: { value: number; label: string }[];
    employees: { value: number; label: string }[];
    departments: { value: number; label: string; branch_id: number }[];
    statuses: { value: string; label: string }[];
}

interface AttendanceRecord {
    date: string;
    employee_name: string;
    emp_code: string;
    employee_user_id: number;
    department_name: string;
    designation_name: string;
    clock_in: string;
    clock_out: string;
    total_hour: number;
    break_hour: number;
    overtime_hours: number;
    overtime_amount: number;
    status: string;
    punctuality: string;
    employee_avatar?: string;
}

interface Summary {
    total_records: number;
    total_employees: number;
    total_present: number;
    total_half_day: number;
    total_absent: number;
    total_on_leave: number;
    total_working_hours: number;
    total_overtime_hours: number;
    total_overtime_amount: number;
    attendance_rate: number;
    late_arrivals: number;
    early_departures: number;
}

interface HolidayRecord {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
}

const AttendanceReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [dateRange, setDateRange] = useState<string>('');
    const [selectedEmployees, setSelectedEmployees] = useState<number[]>([]);
    const [selectedBranch, setSelectedBranch] = useState<string>('all');
    const [selectedDepartments, setSelectedDepartments] = useState<number[]>([]);
    const [selectedStatuses, setSelectedStatuses] = useState<string[]>([]);
    const [reportData, setReportData] = useState<AttendanceRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [holidays, setHolidays] = useState<HolidayRecord[]>([]);
    const [viewMode, setViewMode] = useState<'table' | 'calendar'>('calendar');
    const [perPage, setPerPage] = useState<number>(10);
    const [expandedEmployees, setExpandedEmployees] = useState<Set<string>>(new Set());

    useEffect(() => {
        fetchFilterOptions();

        // Auto-set current month date range
        const currentMonthStart = new Date();
        currentMonthStart.setDate(1);

        const currentMonthEnd = new Date();
        // If today is 1st, set end to 2nd to avoid single-day range
        if (currentMonthEnd.getDate() === 1) {
            currentMonthEnd.setDate(2);
        }

        const formatDate = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const autoDateRange = `${formatDate(currentMonthStart)} - ${formatDate(currentMonthEnd)}`;
        setDateRange(autoDateRange);

        // Auto-generate report after setting date range
        setTimeout(() => {
            const [startDate, endDate] = autoDateRange.split(' - ');

            axios.post(route('smart-reports.attendance.generate'), {
                start_date: startDate,
                end_date: endDate,
                employee_ids: null,
                department_ids: null,
                status: null,
                export_type: 'view',
            }).then(response => {
                if (response.data.success) {
                    const reportResult = response.data.data;
                    setReportData(reportResult.data || []);
                    setSummary(reportResult.summary || null);
                    setHolidays(reportResult.holidays || []);
                }
            }).catch(error => {
                console.error('Auto-generate error:', error);
            });
        }, 500);
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const response = await axios.get(route('smart-reports.attendance.filter-options'));
            if (response.data.success) {
                setFilterOptions(response.data.data);
            }
        } catch (error: any) {
            console.error('Filter options error:', error);
            const errorMsg = error.response?.data?.message || error.message || t('Failed to load filter options');
            toast.error(errorMsg);
        }
    };

    const handleGenerateReport = async (exportType: 'pdf' | 'excel' | 'view') => {
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
            if (exportType === 'view') {
                const response = await axios.post(route('smart-reports.attendance.generate'), {
                    start_date: startDate,
                    end_date: endDate,
                    employee_ids: selectedEmployees.length > 0 ? selectedEmployees : null,
                    branch_ids: selectedBranch !== 'all' ? [parseInt(selectedBranch)] : null,
                    department_ids: selectedDepartments.length > 0 ? selectedDepartments : null,
                    status: selectedStatuses.length > 0 ? selectedStatuses : null,
                    export_type: 'view',
                });

                if (response.data.success) {
                    const reportResult = response.data.data;
                    setReportData(reportResult.data || []);
                    setSummary(reportResult.summary || null);
                    setHolidays(reportResult.holidays || []);
                    if (response.data.message) {
                        toast.info(response.data.message);
                    } else {
                        toast.success(t('Report generated successfully'));
                    }
                }
            }
        } catch (error: any) {
            console.error('Export error:', error);
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        }
    };

    const getStatusBadge = (status: string) => {
        const styles: Record<string, string> = {
            'present': 'bg-green-100 text-green-800',
            'half day': 'bg-yellow-100 text-yellow-800',
            'absent': 'bg-red-100 text-red-800',
            'on leave': 'bg-blue-100 text-blue-800',
        };
        const labels: Record<string, string> = {
            'present': t('Present'),
            'half day': t('Half Day'),
            'absent': t('Absent'),
            'on leave': t('On Leave'),
        };
        return (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[status] || 'bg-gray-100 text-gray-800'}`}>
                {labels[status] || status}
            </span>
        );
    };

    const getPunctualityBadge = (punctuality: string) => {
        const styles: Record<string, string> = {
            'On Time': 'bg-green-100 text-green-800',
            'Late': 'bg-orange-100 text-orange-800',
            'Early': 'bg-blue-100 text-blue-800',
            'Absent': 'bg-red-100 text-red-800',
            'On Leave': 'bg-blue-100 text-blue-800',
        };
        return (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[punctuality] || 'bg-gray-100 text-gray-800'}`}>
                {punctuality}
            </span>
        );
    };

    function DataTable({ data }: { data: AttendanceRecord[] }) {
        const [searchQuery, setSearchQuery] = useState<string>('');
        const [currentPage, setCurrentPage] = useState<number>(1);

        // Group data by employee
        const groupedData = useMemo(() => {
            const groups: Record<string, {
                employee_name: string;
                employee_id: string;
                department_name: string;
                employee_avatar?: string;
                records: AttendanceRecord[];
                stats: {
                    total: number;
                    present: number;
                    absent: number;
                    leave: number;
                    total_hours: number;
                };
            }> = {};

            data.forEach(record => {
                const key = `${record.employee_user_id}`;
                if (!groups[key]) {
                    groups[key] = {
                        employee_name: record.employee_name,
                        employee_id: record.emp_code,
                        department_name: record.department_name,
                        employee_avatar: record.employee_avatar,
                        records: [],
                        stats: {
                            total: 0,
                            present: 0,
                            absent: 0,
                            leave: 0,
                            total_hours: 0
                        }
                    };
                }
                groups[key].records.push(record);
                groups[key].stats.total++;
                if (record.status === 'present') groups[key].stats.present++;
                if (record.status === 'absent') groups[key].stats.absent++;
                if (record.status === 'on leave') groups[key].stats.leave++;
                const hours = parseFloat(record.total_hour as any) || 0;
                groups[key].stats.total_hours += hours;
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
                                routeName="smart-reports.attendance.index"
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
                                <th className="px-4 py-3 text-center font-medium">{t('Total Days')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Present')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Absent')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('On Leave')}</th>
                                <th className="px-4 py-3 text-center font-medium">{t('Total Hours')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {paginatedData.length === 0 ? (
                                <tr>
                                    <td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">
                                        <Users className="h-12 w-12 mx-auto mb-2 opacity-50" />
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
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                                    {group.stats.present}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">
                                                    {group.stats.absent}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                                    {group.stats.leave}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center font-semibold">{(group.stats.total_hours || 0).toFixed(2)}</td>
                                        </tr>

                                        {/* Expanded Details */}
                                        {expandedEmployees.has(group.key) && (
                                            <tr>
                                                <td colSpan={8} className="bg-gray-50/50 p-0">
                                                    <div className="px-8 py-4">
                                                        <table className="w-full text-sm">
                                                            <thead className="bg-white">
                                                                <tr className="border-b">
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Date')}</th>
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Clock In')}</th>
                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Clock Out')}</th>
                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Hours')}</th>
                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Status')}</th>
                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Punctuality')}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="divide-y">
                                                                {group.records.map((record, idx) => (
                                                                    <tr key={idx} className="hover:bg-white transition-colors">
                                                                        <td className="px-3 py-2 whitespace-nowrap">{formatDate(record.date)}</td>
                                                                        <td className="px-3 py-2 whitespace-nowrap">{record.clock_in ? formatTime(record.clock_in) : '-'}</td>
                                                                        <td className="px-3 py-2 whitespace-nowrap">{record.clock_out ? formatTime(record.clock_out) : '-'}</td>
                                                                        <td className="px-3 py-2 text-center">{record.total_hour || 0}</td>
                                                                        <td className="px-3 py-2 text-center">{getStatusBadge(record.status)}</td>
                                                                        <td className="px-3 py-2 text-center">{getPunctualityBadge(record.punctuality)}</td>
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

    function CalendarView({ data, holidays }: { data: AttendanceRecord[]; holidays: HolidayRecord[] }) {
        if (!dateRange) return null;

        const [startDate, endDate] = dateRange.split(' - ');
        const start = new Date(startDate);
        const end = new Date(endDate);

        const dates: Date[] = [];
        const current = new Date(start);
        while (current <= end) {
            dates.push(new Date(current));
            current.setDate(current.getDate() + 1);
        }

        const employeeData = data.reduce((acc, record) => {
            const uniqueKey = `${record.employee_user_id}`;
            if (!acc[uniqueKey]) {
                acc[uniqueKey] = {
                    name: record.employee_name,
                    id: record.emp_code,
                    department: record.department_name,
                    avatar: record.employee_avatar || null,
                    records: {}
                };
            }
            const normalizedDate = record.date.split('T')[0];
            acc[uniqueKey].records[normalizedDate] = record;
            return acc;
        }, {} as Record<string, any>);

        const statusConfig: Record<string, { iconColor: string; label: string; icon: React.ReactNode }> = {
            'present': { iconColor: 'text-emerald-500', label: t('Present'), icon: <Check className="w-4 h-4" strokeWidth={2.5} /> },
            'half day': { iconColor: 'text-amber-500', label: t('Half Day'), icon: <Minus className="w-4 h-4" strokeWidth={2.5} /> },
            'absent': { iconColor: 'text-rose-500', label: t('Absent'), icon: <X className="w-4 h-4" strokeWidth={2.5} /> },
            'on leave': { iconColor: 'text-sky-500', label: t('On Leave'), icon: <Plane className="w-4 h-4" strokeWidth={2} /> },
        };

        const isWeekend = (date: Date) => date.getDay() === 0 || date.getDay() === 6;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const isToday = (date: Date) => {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            return d.getTime() === today.getTime();
        };

        const getHolidayForDate = (dateStr: string) => {
            return holidays.find(h => {
                const start = h.start_date.split('T')[0];
                const end = h.end_date.split('T')[0];
                return start <= dateStr && end >= dateStr;
            }) || null;
        };

        return (
            <Card className="shadow-sm overflow-hidden">
                {/* Legend */}
                <div className="flex items-center gap-4 px-4 py-2.5 bg-gray-50 border-b text-xs flex-wrap">
                    <span className="font-semibold text-gray-500 uppercase tracking-wider">{t('Legend')}</span>
                    {Object.entries(statusConfig).map(([, cfg]) => (
                        <div key={cfg.label} className="flex items-center gap-1.5">
                            <span className={cfg.iconColor}>{cfg.icon}</span>
                            <span className="text-gray-600">{cfg.label}</span>
                        </div>
                    ))}
                    <div className="flex items-center gap-1.5">
                        <span className="inline-block w-2.5 h-2.5 rounded-full bg-slate-300" />
                        <span className="text-gray-600">{t('Weekend')}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <Umbrella className="w-3.5 h-3.5 text-yellow-600" />
                        <span className="text-gray-600">{t('Holiday')}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="text-[10px] font-bold text-blue-500">O</span>
                        <span className="text-gray-600">{t('Overtime')}</span>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-sm border-collapse">
                        <thead>
                            <tr className="bg-gray-50">
                                <th className="px-4 py-3 text-left font-semibold text-gray-700 border-r-2 border-gray-200 sticky left-0 bg-gray-50 z-30 min-w-[220px] w-[220px]">
                                    {t('Employee')}
                                </th>
                                {dates.map((date, index) => {
                                    const weekend = isWeekend(date);
                                    const todayCol = isToday(date);
                                    return (
                                        <th
                                            key={index}
                                            className={`py-2 px-1 text-center border-r min-w-[52px] ${todayCol ? 'bg-blue-100' : weekend ? 'bg-slate-100' : ''
                                                }`}
                                        >
                                            <div className={`text-[10px] uppercase tracking-wider font-medium ${weekend ? 'text-slate-400' : 'text-gray-400'
                                                }`}>
                                                {date.toLocaleDateString('en-US', { weekday: 'short' })}
                                            </div>
                                            <div className={`text-sm font-bold mt-0.5 ${todayCol
                                                    ? 'text-blue-600'
                                                    : weekend ? 'text-slate-400' : 'text-gray-800'
                                                }`}>
                                                {date.getDate()}
                                            </div>
                                        </th>
                                    );
                                })}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {Object.values(employeeData).map((employee: any, empIndex) => (
                                <tr key={empIndex} className="hover:bg-gray-50/70 transition-colors group">
                                    <td className="px-4 py-2.5 border-r-2 border-gray-200 sticky left-0 bg-white group-hover:bg-gray-50/70 z-10">
                                        <div className="flex items-center gap-2.5">
                                            <div className="h-8 w-8 rounded-full overflow-hidden bg-gray-100 border-2 border-gray-200 flex items-center justify-center flex-shrink-0">
                                                {employee.avatar ? (
                                                    <img src={getImagePath(employee.avatar)} alt="Avatar" className="w-full h-full object-cover" />
                                                ) : (
                                                    <UserIcon className="w-4 h-4 text-gray-400" />
                                                )}
                                            </div>
                                            <div className="min-w-0">
                                                <div className="font-semibold text-gray-900 text-sm truncate leading-tight">{employee.name}</div>
                                                <div className="text-xs text-gray-400 truncate">{employee.id} · {employee.department}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {dates.map((date, dateIndex) => {
                                        const year = date.getFullYear();
                                        const month = String(date.getMonth() + 1).padStart(2, '0');
                                        const day = String(date.getDate()).padStart(2, '0');
                                        const dateStr = `${year}-${month}-${day}`;
                                        const record = employee.records[dateStr];
                                        const holiday = getHolidayForDate(dateStr);
                                        const weekend = isWeekend(date);
                                        const todayCol = isToday(date);
                                        const cfg = record ? (statusConfig[record.status] || { iconColor: 'text-gray-400', label: record.status, icon: <Minus className="w-4 h-4" /> }) : null;

                                        return (
                                            <td
                                                key={dateIndex}
                                                className={`border-r p-1 ${todayCol ? 'bg-blue-50/40' : holiday ? 'bg-yellow-50/40' : weekend ? 'bg-slate-50' : ''
                                                    }`}
                                            >
                                                {holiday ? (
                                                    <TooltipProvider>
                                                        <Tooltip delayDuration={0}>
                                                            <TooltipTrigger asChild>
                                                                <div className="text-yellow-600 rounded cursor-pointer hover:bg-gray-100 transition-all h-9 flex flex-col items-center justify-center">
                                                                    <Umbrella className="w-4 h-4" />
                                                                </div>
                                                            </TooltipTrigger>
                                                            <TooltipContent side="top" className="text-xs">
                                                                <div className="space-y-1">
                                                                    <p className="font-semibold text-yellow-600">{t('Holiday')}</p>
                                                                    <p>{holiday.name}</p>
                                                                    <p>{formatDate(holiday.start_date)} - {formatDate(holiday.end_date)}</p>
                                                                </div>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                ) : record && cfg ? (
                                                    <TooltipProvider>
                                                        <Tooltip delayDuration={0}>
                                                            <TooltipTrigger asChild>
                                                                <div className={`${cfg.iconColor} rounded cursor-pointer hover:bg-gray-100 transition-all h-9 flex flex-col items-center justify-center`}>
                                                                    {cfg.icon}
                                                                    {parseFloat(record.overtime_hours as any) > 0 && <span className="text-[9px] text-blue-500 leading-none">O</span>}
                                                                </div>
                                                            </TooltipTrigger>
                                                            <TooltipContent side="top" className="text-xs">
                                                                <div className="space-y-1">
                                                                    <p className="font-semibold">{cfg.label}</p>
                                                                    {record.clock_in && record.clock_in !== "-" && record.clock_in !== "null" && <p>{t("In")}: {formatTime(record.clock_in)}</p>}
                                                                    {record.clock_out && record.clock_out !== "-" && record.clock_out !== "null" && <p>{t("Out")}: {formatTime(record.clock_out)}</p>}
                                                                    {parseFloat(record.total_hour as any) > 0 && <p>{t("Hours")}: {record.total_hour}</p>}
                                                                    {parseFloat(record.overtime_hours as any) > 0 && <p className="text-blue-400">{t("Overtime")}: {record.overtime_hours}h</p>}
                                                                    {record.punctuality && record.punctuality !== "-" && <p>{t("Punctuality")}: {record.punctuality}</p>}
                                                                </div>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                ) : (
                                                    <div className="h-9 flex items-center justify-center">
                                                        {weekend
                                                            ? <span className="w-1.5 h-1.5 rounded-full bg-slate-200 block" />
                                                            : <span className="text-gray-200 text-base">—</span>
                                                        }
                                                    </div>
                                                )}
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Card>
        );
    }

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Smart Reports'), url: route('smart-reports.index') },
                { label: t('Attendance Summary Report') }
            ]}
            pageTitle={t('Attendance Summary Report')}
            pageActions={
                <div className="flex items-center gap-2">
                    <ExportButton
                        data={reportData || []}
                        columns={[
                            { key: 'employee_name', header: t('Employee') },
                            { key: 'emp_code', header: t('Employee ID') },
                            { key: 'department_name', header: t('Department') },
                            { key: 'date', header: t('Date'), type: 'date' },
                            { key: 'status', header: t('Status'), type: 'status' },
                        ]}
                        filename={`attendance-report-${dateRange.replace(' - ', '-to-')}`}
                        title={t('Attendance Summary Report')}
                        groupedConfig={{
                            sheetName: 'Attendance Report',
                            groupKey: (r) => `${r.employee_user_id}`,
                            summaryColumns: [
                                { key: 'employee_name', header: t('Employee') },
                                { key: 'emp_code', header: t('Employee ID') },
                                { key: 'department_name', header: t('Department') },
                                { key: 'designation_name', header: t('Designation') },
                                { key: 'total_days', header: t('Total Days'), type: 'number' },
                                { key: 'present', header: t('Present'), type: 'number' },
                                { key: 'absent', header: t('Absent'), type: 'number' },
                                { key: 'on_leave', header: t('On Leave'), type: 'number' },
                                { key: 'half_day', header: t('Half Day'), type: 'number' },
                                { key: 'total_hours', header: t('Total Hours'), type: 'number' },
                            ],
                            detailColumns: [
                                { key: 'date', header: t('Date'), type: 'date' },
                                { key: 'clock_in', header: t('Clock In') },
                                { key: 'clock_out', header: t('Clock Out') },
                                { key: 'total_hour', header: t('Hours'), type: 'number' },
                                { key: 'overtime_hours', header: t('Overtime Hrs'), type: 'number' },
                                { key: 'status', header: t('Status'), type: 'status' },
                                { key: 'punctuality', header: t('Punctuality'), type: 'status' },
                            ],
                            summaryBuilder: (rows) => ({
                                employee_name: rows[0].employee_name,
                                emp_code: rows[0].emp_code,
                                department_name: rows[0].department_name || '',
                                designation_name: rows[0].designation_name || '',
                                total_days: rows.length,
                                present: rows.filter(r => r.status === 'present').length,
                                absent: rows.filter(r => r.status === 'absent').length,
                                on_leave: rows.filter(r => r.status === 'on leave').length,
                                half_day: rows.filter(r => r.status === 'half day').length,
                                total_hours: rows.reduce((s, r) => s + (parseFloat(r.total_hour) || 0), 0).toFixed(2),
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
            <Head title={t('Attendance Summary Report')} />

            <div className="space-y-6">
                {/* Filters */}
                <Card>
                        <CardContent className="px-4 py-3">
                            {/* xl+: all in one flex row | lg: 2-col grid | <lg: 1-col grid */}
                            <div className="hidden xl:flex items-center gap-2">
                                <div className="flex-1 min-w-0">
                                    <DateRangePicker
                                        value={dateRange}
                                        onChange={(value) => setDateRange(value)}
                                        placeholder={t('Select date range')}
                                    />
                                </div>
                                {filterOptions && (
                                    <>
                                        <div className="flex-1 min-w-0">
                                            <Select
                                                value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'}
                                                onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Employee')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((emp) => (
                                                        <SelectItem key={emp.value} value={emp.value.toString()}>
                                                            {emp.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedBranch} onValueChange={v => { setSelectedBranch(v); setSelectedDepartments([]); }}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Branch')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Branches')}</SelectItem>
                                                    {filterOptions.branches.map((b) => (
                                                        <SelectItem key={b.value} value={b.value.toString()}>{b.label}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select
                                                value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'}
                                                onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])}
                                                disabled={selectedBranch === 'all'}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder={selectedBranch === 'all' ? t('Select Branch first') : t('Department')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                    {filterOptions.departments.filter(d => d.branch_id === parseInt(selectedBranch)).map((dept) => (
                                                        <SelectItem key={dept.value} value={dept.value.toString()}>
                                                            {dept.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select
                                                value={selectedStatuses.length > 0 ? selectedStatuses[0] : 'all'}
                                                onValueChange={(value) => setSelectedStatuses(value === 'all' ? [] : [value])}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Status')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Status')}</SelectItem>
                                                    {filterOptions.statuses.map((status) => (
                                                        <SelectItem key={status.value} value={status.value}>
                                                            {t(status.label)}
                                                        </SelectItem>
                                                    ))}
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
                                        const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                                        const newRange = `${fmt(start)} - ${fmt(now)}`;
                                        setDateRange(newRange);
                                        setSelectedEmployees([]);
                                        setSelectedBranch('all');
                                        setSelectedDepartments([]);
                                        setSelectedStatuses([]);
                                        axios.post(route('smart-reports.attendance.generate'), {
                                            start_date: fmt(start),
                                            end_date: fmt(now),
                                            employee_ids: null,
                                            branch_ids: null,
                                            department_ids: null,
                                            status: null,
                                            export_type: 'view',
                                        }).then(response => {
                                            if (response.data.success) {
                                                const reportResult = response.data.data;
                                                setReportData(reportResult.data || []);
                                                setSummary(reportResult.summary || null);
                                                setHolidays(reportResult.holidays || []);
                                            }
                                        }).catch(error => console.error('Clear filter error:', error));
                                    }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                                                    </div>
                            </div>
                            {/* lg (1024px): 2-col grid, buttons on last row right | <lg: 1-col */}
                            <div className="xl:hidden grid grid-cols-1 lg:grid-cols-2 gap-2">
                                <div>
                                    <DateRangePicker
                                        value={dateRange}
                                        onChange={(value) => setDateRange(value)}
                                        placeholder={t('Select date range')}
                                    />
                                </div>
                                {filterOptions && (
                                    <>
                                        <div>
                                            <Select
                                                value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'}
                                                onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Employee')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((emp) => (
                                                        <SelectItem key={emp.value} value={emp.value.toString()}>
                                                            {emp.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select value={selectedBranch} onValueChange={v => { setSelectedBranch(v); setSelectedDepartments([]); }}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Branch')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Branches')}</SelectItem>
                                                    {filterOptions.branches.map((b) => (
                                                        <SelectItem key={b.value} value={b.value.toString()}>{b.label}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select
                                                value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'}
                                                onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])}
                                                disabled={selectedBranch === 'all'}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder={selectedBranch === 'all' ? t('Select Branch first') : t('Department')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                    {filterOptions.departments.filter(d => d.branch_id === parseInt(selectedBranch)).map((dept) => (
                                                        <SelectItem key={dept.value} value={dept.value.toString()}>
                                                            {dept.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        {/* Status + buttons share last lg row */}
                                        <div className="flex items-center gap-2 lg:col-span-1">
                                            <div className="flex-1">
                                                <Select
                                                    value={selectedStatuses.length > 0 ? selectedStatuses[0] : 'all'}
                                                    onValueChange={(value) => setSelectedStatuses(value === 'all' ? [] : [value])}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Status')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="all">{t('All Status')}</SelectItem>
                                                        {filterOptions.statuses.map((status) => (
                                                            <SelectItem key={status.value} value={status.value}>
                                                                {t(status.label)}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="hidden lg:flex items-center gap-2 shrink-0">
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                                    const now = new Date();
                                                    const start = new Date(now.getFullYear(), now.getMonth(), 1);
                                                    const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                                                    const newRange = `${fmt(start)} - ${fmt(now)}`;
                                                    setDateRange(newRange);
                                                    setSelectedEmployees([]);
                                                    setSelectedBranch('all');
                                                    setSelectedDepartments([]);
                                                    setSelectedStatuses([]);
                                                    axios.post(route('smart-reports.attendance.generate'), {
                                                        start_date: fmt(start),
                                                        end_date: fmt(now),
                                                        employee_ids: null,
                                                        branch_ids: null,
                                                        department_ids: null,
                                                        status: null,
                                                        export_type: 'view',
                                                    }).then(response => {
                                                        if (response.data.success) {
                                                            const reportResult = response.data.data;
                                                            setReportData(reportResult.data || []);
                                                            setSummary(reportResult.summary || null);
                                                            setHolidays(reportResult.holidays || []);
                                                        }
                                                    }).catch(error => console.error('Clear filter error:', error));
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
                                    const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                                    const newRange = `${fmt(start)} - ${fmt(now)}`;
                                    setDateRange(newRange);
                                    setSelectedEmployees([]);
                                    setSelectedBranch('all');
                                    setSelectedDepartments([]);
                                    setSelectedStatuses([]);
                                    axios.post(route('smart-reports.attendance.generate'), {
                                        start_date: fmt(start),
                                        end_date: fmt(now),
                                        employee_ids: null,
                                        branch_ids: null,
                                        department_ids: null,
                                        status: null,
                                        export_type: 'view',
                                    }).then(response => {
                                        if (response.data.success) {
                                            const reportResult = response.data.data;
                                            setReportData(reportResult.data || []);
                                            setSummary(reportResult.summary || null);
                                            setHolidays(reportResult.holidays || []);
                                        }
                                    }).catch(error => console.error('Clear filter error:', error));
                                }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                            </div>
                        </CardContent>
                    </Card>

                {/* Summary Cards */}
                {summary && Array.isArray(reportData) && reportData.length > 0 && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Users className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Total Employees')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_employees}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-emerald-50/50 border-emerald-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <UserCheck className="h-4 w-4 text-emerald-600 shrink-0" />
                                        <span className="text-sm font-medium text-emerald-700 truncate">{t('Attendance Rate')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-emerald-700 shrink-0">{summary.attendance_rate}%</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-orange-50/50 border-orange-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Calendar className="h-4 w-4 text-orange-500 shrink-0" />
                                        <span className="text-sm font-medium text-orange-600 truncate">{t('Working Hours')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-orange-600 shrink-0">{(+summary.total_working_hours || 0).toFixed(1)}h</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-violet-50/50 border-violet-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <UserX className="h-4 w-4 text-violet-600 shrink-0" />
                                        <span className="text-sm font-medium text-violet-700 truncate">{t('Overtime Hours')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-violet-700 shrink-0">{(+summary.total_overtime_hours || 0).toFixed(1)}h</div>
                                </div>
                            </CardContent>
                        </Card>
                        {reportData && reportData.length > 0 && (
                            <div className="flex items-center justify-end w-full xl:w-auto shrink-0">
                                <div className="flex border rounded-lg">
                                    <Button
                                        variant={viewMode === 'calendar' ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => setViewMode('calendar')}
                                        className="rounded-r-none"
                                    >
                                        <LayoutGrid className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant={viewMode === 'table' ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => setViewMode('table')}
                                        className="rounded-l-none"
                                    >
                                        <List className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Report Data */}
                {reportData && reportData.length > 0 && (
                    <div>
                        {viewMode === 'table' ? <DataTable data={reportData} /> : <CalendarView data={reportData} holidays={holidays} />}
                    </div>

                )}

                {reportData && reportData.length === 0 && (
                    <Card>
                        <CardContent className="text-center py-12">
                            <Users className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p className="text-lg font-medium text-muted-foreground">
                                {t('No attendance records found.')}
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
};

export default AttendanceReport;
