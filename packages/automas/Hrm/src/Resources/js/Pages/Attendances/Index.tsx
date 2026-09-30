import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Check, X, ChevronLeft, ChevronRight, Flag, Umbrella, Coffee, AlertCircle, Timer, User as UserIcon } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Dialog } from "@/components/ui/dialog";
import { SearchInput } from '@/components/ui/search-input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

import Create from './Create';
import Edit from './Edit';
import View from './Show';
import { Attendance, AttendancesIndexProps, AttendanceModalState } from './types';
import { formatDate, formatTime, getImagePath } from '@/utils/helpers';

export default function Index() {
    const { t } = useTranslation();
    const { attendances, auth, employees, shifts, leaves, holidays, workingDays } = usePage<AttendancesIndexProps>().props;
    const [currentMonth, setCurrentMonth] = useState(new Date());
    const [search, setSearch] = useState('');
    const [appliedSearch, setAppliedSearch] = useState('');
    const [modalState, setModalState] = useState<AttendanceModalState>({
        isOpen: false,
        mode: '',
        data: null
    });


    // Calendar helpers
    const getDaysInMonth = (date: Date) => {
        const year = date.getFullYear();
        const month = date.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        return Array.from({ length: daysInMonth }, (_, i) => i + 1);
    };

    const getMonthName = (date: Date) => {
        return date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
    };

    const getDayName = (day: number) => {
        const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), day);
        return date.toLocaleString('default', { weekday: 'short' }).toUpperCase();
    };

    const previousMonth = () => {
        const newMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1);
        setCurrentMonth(newMonth);
        router.get(route('hrm.attendances.index'), { 
            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
        }, {
            preserveState: true,
            replace: true
        });
    };

    const nextMonth = () => {
        const newMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1);
        setCurrentMonth(newMonth);
        router.get(route('hrm.attendances.index'), { 
            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
        }, {
            preserveState: true,
            replace: true
        });
    };

    const getLeaveForDay = (employeeId: number, day: number) => {
        const dateStr = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        return leaves?.find((leave: any) => {
            return leave.employee_id === employeeId && 
                   leave.start_date <= dateStr && 
                   leave.end_date >= dateStr;
        });
    };

    const getHolidayForDay = (day: number) => {
        const dateStr = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        return holidays?.find((holiday: any) => {
            return holiday.start_date <= dateStr && holiday.end_date >= dateStr;
        });
    };

    const isDayOff = (day: number) => {
        const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), day);
        const dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        // Convert workingDays strings to numbers for comparison
        const workingDaysNumbers = workingDays?.map((d: any) => parseInt(d)) || [];
        return workingDaysNumbers.length > 0 && !workingDaysNumbers.includes(dayOfWeek);
    };

    const getFilteredEmployees = () => {
        let filtered = employees || [];
        if (appliedSearch) {
            filtered = filtered.filter((emp: any) => {
                const name = emp.name || emp.user?.name || '';
                return name.toLowerCase().includes(appliedSearch.toLowerCase());
            });
        }
        return filtered;
    };

    const handleSearch = () => {
        setAppliedSearch(search);
    };

    const handleClearSearch = () => {
        setSearch('');
        setAppliedSearch('');
    };

    const getAttendanceForDay = (employeeId: number, day: number) => {
        const dateStr = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const attendance = attendances?.data?.find((att: any) => {
            const attDate = att.date ? att.date.split('T')[0] : '';
            return att.employee_id === employeeId && attDate === dateStr;
        });
        return attendance;
    };

    const getAttendanceIcon = (attendance: any, leave: any, holiday: any, dayOff: boolean) => {
        // Priority: Holiday > Leave > Day Off > Attendance
        if (holiday) {
            return <Umbrella className="h-4 w-4 text-yellow-600" />;
        }
        
        if (leave) {
            return <Flag className="h-4 w-4 text-red-600" />;
        }
        
        if (dayOff) {
            return <Coffee className="h-4 w-4 text-gray-600" />;
        }
        
        if (!attendance) return <span className="text-gray-300">○</span>;
        
        // Check for pending (clock in but no clock out)
        if (attendance.is_pending) {
            return <AlertCircle className="h-4 w-4 text-orange-500" />;
        }
        
        const overtime = attendance.overtime_hours > 0;
        const isLate = attendance.is_late;
        const isEarly = attendance.is_early;
        
        switch(attendance.status) {
            case 'present':
                return (
                    <div className="flex flex-col items-center">
                        <div className="flex items-center gap-0.5">
                            <Check className="h-4 w-4 text-green-600" />
                            {isLate && <Timer className="h-3 w-3 text-red-600" />}
                            {isEarly && <Timer className="h-3 w-3 text-green-600" />}
                        </div>
                        {overtime && <span className="text-[10px] text-blue-500">O</span>}
                    </div>
                );
            case 'absent':
                return (
                    <div className="flex flex-col items-center">
                        <div className="flex items-center gap-0.5">
                            <X className="h-4 w-4 text-red-600" />
                            {isLate && <Timer className="h-3 w-3 text-red-600" />}
                            {isEarly && <Timer className="h-3 w-3 text-green-600" />}
                        </div>
                        {overtime && <span className="text-[10px] text-blue-500">O</span>}
                    </div>
                );
            case 'half day':
                return (
                    <div className="flex flex-col items-center">
                        <div className="flex items-center gap-0.5">
                            <span className="text-yellow-600 font-bold text-sm">½</span>
                            {isLate && <Timer className="h-3 w-3 text-red-600" />}
                            {isEarly && <Timer className="h-3 w-3 text-green-600" />}
                        </div>
                        {overtime && <span className="text-[10px] text-blue-500">O</span>}
                    </div>
                );
            default:
                return <span className="text-gray-300">○</span>;
        }
    };



    const calculateMonthlyTotal = (employeeId: number) => {
        const days = getDaysInMonth(currentMonth);
        let total = 0;
        days.forEach(day => {
            const attendance = getAttendanceForDay(employeeId, day);
            if (attendance?.total_hour) {
                total += parseFloat(attendance.total_hour);
            }
        });
        return total.toFixed(1);
    };



    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'hrm.attendances.destroy',
        defaultMessage: t('Are you sure you want to delete this attendance?')
    });

    const openModal = (mode: 'add' | 'edit' | 'view', data: Attendance | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };


    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('HRM'), url: route('hrm.index') },
                { label: t('Attendances') }
            ]}
            pageTitle={t('Manage Attendances')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-attendances') && (
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
            <Head title={t('Attendances')} />

            {/* Main Content Card */}
            <Card className="shadow-sm">
                {/* Calendar Content */}
                <CardContent className="p-0">
                    <div className="p-6">
                            {/* Search, Month & Year Dropdowns */}
                            <div className="flex items-center justify-between mb-4 gap-4">
                                <div className="max-w-sm w-full">
                                    <SearchInput
                                        value={search}
                                        onChange={(value) => setSearch(value)}
                                        onSearch={handleSearch}
                                        placeholder={t('Search employee...')}
                                    />
                                </div>
                                <div className="flex items-center gap-2">
                                    <div className="w-44">
                                        <Select
                                            value={currentMonth.getMonth().toString()}
                                            onValueChange={(value) => {
                                                const monthIndex = parseInt(value);
                                                const newMonth = new Date(currentMonth.getFullYear(), monthIndex);
                                                setCurrentMonth(newMonth);
                                                router.get(route('hrm.attendances.index'), {
                                                    month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
                                                }, {
                                                    preserveState: true,
                                                    replace: true
                                                });
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={t('Select Month')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Array.from({ length: 12 }, (_, i) => (
                                                    <SelectItem key={i} value={i.toString()}>
                                                        {new Date(2024, i, 1).toLocaleString('default', { month: 'long' })}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="w-28">
                                        <Select
                                            value={currentMonth.getFullYear().toString()}
                                            onValueChange={(value) => {
                                                const year = parseInt(value);
                                                const newMonth = new Date(year, currentMonth.getMonth());
                                                setCurrentMonth(newMonth);
                                                router.get(route('hrm.attendances.index'), {
                                                    month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
                                                }, {
                                                    preserveState: true,
                                                    replace: true
                                                });
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={t('Select Year')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Array.from({ length: 11 }, (_, i) => {
                                                    const year = 2017 + i;
                                                    return (
                                                        <SelectItem key={year} value={year.toString()}>
                                                            {year}
                                                        </SelectItem>
                                                    );
                                                })}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>

                            {/* Calendar Header */}
                            <div className="flex items-center justify-between mb-6 gap-4">
                                <h2 className="text-lg font-semibold text-green-600 whitespace-nowrap">
                                    ATTENDANCE REPORT: {getMonthName(currentMonth)}
                                </h2>
                                <div className="flex items-center gap-2">
                                    <Button variant="outline" size="sm" onClick={previousMonth}>
                                        <ChevronLeft className="h-4 w-4" />
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={nextMonth}>
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            {/* Legend */}
                            <div className="flex flex-wrap items-center gap-4 mb-6 text-xs">
                                <div className="flex items-center gap-1">
                                    <Check className="h-3 w-3 text-green-600" />
                                    <span className="text-gray-600">PRESENT</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <X className="h-3 w-3 text-red-600" />
                                    <span className="text-gray-600">ABSENT</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <span className="text-yellow-600 font-bold">½</span>
                                    <span className="text-gray-600">HALF DAY</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Flag className="h-3 w-3 text-red-600" />
                                    <span className="text-gray-600">ON LEAVE</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Umbrella className="h-3 w-3 text-yellow-600" />
                                    <span className="text-gray-600">HOLIDAY</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Coffee className="h-3 w-3 text-gray-600" />
                                    <span className="text-gray-600">DAY OFF</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <span className="text-gray-400">○</span>
                                    <span className="text-gray-600">FUTURE</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <AlertCircle className="h-3 w-3 text-gray-400" />
                                    <span className="text-gray-600">PENDING</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Timer className="h-3 w-3 text-red-600" />
                                    <span className="text-gray-600">LATE</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Timer className="h-3 w-3 text-green-600" />
                                    <span className="text-gray-600">EARLY</span>
                                </div>
                                <div className="flex items-center gap-1">
                                    <Timer className="h-3 w-3 text-blue-600" />
                                    <span className="text-gray-600">OVERTIME</span>
                                </div>
                            </div>

                            {/* Calendar Grid - Fixed width with horizontal scroll */}
                            <div className="w-full overflow-auto border rounded-lg max-h-[600px]">
                                <table className="border-collapse text-xs" style={{ minWidth: '100%' }}>
                                    <thead>
                                        <tr className="bg-gray-50">
                                            <th className="border p-2 text-left font-semibold text-gray-700 sticky top-0 left-0 bg-gray-50 z-30 w-48">
                                                EMPLOYEE
                                            </th>
                                            {getDaysInMonth(currentMonth).map(day => (
                                                <th key={day} className="border p-2 text-center w-12 sticky top-0 bg-gray-50 z-20">
                                                    <div className="font-semibold text-gray-900">{day}</div>
                                                    <div className="text-[10px] text-gray-500 font-normal">{getDayName(day)}</div>
                                                </th>
                                            ))}
                                            <th className="border p-2 text-center font-semibold text-gray-700 sticky top-0 right-0 bg-gray-50 z-20 w-16">
                                                TOTAL
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {getFilteredEmployees()?.length === 0 ? (
                                            <tr>
                                                <td colSpan={getDaysInMonth(currentMonth).length + 2} className="border p-8 text-center">
                                                    <div className="flex flex-col items-center gap-2 text-gray-500">
                                                        <AlertCircle className="h-12 w-12 text-gray-400" />
                                                        <p className="text-lg font-medium">{t('No employees found')}</p>
                                                        {(search || appliedSearch) && (
                                                            <Button variant="outline" size="sm" onClick={handleClearSearch} className="mt-2">
                                                                {t('Clear Search')}
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ) : (
                                            getFilteredEmployees()?.map((employee: any) => (
                                            <tr key={employee.id} className="hover:bg-gray-50">
                                                <td className="border p-3 sticky left-0 bg-white z-10 w-48">
                                                    <div className="flex items-center gap-3">
                                                        <div className="h-8 w-8 rounded-lg overflow-hidden bg-gray-100 border flex items-center justify-center flex-shrink-0">
                                                            {employee.avatar || employee.user?.avatar ? (
                                                                <img
                                                                    src={getImagePath(employee.avatar || employee.user?.avatar)}
                                                                    alt="Avatar"
                                                                    className="w-full h-full object-cover"
                                                                />
                                                            ) : (
                                                                <UserIcon className="w-4 h-4 text-gray-400" />
                                                            )}
                                                        </div>
                                                        <div className="min-w-0">
                                                            <div className="font-semibold text-gray-900 truncate">{employee.name || employee.user?.name}</div>
                                                            <div className="text-[10px] text-gray-500 truncate">{employee.designation?.designation_name || 'N/A'}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                {getDaysInMonth(currentMonth).map(day => {
                                                    const attendance = getAttendanceForDay(employee.id, day);
                                                    const leave = getLeaveForDay(employee.id, day);
                                                    const holiday = getHolidayForDay(day);
                                                    const dayOff = isDayOff(day);
                                                    
                                                    return (
                                                        <td key={day} className="border p-2 text-center w-12">
                                                            <TooltipProvider>
                                                                <Tooltip delayDuration={0}>
                                                                    <TooltipTrigger asChild>
                                                                        <div 
                                                                            className="cursor-pointer flex justify-center items-center h-6 hover:bg-gray-100 rounded"
                                                                            onClick={() => {
                                                                                if (attendance && auth.user?.permissions?.includes('edit-attendances')) {
                                                                                    openModal('edit', attendance);
                                                                                }
                                                                            }}
                                                                        >
                                                                            {getAttendanceIcon(attendance, leave, holiday, dayOff)}
                                                                        </div>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <div className="text-xs">
                                                                            {holiday && (
                                                                                <>
                                                                                    <p><strong>Holiday:</strong> {holiday.name}</p>
                                                                                    <p><strong>Date:</strong> {formatDate(holiday.start_date)} - {formatDate(holiday.end_date)}</p>
                                                                                </>
                                                                            )}
                                                                            {leave && !holiday && (
                                                                                <>
                                                                                    <p><strong>On Leave</strong></p>
                                                                                    <p><strong>Type:</strong> {leave.leave_type?.name || 'Leave'}</p>
                                                                                    <p><strong>Date:</strong> {formatDate(leave.start_date)} - {formatDate(leave.end_date)}</p>
                                                                                </>
                                                                            )}
                                                                            {dayOff && !leave && !holiday && (
                                                                                <p><strong>Day Off</strong> (Non-working day)</p>
                                                                            )}
                                                                            {attendance && !leave && !holiday && !dayOff && (
                                                                                <>
                                                                                    <p><strong>Date:</strong> {formatDate(attendance.date)}</p>
                                                                                    <p><strong>Status:</strong> {attendance.status}</p>
                                                                                    {attendance.is_pending && (
                                                                                        <p className="text-orange-500"><strong>Status:</strong> Pending (No Clock Out)</p>
                                                                                    )}
                                                                                    {attendance.is_late && (
                                                                                        <p className="text-red-500"><strong>Late Entry</strong></p>
                                                                                    )}
                                                                                    {attendance.is_early && (
                                                                                        <p className="text-green-500"><strong>Early Exit</strong></p>
                                                                                    )}
                                                                                    <p><strong>Clock In:</strong> {attendance.clock_in ? formatTime(attendance.clock_in) : '-'}</p>
                                                                                    <p><strong>Clock Out:</strong> {attendance.clock_out ? formatTime(attendance.clock_out) : '-'}</p>
                                                                                    <p><strong>Total Hours:</strong> {attendance.total_hour || 0}h</p>
                                                                                    {attendance.overtime_hours > 0 && (
                                                                                        <p><strong>Overtime:</strong> {attendance.overtime_hours}h</p>
                                                                                    )}
                                                                                    {auth.user?.permissions?.includes('edit-attendances') && (
                                                                                        <p className="mt-2 text-blue-500">Click to edit</p>
                                                                                    )}
                                                                                </>
                                                                            )}
                                                                            {!attendance && !leave && !holiday && !dayOff && (
                                                                                <p>No record</p>
                                                                            )}
                                                                        </div>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            </TooltipProvider>
                                                        </td>
                                                    );
                                                })}
                                                <td className="border p-2 text-center sticky right-0 bg-white z-10 w-16">
                                                    <div className="font-semibold text-green-600">{calculateMonthlyTotal(employee.id)}</div>
                                                    <div className="text-[10px] text-gray-500">
                                                        /{getDaysInMonth(currentMonth).length}
                                                    </div>
                                                </td>
                                            </tr>
                                        )))
                                        }
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <Edit
                        attendance={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
                {modalState.mode === 'view' && modalState.data && (
                    <View
                        attendance={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>



            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Attendance')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}