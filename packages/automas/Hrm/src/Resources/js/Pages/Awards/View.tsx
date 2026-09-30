import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Tag } from 'lucide-react';
import { Award } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewAwardProps {
    award: Award;
}

export default function View({ award }: ViewAwardProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Tag className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Award Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{award.employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Award Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{award.award_date ? formatDate(award.award_date) : '-'}</p>
                    </div>
                </div>
                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Award Type')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{award.award_type?.name || '-'}</p>
                </div>

                {award.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{award.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}