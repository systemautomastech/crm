import { useState, useMemo, useCallback } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { usePageButtons } from '@/hooks/usePageButtons';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Edit, Trash2, Package, Eye, Copy, CalendarDays, Users, ArrowRight, AlertTriangle, Bug } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { FilterButton } from '@/components/ui/filter-button';
import { DateRangePicker } from '@/components/ui/date-range-picker';

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatCurrency, getImagePath, formatDate } from '@/utils/helpers';
import Create from './Create';
import EditItem from './Edit';
import DuplicateModal from './DuplicateModal';
import NoRecordsFound from '@/components/no-records-found';

interface ProjectItem {
    id: number;
    name: string;
    description?: string;
    budget?: number;
    start_date?: string;
    end_date?: string;
    status: 'Ongoing' | 'Onhold' | 'Finished';
    team_members?: Array<{
        id: number;
        name: string;
        avatar?: string;
    }>;
    task_count?: number;
    bug_count?: number;
    milestone_count?: number;
    completed_milestones?: number;
    milestone_cost?: number;
    created_at: string;
}

interface ProjectIndexProps {
    items: {
        data: ProjectItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    users: Array<{
        id: number;
        name: string;
    }>;
    auth: {
        user: {
            permissions: string[];
        };
    };
}

export default function Index() {
    const { t } = useTranslation();
    const { items, users, auth } = usePage<ProjectIndexProps>().props;
    const urlParams = useMemo(() => new URLSearchParams(window.location.search), []);

    const [filters, setFilters] = useState({
        name: urlParams.get('name') || '',
        status: urlParams.get('status') || '',
        date_range: (() => {
            const fromDate = urlParams.get('date_from');
            const toDate = urlParams.get('date_to');
            return (fromDate && toDate) ? `${fromDate} - ${toDate}` : '';
        })()
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'grid');
    const [showFilters, setShowFilters] = useState(false);

    const [modalState, setModalState] = useState<{
        isOpen: boolean;
        mode: string;
        data: ProjectItem | null;
    }>({
        isOpen: false,
        mode: '',
        data: null
    });

    const [duplicateModalState, setDuplicateModalState] = useState<{
        isOpen: boolean;
        project: ProjectItem | null;
    }>({
        isOpen: false,
        project: null
    });

    useFlashMessages();

    const pageButtons = usePageButtons('projectBtn','Test data');
    const googleDriveButtons = usePageButtons('googleDriveBtn', { module: 'Projects', settingKey: 'GoogleDrive Projects' });
    const oneDriveButtons = usePageButtons('oneDriveBtn', { module: 'Projects', settingKey: 'OneDrive Projects' });
    const dropboxBtn = usePageButtons('dropboxBtn', { module: 'Projects', settingKey: 'Dropbox Projects' });
    const boxBtn = usePageButtons('boxBtn', { module: 'Projects', settingKey: 'Box Projects' });

    const renderTemplateButtons = useCallback((item: ProjectItem) => {
        const TemplateButtonComponent = () => {
            const buttons = usePageButtons('templateBtn', item, undefined, false);
            return buttons?.map((button) => (
                <div key={button.id}>{button.component}</div>
            )) || null;
        };
        return <TemplateButtonComponent />;
    }, []);

    const renderGridTemplateButtons = useCallback((item: ProjectItem) => {
        const buttons = usePageButtons('templateBtn', item, undefined, false);
        return buttons?.map((button) => (
            <div key={button.id}>{button.component}</div>
        )) || null;
    }, []);

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'project.destroy',
        defaultMessage: t('Are you sure you want to delete this project item?')
    });

