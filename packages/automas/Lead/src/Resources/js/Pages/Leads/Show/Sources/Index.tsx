import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Database, Trash2, Plus } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { Lead } from '../../types';
import Create from './Create';

interface SourcesProps {
    lead: Lead;
}

export default function Index({ lead }: SourcesProps) {
    const { t } = useTranslation();
    const { auth, sourceItems } = usePage<any>().props;
    const sourceItemsList: { id: number; name: string }[] = sourceItems || [];
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [availableSources, setAvailableSources] = useState<{ value: string; label: string }[]>([]);
    const [deleteState, setDeleteState] = useState<{ isOpen: boolean; sourceId: string | null; message: string }>({
        isOpen: false, sourceId: null, message: '',
    });

    const openCreateDialog = async () => {
        try {
            const res = await fetch(route('lead.leads.available-sources', lead.id));
            const sources = await res.json();
            setAvailableSources(sources.map((s: any) => ({ value: s.id.toString(), label: s.name })));
        } catch {}
        setCreateOpen(true);
    };

    const openDeleteDialog = (sourceId: string) => {
        setDeleteState({ isOpen: true, sourceId, message: t('Are you sure you want to delete this source?') });
    };

    const confirmDelete = () => {
        if (deleteState.sourceId) {
            router.delete(route('lead.leads.remove-source', { lead: lead.id, source: deleteState.sourceId }));
            setDeleteState({ isOpen: false, sourceId: null, message: '' });
        }
    };

    const columns = [
        {
            key: 'name',
            header: t('Source Name'),
            render: (_: any, row: any) => row.name || '-',
        },
        ...(auth?.user?.permissions?.some((p: string) => ['delete-lead-sources'].includes(p)) ? [{
            key: 'actions',
            header: t('Action'),
            render: (_: any, row: any) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(row.id.toString())} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            ),
        }] : []),
    ];

    return (
        <>
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-medium">{t('Sources')}</h3>
                {auth?.user?.permissions?.includes('create-lead-sources') && (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={openCreateDialog}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Add Source')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                )}
            </div>

            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full">
                <div className="min-w-[600px]">
                    <DataTable
                        data={sourceItemsList}
                        columns={columns}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={Database}
                                title={t('No Sources added')}
                                description={t('Get started by adding sources to this lead.')}
                                createPermission="create-lead-sources"
                                onCreateClick={openCreateDialog}
                                createButtonText={t('Add Sources')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} leadId={lead.id} availableSources={availableSources} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={(open) => { if (!open) setDeleteState({ isOpen: false, sourceId: null, message: '' }); }}
                title={t('Delete Source')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </>
    );
}
