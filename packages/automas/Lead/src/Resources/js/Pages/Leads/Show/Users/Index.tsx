import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Users as UsersIcon, Trash2, Plus } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { getImagePath } from '@/utils/helpers';
import { Lead } from '../../types';
import Create from './Create';

interface UsersProps {
    lead: Lead;
    onRegisterAddHandler?: (handler: () => void) => void;
}

export default function Index({ lead }: UsersProps) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [availableUsers, setAvailableUsers] = useState<{ value: string; label: string }[]>([]);
    const [deleteState, setDeleteState] = useState<{ isOpen: boolean; userId: number | null; message: string }>({
        isOpen: false, userId: null, message: '',
    });

    const openCreateDialog = async () => {
        try {
            const res = await fetch(route('lead.leads.available-users', lead.id));
            const users = await res.json();
            setAvailableUsers(users.map((u: any) => ({ value: u.id.toString(), label: u.name })));
        } catch {}
        setCreateOpen(true);
    };

    const openDeleteDialog = (userId: number) => {
        setDeleteState({ isOpen: true, userId, message: t('Are you sure you want to delete this user?') });
    };

    const confirmDelete = () => {
        if (deleteState.userId) {
            router.delete(route('lead.leads.remove-user', { lead: lead.id, user: deleteState.userId }));
            setDeleteState({ isOpen: false, userId: null, message: '' });
        }
    };

    const columns = [
        {
            key: 'user.avatar',
            header: t('Avatar'),
            render: (_: any, userLead: any) => (
                <div className="h-8 w-8 rounded-full border-2 border-background overflow-hidden">
                    {userLead.user?.avatar ? (
                        <img src={getImagePath(userLead.user.avatar)} alt={userLead.user.name} className="h-full w-full object-cover" />
                    ) : (
                        <div className="h-full w-full bg-primary/10 flex items-center justify-center text-sm font-medium">
                            {userLead.user?.name?.charAt(0)?.toUpperCase() || 'U'}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: 'user.name',
            header: t('User Name'),
            render: (_: any, userLead: any) => userLead.user?.name || '-',
        },
        ...(auth?.user?.permissions?.some((p: string) => ['delete-lead-users'].includes(p)) ? [{
            key: 'actions',
            header: t('Action'),
            render: (_: any, userLead: any) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('delete-lead-users') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(userLead.user?.id)} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
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
                <h3 className="text-lg font-medium">{t('Users')}</h3>
                {auth?.user?.permissions?.includes('create-lead-users') && (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={openCreateDialog}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Add User')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                )}
            </div>

            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full">
                <div className="min-w-[600px]">
                    <DataTable
                        data={lead.user_leads || []}
                        columns={columns}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={UsersIcon}
                                title={t('No Users added')}
                                description={t('Get started by adding users to this lead.')}
                                createPermission="create-lead-users"
                                onCreateClick={openCreateDialog}
                                createButtonText={t('Add Users')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} leadId={lead.id} availableUsers={availableUsers} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={(open) => { if (!open) setDeleteState({ isOpen: false, userId: null, message: '' }); }}
                title={t('Delete User')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </>
    );
}
