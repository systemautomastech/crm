import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Calendar } from 'lucide-react';
import { Event } from './types';
import { formatDate, formatTime } from '@/utils/helpers';

interface ViewProps {
    event: Event;
}

export default function View({ event }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Calendar className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Event Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Title')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.title || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Event Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.event_type?.event_type || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.start_date ? formatDate(event.start_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.end_date ? formatDate(event.end_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Time')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.start_time ? formatTime(event.start_time) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Time')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.end_time ? formatTime(event.end_time) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Location')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.location || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.approved_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                event.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                event.status === 'approved' ? 'bg-green-100 text-green-800' :
                                event.status === 'reject' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {event.status ? event.status.charAt(0).toUpperCase() + event.status.slice(1) : '-'}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Departments')}</label>
                    <div className="p-1 rounded">
                        {event.departments && event.departments.length > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {event.departments.map((dept: any) => (
                                    <span key={dept.id} className="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium">
                                        {dept.department_name}{dept.branch?.branch_name ? ` (${dept.branch.branch_name})` : ''}
                                    </span>
                                ))}
                            </div>
                        ) : (
                            <span className="text-sm text-gray-900">-</span>
                        )}
                    </div>
                </div>

                {event.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{event.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}