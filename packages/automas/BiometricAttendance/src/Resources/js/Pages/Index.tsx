import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { DatePicker } from '@/components/ui/date-picker';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { RefreshCw, Clock, Eye } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import NoRecordsFound from '@/components/no-records-found';
import { toast } from 'sonner';
import { formatTime, formatDate } from '@/utils/helpers';

interface AttendanceEntry {
    id: number;
    time: string;
    punch_state_display: string;
    verify_type_display: string;
    terminal_alias: string;
}

interface SelectedEmployee {
    code: string;
    date: string;
    name: string;
}


export default function Index() {
    const { t } = useTranslation();
    const { attendances, employees, auth, configurationMissing, isZktecoSync } = usePage().props;
    const urlParams = new URLSearchParams(window.location.search);
    const { pageProps } = usePage().props;
    const TimeDisplay = ({ time, pageProps }: { time: string; pageProps: any }) => {
        return <>{formatTime(time, pageProps)}</>;
    };
    
    const DateDisplay = ({ date, pageProps }: { date: string; pageProps: any }) => {
        return <>{formatDate(date, pageProps)}</>;
    };

    const [filters, setFilters] = useState({
        employee_id: urlParams.get('employee_id') || '',
        search: urlParams.get('search') || '',
        date_from: urlParams.get('date_from') || '',
        date_to: urlParams.get('date_to') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [viewMode, setViewMode] = useState(urlParams.get('view') || 'list');
    const [showFilters, setShowFilters] = useState(false);
    const [showDetailsModal, setShowDetailsModal] = useState(false);
    const [detailEntries, setDetailEntries] = useState<AttendanceEntry[]>([]);
    const [selectedEmployee, setSelectedEmployee] = useState<SelectedEmployee | null>(null);
    const [showSyncDialog, setShowSyncDialog] = useState(false);
    const [syncDateRange, setSyncDateRange] = useState({ start_date: '', end_date: '' });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'biometric-attendance.destroy',
        defaultMessage: t('Are you sure you want to delete this record?')
    });

    const handleFilter = () => {
        router.get(route('biometric-attendance.index'), {...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('biometric-attendance.index'), {...filters, per_page: perPage, sort: field, direction, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ employee_id: '', search: '', date_from: '', date_to: '' });
        router.get(route('biometric-attendance.index'), {per_page: perPage, view: viewMode});
    };

    const handleSync = (row) => {
        if (configurationMissing) {
            router.visit(route('biometric-attendance.settings'));
            return;
        }
        
        // Sync individual row
        const syncData = {
            biometric_emp_id: row?.employee_code,
            biometric_id: row?.biometric_id,
            date: row?.date,
            clock_in: row?.clock_in,
            clock_out: row?.clock_out
        };
        
        router.post(route('biometric-attendance.sync'), syncData, {
            onSuccess: (page) => {
                // Success message will be shown by flash message handler
            },
            onError: (errors) => {
                // Error message will be shown by flash message handler
            }
        });
    };

    const handleBulkSync = () => {
        if (!syncDateRange.start_date || !syncDateRange.end_date) {
            toast.error(t('Please select both start and end dates'));
            return;
        }
        
        router.post(route('biometric-attendance.sync-all-by-date-range'), syncDateRange, {
            onSuccess: () => {
                setShowSyncDialog(false);
                setSyncDateRange({ start_date: '', end_date: '' });
                // Success message will be shown by flash message handler
            },
            onError: () => {
                // Error message will be shown by flash message handler
            }
        });
    };

    const handleShowDetails = async (row: any) => {
        try {
            const response = await fetch(route('biometric-attendance.show', {
                employeeCode: row.employee_code,
                date: row.date
            }));

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                setDetailEntries(result.data.entries || []);
                setSelectedEmployee({
                    code: result.data.employee_code,
                    date: result.data.date,
                    name: row.name
                });
                setShowDetailsModal(true);
            } else {
                toast.error(t(result.message || 'Failed to fetch details'));
            }
        } catch (error) {
            toast.error(t('Error fetching details'));
        }
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Employee'),
            sortable: true,
            render: (value, row) => (
                <div>
                    <div className="font-medium">{value}</div>
                    <div className="text-sm text-gray-500">{row.employee_id}</div>
                </div>
            )
        },
        {
            key: 'date',
            header: t('Date'),
            sortable: true,
            render: (value) => formatDate(value) || '-'
        },
        {
            key: 'clock_in',
            header: t('Clock In'),
            render: (value) => formatTime(value) || '-'
        },
        {
            key: 'clock_out',
            header: t('Clock Out'),
            render: (value) => formatTime(value) || '-'
        },
        {
            key: 'biometric_id',
            header: t('Biometric ID')
        },
        {
            key: 'status',
            header: t('Status'),
            render: (value) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                    {t(value && value.length > 0 ? value.charAt(0).toUpperCase() + value.slice(1) : 'Unknown')}
                </span>
            )
        },
        ...(auth?.user?.permissions?.some((p: string) => ['view-biometric-attendance', 'sync-biometric-attendance'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, attendance: any) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth?.user?.permissions?.includes('view-biometric-attendance') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button 
                                        variant="ghost" 
                                        size="sm" 
                                        className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                        onClick={() => handleShowDetails(attendance)}
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {(auth?.user?.permissions?.includes('sync-biometric-attendance') && (isZktecoSync != '0')) && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleSync(attendance)}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                    >
                                        <RefreshCw className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Sync')}</p>
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
            breadcrumbs={[{label: t('Biometric Attendance')},{label: t('Attendance')}]}
            pageTitle={t('Manage Biometric Attendance')}
            pageActions={ (isZktecoSync != '0') && (
                <div className="flex gap-2">
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button 
                                    variant="outline"
                                    size="sm" 
                                    onClick={() => configurationMissing ? router.visit(route('biometric-attendance.settings')) : setShowSyncDialog(true)}
                                >
                                    <RefreshCw className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{configurationMissing ? t('Configure Settings') : t('Sync Data')}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
            }
        >
            <Head title={t('Biometric Attendance')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.search}
                                onChange={(value) => setFilters({...filters, search: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search attendance...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <ListGridToggle
                                currentView={viewMode}
                                routeName="biometric-attendance.index"
                                filters={{...filters, per_page: perPage}}
                            />
                            <PerPageSelector
                                routeName="biometric-attendance.index"
                                filters={{...filters, view: viewMode}}
                            />
                            <FilterButton
                                showFilters={showFilters}
                                onToggle={() => setShowFilters(!showFilters)}
                            />
                        </div>
                    </div>
                </CardContent>

                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Employee')}</label>
                                <Select value={filters.employee_id} onValueChange={(value) => setFilters({...filters, employee_id: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by employee')} />
                                    </SelectTrigger>
                                    <SelectContent searchable>
                                        {employees?.map((employee) => (
                                            <SelectItem key={employee.id} value={employee.id.toString()}>
                                                {employee.user?.name} ({employee.employee_id})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Date From')}</label>
                                <DatePicker
                                    value={filters.date_from}
                                    onChange={(value) => setFilters({...filters, date_from: value})}
                                    placeholder={t('Select start date')}
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Date To')}</label>
                                <DatePicker
                                    value={filters.date_to}
                                    onChange={(value) => setFilters({...filters, date_to: value})}
                                    placeholder={t('Select end date')}
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
                        <div className="overflow-y-auto max-h-[70vh] w-full">
                            <DataTable
                                key={attendances?.data?.length}
                                data={attendances?.data || []}
                                columns={tableColumns}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection}
                                emptyState={
                                    <NoRecordsFound
                                        icon={Clock}
                                        title={configurationMissing ? t('Configuration Required') : t('No records found')}
                                        description={configurationMissing ? t('Please configure biometric settings first.') : t('Sync biometric data to get started.')}
                                        hasFilters={!!(filters.employee_id || filters.search || filters.date_from || filters.date_to)}
                                        onClearFilters={clearFilters}
                                        onCreateClick={() => configurationMissing ? router.visit(route('biometric-attendance.settings')) : setShowSyncDialog(true)}
                                        createButtonText={configurationMissing ? t('Configure Settings') : t('Sync Data')}
                                    />
                                }
                            />
                        </div>
                    ) : (
                        <div className="overflow-auto max-h-[70vh] p-6">
                            {attendances?.data?.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                                    {attendances.data.map((attendance) => (
                                        <Card key={attendance.id} className="border border-gray-200 hover:shadow-lg transition-all duration-200">
                                            <div className="p-4">
                                                <div className="flex items-start justify-between mb-3">
                                                    <div className="flex-1">
                                                        <h3 className="font-semibold text-base text-gray-900 truncate">{attendance.name}</h3>
                                                        <p className="text-xs text-blue-600 font-medium mt-1">{attendance.employee_code}</p>
                                                    </div>
                                                    <span className={`px-2 py-1 rounded-full text-xs ${
                                                        attendance.status === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                    }`}>
                                                        {t(attendance.status && attendance.status.length > 0 ? attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1) : 'Unknown')}
                                                    </span>
                                                </div>

                                                <div className="space-y-2 mb-3">
                                                    <div className="flex justify-between items-center">
                                                        <span className="text-xs text-gray-500">{t('Date')}</span>
                                                        <span className="text-xs font-medium text-gray-900">{formatDate(attendance.date) || '-'}</span>
                                                    </div>
                                                    <div className="flex justify-between items-center">
                                                        <span className="text-xs text-gray-500">{t('Clock In')}</span>
                                                        <span className="text-xs text-gray-900">{formatTime(attendance.clock_in) || '-'}</span>
                                                    </div>
                                                    <div className="flex justify-between items-center">
                                                        <span className="text-xs text-gray-500">{t('Clock Out')}</span>
                                                        <span className="text-xs text-gray-900">{formatTime(attendance.clock_out) || '-'}</span>
                                                    </div>
                                                    <div className="flex justify-between items-center">
                                                        <span className="text-xs text-gray-500">{t('Biometric ID')}</span>
                                                        <span className="text-xs font-medium text-gray-900">{attendance.biometric_id}</span>
                                                    </div>
                                                </div>

                                                <div className="flex items-center justify-between pt-3 border-t border-gray-100">
                                                    <div className="flex gap-1">
                                                        {auth?.user?.permissions?.some((p: string) => ['view-biometric-attendance', 'sync-biometric-attendance'].includes(p)) && (
                                                            <TooltipProvider>
                                                                {auth?.user?.permissions?.includes('view-biometric-attendance') && (
                                                                    <Tooltip delayDuration={0}>
                                                                        <TooltipTrigger asChild>
                                                                            <Button variant="ghost" size="sm" onClick={() => handleShowDetails(attendance)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                                                                <Eye className="h-4 w-4" />
                                                                            </Button>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent>
                                                                            <p>{t('View')}</p>
                                                                        </TooltipContent>
                                                                    </Tooltip>
                                                                )}
                                                                {auth?.user?.permissions?.includes('sync-biometric-attendance') && (
                                                                    <Tooltip delayDuration={0}>
                                                                        <TooltipTrigger asChild>
                                                                            <Button variant="ghost" size="sm" onClick={() => handleSync(attendance)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                                                                <RefreshCw className="h-4 w-4" />
                                                                            </Button>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent>
                                                                            <p>{t('Sync')}</p>
                                                                        </TooltipContent>
                                                                    </Tooltip>
                                                                )}
                                                            </TooltipProvider>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={Clock}
                                    title={configurationMissing ? t('Configuration Required') : t('No records found')}
                                    description={configurationMissing ? t('Please configure biometric settings first.') : t('Sync biometric data to get started.')}
                                    hasFilters={!!(filters.employee_id || filters.search || filters.date_from || filters.date_to)}
                                    onClearFilters={clearFilters}
                                    onCreateClick={() => configurationMissing ? router.visit(route('biometric-attendance.settings')) : setShowSyncDialog(true)}
                                    createButtonText={configurationMissing ? t('Configure Settings') : t('Sync Data')}
                                    className="h-auto"
                                />
                            )}
                        </div>
                    )}
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={attendances}
                        routeName="biometric-attendance.index"
                        filters={{...filters, per_page: perPage, view: viewMode}}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Record')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            {/* Sync Date Range Dialog */}
            <Dialog open={showSyncDialog} onOpenChange={setShowSyncDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('Bulk Sync Attendance')}</DialogTitle>
                        <DialogDescription>
                            {t('Select date range to sync attendance data from biometric device')}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid grid-cols-2 gap-4 py-4">
                        <div>
                            <label className="block text-sm font-medium mb-2">{t('Start Date')}</label>
                            <DatePicker
                                value={syncDateRange.start_date}
                                onChange={(value) => setSyncDateRange({...syncDateRange, start_date: value})}
                                placeholder={t('Select start date')}
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-2">{t('End Date')}</label>
                            <DatePicker
                                value={syncDateRange.end_date}
                                onChange={(value) => setSyncDateRange({...syncDateRange, end_date: value})}
                                placeholder={t('Select end date')}
                            />
                        </div>
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button variant="outline" onClick={() => setShowSyncDialog(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button onClick={handleBulkSync}>
                            {t('Sync')}
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Details Modal */}
            <Dialog open={showDetailsModal} onOpenChange={setShowDetailsModal}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>
                        {t('Punch Details')} - {selectedEmployee?.name} ({selectedEmployee?.code})
                        </DialogTitle>
                        <DialogDescription>
                            {t('Detailed punch records for')} {selectedEmployee?.name} {t('on')} {<><DateDisplay date={selectedEmployee?.date || ''} pageProps={pageProps} /></>}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="max-h-96 overflow-y-auto">
                        <DataTable
                            key={selectedEmployee?.id || "id"}
                            data={detailEntries}
                            columns={[
                                {
                                    key: 'time',
                                    header: t('Time'),
                                    render: (value) => <span className="font-sm">{<><TimeDisplay time={value} pageProps={pageProps} /></>}</span>
                                },
                                {
                                    key: 'punch_state_display',
                                    header: t('Status')
                                },
                                {
                                    key: 'verify_type_display',
                                    header: t('Verify Type')
                                },
                                {
                                    key: 'terminal_alias',
                                    header: t('Terminal')
                                }
                            ]}
                            emptyState={
                                <div className="text-center py-8 text-gray-500">
                                    {t('No entries found')}
                                </div>
                            }
                        />
                    </div>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}