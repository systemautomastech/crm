import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { CheckSquare, Edit, Plus, Trash2 } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate, formatTime } from '@/utils/helpers';
import { Lead } from '../../types';
import { LeadTask } from './types';
import Create from './Create';
import EditTask from './Edit';

interface TasksProps {
    lead: Lead;
    onRegisterAddHandler?: (handler: () => void) => void;
}

export default function Index({ lead, onRegisterAddHandler }: TasksProps) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [editingTask, setEditingTask] = useState<LeadTask | null>(null);
    const [sortKey, setSortKey] = useState('');
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');

    useEffect(() => {
        onRegisterAddHandler?.(() => setCreateOpen(true));
    }, [onRegisterAddHandler]);

    const handleSort = (key: string) => {
        const direction = sortKey === key && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortKey(key);
        setSortDirection(direction);
    };

    const sortedTasks = [...(lead.tasks || [])].sort((a: any, b: any) => {
        if (!sortKey) return 0;
        const aVal = a[sortKey] ?? '';
        const bVal = b[sortKey] ?? '';
        const cmp = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
        return sortDirection === 'asc' ? cmp : -cmp;
    });

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'lead.tasks.destroy',
        defaultMessage: t('Are you sure you want to delete this task?'),
    });

    const getPriorityClass = (priority: string) => {
        switch (priority) {
            case 'Low': return 'bg-green-100 text-green-800';
            case 'Medium': return 'bg-yellow-100 text-yellow-800';
            case 'High': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusClass = (status: string) => {
        switch (status) {
            case 'On Going': return 'bg-yellow-100 text-yellow-800';
            case 'Complete': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const columns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true,
        },
        {
            key: 'date',
            header: t('Date'),
            sortable: true,
            render: (value: string, task: any) => {
                if (!value) return '-';
                const taskDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                taskDate.setHours(0, 0, 0, 0);
                const isExpired = taskDate < today && task.status !== 'Complete';
                return (
                    <span className={isExpired ? 'text-red-600 font-medium' : ''}>
                        {formatDate(value)}
                    </span>
                );
            },
        },
        {
            key: 'time',
            header: t('Time'),
            render: (value: string) => value ? formatTime(value) : '-',
        },
        {
            key: 'priority',
            header: t('Priority'),
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${getPriorityClass(value)}`}>
                    {t(value)}
                </span>
            ),
        },
        {
            key: 'status',
            header: t('Status'),
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${getStatusClass(value)}`}>
                    {t(value)}
                </span>
            ),
        },
        ...(auth?.user?.permissions?.some((p: string) => ['edit-lead-tasks', 'delete-lead-tasks'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, task: LeadTask) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('edit-lead-tasks') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => setEditingTask(task)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <Edit className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                            </Tooltip>
                        )}
                        {auth?.user?.permissions?.includes('delete-lead-tasks') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(task.id)} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                            </Tooltip>
                        )}
                    </TooltipProvider>
                </div>
            ),
        }] : []),
    ];

    return (
        <>
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-medium">{t('Tasks')}</h3>
                <TooltipProvider>
                    {auth?.user?.permissions?.includes('create-lead-tasks') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => setCreateOpen(true)}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Create')}</p></TooltipContent>
                        </Tooltip>
                    )}
                </TooltipProvider>
            </div>
            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] w-full">
                <div className="min-w-[600px]">
                    <DataTable
                        data={sortedTasks}
                        columns={columns}
                        onSort={handleSort}
                        sortKey={sortKey}
                        sortDirection={sortDirection}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={CheckSquare}
                                title={t('No Tasks found')}
                                description={t('Get started by creating your first Task.')}
                                createPermission="create-lead-tasks"
                                onCreateClick={() => setCreateOpen(true)}
                                createButtonText={t('Create Task')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} leadId={lead.id} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <Dialog open={!!editingTask} onOpenChange={(open) => { if (!open) setEditingTask(null); }}>
                {editingTask && (
                    <EditTask task={editingTask} onSuccess={() => setEditingTask(null)} />
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
        </>
    );
}
