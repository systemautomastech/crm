import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileCheck } from 'lucide-react';
import { Acknowledgment } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    acknowledgment: Acknowledgment;
}

export default function View({ acknowledgment }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileCheck className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Acknowledgment Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{acknowledgment.document?.title}</p>
                    </div>
                </div>
            </DialogHeader>
            
            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Employee')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{acknowledgment.employee?.name || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Assigned By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{acknowledgment.assigned_by?.name || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Acknowledged At')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{acknowledgment.acknowledged_at ? formatDate(acknowledgment.acknowledged_at) : '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-sm ${
                                acknowledgment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                acknowledgment.status === 'acknowledged' ? 'bg-green-100 text-green-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(acknowledgment.status === 'pending' ? 'Pending' : acknowledgment.status === 'acknowledged' ? 'Acknowledged' : acknowledgment.status || '-')}
                            </span>
                        </div>
                    </div>
                </div>
                
                {acknowledgment.acknowledgment_note && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Acknowledgment Note')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{acknowledgment.acknowledgment_note}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}