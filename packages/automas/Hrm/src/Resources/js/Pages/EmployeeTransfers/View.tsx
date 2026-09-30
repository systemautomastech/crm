import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { ArrowRight, User, Building, Users, Calendar, FileText, Download } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmployeeTransfer } from './types';
import { formatDate, getImagePath } from '@/utils/helpers';

interface ViewProps {
    employeetransfer: EmployeeTransfer;
}

export default function View({ employeetransfer }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <User className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Employee Transfer Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{employeetransfer.employee?.name}</p>
                    </div>
                </div>
            </DialogHeader>
            <div className="overflow-y-auto flex-1 p-6 space-y-6">
                {/* Transfer Path Visualization */}
                <div className="bg-blue-50 p-4 rounded-lg">
                    {/* Transfer Summary */}
                    <div className="text-center mb-2">
                        <p className="text-lg font-semibold text-blue-800">
                            {employeetransfer.from_branch?.branch_name || '-'} → {employeetransfer.to_branch?.branch_name || '-'}
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        {/* From */}
                        <div className="text-center">
                            <div className="bg-white p-4 rounded-lg border-2 border-red-200">
                                <Building className="h-8 w-8 mx-auto mb-2 text-red-600" />
                                <h5 className="font-semibold text-red-800">{t('From')}</h5>
                                <div className="mt-2 space-y-1 text-sm">
                                    <p><strong>{t('Branch')}:</strong> {employeetransfer.from_branch?.branch_name || '-'}</p>
                                    <p><strong>{t('Department')}:</strong> {employeetransfer.from_department?.department_name || '-'}</p>
                                    <p><strong>{t('Designation')}:</strong> {employeetransfer.from_designation?.designation_name || '-'}</p>
                                </div>
                            </div>
                        </div>

                        {/* Arrow */}
                        <div className="flex justify-center">
                            <ArrowRight className="h-8 w-8 text-blue-600" />
                        </div>

                        {/* To */}
                        <div className="text-center">
                            <div className="bg-white p-4 rounded-lg border-2 border-green-200">
                                <Building className="h-8 w-8 mx-auto mb-2 text-green-600" />
                                <h5 className="font-semibold text-green-800">{t('To')}</h5>
                                <div className="mt-2 space-y-1 text-sm">
                                    <p><strong>{t('Branch')}:</strong> {employeetransfer.to_branch?.branch_name || '-'}</p>
                                    <p><strong>{t('Department')}:</strong> {employeetransfer.to_department?.department_name || '-'}</p>
                                    <p><strong>{t('Designation')}:</strong> {employeetransfer.to_designation?.designation_name || '-'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Transfer Details */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Transfer Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{employeetransfer.transfer_date ? formatDate(employeetransfer.transfer_date) : '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Effective Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{employeetransfer.effective_date ? formatDate(employeetransfer.effective_date) : '-'}</p>
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{employeetransfer.approved_by?.name || '-'}</p>
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-sm font-medium ${
                                employeetransfer.status?.toLowerCase() === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                employeetransfer.status?.toLowerCase() === 'approved' ? 'bg-green-100 text-green-800' :
                                employeetransfer.status?.toLowerCase() === 'in progress' ? 'bg-blue-100 text-blue-800' :
                                employeetransfer.status?.toLowerCase() === 'completed' ? 'bg-green-100 text-green-800' :
                                employeetransfer.status?.toLowerCase() === 'rejected' ? 'bg-red-100 text-red-800' :
                                employeetransfer.status?.toLowerCase() === 'cancelled' ? 'bg-gray-100 text-gray-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(employeetransfer.status?.toLowerCase() === 'in progress' ? 'In Progress' : (employeetransfer.status?.charAt(0).toUpperCase() + employeetransfer.status?.slice(1) || '-'))}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Reason')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{employeetransfer.reason || '-'}</p>
                </div>
            </div>
        </DialogContent>
    );
}