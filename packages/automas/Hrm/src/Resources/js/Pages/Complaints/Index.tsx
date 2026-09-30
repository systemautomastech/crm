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
import { Plus, Edit as EditIcon, Trash2, MessageSquareWarning, FileImage, Settings, Eye, Play, ChevronDown } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";

import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Create from './Create';
import EditComplaint from './Edit';
import ComplaintStatus from './ComplaintStatus';
import Show from './Show';
import NoRecordsFound from '@/components/no-records-found';
import { Complaint, ComplaintsIndexProps, ComplaintFilters, ComplaintModalState } from './types';
import { formatDate, getImagePath } from '@/utils/helpers';

export default function Index() {
    const { t } = useTranslation();
    const { complaints, auth, employees, complaintTypes } = usePage<ComplaintsIndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<ComplaintFilters>({
        subject: urlParams.get('subject') || '',
        employee_id: urlParams.get('employee_id') || 'all',
        complaint_type_id: urlParams.get('complaint_type_id') || 'all',
        status: urlParams.get('status') || 'all',
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');

    const [modalState, setModalState] = useState<ComplaintModalState>({
        isOpen: false,
        mode: '',
        data: null
    });
    const [showFilters, setShowFilters] = useState(false);

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'hrm.complaints.destroy',
        defaultMessage: t('Are you sure you want to delete this complaint?')
    });

    const handleFilter = () => {
        router.get(route('hrm.complaints.index'), { ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('hrm.complaints.index'), { ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({
            subject: '',
            employee_id: 'all',
            complaint_type_id: 'all',
            status: 'all',
        });
        router.get(route('hrm.complaints.index'), { per_page: perPage });
    };

    const openModal = (mode: 'add' | 'edit' | 'status' | 'show', data: Complaint | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const updateStatus = (complaintId: number, newStatus: string) => {
        router.put(route('hrm.complaints.update-status', complaintId), {
            status: newStatus
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Status updated successfully
            }
        });
    };



    const tableColumns = [
        {
            key: 'employee.name',
            header: t('Employee Name'),
            sortable: false,
            render: (value: any, row: any) => row.employee?.name || '-'
        },
        {
            key: 'against_employee.name',
            header: t('Against Employee'),
            sortable: false,
            render: (value: any, row: any) => row.against_employee?.name || '-'
        },
        {
            key: 'complaint_type.complaint_type',
            header: t('Complaint Type'),
            sortable: false,
            render: (value: any, row: any) => row.complaint_type?.complaint_type || '-'
        },
        {
            key: 'subject',
            header: t('Subject'),
            sortable: true
        },
        {
            key: 'complaint_date',
            header: t('Complaint Date'),
            sortable: false,
            render: (value: string) => value ? formatDate(value) : '-'
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: false,
            render: (value: string, row: any) => {
                const statusColors = {
                    pending: 'bg-yellow-100 text-yellow-700',
                    'in review': 'bg-blue-100 text-blue-700',
                    assigned: 'bg-purple-100 text-purple-700',
                    'in progress': 'bg-orange-100 text-orange-700',
                    resolved: 'bg-green-100 text-green-700'
                };
                const normalizedStatus = value?.toLowerCase() || '';
                const displayValue = normalizedStatus === 'in review' ? 'In Review' : normalizedStatus === 'in progress' ? 'In Progress' : (value ? value.charAt(0).toUpperCase() + value.slice(1) : 'Pending');

                if (auth.user?.permissions?.includes('manage-complaint-status')) {
                    return (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" className={`px-2 py-1 rounded-full text-sm font-medium h-auto hover:opacity-80 ${statusColors[normalizedStatus] || statusColors.pending}`}>
                                    {t(displayValue)} <ChevronDown className="h-2 w-2 ml-1" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem onClick={() => updateStatus(row.id, 'pending')}>
                                    {t('Pending')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => updateStatus(row.id, 'in review')}>
                                    {t('In Review')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => updateStatus(row.id, 'assigned')}>
                                    {t('Assigned')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => updateStatus(row.id, 'in progress')}>
                                    {t('In Progress')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => updateStatus(row.id, 'resolved')}>
                                    {t('Resolved')}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    );
                }

                return (
                    <span className={`px-2 py-1 rounded-full text-sm font-medium ${statusColors[normalizedStatus] || 'bg-gray-100 text-gray-800'}`}>
                        {t(displayValue)}
                    </span>
                );
            }
        },

        ...(auth.user?.permissions?.some((p: string) => ['view-complaints', 'manage-complaint-status', 'edit-complaints', 'delete-complaints'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, complaint: Complaint) => (
                <div className="flex gap-1">


                    <TooltipProvider>
                        {complaint.document && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" asChild className="h-8 w-8 p-0 text-orange-600 hover:text-orange-700">
                                        <a href={getImagePath(complaint.document)} target="_blank" rel="noopener noreferrer">
                                            <FileImage className="h-4 w-4" />
                                        </a>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Document')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('view-complaints') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openModal('show', complaint)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}

                        {auth.user?.permissions?.includes('edit-complaints') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', complaint)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <EditIcon className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Edit')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('delete-complaints') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(complaint.id)}
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
            breadcrumbs={[
                { label: t('Hrm'), url: route('hrm.index') },
                { label: t('Complaints') }
            ]}
            pageTitle={t('Manage Complaints')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-complaints') && (
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
                </TooltipProvider>
            }
        >
            <Head title={t('Complaints')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.subject}
                                onChange={(value) => setFilters({ ...filters, subject: value })}
                                onSearch={handleFilter}
                                placeholder={t('Search Complaints...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="hrm.complaints.index"
                                filters={{ ...filters }}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [
                                        filters.employee_id !== 'all' ? filters.employee_id : '',
                                        filters.complaint_type_id !== 'all' ? filters.complaint_type_id : '',
                                        filters.status !== 'all' ? filters.status : ''
                                    ].filter(f => f !== '' && f !== null && f !== undefined).length;
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
                            {auth.user?.permissions?.includes('manage-employees') && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Employee')}</label>
                                    <Select value={filters.employee_id} onValueChange={(value) => setFilters({ ...filters, employee_id: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('All Employees')} />
                                        </SelectTrigger>
                                        <SelectContent searchable={true}>
                                            <SelectItem value="all">{t('All Employees')}</SelectItem>
                                            {employees?.map((employee: any) => (
                                                <SelectItem key={employee.id} value={employee.id.toString()}>
                                                    {employee.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}

                            {auth.user?.permissions?.includes('manage-complaint-types') && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Complaint Type')}</label>
                                    <Select value={filters.complaint_type_id} onValueChange={(value) => setFilters({ ...filters, complaint_type_id: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('All Types')} />
                                        </SelectTrigger>
                                        <SelectContent searchable={true}>
                                            <SelectItem value="all">{t('All Types')}</SelectItem>
                                            {complaintTypes?.map((type: any) => (
                                                <SelectItem key={type.id} value={type.id.toString()}>
                                                    {type.complaint_type}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                <Select value={filters.status} onValueChange={(value) => setFilters({ ...filters, status: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('All Status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Status')}</SelectItem>
                                        <SelectItem value="pending">{t('Pending')}</SelectItem>
                                        <SelectItem value="in review">{t('In Review')}</SelectItem>
                                        <SelectItem value="assigned">{t('Assigned')}</SelectItem>
                                        <SelectItem value="in progress">{t('Progress')}</SelectItem>
                                        <SelectItem value="resolved">{t('Resolved')}</SelectItem>
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
                                data={complaints?.data || []}
                                columns={tableColumns}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection as 'asc' | 'desc'}
                                className="rounded-none"
                                emptyState={
                                    <NoRecordsFound
                                        icon={MessageSquareWarning}
                                        title={t('No Complaints found')}
                                        description={t('Get started by creating your first Complaint.')}
                                        hasFilters={!!(filters.subject || (filters.employee_id !== 'all' && filters.employee_id) || (filters.complaint_type_id !== 'all' && filters.complaint_type_id) || (filters.status !== 'all' && filters.status))}
                                        onClearFilters={clearFilters}
                                        createPermission="create-complaints"
                                        onCreateClick={() => openModal('add')}
                                        createButtonText={t('Create Complaint')}
                                        className="h-auto"
                                    />
                                }
                            />
                        </div>
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={complaints || { data: [], links: [], meta: {} }}
                        routeName="hrm.complaints.index"
                        filters={{ ...filters, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <EditComplaint
                        complaint={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
                {modalState.mode === 'status' && modalState.data && (
                    <ComplaintStatus
                        complaint={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
                {modalState.mode === 'show' && modalState.data && (
                    <Show
                        complaint={modalState.data}
                        onClose={closeModal}
                    />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Complaint')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}