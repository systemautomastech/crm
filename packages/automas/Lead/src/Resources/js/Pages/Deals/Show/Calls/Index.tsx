import { useState, useEffect } from 'react';
import { usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Phone, Edit, Trash2, Plus } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { formatCallDuration, formatDigitalDuration } from '@/utils/helpers';
import { Deal } from '../../types';
import { DealCall } from './types';
import Create from './Create';
import EditCall from './Edit';

interface CallsProps {
    deal: Deal;
    onRegisterAddHandler?: (handler: () => void) => void;
}

export default function Index({ deal, onRegisterAddHandler }: CallsProps) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [editingCall, setEditingCall] = useState<DealCall | null>(null);
    const [editKey, setEditKey] = useState(0);
    const [deleteState, setDeleteState] = useState<{ isOpen: boolean; callId: number | null; message: string }>({
        isOpen: false, callId: null, message: '',
    });

    useEffect(() => {
        onRegisterAddHandler?.(() => { setCreateKey(k => k + 1); setCreateOpen(true); });
    }, []);

    const openDeleteDialog = (callId: number) => {
        setDeleteState({ isOpen: true, callId, message: t('Are you sure you want to delete this call?') });
    };

    const confirmDelete = () => {
        if (deleteState.callId) {
            router.delete(route('deal.calls.destroy', deleteState.callId));
            setDeleteState({ isOpen: false, callId: null, message: '' });
        }
    };

    const columns = [
        { key: 'subject', header: t('Subject') },
        {
            key: 'call_type',
            header: t('Call Type'),
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${value === 'Inbound' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
                    {t(value)}
                </span>
            ),
        },
        {
            key: 'duration',
            header: t('Duration'),
            render: (value: string) => {
                if (!value) return '-';
                const formatted = formatCallDuration(value);
                if (!formatted || formatted === '-') return '-';
                const digital = formatDigitalDuration(value);
                return (
                    <div className="flex items-center gap-1.5 whitespace-nowrap">
                        <span className="font-mono font-medium text-slate-800 dark:text-slate-200">{digital}</span>
                        <span className="text-xs text-muted-foreground">({formatted})</span>
                    </div>
                );
            },
        },
        {
            key: 'user_id',
            header: t('Assignee'),
            render: (_: any, call: DealCall) => {
                const userDeal = deal.user_deals?.find((ud: any) => ud.user?.id === call.user_id);
                return userDeal?.user?.name || '-';
            },
        },
        ...(auth?.user?.permissions?.some((p: string) => ['edit-deal-calls', 'delete-deal-calls'].includes(p)) ? [{
            key: 'actions',
            header: t('Action'),
            render: (_: any, call: DealCall) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('edit-deal-calls') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => { setEditingCall(call); setEditKey(k => k + 1); }} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <Edit className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                            </Tooltip>
                        )}
                        {auth?.user?.permissions?.includes('delete-deal-calls') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(call.id)} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
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
                <h3 className="text-lg font-medium">{t('Calls')}</h3>
                {auth?.user?.permissions?.includes('create-deal-calls') && (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => { setCreateKey(k => k + 1); setCreateOpen(true); }}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Create')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                )}
            </div>

            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full">
                <div className="min-w-[600px]">
                    <DataTable
                        data={deal.calls || []}
                        columns={columns}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={Phone}
                                title={t('No Calls found')}
                                description={t('Get started by adding your first call.')}
                                createPermission="create-deal-calls"
                                onCreateClick={() => { setCreateKey(k => k + 1); setCreateOpen(true); }}
                                createButtonText={t('Create Call')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} dealId={deal.id} userDeals={deal.user_deals || []} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <Dialog open={!!editingCall} onOpenChange={(open) => { if (!open) setEditingCall(null); }}>
                {editingCall && (
                    <EditCall key={editKey} call={editingCall} userDeals={deal.user_deals || []} onSuccess={() => setEditingCall(null)} />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={(open) => { if (!open) setDeleteState({ isOpen: false, callId: null, message: '' }); }}
                title={t('Delete Call')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </>
    );
}
