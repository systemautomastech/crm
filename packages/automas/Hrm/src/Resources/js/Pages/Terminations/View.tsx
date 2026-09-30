import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { UserX } from 'lucide-react';
import { Termination } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    termination: Termination;
}

function View({ termination }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <UserX className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Termination Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Termination Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.termination_type?.termination_type || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Notice Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.notice_date ? formatDate(termination.notice_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Termination Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.termination_date ? formatDate(termination.termination_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.approved_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <p className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                termination.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                termination.status === 'approved' ? 'bg-green-100 text-green-800' :
                                termination.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(termination.status?.charAt(0).toUpperCase() + termination.status?.slice(1) || 'Pending')}
                            </span>
                        </p>
                    </div>
                </div>
                
                {termination.reason && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Reason')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.reason}</p>
                    </div>
                )}
                
                {termination.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{termination.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}

export default View;