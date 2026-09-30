import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Label } from "@/components/ui/label";
import { useEffect, useState } from "react";
import axios from "axios";
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    adjustment: any;
}

export default function View({ adjustment }: ViewProps) {
    const { t } = useTranslation();
    const [data, setData] = useState<any>(null);

    useEffect(() => {
        axios.get(route('inventory.adjustments.show', adjustment.id))
            .then(response => {
                setData(response.data.adjustment);
            });
    }, [adjustment.id]);

    if (!data) return null;

    return (
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{t('Inventory Adjustment Details')}</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <Label>{t('Adjustment Number')}</Label>
                        <p className="text-sm mt-1">{data.adjustment_number}</p>
                    </div>
                    <div>
                        <Label>{t('Adjustment Date')}</Label>
                        <p className="text-sm mt-1">{formatDate(data.adjustment_date)}</p>
                    </div>
                    <div>
                        <Label>{t('Warehouse')}</Label>
                        <p className="text-sm mt-1">{data.warehouse?.name || '-'}</p>
                    </div>
                    <div>
                        <Label>{t('Adjustment Type')}</Label>
                        <p className="text-sm mt-1">{data.adjustment_type?.charAt(0).toUpperCase() + data.adjustment_type?.slice(1)}</p>
                    </div>                    
                    <div>
                        <Label>{t('Status')}</Label>
                        <div className="mt-1">
                            <span className={`inline-block px-2 py-1 rounded-full text-sm ${data.status === 'posted' ? 'bg-green-100 text-green-800' :
                                    data.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                        'bg-yellow-100 text-yellow-800'
                                }`}>
                                {data.status?.charAt(0).toUpperCase() + data.status?.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <Label>{t('Reason')}</Label>
                    <p className="text-sm mt-1">{data.reason || '-'}</p>
                </div>

                <div>
                    <Label>{t('Adjustment Items')}</Label>
                    <div className="mt-2 border rounded-lg overflow-hidden">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left">{t('Item')}</th>
                                    <th className="px-4 py-2 text-right">{t('Current Qty')}</th>
                                    <th className="px-4 py-2 text-right">{t('Adjusted Qty')}</th>
                                    <th className="px-4 py-2 text-right">{t('Difference')}</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                {data.items?.map((item: any, index: number) => (
                                    <tr key={index} className="border-t">
                                        <td className="px-4 py-2">{item.inventory_item?.product?.name || '-'}</td>
                                        <td className="px-4 py-2 text-right">{item.current_quantity}</td>
                                        <td className="px-4 py-2 text-right">{item.adjusted_quantity}</td>
                                        <td className="px-4 py-2 text-right">{item.difference_quantity}</td>                                      
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {data.approved_by && (
                    <div className="grid grid-cols-2 gap-4 pt-4 border-t">
                        <div>
                            <Label>{t('Approved By')}</Label>
                            <p className="text-sm mt-1">{data.approver?.name || '-'}</p>
                        </div>
                        <div>
                            <Label>{t('Approved At')}</Label>
                            <p className="text-sm mt-1">{data.approved_at ? formatDate(data.approved_at) : '-'}</p>
                        </div>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}
