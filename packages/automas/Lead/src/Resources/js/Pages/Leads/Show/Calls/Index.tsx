import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Phone, Edit, Trash2, Plus } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { formatCallDuration, formatDigitalDuration } from '@/utils/helpers';
import { Lead } from '../../types';
import { LeadCall } from './types';
import Create from './Create';
import EditCall from './Edit';

interface CallsProps {
    lead: Lead;
}

export default function Index({ lead }: CallsProps) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [editingCall, setEditingCall] = useState<LeadCall | null>(null);
    const [editKey, setEditKey] = useState(0);
    const [deleteState, setDeleteState] = useState<{ isOpen: boolean; callId: number | null; message: string }>({
        isOpen: false, callId: null, message: '',
    });

    const openDeleteDialog = (callId: number) => {
        setDeleteState({ isOpen: true, callId, message: t('Are you sure you want to delete this call?') });
    };

    const confirmDelete = () => {
        if (deleteState.callId) {
            router.delete(route('lead.calls.destroy', deleteState.callId));
            setDeleteState({ isOpen: false, callId: null, message: '' });
        }
    };

    const canAdd = auth?.user?.permissions?.includes('edit-leads');

    const columns = [
        {
            key: 'subject',
            header: t('Subject'),
        },
        {
            key: 'call_type',
            header: t('Call Type'),
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === 'Inbound' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                }`}>
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
            render: (_: any, call: LeadCall) => {
                const userLead = lead.user_leads?.find((ul: any) => ul.user?.id === call.user_id);
                return userLead?.user?.name || '-';
            },
        },
        ...(auth?.user?.permissions?.some((p: string) => ['edit-lead-calls','delete-lead-calls'].includes(p)) ? [{
            key: 'actions',
            header: t('Action'),
            render: (_: any, call: LeadCall) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('edit-lead-calls') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => { setEditingCall(call); setEditKey(k => k + 1); }} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                    <Edit className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>
                         )}
                         {auth?.user?.permissions?.includes('delete-lead-calls') && (
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
               {auth?.user?.permissions?.includes('create-lead-calls') && (
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
                        data={lead.calls || []}
                        columns={columns}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={Phone}
                                title={t('No Calls found')}
                                description={t('Get started by adding your first call.')}
                                createPermission="create-lead-calls"
                                onCreateClick={() => { setCreateKey(k => k + 1); setCreateOpen(true); }}
                                createButtonText={t('Create Call')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} leadId={lead.id} userLeads={lead.user_leads || []} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <Dialog open={!!editingCall} onOpenChange={(open) => { if (!open) setEditingCall(null); }}>
                {editingCall && (
                    <EditCall key={editKey} call={editingCall} userLeads={lead.user_leads || []} onSuccess={() => setEditingCall(null)} />
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
