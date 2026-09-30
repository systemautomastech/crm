import React, { useState, useEffect, useMemo, useCallback } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    FolderKanban, Search,
    BarChart3, List, CheckCircle2,
    AlertTriangle, TrendingUp, Target,
    ListTodo, Flag,
    User, CalendarDays, Clock, Milestone, FilterX, ArrowLeft
} from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip as RechartsTooltip, ResponsiveContainer,
} from 'recharts';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose,
} from '@/components/ui/dialog';

interface FilterOptions {
    projects: { value: number; label: string }[];
    statuses: { value: string; label: string }[];
}

interface ProjectRecord {
    id: number;
    project_name: string;
    description: string;
    budget: number;
    start_date: string;
    end_date: string;
    status: string;
    total_tasks: number;
    completed_tasks: number;
    pending_tasks: number;
    progress_percentage: number;
    total_milestones: number;
    completed_milestones: number;
    milestone_cost: number;
    days_remaining: number;
    health_status: string;
}

interface Summary {
    total_projects: number;
    ongoing_projects: number;
    onhold_projects: number;
    finished_projects: number;
    on_track_projects: number;
    at_risk_projects: number;
    delayed_projects: number;
    total_budget: number;
    total_milestone_cost: number;
    total_tasks: number;
    completed_tasks: number;
    avg_progress: number;
    overall_completion: number;
}

interface ChartDataItem {
    name: string;
    fullName: string;
    id: number;
    status: string;
    health: string;
    record: ProjectRecord;
    [key: string]: any;
}

interface MilestoneRecord {
    id: number;
    title: string;
    cost: number;
    start_date: string;
    end_date: string;
    summary: string;
    status: string;
    progress: number;
}

interface TaskRecord {
    id: number;
    title: string;
    priority: string;
    assigned_to_names: string;
    duration: string;
    description: string;
    milestone_id: number;
    stage_name: string;
    stage_color: string;
    is_complete: boolean;
}

