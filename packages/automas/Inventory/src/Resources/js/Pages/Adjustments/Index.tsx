import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Trash2, CheckCircle, Send, ClipboardList, Eye, FileCheck, FileText } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate } from '@/utils/helpers';
import Create from './Create';
import View from './View';

export default function Index() {
    const { t } = useTranslation();
    const { adjustments, auth } = usePage<any>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        adjustment_number: urlParams.get('adjustment_number') || '',
        adjustment_type: urlParams.get('adjustment_type') || '',
        status: urlParams.get('status') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [showFilters, setShowFilters] = useState(false);
    const [modalState, setModalState] = useState<{isOpen: boolean, mode: string, data: any}>({
        isOpen: false,
        mode: '',
        data: null
    });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'inventory.adjustments.destroy',
        defaultMessage: t('Are you sure you want to delete this adjustment?')
    });

    const [approveState, setApproveState] = useState<{isOpen: boolean, id: number | null}>({
        isOpen: false,
        id: null
    });

    const handleFilter = () => {
        router.get(route('inventory.adjustments.index'), {...filters, per_page: perPage, sort: sortField, direction: sortDirection}, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('inventory.adjustments.index'), {...filters, per_page: perPage, sort: field, direction}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ adjustment_number: '', adjustment_type: '', status: '' });
        router.get(route('inventory.adjustments.index'), {per_page: perPage});
    };

    const openModal = (mode: string, data: any = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const openApproveDialog = (id: number) => {
        setApproveState({ isOpen: true, id });
    };

    const closeApproveDialog = () => {
        setApproveState({ isOpen: false, id: null });
    };

    const confirmApprove = () => {
        if (approveState.id) {
            router.post(route('inventory.adjustments.approve', approveState.id), {}, {
                preserveScroll: true,
                onFinish: () => closeApproveDialog()
            });
        }
    };

    const [postState, setPostState] = useState<{isOpen: boolean, id: number | null}>({
        isOpen: false,
        id: null
    });

    const openPostDialog = (id: number) => {
        setPostState({ isOpen: true, id });
    };

    const closePostDialog = () => {
        setPostState({ isOpen: false, id: null });
    };

    const confirmPost = () => {
        if (postState.id) {
            router.post(route('inventory.adjustments.post', postState.id), {}, {
                preserveScroll: true,
                onFinish: () => closePostDialog()
            });
        }
    };

    const tableColumns = [
        {
            key: 'adjustment_number',
            header: t('Adjustment Number'),
            sortable: true,
            render: (value: string, item: any) => (
                <button
                    onClick={() => openModal('view', item)}
                    className="text-blue-600 hover:text-blue-800"
                >
                    {value}
                </button>
            )
        },
        {
            key: 'adjustment_date',
            header: t('Date'),
            sortable: true,
            render: (value: string) => formatDate(value)
        },
        {
            key: 'warehouse.name',
            header: t('Warehouse'),
            render: (_: any, item: any) => item.warehouse?.name || '-'
        },
        {
            key: 'adjustment_type',
            header: t('Type'),
            sortable: true,
            render: (value: string) => value?.charAt(0).toUpperCase() + value?.slice(1)
        },      
        {
            key: 'status',
            header: t('Status'),
            sortable: true,
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === 'posted' ? 'bg-green-100 text-green-800' : 
                    value === 'approved' ? 'bg-blue-100 text-blue-800' : 
                    'bg-yellow-100 text-yellow-800'
                }`}>
                    {value?.charAt(0).toUpperCase() + value?.slice(1)}
                </span>
            )
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-inventory-adjustments', 'delete-inventory-adjustments', 'approve-inventory-adjustments', 'post-inventory-adjustments'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: any) => (
                <div className="flex gap-1">
                    <TooltipProvider>                      
                        {auth.user?.permissions?.includes('approve-inventory-adjustments') && item.status === 'draft' && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openApproveDialog(item.id)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <CheckCircle className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Approve')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('approve-inventory-adjustments') && item.status === 'approved' && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openPostDialog(item.id)} className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700">
                                        <FileText className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Post')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                          {auth.user?.permissions?.includes('view-inventory-adjustments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openModal('view', item)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}                        

                        {auth.user?.permissions?.includes('delete-inventory-adjustments') && item.status !== 'posted' && (
                            <Tooltip delayDuration={0}>
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
                    </TooltipProvider>
                </div>
            )
        }] : [])
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Inventory')}, {label: t('Adjustments')}]}
            pageTitle={t('Manage Inventory Adjustments')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-inventory-adjustments') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => router.visit(route('inventory.adjustments.create'))}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Create')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                </TooltipProvider>
            }
        >
            <Head title={t('Inventory Adjustments')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.adjustment_number}
                                onChange={(value) => setFilters({...filters, adjustment_number: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search adjustments...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="inventory.adjustments.index"
                                filters={filters}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.adjustment_type, filters.status].filter(Boolean).length;
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
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Type')}</label>
                                <Select value={filters.adjustment_type} onValueChange={(value) => setFilters({...filters, adjustment_type: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by type')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="increase">{t('Increase')}</SelectItem>
                                        <SelectItem value="decrease">{t('Decrease')}</SelectItem>
                                        <SelectItem value="recount">{t('Recount')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                <Select value={filters.status} onValueChange={(value) => setFilters({...filters, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">{t('Draft')}</SelectItem>
                                        <SelectItem value="approved">{t('Approved')}</SelectItem>
                                        <SelectItem value="posted">{t('Posted')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-end gap-2">
                                <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                            </div>
                        </div>
                    </CardContent>
                )}

                <CardContent className="p-0">
                    <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                        <div className="min-w-[800px]">
                            <DataTable
                                data={adjustments.data}
                                columns={tableColumns}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection as 'asc' | 'desc'}
                                className="rounded-none"
                                emptyState={
                                    <NoRecordsFound
                                        icon={ClipboardList}
                                        title={t('No adjustments found')}
                                        description={t('Get started by creating your first adjustment.')}
                                        hasFilters={!!(filters.adjustment_number || filters.adjustment_type || filters.status)}
                                        onClearFilters={clearFilters}
                                        createPermission="create-inventory-adjustments"
                                        onCreateClick={() => router.visit(route('inventory.adjustments.create'))}
                                        createButtonText={t('Create Adjustment')}
                                        className="h-auto"
                                    />
                                }
                            />
                        </div>
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={adjustments}
                        routeName="inventory.adjustments.index"
                        filters={{...filters, per_page: perPage}}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'view' && modalState.data && (
                    <View adjustment={modalState.data} />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Adjustment')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            <ConfirmationDialog
                open={approveState.isOpen}
                onOpenChange={closeApproveDialog}
                title={t('Approve Adjustment')}
                message={t('Are you sure you want to approve this adjustment?')}
                confirmText={t('Approve')}
                onConfirm={confirmApprove}
            />

            <ConfirmationDialog
                open={postState.isOpen}
                onOpenChange={closePostDialog}
                title={t('Post Adjustment')}
                message={t('Are you sure you want to post this adjustment? This will update warehouse stock quantities and cannot be undone.')}
                confirmText={t('Post')}
                onConfirm={confirmPost}
            />
        </AuthenticatedLayout>
    );
}
