import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, DollarSign, TrendingUp, TrendingDown, Clock, Award, AlertCircle, CheckCircle2, Info, LayoutGrid, Table2, Layout, BarChart3, Search, FilterX, ArrowLeft } from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, ResponsiveContainer } from 'recharts';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';

interface FilterOptions {
    employees: { value: number; label: string }[];
    departments: { value: number; label: string }[];
    payrolls: { value: number; label: string }[];
}

interface PayrollRecord {
    employee_id: string;
    employee_name: string;
    department_name: string;
    designation_name: string;
    basic_salary: number;
    total_allowances: number;
    total_manual_overtimes: number;
    attendance_overtime_amount: number;
    gross_pay: number;
    total_deductions: number;
    total_loans: number;
    net_pay: number;
    working_days: number;
    present_days: number;
    absent_days: number;
    half_days: number;
    paid_leave_days: number;
    unpaid_leave_days: number;
    unpaid_leave_deduction: number;
    half_day_deduction: number;
    absent_day_deduction: number;
    allowances_breakdown?: any[];
    deductions_breakdown?: any[];
    manual_overtimes_breakdown?: any[];
    loans_breakdown?: any[];
    payroll_title: string;
    pay_date: string;
}

interface Summary {
    total_records: number;
    total_gross_pay: number;
    total_net_pay: number;
    total_allowances: number;
    total_deductions: number;
    total_loans: number;
    total_overtime: number;
    average_net_pay: number;
}

const PayrollReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [dateRange, setDateRange] = useState<string>('');
    const [selectedEmployees, setSelectedEmployees] = useState<number[]>([]);
    const [selectedDepartments, setSelectedDepartments] = useState<number[]>([]);
    const [selectedPayroll, setSelectedPayroll] = useState<string>('');
    const [reportData, setReportData] = useState<PayrollRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [selectedEmployee, setSelectedEmployee] = useState<PayrollRecord | null>(null);
    const [viewMode, setViewMode] = useState<'grid' | 'chart'>('chart');
    const [searchQuery, setSearchQuery] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [itemsPerPage, setItemsPerPage] = useState(10);

    // filteredData - search apply, used for both grid view AND export
    const filteredData = useMemo(() => {
        if (!reportData?.length) return [];
        if (!searchQuery.trim()) return reportData;
        const q = searchQuery.toLowerCase();
        return reportData.filter(r =>
            r.employee_name.toLowerCase().includes(q) ||
            r.employee_id.toLowerCase().includes(q) ||
            r.department_name.toLowerCase().includes(q)
        );
    }, [reportData, searchQuery]);

    const selectEmployee = (record: PayrollRecord) => {
        console.log('Selected Employee Data:', record);
        console.log('Total Deductions:', record.total_deductions);
        console.log('Total Loans:', record.total_loans);
        console.log('Unpaid Leave Deduction:', record.unpaid_leave_deduction);
        console.log('Half Day Deduction:', record.half_day_deduction);
        console.log('Absent Day Deduction:', record.absent_day_deduction);
        console.log('Sum of all deductions:', record.total_deductions + record.total_loans);
        setSelectedEmployee(record);
    };

    useEffect(() => {
        fetchFilterOptions();

        // Auto-set last month date range
        const lastMonthStart = new Date();
        lastMonthStart.setMonth(lastMonthStart.getMonth() - 1);
        lastMonthStart.setDate(1);

        const lastMonthEnd = new Date();
        lastMonthEnd.setDate(0);

        const formatDateHelper = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const autoDateRange = `${formatDateHelper(lastMonthStart)} - ${formatDateHelper(lastMonthEnd)}`;
        setDateRange(autoDateRange);

        // Auto-generate report
        setTimeout(() => {
            const [startDate, endDate] = autoDateRange.split(' - ');

            axios.post(route('smart-reports.payroll.generate'), {
                start_date: startDate,
                end_date: endDate,
                employee_ids: null,
                department_ids: null,
                payroll_id: null,
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
            const response = await axios.get(route('smart-reports.payroll.filter-options'));
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
            const response = await axios.post(route('smart-reports.payroll.generate'), {
                start_date: startDate,
                end_date: endDate,
                employee_ids: selectedEmployees.length > 0 ? selectedEmployees : null,
                department_ids: selectedDepartments.length > 0 ? selectedDepartments : null,
                payroll_id: selectedPayroll || null,
                export_type: 'view',
            });

            if (response.data.success) {
                const reportResult = response.data.data;
                setReportData(reportResult.data || []);
                setSummary(reportResult.summary || null);
                setCurrentPage(1);
                toast.success(t('Report generated successfully'));
            }
        } catch (error: any) {
            console.error('Export error:', error);
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Smart Reports'), url: route('smart-reports.index') },
                { label: t('Payroll Sheet Report') }
            ]}
            pageTitle={t('Payroll Sheet Report')}
            pageActions={
                <div className="flex items-center gap-2">
                    <ExportButton
                        data={filteredData}
                        columns={[
                            { key: 'employee_name', header: t('Employee Name') },
                            { key: 'employee_id', header: t('Employee ID') },
                            { key: 'department_name', header: t('Department') },
                            { key: 'payroll_title', header: t('Payroll Name') },
                            { key: 'total_deductions', header: t('Deductions'), type: 'currency' as const },
                            { key: 'net_pay', header: t('Net Salary'), type: 'currency' as const },
                        ]}
                        filename={`payroll-report-${dateRange.replace(' - ', '-to-')}`}
                        title={t('Payroll Sheet Report')}
                        groupedConfig={{
                            sheetName: 'Payroll Report',
                            groupKey: (r) => r.payroll_title || 'Unknown Payroll',
                            summaryColumns: [
                                { key: 'payroll_title', header: t('Payroll Name') },
                                { key: 'total_employees', header: t('Total Employees'), type: 'number' },
                                { key: 'total_net', header: t('Total Net Salary'), type: 'currency' },
                                { key: 'total_deductions', header: t('Total Deductions'), type: 'currency' },
                            ],
                            detailColumns: [
                                { key: 'employee_name', header: t('Employee Name') },
                                { key: 'employee_id', header: t('Employee ID') },
                                { key: 'department_name', header: t('Department') },
                                { key: 'total_deductions', header: t('Deductions'), type: 'currency' },
                                { key: 'net_pay', header: t('Net Salary'), type: 'currency' },
                            ],
                            summaryBuilder: (rows) => ({
                                payroll_title: rows[0].payroll_title || '',
                                total_employees: rows.length,
                                total_net: rows.reduce((s: number, r: any) => s + (parseFloat(r.net_pay) || 0), 0),
                                total_deductions: rows.reduce((s: number, r: any) => s + (parseFloat(r.total_deductions) || 0), 0),
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
            <Head title={t('Payroll Sheet Report')} />

            <div className="space-y-6">
                {/* Filters */}
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
                                            <Select value={selectedPayroll || 'all'} onValueChange={(value) => setSelectedPayroll(value === 'all' ? '' : value)}>
                                                <SelectTrigger><SelectValue placeholder={t('Payroll')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Payrolls')}</SelectItem>
                                                    {filterOptions.payrolls.map((p) => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'} onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Employee')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((e) => <SelectItem key={e.value} value={e.value.toString()}>{e.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <Select value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'} onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Department')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                    {filterOptions.departments.map((d) => <SelectItem key={d.value} value={d.value.toString()}>{d.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </>
                                )}
                                <div className="flex items-center gap-2 shrink-0">
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth() - 1, 1); const end = new Date(now.getFullYear(), now.getMonth(), 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedEmployees([]); setSelectedDepartments([]); setSelectedPayroll(''); axios.post(route('smart-reports.payroll.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, payroll_id: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
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
                                            <Select value={selectedPayroll || 'all'} onValueChange={(value) => setSelectedPayroll(value === 'all' ? '' : value)}>
                                                <SelectTrigger><SelectValue placeholder={t('Payroll')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Payrolls')}</SelectItem>
                                                    {filterOptions.payrolls.map((p) => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Select value={selectedEmployees.length > 0 ? selectedEmployees[0].toString() : 'all'} onValueChange={(value) => setSelectedEmployees(value === 'all' ? [] : [parseInt(value)])}>
                                                <SelectTrigger><SelectValue placeholder={t('Employee')} /></SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Employees')}</SelectItem>
                                                    {filterOptions.employees.map((e) => <SelectItem key={e.value} value={e.value.toString()}>{e.label}</SelectItem>)}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        {/* Department + buttons share last lg row */}
                                        <div className="flex items-center gap-2 lg:col-span-1">
                                            <div className="flex-1">
                                                <Select value={selectedDepartments.length > 0 ? selectedDepartments[0].toString() : 'all'} onValueChange={(value) => setSelectedDepartments(value === 'all' ? [] : [parseInt(value)])}>
                                                    <SelectTrigger><SelectValue placeholder={t('Department')} /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="all">{t('All Departments')}</SelectItem>
                                                        {filterOptions.departments.map((d) => <SelectItem key={d.value} value={d.value.toString()}>{d.label}</SelectItem>)}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="hidden lg:flex items-center gap-2 shrink-0">
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth() - 1, 1); const end = new Date(now.getFullYear(), now.getMonth(), 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedEmployees([]); setSelectedDepartments([]); setSelectedPayroll(''); axios.post(route('smart-reports.payroll.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, payroll_id: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                            {/* Buttons for <lg only */}
                            <div className="flex lg:hidden items-center justify-end gap-2 mt-2">
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth() - 1, 1); const end = new Date(now.getFullYear(), now.getMonth(), 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedEmployees([]); setSelectedDepartments([]); setSelectedPayroll(''); axios.post(route('smart-reports.payroll.generate'), { start_date: fmt(start), end_date: fmt(end), employee_ids: null, department_ids: null, payroll_id: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                            </div>
                        </CardContent>
                    </Card>

                {/* Summary Cards */}
                {summary && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Users className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Employees')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_records}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-green-50/50 border-green-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <TrendingUp className="h-4 w-4 text-green-600 shrink-0" />
                                        <span className="text-sm font-medium text-green-700 truncate">{t('Gross Pay')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-green-700 shrink-0">{formatCurrency(summary.total_gross_pay)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-red-50/50 border-red-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <TrendingDown className="h-4 w-4 text-red-600 shrink-0" />
                                        <span className="text-sm font-medium text-red-700 truncate">{t('Deductions')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-red-700 shrink-0">{formatCurrency(summary.total_deductions)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <DollarSign className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Net Pay')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-blue-700 shrink-0">{formatCurrency(summary.total_net_pay)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        {reportData && reportData.length > 0 && (
                            <div className="flex items-center justify-end w-full xl:w-auto shrink-0">
                                <div className="flex border rounded-lg">
                                    <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-r-none">
                                        <BarChart3 className="h-4 w-4" />
                                    </Button>
                                    <Button variant={viewMode === 'grid' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('grid')} className="rounded-l-none">
                                        <LayoutGrid className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {reportData && reportData.length > 0 && !summary && (
                    <div className="flex justify-end">
                        <div className="flex border rounded-lg">
                            <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-r-none">
                                <BarChart3 className="h-4 w-4" />
                            </Button>
                            <Button variant={viewMode === 'grid' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('grid')} className="rounded-l-none">
                                <LayoutGrid className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                )}

                {reportData && reportData.length > 0 && viewMode === 'grid' && (() => {
                    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                    const paginatedData = filteredData.slice(
                        (currentPage - 1) * itemsPerPage,
                        currentPage * itemsPerPage
                    );

                    return (
                        <>
                            <Card>
                                <CardContent className="p-4">
                                    <div className="flex items-center justify-between gap-4">
                                        <div className="flex-1 max-w-md">
                                            <input
                                                type="text"
                                                value={searchQuery}
                                                onChange={(e) => {
                                                    setSearchQuery(e.target.value);
                                                    setCurrentPage(1);
                                                }}
                                                placeholder={t('Search by employee name')}
                                                className="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                            />
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center gap-2">
                                                <PerPageSelector
                                                    routeName="smart-reports.payroll.index"
                                                    defaultValue={itemsPerPage.toString()}
                                                    onPageChange={(val) => { setItemsPerPage(Number(val)); setCurrentPage(1); }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                {paginatedData.map((record, index) => (
                                    <Card key={index} className="hover:shadow-xl transition-all duration-300 border-2 hover:border-blue-400">
                                        <CardHeader className="pb-3 bg-gradient-to-br from-slate-50 to-white">
                                            {record.payroll_title && (
                                                <CardTitle className="text-sm font-bold text-blue-600 truncate mb-2" title={record.payroll_title}>
                                                    {record.payroll_title}
                                                </CardTitle>
                                            )}
                                            <CardDescription className="text-xs space-y-1">
                                                <div className="flex items-center justify-between gap-2">
                                                    <span className="text-slate-900 font-semibold truncate" title={record.employee_name}>{record.employee_name}</span>
                                                    <span className="text-slate-600 font-medium">{record.employee_id}</span>
                                                </div>
                                                <div className="text-slate-500 truncate" title={record.department_name}>{record.department_name}</div>
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-3 pt-1">
                                            <div className="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 text-center shadow-sm border border-blue-200">
                                                <div className="text-xs text-blue-700 font-medium">{t('Net Salary')}</div>
                                                <div className="text-xl font-bold text-blue-700">{formatCurrency(record.net_pay)}</div>
                                            </div>
                                            <div className="space-y-2">
                                                <div className="flex justify-between items-center text-xs">
                                                    <span className="text-slate-600 font-medium">{t('Gross')}:</span>
                                                    <span className="font-bold text-green-700">{formatCurrency(record.gross_pay)}</span>
                                                </div>
                                                <div className="flex justify-between items-center text-xs">
                                                    <span className="text-slate-600 font-medium">{t('Deductions')}:</span>
                                                    <span className="font-bold text-red-700">-{formatCurrency(Number(record.total_deductions) + Number(record.total_loans))}</span>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            <Card>
                                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                                    <Pagination
                                        data={{ current_page: currentPage, last_page: totalPages, per_page: itemsPerPage, total: filteredData.length, from: ((currentPage - 1) * itemsPerPage) + 1, to: Math.min(currentPage * itemsPerPage, filteredData.length) }}
                                        onPageChange={(page) => setCurrentPage(page)}
                                    />
                                </CardContent>
                            </Card>
                        </>
                    );
                })()}

                {reportData && reportData.length > 0 && viewMode === 'chart' && (() => {
                    const chartWidth = Math.max(reportData.length * 120, 600);

                    const WrappedXAxisTick = ({ x, y, payload }: any) => {
                        const name: string = payload.value || '';
                        const maxCharsPerLine = 12;
                        const words = name.split(' ');
                        let line1 = '';
                        let line2 = '';
                        for (const word of words) {
                            if (!line1) { line1 = word; continue; }
                            if ((line1 + ' ' + word).length <= maxCharsPerLine) { line1 += ' ' + word; }
                            else if (!line2) { line2 = word; }
                            else if ((line2 + ' ' + word).length <= maxCharsPerLine) { line2 += ' ' + word; }
                            else { line2 = line2.length > maxCharsPerLine - 1 ? line2.slice(0, maxCharsPerLine - 1) + '…' : line2 + '…'; break; }
                        }
                        return (
                            <g transform={`translate(${x},${y})`}>
                                <text x={0} y={0} dy={14} textAnchor="middle" fontSize={11} fill="#666">
                                    {line1}
                                </text>
                                {line2 && (
                                    <text x={0} y={0} dy={28} textAnchor="middle" fontSize={11} fill="#666">
                                        {line2}
                                    </text>
                                )}
                            </g>
                        );
                    };

                    return (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg flex items-center gap-2">
                                    <BarChart3 className="h-5 w-5" />
                                    {t('Salary Comparison Chart')}
                                </CardTitle>
                                <CardDescription>{t('Employee salary breakdown')}</CardDescription>
                            </CardHeader>
                            <CardContent className="pb-2">
                                <div style={{ overflowX: 'auto' }}>
                                    <div style={{ width: chartWidth, minWidth: '100%' }}>
                                        <ResponsiveContainer width="100%" height={400} debounce={1}>
                                            <LineChart
                                                data={reportData.slice(0, Math.min(reportData.length, 50))}
                                                margin={{ top: 5, right: 30, left: 20, bottom: 10 }}
                                            >
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis
                                                    dataKey="employee_name"
                                                    height={60}
                                                    interval={0}
                                                    tick={<WrappedXAxisTick />}
                                                    tickLine={false}
                                                />
                                                <YAxis
                                                    tick={{ fontSize: 12 }}
                                                    tickFormatter={(value) => `${(value / 1000).toFixed(0)}k`}
                                                />
                                                <RechartsTooltip
                                                    formatter={(value: any) => formatCurrency(value)}
                                                    labelFormatter={(label: string, payload: any) => {
                                                        if (payload && payload.length > 0) {
                                                            const d = payload[0].payload;
                                                            return `${d.employee_name} (${d.department_name})`;
                                                        }
                                                        return label;
                                                    }}
                                                    contentStyle={{ backgroundColor: '#fff', border: '1px solid #ccc', borderRadius: '8px', padding: '10px' }}
                                                />
                                                <Line type="monotone" dataKey="gross_pay" stroke="#10b981" strokeWidth={2} name={t('Gross Pay')} dot={{ fill: '#10b981', r: 4 }} />
                                                <Line type="monotone" dataKey="net_pay" stroke="#3b82f6" strokeWidth={2} name={t('Net Pay')} dot={{ fill: '#3b82f6', r: 4 }} />
                                                <Line type="monotone" dataKey={(d) => Number(d.total_deductions) + Number(d.total_loans)} stroke="#ef4444" strokeWidth={2} name={t('Deductions')} dot={{ fill: '#ef4444', r: 4 }} />
                                            </LineChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                                {/* Static legend outside scroll – mirrors LeaveReport pattern */}
                                <div className="flex items-center justify-center gap-6 pt-2 pb-1">
                                    <div className="flex items-center gap-1.5">
                                        <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#10b981]"></span>
                                        <span className="text-sm font-medium text-muted-foreground">{t('Gross Pay')}</span>
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#3b82f6]"></span>
                                        <span className="text-sm font-medium text-muted-foreground">{t('Net Pay')}</span>
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#ef4444]"></span>
                                        <span className="text-sm font-medium text-muted-foreground">{t('Deductions')}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })()}

                {reportData !== null && reportData.length === 0 && (
                    <Card>
                        <CardContent className="text-center py-12">
                            <Users className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p className="text-lg font-medium text-muted-foreground">
                                {t('No payroll records found.')}
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
};

export default PayrollReport;
