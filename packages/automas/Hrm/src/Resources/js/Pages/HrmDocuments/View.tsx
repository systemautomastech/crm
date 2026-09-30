import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileText } from 'lucide-react';
import { Document } from './types';
import { formatDate } from '@/utils/helpers';
import { usePage } from '@inertiajs/react';

interface ViewProps {
    document: Document;
}

export default function View({ document }: ViewProps) {
    const { t } = useTranslation();
    const { documentcategories, users } = usePage<any>().props;
    
    const documentCategory = documentcategories?.find((item: any) => item.id.toString() === document.document_category_id?.toString());

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Document Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{document.title}</p>
                    </div>
                </div>
            </DialogHeader>
            
            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Title')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.title || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Document Category')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.document_category?.document_type || documentCategory?.document_type || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Effective Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.effective_date ? formatDate(document.effective_date) : '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Uploaded By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.uploaded_by?.name || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.approved_by?.name || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                document.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                document.status === 'approve' ? 'bg-green-100 text-green-800' :
                                document.status === 'reject' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(document.status === 'pending' ? 'Pending' : document.status === 'approve' ? 'Approved' : document.status === 'reject' ? 'Rejected' : document.status)}
                            </span>
                        </div>
                    </div>
                </div>
                
                {document.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{document.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}