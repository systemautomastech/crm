import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PieChart } from '@/components/charts/PieChart';
import { BarChart } from '@/components/charts/BarChart';
import { formatCurrency, formatDate, getImagePath } from '@/utils/helpers';
import { Tooltip as UITooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
    CalendarDays, Users, CheckCircle, AlertTriangle,
    BarChart3, PieChart as PieChartIcon, Bug, DollarSign,
    Clock, TrendingUp, Target, ListChecks, Receipt,
} from 'lucide-react';

// ── Types ──────────────────────────────────────────────────────────────────────

interface ProjectReportViewProps {
    project: {
        id: number;
        name: string;
        description?: string;
        start_date?: string;
        end_date?: string;
        status: string;
        budget?: number;
        is_overdue?: boolean;
    };
    taskStatusData:   Array<{ name: string; value: number; color?: string }>;
    taskPriorityData: Array<{ name: string; value: number }>;
    bugStatusData:    Array<{ name: string; value: number; color?: string }>;
    bugPriorityData:  Array<{ name: string; value: number }>;
    projectStats: {
        total_tasks: number;
        completed_tasks: number;
        in_progress_tasks: number;
        team_members: number;
        total_bugs: number;
        resolved_bugs: number;
        open_bugs: number;
        overdue_tasks: number;
        total_milestones: number;
        complete_milestones: number;
        days_left: number | null;
    };
    usersData: Array<{
        id: number;
        name: string;
        assigned_tasks: number;
        done_tasks: number;
        assigned_bugs: number;
        resolved_bugs: number;
    }>;
    milestonesData: Array<{
        id: number;
        name: string;
        progress: number;
        cost: number;
        amount_billed: number;
        status: string;
        start_date?: string;
        end_date?: string;
        is_overdue: boolean;
    }>;
    tasksData: Array<{
        id: number;
        title: string;
        priority: string;
        stage: string;
        stage_color: string;
        is_complete: boolean;
        assigned_users: Array<{ id: number; name: string; avatar?: string }>;
        start_date?: string;
        end_date?: string;
        is_overdue: boolean;
        subtask_total: number;
        subtask_done: number;
        subtask_pct: number | null;
    }>;
    bugsData: Array<{
        id: number;
        title: string;
        priority: string;
        stage: string;
        stage_color: string;
        is_resolved: boolean;
        assigned_users: Array<{ id: number; name: string; avatar?: string }>;
        created_at: string;
    }>;
    financialData: {
        budget: number;
        milestone_cost_total: number;
        total_invoiced: number;
        total_collected: number;
        outstanding: number;
        payment_list: Array<{
            id: number;
            number: string;
            date?: string;
            due_date?: string;
            total_amount: number;
            status: string;
            is_overdue: boolean;
        }>;
    };
}

// ── Helpers ────────────────────────────────────────────────────────────────────

const priorityColor = (p: string) =>
    p === 'High' ? 'bg-red-100 text-red-700' :
    p === 'Medium' ? 'bg-amber-100 text-amber-700' :
    'bg-green-100 text-green-700';

const AvatarStack = ({ users }: { users: Array<{ id: number; name: string; avatar?: string }> }) => {
    if (!users || users.length === 0) return <span className="text-xs text-gray-400">—</span>;
    const visible = users.slice(0, 4);
    const extra   = users.length - 4;
    return (
        <div className="flex -space-x-1.5">
            {visible.map((u) => (
                <TooltipProvider key={u.id}>
                    <UITooltip delayDuration={0}>
                        <TooltipTrigger>
                            <div className="h-7 w-7 rounded-full border-2 border-white overflow-hidden shadow-sm">
                                <img
                                    src={u.avatar ? getImagePath(u.avatar) : getImagePath('avatar.png')}
                                    alt={u.name}
                                    className="h-full w-full object-cover"
                                />
                            </div>
                        </TooltipTrigger>
                        <TooltipContent><p>{u.name}</p></TooltipContent>
                    </UITooltip>
                </TooltipProvider>
            ))}
            {extra > 0 && (
                <div className="h-7 w-7 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center shadow-sm">
                    <span className="text-xs text-gray-600 font-semibold">+{extra}</span>
                </div>
            )}
        </div>
    );
};

