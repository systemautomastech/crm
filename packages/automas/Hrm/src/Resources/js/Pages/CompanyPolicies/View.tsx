import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileText, Building } from 'lucide-react';
import { CompanyPolicy } from './types';
import { getImagePath } from '@/utils/helpers';

interface ViewProps {
    companyPolicy: CompanyPolicy;
}

export default function View({ companyPolicy }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Company Policy Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Title')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{companyPolicy.title || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Branch')}</label>
                        <div className="flex items-center gap-2 text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            {companyPolicy.branch?.branch_name || '-'}
                        </div>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded whitespace-pre-wrap">{companyPolicy.description || '-'}</p>
                </div>
            </div>
        </DialogContent>
    );
}
