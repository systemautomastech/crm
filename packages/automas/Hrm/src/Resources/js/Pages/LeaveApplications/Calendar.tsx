import { useState, useMemo } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import CalendarView from "@/components/calendar-view";
import {
    Plus, Eye, Play, CalendarDays,
    ListFilter, Users, CheckCircle2, XCircle, AlertCircle,
    ArrowUpRight, FileText, Filter
} from "lucide-react";
import Create from './Create';
import EditLeaveApplication from './Edit';
import View from './View';
import StatusUpdate from './StatusUpdate';
import { formatDate } from '@/utils/helpers';
import { usePageButtons } from '@/hooks/usePageButtons';

interface CalendarLeaveEvent {
    id: number;
    title: string;
    startDate: string;
    endDate: string;
    time: string;
    color: string;
    borderColor: string;
    status: string;
    leaveType: string;
    leaveTypeColor: string;
    totalDays: number;
    reason: string;
    isPaid: boolean;
    employeeName: string;
    approvedBy: string | null;
    approvedAt: string | null;
    approverComment: string | null;
    createdAt: string;
    attachment: string | null;
    employeeId: number;
    leaveTypeId: number;
}

interface CalendarStats {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
    upcoming: number;
}

interface CalendarProps {
    leaveEvents: CalendarLeaveEvent[];
    employees: { id: number; name: string }[];
    leavetypes: { id: number; name: string; color: string }[];
    stats: CalendarStats;
    auth: {
        user: {
            permissions: string[];
        }
    };
}