const ProjectProgressReport: React.FC = () => {
    const { t } = useTranslation();

    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [selectedStatus, setSelectedStatus] = useState<string>('all');
    const [selectedProject, setSelectedProject] = useState<string>('all');

    const getDefaultDateRange = () => {
        const now = new Date();
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        const fmt = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        return `${fmt(start)} - ${fmt(end)}`;
    };

    const [dateRange, setDateRange] = useState<string>(getDefaultDateRange());
    const [reportData, setReportData] = useState<ProjectRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [viewMode, setViewMode] = useState<'chart' | 'table'>('chart');
    const [expandedRows, setExpandedRows] = useState<Set<number>>(new Set());
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [selectedChartProject, setSelectedChartProject] = useState<ProjectRecord | null>(null);
    const [milestones, setMilestones] = useState<MilestoneRecord[]>([]);
    const [tasks, setTasks] = useState<TaskRecord[]>([]);
    const [showMilestonesModal, setShowMilestonesModal] = useState(false);
    const [showTasksModal, setShowTasksModal] = useState(false);

    useEffect(() => {
        fetchFilterOptions();
        const defaultRange = getDefaultDateRange();
        const [s, e] = defaultRange.split(' - ');
        axios.post(route('smart-reports.project-progress.generate'), { export_type: 'view', start_date: s, end_date: e })
            .then(res => {
                if (res.data.success) {
                    setReportData(res.data.data.data || []);
                    setSummary(res.data.data.summary || null);
                }
            }).catch(() => { });
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const res = await axios.get(route('smart-reports.project-progress.filter-options'));
            if (res.data.success) setFilterOptions(res.data.data);
        } catch {
            toast.error(t('Failed to load filter options'));
        }
    };

    const handleGenerateReport = async (exportType: 'view') => {
        try {
            const payload: Record<string, any> = {
                export_type: 'view',
                status: selectedStatus !== 'all' ? [selectedStatus] : null,
                project_ids: selectedProject !== 'all' ? [parseInt(selectedProject)] : null,
            };
            if (dateRange?.includes(' - ')) {
                const [s, e] = dateRange.split(' - ');
                payload.start_date = s;
                payload.end_date = e;
            }
            const res = await axios.post(route('smart-reports.project-progress.generate'), payload);
            if (res.data.success) {
                setReportData(res.data.data.data || []);
                setSummary(res.data.data.summary || null);
                toast.success(t('Report generated successfully'));
            }
        } catch {
            toast.error(t('Failed to generate report'));
        }
    };

    const handleOpenMilestones = useCallback((project: any) => {
        setSelectedChartProject(project);
        if (project.milestones) {
            setMilestones(project.milestones);
        } else {
            setMilestones([]);
        }
        setShowMilestonesModal(true);
    }, []);

    const handleOpenTasks = useCallback((project: any) => {
        setSelectedChartProject(project);
        if (project.tasks) {
            setTasks(project.tasks);
        } else {
            setTasks([]);
        }
        setShowTasksModal(true);
    }, []);

    const toggleRow = (id: number) =>
        setExpandedRows(prev => { const s = new Set(prev); s.has(id) ? s.delete(id) : s.add(id); return s; });

    const filtered = useMemo(() => {
        if (!reportData) return [];
        if (!searchQuery.trim()) return reportData;
        const q = searchQuery.toLowerCase();
        return reportData.filter(r => r.project_name.toLowerCase().includes(q));
    }, [reportData, searchQuery]);

    const chartData: ChartDataItem[] = useMemo(() => {
        if (!reportData) return [];
        return [...reportData].sort((a, b) => b.total_tasks - a.total_tasks).map(r => ({
            name: r.project_name.length > 14 ? r.project_name.slice(0, 14) + '\u2026' : r.project_name,
            fullName: r.project_name,
            id: r.id,
            status: r.status,
            health: r.health_status,
            record: r,
            [t('Completed Tasks')]: parseInt(r.completed_tasks as any) || 0,
            [t('Pending Tasks')]: parseInt(r.pending_tasks as any) || 0,
            [t('Milestones Done')]: parseInt(r.completed_milestones as any) || 0,
            [t('Progress')]: parseFloat(r.progress_percentage as any) || 0,
        }));
    }, [reportData, t]);

    const handleBarClick = useCallback((data: any) => {
        if (data?.record) {
            setSelectedChartProject(data.record);
        }
    }, []);

    const handleXAxisClick = useCallback((data: any) => {
        if (data?.record) {
            setSelectedChartProject(data.record);
        }
    }, []);

    const healthBadge = (h: string) => {
        const map: Record<string, string> = {
            'On Track': 'bg-green-100 text-green-700',
            'At Risk': 'bg-amber-100 text-amber-700',
            'Delayed': 'bg-red-100 text-red-700',
        };
        return map[h] || 'bg-gray-100 text-gray-700';
    };

    const statusBadge = (s: string) => {
        const map: Record<string, string> = {
            Ongoing: 'bg-indigo-100 text-indigo-700',
            Onhold: 'bg-amber-100 text-amber-700',
            Finished: 'bg-green-100 text-green-700',
        };
        return map[s] || 'bg-gray-100 text-gray-700';
    };

    const priorityColor = (p: string) => {
        const map: Record<string, string> = {
            High: 'text-red-600 bg-red-50 border-red-200',
            Medium: 'text-amber-600 bg-amber-50 border-amber-200',
            Low: 'text-green-600 bg-green-50 border-green-200',
        };
        return map[p] || 'text-gray-600 bg-gray-50 border-gray-200';
    };

   const filtersPanel = (
        <Card>
            <CardContent className="px-4 py-3">
                <div className="hidden xl:flex items-center gap-2">
                    <div className="flex-1 min-w-0">
                        <DateRangePicker value={dateRange} onChange={setDateRange} placeholder={t('Select date range')} />
                    </div>
                    <div className="min-w-[140px] flex-1">
                        <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                            <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Status')}</SelectItem>
                                {filterOptions?.statuses.map(s => <SelectItem key={s.value} value={s.value}>{t(s.label)}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="min-w-[150px] flex-1">
                        <Select value={selectedProject} onValueChange={setSelectedProject}>
                            <SelectTrigger><SelectValue placeholder={t('Project')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Projects')}</SelectItem>
                                {filterOptions?.projects.map(p => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('View Report')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                        setDateRange(getDefaultDateRange());
                                        setSelectedStatus('all'); setSelectedProject('all');
                                        const dr = getDefaultDateRange(); const [cs, ce] = dr.split(' - ');
                                        axios.post(route('smart-reports.project-progress.generate'), { export_type: 'view', start_date: cs, end_date: ce }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                    }}><FilterX className="h-4 w-4" /></Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Clear Filter')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                </div>

                <div className="xl:hidden grid grid-cols-1 gap-2 lg:grid-cols-2">
                    <div className="lg:col-span-2">
                        <DateRangePicker value={dateRange} onChange={setDateRange} placeholder={t('Select date range')} />
                    </div>
                    <div>
                        <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                            <SelectTrigger><SelectValue placeholder={t('Status')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Status')}</SelectItem>
                                {filterOptions?.statuses.map(s => <SelectItem key={s.value} value={s.value}>{t(s.label)}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Select value={selectedProject} onValueChange={setSelectedProject}>
                            <SelectTrigger><SelectValue placeholder={t('Project')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Projects')}</SelectItem>
                                {filterOptions?.projects.map(p => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="flex items-center justify-end gap-2 lg:col-span-2">
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('View Report')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" variant="outline" className="text-muted-foreground" onClick={() => {
                                        setDateRange(getDefaultDateRange());
                                        setSelectedStatus('all'); setSelectedProject('all');
                                        const dr2 = getDefaultDateRange(); const [ms, me] = dr2.split(' - ');
                                        axios.post(route('smart-reports.project-progress.generate'), { export_type: 'view', start_date: ms, end_date: me }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
                                    }}><FilterX className="h-4 w-4" /></Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Clear Filter')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                </div>
            </CardContent>
        </Card>
    );

    const pageActions = (
        <div className="flex items-center gap-2">
            <ExportButton
                data={reportData || []}
                columns={[
                    { key: 'project_name', header: t('Project') },
                    { key: 'status', header: t('Status'), type: 'status' as const },
                ]}
                filename="project-progress-report"
                title={t('Project Progress Report')}
                groupedConfig={{
                    sheetName: 'Project Progress',
                    groupKey: (r) => String(r.id),
                    summaryColumns: [
                        { key: 'project_name',          header: t('Project') },
                        { key: 'status',                header: t('Status'),           type: 'status' },
                        { key: 'health_status',         header: t('Health'),            type: 'status' },
                        { key: 'progress_percentage',   header: t('Progress %'),        type: 'number' },
                        { key: 'total_tasks',           header: t('Total Tasks'),       type: 'number' },
                        { key: 'completed_tasks',       header: t('Completed Tasks'),   type: 'number' },
                        { key: 'pending_tasks',         header: t('Pending Tasks'),     type: 'number' },
                        { key: 'total_milestones',      header: t('Milestones'),        type: 'number' },
                        { key: 'completed_milestones',  header: t('Milestones Done'),   type: 'number' },
                        { key: 'budget',                header: t('Budget'),            type: 'currency' },
                        { key: 'milestone_cost',        header: t('Milestone Cost'),    type: 'currency' },
                        { key: 'start_date',            header: t('Start Date'),        type: 'date' },
                        { key: 'end_date',              header: t('End Date'),          type: 'date' },
                    ],
                    detailColumns: [],
                    summaryBuilder: (rows) => {
                        const r = rows[0];
                        return {
                            project_name:         r.project_name,
                            status:               r.status,
                            health_status:        r.health_status,
                            progress_percentage:  parseFloat(r.progress_percentage) || 0,
                            total_tasks:          r.total_tasks,
                            completed_tasks:      r.completed_tasks,
                            pending_tasks:        r.pending_tasks,
                            total_milestones:     r.total_milestones,
                            completed_milestones: r.completed_milestones,
                            budget:               r.budget,
                            milestone_cost:       r.milestone_cost,
                            days_remaining:       r.days_remaining,
                            start_date:           r.start_date,
                            end_date:             r.end_date,
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

    const activeProject = selectedChartProject || (chartData.length > 0 ? chartData[0].record : null);

    // ── Milestones Modal ──────────────────────────────────────────────────
    const milestonesModal = (
        <Dialog open={showMilestonesModal} onOpenChange={setShowMilestonesModal}>
            <DialogContent className="max-w-3xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-lg">
                        <Milestone className="h-5 w-5 text-amber-500" />
                        {t('Milestones')} — {selectedChartProject?.project_name || ''}
                    </DialogTitle>
                    <DialogClose />
                </DialogHeader>
                {milestones.length === 0 ? (
                    <div className="py-12 text-center text-muted-foreground">{t('No milestones found for this project.')}</div>
                ) : (
                    <div className="space-y-3">
                        {milestones.map(m => (
                            <div key={m.id} className="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div className="flex items-center justify-between mb-2">
                                    <h4 className="font-semibold text-gray-900">{m.title}</h4>
                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${m.status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}`}>
                                        {m.status}
                                    </span>
                                </div>
                                {m.summary && <p className="text-xs text-muted-foreground mb-2">{m.summary}</p>}
                                <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                    <span className="flex items-center gap-1">
                                        <CalendarDays className="h-3.5 w-3.5" />
                                        {formatDate(m.start_date)} → {formatDate(m.end_date)}
                                    </span>
                                    <span className="flex items-center gap-1">
                                        <Flag className="h-3.5 w-3.5" />
                                        {t('Cost')}: {formatCurrency(m.cost)}
                                    </span>
                                    <span className="flex items-center gap-1">
                                        <TrendingUp className="h-3.5 w-3.5" />
                                        {t('Progress')}: {m.progress}%
                                    </span>
                                </div>
                                <div className="w-full h-1.5 bg-gray-200 rounded-full mt-2 overflow-hidden">
                                    <div className="h-full bg-amber-500 rounded-full" style={{ width: `${Math.min(m.progress, 100)}%` }} />
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );

    // ── Tasks Modal ───────────────────────────────────────────────────────
    const tasksModal = (
        <Dialog open={showTasksModal} onOpenChange={setShowTasksModal}>
            <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-lg">
                        <ListTodo className="h-5 w-5 text-indigo-500" />
                        {t('Tasks')} — {selectedChartProject?.project_name || ''}
                    </DialogTitle>
                    <DialogClose />
                </DialogHeader>
                {tasks.length === 0 ? (
                    <div className="py-12 text-center text-muted-foreground">{t('No tasks found for this project.')}</div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 sticky top-0">
                                <tr>
                                    <th className="px-3 py-2 text-left font-medium">{t('Title')}</th>
                                    <th className="px-3 py-2 text-left font-medium">{t('Assigned To')}</th>
                                    <th className="px-3 py-2 text-center font-medium">{t('Stage')}</th>
                                    <th className="px-3 py-2 text-center font-medium">{t('Priority')}</th>
                                    <th className="px-3 py-2 text-center font-medium">{t('Status')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {tasks.map(task => (
                                    <tr key={task.id} className="hover:bg-muted/40 transition-colors">
                                        <td className="px-3 py-2.5">
                                            <div className="font-medium text-gray-900">{task.title}</div>
                                            {task.description && (
                                                <div className="text-xs text-muted-foreground line-clamp-1">{task.description}</div>
                                            )}
                                        </td>
                                        <td className="px-3 py-2.5">
                                            {task.assigned_to_names ? (
                                                <span className="flex items-center gap-1 text-xs text-gray-700">
                                                    <User className="h-3 w-3 text-muted-foreground shrink-0" />
                                                    {task.assigned_to_names}
                                                </span>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">—</span>
                                            )}
                                        </td>
                                        <td className="px-3 py-2.5 text-center">
                                            <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                style={{
                                                    backgroundColor: task.stage_color ? `${task.stage_color}20` : '#f3f4f6',
                                                    color: task.stage_color || '#6b7280',
                                                    borderColor: task.stage_color || '#d1d5db',
                                                    borderWidth: '1px',
                                                }}
                                            >
                                                {task.stage_name}
                                            </span>
                                        </td>
                                        <td className="px-3 py-2.5 text-center">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border ${priorityColor(task.priority)}`}>
                                                {task.priority}
                                            </span>
                                        </td>
                                        <td className="px-3 py-2.5 text-center">
                                            {task.is_complete ? (
                                                <span className="inline-flex items-center gap-1 text-xs font-medium text-green-600">
                                                    <CheckCircle2 className="h-3.5 w-3.5" /> {t('Done')}
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-600">
                                                    <Clock className="h-3.5 w-3.5" /> {t('Pending')}
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );

    if (!reportData || reportData.length === 0) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Project Progress Report') }]}
                pageTitle={t('Project Progress Report')}
                pageActions={pageActions}
            >
                <Head title={t('Project Progress Report')} />
                <div className="space-y-6">
                    {filtersPanel}
                    {reportData !== null && reportData.length === 0 && (
                        <Card>
                            <CardContent className="text-center py-12">
                                <FolderKanban className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <p className="text-lg font-medium text-muted-foreground">{t('No projects found for the selected filters.')}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
                {milestonesModal}
                {tasksModal}
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Project Progress Report') }]}
            pageTitle={t('Project Progress Report')}
            pageActions={pageActions}
        >
            <Head title={t('Project Progress Report')} />
            <div className="space-y-6">
                {filtersPanel}

                {/* Summary Cards + View Toggle */}
                 {summary && (
                    <div className="flex flex-col gap-3 xl:flex-row xl:items-stretch">
                        <div className="grid flex-1 grid-cols-1 gap-3 min-w-0 sm:grid-cols-2 xl:grid-cols-4">
                            <Card className="bg-indigo-50/50 border-indigo-200 min-w-0">
                                <CardContent className="px-4 py-3">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <FolderKanban className="h-4 w-4 text-indigo-600" />
                                            <span className="text-sm font-medium text-indigo-700">{t('Total Projects')}</span>
                                        </div>
                                        <div className="text-2xl font-bold text-indigo-900">{summary.total_projects}</div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="bg-green-50/50 border-green-200 min-w-0">
                                <CardContent className="px-4 py-3">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <Target className="h-4 w-4 text-green-600" />
                                            <span className="text-sm font-medium text-green-700">{t('Avg Progress')}</span>
                                        </div>
                                        <div className="text-right">
                                            <div className="text-xl font-bold text-green-700">{summary.avg_progress}%</div>
                                            <div className="text-xs text-green-600">{t('Overall')}: {summary.overall_completion}%</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="bg-amber-50/50 border-amber-200 min-w-0">
                                <CardContent className="px-4 py-3">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <CheckCircle2 className="h-4 w-4 text-amber-600" />
                                            <span className="text-sm font-medium text-amber-700">{t('Tasks')}</span>
                                        </div>
                                        <div className="text-right">
                                            <div className="text-xl font-bold text-amber-700">{summary.completed_tasks}/{summary.total_tasks}</div>
                                            <div className="text-xs text-amber-600">{t('Completed')}</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="bg-fuchsia-50/50 border-fuchsia-200 min-w-0">
                                <CardContent className="px-4 py-3">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <TrendingUp className="h-4 w-4 text-fuchsia-600" />
                                            <span className="text-sm font-medium text-fuchsia-700">{t('Budget')}</span>
                                        </div>
                                        <div className="text-right">
                                            <div className="text-lg font-bold text-fuchsia-700">{formatCurrency(summary.total_budget)}</div>
                                            <div className="text-xs text-fuchsia-600">{t('Cost')}: {formatCurrency(summary.total_milestone_cost)}</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div className="flex items-center justify-end shrink-0">
                            <div className="flex rounded-lg border">
                                <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-r-none">
                                    <BarChart3 className="h-4 w-4" />
                                </Button>
                                <Button variant={viewMode === 'table' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('table')} className="rounded-l-none">
                                    <List className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* CHART VIEW */}
                {viewMode === 'chart' && (() => {
                    const needsScroll = chartData.length > 5;
                    const chartWidth = needsScroll ? chartData.length * 130 : undefined;
                    const chartHeight = 320;

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
                                <text x={0} y={0} dy={14} textAnchor="middle" fontSize={11} fill="#666">{line1}</text>
                                {line2 && <text x={0} y={0} dy={28} textAnchor="middle" fontSize={11} fill="#666">{line2}</text>}
                            </g>
                        );
                    };

                    const tasksBar = (
                        <BarChart width={chartWidth} height={chartHeight} data={chartData}
                            barCategoryGap="28%" margin={{ top: 10, right: 10, left: 0, bottom: 10 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                            <XAxis dataKey="name" height={60} interval={0} tick={<WrappedXAxisTick />} tickLine={false} onClick={handleXAxisClick} />
                            <YAxis allowDecimals={false} tick={{ fontSize: 11 }} width={30} />
                            <RechartsTooltip
                                labelFormatter={(_: any, p: any) => p?.[0]?.payload?.fullName || ''}
                                contentStyle={{ borderRadius: '8px', fontSize: '12px' }}
                                formatter={(value: any, name: string) => [value, name]}
                                cursor={{ fill: 'rgba(99, 102, 241, 0.08)' }}
                            />
                            <Bar dataKey={t('Completed Tasks')} fill="#22c55e" radius={[4, 4, 0, 0]} onClick={handleBarClick} cursor="pointer" />
                            <Bar dataKey={t('Pending Tasks')} fill="#f59e0b" radius={[4, 4, 0, 0]} onClick={handleBarClick} cursor="pointer" />
                            <Bar dataKey={t('Milestones Done')} fill="#6366f1" radius={[4, 4, 0, 0]} onClick={handleBarClick} cursor="pointer" />
                        </BarChart>
                    );

                    return (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base flex items-center gap-2">
                                        <BarChart3 className="h-5 w-5 text-black-500" />
                                        {t('Tasks & Milestones by Project')}
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
                                                    <p>{t('Click on any project bar to view its details on the right panel.')}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="pb-2">
                                    {needsScroll
                                        ? <div className="overflow-x-auto"><div style={{ width: chartWidth }}>{tasksBar}</div></div>
                                        : <ResponsiveContainer width="100%" height={chartHeight}>{tasksBar}</ResponsiveContainer>}
                                    <div className="flex items-center justify-center gap-6 pt-2 pb-1">
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#22c55e]"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Completed Tasks')}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#f59e0b]"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Pending Tasks')}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#6366f1]"></span>
                                            <span className="text-sm font-medium text-muted-foreground">{t('Milestones Done')}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {activeProject && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base flex items-center gap-2">
                                        <FolderKanban className="h-5 w-5 text-black-500" />
                                        {t('Selected Project Details')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between border-b pb-3">
                                        <div>
                                            <h3 className="text-lg font-semibold text-gray-900">{activeProject.project_name}</h3>
                                            <p className="text-xs text-muted-foreground mt-1">
                                                {formatDate(activeProject.start_date)} → {formatDate(activeProject.end_date)}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusBadge(activeProject.status)}`}>
                                                {t(activeProject.status)}
                                            </span>
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${healthBadge(activeProject.health_status)}`}>
                                                {t(activeProject.health_status)}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        {/* Row 1: Progress + Budget side by side */}
                                        <div className="bg-green-50/50 rounded-lg p-3 border border-green-200">
                                            <p className="text-xs text-green-600 font-medium">{t('Progress')}</p>
                                            <p className="text-xl font-bold text-green-700">{parseFloat(activeProject.progress_percentage as any) || 0}%</p>
                                            <div className="w-full h-1.5 bg-green-200 rounded-full mt-1 overflow-hidden">
                                                <div className="h-full bg-green-500 rounded-full" style={{ width: `${Math.min(parseFloat(activeProject.progress_percentage as any) || 0, 100)}%` }} />
                                            </div>
                                        </div>
                                        <div className="bg-fuchsia-50/50 rounded-lg p-3 border border-fuchsia-200">
                                            <p className="text-xs text-fuchsia-600 font-medium">{t('Budget')}</p>
                                            <p className="text-xl font-bold text-fuchsia-700">{formatCurrency(activeProject.budget)}</p>
                                            <p className={`text-xs font-semibold ${(activeProject.budget - activeProject.milestone_cost) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                {t('Variance')}: {formatCurrency(activeProject.budget - activeProject.milestone_cost)}
                                            </p>
                                        </div>
                                        {/* Row 2: Tasks + Milestones side by side */}
                                        <div className="bg-indigo-50/50 rounded-lg p-3 border border-indigo-200 cursor-pointer hover:bg-indigo-100/50 transition-colors" onClick={() => handleOpenTasks(activeProject)}>
                                            <p className="text-xs text-indigo-600 font-medium">{t('Tasks')}</p>
                                            <p className="text-xl font-bold text-indigo-700">
                                                <span className="text-indigo-600">{activeProject.completed_tasks}</span>
                                                <span className="text-indigo-400">/{activeProject.total_tasks}</span>
                                            </p>
                                            <p className="text-xs text-indigo-500">{activeProject.pending_tasks} {t('pending')}</p>
                                        </div>
                                        <div className="bg-amber-50/50 rounded-lg p-3 border border-amber-200 cursor-pointer hover:bg-amber-100/50 transition-colors" onClick={() => handleOpenMilestones(activeProject)}>
                                            <p className="text-xs text-amber-600 font-medium">{t('Milestones')}</p>
                                            <p className="text-xl font-bold text-amber-700">
                                                <span className="text-amber-600">{activeProject.completed_milestones}</span>
                                                <span className="text-amber-400">/{activeProject.total_milestones}</span>
                                            </p>
                                            <p className="text-xs text-amber-500">{t('Cost')}: {formatCurrency(activeProject.milestone_cost)}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between text-xs text-muted-foreground border-t pt-3">
                                        <span className="flex items-center gap-1">
                                            <CheckCircle2 className="h-3.5 w-3.5 text-green-500" />
                                            {t('Days remaining')}: <span className={`font-semibold ${activeProject.days_remaining < 0 ? 'text-red-600' : activeProject.days_remaining <= 7 ? 'text-amber-600' : 'text-green-600'}`}>
                                                {activeProject.days_remaining < 0
                                                    ? `${Math.abs(activeProject.days_remaining)}d ${t('overdue')}`
                                                    : `${activeProject.days_remaining}d`}
                                            </span>
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                            )}
                        </div>
                    );
                })()}

                {/* TABLE VIEW */}
                {viewMode === 'table' && (() => {
                    const totalPages = Math.ceil(filtered.length / perPage);
                    const paginatedData = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);
                    return (
                    <Card>
                        <CardContent className="p-4 border-b bg-gray-50/50">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex-1 max-w-md relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input placeholder={t('Search projects...')} value={searchQuery} onChange={(e: React.ChangeEvent<HTMLInputElement>) => { setSearchQuery(e.target.value); setCurrentPage(1); }} className="pl-9" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <PerPageSelector
                                        routeName="smart-reports.project-progress.index"
                                        defaultValue={perPage.toString()}
                                        onPageChange={(val: any) => { setPerPage(Number(val)); setCurrentPage(1); }}
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-muted/50 sticky top-0">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium">{t('Project')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Status')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Health')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Progress')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Tasks')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Milestones')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Budget')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Milestone Cost')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Variance')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Days Left')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {paginatedData.length === 0 ? (
                                        <tr>
                                            <td colSpan={10} className="px-4 py-12 text-center text-muted-foreground">
                                                <FolderKanban className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                <p>{t('No records found')}</p>
                                            </td>
                                        </tr>
                                    ) : paginatedData.map(project => (
                                        <tr key={project.id} className="hover:bg-muted/40 transition-colors">
                                            <td className="px-4 py-3">
                                                <div className="font-semibold text-gray-900">{project.project_name}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {formatDate(project.start_date)} → {formatDate(project.end_date)}
                                                </div>
                                                {project.description && (
                                                    <div className="text-xs text-muted-foreground mt-0.5 line-clamp-1">{project.description}</div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusBadge(project.status)}`}>
                                                    {t(project.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${healthBadge(project.health_status)}`}>
                                                    {t(project.health_status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex flex-col gap-1 min-w-[80px]">
                                                    <div className="flex items-center justify-between">
                                                        <span className="text-xs font-semibold text-primary">{parseFloat(project.progress_percentage as any) || 0}%</span>
                                                    </div>
                                                    <div className="w-full bg-gray-100 rounded-full h-1.5">
                                                        <div
                                                            className="bg-emerald-500 h-1.5 rounded-full transition-all duration-300"
                                                            style={{ width: `${Math.min(parseFloat(project.progress_percentage as any) || 0, 100)}%` }}
                                                        />
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="text-xs">
                                                    <span className="font-semibold text-green-600">{project.completed_tasks}</span>
                                                    <span className="text-muted-foreground">/{project.total_tasks}</span>
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <span className="text-xs">
                                                    <span className="font-semibold text-amber-600">{project.completed_milestones}</span>
                                                    <span className="text-muted-foreground">/{project.total_milestones}</span>
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right font-medium text-xs">{formatCurrency(project.budget)}</td>
                                            <td className="px-4 py-3 text-right text-xs font-medium">{formatCurrency(project.milestone_cost)}</td>
                                            <td className="px-4 py-3 text-right text-xs font-semibold">
                                                <span className={(project.budget - project.milestone_cost) >= 0 ? 'text-green-600' : 'text-red-600'}>
                                                    {formatCurrency(project.budget - project.milestone_cost)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <span className={`text-xs font-semibold ${project.days_remaining < 0 ? 'text-red-600' :
                                                        project.days_remaining <= 7 ? 'text-amber-600' : 'text-green-600'
                                                    }`}>
                                                    {project.days_remaining < 0
                                                        ? `${Math.abs(project.days_remaining)}d ${t('overdue')}`
                                                        : `${project.days_remaining}d`}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                            <Pagination
                                data={{ current_page: currentPage, last_page: totalPages, per_page: perPage, total: filtered.length, from: ((currentPage - 1) * perPage) + 1, to: Math.min(currentPage * perPage, filtered.length) }}
                                onPageChange={(page: any) => setCurrentPage(page)}
                            />
                        </CardContent>
                    </Card>
                    );
                })()}
            </div>
            {milestonesModal}
            {tasksModal}
        </AuthenticatedLayout>
    );
};

export default ProjectProgressReport;