    const handleFilter = () => {
        const filterParams = {...filters};

        // Convert date_range to date_from and date_to for backend
        if (filters.date_range) {
            const [fromDate, toDate] = filters.date_range.split(' - ');
            filterParams.date_from = fromDate;
            filterParams.date_to = toDate;
        }
        delete filterParams.date_range;

        router.get(route('project.index'), {...filterParams, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('project.index'), {...filters, per_page: perPage, sort: field, direction, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', status: '', date_range: '' });
        router.get(route('project.index'), {per_page: perPage, view: viewMode});
    };

    const openModal = (mode: 'add' | 'edit', data: ProjectItem | null = null) => {
        setModalState({
            isOpen: true,
            mode,
            data
        });
    };

    const closeModal = () => {
        setModalState({
            isOpen: false,
            mode: '',
            data: null
        });
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true,
            render: (value: string, item: ProjectItem) => {
                const canView = auth.user?.permissions?.includes('view-project');
                return canView ? (
                    <button
                        className="font-medium text-gray-900 hover:text-primary transition-colors cursor-pointer text-left"
                        onClick={() => router.get(route('project.show', item.id))}
                    >
                        {value}
                    </button>
                ) : (
                    <span className="font-medium text-gray-900">{value}</span>
                );
            }
        },
        {
            key: 'user',
            header: t('Users'),
            render: (_: any, item: ProjectItem) => {
                const teamMembers = item.team_members || [];
                const maxVisible = 4;

                if (teamMembers.length === 0) return '-';

                return (
                    <div className="flex items-center gap-1">
                        <div className="flex -space-x-1">
                            {teamMembers.slice(0, maxVisible).map((user) => (
                                <TooltipProvider key={user.id}>
                                    <Tooltip delayDuration={0}>
                                        <TooltipTrigger>
                                            <div className="h-8 w-8 rounded-full border-2 border-background overflow-hidden">
                                                {user.avatar ? (
                                                    <img
                                                        src={getImagePath(user.avatar)}
                                                        alt={user.name}
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <img
                                                        src={getImagePath('avatar.png')}
                                                        alt={user.name}
                                                        className="h-full w-full object-cover"
                                                    />
                                                )}
                                            </div>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{user.name}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            ))}
                            {teamMembers.length > maxVisible && (
                                <div className="h-8 w-8 rounded-full bg-gray-200 border-2 border-background flex items-center justify-center">
                                    <span className="text-xs text-gray-600">+{teamMembers.length - maxVisible}</span>
                                </div>
                            )}
                        </div>
                    </div>
                );
            }
        },
        {
            key: 'budget',
            header: t('Budget'),
            render: (value: number) => value ? formatCurrency(value) : '-'
        },
        {
            key: 'start_date',
            header: t('Start Date'),
            render: (value: string) => value ? formatDate(value) : '-'
        },
        {
            key: 'end_date',
            header: t('End Date'),
            render: (value: string) => {
                if (!value) return '-';
                const isOverdue = new Date(value) < new Date();
                return (
                    <span className={isOverdue ? 'text-red-600' : ''}>
                        {formatDate(value)}
                    </span>
                );
            }
        },
        {
            key: 'status',
            header: t('Status'),
            render: (value: string) => {
                const statusColors = {
                    'Ongoing': 'bg-blue-100 text-blue-800',
                    'Onhold': 'bg-yellow-100 text-yellow-800',
                    'Finished': 'bg-green-100 text-green-800'
                };
                return (
                    <span className={`px-2 py-1 rounded-full text-sm ${statusColors[value as keyof typeof statusColors]}`}>
                        {t(value)}
                    </span>
                );
            }
        },
        {
            key: 'task_count',
            header: t('Tasks / Bugs'),
            render: (_: any, item: ProjectItem) => (
                <div className="flex items-center gap-2">
                    <span className="inline-flex items-center gap-1 text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full font-medium">
                        <Package className="h-3 w-3" />
                        {item.task_count || 0}
                    </span>
                    <span className="inline-flex items-center gap-1 text-xs text-red-600 bg-red-50 px-2 py-0.5 rounded-full font-medium">
                        <Bug className="h-3 w-3" />
                        {item.bug_count || 0}
                    </span>
                </div>
            )
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-project', 'edit-project', 'delete-project', 'duplicate-project'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: ProjectItem) => (
                <div className="flex gap-1">
                    {renderTemplateButtons(item)}
                    {auth.user?.permissions?.includes('duplicate-project') && (
                        <Tooltip key={`duplicate-${item.id}`} delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => setDuplicateModalState({ isOpen: true, project: item })} className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700">
                                    <Copy className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Duplicate')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}

                    {auth.user?.permissions?.includes('view-project') && (
                        <Tooltip key={`view-${item.id}`} delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => router.get(route('project.show', item.id))} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                    <Eye className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('View')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                    {auth.user?.permissions?.includes('edit-project') && (
                        <Tooltip key={`edit-${item.id}`} delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => openModal('edit', item)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                    <Edit className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Edit')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                    {auth.user?.permissions?.includes('delete-project') && (
                        <Tooltip key={`delete-${item.id}`} delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => openDeleteDialog(item.id)}
                                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Delete')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                </div>
            )
        }] : [])
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Projects'), url: route('project.index') },
            ]}
            pageTitle={t('Manage Project')}
            pageActions={
                <div className="flex gap-2">
                    {googleDriveButtons.map((button) => (
                        <div key={button.id}>{button.component}</div>
                    ))}
                    {oneDriveButtons.map((button) => (
                        <div key={button.id}>{button.component}</div>
                    ))}
                    {dropboxBtn.map((button) => (
                        <div key={button.id}>{button.component}</div>
                    ))}
                    {boxBtn.map((button) => (
                        <div key={button.id}>{button.component}</div>
                    ))}
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('create-project') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" onClick={() => openModal('add')}>
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Create')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {pageButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                    </TooltipProvider>
                </div>
            }
        >
            <Head title={t('Project')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.name}
                                onChange={(value) => setFilters({...filters, name: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search projects...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <ListGridToggle
                                currentView={viewMode}
                                routeName="project.index"
                                filters={{...filters, per_page: perPage}}
                            />
                            <PerPageSelector
                                routeName="project.index"
                                filters={{...filters, view: viewMode}}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.status, filters.date_range].filter(Boolean).length;
                                    return activeFilters > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                            {activeFilters}
                                        </span>
                                    );
                                })()}
                            </div>
                        </div>
                    </div>
                </CardContent>

                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                <Select value={filters.status} onValueChange={(value) => setFilters({...filters, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by status')} />
                                    </SelectTrigger>
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
                                    onChange={(value) => setFilters({...filters, date_range: value})}
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
                    {viewMode === 'list' ? (
                        <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                            <div className="min-w-[800px]">
                                <DataTable
                                    data={items.data}
                                    columns={tableColumns}
                                    onSort={handleSort}
                                    sortKey={sortField}
                                    sortDirection={sortDirection as 'asc' | 'desc'}
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={Package}
                                            title={t('No projects found')}
                                            description={t('Get started by creating your first project.')}
                                            hasFilters={!!(filters.name || filters.status || filters.date_range)}
                                            onClearFilters={clearFilters}
                                            createPermission="create-project"
                                            onCreateClick={() => openModal('add')}
                                            createButtonText={t('Create Project')}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="overflow-auto max-h-[70vh] p-3 sm:p-4 lg:p-6">
                            {items.data.length > 0 ? (
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
                                    {items.data.map((project) => {
                                        const statusConfig: Record<string, { dot: string; badgeBg: string; badgeText: string }> = {
                                            'Ongoing':  { dot: 'bg-blue-500',  badgeBg: 'bg-blue-50',  badgeText: 'text-blue-700'  },
                                            'Onhold':   { dot: 'bg-amber-500', badgeBg: 'bg-amber-50', badgeText: 'text-amber-700' },
                                            'Finished': { dot: 'bg-green-500', badgeBg: 'bg-green-50', badgeText: 'text-green-700' },
                                        };
                                        const cfg = statusConfig[project.status] ?? { dot: 'bg-slate-400', badgeBg: 'bg-slate-50', badgeText: 'text-slate-600' };
                                        const isOverdue = project.status !== 'Finished' && project.end_date && new Date(project.end_date) < new Date();
                                        const isCostOverBudget = !!(project.budget && (project.milestone_cost ?? 0) > 0 && project.milestone_cost! > project.budget);
                                        const canView = auth.user?.permissions?.includes('view-project');

                                        return (
                                            <Card key={project.id} className="group flex flex-col border border-gray-200">
                                                {/* Card body */}
                                                <div className="p-3 sm:p-3.5 flex flex-col flex-1 gap-2 sm:gap-2.5">

                                                    {/* Row 1 — status badge + task + bug counts */}
                                                    <div className="flex items-center justify-between gap-1">
                                                        <span className={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold ${cfg.badgeBg} ${cfg.badgeText}`}>
                                                            <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot} flex-shrink-0`} />
                                                            {t(project.status)}
                                                        </span>
                                                        <div className="flex items-center gap-1">
                                                            <span className="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-medium">
                                                                <Package className="h-3 w-3" />
                                                                {project.task_count || 0}
                                                            </span>
                                                            <span className="inline-flex items-center gap-1 text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded-full font-medium">
                                                                <Bug className="h-3 w-3" />
                                                                {project.bug_count || 0}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {/* Row 2 — name + description */}
                                                    <div>
                                                        <h3
                                                            className={`font-bold text-sm text-gray-900 line-clamp-2 leading-snug mb-0.5 ${canView ? 'cursor-pointer hover:text-primary transition-colors' : ''}`}
                                                            title={project.name}
                                                            onClick={() => canView && router.get(route('project.show', project.id))}
                                                        >
                                                            {project.name}
                                                        </h3>
                                                        {project.description && (
                                                            <p className="text-xs text-gray-400 line-clamp-1 leading-relaxed">{project.description}</p>
                                                        )}
                                                    </div>

                                                    {/* Row 3 — Budget + Milestone Cost (2-col) */}
                                                    {(project.budget || (project.milestone_cost ?? 0) > 0) && (
                                                        <div className="grid grid-cols-2 gap-1.5">
                                                            <div className="bg-emerald-50 border border-emerald-100 rounded-md px-2.5 py-2">
                                                                <p className="text-[10px] text-emerald-600 font-medium uppercase tracking-wide leading-none mb-1">{t('Budget')}</p>
                                                                <p className="text-xs font-bold text-emerald-800 truncate">
                                                                    {project.budget ? formatCurrency(project.budget) : '—'}
                                                                </p>
                                                            </div>
                                                            <div className={`rounded-md px-2.5 py-2 border ${isCostOverBudget ? 'bg-amber-50 border-amber-200' : 'bg-violet-50 border-violet-100'}`}>
                                                                <p className={`text-[10px] font-medium uppercase tracking-wide leading-none mb-1 ${isCostOverBudget ? 'text-amber-600' : 'text-violet-600'}`}>
                                                                    {t('Milestone Cost')}
                                                                </p>
                                                                <div className="flex items-center gap-1">
                                                                    <p className={`text-xs font-bold truncate ${isCostOverBudget ? 'text-amber-800' : 'text-violet-800'}`}>
                                                                        {(project.milestone_cost ?? 0) > 0 ? formatCurrency(project.milestone_cost!) : '—'}
                                                                    </p>
                                                                    {isCostOverBudget && (
                                                                        <Tooltip delayDuration={0}>
                                                                            <TooltipTrigger asChild>
                                                                                <AlertTriangle className="h-3 w-3 text-amber-500 flex-shrink-0 cursor-pointer" />
                                                                            </TooltipTrigger>
                                                                            <TooltipContent side="top">
                                                                                <p>{t('Milestone cost exceeds budget')}</p>
                                                                            </TooltipContent>
                                                                        </Tooltip>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    )}

                                                    {/* Row 4 — Milestone progress */}
                                                    {(project.milestone_count ?? 0) > 0 && (
                                                        <div className="space-y-1">
                                                            <div className="flex items-center justify-between">
                                                                <span className="text-xs text-gray-500 font-medium">{t('Milestones')}</span>
                                                                <span className="text-xs font-semibold text-gray-700">
                                                                    {project.completed_milestones ?? 0} / {project.milestone_count} {t('done')}
                                                                </span>
                                                            </div>
                                                            <div className="w-full bg-gray-100 rounded-full h-1.5">
                                                                <div
                                                                    className="bg-emerald-500 h-1.5 rounded-full transition-all duration-300"
                                                                    style={{ width: `${Math.round(((project.completed_milestones ?? 0) / (project.milestone_count ?? 1)) * 100)}%` }}
                                                                />
                                                            </div>
                                                        </div>
                                                    )}

                                                    {/* Row 5 — date range */}
                                                    {(project.start_date || project.end_date) && (
                                                        <div className="flex items-center gap-1 text-xs">
                                                            <CalendarDays className="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                                            <span className="text-gray-500">{project.start_date ? formatDate(project.start_date) : '—'}</span>
                                                            <ArrowRight className="h-3 w-3 text-gray-300 flex-shrink-0" />
                                                            <span className={isOverdue ? 'text-red-500 font-medium' : 'text-gray-500'}>
                                                                {project.end_date ? formatDate(project.end_date) : '—'}
                                                            </span>
                                                            {isOverdue && <span className="ml-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-semibold">{t('Overdue')}</span>}
                                                        </div>
                                                    )}

                                                    {/* Row 6 — team members */}
                                                    <div className="flex items-center gap-1.5 flex-1 content-end">
                                                        <Users className="h-3.5 w-3.5 text-gray-300 flex-shrink-0" />
                                                        {project.team_members && project.team_members.length > 0 ? (
                                                            <>
                                                                <div className="flex -space-x-1.5">
                                                                    {project.team_members.slice(0, 5).map((user) => (
                                                                        <TooltipProvider key={user.id}>
                                                                            <Tooltip delayDuration={0}>
                                                                                <TooltipTrigger>
                                                                                    <div className="h-7 w-7 sm:h-6 sm:w-6 rounded-full border-2 border-white overflow-hidden shadow-sm">
                                                                                        <img
                                                                                            src={user.avatar ? getImagePath(user.avatar) : getImagePath('avatar.png')}
                                                                                            alt={user.name}
                                                                                            className="h-full w-full object-cover"
                                                                                        />
                                                                                    </div>
                                                                                </TooltipTrigger>
                                                                                <TooltipContent><p>{user.name}</p></TooltipContent>
                                                                            </Tooltip>
                                                                        </TooltipProvider>
                                                                    ))}
                                                                    {project.team_members.length > 5 && (
                                                                        <div className="h-7 w-7 sm:h-6 sm:w-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center shadow-sm">
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

                                                {/* Actions footer */}
                                                <div className="flex items-center justify-end gap-0.5 px-2 sm:px-2.5 py-1.5 border-t border-gray-100 bg-gray-50/50 flex-shrink-0">
                                                    <TooltipProvider>
                                                        {renderGridTemplateButtons(project)}
                                                        {auth.user?.permissions?.includes('duplicate-project') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => setDuplicateModalState({ isOpen: true, project })} className="h-8 w-8 sm:h-7 sm:w-7 p-0 text-purple-600 hover:text-purple-700">
                                                                        <Copy className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Duplicate')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('view-project') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => router.get(route('project.show', project.id))} className="h-8 w-8 sm:h-7 sm:w-7 p-0 text-green-600 hover:text-green-700">
                                                                        <Eye className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('View')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('edit-project') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', project)} className="h-8 w-8 sm:h-7 sm:w-7 p-0 text-blue-600 hover:text-blue-700">
                                                                        <Edit className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('delete-project') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(project.id)} className="h-8 w-8 sm:h-7 sm:w-7 p-0 text-destructive hover:text-destructive">
                                                                        <Trash2 className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </TooltipProvider>
                                                </div>
                                            </Card>
                                        );
                                    })}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={Package}
                                    title={t('No projects found')}
                                    description={t('Get started by creating your first project.')}
                                    hasFilters={!!(filters.name || filters.status || filters.date_range)}
                                    onClearFilters={clearFilters}
                                    createPermission="create-project"
                                    onCreateClick={() => openModal('add')}
                                    createButtonText={t('Create Project')}
                                />
                            )}
                        </div>
                    )}
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={items}
                        routeName="project.index"
                        filters={{...filters, per_page: perPage, view: viewMode}}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} users={users} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <EditItem
                        item={modalState.data}
                        users={users}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Project')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            <Dialog open={duplicateModalState.isOpen} onOpenChange={() => setDuplicateModalState({ isOpen: false, project: null })}>
                <DuplicateModal
                    isOpen={duplicateModalState.isOpen}
                    project={duplicateModalState.project}
                    onClose={() => setDuplicateModalState({ isOpen: false, project: null })}
                />
            </Dialog>
        </AuthenticatedLayout>
    );
}
