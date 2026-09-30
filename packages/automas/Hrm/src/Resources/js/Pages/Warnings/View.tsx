import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { AlertOctagon } from 'lucide-react';
import { formatDate } from '@/utils/helpers';

interface WarningViewProps {
    warning: any;
    onClose: () => void;
}

export default function WarningView({ warning, onClose }: WarningViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <AlertOctagon className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Warning Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.employee?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Warning By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.warning_by?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Warning Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.warning_type?.warning_type_name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Warning Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.warning_date ? formatDate(warning.warning_date) : '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Severity')}</label>
                        <p className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                warning.severity === 'Minor' ? 'bg-green-100 text-green-800' :
                                warning.severity === 'Moderate' ? 'bg-yellow-100 text-yellow-800' :
                                warning.severity === 'Major' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(warning.severity || '-')}
                            </span>
                        </p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <p className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                warning.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                warning.status === 'approved' ? 'bg-green-100 text-green-800' :
                                warning.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(warning.status ? warning.status.charAt(0).toUpperCase() + warning.status.slice(1) : 'Pending')}
                            </span>
                        </p>
                    </div>
                </div>
                
                {warning.subject && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Subject')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.subject}</p>
                    </div>
                )}
                
                {warning.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.description}</p>
                    </div>
                )}
                
                {warning.employee_response && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee Response')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{warning.employee_response}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}