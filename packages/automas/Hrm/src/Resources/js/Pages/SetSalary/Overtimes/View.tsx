import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Clock } from 'lucide-react';
import { formatCurrency, formatDate } from '@/utils/helpers';

interface Overtime {
    id: number;
    title: string;
    total_days: number;
    hours: number;
    rate: number;
    start_date?: string;
    end_date?: string;
    notes?: string;
    status: string;
}

interface ViewOvertimeProps {
    overtime: Overtime;
}

export default function View({ overtime }: ViewOvertimeProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Clock className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Overtime Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{overtime.title}</p>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Title')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{overtime.title || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Total Days')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{overtime.total_days || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Hours')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{overtime.hours || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Rate')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{formatCurrency(overtime.rate) || '0'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{formatDate(overtime.start_date) || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{formatDate(overtime.end_date) || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-2 rounded">
                            <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                overtime.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }`}>
                                {t(overtime.status === 'active' ? 'Active' : 'Expired')}
                            </span>
                        </div>
                    </div>
                </div>
                
                {overtime.notes && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Notes')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{overtime.notes}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}