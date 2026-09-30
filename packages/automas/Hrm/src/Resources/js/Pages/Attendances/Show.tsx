import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Clock } from 'lucide-react';
import { Attendance } from './types';
import { formatDate, formatTime, formatDateTime, formatCurrency } from '@/utils/helpers';

interface ViewAttendanceProps {
    attendance: Attendance;
    onSuccess: () => void;
}

export default function View({ attendance, onSuccess }: ViewAttendanceProps) {
    const { t } = useTranslation();

    const formatStatus = (status: string) => {
        return status.split(' ').map(word =>
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    };

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Clock className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Attendance Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.user?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Shift')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.shift?.shift_name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Clock In Time')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.clock_in ? formatDateTime(attendance.clock_in) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Clock Out Time')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.clock_out ? formatDateTime(attendance.clock_out) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Break Hours')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.break_hour ? `${attendance.break_hour}h` : '0h'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Total Hours')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.total_hour ? `${attendance.total_hour}h` : '0h'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Overtime Hours')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.overtime_hours ? `${attendance.overtime_hours}h` : '0h'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Overtime Amount')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            {attendance.overtime_amount ? formatCurrency(attendance.overtime_amount) : 0}
                        </p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.date ? formatDate(attendance.date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="rounded">
                            <span className={`px-2 py-1 rounded-full text-xs ${attendance.status === 'present' ? 'bg-green-100 text-green-800 text-sm font-medium' :
                                        attendance.status === 'half day' ? 'bg-yellow-100 text-yellow-800 text-xs font-medium' :
                                        attendance.status === 'absent' ? 'bg-red-100 text-red-800 text-sm font-medium' :
                                            'bg-gray-100 text-gray-800'
                                }`}>
                                {formatStatus(attendance.status || 'Unknown')}
                            </span>
                        </div>
                    </div>
                </div>

                {attendance.notes && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Notes')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{attendance.notes}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}