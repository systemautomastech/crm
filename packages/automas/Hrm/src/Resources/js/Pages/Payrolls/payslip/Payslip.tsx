import React, { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { formatCurrency, formatDate, getCompanySetting, getImagePath } from '@/utils/helpers';
import { usePageButtons } from '@/hooks/usePageButtons';
import { Printer, Download, ArrowLeft } from 'lucide-react';

interface Employee {
    id: number;
    employee_id?: string;
    name: string;
    email: string;
    bank_name?: string;
    account_number?: string;
    bank_identifier_code?: string;
    user?: {
        name: string;
        email: string;
    };
    designation?: {
        id?: number;
        designation_name?: string;
        name?: string;
    };
    department?: {
        id?: number;
        department_name?: string;
        name?: string;
    };
}

interface Payroll {
    id: number;
    title: string;
    pay_period_start: string;
    pay_period_end: string;
    pay_date: string;
}

interface PayrollEntry {
    id: number;
    employee: Employee;
    payroll: Payroll;
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
    allowances_breakdown?: Record<string, number>;
    deductions_breakdown?: Record<string, number>;
    manual_overtimes_breakdown?: Record<string, number>;
    loans_breakdown?: Record<string, number>;
}

interface PayslipProps {
    payrollEntry: PayrollEntry;
    companySettings?: Record<string, any>;
    companyAllSetting?: Record<string, any>;
}

/**
 * Convert numeric amount to words in Bangladeshi Taka style
 */
const numberToWords = (amount: number): string => {
    const num = Math.abs(Number(amount) || 0);
    if (num === 0) return 'Zero Taka Only';

    const integerPart = Math.floor(num);
    const decimalPart = Math.round((num - integerPart) * 100);

    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    const convertLessThanThousand = (n: number): string => {
        if (n === 0) return '';
        if (n < 20) return ones[n];
        if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
        return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + convertLessThanThousand(n % 100) : '');
    };

    const convertNumber = (n: number): string => {
        if (n === 0) return '';
        if (n < 1000) return convertLessThanThousand(n);
        if (n < 100000) {
            return convertLessThanThousand(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 ? ' ' + convertNumber(n % 1000) : '');
        }
        if (n < 10000000) {
            return convertLessThanThousand(Math.floor(n / 100000)) + ' Lakh' + (n % 100000 ? ' ' + convertNumber(n % 100000) : '');
        }
        return convertLessThanThousand(Math.floor(n / 10000000)) + ' Crore' + (n % 10000000 ? ' ' + convertNumber(n % 10000000) : '');
    };

    let result = convertNumber(integerPart).trim() + ' Taka';
    if (decimalPart > 0) {
        result += ' and ' + convertLessThanThousand(decimalPart).trim() + ' Poisha';
    }
    return result + ' Only';
};

