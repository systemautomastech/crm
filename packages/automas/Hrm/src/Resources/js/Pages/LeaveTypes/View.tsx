import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Calendar, FileText, Palette, DollarSign, Hash } from 'lucide-react';
import { LeaveType } from './types';

interface ViewProps {
    leavetype: LeaveType;
}

export default function View({ leavetype }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Calendar className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Leave Type Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>
            
            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leavetype.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Max Days Per Year')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leavetype.max_days_per_year || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Color')}</label>
                        <div className="p-1 rounded flex items-center gap-2">
                            <div 
                                className="w-6 h-6 rounded border border-gray-300" 
                                style={{ backgroundColor: leavetype.color || '#FF6B6B' }}
                            ></div>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Is Paid')}</label>
                        <div className="p-1 rounded">
                            <span className={`inline-block px-2 py-1 rounded-full font-medium text-xs ${
                                leavetype.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }`}>
                                {leavetype.is_paid ? t('Paid') : t('Unpaid')}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{leavetype.description || '-'}</p>
                </div>
            </div>
        </DialogContent>
    );
}