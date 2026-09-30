import { useTranslation } from 'react-i18next';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { User, Calendar, DollarSign, Calculator } from 'lucide-react';
import { usePageButtons } from '@/hooks/usePageButtons';

interface PayrollEntry {
    id: number;
    employee: {
        id: number;
        name: string;
        email: string;
        user: {
            name: string;
            email: string;
        };
    };
    basic_salary: number;
    total_allowances: number;
    total_manual_overtimes: number;
    total_deductions: number;
    total_loans: number;
    gross_pay: number;
    net_pay: number;
    attendance_overtime_amount: number;
    attendance_overtime_rate: number;
    working_days: number;
    present_days: number;
    half_days: number;
    absent_days: number;
    paid_leave_days: number;
    unpaid_leave_days: number;
    manual_overtime_hours: number;
    attendance_overtime_hours: number;
    overtime_hours: number;
    per_day_salary: number;
    unpaid_leave_deduction: number;
    half_day_deduction: number;
    absent_day_deduction: number;
    allowances_breakdown: Record<string, number>;
    deductions_breakdown: Record<string, number>;
    manual_overtimes_breakdown: Record<string, number>;
    loans_breakdown: Record<string, number>;
}

interface Payroll {
    id: number;
    title: string;
    payroll_frequency: string;
    pay_period_start: string;
    pay_period_end: string;
    pay_date: string;
    status: string;
}

interface PayslipModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    payrollEntry: PayrollEntry | null;
    payroll: Payroll;
}

