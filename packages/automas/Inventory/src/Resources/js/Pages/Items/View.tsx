import { useTranslation } from 'react-i18next';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { ViewProps } from './types';

export default function View({ onSuccess, item, data: itemData }: ViewProps) {
    const { t } = useTranslation();
    const viewItem = item || itemData;
    

    return (
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{t('Inventory Item Details')}</DialogTitle>
            </DialogHeader>
            <div className="py-6">
                <div className="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-lg mb-6">
                    <Label className="text-xs text-gray-500 uppercase tracking-wide">{t('Product Information')}</Label>
                    <h3 className="text-2xl font-bold text-gray-900 mt-2">{viewItem?.product?.name}</h3>
                    <p className="text-sm text-gray-600 mt-1">{t('SKU')}: {viewItem?.product?.sku}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div className="bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                        <Label className="text-xs text-gray-500 uppercase tracking-wide">{t('Reorder Level')}</Label>
                        <p className="text-3xl font-bold text-blue-600 mt-2">{viewItem?.reorder_level}</p>
                    </div>

                    <div className="bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                        <Label className="text-xs text-gray-500 uppercase tracking-wide">{t('Maximum Level')}</Label>
                        <p className="text-3xl font-bold text-green-600 mt-2">{viewItem?.maximum_level}</p>
                    </div>

                    <div className="bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                        <Label className="text-xs text-gray-500 uppercase tracking-wide">{t('Valuation Method')}</Label>
                        <p className="text-sm font-semibold text-gray-900 mt-2 break-words">{viewItem?.valuation_method?.toUpperCase()}</p>
                    </div>

                    <div className="bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                        <Label className="text-xs text-gray-500 uppercase tracking-wide">{t('Track Inventory')}</Label>
                        <div className="mt-2">
                            <span className={`inline-flex px-3 py-1 rounded-full text-sm font-medium ${
                                viewItem?.is_tracked ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }`}>
                                {viewItem?.is_tracked ? t('Yes') : t('No')}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    );
}