export default function Payslip() {
    const { t } = useTranslation();
    const { payrollEntry, companySettings, companyAllSetting } = usePage<PayslipProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);

    const getSetting = (key: string, defaultValue: string = ''): string => {
        if (companySettings && companySettings[key] !== undefined && companySettings[key] !== null) {
            return String(companySettings[key]);
        }
        if (companyAllSetting && companyAllSetting[key] !== undefined && companyAllSetting[key] !== null) {
            return String(companyAllSetting[key]);
        }
        const val = getCompanySetting(key);
        if (val !== null && val !== undefined) {
            return String(val);
        }
        return defaultValue;
    };

    // Setting Toggles (stored as 'on' / 'off')
    const showLogo = getSetting('payslip_show_logo', 'on') !== 'off';
    const enableLetterhead = getSetting('payslip_enable_letterhead', 'off') === 'on';
    const showSignatures = getSetting('payslip_show_signatures', 'on') !== 'off';

    // Setting Values
    const customLogo = getSetting('payslip_logo', '');
    const bgLetterhead = getSetting('payslip_bg_letterhead', '');
    const hrSignature = getSetting('payslip_hr_signature', '');
    const hrName = getSetting('payslip_hr_name', '');
    const hrTitle = getSetting('payslip_hr_title', 'HR Manager / Authorized Signatory');
    const payslipNote = getSetting('payslip_note', 'This is a computer generated payslip and does not require signature.');

    // Image asset URLs
    const logoUrl = customLogo
        ? getImagePath(customLogo)
        : (getCompanySetting('company_logo') ? getImagePath(getCompanySetting('company_logo')) : '');

    const letterheadUrl = bgLetterhead ? getImagePath(bgLetterhead) : '';
    const hrSignatureUrl = hrSignature ? getImagePath(hrSignature) : '';

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1' || urlParams.get('autoprint') === '1' || urlParams.get('download') === 'pdf') {
            const timer = setTimeout(() => {
                window.print();
            }, 300);
            return () => clearTimeout(timer);
        }
    }, []);

    const handlePrint = () => {
        window.print();
    };

    const employeeName = payrollEntry.employee?.user?.name || payrollEntry.employee?.name || 'N/A';
    const employeeId = payrollEntry.employee?.employee_id || 'N/A';
    const designation = payrollEntry.employee?.designation?.designation_name || payrollEntry.employee?.designation?.name || 'N/A';
    const department = payrollEntry.employee?.department?.department_name || payrollEntry.employee?.department?.name || 'N/A';
    const bankName = payrollEntry.employee?.bank_name || 'N/A';
    const accountNumber = payrollEntry.employee?.account_number || 'N/A';

    // Earnings calculation
    const totalAllowances = Number(payrollEntry.total_allowances || 0);
    const totalManualOvertimes = Number(payrollEntry.total_manual_overtimes || 0);
    const attendanceOvertimeAmount = Number(payrollEntry.attendance_overtime_amount || 0);
    const totalEarnings = Number(payrollEntry.basic_salary || 0) + totalAllowances + totalManualOvertimes + attendanceOvertimeAmount;

    // Deductions calculation
    const unpaidLeaveDeduction = Number(payrollEntry.unpaid_leave_deduction || 0);
    const halfDayDeduction = Number(payrollEntry.half_day_deduction || 0);
    const absentDayDeduction = Number(payrollEntry.absent_day_deduction || 0);
    const totalLeaveDeductions = unpaidLeaveDeduction + halfDayDeduction + absentDayDeduction;

    const totalOtherDeductions = Number(payrollEntry.total_deductions || 0);
    const totalLoans = Number(payrollEntry.total_loans || 0);
    const totalDeductions = totalLeaveDeductions + totalOtherDeductions + totalLoans;

    return (
        <div className="min-h-screen bg-slate-100 py-6 px-4 print:py-0 print:px-0 print:bg-white flex flex-col items-center">
            <Head title={`${t('Payslip')} - ${employeeName}`} />

            {/* Top Toolbar (Hidden during print / PDF) */}
            <div className="w-full max-w-[210mm] mb-4 flex items-center justify-between no-print bg-white p-3 rounded-lg shadow-sm border border-slate-200">
                <button
                    onClick={() => window.history.back()}
                    className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors"
                >
                    <ArrowLeft className="h-4 w-4" />
                    {t('Back')}
                </button>

                <div className="flex items-center gap-3">
                    <button
                        onClick={handlePrint}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white rounded transition-colors"
                    >
                        <Printer className="h-3.5 w-3.5" />
                        {t('Print')}
                    </button>
                </div>
            </div>

            {/* Printable Payslip Container (Strict 1-Page A4 Dimensions) */}
            <div
                id="payslip-print-area"
                className="relative w-[210mm] h-[297mm] px-[12mm] py-[8mm] box-border bg-white font-sans text-slate-800 shadow-md print:shadow-none overflow-hidden">
                {/* Background Letterhead Image (Independent layer behind content) */}
                {enableLetterhead && letterheadUrl && (
                    <img
                        src={letterheadUrl}
                        alt="Letterhead Background"
                        className="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none opacity-85"
                    />
                )}

                {/* Foreground Content */}
                <div className="relative z-10 space-y-4">
                    {showLogo && logoUrl ? (
                        <img
                            src={logoUrl}
                            alt="Company Logo"
                            className="max-h-[1.2in] max-w-[220px] object-contain"
                        />
                    ) : (
                        <div></div>
                    )}
                    <div className="flex justify-between items-start min-h-[1.2in] pt-8 pb-5">
                        <div className="text-left">
                            <h1 className="text-lg leading-[1.2] font-bold text-slate-900 tracking-tight uppercase">
                                {getCompanySetting('company_name') || 'COMPANY NAME'}
                            </h1>
                            <p className="text-[11px] leading-[1.2] text-slate-500">
                                {getCompanySetting('company_address') || 'COMPANY ADDRESS'}
                            </p>
                            <p className="text-[11px] leading-[1.2] text-slate-500">
                                {t('Phone')}: {getCompanySetting('company_telephone') || '+880-96XXXXXXX'}
                            </p>
                            <p className="text-[11px] leading-[1.2] text-slate-500">
                                {t('Email')}: {getCompanySetting('company_email') || 'company@email.com'}
                            </p>
                        </div>

                        <div className="text-right">
                            <h1 className="text-lg leading-[1.2] font-bold text-slate-900 tracking-tight uppercase">
                                {t('Payslip')}
                            </h1>
                            <p className="text-xs leading-[1.2] font-semibold text-slate-700">
                                {payrollEntry.payroll.title}
                            </p>
                            <p className="text-[11px] leading-[1.2] text-slate-500">
                                {t('Pay Period')}: {formatDate(payrollEntry.payroll.pay_period_start)} - {formatDate(payrollEntry.payroll.pay_period_end)}
                            </p>
                            {payrollEntry.payroll.pay_date && (
                                <p className="text-[11px] leading-[1.2] text-slate-500">
                                    {t('Pay Date')}: {formatDate(payrollEntry.payroll.pay_date)}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Employee Details Grid */}
                    <div className="bg-white/95 rounded border border-slate-300 p-3 text-xs">
                        <div className="grid grid-cols-2 gap-x-6 gap-y-2">
                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Employee Name')}:</span>
                                <span className="font-semibold text-slate-900">{employeeName}</span>
                            </div>
                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Employee ID')}:</span>
                                <span className="font-semibold text-slate-900">{employeeId}</span>
                            </div>

                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Designation')}:</span>
                                <span className="font-semibold text-slate-900">{designation}</span>
                            </div>
                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Department')}:</span>
                                <span className="font-semibold text-slate-900">{department}</span>
                            </div>

                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Bank / MFS')}:</span>
                                <span className="font-semibold text-slate-900">{bankName}</span>
                            </div>
                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Account Number')}:</span>
                                <span className="font-semibold text-slate-900">{accountNumber}</span>
                            </div>

                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Working / Present Days')}:</span>
                                <span className="font-semibold text-slate-900">{payrollEntry.present_days} / {payrollEntry.working_days} Days</span>
                            </div>
                            <div className="flex justify-between items-center border-b border-slate-100 pb-1">
                                <span className="text-slate-500 font-medium">{t('Paid / LWP Leave')}:</span>
                                <span className="font-semibold text-slate-900">{payrollEntry.paid_leave_days} Paid / {payrollEntry.unpaid_leave_days} LWP</span>
                            </div>
                        </div>
                    </div>

                    {/* Two-Column Earnings & Deductions Layout */}
                    <div className="border border-slate-300 rounded bg-white/95 text-xs overflow-hidden">
                        <div className="grid grid-cols-2 divide-x divide-slate-300">
                            {/* EARNINGS */}
                            <div className="flex flex-col justify-between">
                                <div>
                                    <div className="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center font-bold text-slate-800">
                                        <span>{t('EARNINGS')}</span>
                                        <span>{t('AMOUNT')}</span>
                                    </div>
                                    <div className="p-3 space-y-1.5">
                                        <div className="flex justify-between items-center text-slate-800">
                                            <span>{t('Basic Salary')}</span>
                                            <span className="font-medium">{formatCurrency(payrollEntry.basic_salary)}</span>
                                        </div>

                                        {/* Allowances Breakdown */}
                                        {Object.keys(payrollEntry.allowances_breakdown || {}).length > 0 ? (
                                            Object.entries(payrollEntry.allowances_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{name}</span>
                                                    <span>{formatCurrency(Number(amount))}</span>
                                                </div>
                                            ))
                                        ) : (
                                            totalAllowances > 0 && (
                                                <div className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{t('Allowances')}</span>
                                                    <span>{formatCurrency(totalAllowances)}</span>
                                                </div>
                                            )
                                        )}

                                        {/* Manual Overtime Breakdown */}
                                        {Object.keys(payrollEntry.manual_overtimes_breakdown || {}).length > 0 ? (
                                            Object.entries(payrollEntry.manual_overtimes_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{name}</span>
                                                    <span>{formatCurrency(Number(amount))}</span>
                                                </div>
                                            ))
                                        ) : (
                                            totalManualOvertimes > 0 && (
                                                <div className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{t('Manual Overtime')}</span>
                                                    <span>{formatCurrency(totalManualOvertimes)}</span>
                                                </div>
                                            )
                                        )}

                                        {/* Attendance Overtime */}
                                        {attendanceOvertimeAmount > 0 && (
                                            <div className="flex justify-between items-center text-slate-700 pl-2">
                                                <span>{t('Attendance Overtime')} ({payrollEntry.attendance_overtime_hours || 0}h)</span>
                                                <span>{formatCurrency(attendanceOvertimeAmount)}</span>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Total Earnings */}
                                <div className="bg-slate-50 border-t border-slate-300 px-3 py-2 flex justify-between items-center font-bold text-slate-900">
                                    <span>{t('TOTAL EARNINGS')}</span>
                                    <span>{formatCurrency(totalEarnings)}</span>
                                </div>
                            </div>

                            {/* DEDUCTIONS */}
                            <div className="flex flex-col justify-between">
                                <div>
                                    <div className="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center font-bold text-slate-800">
                                        <span>{t('DEDUCTIONS')}</span>
                                        <span>{t('AMOUNT')}</span>
                                    </div>
                                    <div className="p-3 space-y-1.5">
                                        {/* LWP Deduction */}
                                        {unpaidLeaveDeduction > 0 && (
                                            <div className="flex justify-between items-center text-slate-700">
                                                <span>{t('LWP Deduction')} ({payrollEntry.unpaid_leave_days}d)</span>
                                                <span>{formatCurrency(unpaidLeaveDeduction)}</span>
                                            </div>
                                        )}

                                        {/* Half Day Deduction */}
                                        {halfDayDeduction > 0 && (
                                            <div className="flex justify-between items-center text-slate-700">
                                                <span>{t('Half Day Deduction')} ({payrollEntry.half_days}d)</span>
                                                <span>{formatCurrency(halfDayDeduction)}</span>
                                            </div>
                                        )}

                                        {/* Absent Day Deduction */}
                                        {absentDayDeduction > 0 && (
                                            <div className="flex justify-between items-center text-slate-700">
                                                <span>{t('Absent Deduction')} ({payrollEntry.absent_days}d)</span>
                                                <span>{formatCurrency(absentDayDeduction)}</span>
                                            </div>
                                        )}

                                        {/* Other Deductions Breakdown */}
                                        {Object.keys(payrollEntry.deductions_breakdown || {}).length > 0 ? (
                                            Object.entries(payrollEntry.deductions_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{name}</span>
                                                    <span>{formatCurrency(Number(amount))}</span>
                                                </div>
                                            ))
                                        ) : (
                                            totalOtherDeductions > 0 && (
                                                <div className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{t('Other Deductions')}</span>
                                                    <span>{formatCurrency(totalOtherDeductions)}</span>
                                                </div>
                                            )
                                        )}

                                        {/* Loans Breakdown */}
                                        {Object.keys(payrollEntry.loans_breakdown || {}).length > 0 ? (
                                            Object.entries(payrollEntry.loans_breakdown || {}).map(([name, amount]) => (
                                                <div key={name} className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{name}</span>
                                                    <span>{formatCurrency(Number(amount))}</span>
                                                </div>
                                            ))
                                        ) : (
                                            totalLoans > 0 && (
                                                <div className="flex justify-between items-center text-slate-700 pl-2">
                                                    <span>{t('Loan Installments')}</span>
                                                    <span>{formatCurrency(totalLoans)}</span>
                                                </div>
                                            )
                                        )}

                                        {totalDeductions === 0 && (
                                            <div className="text-slate-400 italic text-center py-2">{t('No Deductions')}</div>
                                        )}
                                    </div>
                                </div>

                                {/* Total Deductions */}
                                <div className="bg-slate-50 border-t border-slate-300 px-3 py-2 flex justify-between items-center font-bold text-slate-900">
                                    <span>{t('TOTAL DEDUCTIONS')}</span>
                                    <span>{formatCurrency(totalDeductions)}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Net Salary Payable & Amount in Words */}
                    <div className="border border-slate-300 rounded bg-white/95 p-3">
                        <div className="flex justify-between items-center">
                            <div>
                                <div className="text-xs leading-[1.2] font-bold text-slate-700 uppercase tracking-wider">{t('NET SALARY PAYABLE')}</div>
                                <div className="text-[11px] leading-[1.2] text-slate-500 font-medium">
                                    {t('Gross Pay')} ({formatCurrency(payrollEntry.gross_pay)}) - {t('Deductions')} ({formatCurrency(totalDeductions)})
                                </div>
                            </div>
                            <div className="text-lg leading-[1.2] font-bold text-slate-900 border-l border-slate-300 pl-4">
                                {formatCurrency(payrollEntry.net_pay)}
                            </div>
                        </div>
                        <div className="mt-2 border-t border-slate-200 pt-2 text-xs font-medium text-slate-800">
                            <span className="text-[11px] leading-[1.2] text-slate-500">{t('Amount in Words')}: </span>
                            <span className="font-semibold">{numberToWords(payrollEntry.net_pay)}</span>
                        </div>
                    </div>

                    {/* Signatures & Disclaimer (Follows directly after the tables) */}
                    <div className="pt-5">
                        {showSignatures && (
                            <div className="pt-5">
                                <div className="grid grid-cols-3 gap-4 text-center items-end text-xs">
                                    {/* Employee Signature */}
                                    <div>
                                        <div className="h-10 flex items-end justify-center mb-1"></div>
                                        <div className="border-t border-slate-400 pt-1 font-semibold text-slate-700">
                                            {t('Employee Signature')}
                                            {hrTitle && <div className="text-[10px] text-slate-500">&nbsp;</div>}
                                        </div>
                                    </div>

                                    {/* Prepared By */}
                                    <div>
                                        <div className="h-10 flex items-end justify-center mb-1"></div>
                                        <div className="border-t border-slate-400 pt-1 font-semibold text-slate-700">
                                            {t('Prepared By')}
                                            {hrTitle && <div className="text-[10px] text-slate-500">&nbsp;</div>}
                                        </div>
                                    </div>

                                    {/* HR Manager / Authorized Signatory */}
                                    <div className="flex flex-col items-center">
                                        <div className="h-10 flex items-end justify-center mb-1">
                                            {hrSignatureUrl ? (
                                                <img
                                                    src={hrSignatureUrl}
                                                    alt="HR Signature"
                                                    className="max-h-9 max-w-full object-contain"
                                                />
                                            ) : null}
                                        </div>
                                        <div className="w-full border-t border-slate-400 pt-1">
                                            <div className="font-bold text-slate-900">{hrName || t('Authorized Signatory')}</div>
                                            {hrTitle && <div className="text-[10px] text-slate-500">{hrTitle}</div>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {payslipNote && (
                            <div
                                className="mt-5 text-center text-[15px] text-slate-500 italic"
                                dangerouslySetInnerHTML={{ __html: payslipNote }}
                            />
                        )}
                    </div>
                </div>
            </div>

            {/* Clean Print Styles */}
            <style>{`
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 0;
                    }
                    html, body {
                        background: #ffffff !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                    #payslip-print-area {
                        width: 210mm !important;
                        height: 297mm !important;
                        margin: 0 auto !important;
                        box-shadow: none !important;
                        border: none !important;
                        overflow: hidden !important;
                        page-break-after: avoid !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }
                }
            `}</style>
        </div>
    );
}