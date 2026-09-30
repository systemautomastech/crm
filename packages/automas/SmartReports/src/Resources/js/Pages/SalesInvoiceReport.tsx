import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Card, CardContent } from '@/components/ui/card';
import { Filter, FileText, DollarSign, TrendingUp, Receipt, Search, ChevronRight, ChevronDown, Clock, Building2, Users, Warehouse, EyeOff, FilterX, ArrowLeft } from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';

interface FilterOptions {
    customers: { value: number; label: string }[];
    warehouses: { value: number; label: string }[];
}

interface SalesInvoiceRecord {
    id: number;
    invoice_number: string;
    invoice_date: string;
    due_date: string;
    customer_name: string;
    customer_email: string;
    warehouse_name: string;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    paid_amount: number;
    balance_amount: number;
    status: string;
}

interface Summary {
    total_invoices: number;
    total_amount: number;
    total_paid: number;
    total_balance: number;
    total_tax: number;
    average_invoice_value: number;
}

const SalesInvoiceReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [dateRange, setDateRange] = useState<string>('');
    const [selectedCustomers, setSelectedCustomers] = useState<number[]>([]);
    const [selectedWarehouses, setSelectedWarehouses] = useState<number[]>([]);
    const [selectedStatus, setSelectedStatus] = useState<string[]>([]);
    const [reportData, setReportData] = useState<SalesInvoiceRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [groupBy, setGroupBy] = useState<'customer' | 'warehouse' | 'monthly'>('customer');
    const [expandedGroups, setExpandedGroups] = useState<Set<string>>(new Set());
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);

    useEffect(() => {
        fetchFilterOptions();

        const now = new Date();
        const currentMonthStart = new Date(now.getFullYear(), now.getMonth(), 1);
        const currentMonthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        const formatDateHelper = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const autoDateRange = `${formatDateHelper(currentMonthStart)} - ${formatDateHelper(currentMonthEnd)}`;
        setDateRange(autoDateRange);

        setTimeout(() => {
            const [startDate, endDate] = autoDateRange.split(' - ');

            axios.post(route('smart-reports.sales-invoice.generate'), {
                start_date: startDate,
                end_date: endDate,
                customer_ids: null,
                warehouse_ids: null,
                status: null,
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
            const response = await axios.get(route('smart-reports.sales-invoice.filter-options'));
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
            const response = await axios.post(route('smart-reports.sales-invoice.generate'), {
                start_date: startDate,
                end_date: endDate,
                customer_ids: selectedCustomers.length > 0 ? selectedCustomers : null,
                warehouse_ids: selectedWarehouses.length > 0 ? selectedWarehouses : null,
                status: selectedStatus.length > 0 ? selectedStatus : null,
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

    const toggleGroup = (key: string) => {
        setExpandedGroups(prev => {
            const newSet = new Set(prev);
            if (newSet.has(key)) {
                newSet.delete(key);
            } else {
                newSet.add(key);
            }
            return newSet;
        });
    };

    const getStatusBadgeClass = (status: string) => {
        const classes: Record<string, string> = {
            'draft': 'bg-gray-100 text-gray-700',
            'posted': 'bg-blue-100 text-blue-700',
            'paid': 'bg-green-100 text-green-700',
            'partial': 'bg-yellow-100 text-yellow-700',
            'overdue': 'bg-red-100 text-red-700',
        };
        return `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${classes[status] || 'bg-gray-100 text-gray-700'}`;
    };

    const buildGroups = (data: SalesInvoiceRecord[], mode: 'customer' | 'warehouse' | 'monthly') => {
        const groups: Record<string, {
            key: string;
            name: string;
            email?: string;
            total_invoices: number;
            total_amount: number;
            paid_amount: number;
            balance_amount: number;
            invoices: SalesInvoiceRecord[];
        }> = {};

        data.forEach(record => {
            let key: string;
            let name: string;
            let email: string | undefined;

            if (mode === 'customer') {
                key = record.customer_name;
                name = record.customer_name;
                email = record.customer_email;
            } else if (mode === 'warehouse') {
                key = record.warehouse_name || 'No Warehouse';
                name = key;
            } else {
                const date = new Date(record.invoice_date);
                key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                name = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            }

            if (!groups[key]) {
                groups[key] = { key, name, email, total_invoices: 0, total_amount: 0, paid_amount: 0, balance_amount: 0, invoices: [] };
            }
            groups[key].total_invoices++;
            groups[key].total_amount += +record.total_amount;
            groups[key].paid_amount += +record.paid_amount;
            groups[key].balance_amount += +record.balance_amount;
            groups[key].invoices.push(record);
        });

        const entries = Object.values(groups);
        return mode === 'monthly'
            ? entries.sort((a, b) => a.key.localeCompare(b.key))
            : entries.sort((a, b) => b.total_amount - a.total_amount);
    };

    const groupedData = useMemo(() => {
        if (!reportData || reportData.length === 0) return [];
        return buildGroups(reportData, groupBy);
    }, [reportData, groupBy]);

    const filteredGroups = useMemo(() => {
        if (!groupedData.length) return [];
        if (!searchQuery.trim()) return groupedData;
        const query = searchQuery.toLowerCase();
        return groupedData.filter(group =>
            group.name.toLowerCase().includes(query) ||
            group.email?.toLowerCase().includes(query) ||
            group.invoices.some(inv => inv.invoice_number.toLowerCase().includes(query))
        );
    }, [groupedData, searchQuery]);

    const salesFilterSection = (
        <Card>
            <CardContent className="px-4 py-3">
                {/* xl+: all in one flex row | lg: 2-col grid | <lg: 1-col grid */}
                <div className="hidden xl:flex items-center gap-2">
                    <div className="flex-1 min-w-0">
                        <DateRangePicker value={dateRange} onChange={setDateRange} placeholder={t('Select date range')} />
                    </div>
                    {filterOptions && (
                        <>
                            <div className="flex-1 min-w-0">
                                <Select value={selectedCustomers.length > 0 ? selectedCustomers[0].toString() : 'all'} onValueChange={(v) => setSelectedCustomers(v === 'all' ? [] : [parseInt(v)])}>
                                    <SelectTrigger><SelectValue placeholder={t('Customer')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Customers')}</SelectItem>
                                        {filterOptions.customers.map(c => <SelectItem key={c.value} value={c.value.toString()}>{c.label}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex-1 min-w-0">
                                <Select value={selectedWarehouses.length > 0 ? selectedWarehouses[0].toString() : 'all'} onValueChange={(v) => setSelectedWarehouses(v === 'all' ? [] : [parseInt(v)])}>
                                    <SelectTrigger><SelectValue placeholder={t('Warehouse')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                        {filterOptions.warehouses.map(w => <SelectItem key={w.value} value={w.value.toString()}>{w.label}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex-1 min-w-0">
                                <Select value={selectedStatus.length > 0 ? selectedStatus[0] : 'all'} onValueChange={(v) => setSelectedStatus(v === 'all' ? [] : [v])}>
                                    <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Status')}</SelectItem>
                                        <SelectItem value="draft">{t('Draft')}</SelectItem>
                                        <SelectItem value="posted">{t('Posted')}</SelectItem>
                                        <SelectItem value="partial">{t('Partial')}</SelectItem>
                                        <SelectItem value="paid">{t('Paid')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </>
                    )}
                    <div className="flex items-center gap-2 shrink-0">
                        <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                        <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth(), 1); const end = new Date(now.getFullYear(), now.getMonth() + 1, 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedCustomers([]); setSelectedWarehouses([]); setSelectedStatus([]); axios.post(route('smart-reports.sales-invoice.generate'), { start_date: fmt(start), end_date: fmt(end), customer_ids: null, warehouse_ids: null, status: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                    </div>
                </div>
                {/* lg (1024px): 2-col grid, buttons on last row right | <lg: 1-col */}
                <div className="xl:hidden grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <div>
                        <DateRangePicker value={dateRange} onChange={setDateRange} placeholder={t('Select date range')} />
                    </div>
                    {filterOptions && (
                        <>
                            <div>
                                <Select value={selectedCustomers.length > 0 ? selectedCustomers[0].toString() : 'all'} onValueChange={(v) => setSelectedCustomers(v === 'all' ? [] : [parseInt(v)])}>
                                    <SelectTrigger><SelectValue placeholder={t('Customer')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Customers')}</SelectItem>
                                        {filterOptions.customers.map(c => <SelectItem key={c.value} value={c.value.toString()}>{c.label}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Select value={selectedWarehouses.length > 0 ? selectedWarehouses[0].toString() : 'all'} onValueChange={(v) => setSelectedWarehouses(v === 'all' ? [] : [parseInt(v)])}>
                                    <SelectTrigger><SelectValue placeholder={t('Warehouse')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                        {filterOptions.warehouses.map(w => <SelectItem key={w.value} value={w.value.toString()}>{w.label}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            {/* Status + buttons share last lg row */}
                            <div className="flex items-center gap-2 lg:col-span-1">
                                <div className="flex-1">
                                    <Select value={selectedStatus.length > 0 ? selectedStatus[0] : 'all'} onValueChange={(v) => setSelectedStatus(v === 'all' ? [] : [v])}>
                                        <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Status')}</SelectItem>
                                            <SelectItem value="draft">{t('Draft')}</SelectItem>
                                            <SelectItem value="posted">{t('Posted')}</SelectItem>
                                            <SelectItem value="partial">{t('Partial')}</SelectItem>
                                            <SelectItem value="paid">{t('Paid')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="hidden lg:flex items-center gap-2 shrink-0">
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth(), 1); const end = new Date(now.getFullYear(), now.getMonth() + 1, 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedCustomers([]); setSelectedWarehouses([]); setSelectedStatus([]); axios.post(route('smart-reports.sales-invoice.generate'), { start_date: fmt(start), end_date: fmt(end), customer_ids: null, warehouse_ids: null, status: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                                </div>
                            </div>
                        </>
                    )}
                </div>
                {/* Buttons for <lg only */}
                <div className="flex lg:hidden items-center justify-end gap-2 mt-2">
                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => { const now = new Date(); const start = new Date(now.getFullYear(), now.getMonth(), 1); const end = new Date(now.getFullYear(), now.getMonth() + 1, 0); const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; setDateRange(`${fmt(start)} - ${fmt(end)}`); setSelectedCustomers([]); setSelectedWarehouses([]); setSelectedStatus([]); axios.post(route('smart-reports.sales-invoice.generate'), { start_date: fmt(start), end_date: fmt(end), customer_ids: null, warehouse_ids: null, status: null, export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {}); }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                </div>
            </CardContent>
        </Card>
    );

    const salesPageActions = (
        <div className="flex items-center gap-2">
            <ExportButton
                data={reportData || []}
                columns={[
                    { key: 'invoice_number', header: t('Invoice #') },
                    { key: 'customer_name', header: t('Customer') },
                    { key: 'status', header: t('Status'), type: 'status' as const },
                ]}
                filename={`sales-invoice-report-${dateRange.replace(' - ', '-to-')}`}
                title={t('Sales Invoice Report')}
                groupedConfig={{
                    sheetName: 'Sales Invoice',
                    groupKey: (r) => {
                        if (groupBy === 'customer') return r.customer_name;
                        if (groupBy === 'warehouse') return r.warehouse_name || 'No Warehouse';
                        const d = new Date(r.invoice_date);
                        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
                    },
                    summaryColumns: [
                        { key: 'group_name',     header: groupBy === 'customer' ? t('Customer') : groupBy === 'warehouse' ? t('Warehouse') : t('Period') },
                        { key: 'total_invoices', header: t('Total Invoices'), type: 'number' },
                        { key: 'total_amount',   header: t('Total Amount'),   type: 'currency' },
                        { key: 'paid_amount',    header: t('Paid Amount'),    type: 'currency' },
                        { key: 'balance_amount', header: t('Balance Due'),    type: 'currency' },
                    ],
                    detailColumns: [
                        { key: 'invoice_number',  header: t('Invoice #') },
                        { key: 'invoice_date',    header: t('Invoice Date'), type: 'date' },
                        { key: 'due_date',        header: t('Due Date'),     type: 'date' },
                        { key: 'customer_name',   header: t('Customer') },
                        { key: 'warehouse_name',  header: t('Warehouse') },
                        { key: 'total_amount',    header: t('Amount'),       type: 'currency' },
                        { key: 'paid_amount',     header: t('Paid'),         type: 'currency' },
                        { key: 'balance_amount',  header: t('Balance'),      type: 'currency' },
                        { key: 'status',          header: t('Status'),       type: 'status' },
                    ],
                    summaryBuilder: (rows) => {
                        const gName = groupBy === 'customer' ? rows[0].customer_name
                            : groupBy === 'warehouse' ? (rows[0].warehouse_name || 'No Warehouse')
                            : (() => { const d = new Date(rows[0].invoice_date); return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }); })();
                        return {
                            group_name:     gName,
                            total_invoices: rows.length,
                            total_amount:   rows.reduce((s, r) => s + (+r.total_amount || 0), 0),
                            paid_amount:    rows.reduce((s, r) => s + (+r.paid_amount || 0), 0),
                            balance_amount: rows.reduce((s, r) => s + (+r.balance_amount || 0), 0),
                        };
                    },
                }}
            />
            <Button variant="outline" size="sm" onClick={() => router.visit(route('smart-reports.index'))}>
                <ArrowLeft className="h-4 w-4" />
                {t('Back')}
            </Button>
        </div>
    );

    if (!reportData || reportData.length === 0) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[
                    { label: t('Smart Reports'), url: route('smart-reports.index') },
                    { label: t('Sales Invoice Report') }
                ]}
                pageTitle={t('Sales Invoice Report')}
                pageActions={salesPageActions}
            >
                <Head title={t('Sales Invoice Report')} />
                <div className="space-y-6">
                    {salesFilterSection}
                    {reportData !== null && reportData.length === 0 && (
                        <Card>
                            <CardContent className="text-center py-12">
                                <Receipt className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <p className="text-lg font-medium text-muted-foreground">{t('No sales invoices found.')}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Smart Reports'), url: route('smart-reports.index') },
                { label: t('Sales Invoice Report') }
            ]}
            pageTitle={t('Sales Invoice Report')}
            pageActions={salesPageActions}
        >
            <Head title={t('Sales Invoice Report')} />

            <div className="space-y-6">
                {salesFilterSection}

                {summary && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Receipt className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Total Invoices')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_invoices}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-green-50/50 border-green-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <TrendingUp className="h-4 w-4 text-green-600 shrink-0" />
                                        <span className="text-sm font-medium text-green-700 truncate">{t('Total Amount')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-green-700 shrink-0">{formatCurrency(summary.total_amount)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-teal-50/50 border-teal-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <DollarSign className="h-4 w-4 text-teal-600 shrink-0" />
                                        <span className="text-sm font-medium text-teal-700 truncate">{t('Paid Amount')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-teal-700 shrink-0">{formatCurrency(summary.total_paid)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-red-50/50 border-red-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <FileText className="h-4 w-4 text-red-600 shrink-0" />
                                        <span className="text-sm font-medium text-red-700 truncate">{t('Balance Due')}</span>
                                    </div>
                                    <div className="text-xl font-bold text-red-700 shrink-0">{formatCurrency(summary.total_balance)}</div>
                                </div>
                            </CardContent>
                        </Card>
                        {reportData && reportData.length > 0 && (
                            <div className="flex items-center justify-end w-full xl:w-auto shrink-0">
                                <div className="flex border rounded-lg">
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button variant={groupBy === 'customer' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('customer')} className="rounded-r-none">
                                                    <Users className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent><p>{t('By Customer')}</p></TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button variant={groupBy === 'warehouse' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('warehouse')} className="rounded-l-none rounded-r-none">
                                                    <Warehouse className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent><p>{t('By Warehouse')}</p></TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button variant={groupBy === 'monthly' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('monthly')} className="rounded-l-none">
                                                    <Clock className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent><p>{t('By Month')}</p></TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {reportData && reportData.length > 0 && !summary && (
                    <div className="flex justify-end">
                        <div className="flex border rounded-lg">
                            <TooltipProvider>
                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Button variant={groupBy === 'customer' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('customer')} className="rounded-r-none">
                                            <Users className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent><p>{t('By Customer')}</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <TooltipProvider>
                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Button variant={groupBy === 'warehouse' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('warehouse')} className="rounded-l-none rounded-r-none">
                                            <Warehouse className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent><p>{t('By Warehouse')}</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <TooltipProvider>
                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Button variant={groupBy === 'monthly' ? 'default' : 'ghost'} size="sm" onClick={() => setGroupBy('monthly')} className="rounded-l-none">
                                            <Clock className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent><p>{t('By Month')}</p></TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </div>
                )}

                {/* Table View */}
                {(() => {
                    const totalPages = Math.ceil(filteredGroups.length / perPage);
                    const paginatedGroups = filteredGroups.slice((currentPage - 1) * perPage, currentPage * perPage);
                    return (
                    <Card>
                        <CardContent className="p-4 border-b bg-gray-50/50">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex-1 max-w-md relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input type="text" placeholder={t('Search...')} value={searchQuery} onChange={(e) => { setSearchQuery(e.target.value); setCurrentPage(1); }} className="pl-9" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <PerPageSelector
                                        routeName="smart-reports.sales-invoice.index"
                                        defaultValue={perPage.toString()}
                                        onPageChange={(val) => { setPerPage(Number(val)); setCurrentPage(1); }}
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-muted/50 sticky top-0">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium w-12"></th>
                                        <th className="px-4 py-3 text-left font-medium">
                                            {groupBy === 'customer' ? t('Customer') : groupBy === 'warehouse' ? t('Warehouse') : t('Period')}
                                        </th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Total Invoices')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Total Amount')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Paid Amount')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Balance Due')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Collection %')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {paginatedGroups.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-12 text-center text-muted-foreground">
                                                <Receipt className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                <p>{t('No records found')}</p>
                                            </td>
                                        </tr>
                                    ) : (
                                        paginatedGroups.map((group) => (
                                            <React.Fragment key={group.key}>
                                                <tr
                                                    className="hover:bg-muted/50 transition-colors bg-blue-50/30 cursor-pointer"
                                                    onClick={() => toggleGroup(group.key)}
                                                >
                                                    <td className="px-4 py-3">
                                                        {expandedGroups.has(group.key) ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <div>
                                                            <div className="font-semibold text-gray-900">{group.name}</div>
                                                            {groupBy === 'customer' && group.email && (
                                                                <div className="text-xs text-muted-foreground">{group.email}</div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">
                                                            {group.total_invoices}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-bold text-gray-900">{formatCurrency(group.total_amount)}</td>
                                                    <td className="px-4 py-3 text-right font-semibold text-green-600">{formatCurrency(group.paid_amount)}</td>
                                                    <td className="px-4 py-3 text-right font-semibold text-red-600">{formatCurrency(group.balance_amount)}</td>
                                                    <td className="px-4 py-3 text-center">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <div className="w-16 bg-gray-200 rounded-full h-2">
                                                                <div
                                                                    className="bg-green-500 h-2 rounded-full"
                                                                    style={{ width: `${group.total_amount > 0 ? Math.min((group.paid_amount / group.total_amount * 100), 100) : 0}%` }}
                                                                ></div>
                                                            </div>
                                                            <span className="text-xs font-semibold text-gray-700">
                                                                {group.total_amount > 0 ? Math.round(group.paid_amount / group.total_amount * 100) : 0}%
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>

                                                {expandedGroups.has(group.key) && (() => {
                                                    // For 'customer' groupBy: flat invoice rows (no sub-grouping needed)
                                                    // For 'monthly'/'warehouse': sub-group by customer, collapsible if >1 invoice
                                                    if (groupBy === 'customer') {
                                                        return (
                                                            <tr>
                                                                <td colSpan={7} className="bg-gray-50/50 p-0">
                                                                    <div className="px-8 py-4">
                                                                        <table className="w-full text-sm">
                                                                            <thead className="bg-white">
                                                                                <tr className="border-b">
                                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Invoice #')}</th>
                                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Warehouse')}</th>
                                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Invoice Date')}</th>
                                                                                    <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Due Date')}</th>
                                                                                    <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Amount')}</th>
                                                                                    <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Paid')}</th>
                                                                                    <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Balance')}</th>
                                                                                    <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Status')}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody className="divide-y">
                                                                                {group.invoices.map((record, idx) => (
                                                                                    <tr key={idx} className="hover:bg-white transition-colors">
                                                                                        <td className="px-3 py-2 font-medium">{record.invoice_number}</td>
                                                                                        <td className="px-3 py-2">{record.warehouse_name || '-'}</td>
                                                                                        <td className="px-3 py-2 whitespace-nowrap">{formatDate(record.invoice_date)}</td>
                                                                                        <td className="px-3 py-2 whitespace-nowrap">{formatDate(record.due_date)}</td>
                                                                                        <td className="px-3 py-2 text-right font-semibold">{formatCurrency(record.total_amount)}</td>
                                                                                        <td className="px-3 py-2 text-right text-green-600 font-semibold">{formatCurrency(record.paid_amount)}</td>
                                                                                        <td className="px-3 py-2 text-right text-red-600 font-semibold">{formatCurrency(record.balance_amount)}</td>
                                                                                        <td className="px-3 py-2 text-center">
                                                                                            <span className={getStatusBadgeClass(record.status)}>
                                                                                                {t(record.status.charAt(0).toUpperCase() + record.status.slice(1))}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                ))}
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        );
                                                    }

                                                    // 'monthly' or 'warehouse': sub-group invoices by customer
                                                    const customerSubGroups = Object.values(
                                                        group.invoices.reduce((acc, inv) => {
                                                            if (!acc[inv.customer_name]) {
                                                                acc[inv.customer_name] = { name: inv.customer_name, invoices: [], total: 0, paid: 0, balance: 0 };
                                                            }
                                                            acc[inv.customer_name].invoices.push(inv);
                                                            acc[inv.customer_name].total += +inv.total_amount;
                                                            acc[inv.customer_name].paid += +inv.paid_amount;
                                                            acc[inv.customer_name].balance += +inv.balance_amount;
                                                            return acc;
                                                        }, {} as Record<string, { name: string; invoices: SalesInvoiceRecord[]; total: number; paid: number; balance: number }>)
                                                    );

                                                    return (
                                                        <tr>
                                                            <td colSpan={7} className="bg-gray-50/50 p-0">
                                                                <div className="px-8 py-4">
                                                                    <table className="w-full text-sm">
                                                                        <thead className="bg-white">
                                                                            <tr className="border-b">
                                                                                <th className="w-8"></th>
                                                                                <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Customer')}</th>
                                                                                <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Invoices')}</th>
                                                                                <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Invoice Date')}</th>
                                                                                <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Amount')}</th>
                                                                                <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Paid')}</th>
                                                                                <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Balance')}</th>
                                                                                <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Status')}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody className="divide-y">
                                                                            {customerSubGroups.map((cg) => {
                                                                                const subKey = `${group.key}::${cg.name}`;
                                                                                const isSubExpanded = expandedGroups.has(subKey);
                                                                                const hasMultiple = cg.invoices.length > 1;
                                                                                return (
                                                                                    <React.Fragment key={subKey}>
                                                                                        {!hasMultiple ? (
                                                                                            // Single invoice: direct row, no sub-group header
                                                                                            <tr className="bg-white hover:bg-gray-50 transition-colors">
                                                                                                <td></td>
                                                                                                <td className="px-3 py-2 font-medium text-gray-800">{cg.name}<span className="ml-2 text-xs text-muted-foreground font-normal">({cg.invoices[0].invoice_number})</span></td>
                                                                                                <td className="px-3 py-2 text-center">
                                                                                                    <span className="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">1</span>
                                                                                                </td>
                                                                                                <td className="px-3 py-2 text-xs text-muted-foreground whitespace-nowrap">{formatDate(cg.invoices[0].invoice_date)}</td>
                                                                                                <td className="px-3 py-2 text-right font-bold">{formatCurrency(cg.invoices[0].total_amount)}</td>
                                                                                                <td className="px-3 py-2 text-right text-green-600 font-semibold">{formatCurrency(cg.invoices[0].paid_amount)}</td>
                                                                                                <td className="px-3 py-2 text-right text-red-600 font-semibold">{formatCurrency(cg.invoices[0].balance_amount)}</td>
                                                                                                <td className="px-3 py-2 text-center">
                                                                                                    <span className={getStatusBadgeClass(cg.invoices[0].status)}>
                                                                                                        {t(cg.invoices[0].status.charAt(0).toUpperCase() + cg.invoices[0].status.slice(1))}
                                                                                                    </span>
                                                                                                </td>
                                                                                            </tr>
                                                                                        ) : (
                                                                                            // Multiple invoices: collapsible sub-group
                                                                                            <>
                                                                                                <tr className="bg-white hover:bg-blue-50/30 transition-colors cursor-pointer" onClick={() => toggleGroup(subKey)}>
                                                                                                    <td className="px-2 py-2 text-center">
                                                                                                        {isSubExpanded ? <ChevronDown className="h-3 w-3" /> : <ChevronRight className="h-3 w-3" />}
                                                                                                    </td>
                                                                                                    <td className="px-3 py-2 font-semibold text-gray-800">{cg.name}</td>
                                                                                                    <td className="px-3 py-2 text-center">
                                                                                                        <span className="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">{cg.invoices.length}</span>
                                                                                                    </td>
                                                                                                    <td className="px-3 py-2 text-left text-xs text-muted-foreground">—</td>
                                                                                                    <td className="px-3 py-2 text-right font-bold">{formatCurrency(cg.total)}</td>
                                                                                                    <td className="px-3 py-2 text-right text-green-600 font-semibold">{formatCurrency(cg.paid)}</td>
                                                                                                    <td className="px-3 py-2 text-right text-red-600 font-semibold">{formatCurrency(cg.balance)}</td>
                                                                                                    <td></td>
                                                                                                </tr>
                                                                                                {isSubExpanded && cg.invoices.map((record, idx) => (
                                                                                                    <tr key={idx} className="bg-gray-50 hover:bg-white transition-colors">
                                                                                                        <td></td>
                                                                                                        <td className="px-3 py-2 pl-6 font-medium text-gray-600">{record.invoice_number}</td>
                                                                                                        <td className="px-3 py-2 text-center"><span className="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600">1</span></td>
                                                                                                        <td className="px-3 py-2 text-left text-xs text-muted-foreground whitespace-nowrap">{formatDate(record.invoice_date)}</td>
                                                                                                        <td className="px-3 py-2 text-right">{formatCurrency(record.total_amount)}</td>
                                                                                                        <td className="px-3 py-2 text-right text-green-600">{formatCurrency(record.paid_amount)}</td>
                                                                                                        <td className="px-3 py-2 text-right text-red-600">{formatCurrency(record.balance_amount)}</td>
                                                                                                        <td className="px-3 py-2 text-center">
                                                                                                            <span className={getStatusBadgeClass(record.status)}>
                                                                                                                {t(record.status.charAt(0).toUpperCase() + record.status.slice(1))}
                                                                                                            </span>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                ))}
                                                                                            </>
                                                                                        )}
                                                                                    </React.Fragment>
                                                                                );
                                                                            })}
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })()}
                                            </React.Fragment>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                            <Pagination
                                data={{ current_page: currentPage, last_page: totalPages, per_page: perPage, total: filteredGroups.length, from: ((currentPage - 1) * perPage) + 1, to: Math.min(currentPage * perPage, filteredGroups.length) }}
                                onPageChange={(page) => setCurrentPage(page)}
                            />
                        </CardContent>
                    </Card>
                    );
                })()}
            </div>
        </AuthenticatedLayout>
    );
};

export default SalesInvoiceReport;