const progressBar = (pct: number, color = 'bg-blue-500') => (
    <div className="flex items-center gap-2">
        <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div className={`h-full rounded-full ${color}`} style={{ width: `${pct}%` }} />
        </div>
        <span className="text-xs font-semibold w-9 text-right">{pct}%</span>
    </div>
);

// ── Main Component ─────────────────────────────────────────────────────────────

export default function View() {
    const { t } = useTranslation();
    const {
        project, taskStatusData, taskPriorityData, bugStatusData, bugPriorityData,
        projectStats, usersData, milestonesData, tasksData, bugsData, financialData,
    } = usePage<ProjectReportViewProps>().props;

    const taskCompletionPct = projectStats.total_tasks > 0
        ? Math.round((projectStats.completed_tasks / projectStats.total_tasks) * 100) : 0;
    const bugResolvePct = projectStats.total_bugs > 0
        ? Math.round((projectStats.resolved_bugs / projectStats.total_bugs) * 100) : 0;
    const milestonePct = projectStats.total_milestones > 0
        ? Math.round((projectStats.complete_milestones / projectStats.total_milestones) * 100) : 0;
    const budgetUsedPct = financialData.budget > 0
        ? Math.min(100, Math.round((financialData.total_collected / financialData.budget) * 100)) : 0;

    const daysLeftLabel = projectStats.days_left === null ? '-'
        : projectStats.days_left < 0 ? `${Math.abs(projectStats.days_left)}d overdue`
        : `${projectStats.days_left}d left`;

    // ── KPI Strip ──────────────────────────────────────────────────────────────

    const KpiCard = ({ icon: Icon, label, value, sub, accent = 'blue' }: {
        icon: any; label: string; value: string | number; sub?: string; accent?: string;
    }) => {
        const colors: Record<string, string> = {
            blue:   'bg-blue-50 text-blue-600',
            green:  'bg-emerald-50 text-emerald-600',
            amber:  'bg-amber-50 text-amber-600',
            red:    'bg-red-50 text-red-600',
            purple: 'bg-purple-50 text-purple-600',
            sky:    'bg-sky-50 text-sky-600',
        };
        return (
            <Card className="shadow-sm">
                <CardContent className="p-4 flex items-center gap-3">
                    <div className={`p-2.5 rounded-xl ${colors[accent]}`}>
                        <Icon className="h-5 w-5" />
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 font-medium">{label}</p>
                        <p className="text-lg font-bold text-gray-800 leading-tight">{value}</p>
                        {sub && <p className="text-xs text-gray-400">{sub}</p>}
                    </div>
                </CardContent>
            </Card>
        );
    };

    // ── Shared empty state ─────────────────────────────────────────────────────

    const EmptyState = ({ icon: Icon, text }: { icon: any; text: string }) => (
        <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <Icon className="h-10 w-10 mb-2 opacity-30" />
            <p className="text-sm">{text}</p>
        </div>
    );

    // ── Render ─────────────────────────────────────────────────────────────────

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Project Report'), url: route('project.report.index') },
                { label: project.name },
            ]}
            pageTitle={`${t('Project Report')} - ${project.name}`}
        >
            <Head title={`${t('Project Report')} - ${project.name}`} />

            <div className="space-y-5">

                {/* ── Project header ──────────────────────────────────── */}
                <Card className="shadow-sm">
                    <CardContent className="p-5">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            {/* Left: name + description */}
                            <div className="flex-1 min-w-0">
                                <div className="flex items-center gap-2 flex-wrap">
                                    <h2 className="text-xl font-bold text-gray-900">{project.name}</h2>
                                    {/* Status badge */}
                                    <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                        project.status === 'Finished' ? 'bg-emerald-100 text-emerald-700' :
                                        project.status === 'Ongoing'  ? 'bg-blue-100 text-blue-700' :
                                        'bg-amber-100 text-amber-700'
                                    }`}>
                                        {t(project.status)}
                                    </span>
                                    {/* Overdue badge */}
                                    {project.is_overdue && (
                                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center gap-1">
                                            <AlertTriangle className="h-3 w-3" />
                                            {t('Overdue')}
                                        </span>
                                    )}
                                </div>
                                {project.description && (
                                    <p className="text-sm text-gray-500 mt-1 line-clamp-2">{project.description}</p>
                                )}
                            </div>

                            {/* Right: meta info */}
                            <div className="flex items-center gap-4 flex-wrap text-sm text-gray-600">
                                {project.start_date && (
                                    <div className="flex items-center gap-1.5">
                                        <CalendarDays className="h-4 w-4 text-gray-400 shrink-0" />
                                        <span>
                                            {formatDate(project.start_date)}
                                            <span className="mx-1 text-gray-400">→</span>
                                            <span className={project.is_overdue ? 'text-red-600 font-semibold' : ''}>
                                                {project.end_date ? formatDate(project.end_date) : '—'}
                                            </span>
                                        </span>
                                    </div>
                                )}
                                {project.budget && (
                                    <div className="flex items-center gap-1.5">
                                        <DollarSign className="h-4 w-4 text-gray-400 shrink-0" />
                                        <span className="font-medium">{formatCurrency(project.budget)}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* ── 6 KPI Cards ─────────────────────────────────────── */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <KpiCard icon={CheckCircle}    label={t('Tasks Done')}          value={`${taskCompletionPct}%`}    sub={`${projectStats.completed_tasks}/${projectStats.total_tasks}`}       accent="green"  />
                    <KpiCard icon={Bug}            label={t('Bugs Resolved')}       value={`${bugResolvePct}%`}        sub={`${projectStats.resolved_bugs}/${projectStats.total_bugs}`}          accent="red"    />
                    <KpiCard icon={Target}         label={t('Milestones')}           value={`${milestonePct}%`}         sub={`${projectStats.complete_milestones}/${projectStats.total_milestones}`} accent="purple" />
                    <KpiCard icon={AlertTriangle}  label={t('Overdue Tasks')}        value={projectStats.overdue_tasks} accent={projectStats.overdue_tasks > 0 ? 'red' : 'green'} />
                    <KpiCard icon={Users}          label={t('Team Members')}         value={projectStats.team_members}  accent="sky"    />
                    <KpiCard icon={Clock}          label={t('Timeline')}             value={daysLeftLabel}              accent={projectStats.days_left !== null && projectStats.days_left < 0 ? 'red' : 'blue'} />
                </div>

                {/* ── Tabs ────────────────────────────────────────────── */}
                <Tabs defaultValue="overview" className="w-full">
                    <TabsList className="mb-3 w-full justify-start overflow-x-auto overflow-y-hidden h-auto p-1">
                        <TabsTrigger value="overview"  className="flex items-center gap-1.5 whitespace-nowrap flex-shrink-0"><BarChart3 className="h-3.5 w-3.5" />{t('Overview')}</TabsTrigger>
                        <TabsTrigger value="tasks"     className="flex items-center gap-1.5 whitespace-nowrap flex-shrink-0"><ListChecks className="h-3.5 w-3.5" />{t('Tasks')} <span className="ml-1 bg-gray-200 text-gray-700 rounded-full px-1.5 py-0 text-xs font-bold">{projectStats.total_tasks}</span></TabsTrigger>
                        <TabsTrigger value="bugs"      className="flex items-center gap-1.5 whitespace-nowrap flex-shrink-0"><Bug className="h-3.5 w-3.5" />{t('Bugs')} <span className="ml-1 bg-gray-200 text-gray-700 rounded-full px-1.5 py-0 text-xs font-bold">{projectStats.total_bugs}</span></TabsTrigger>
                        <TabsTrigger value="financial" className="flex items-center gap-1.5 whitespace-nowrap flex-shrink-0"><DollarSign className="h-3.5 w-3.5" />{t('Financial')}</TabsTrigger>
                        <TabsTrigger value="team"      className="flex items-center gap-1.5 whitespace-nowrap flex-shrink-0"><Users className="h-3.5 w-3.5" />{t('Team & Milestones')}</TabsTrigger>
                    </TabsList>

                    {/* ── TAB: Overview ──────────────────────────────── */}
                    <TabsContent value="overview" className="mt-4">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            {/* Task Status Pie */}
                            <Card className="shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <PieChartIcon className="h-4 w-4" />
                                        {t('Task Status Distribution')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {taskStatusData.length > 0 ? (
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <PieChart
                                                    data={taskStatusData}
                                                    dataKey="value"
                                                    nameKey="name"
                                                    height={300}
                                                    outerRadius={110}
                                                    showLabels={false}
                                                    showLegend={false}
                                                    showTooltip={true}
                                                    colors={taskStatusData.map(i => i.color)}
                                                />
                                            </div>
                                            <div className="w-40 space-y-2 mt-6">
                                                {taskStatusData.map((item, idx) => (
                                                    <div key={idx} className="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg">
                                                        <div className="flex items-center gap-2">
                                                            <div className="w-3 h-3 rounded-full" style={{ backgroundColor: item.color || '#6b7280' }} />
                                                            <span className="text-xs font-medium">{item.name}</span>
                                                        </div>
                                                        <span className="text-sm font-bold">{item.value}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ) : (
                                        <EmptyState icon={PieChartIcon} text={t('No task data available')} />
                                    )}
                                </CardContent>
                            </Card>

                            {/* Task Priority Bar */}
                            <Card className="shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <BarChart3 className="h-4 w-4" />
                                        {t('Task Priority Distribution')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {taskPriorityData.length > 0 ? (
                                        <BarChart
                                            data={taskPriorityData}
                                            dataKey="value"
                                            xAxisKey="name"
                                            color="#3b82f6"
                                            height={300}
                                            showGrid={true}
                                            showTooltip={true}
                                        />
                                    ) : (
                                        <EmptyState icon={BarChart3} text={t('No priority data available')} />
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* ── TAB: Tasks ─────────────────────────────────── */}
                    <TabsContent value="tasks" className="mt-4">
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <ListChecks className="h-4 w-4" />
                                    {t('Task List')}
                                    <span className="ml-auto text-sm font-normal text-gray-500">
                                        {projectStats.completed_tasks}/{projectStats.total_tasks} {t('completed')}
                                        {projectStats.overdue_tasks > 0 && (
                                            <span className="ml-2 text-red-600 font-semibold">· {projectStats.overdue_tasks} {t('overdue')}</span>
                                        )}
                                    </span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {tasksData.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="border-b bg-gray-50">
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Title')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Priority')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Stage')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Assigned To')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Start Date')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('End Date')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Subtasks')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {tasksData.map((task) => (
                                                    <tr key={task.id} className={`border-b last:border-b-0 hover:bg-gray-50 transition-colors ${task.is_overdue ? 'bg-red-50/40' : ''}`}>
                                                        <td className="py-3 px-4">
                                                            <div className="flex items-center gap-2">
                                                                {task.is_overdue && <AlertTriangle className="h-3.5 w-3.5 text-red-500 shrink-0" />}
                                                                <span className={`font-medium ${task.is_complete ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                                                                    {task.title}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${priorityColor(task.priority)}`}>
                                                                {t(task.priority)}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className="flex items-center gap-1.5">
                                                                <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: task.stage_color }} />
                                                                <span className="text-xs text-gray-700">{task.stage}</span>
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <AvatarStack users={task.assigned_users} />
                                                        </td>
                                                        <td className="py-3 px-4 text-xs text-gray-600">
                                                            {task.start_date ? formatDate(task.start_date) : '—'}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`text-xs font-medium ${task.is_overdue ? 'text-red-600' : 'text-gray-600'}`}>
                                                                {task.end_date ? formatDate(task.end_date) : '—'}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            {task.subtask_total > 0 ? (
                                                                <div className="space-y-1 min-w-[80px]">
                                                                    {progressBar(task.subtask_pct ?? 0, 'bg-blue-500')}
                                                                    <p className="text-xs text-gray-400">{task.subtask_done}/{task.subtask_total}</p>
                                                                </div>
                                                            ) : (
                                                                <span className="text-xs text-gray-400">—</span>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState icon={ListChecks} text={t('No tasks found for this project')} />
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* ── TAB: Bugs ──────────────────────────────────── */}
                    <TabsContent value="bugs" className="mt-4 space-y-5">
                        {/* Bug Charts */}
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <Card className="shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <PieChartIcon className="h-4 w-4" />
                                        {t('Bug Status Distribution')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {bugStatusData.length > 0 ? (
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <PieChart
                                                    data={bugStatusData}
                                                    dataKey="value"
                                                    nameKey="name"
                                                    height={280}
                                                    outerRadius={100}
                                                    showLabels={false}
                                                    showLegend={false}
                                                    showTooltip={true}
                                                    colors={bugStatusData.map(i => i.color)}
                                                />
                                            </div>
                                            <div className="w-40 space-y-2 mt-4">
                                                {bugStatusData.map((item, idx) => (
                                                    <div key={idx} className="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg">
                                                        <div className="flex items-center gap-2">
                                                            <div className="w-3 h-3 rounded-full" style={{ backgroundColor: item.color || '#6b7280' }} />
                                                            <span className="text-xs font-medium">{item.name}</span>
                                                        </div>
                                                        <span className="text-sm font-bold">{item.value}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ) : (
                                        <EmptyState icon={PieChartIcon} text={t('No bug data available')} />
                                    )}
                                </CardContent>
                            </Card>

                            <Card className="shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <BarChart3 className="h-4 w-4" />
                                        {t('Bug Priority Distribution')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {bugPriorityData.length > 0 ? (
                                        <BarChart
                                            data={bugPriorityData}
                                            dataKey="value"
                                            xAxisKey="name"
                                            color="#ef4444"
                                            height={280}
                                            showGrid={true}
                                            showTooltip={true}
                                        />
                                    ) : (
                                        <EmptyState icon={BarChart3} text={t('No priority data available')} />
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Bug List */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Bug className="h-4 w-4" />
                                    {t('Bug List')}
                                    <span className="ml-auto text-sm font-normal text-gray-500">
                                        {projectStats.resolved_bugs}/{projectStats.total_bugs} {t('resolved')}
                                    </span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {bugsData.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="border-b bg-gray-50">
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Title')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Priority')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Stage')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Assigned To')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Created')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Status')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {bugsData.map((bug) => (
                                                    <tr key={bug.id} className="border-b last:border-b-0 hover:bg-gray-50 transition-colors">
                                                        <td className="py-3 px-4">
                                                            <span className={`font-medium ${bug.is_resolved ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                                                                {bug.title}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${priorityColor(bug.priority)}`}>
                                                                {t(bug.priority)}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className="flex items-center gap-1.5">
                                                                <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: bug.stage_color }} />
                                                                <span className="text-xs text-gray-700">{bug.stage}</span>
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <AvatarStack users={bug.assigned_users} />
                                                        </td>
                                                        <td className="py-3 px-4 text-xs text-gray-600">
                                                            {formatDate(bug.created_at)}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${bug.is_resolved ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'}`}>
                                                                {bug.is_resolved ? t('Resolved') : t('Open')}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState icon={Bug} text={t('No bugs found for this project')} />
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* ── TAB: Financial ─────────────────────────────── */}
                    <TabsContent value="financial" className="mt-4 space-y-5">
                        {/* Summary bars */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            {[
                                { label: t('Total Budget'),      value: financialData.budget,               color: 'blue',   icon: DollarSign },
                                { label: t('Milestone Costs'),   value: financialData.milestone_cost_total, color: 'purple', icon: Target },
                                { label: t('Total Invoiced'),    value: financialData.total_invoiced,       color: 'amber',  icon: Receipt },
                                { label: t('Collected (Posted)'),value: financialData.total_collected,      color: 'green',  icon: TrendingUp },
                            ].map(({ label, value, color, icon: Icon }) => (
                                <Card key={label} className="shadow-sm">
                                    <CardContent className="p-4">
                                        <div className="flex items-center gap-2 mb-1">
                                            <Icon className="h-4 w-4 text-gray-400" />
                                            <span className="text-xs text-gray-500 font-medium">{label}</span>
                                        </div>
                                        <p className={`text-xl font-bold ${
                                            color === 'blue'   ? 'text-blue-600'   :
                                            color === 'purple' ? 'text-purple-600' :
                                            color === 'amber'  ? 'text-amber-600'  :
                                                                  'text-emerald-600'
                                        }`}>{formatCurrency(value)}</p>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Budget utilisation bar */}
                        {financialData.budget > 0 && (
                            <Card className="shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-base">{t('Budget Utilisation')}</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {[
                                        { label: t('Milestone Costs vs Budget'),    value: financialData.milestone_cost_total, color: 'bg-purple-500' },
                                        { label: t('Total Invoiced vs Budget'),     value: financialData.total_invoiced,       color: 'bg-amber-500'  },
                                        { label: t('Amount Collected vs Budget'),   value: financialData.total_collected,      color: 'bg-emerald-500'},
                                    ].map(({ label, value, color }) => {
                                        const pct = Math.min(100, financialData.budget > 0 ? Math.round((value / financialData.budget) * 100) : 0);
                                        return (
                                            <div key={label}>
                                                <div className="flex justify-between items-center mb-1">
                                                    <span className="text-sm font-medium text-gray-700">{label}</span>
                                                    <span className="text-sm font-bold text-gray-800">{formatCurrency(value)} <span className="text-gray-400 font-normal">({pct}%)</span></span>
                                                </div>
                                                <div className="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                                    <div className={`h-full rounded-full ${color} transition-all`} style={{ width: `${pct}%` }} />
                                                </div>
                                            </div>
                                        );
                                    })}
                                </CardContent>
                            </Card>
                        )}

                        {/* Outstanding */}
                        {financialData.outstanding > 0 && (
                            <Card className="shadow-sm border-l-4 border-l-amber-400">
                                <CardContent className="p-4 flex items-center gap-3">
                                    <AlertTriangle className="h-5 w-5 text-amber-500" />
                                    <p className="text-sm text-gray-700">
                                        <span className="font-semibold text-amber-700">{formatCurrency(financialData.outstanding)}</span>
                                        {' '}{t('pending (invoiced but not yet posted)')}
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Payment list */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Receipt className="h-4 w-4" />
                                    {t('Payment List')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {financialData.payment_list.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="border-b bg-gray-50">
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Number')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Payment Date')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Due Date')}</th>
                                                    <th className="text-right py-3 px-4 font-semibold text-gray-600">{t('Amount')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Status')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {financialData.payment_list.map((p) => (
                                                    <tr key={p.id} className={`border-b last:border-b-0 hover:bg-gray-50 transition-colors ${p.is_overdue ? 'bg-red-50/40' : ''}`}>
                                                        <td className="py-3 px-4 font-medium text-gray-800">{p.number}</td>
                                                        <td className="py-3 px-4 text-gray-600">{p.date ? formatDate(p.date) : '—'}</td>
                                                        <td className="py-3 px-4">
                                                            <span className={`${p.is_overdue ? 'text-red-600 font-semibold' : 'text-gray-600'}`}>
                                                                {p.due_date ? formatDate(p.due_date) : '—'}
                                                                {p.is_overdue && ' ⚠'}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4 text-right font-semibold text-gray-800">
                                                            {formatCurrency(p.total_amount)}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${
                                                                p.status === 'posted' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'
                                                            }`}>
                                                                {t(p.status.charAt(0).toUpperCase() + p.status.slice(1))}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState icon={Receipt} text={t('No payments recorded for this project')} />
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* ── TAB: Team & Milestones ─────────────────────── */}
                    <TabsContent value="team" className="mt-4 space-y-5">
                        {/* Team Productivity */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Users className="h-4 w-4" />
                                    {t('Team Productivity')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {usersData.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="border-b bg-gray-50">
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Name')}</th>
                                                    <th className="text-center py-3 px-4 font-semibold text-gray-600">{t('Tasks Assigned')}</th>
                                                    <th className="text-center py-3 px-4 font-semibold text-gray-600">{t('Tasks Done')}</th>
                                                    <th className="text-center py-3 px-4 font-semibold text-gray-600">{t('Task Rate')}</th>
                                                    <th className="text-center py-3 px-4 font-semibold text-gray-600">{t('Bugs Assigned')}</th>
                                                    <th className="text-center py-3 px-4 font-semibold text-gray-600">{t('Bugs Resolved')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {usersData.map((user) => {
                                                    const taskRate = user.assigned_tasks > 0
                                                        ? Math.round((user.done_tasks / user.assigned_tasks) * 100) : 0;
                                                    return (
                                                        <tr key={user.id} className="border-b last:border-b-0 hover:bg-gray-50 transition-colors">
                                                            <td className="py-3 px-4">
                                                                <div className="flex items-center gap-2.5">
                                                                    <div className="h-8 w-8 rounded-full border border-gray-200 overflow-hidden shrink-0">
                                                                        <img
                                                                            src={user.avatar ? getImagePath(user.avatar) : getImagePath('avatar.png')}
                                                                            alt={user.name}
                                                                            className="h-full w-full object-cover"
                                                                        />
                                                                    </div>
                                                                    <span className="font-medium text-gray-800">{user.name}</span>
                                                                </div>
                                                            </td>
                                                            <td className="py-3 px-4 text-center text-gray-700">{user.assigned_tasks}</td>
                                                            <td className="py-3 px-4 text-center text-emerald-600 font-semibold">{user.done_tasks}</td>
                                                            <td className="py-3 px-4">
                                                                <div className="space-y-1">
                                                                    {progressBar(taskRate, taskRate === 100 ? 'bg-emerald-500' : 'bg-blue-500')}
                                                                </div>
                                                            </td>
                                                            <td className="py-3 px-4 text-center text-gray-700">{user.assigned_bugs}</td>
                                                            <td className="py-3 px-4 text-center">
                                                                <span className={`font-semibold ${user.resolved_bugs > 0 ? 'text-emerald-600' : 'text-gray-400'}`}>
                                                                    {user.resolved_bugs}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState icon={Users} text={t('No users assigned to this project')} />
                                )}
                            </CardContent>
                        </Card>

                        {/* Milestones */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Target className="h-4 w-4" />
                                    {t('Milestones')}
                                    <span className="ml-auto text-sm font-normal text-gray-500">
                                        {projectStats.complete_milestones}/{projectStats.total_milestones} {t('complete')}
                                    </span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {milestonesData.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead>
                                                <tr className="border-b bg-gray-50">
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Milestone')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Progress')}</th>
                                                    <th className="text-right py-3 px-4 font-semibold text-gray-600">{t('Cost')}</th>
                                                    <th className="text-right py-3 px-4 font-semibold text-gray-600">{t('Billed')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Status')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('Start Date')}</th>
                                                    <th className="text-left py-3 px-4 font-semibold text-gray-600">{t('End Date')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {milestonesData.map((m) => (
                                                    <tr key={m.id} className={`border-b last:border-b-0 hover:bg-gray-50 transition-colors ${m.is_overdue ? 'bg-red-50/40' : ''}`}>
                                                        <td className="py-3 px-4">
                                                            <div className="flex items-center gap-1.5">
                                                                {m.is_overdue && <AlertTriangle className="h-3.5 w-3.5 text-red-500" />}
                                                                <span className="font-medium text-gray-800">{m.name}</span>
                                                            </div>
                                                        </td>
                                                        <td className="py-3 px-4 min-w-[130px]">
                                                            {progressBar(m.progress, m.progress === 100 ? 'bg-emerald-500' : 'bg-blue-500')}
                                                        </td>
                                                        <td className="py-3 px-4 text-right font-medium text-gray-700">{formatCurrency(m.cost)}</td>
                                                        <td className="py-3 px-4 text-right">
                                                            <span className={`font-semibold ${m.amount_billed > 0 ? 'text-emerald-600' : 'text-gray-400'}`}>
                                                                {m.amount_billed > 0 ? formatCurrency(m.amount_billed) : '—'}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${
                                                                m.status === 'Complete'   ? 'bg-emerald-100 text-emerald-700' :
                                                                m.status === 'Incomplete' ? 'bg-gray-100 text-gray-600' :
                                                                'bg-amber-100 text-amber-700'
                                                            }`}>
                                                                {t(m.status)}
                                                            </span>
                                                        </td>
                                                        <td className="py-3 px-4 text-xs text-gray-600">{m.start_date ? formatDate(m.start_date) : '—'}</td>
                                                        <td className="py-3 px-4">
                                                            <span className={`text-xs ${m.is_overdue ? 'text-red-600 font-semibold' : 'text-gray-600'}`}>
                                                                {m.end_date ? formatDate(m.end_date) : '—'}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <EmptyState icon={Target} text={t('No milestones found for this project')} />
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
