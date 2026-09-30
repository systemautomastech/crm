import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    CheckSquare,
    Edit,
    Search,
    Trash2,
} from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import NoRecordsFound from '@/components/no-records-found';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { DataTable } from '@/components/ui/data-table';
import { Dialog } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { formatDate, formatTime } from '@/utils/helpers';

import EditTask from '../Leads/Show/Tasks/Edit';

interface Lead {
    id: number;
    name?: string | null;
    subject?: string | null;
}

interface LeadTask {
    id: number;
    lead_id: number;
    name: string;
    date: string;
    time: string;
    priority: string;
    status: string;
    created_at?: string | null;
    lead?: Lead | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTasks {
    data: LeadTask[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
    links?: PaginationLink[];
}

interface PageProps {
    tasks: PaginatedTasks;
    auth: {
        user: {
            permissions: string[];
        };
    };
}

interface Filters {
    name: string;
    priority: string;
    status: string;
}

export default function LeadTasks() {
    const { t } = useTranslation();
    const { tasks, auth } = usePage<PageProps>().props;

    const searchParams = new URLSearchParams(
        window.location.search
    );

    const [filters, setFilters] = useState<Filters>({
        name: searchParams.get('name') || '',
        priority: searchParams.get('priority') || '',
        status: searchParams.get('status') || '',
    });

    const [sortKey, setSortKey] = useState(
        searchParams.get('sort') || ''
    );

    const [sortDirection, setSortDirection] = useState<
        'asc' | 'desc'
    >(
        (searchParams.get('direction') as
            | 'asc'
            | 'desc') || 'asc'
    );

    const [editingTask, setEditingTask] =
        useState<LeadTask | null>(null);

    const permissions = auth?.user?.permissions || [];

    const canEdit = permissions.includes(
        'edit-lead-tasks'
    );

    const canDelete = permissions.includes(
        'delete-lead-tasks'
    );

    const {
        deleteState,
        openDeleteDialog,
        closeDeleteDialog,
        confirmDelete,
    } = useDeleteHandler({
        routeName: 'lead.tasks.destroy',
        defaultMessage: t(
            'Are you sure you want to delete this task?'
        ),
    });

    const currentPerPage =
        searchParams.get('per_page') || '10';

    const applyFilters = () => {
        router.get(
            route('lead.tasks.index'),
            {
                name: filters.name || undefined,
                priority: filters.priority || undefined,
                status: filters.status || undefined,
                sort: sortKey || undefined,
                direction: sortKey
                    ? sortDirection
                    : undefined,
                per_page: currentPerPage,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const clearFilters = () => {
        setFilters({
            name: '',
            priority: '',
            status: '',
        });

        setSortKey('');
        setSortDirection('asc');

        router.get(
            route('lead.tasks.index'),
            {
                per_page: currentPerPage,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const handleSort = (key: string) => {
        const direction: 'asc' | 'desc' =
            sortKey === key &&
            sortDirection === 'asc'
                ? 'desc'
                : 'asc';

        setSortKey(key);
        setSortDirection(direction);

        router.get(
            route('lead.tasks.index'),
            {
                name: filters.name || undefined,
                priority: filters.priority || undefined,
                status: filters.status || undefined,
                sort: key,
                direction,
                per_page: currentPerPage,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const openLead = (leadId: number) => {
        router.get(
            route('lead.leads.show', leadId)
        );
    };

    const getPriorityClass = (
        priority: string
    ) => {
        switch (priority) {
            case 'Low':
                return 'bg-green-100 text-green-800';

            case 'Medium':
                return 'bg-yellow-100 text-yellow-800';

            case 'High':
                return 'bg-red-100 text-red-800';

            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusClass = (status: string) => {
        switch (status) {
            case 'On Going':
                return 'bg-yellow-100 text-yellow-800';

            case 'Complete':
                return 'bg-green-100 text-green-800';

            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const columns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true,
        },
        {
            key: 'lead',
            header: t('Lead'),
            render: (
                _value: unknown,
                task: LeadTask
            ) => {
                if (!task.lead) {
                    return '-';
                }

                return (
                    <button
                        type="button"
                        onClick={() =>
                            openLead(task.lead!.id)
                        }
                        className="text-left font-medium text-primary hover:underline"
                    >
                        {task.lead.name ||
                            task.lead.subject ||
                            `#${task.lead.id}`}
                    </button>
                );
            },
        },
        {
            key: 'date',
            header: t('Date'),
            sortable: true,
            render: (
                value: string,
                task: LeadTask
            ) => {
                if (!value) {
                    return '-';
                }

                const taskDate = new Date(value);
                const today = new Date();

                taskDate.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);

                const isExpired =
                    taskDate < today &&
                    task.status !== 'Complete';

                return (
                    <span
                        className={
                            isExpired
                                ? 'font-medium text-red-600'
                                : ''
                        }
                    >
                        {formatDate(value)}
                    </span>
                );
            },
        },
        {
            key: 'time',
            header: t('Time'),
            render: (value: string) =>
                value ? formatTime(value) : '-',
        },
        {
            key: 'priority',
            header: t('Priority'),
            sortable: true,
            render: (value: string) => (
                <span
                    className={`inline-flex rounded-full px-2 py-1 text-sm ${getPriorityClass(
                        value
                    )}`}
                >
                    {t(value)}
                </span>
            ),
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: true,
            render: (value: string) => (
                <span
                    className={`inline-flex rounded-full px-2 py-1 text-sm ${getStatusClass(
                        value
                    )}`}
                >
                    {t(value)}
                </span>
            ),
        },
        ...(canEdit || canDelete
            ? [
                  {
                      key: 'actions',
                      header: t('Actions'),
                      render: (
                          _value: unknown,
                          task: LeadTask
                      ) => (
                          <div className="flex items-center gap-1">
                              <TooltipProvider>
                                  {canEdit && (
                                      <Tooltip delayDuration={0}>
                                          <TooltipTrigger asChild>
                                              <Button
                                                  type="button"
                                                  variant="ghost"
                                                  size="sm"
                                                  onClick={() =>
                                                      setEditingTask(
                                                          task
                                                      )
                                                  }
                                                  className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                              >
                                                  <Edit className="h-4 w-4" />
                                              </Button>
                                          </TooltipTrigger>

                                          <TooltipContent>
                                              <p>{t('Edit')}</p>
                                          </TooltipContent>
                                      </Tooltip>
                                  )}

                                  {canDelete && (
                                      <Tooltip delayDuration={0}>
                                          <TooltipTrigger asChild>
                                              <Button
                                                  type="button"
                                                  variant="ghost"
                                                  size="sm"
                                                  onClick={() =>
                                                      openDeleteDialog(
                                                          task.id
                                                      )
                                                  }
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
                              </TooltipProvider>
                          </div>
                      ),
                  },
              ]
            : []),
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label: t('CRM'),
                    url: route('lead.index'),
                },
                {
                    label: t('Lead Tasks'),
                },
            ]}
            pageTitle={t('Manage Lead Tasks')}
        >
            <Head title={t('Lead Tasks')} />

            <Card>
                <CardContent className="border-b p-6">
                    <div className="mb-5">
                        <h3 className="text-lg font-medium">
                            {t('Lead Tasks')}
                        </h3>

                        <p className="text-sm text-muted-foreground">
                            {t(
                                'Manage all lead tasks.'
                            )}
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <div className="relative lg:col-span-2">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                            <Input
                                value={filters.name}
                                onChange={(event) =>
                                    setFilters(
                                        (previous) => ({
                                            ...previous,
                                            name: event.target.value,
                                        })
                                    )
                                }
                                onKeyDown={(event) => {
                                    if (
                                        event.key === 'Enter'
                                    ) {
                                        applyFilters();
                                    }
                                }}
                                placeholder={t(
                                    'Search task name...'
                                )}
                                className="pl-9"
                            />
                        </div>

                        <Select
                            value={
                                filters.priority || 'all'
                            }
                            onValueChange={(value) =>
                                setFilters(
                                    (previous) => ({
                                        ...previous,
                                        priority:
                                            value === 'all'
                                                ? ''
                                                : value,
                                    })
                                )
                            }
                        >
                            <SelectTrigger>
                                <SelectValue
                                    placeholder={t(
                                        'Priority'
                                    )}
                                />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem value="all">
                                    {t('All Priorities')}
                                </SelectItem>

                                <SelectItem value="Low">
                                    {t('Low')}
                                </SelectItem>

                                <SelectItem value="Medium">
                                    {t('Medium')}
                                </SelectItem>

                                <SelectItem value="High">
                                    {t('High')}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(value) =>
                                setFilters(
                                    (previous) => ({
                                        ...previous,
                                        status:
                                            value === 'all'
                                                ? ''
                                                : value,
                                    })
                                )
                            }
                        >
                            <SelectTrigger>
                                <SelectValue
                                    placeholder={t(
                                        'Status'
                                    )}
                                />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem value="all">
                                    {t('All Statuses')}
                                </SelectItem>

                                <SelectItem value="On Going">
                                    {t('On Going')}
                                </SelectItem>

                                <SelectItem value="Complete">
                                    {t('Complete')}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                onClick={applyFilters}
                            >
                                {t('Apply')}
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                onClick={clearFilters}
                            >
                                {t('Clear')}
                            </Button>
                        </div>
                    </div>
                </CardContent>

                <CardContent className="flex items-center justify-end border-b px-6 py-3">
                    <PerPageSelector
                        routeName="lead.tasks.index"
                        filters={{
                            name:
                                filters.name || undefined,
                            priority:
                                filters.priority ||
                                undefined,
                            status:
                                filters.status || undefined,
                            sort: sortKey || undefined,
                            direction: sortKey
                                ? sortDirection
                                : undefined,
                        }}
                    />
                </CardContent>

                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <div className="min-w-[850px]">
                            <DataTable
                                data={tasks?.data || []}
                                columns={columns}
                                onSort={handleSort}
                                sortKey={sortKey}
                                sortDirection={
                                    sortDirection
                                }
                                className="rounded-none"
                                emptyState={
                                    <NoRecordsFound
                                        icon={CheckSquare}
                                        title={t(
                                            'No Tasks found'
                                        )}
                                        description={t(
                                            'No lead tasks match the current filters.'
                                        )}
                                        hasFilters={Boolean(
                                            filters.name ||
                                                filters.priority ||
                                                filters.status
                                        )}
                                        onClearFilters={
                                            clearFilters
                                        }
                                        className="h-auto"
                                    />
                                }
                            />
                        </div>
                    </div>
                </CardContent>

                <CardContent className="border-t px-4 py-2">
                    <Pagination
                        data={tasks}
                        routeName="lead.tasks.index"
                        filters={{
                            name:
                                filters.name || undefined,
                            priority:
                                filters.priority ||
                                undefined,
                            status:
                                filters.status || undefined,
                            sort: sortKey || undefined,
                            direction: sortKey
                                ? sortDirection
                                : undefined,
                        }}
                    />
                </CardContent>
            </Card>

            <Dialog
                open={!!editingTask}
                onOpenChange={(open) => {
                    if (!open) {
                        setEditingTask(null);
                    }
                }}
            >
                {editingTask && (
                    <EditTask
                        task={editingTask}
                        onSuccess={() =>
                            setEditingTask(null)
                        }
                    />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Task')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}