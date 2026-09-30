import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Monitor } from 'lucide-react';

interface PosCounter {
    id: number;
    name: string;
    code: string;
    status: boolean;
    description?: string;
}

interface ViewCounterProps {
    counter: PosCounter;
}

export default function View({ counter }: ViewCounterProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Monitor className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Counter Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Counter Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{counter.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Counter Code')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{counter.code || '-'}</p>
                    </div>
                </div>
                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                    <p className="text-sm text-gray-900 p-2 rounded">
                        <span className={`px-2 py-1 rounded-full text-sm ${
                            counter.status === true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }`}>
                            {counter.status === true ? t('Active') : t('Inactive')}
                        </span>
                    </p>
                </div>

                {counter.description && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{counter.description}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}
