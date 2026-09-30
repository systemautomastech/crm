import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Package, Trash2, Plus } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { Lead } from '../../types';
import Create from './Create';

interface ProductsProps {
    lead: Lead;
}

export default function Index({ lead }: ProductsProps) {
    const { t } = useTranslation();
    const { auth, productItems } = usePage<any>().props;
    const productItemsList: { id: number; name: string }[] = productItems || [];
    const [createOpen, setCreateOpen] = useState(false);
    const [createKey, setCreateKey] = useState(0);
    const [availableProducts, setAvailableProducts] = useState<{ value: string; label: string }[]>([]);
    const [deleteState, setDeleteState] = useState<{ isOpen: boolean; productId: string | null; message: string }>({
        isOpen: false, productId: null, message: '',
    });

    const openCreateDialog = async () => {
        try {
            const res = await fetch(route('lead.leads.available-products', lead.id));
            const products = await res.json();
            setAvailableProducts(products.map((p: any) => ({ value: p.id.toString(), label: p.name })));
        } catch {}
        setCreateOpen(true);
    };

    const openDeleteDialog = (productId: string) => {
        setDeleteState({ isOpen: true, productId, message: t('Are you sure you want to delete this product?') });
    };

    const confirmDelete = () => {
        if (deleteState.productId) {
            router.delete(route('lead.leads.remove-product', { lead: lead.id, product: deleteState.productId }));
            setDeleteState({ isOpen: false, productId: null, message: '' });
        }
    };

    const columns = [
        {
            key: 'name',
            header: t('Product Name'),
            render: (_: any, row: any) => row.name || '-',
        },
        ...(auth?.user?.permissions?.some((p: string) => ['delete-lead-products'].includes(p)) ? [{
            key: 'actions',
            header: t('Action'),
            render: (_: any, row: any) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('delete-lead-products') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(row.id.toString())} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
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
                <h3 className="text-lg font-medium">{t('Products')}</h3>
                {auth?.user?.permissions?.includes('create-lead-products') && (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={openCreateDialog}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Add Product')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                )}
            </div>

            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full">
                <div className="min-w-[600px]">
                    <DataTable
                        data={productItemsList}
                        columns={columns}
                        className="rounded-none"
                        emptyState={
                            <NoRecordsFound
                                icon={Package}
                                title={t('No Products')}
                                description={t('Get started by adding products to this lead.')}
                                createPermission="create-lead-products"
                                onCreateClick={openCreateDialog}
                                createButtonText={t('Add Products')}
                                className="h-auto"
                            />
                        }
                    />
                </div>
            </div>

            <Dialog open={createOpen} onOpenChange={(open) => { if (!open) setCreateKey(k => k + 1); setCreateOpen(open); }}>
                <Create key={createKey} leadId={lead.id} availableProducts={availableProducts} onSuccess={() => setCreateOpen(false)} />
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={(open) => { if (!open) setDeleteState({ isOpen: false, productId: null, message: '' }); }}
                title={t('Delete Product')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </>
    );
}
