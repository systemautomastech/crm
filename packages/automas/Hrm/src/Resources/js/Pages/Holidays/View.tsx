import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Calendar } from 'lucide-react';
import { Holiday } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    holiday: Holiday;
}

export default function View({ holiday }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Calendar className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Holiday Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Holiday Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{holiday.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Holiday Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{holiday.holiday_type?.holiday_type || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{holiday.start_date ? formatDate(holiday.start_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{holiday.end_date ? formatDate(holiday.end_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Paid')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-sm font-medium ${
                                holiday.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }`}>
                                {holiday.is_paid ? t('Yes') : t('No')}
                            </span>
                        </div>
                    </div>
                </div>

                {holiday.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{holiday.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}