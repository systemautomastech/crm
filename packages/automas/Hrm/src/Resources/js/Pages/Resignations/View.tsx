import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Tag } from 'lucide-react';
import { Resignation } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    resignation: Resignation;
}

export default function View({ resignation }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Tag className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Resignation Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{resignation.employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Last Working Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{resignation.last_working_date ? formatDate(resignation.last_working_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{resignation.approved_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <p className="p-1 rounded">
                            <span className={`inline-block px-2 py-1 rounded-full font-medium text-xs ${
                                resignation.status === 'accepted' ? 'bg-green-100 text-green-800' :
                                resignation.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-red-100 text-red-800'
                            }`}>
                                {resignation.status?.charAt(0).toUpperCase() + resignation.status?.slice(1) || 'Pending'}
                            </span>
                        </p>
                    </div>
                </div>
                
                {resignation.reason && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Reason')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{resignation.reason}</p>
                    </div>
                )}
                
                {resignation.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{resignation.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}