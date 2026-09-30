import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { DollarSign } from 'lucide-react';
import { formatCurrency, formatDate } from '@/utils/helpers';

interface Loan {
    id: number;
    title: string;
    loan_type_id: number;
    type: string;
    amount: number;
    start_date?: string;
    end_date?: string;
    reason?: string;
    loan_type?: {
        name: string;
    };
}

interface ViewLoanProps {
    loan: Loan;
}

export default function View({ loan }: ViewLoanProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <DollarSign className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Loan Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Title')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{loan.title || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Loan Type')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{loan.loan_type?.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Type')}</label>
                        <div className="rounded">
                            <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                loan.type === 'fixed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                            }`}>
                                {t(loan.type === 'fixed' ? 'Fixed' : 'Percentage')}
                            </span>
                        </div>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Amount')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            {loan.type === 'fixed' 
                                ? formatCurrency(loan.amount) || '0'
                                : `${loan.amount || '0'}%`
                            }
                        </p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{formatDate(loan.start_date) || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{formatDate(loan.end_date) || '-'}</p>
                    </div>
                </div>
                
                {loan.reason && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Reason')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{loan.reason}</p>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}