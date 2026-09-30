import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    TrendingUp, Target, ChevronRight, ChevronDown,
    Search, BarChart3, List, Trophy, XCircle,
    FilterX, ArrowLeft,
} from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip as RechartsTooltip, ResponsiveContainer,
} from 'recharts';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';

interface FilterOptions {
    pipelines: { value: number; label: string }[];
    stages: { value: number; label: string; pipeline_id: number }[];
    users: { value: number; label: string }[];
}

interface DealRecord {
    id: number;
    deal_name: string;
    pipeline_name: string;
    stage_name: string;
    deal_value: number;
    status: string | number;
    assigned_to: string;
    client_names: string;
    created_at: string;
    age_days: number;
    phone: string;
    task_count: number;
    call_count: number;
    email_count: number;
}

interface Summary {
    total_deals: number;
    total_value: number;
    open_deals: number;
    won_deals: number;
    lost_deals: number;
    open_value: number;
    won_value: number;
    lost_value: number;
    win_rate: number;
    average_deal_value: number;
    average_age_days: number;
}

// Normalise status to string regardless of what the API returns
const normaliseStatus = (s: string | number): string => String(s);

const STATUS_MAP: Record<string, { label: string; className: string }> = {
    'Won': { label: 'Won', className: 'bg-green-100 text-green-700' },
    'Loss': { label: 'Loss', className: 'bg-red-100 text-red-700' },
    'Active': { label: 'Active', className: 'bg-blue-100 text-blue-700' },
};

const DealPipelineReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [selectedPipeline, setSelectedPipeline] = useState<string>('all');
    const [selectedStage, setSelectedStage] = useState<string>('all');
    const [selectedStatus, setSelectedStatus] = useState<string>('all');
    const [reportData, setReportData] = useState<DealRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [viewMode, setViewMode] = useState<'chart' | 'table'>('chart');
    const [groupBy, setGroupBy] = useState<'pipeline' | 'stage' | 'status'>('pipeline');
    const [expandedGroups, setExpandedGroups] = useState<Set<string>>(new Set());
    const [selectedChartGroup, setSelectedChartGroup] = useState<string | null>(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);

    useEffect(() => {
        fetchFilterOptions();
        axios.post(route('smart-reports.deal-pipeline.generate'), { export_type: 'view' })
            .then(res => {
                if (res.data.success) {
                    setReportData(res.data.data.data || []);
                    setSummary(res.data.data.summary || null);
                } else {
                    setReportData([]);
                }
            }).catch(() => { setReportData([]); });
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const res = await axios.get(route('smart-reports.deal-pipeline.filter-options'));
            if (res.data.success) setFilterOptions(res.data.data);
        } catch (error: any) {
            toast.error(error.response?.data?.message || t('Failed to load filter options'));
        }
    };

    const handleGenerateReport = async () => {
        try {
            const res = await axios.post(route('smart-reports.deal-pipeline.generate'), {
                pipeline_ids: selectedPipeline !== 'all' ? [parseInt(selectedPipeline)] : null,
                stage_ids: selectedStage !== 'all' ? [parseInt(selectedStage)] : null,
                status: selectedStatus !== 'all' ? [selectedStatus] : null,
                export_type: 'view',
            });
            if (res.data.success) {
                setReportData(res.data.data.data || []);
                setSummary(res.data.data.summary || null);
                toast.success(t('Report generated successfully'));
            }
        } catch (error: any) {
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        }
    };

    const toggleGroup = (key: string) => {
        setExpandedGroups(prev => {
            const s = new Set(prev);
            s.has(key) ? s.delete(key) : s.add(key);
            return s;
        });
    };

    const buildGroups = (data: DealRecord[], mode: 'pipeline' | 'stage' | 'status') => {
        const groups: Record<string, {
            key: string; name: string;
            total_deals: number; total_value: number;
            won: number; lost: number; open: number;
            deals: DealRecord[];
        }> = {};

        data.forEach(d => {
            const st = normaliseStatus(d.status);
            const key = mode === 'pipeline' ? (d.pipeline_name || 'No Pipeline')
                : mode === 'stage' ? (d.stage_name || 'No Stage')
                    : (STATUS_MAP[st]?.label || st);

            if (!groups[key]) {
                groups[key] = { key, name: key, total_deals: 0, total_value: 0, won: 0, lost: 0, open: 0, deals: [] };
            }
            groups[key].total_deals++;
            groups[key].total_value += +(d.deal_value ?? 0);
            if (st === 'Won') groups[key].won++;
            else if (st === 'Loss') groups[key].lost++;
            else groups[key].open++;
            groups[key].deals.push(d);
        });

        return Object.values(groups).sort((a, b) => b.total_value - a.total_value);
    };

    const groupedData = useMemo(() => {
        if (!reportData?.length) return [];
        return buildGroups(reportData, groupBy);
    }, [reportData, groupBy]);

    const filteredGroups = useMemo(() => {
        if (!groupedData.length) return [];
        if (!searchQuery.trim()) return groupedData;
        const q = searchQuery.toLowerCase();
        return groupedData.filter(g =>
            g.name.toLowerCase().includes(q) ||
            g.deals.some(d => d.deal_name.toLowerCase().includes(q) || d.assigned_to?.toLowerCase().includes(q))
        );
    }, [groupedData, searchQuery]);

    // --- Chart data ---
    // Stacked bar: open / won / lost counts per group
    const barChartData = useMemo(() => groupedData.map(g => ({
        name: g.name,
        fullName: g.name,
        [t('Active')]: g.open,
        [t('Won')]: g.won,
        [t('Loss')]: g.lost,
    })), [groupedData, t]);

    // ---- Filters panel ----
    const filtersPanel = (
        <Card>
            <CardContent className="px-4 py-3">
                {/* xl+: all in one flex row | lg: 2-col grid | <lg: 1-col grid */}
                <div className="hidden xl:flex items-center gap-2">
                    {filterOptions && (<>
                        <div className="flex-1 min-w-0">
                            <Select value={selectedPipeline} onValueChange={v => { setSelectedPipeline(v); setSelectedStage('all'); }}>
                                <SelectTrigger><SelectValue placeholder={t('Pipeline')} /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Pipelines')}</SelectItem>
                                    {filterOptions.pipelines.map(p => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex-1 min-w-0">
                            <Select value={selectedStage} onValueChange={setSelectedStage} disabled={selectedPipeline === 'all'}>
                                <SelectTrigger><SelectValue placeholder={selectedPipeline === 'all' ? t('Select Pipeline first') : t('Stage')} /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Stages')}</SelectItem>
                                    {filterOptions.stages.filter(s => s.pipeline_id === parseInt(selectedPipeline)).map(s => <SelectItem key={s.value} value={s.value.toString()}>{s.label}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex-1 min-w-0">
                            <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Status')}</SelectItem>
                                    <SelectItem value="Active">{t('Active')}</SelectItem>
                                    <SelectItem value="Won">{t('Won')}</SelectItem>
                                    <SelectItem value="Loss">{t('Loss')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </>)}
                    <div className="flex items-center gap-2 shrink-0">
                        <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport()}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                        <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                            setSelectedPipeline('all'); setSelectedStage('all'); setSelectedStatus('all');
                            axios.post(route('smart-reports.deal-pipeline.generate'), { export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                        }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                    </div>
                </div>
                {/* lg (1024px): 2-col grid | <lg: 1-col */}
                <div className="xl:hidden grid grid-cols-1 lg:grid-cols-2 gap-2">
                    {filterOptions && (<>
                        <div>
                            <Select value={selectedPipeline} onValueChange={v => { setSelectedPipeline(v); setSelectedStage('all'); }}>
                                <SelectTrigger><SelectValue placeholder={t('Pipeline')} /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Pipelines')}</SelectItem>
                                    {filterOptions.pipelines.map(p => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Select value={selectedStage} onValueChange={setSelectedStage} disabled={selectedPipeline === 'all'}>
                                <SelectTrigger><SelectValue placeholder={selectedPipeline === 'all' ? t('Select Pipeline first') : t('Stage')} /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Stages')}</SelectItem>
                                    {filterOptions.stages.filter(s => s.pipeline_id === parseInt(selectedPipeline)).map(s => <SelectItem key={s.value} value={s.value.toString()}>{s.label}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex items-center gap-2 lg:col-span-1">
                            <div className="flex-1">
                                <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                    <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Status')}</SelectItem>
                                        <SelectItem value="Active">{t('Active')}</SelectItem>
                                        <SelectItem value="Won">{t('Won')}</SelectItem>
                                        <SelectItem value="Loss">{t('Loss')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="hidden lg:flex items-center gap-2 shrink-0">
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport()}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                                <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                    setSelectedPipeline('all'); setSelectedStage('all'); setSelectedStatus('all');
                                    axios.post(route('smart-reports.deal-pipeline.generate'), { export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                            </div>
                        </div>
                    </>)}
                </div>
                {/* Buttons for <lg only */}
                <div className="flex lg:hidden items-center justify-end gap-2 mt-2">
                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport()}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
                    <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                        setSelectedPipeline('all'); setSelectedStage('all'); setSelectedStatus('all');
                        axios.post(route('smart-reports.deal-pipeline.generate'), { export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                    }}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
                </div>
            </CardContent>
        </Card>
    );

    const pageActions = (
        <div className="flex items-center gap-2">
            <ExportButton
                data={reportData || []}
                columns={[
                    { key: 'deal_name', header: t('Deal') },
                    { key: 'pipeline_name', header: t('Pipeline') },
                    { key: 'status', header: t('Status'), type: 'status' },
                ]}
                filename="deal-pipeline-report"
                title={t('Deal Pipeline Report')}
                groupedConfig={{
                    sheetName: 'Deal Pipeline',
                    groupKey: (r) => groupBy === 'pipeline' ? (r.pipeline_name || 'No Pipeline')
                        : groupBy === 'stage' ? (r.stage_name || 'No Stage')
                        : (String(r.status) || 'Unknown'),
                    summaryColumns: [
                        { key: 'group_name',   header: groupBy === 'pipeline' ? t('Pipeline') : groupBy === 'stage' ? t('Stage') : t('Status') },
                        { key: 'total_deals',  header: t('Total Deals'),  type: 'number' },
                        { key: 'open',         header: t('Active'),       type: 'number' },
                        { key: 'won',          header: t('Won'),          type: 'number' },
                        { key: 'lost',         header: t('Loss'),         type: 'number' },
                        { key: 'total_value',  header: t('Total Value'),  type: 'currency' },
                        { key: 'win_rate',     header: t('Win Rate %'),   type: 'number' },
                    ],
                    detailColumns: [
                        { key: 'deal_name',     header: t('Deal') },
                        { key: 'pipeline_name', header: t('Pipeline') },
                        { key: 'stage_name',    header: t('Stage') },
                        { key: 'deal_value',    header: t('Value'),       type: 'currency' },
                        { key: 'status',        header: t('Status'),      type: 'status' },
                        { key: 'assigned_to',   header: t('Assigned To') },
                        { key: 'client_names',  header: t('Clients') },
                        { key: 'age_days',      header: t('Age (Days)'),  type: 'number' },
                        { key: 'created_at',    header: t('Created At'),  type: 'date' },
                    ],
                    summaryBuilder: (rows) => {
                        const totalValue = rows.reduce((s, r) => s + (+r.deal_value || 0), 0);
                        const won = rows.filter(r => String(r.status) === 'Won').length;
                        const lost = rows.filter(r => String(r.status) === 'Loss').length;
                        const open = rows.filter(r => !['Won','Loss'].includes(String(r.status))).length;
                        const winRate = rows.length > 0 ? Math.round(won / rows.length * 100) : 0;
                        const gKey = groupBy === 'pipeline' ? (rows[0].pipeline_name || 'No Pipeline')
                            : groupBy === 'stage' ? (rows[0].stage_name || 'No Stage')
                            : String(rows[0].status);
                        return { group_name: gKey, total_deals: rows.length, open, won, lost, total_value: totalValue, win_rate: winRate };
                    },
                }}
            />
            <Button variant="outline" size="sm" onClick={() => router.visit(route('smart-reports.index'))}>
                <ArrowLeft className="h-4 w-4" />
                {t('Back')}
            </Button>
        </div>
    );

    // ---- Empty state ----
    if (!reportData || reportData.length === 0) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Deal Pipeline Report') }]}
                pageTitle={t('Deal Pipeline Report')}
                pageActions={pageActions}
            >
                <Head title={t('Deal Pipeline Report')} />
                <div className="space-y-6">
                    {filtersPanel}
                    {reportData !== null && reportData.length === 0 && (
                        <Card>
                            <CardContent className="text-center py-12">
                                <Target className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <p className="text-lg font-medium text-muted-foreground">{t('No deals found.')}</p>
                            </CardContent>
                        </Card>
                    )}
                    {reportData === null && (
                        <Card>
                            <CardContent className="text-center py-12">
                                <div className="h-8 w-8 mx-auto border-4 border-primary border-t-transparent rounded-full animate-spin mb-4" />
                                <p className="text-sm text-muted-foreground">{t('Loading...')}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Deal Pipeline Report') }]}
            pageTitle={t('Deal Pipeline Report')}
            pageActions={pageActions}
        >
            <Head title={t('Deal Pipeline Report')} />
            <div className="space-y-6">
                {filtersPanel}

                {/* Summary Cards */}
                {summary && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Target className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Total Deals')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_deals}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-green-50/50 border-green-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Trophy className="h-4 w-4 text-green-600 shrink-0" />
                                        <span className="text-sm font-medium text-green-700 truncate">{t('Won Deals')}</span>
                                    </div>
                                    <div className="text-right min-w-0">
                                        <div className="text-xl font-bold text-green-700 shrink-0">{summary.won_deals}</div>
                                        <div className="text-xs text-green-600 truncate">{formatCurrency(summary.won_value)}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-red-50/50 border-red-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <XCircle className="h-4 w-4 text-red-600 shrink-0" />
                                        <span className="text-sm font-medium text-red-700 truncate">{t('Loss Deals')}</span>
                                    </div>
                                    <div className="text-right min-w-0">
                                        <div className="text-xl font-bold text-red-700 shrink-0">{summary.lost_deals}</div>
                                        <div className="text-xs text-red-600 truncate">{formatCurrency(summary.lost_value)}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-purple-50/50 border-purple-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <TrendingUp className="h-4 w-4 text-purple-600 shrink-0" />
                                        <span className="text-sm font-medium text-purple-700 truncate">{t('Win Rate')}</span>
                                    </div>
                                    <div className="text-right min-w-0">
                                        <div className="text-xl font-bold text-purple-700 shrink-0">{summary.win_rate}%</div>
                                        <div className="text-xs text-purple-600 truncate" title={`${formatCurrency(summary.total_value)} ${t('total')}`}>{formatCurrency(summary.total_value)} {t('total')}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Controls */}
                <Card>
                    <CardContent className="px-4 py-2">
                        <div className="flex items-center justify-between gap-2 flex-wrap">
                            <div className="flex items-center gap-2">
                                <Button variant={groupBy === 'pipeline' ? 'default' : 'outline'} size="sm" onClick={() => setGroupBy('pipeline')}>{t('By Pipeline')}</Button>
                                <Button variant={groupBy === 'stage' ? 'default' : 'outline'} size="sm" onClick={() => setGroupBy('stage')}>{t('By Stage')}</Button>
                                <Button variant={groupBy === 'status' ? 'default' : 'outline'} size="sm" onClick={() => setGroupBy('status')}>{t('By Status')}</Button>
                            </div>
                            <div className="flex border rounded-lg">
                                <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-r-none">
                                    <BarChart3 className="h-4 w-4" />
                                </Button>
                                <Button variant={viewMode === 'table' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('table')} className="rounded-l-none">
                                    <List className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* ---- CHART VIEW ---- */}
                {viewMode === 'chart' && (() => {
                    const needsScroll = barChartData.length > 5;
                    const chartWidth = needsScroll ? barChartData.length * 130 : undefined;
                    const barSize = needsScroll ? 28 : Math.max(12, Math.floor(560 / (barChartData.length * 3 + barChartData.length)));
                    const chartHeight = 360;

                    const WrappedXAxisTick = ({ x, y, payload }: any) => {
                        const words = (payload.value as string).split(' ');
                        const mid = Math.ceil(words.length / 2);
                        let line1 = words.slice(0, mid).join(' ');
                        let line2 = words.slice(mid).join(' ');
                        const MAX = 14;
                        if (line1.length > MAX) line1 = line1.slice(0, MAX - 1) + '…';
                        if (line2.length > MAX) line2 = line2.slice(0, MAX - 1) + '…';
                        return (
                            <g transform={`translate(${x},${y})`}>
                                <text x={0} y={0} dy={14} textAnchor="middle" fontSize={11} fill="#666">{line1}</text>
                                {line2 && <text x={0} y={0} dy={28} textAnchor="middle" fontSize={11} fill="#666">{line2}</text>}
                            </g>
                        );
                    };

                    const chartInner = (
                        <BarChart
                            width={chartWidth}
                            height={chartHeight}
                            data={barChartData}
                            barSize={barSize}
                            barCategoryGap="30%"
                            margin={{ top: 10, right: 20, left: 0, bottom: 10 }}
                        >
                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                            <XAxis
                                dataKey="name"
                                tick={<WrappedXAxisTick />}
                                height={60}
                                interval={0}
                                tickLine={false}
                            />
                            <YAxis allowDecimals={false} tick={{ fontSize: 11 }} width={35} />
                            <RechartsTooltip
                                labelFormatter={(label, payload) => payload?.[0]?.payload?.fullName || label}
                                contentStyle={{ borderRadius: '8px', fontSize: '13px' }}
                            />
                            <Bar dataKey={t('Active')} fill="#3b82f6" radius={[4, 4, 0, 0]} cursor="pointer"
                                onClick={(data) => { const n = data?.fullName || data?.name; if (n) setSelectedChartGroup(prev => prev === n ? null : n); }}
                            />
                            <Bar dataKey={t('Won')} fill="#22c55e" radius={[4, 4, 0, 0]} cursor="pointer"
                                onClick={(data) => { const n = data?.fullName || data?.name; if (n) setSelectedChartGroup(prev => prev === n ? null : n); }}
                            />
                            <Bar dataKey={t('Loss')} fill="#ef4444" radius={[4, 4, 0, 0]} cursor="pointer"
                                onClick={(data) => { const n = data?.fullName || data?.name; if (n) setSelectedChartGroup(prev => prev === n ? null : n); }}
                            />
                        </BarChart>
                    );

                    return (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Left: Chart */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base flex items-center gap-2">
                                        <BarChart3 className="h-5 w-5" />
                                        {t('Deal Count by')} {groupBy === 'pipeline' ? t('Pipeline') : groupBy === 'stage' ? t('Stage') : t('Status')}
                                        <TooltipProvider>
                                            <Tooltip delayDuration={0}>
                                                <TooltipTrigger asChild>
                                                    <span className="ml-auto cursor-pointer text-yellow-500 hover:text-yellow-600 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="12" y1="16" x2="12" y2="12" />
                                                            <line x1="12" y1="8" x2="12.01" y2="8" />
                                                        </svg>
                                                    </span>
                                                </TooltipTrigger>
                                                <TooltipContent side="left" className="max-w-xs text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 font-normal">
                                                    <p>{t('Click on any bar to view its deal details on the right panel.')}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {needsScroll ? (
                                        <div className="overflow-x-auto">
                                            <div style={{ width: chartWidth }}>
                                                {chartInner}
                                            </div>
                                        </div>
                                    ) : (
                                        <ResponsiveContainer width="100%" height={chartHeight}>
                                            {chartInner}
                                        </ResponsiveContainer>
                                    )}
                                    <div className="flex flex-wrap items-center justify-center gap-4 pt-2 pb-1">
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#3b82f6] shrink-0"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Active')}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#22c55e] shrink-0"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Won')}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#ef4444] shrink-0"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Loss')}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Right: Pipeline deal detail panel */}
                            <Card>
                                <CardHeader>
                                    {selectedChartGroup ? (
                                        <CardTitle className="text-base flex items-center justify-between gap-2">
                                            <div className="flex items-center gap-2 min-w-0">
                                                <BarChart3 className="h-5 w-5 text-black-500 shrink-0" />
                                                <span className="truncate">{selectedChartGroup}</span>
                                            </div>
                                            <button onClick={() => setSelectedChartGroup(null)} className="text-xs text-muted-foreground hover:text-foreground shrink-0">
                                                ✕ {t('Clear')}
                                            </button>
                                        </CardTitle>
                                    ) : (
                                        <CardTitle className="text-base flex items-center gap-2">
                                            <Target className="h-5 w-5 text-black-500" />
                                            {t('Deals by')} {groupBy === 'pipeline' ? t('Pipeline') : groupBy === 'stage' ? t('Stage') : t('Status')}
                                        </CardTitle>
                                    )}
                                </CardHeader>
                                <CardContent className="p-0">
                                    {selectedChartGroup ? (() => {
                                        const grp = groupedData.find(g => g.name === selectedChartGroup);
                                        if (!grp) return <div className="px-4 py-8 text-center text-muted-foreground text-sm">{t('No data')}</div>;
                                        return (
                                            <div>
                                                <div className="grid grid-cols-4 divide-x border-b">
                                                    <div className="px-3 py-2 text-center">
                                                        <div className="text-xs text-muted-foreground">{t('Total')}</div>
                                                        <div className="font-bold text-sm">{grp.total_deals}</div>
                                                    </div>
                                                    <div className="px-3 py-2 text-center">
                                                        <div className="text-xs text-blue-600">{t('Active')}</div>
                                                        <div className="font-bold text-sm text-blue-700">{grp.open}</div>
                                                    </div>
                                                    <div className="px-3 py-2 text-center">
                                                        <div className="text-xs text-green-600">{t('Won')}</div>
                                                        <div className="font-bold text-sm text-green-700">{grp.won}</div>
                                                    </div>
                                                    <div className="px-3 py-2 text-center">
                                                        <div className="text-xs text-red-500">{t('Loss')}</div>
                                                        <div className="font-bold text-sm text-red-600">{grp.lost}</div>
                                                    </div>
                                                </div>
                                                <div className="overflow-x-auto">
                                                    <table className="w-full text-sm">
                                                        <thead className="bg-muted/40">
                                                            <tr>
                                                                <th className="px-4 py-2 text-left text-xs font-medium">{t('Deal Name')}</th>
                                                                <th className="px-4 py-2 text-left text-xs font-medium">{t('Stage')}</th>
                                                                <th className="px-4 py-2 text-right text-xs font-medium">{t('Value')}</th>
                                                                <th className="px-4 py-2 text-center text-xs font-medium">{t('Status')}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody className="divide-y">
                                                            {grp.deals.map(deal => {
                                                                const st = normaliseStatus(deal.status);
                                                                const info = STATUS_MAP[st] ?? { label: st, className: 'bg-gray-100 text-gray-700' };
                                                                return (
                                                                    <tr key={deal.id} className="hover:bg-muted/30">
                                                                        <td className="px-4 py-2 font-medium text-gray-900">{deal.deal_name}</td>
                                                                        <td className="px-4 py-2 text-xs text-muted-foreground">{deal.stage_name || '-'}</td>
                                                                        <td className="px-4 py-2 text-right font-semibold">{formatCurrency(deal.deal_value)}</td>
                                                                        <td className="px-4 py-2 text-center">
                                                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${info.className}`}>
                                                                                {t(info.label)}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                );
                                                            })}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        );
                                    })() : (
                                        // Default: show all groups with deal counts
                                        <div className="overflow-x-auto">
                                            <table className="w-full text-sm">
                                                <thead className="bg-muted/40">
                                                    <tr>
                                                        <th className="px-4 py-2 text-left text-xs font-medium">{groupBy === 'pipeline' ? t('Pipeline') : groupBy === 'stage' ? t('Stage') : t('Status')}</th>
                                                        <th className="px-4 py-2 text-center text-xs font-medium">{t('Total')}</th>
                                                        <th className="px-4 py-2 text-center text-xs font-medium">{t('Active')}</th>
                                                        <th className="px-4 py-2 text-center text-xs font-medium">{t('Won')}</th>
                                                        <th className="px-4 py-2 text-center text-xs font-medium">{t('Loss')}</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y">
                                                    {groupedData.map(g => (
                                                        <tr key={g.key} className="hover:bg-muted/30 cursor-pointer" onClick={() => setSelectedChartGroup(g.name)}>
                                                            <td className="px-4 py-2 font-medium text-gray-900">{g.name}</td>
                                                            <td className="px-4 py-2 text-center">
                                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">{g.total_deals}</span>
                                                            </td>
                                                            <td className="px-4 py-2 text-center">
                                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700">{g.open}</span>
                                                            </td>
                                                            <td className="px-4 py-2 text-center">
                                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700">{g.won}</span>
                                                            </td>
                                                            <td className="px-4 py-2 text-center">
                                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700">{g.lost}</span>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    );
                })()}

                {/* ---- TABLE VIEW ---- */}
                {viewMode === 'table' && (() => {
                    const totalPages = Math.ceil(filteredGroups.length / perPage);
                    const paginatedGroups = filteredGroups.slice((currentPage - 1) * perPage, currentPage * perPage);
                    return (
                    <Card>
                        <CardContent className="p-4 border-b bg-gray-50/50">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex-1 max-w-md relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input placeholder={t('Search...')} value={searchQuery} onChange={e => { setSearchQuery(e.target.value); setCurrentPage(1); }} className="pl-9" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <PerPageSelector
                                        routeName="smart-reports.deal-pipeline.index"
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
                                            {groupBy === 'pipeline' ? t('Pipeline') : groupBy === 'stage' ? t('Stage') : t('Status')}
                                        </th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Total Deals')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Active')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Won')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Loss')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Total Value')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Win Rate')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {paginatedGroups.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">
                                                <Target className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                <p>{t('No records found')}</p>
                                            </td>
                                        </tr>
                                    ) : paginatedGroups.map(group => {
                                        const eligible = group.total_deals;
                                        const rate = eligible > 0 ? Math.round(group.won / eligible * 100) : 0;
                                        return (
                                            <React.Fragment key={group.key}>
                                                <tr
                                                    className={`hover:bg-muted/50 transition-colors bg-purple-50/20 cursor-pointer`}
                                                    onClick={() => toggleGroup(group.key)}
                                                >
                                                    <td className="px-4 py-3">
                                                        {expandedGroups.has(group.key)
                                                            ? <ChevronDown className="h-4 w-4" />
                                                            : <ChevronRight className="h-4 w-4" />}
                                                    </td>
                                                    <td className="px-4 py-3 font-semibold text-gray-900">{group.name}</td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">{group.total_deals}</span>
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700">{group.open}</span>
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700">{group.won}</span>
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700">{group.lost}</span>
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-bold text-gray-900">{formatCurrency(group.total_value)}</td>
                                                    <td className="px-4 py-3 text-center">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <div className="w-16 bg-gray-200 rounded-full h-2">
                                                                <div className="bg-green-500 h-2 rounded-full" style={{ width: `${rate}%` }} />
                                                            </div>
                                                            <span className="text-xs font-semibold text-gray-700">{rate}%</span>
                                                        </div>
                                                    </td>
                                                </tr>

                                                {expandedGroups.has(group.key) && (
                                                    <tr>
                                                        <td colSpan={8} className="bg-gray-50/50 p-0">
                                                            <div className="px-8 py-4">
                                                                <table className="w-full text-sm">
                                                                    <thead className="bg-white">
                                                                        <tr className="border-b">
                                                                            <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground w-36">{t('Deal Name')}</th>
                                                                            <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground w-40">{t('Pipeline / Stage')}</th>
                                                                            <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground w-44">{t('Clients')}</th>
                                                                            <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground w-28">{t('Value')}</th>
                                                                            <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground w-20">{t('Status')}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody className="divide-y">
                                                                        {group.deals.map(deal => {
                                                                            const st = normaliseStatus(deal.status);
                                                                            const info = STATUS_MAP[st] ?? { label: st, className: 'bg-gray-100 text-gray-700' };
                                                                            return (
                                                                                <tr key={deal.id} className="hover:bg-white transition-colors">
                                                                                    <td className="px-3 py-2 font-medium">{deal.deal_name}</td>
                                                                                    <td className="px-3 py-2 text-muted-foreground text-xs">
                                                                                        {deal.pipeline_name}{deal.stage_name ? ` / ${deal.stage_name}` : ''}
                                                                                    </td>
                                                                                    <td className="px-3 py-2 text-xs text-muted-foreground w-44 max-w-[11rem] truncate" title={deal.client_names || ""}>{deal.client_names || "-"}</td>
                                                                                    <td className="px-3 py-2 text-right font-semibold w-28">{formatCurrency(deal.deal_value)}</td>
                                                                                    <td className="px-3 py-2 text-center">
                                                                                        <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${info.className}`}>
                                                                                            {t(info.label)}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            );
                                                                        })}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                )}
                                            </React.Fragment>
                                        );
                                    })}
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

export default DealPipelineReport;