export function PayslipModal({ open, onOpenChange, payrollEntry, payroll }: PayslipModalProps) {
    const { t } = useTranslation();
    
    const signaturePrintButtons = usePageButtons('signaturePrintBtn', {
        invoice: payroll,
        invoiceType: 'payroll'
    });

    if (!payrollEntry) return null;

    const employeeName = payrollEntry.employee?.user?.name || payrollEntry.employee?.name;
    const employeeEmail = payrollEntry.employee?.user?.email || payrollEntry.employee?.email;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl max-h-[90vh] overflow-hidden">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 pb-4">
                        <User className="h-5 w-5" />
                        {t('Payslip')} - {employeeName}
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-6 overflow-y-auto max-h-[calc(90vh-8rem)]">
                    {/* Header Info */}
                    <div className="bg-white p-4 border rounded-lg">
                        <div className="grid grid-cols-2 gap-6">
                            <div>
                                <div className="space-y-1 text-sm">
                                    <p className="font-bold">{employeeName}</p>
                                    <p>{employeeEmail}</p>
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="space-y-1 text-sm">
                                    <p className="font-medium">{payroll.title}</p>
                                    <p>{formatDate(payroll.pay_period_start)} - {formatDate(payroll.pay_period_end)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {/* Attendance Summary */}
                    <div className="bg-white border rounded-lg p-4">
                        <h3 className="font-semibold text-gray-700 mb-4">{t('Attendance Summary')}</h3>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div className="text-center p-2 bg-blue-50 rounded">
                                <p className="text-sm text-gray-600">{t('Working Days')}</p>
                                <p className="text-lg font-bold text-blue-600">{payrollEntry.working_days}</p>
                            </div>
                            <div className="text-center p-2 bg-green-50 rounded">
                                <p className="text-sm text-gray-600">{t('Present Days')}</p>
                                <p className="text-lg font-bold text-green-600">{payrollEntry.present_days}</p>
                            </div>
                            <div className="text-center p-2 bg-yellow-50 rounded">
                                <p className="text-sm text-gray-600">{t('Half Days')}</p>
                                <p className="text-lg font-bold text-yellow-600">{payrollEntry.half_days}</p>
                            </div>
                            <div className="text-center p-2 bg-red-50 rounded">
                                <p className="text-sm text-gray-600">{t('Absent Days')}</p>
                                <p className="text-lg font-bold text-red-600">{payrollEntry.absent_days}</p>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="text-center p-2 bg-purple-50 rounded">
                                <p className="text-sm text-gray-600">{t('Manual OT Hours')}</p>
                                <p className="text-lg font-bold text-purple-600">{payrollEntry.manual_overtime_hours}</p>
                            </div>
                            <div className="text-center p-2 bg-indigo-50 rounded">
                                <p className="text-sm text-gray-600">{t('Attendance OT Hours')}</p>
                                <p className="text-lg font-bold text-indigo-600">{payrollEntry.attendance_overtime_hours}</p>
                            </div>
                        </div>
                    </div>
                    {/* Pay Formula Summary */}
                    <div className="bg-blue-50 dark:bg-blue-900/20  rounded-lg p-3">

                        <div className="space-y-2">
                            <div className="text-xs">
                                <span className="font-semibold text-blue-800">{t('Gross Pay Formula')} :</span>
                                <span className="text-blue-800 ml-2 font-mono">{t('Total Earnings (Basic Salary + Allowances + Overtimes) - Total Leave Deductions')}</span>
                            </div>
                            <div className="text-xs">
                                <span className="font-semibold text-blue-800">{t('Net Pay Formula')} :</span>
                                <span className="text-blue-800 ml-2 font-mono">{t('Gross Pay - Total Deductions')}</span>
                            </div>
                        </div>
                    </div>
                    {/* Main Payslip Table */}
                    <div className="bg-white border rounded-lg overflow-hidden">
                        <div className="grid grid-cols-2 gap-0">
                            {/* Earnings Section */}
                            <div className="border-r">
                                <div className="bg-green-50 px-4 py-3 border-b">
                                    <h3 className="font-semibold text-green-700">{t('Earnings')}</h3>
                                </div>
                                <div className="p-4 space-y-3">
                                    <div className="flex justify-between py-2 border-b border-gray-100">
                                        <span className="text-sm font-semibold">{t('Basic Salary')}</span>
                                        <span className="font-medium">{formatCurrency(payrollEntry.basic_salary)}</span>
                                    </div>

                                    {/* Allowances with breakdown */}
                                    {payrollEntry.total_allowances > 0 && (
                                        <div className="space-y-2">
                                            <div className="flex justify-between py-2 border-b border-gray-200 font-medium">
                                                <span className="text-sm">{t('Allowances')}</span>
                                                <span>{formatCurrency(payrollEntry.total_allowances)}</span>
                                            </div>
                                            {Object.entries(payrollEntry.allowances_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between py-1 text-xs text-gray-600 ml-4">
                                                    <span>• {name}</span>
                                                    <span>{formatCurrency(amount)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    {/* Manual Overtime with breakdown */}
                                    {payrollEntry.total_manual_overtimes > 0 && (
                                        <div className="space-y-2">
                                            <div className="flex justify-between py-2 border-b border-gray-200 font-medium">
                                                <span className="text-sm">{t('Manual Overtime')}</span>
                                                <span>{formatCurrency(payrollEntry.total_manual_overtimes)}</span>
                                            </div>
                                            {Object.entries(payrollEntry.manual_overtimes_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between py-1 text-xs text-gray-600 ml-4">
                                                    <span>• {name}</span>
                                                    <span>{formatCurrency(amount)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    {payrollEntry.attendance_overtime_amount > 0 && (
                                        <div className="flex justify-between py-2 border-b border-gray-100 font-medium">
                                            <span className="text-sm">{t('Attendance Overtime')}</span>
                                            <span>{formatCurrency(payrollEntry.attendance_overtime_amount)}</span>
                                        </div>
                                    )}

                                    <div className="flex justify-between py-3 bg-green-50 px-3 rounded font-semibold text-green-700">
                                        <span>{t('Total Earnings')}</span>
                                        <span>{formatCurrency(
                                            Number(payrollEntry.basic_salary) +
                                            Number(payrollEntry.total_allowances || 0) +
                                            Number(payrollEntry.total_manual_overtimes || 0) +
                                            Number(payrollEntry.attendance_overtime_amount || 0)
                                        )}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Deductions Section */}
                            <div>
                                <div className="bg-red-50 px-4 py-3 border-b">
                                    <h3 className="font-semibold text-red-700">{t('Deductions')}</h3>
                                </div>
                                <div className="p-4 space-y-3">
                                    <div className="flex justify-between py-2 border-b border-gray-100">
                                        <span className="text-sm">{t('Paid Leave')} ({payrollEntry.unpaid_leave_days} {t('days')})</span>
                                        <span className="text-gray-600">{t('No deduction')}</span>
                                    </div>
                                    <div className="flex justify-between py-2 border-b border-gray-100">
                                        <span className="text-sm">{t('Unpaid Leave')} ({payrollEntry.unpaid_leave_days} {t('days')})</span>
                                        <span className="font-medium text-red-600">{formatCurrency(payrollEntry.unpaid_leave_deduction)}</span>
                                    </div>

                                    <div className="flex justify-between py-2 border-b border-gray-100">
                                        <span className="text-sm">{t('Half Days')} ({payrollEntry.half_days} {t('days')})</span>
                                        <span className="font-medium text-red-600">{formatCurrency(payrollEntry.half_day_deduction)}</span>
                                    </div>

                                    <div className="flex justify-between py-2 border-b border-gray-100">
                                        <span className="text-sm">{t('Absent Days')} ({payrollEntry.absent_days} {t('days')})</span>
                                        <span className="font-medium text-red-600">{formatCurrency(payrollEntry.unpaid_leave_deduction)}</span>

                                    </div>

                                    {/* Total Leave Deductions */}
                                    {(payrollEntry.unpaid_leave_deduction > 0 || payrollEntry.half_day_deduction > 0 || payrollEntry.absent_day_deduction > 0) && (
                                        <div className="flex justify-between py-3 bg-red-50 px-3 rounded font-semibold text-red-700">
                                            <span>{t('Total Leave Deductions')}</span>
                                            <span>{formatCurrency(
                                                Number(payrollEntry.unpaid_leave_deduction) +
                                                Number(payrollEntry.half_day_deduction) +
                                                Number(payrollEntry.absent_day_deduction)
                                            )}</span>
                                        </div>
                                    )}

                                    {/* Other Deductions with breakdown */}
                                    {payrollEntry.total_deductions > 0 && (
                                        <div className="space-y-2">
                                            <div className="flex justify-between py-2 border-b border-gray-200 font-medium">
                                                <span className="text-sm">{t('Other Deductions')}</span>
                                                <span>{formatCurrency(payrollEntry.total_deductions)}</span>
                                            </div>
                                            {Object.entries(payrollEntry.deductions_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between py-1 text-xs text-gray-600 ml-4">
                                                    <span>• {name}</span>
                                                    <span className="text-red-600">{formatCurrency(amount)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    {/* Loans with breakdown */}
                                    {payrollEntry.total_loans > 0 && (
                                        <div className="space-y-2">
                                            <div className="flex justify-between py-2 border-b border-gray-200 font-medium">
                                                <span className="text-sm">{t('Loans')}</span>
                                                <span>{formatCurrency(payrollEntry.total_loans)}</span>
                                            </div>
                                            {Object.entries(payrollEntry.loans_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between py-1 text-xs text-gray-600 ml-4">
                                                    <span>• {name}</span>
                                                    <span className="text-red-600">{formatCurrency(amount)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    <div className="flex justify-between py-3 bg-red-50 px-3 rounded font-semibold text-red-700">
                                        <span>{t('Total Deductions')}</span>
                                        <span>{formatCurrency(
                                            Number(payrollEntry.total_deductions || 0) +
                                            Number(payrollEntry.total_loans || 0)
                                        )}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Gross Pay and Net Pay Section */}
                        <div className="px-5 py-4 border-t-2 border-gray-200 space-y-3">
                            <div className="bg-green-50 px-4 py-3 rounded-lg">
                                <div className="flex justify-between items-center">
                                    <div className="flex items-center gap-2">
                                        <span className="text-lg font-bold text-green-800">{t('Gross Pay')}</span>
                                        <span className="text-xs text-green-600">
                                            ( {formatCurrency(
                                                Number(payrollEntry.basic_salary) +
                                                Number(payrollEntry.total_allowances || 0) +
                                                Number(payrollEntry.total_manual_overtimes || 0) +
                                                Number(payrollEntry.attendance_overtime_amount || 0)
                                            )} - {formatCurrency(
                                                Number(payrollEntry.unpaid_leave_deduction || 0) +
                                                Number(payrollEntry.half_day_deduction || 0) +
                                                Number(payrollEntry.absent_day_deduction || 0)
                                            )} )
                                        </span>
                                    </div>
                                    <span className="text-lg font-bold text-green-800">{formatCurrency(payrollEntry.gross_pay)}</span>
                                </div>
                            </div>
                            <div className="bg-blue-50 px-4 py-3 rounded-lg">
                                <div className="flex justify-between items-center">
                                    <div className="flex items-center gap-2">
                                        <span className="text-lg font-bold text-blue-800">{t('Net Pay')}</span>
                                        <span className="text-xs text-blue-600">
                                            ( {formatCurrency(payrollEntry.gross_pay)} - {formatCurrency(
                                                Number(payrollEntry.total_deductions || 0) +
                                                Number(payrollEntry.total_loans || 0)
                                            )} )
                                        </span>
                                    </div>
                                    <span className="text-lg font-bold text-blue-800">{formatCurrency(payrollEntry.net_pay)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {/* Signature */}
                    {signaturePrintButtons.length > 0 && signaturePrintButtons.map((button) => (
                        <div key={button.id}>{button.component}</div>
                    ))}
                </div>
            </DialogContent>
        </Dialog>
    );
}