export default function LeaveCalendar() {
    const { t } = useTranslation();
    const { leaveEvents, employees, leavetypes, stats, auth } = usePage<CalendarProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        status: urlParams.get('status') || '',
        employee_id: urlParams.get('employee_id') || '',
        leave_type_id: urlParams.get('leave_type_id') || '',
    });

    const [modalState, setModalState] = useState<{
        isOpen: boolean;
        mode: string;
        data: any;
    }>({
        isOpen: false,
        mode: '',
        data: null
    });
    const [viewingItem, setViewingItem] = useState<any>(null);
    const [statusModalItem, setStatusModalItem] = useState<any>(null);
    const [showFilters, setShowFilters] = useState(false);

    useFlashMessages();
    const googleDriveButtons = usePageButtons('googleDriveBtn', { module: 'Leave', settingKey: 'GoogleDrive Leave' });
    const oneDriveButtons = usePageButtons('oneDriveBtn', { module: 'Leave', settingKey: 'OneDrive Leave' });
    const dropboxButtons = usePageButtons('dropboxBtn', { module: 'Leave', settingKey: 'Dropbox Leave' });
    const boxButtons = usePageButtons('boxBtn', { module: 'Leave', settingKey: 'Box Leave' });

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'hrm.leave-applications.destroy',
        defaultMessage: t('Are you sure you want to delete this leave application?')
    });

    const calendarEvents = useMemo(() => {
        return leaveEvents.map(event => {
            let statusColor = event.color;
            
            // Apply lighter gradient colors based on status
            if (event.status === 'pending') {
                statusColor = '#fbbf24'; // Lighter amber/yellow
            } else if (event.status === 'approved') {
                statusColor = '#34d399'; // Lighter emerald green
            } else if (event.status === 'rejected') {
                statusColor = '#f87171'; // Lighter red
            }
            
            return {
                id: event.id,
                title: event.title,
                startDate: event.startDate,
                endDate: event.endDate,
                time: event.time,
                color: statusColor,
                ...event
            };
        });
    }, [leaveEvents]);

    const upcomingLeaves = useMemo(() => {
        return leaveEvents
            .filter(l => l.status === 'approved' && l.startDate >= new Date().toISOString().split('T')[0])
            .sort((a, b) => a.startDate.localeCompare(b.startDate))
            .slice(0, 10);
    }, [leaveEvents]);

    const pendingLeaves = useMemo(() => {
        return leaveEvents
            .filter(l => l.status === 'pending')
            .sort((a, b) => a.startDate.localeCompare(b.startDate))
            .slice(0, 10);
    }, [leaveEvents]);

    const handleFilter = () => {
        router.get(route('hrm.leave-applications.calendar'), { ...filters }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ status: '', employee_id: '', leave_type_id: '' });
        router.get(route('hrm.leave-applications.calendar'));
    };

    const openModal = (mode: 'add' | 'edit', data: any = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const handleEventClick = (event: any) => {
        const leaveApp = leaveEvents.find(l => l.id === event.id);
        if (leaveApp) {
            setViewingItem({
                id: leaveApp.id,
                start_date: leaveApp.startDate,
                end_date: leaveApp.endDate,
                total_days: leaveApp.totalDays,
                reason: leaveApp.reason,
                status: leaveApp.status,
                approver_comment: leaveApp.approverComment,
                approved_at: leaveApp.approvedAt,
                attachment: leaveApp.attachment,
                created_at: leaveApp.createdAt,
                employee: { name: leaveApp.employeeName },
                leave_type: { name: leaveApp.leaveType, color: leaveApp.leaveTypeColor, is_paid: leaveApp.isPaid },
                approved_by: leaveApp.approvedBy ? { name: leaveApp.approvedBy } : null,
            });
        }
    };

    const mapToLeaveApplication = (item: CalendarLeaveEvent): any => ({
        id: item.id,
        start_date: item.startDate,
        end_date: item.endDate,
        total_days: item.totalDays,
        reason: item.reason,
        status: item.status,
        approver_comment: item.approverComment,
        approved_at: item.approvedAt,
        attachment: item.attachment,
        created_at: item.createdAt,
        employee_id: item.employeeId,
        leave_type_id: item.leaveTypeId,
        employee: { name: item.employeeName },
        leave_type: { name: item.leaveType, color: item.leaveTypeColor, is_paid: item.isPaid },
        approved_by: item.approvedBy ? { name: item.approvedBy } : null,
    });

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Hrm'), url: route('hrm.index') },
                { label: t('Leave Management') },
                { label: t('Leave Calendar') }
            ]}
            pageTitle={t('Leave Calendar')}
            pageActions={
                <div className="flex gap-2">
                    <TooltipProvider>
                        {googleDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {oneDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {dropboxButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {boxButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => router.get(route('hrm.leave-applications.index'))}
                                >
                                    <ListFilter className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('List View')}</p>
                            </TooltipContent>
                        </Tooltip>
                        {auth.user?.permissions?.includes('create-leave-applications') && (
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
                </div>
            }
        >
            <Head title={t('Leave Calendar')} />

            <div className="space-y-6">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {/* Main Calendar */}
                    <div className="lg:col-span-8">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                        <CalendarDays className="h-5 w-5" />
                                        {t('Monthly Leave Overview')}
                                    </CardTitle>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setShowFilters(!showFilters)}
                                        >
                                            <Filter className="h-4 w-4 mr-1" />
                                            {t('Filters')}
                                            {(filters.status || filters.employee_id || filters.leave_type_id) && (
                                                <span className="ml-1 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                    {[filters.status, filters.employee_id, filters.leave_type_id].filter(Boolean).length}
                                                </span>
                                            )}
                                        </Button>
                                    </div>
                                </div>

                                {showFilters && (
                                    <div className="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div>
                                            <label className="block text-xs font-medium text-gray-700 mb-1">{t('Employee')}</label>
                                            <Select value={filters.employee_id} onValueChange={(value) => setFilters({ ...filters, employee_id: value })}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue placeholder={t('All Employees')} />
                                                </SelectTrigger>
                                                <SelectContent searchable={true}>
                                                    {employees?.map((emp) => (
                                                        <SelectItem key={emp.id} value={emp.id.toString()}>{emp.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <label className="block text-xs font-medium text-gray-700 mb-1">{t('Status')}</label>
                                            <Select value={filters.status} onValueChange={(value) => setFilters({ ...filters, status: value })}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue placeholder={t('All Status')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="pending">{t('Pending')}</SelectItem>
                                                    <SelectItem value="approved">{t('Approved')}</SelectItem>
                                                    <SelectItem value="rejected">{t('Rejected')}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <label className="block text-xs font-medium text-gray-700 mb-1">{t('Leave Type')}</label>
                                            <Select value={filters.leave_type_id} onValueChange={(value) => setFilters({ ...filters, leave_type_id: value })}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue placeholder={t('All Types')} />
                                                </SelectTrigger>
                                                <SelectContent searchable={true}>
                                                    {leavetypes?.map((lt) => (
                                                        <SelectItem key={lt.id} value={lt.id.toString()}>{lt.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="flex items-end gap-2">
                                            <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                            <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                                        </div>
                                    </div>
                                )}
                            </CardHeader>
                            <CardContent>
                                <CalendarView
                                    events={calendarEvents}
                                    onEventClick={handleEventClick}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="lg:col-span-4 space-y-6">
                        {/* Status Legend */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                    <Users className="h-5 w-5" />
                                    {t('Status Legend')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center gap-4 text-sm">
                                    <div className="flex items-center gap-2">
                                        <span className="w-3 h-3 rounded-full" style={{ backgroundColor: '#fbbf24' }}></span>
                                        <span>{t('Pending')}</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="w-3 h-3 rounded-full" style={{ backgroundColor: '#34d399' }}></span>
                                        <span>{t('Approved')}</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="w-3 h-3 rounded-full" style={{ backgroundColor: '#f87171' }}></span>
                                        <span>{t('Rejected')}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Pending Approvals */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                    <AlertCircle className="h-5 w-5 text-yellow-600" />
                                    {t('Pending Approvals')}
                                    {stats.pending > 0 && (
                                        <Badge variant="secondary" className="ml-auto">{stats.pending}</Badge>
                                    )}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 space-y-3 pr-2">
                                    {pendingLeaves.length === 0 ? (
                                        <div className="flex items-center justify-center h-40 text-gray-500">
                                            <div className="text-center">
                                                <AlertCircle className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                                <p className="text-sm">{t('No pending leaves')}</p>
                                            </div>
                                        </div>
                                    ) : (
                                        pendingLeaves.map((leave) => (
                                            <div key={leave.id} className="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                                <div className="min-w-0 flex-1">
                                                    <p className="text-sm font-medium text-gray-900 truncate">{leave.employeeName}</p>
                                                    <p className="text-xs text-gray-500">{leave.leaveType} &bull; {leave.totalDays} {t('days')}</p>
                                                    <p className="text-xs text-gray-500">{formatDate(leave.startDate)} - {formatDate(leave.endDate)}</p>
                                                </div>
                                                <div className="flex gap-1 ml-2 flex-shrink-0">
                                                    <TooltipProvider>
                                                        {auth.user?.permissions?.includes('manage-leave-status') && (
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        className="h-7 w-7 p-0 text-purple-600 hover:text-purple-700"
                                                                        onClick={() => setStatusModalItem(mapToLeaveApplication(leave))}
                                                                    >
                                                                        <Play className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>{t('Manage Status')}</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('view-leave-applications') && (
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        className="h-7 w-7 p-0 text-green-600 hover:text-green-700"
                                                                        onClick={() => setViewingItem(mapToLeaveApplication(leave))}
                                                                    >
                                                                        <Eye className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>{t('View')}</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </TooltipProvider>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Upcoming Leaves */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                    <ArrowUpRight className="h-5 w-5 text-green-600" />
                                    {t('Upcoming Leaves')}
                                    {stats.upcoming > 0 && (
                                        <Badge variant="secondary" className="ml-auto bg-green-100 text-green-800">{stats.upcoming}</Badge>
                                    )}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 space-y-3 pr-2">
                                    {upcomingLeaves.length === 0 ? (
                                        <div className="flex items-center justify-center h-40 text-gray-500">
                                            <div className="text-center">
                                                <ArrowUpRight className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                                <p className="text-sm">{t('No upcoming leaves')}</p>
                                            </div>
                                        </div>
                                    ) : (
                                        upcomingLeaves.map((leave) => (
                                            <div key={leave.id} className="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                                <div className="min-w-0 flex-1">
                                                    <p className="text-sm font-medium text-gray-900 truncate">{leave.employeeName}</p>
                                                    <p className="text-xs text-gray-500">{leave.leaveType} &bull; {leave.totalDays} {t('days')}</p>
                                                    <p className="text-xs text-gray-500">{formatDate(leave.startDate)} - {formatDate(leave.endDate)}</p>
                                                </div>
                                                <div className="ml-2 flex-shrink-0">
                                                    <TooltipProvider>
                                                        {auth.user?.permissions?.includes('view-leave-applications') && (
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        className="h-7 w-7 p-0 text-green-600 hover:text-green-700"
                                                                        onClick={() => setViewingItem(mapToLeaveApplication(leave))}
                                                                    >
                                                                        <Eye className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>{t('View')}</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </TooltipProvider>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            {/* Modals */}
            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <EditLeaveApplication leaveapplication={modalState.data} onSuccess={closeModal} />
                )}
            </Dialog>

            <Dialog open={!!viewingItem} onOpenChange={() => setViewingItem(null)}>
                {viewingItem && <View leaveapplication={viewingItem} />}
            </Dialog>

            <Dialog open={!!statusModalItem} onOpenChange={() => setStatusModalItem(null)}>
                {statusModalItem && <StatusUpdate leaveapplication={statusModalItem} onSuccess={() => setStatusModalItem(null)} />}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Leave Application')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
