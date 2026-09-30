import { useState, useMemo } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { Eye, BarChart3, Calendar, Receipt, Clock, AlertTriangle, TrendingUp, Users } from "lucide-react";
import { Tooltip as UITooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { FilterButton } from '@/components/ui/filter-button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate, formatCurrency, getImagePath } from '@/utils/helpers';

interface ProjectReportItem {
    id: number;
    name: string;
    start_date?: string;
    end_date?: string;
    status: string;
    tasks_count: string;
    bugs_count: string;
    milestones_count: string;
    budget?: string;
    total_payment?: number;
    team_members_count?: number;
    is_overdue?: boolean;
    budget_used_pct?: number;
    team_members?: Array<{ id: number; name: string; avatar?: string }>;
}

interface ProjectReportIndexProps {
    projects: {
        data: ProjectReportItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    stats: {
        total: number;
        ongoing: number;
        onhold: number;
        finished: number;
        overdue: number;
        total_budget: number;
        total_collected: number;
    };
}

export default function Index() {
    const { t } = useTranslation();
    const { projects, stats } = usePage<ProjectReportIndexProps>().props;
    const urlParams = useMemo(() => new URLSearchParams(window.location.search), []);

    const [filters, setFilters] = useState({
        name: urlParams.get('name') || '',
        status: urlParams.get('status') || '',
        date_range: (() => {
            const fromDate = urlParams.get('date_from');
            const toDate   = urlParams.get('date_to');
            return (fromDate && toDate) ? `${fromDate} - ${toDate}` : '';
        })(),
    });
    const [perPage]     = useState(urlParams.get('per_page') || '10');
    const [showFilters, setShowFilters] = useState(false);

    useFlashMessages();

    const handleFilter = () => {
        const filterParams: any = { ...filters };
        if (filters.date_range) {
            const [fromDate, toDate] = filters.date_range.split(' - ');
            filterParams.date_from = fromDate;
            filterParams.date_to   = toDate;
        }
        delete filterParams.date_range;
        router.get(route('project.report.index'), { ...filterParams, per_page: perPage }, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', status: '', date_range: '' });
        router.get(route('project.report.index'), { per_page: perPage });
    };

    const activeFilterCount = [filters.status, filters.date_range].filter(Boolean).length;

    const StatCard = ({ icon: Icon, label, value, accent = 'blue' }: any) => {
        const accents: Record<string, string> = {
            blue:   'bg-blue-50 text-blue-600',
            green:  'bg-emerald-50 text-emerald-600',
            amber:  'bg-amber-50 text-amber-600',
            purple: 'bg-purple-50 text-purple-600',
            red:    'bg-red-50 text-red-600',
            sky:    'bg-sky-50 text-sky-600',
        };
        return (
            <Card className="border shadow-sm">
                <CardContent className="p-3.5 flex items-center gap-3">
                    <div className={`p-2.5 rounded-xl ${accents[accent]}`}>
                        <Icon className="h-5 w-5" />
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 font-medium">{label}</p>
                        <p className="text-sm font-bold text-gray-800">{value}</p>
                    </div>
                </CardContent>
            </Card>
        );
    };

    const ProjectCard = ({ project }: { project: ProjectReportItem }) => {
        const tasksArr      = (project.tasks_count || '0/0').split('/');
        const bugsArr       = (project.bugs_count || '0/0').split('/');
        const milestonesArr = (project.milestones_count || '0/0').split('/');

        const taskPct      = parseInt(tasksArr[1]) ? Math.round((parseInt(tasksArr[0]) / parseInt(tasksArr[1])) * 100) : 0;
        const bugPct       = parseInt(bugsArr[1]) ? Math.round((parseInt(bugsArr[0]) / parseInt(bugsArr[1])) * 100) : 0;
        const milestonePct = parseInt(milestonesArr[1]) ? Math.round((parseInt(milestonesArr[0]) / parseInt(milestonesArr[1])) * 100) : 0;
        const budgetPct    = project.budget_used_pct ?? 0;

        return (
            <Card className="border border-gray-200/80 shadow-md flex flex-col group">
                <div className="p-4 flex-1 flex flex-col">
                    {/* Header */}
                    <div className="flex items-start justify-between gap-2 mb-3">
                        <h3
                            className="font-bold text-base text-gray-900 group-hover:text-blue-700 transition-colors line-clamp-2 cursor-pointer flex-1"
                            onClick={() => router.get(route('project.report.show', project.id))}
                        >
                            {project.name}
                        </h3>
                        {project.is_overdue && (
                            <span className="shrink-0 flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-700 rounded text-xs font-semibold">
                                <AlertTriangle className="h-3 w-3" />
                                {t('Overdue')}
                            </span>
                        )}
                    </div>

                    {/* Dates */}
                    <div className="text-xs space-y-1 mb-3">
                        <div className="flex justify-between">
                            <span className="text-gray-500">{t('Start Date')}:</span>
                            <span className="font-medium text-gray-700">{project.start_date ? formatDate(project.start_date) : '-'}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-gray-500">{t('End Date')}:</span>
                            <span className={`font-medium ${project.is_overdue ? 'text-red-600' : 'text-gray-700'}`}>
                                {project.end_date ? formatDate(project.end_date) : '-'}
                            </span>
                        </div>
                        {/* Team members avatars */}
                        <div className="flex items-center gap-1.5 pt-0.5">
                            <Users className="h-3.5 w-3.5 text-gray-300 shrink-0" />
                            {project.team_members && project.team_members.length > 0 ? (
                                <>
                                    <div className="flex -space-x-1.5">
                                        {project.team_members.slice(0, 5).map((user) => (
                                            <TooltipProvider key={user.id}>
                                                <UITooltip delayDuration={0}>
                                                    <TooltipTrigger>
                                                        <div className="h-6 w-6 rounded-full border-2 border-white overflow-hidden shadow-sm">
                                                            <img
                                                                src={user.avatar ? getImagePath(user.avatar) : getImagePath('avatar.png')}
                                                                alt={user.name}
                                                                className="h-full w-full object-cover"
                                                            />
                                                        </div>
                                                    </TooltipTrigger>
                                                    <TooltipContent><p>{user.name}</p></TooltipContent>
                                                </UITooltip>
                                            </TooltipProvider>
                                        ))}
                                        {project.team_members.length > 5 && (
                                            <div className="h-6 w-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center shadow-sm">
                                                <span className="text-xs text-gray-600 font-semibold">+{project.team_members.length - 5}</span>
                                            </div>
                                        )}
                                    </div>
                                    <span className="text-xs text-gray-400">{project.team_members.length} {t('members')}</span>
                                </>
                            ) : (
                                <span className="text-xs text-gray-400 italic">{t('No members')}</span>
                            )}
                        </div>
                    </div>

                    {/* Progress Bars */}
                    <div className="space-y-2 mb-4">
                        {[
                            { label: t('Tasks'), count: project.tasks_count, pct: taskPct, color: taskPct === 100 ? 'bg-emerald-500' : taskPct >= 75 ? 'bg-blue-500' : 'bg-amber-500' },
                            { label: t('Milestones'), count: project.milestones_count, pct: milestonePct, color: milestonePct === 100 ? 'bg-emerald-500' : 'bg-purple-500' },
                            { label: t('Bugs'), count: project.bugs_count, pct: bugPct, color: bugPct === 100 ? 'bg-emerald-500' : 'bg-red-500' },
                        ].map(({ label, count, pct, color }) => (
                            <div key={label}>
                                <div className="flex justify-between items-center mb-0.5">
                                    <span className="text-xs font-semibold text-gray-700">{label}</span>
                                    <span className="text-xs font-bold text-gray-600">{count} ({pct}%)</span>
                                </div>
                                <div className="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div className={`h-full rounded-full transition-all ${color}`} style={{ width: `${pct}%` }} />
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Financial */}
                    <div className="grid grid-cols-2 gap-2">
                        <div className="bg-blue-50 rounded-lg p-2 text-center">
                            <p className="text-xs text-gray-500">{t('Budget')}</p>
                            <p className="text-sm font-bold text-blue-600">{project.budget ? formatCurrency(project.budget) : '-'}</p>
                            {project.budget && (
                                <div className="mt-1">
                                    <div className="w-full h-1 bg-blue-200 rounded-full overflow-hidden">
                                        <div
                                            className={`h-full rounded-full ${budgetPct >= 90 ? 'bg-red-500' : budgetPct >= 70 ? 'bg-amber-500' : 'bg-blue-500'}`}
                                            style={{ width: `${budgetPct}%` }}
                                        />
                                    </div>
                                    <p className="text-xs text-gray-400 mt-0.5">{budgetPct}% {t('used')}</p>
                                </div>
                            )}
                        </div>
                        <div className="bg-emerald-50 rounded-lg p-2 text-center">
                            <p className="text-xs text-gray-500">{t('Collected')}</p>
                            <p className="text-sm font-bold text-emerald-600">
                                {project.total_payment ? formatCurrency(project.total_payment) : '-'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="mt-auto p-3 border-t bg-gray-50/50 flex items-center justify-between">
                    <span className={`px-2 py-0.5 rounded-full text-xs font-bold whitespace-nowrap ${
                        project.status === 'Ongoing'  ? 'bg-blue-100 text-blue-700' :
                        project.status === 'Onhold'   ? 'bg-amber-100 text-amber-700' :
                                                         'bg-emerald-100 text-emerald-700'
                    }`}>
                        {t(project.status)}
                    </span>
                    <TooltipProvider>
                        <UITooltip delayDuration={300}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => router.get(route('project.report.show', project.id))}
                                    className="h-8 w-8 sm:h-7 sm:w-7 p-0 text-green-600 hover:text-green-700"
                                >
                                    <Eye className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('View Report')}</p></TooltipContent>
                        </UITooltip>
                    </TooltipProvider>
                </div>
            </Card>
        );
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Project Report'), url: route('project.report.index') },
                { label: t('Project Reports') },
            ]}
            pageTitle={t('Manage Project Reports')}
        >
            <Head title={t('Project Reports')} />

            <div className="space-y-5">
                {/* Summary Stats — 7 cards */}
                <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                    <StatCard icon={BarChart3}      label={t('Total Projects')} value={stats.total}           accent="blue"   />
                    <StatCard icon={TrendingUp}     label={t('Ongoing')}        value={stats.ongoing}         accent="sky"    />
                    <StatCard icon={Clock}          label={t('On Hold')}        value={stats.onhold}          accent="amber"  />
                    <StatCard icon={Calendar}       label={t('Finished')}       value={stats.finished}        accent="green"  />
                    <StatCard icon={AlertTriangle}  label={t('Overdue')}        value={stats.overdue}         accent="red"    />
                    <StatCard icon={Receipt}        label={t('Total Budget')}   value={formatCurrency(stats.total_budget)}    accent="purple" />
                    <StatCard icon={TrendingUp}     label={t('Total Collected')}value={formatCurrency(stats.total_collected)} accent="green"  />
                </div>

                <Card className="shadow-sm">
                    <CardContent className="p-6 border-b bg-gray-50/50">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex-1 max-w-md">
                                <SearchInput
                                    value={filters.name}
                                    onChange={(value) => setFilters({ ...filters, name: value })}
                                    onSearch={handleFilter}
                                    placeholder={t('Search projects...')}
                                />
                            </div>
                            <div className="flex items-center gap-3">
                                <PerPageSelector routeName="project.report.index" filters={{ ...filters }} />
                                <div className="relative">
                                    <FilterButton showFilters={showFilters} onToggle={() => setShowFilters(!showFilters)} />
                                    {activeFilterCount > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                            {activeFilterCount}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    {showFilters && (
                        <CardContent className="p-6 bg-blue-50/30 border-b">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                    <Select value={filters.status} onValueChange={(v) => setFilters({ ...filters, status: v })}>
                                        <SelectTrigger><SelectValue placeholder={t('Filter by status')} /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Ongoing">{t('Ongoing')}</SelectItem>
                                            <SelectItem value="Onhold">{t('Onhold')}</SelectItem>
                                            <SelectItem value="Finished">{t('Finished')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Date Range')}</label>
                                    <DateRangePicker
                                        value={filters.date_range}
                                        onChange={(v) => setFilters({ ...filters, date_range: v })}
                                        placeholder={t('Select date range')}
                                    />
                                </div>
                                <div className="flex items-end gap-2">
                                    <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                    <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                                </div>
                            </div>
                        </CardContent>
                    )}

                    <CardContent className="p-0">
                        <div className="overflow-auto max-h-[70vh] p-4">
                            {projects.data.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                                    {projects.data.map((project) => (
                                        <ProjectCard key={project.id} project={project} />
                                    ))}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={BarChart3}
                                    title={t('No project reports found')}
                                    description={t('No projects available for reporting.')}
                                    hasFilters={!!(filters.name || filters.status || filters.date_range)}
                                    onClearFilters={clearFilters}
                                    className="h-auto"
                                />
                            )}
                        </div>
                    </CardContent>

                    <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                        <Pagination
                            data={projects}
                            routeName="project.report.index"
                            filters={{ ...filters, per_page: perPage }}
                        />
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
