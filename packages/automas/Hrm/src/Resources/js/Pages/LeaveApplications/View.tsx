import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileText, Calendar, User, Clock, CheckCircle, MessageSquare, Tag } from 'lucide-react';
import { LeaveApplication } from './types';
import { formatDate, formatDateTime } from '@/utils/helpers';

interface ViewProps {
    leaveapplication: LeaveApplication;
}

export default function View({ leaveapplication }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Leave Application Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{leaveapplication.employee?.name || 'Unknown Employee'}</p>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Leave Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.leave_type?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Total Days')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.total_days || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.start_date ? formatDate(leaveapplication.start_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.end_date ? formatDate(leaveapplication.end_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.approved_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved At')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.approved_at ? formatDateTime(leaveapplication.approved_at) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Is Paid')}</label>
                        <div className="p-1 rounded">
                            <span className={`inline-block px-2 py-1 rounded-full font-medium text-xs ${
                                leaveapplication.leave_type?.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }`}>
                                {leaveapplication.leave_type?.is_paid ? t('Paid') : t('Unpaid')}
                            </span>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`inline-block px-2 py-1 rounded-full font-medium text-xs ${
                                leaveapplication.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                leaveapplication.status === 'approved' ? 'bg-green-100 text-green-800' :
                                leaveapplication.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(leaveapplication.status?.charAt(0).toUpperCase() + leaveapplication.status?.slice(1) || 'Unknown')}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Reason')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.reason || '-'}</p>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Approver Comment')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leaveapplication.approver_comment || '-'}</p>
                </div>
            </div>
        </DialogContent>
    );
}