import { useTranslation } from 'react-i18next';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { MessageSquareWarning } from 'lucide-react';
import { Complaint } from './types';
import { formatDate, getImagePath } from '@/utils/helpers';

interface ShowComplaintProps {
    complaint: Complaint;
    onClose: () => void;
}

export default function Show({ complaint, onClose }: ShowComplaintProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <MessageSquareWarning className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Complaint Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Against Employee')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.against_employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Resolved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.resolved_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-sm ${complaint.status?.toLowerCase() === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                complaint.status?.toLowerCase() === 'in review' ? 'bg-blue-100 text-blue-800' :
                                    complaint.status?.toLowerCase() === 'assigned' ? 'bg-purple-100 text-purple-800' :
                                        complaint.status?.toLowerCase() === 'in progress' ? 'bg-orange-100 text-orange-800' :
                                            complaint.status?.toLowerCase() === 'resolved' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'
                                }`}>
                                {t(complaint.status?.toLowerCase() === 'in review' ? 'In Review' : complaint.status?.toLowerCase() === 'in progress' ? 'In Progress' : (complaint.status?.charAt(0).toUpperCase() + complaint.status?.slice(1) || '-'))}
                            </span>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Complaint Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.complaint_date ? formatDate(complaint.complaint_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Resolution Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.resolution_date ? formatDate(complaint.resolution_date) : '-'}</p>
                    </div>
                </div>

                {complaint.subject && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Subject')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.subject}</p>
                    </div>
                )}

                {complaint.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{complaint.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}