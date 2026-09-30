<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payslip') }} - {{ $payrollEntry->employee->user->name ?? $payrollEntry->employee->name ?? 'Employee' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff !important;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .payslip-page {
            position: relative;
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            padding: 8mm 12mm;
            box-sizing: border-box;
            background: #ffffff;
            overflow: hidden;
        }
        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            pointer-events: none;
            opacity: 0.85;
        }
        .content-wrapper {
            position: relative;
            z-index: 10;
        }
        .space-y-4 > * + * {
            margin-top: 1rem;
        }
        .logo-img {
            max-height: 1.2in;
            max-width: 220px;
            object-fit: contain;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            min-height: 1.2in;
            padding-top: 2rem;
            padding-bottom: 1.25rem;
        }
        .header-left {
            text-align: left;
        }
        .header-right {
            text-align: right;
        }
        .title-h1 {
            font-size: 1.125rem;
            line-height: 1.2;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.025em;
            text-transform: uppercase;
            margin: 0;
        }
        .sub-text {
            font-size: 11px;
            line-height: 1.2;
            color: #64748b;
            margin: 0;
        }
        .subtitle-bold {
            font-size: 0.75rem;
            line-height: 1.2;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .emp-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 0.25rem;
            border: 1px solid #cbd5e1;
            padding: 0.75rem;
            font-size: 0.75rem;
        }
        .emp-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 1.5rem;
            row-gap: 0.5rem;
        }
        .emp-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 0.25rem;
        }
        .emp-label {
            color: #64748b;
            font-weight: 500;
        }
        .emp-val {
            font-weight: 600;
            color: #0f172a;
        }
        .ed-box {
            border: 1px solid #cbd5e1;
            border-radius: 0.25rem;
            background-color: rgba(255, 255, 255, 0.95);
            font-size: 0.75rem;
            overflow: hidden;
        }
        .ed-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .ed-col {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .ed-col-left {
            border-right: 1px solid #cbd5e1;
        }
        .ed-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 0.375rem 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: #1e293b;
        }
        .ed-body {
            padding: 0.75rem;
        }
        .ed-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #334155;
            margin-bottom: 0.375rem;
        }
        .ed-row:last-child {
            margin-bottom: 0;
        }
        .ed-row-indent {
            padding-left: 0.5rem;
            color: #334155;
        }
        .ed-row-main {
            color: #1e293b;
            font-weight: 500;
        }
        .ed-footer {
            background-color: #f8fafc;
            border-top: 1px solid #cbd5e1;
            padding: 0.5rem 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: #0f172a;
        }
        .no-deductions {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            padding: 0.5rem 0;
        }
        .net-card {
            border: 1px solid #cbd5e1;
            border-radius: 0.25rem;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 0.75rem;
        }
        .net-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .net-title {
            font-size: 0.75rem;
            line-height: 1.2;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .net-subtitle {
            font-size: 11px;
            line-height: 1.2;
            color: #64748b;
            font-weight: 500;
        }
        .net-val {
            font-size: 1.125rem;
            line-height: 1.2;
            font-weight: 700;
            color: #0f172a;
            border-left: 1px solid #cbd5e1;
            padding-left: 1rem;
        }
        .words-box {
            margin-top: 0.5rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #1e293b;
        }
        .words-title {
            font-size: 11px;
            line-height: 1.2;
            color: #64748b;
        }
        .words-val {
            font-weight: 600;
        }
        .sig-container {
            padding-top: 1.25rem;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            text-align: center;
            align-items: flex-end;
            font-size: 0.75rem;
        }
        .sig-box {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sig-space {
            height: 2.5rem;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: 0.25rem;
            width: 100%;
        }
        .sig-line {
            width: 100%;
            border-top: 1px solid #94a3b8;
            padding-top: 0.25rem;
            font-weight: 600;
            color: #334155;
        }
        .sig-name {
            font-weight: 700;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 10px;
            color: #64748b;
            font-weight: normal;
        }
        .payslip-note {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 15px;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>
@php
    $getSetting = function($key, $default = '') use ($companySettings) {
        return $companySettings[$key] ?? $default;
    };

    $showLogo = ($getSetting('payslip_show_logo', 'on') !== 'off');
    $enableLetterhead = ($getSetting('payslip_enable_letterhead', 'off') === 'on');
    $showSignatures = ($getSetting('payslip_show_signatures', 'on') !== 'off');

    $customLogo = $getSetting('payslip_logo', '');
    $companyLogo = $getSetting('company_logo', '');
    $bgLetterhead = $getSetting('payslip_bg_letterhead', '');
    $hrSignature = $getSetting('payslip_hr_signature', '');
    $hrName = $getSetting('payslip_hr_name', '');
    $hrTitle = $getSetting('payslip_hr_title', 'HR Manager / Authorized Signatory');
    $payslipNote = $getSetting('payslip_note', 'This is a computer generated payslip and does not require signature.');

    $logoPath = $customLogo ?: $companyLogo;

    if (!function_exists('getPdfImagePath')) {
        function getPdfImagePath($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'data:image/')) return $path;

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                try {
                    $ext = pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $mime = match(strtolower($ext)) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                        default => 'image/png'
                    };
                    $context = stream_context_create(['http' => ['timeout' => 5], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
                    $data = @file_get_contents($path, false, $context);
                    if ($data !== false) {
                        return 'data:' . $mime . ';base64,' . base64_encode($data);
                    }
                } catch (\Throwable $e) {}
                return $path;
            }

            $cleanPath = ltrim($path, '/');
            $possiblePaths = [
                public_path($cleanPath),
                storage_path('app/public/media/' . $cleanPath),
                storage_path('app/public/' . $cleanPath),
                storage_path('app/' . $cleanPath),
                base_path($cleanPath),
            ];

            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath) && is_file($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $mime = mime_content_type($fullPath) ?: match(strtolower($ext)) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                        default => 'image/png'
                    };
                    $data = file_get_contents($fullPath);
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            }

            $prefix = getImageUrlPrefix();
            return rtrim($prefix, '/') . '/' . $cleanPath;
        }
    }

    if (!function_exists('numberToWordsBangladeshi')) {
        function numberToWordsBangladeshi($amount): string {
            $num = abs((float)$amount);
            if ($num == 0) return 'Zero Taka Only';

            $integerPart = (int)floor($num);
            $decimalPart = (int)round(($num - $integerPart) * 100);

            $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            $convertLessThanThousand = function ($n) use (&$convertLessThanThousand, $ones, $tens) {
                if ($n === 0) return '';
                if ($n < 20) return $ones[$n];
                if ($n < 100) return $tens[(int)floor($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
                return $ones[(int)floor($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $convertLessThanThousand($n % 100) : '');
            };

            $convertNumber = function ($n) use (&$convertNumber, $convertLessThanThousand) {
                if ($n === 0) return '';
                if ($n < 1000) return $convertLessThanThousand($n);
                if ($n < 100000) {
                    return $convertLessThanThousand((int)floor($n / 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convertNumber($n % 1000) : '');
                }
                if ($n < 10000000) {
                    return $convertLessThanThousand((int)floor($n / 100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convertNumber($n % 100000) : '');
                }
                return $convertLessThanThousand((int)floor($n / 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convertNumber($n % 10000000) : '');
            };

            $result = trim($convertNumber($integerPart)) . ' Taka';
            if ($decimalPart > 0) {
                $result .= ' and ' . trim($convertLessThanThousand($decimalPart)) . ' Poisha';
            }
            return $result . ' Only';
        }
    }

    if (!function_exists('formatPayslipCurrency')) {
        function formatPayslipCurrency($amount, $companySettings = []) {
            $num = (float)($amount ?? 0);
            $decimalPlaces = (int)($companySettings['decimalFormat'] ?? 2);
            $decimalSeparator = $companySettings['decimalSeparator'] ?? '.';
            $thousandsSeparator = $companySettings['thousandsSeparator'] ?? ',';
            if ($thousandsSeparator === 'none') {
                $thousandsSeparator = '';
            }
            $floatNumber = ($companySettings['floatNumber'] ?? '1') !== '0';
            $currencySymbolSpace = ($companySettings['currencySymbolSpace'] ?? '0') === '1';
            $currencySymbolPosition = $companySettings['currencySymbolPosition'] ?? 'before';
            $symbol = $companySettings['currencySymbol'] ?? '$';

            if (!$floatNumber) {
                $num = floor($num);
            }

            $formattedNumber = number_format($num, $decimalPlaces, $decimalSeparator, $thousandsSeparator);
            $space = $currencySymbolSpace ? ' ' : '';

            return $currencySymbolPosition === 'before'
                ? $symbol . $space . $formattedNumber
                : $formattedNumber . $space . $symbol;
        }
    }

    if (!function_exists('formatPayslipDate')) {
        function formatPayslipDate($date, $companySettings = []) {
            if (empty($date)) return '';
            $format = $companySettings['dateFormat'] ?? 'Y-m-d';
            try {
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Throwable $e) {
                return (string)$date;
            }
        }
    }

    $logoUrl = getPdfImagePath($logoPath);
    $letterheadUrl = getPdfImagePath($bgLetterhead);
    $hrSignatureUrl = getPdfImagePath($hrSignature);

    $employeeName = $payrollEntry->employee->user->name ?? $payrollEntry->employee->name ?? 'N/A';
    $employeeId = $payrollEntry->employee->employee_id ?? 'N/A';
    $designation = $payrollEntry->employee->designation->designation_name ?? $payrollEntry->employee->designation->name ?? 'N/A';
    $department = $payrollEntry->employee->department->department_name ?? $payrollEntry->employee->department->name ?? 'N/A';
    $bankName = $payrollEntry->employee->bank_name ?? 'N/A';
    $accountNumber = $payrollEntry->employee->account_number ?? 'N/A';

    $totalAllowances = (float)($payrollEntry->total_allowances ?? 0);
    $totalManualOvertimes = (float)($payrollEntry->total_manual_overtimes ?? 0);
    $attendanceOvertimeAmount = (float)($payrollEntry->attendance_overtime_amount ?? 0);
    $totalEarnings = (float)($payrollEntry->basic_salary ?? 0) + $totalAllowances + $totalManualOvertimes + $attendanceOvertimeAmount;

    $unpaidLeaveDeduction = (float)($payrollEntry->unpaid_leave_deduction ?? 0);
    $halfDayDeduction = (float)($payrollEntry->half_day_deduction ?? 0);
    $absentDayDeduction = (float)($payrollEntry->absent_day_deduction ?? 0);
    $totalLeaveDeductions = $unpaidLeaveDeduction + $halfDayDeduction + $absentDayDeduction;

    $totalOtherDeductions = (float)($payrollEntry->total_deductions ?? 0);
    $totalLoans = (float)($payrollEntry->total_loans ?? 0);
    $totalDeductions = $totalLeaveDeductions + $totalOtherDeductions + $totalLoans;

    $allowancesBreakdown = is_array($payrollEntry->allowances_breakdown) ? $payrollEntry->allowances_breakdown : (json_decode($payrollEntry->allowances_breakdown ?? '', true) ?: []);
    $manualOvertimesBreakdown = is_array($payrollEntry->manual_overtimes_breakdown) ? $payrollEntry->manual_overtimes_breakdown : (json_decode($payrollEntry->manual_overtimes_breakdown ?? '', true) ?: []);
    $deductionsBreakdown = is_array($payrollEntry->deductions_breakdown) ? $payrollEntry->deductions_breakdown : (json_decode($payrollEntry->deductions_breakdown ?? '', true) ?: []);
    $loansBreakdown = is_array($payrollEntry->loans_breakdown) ? $payrollEntry->loans_breakdown : (json_decode($payrollEntry->loans_breakdown ?? '', true) ?: []);

    $amountInWords = numberToWordsBangladeshi($payrollEntry->net_pay);
@endphp

<div class="payslip-page">
    @if($enableLetterhead && $letterheadUrl)
        <img src="{{ $letterheadUrl }}" alt="Letterhead Background" class="letterhead-bg" />
    @endif

    <div class="content-wrapper space-y-4">
        @if($showLogo && $logoUrl)
            <img src="{{ $logoUrl }}" alt="Company Logo" class="logo-img" />
        @else
            <div></div>
        @endif

        <div class="header-flex">
            <div class="header-left">
                <h1 class="title-h1">
                    {{ $getSetting('company_name', 'COMPANY NAME') }}
                </h1>
                <p class="sub-text">
                    {{ $getSetting('company_address', 'COMPANY ADDRESS') }}
                </p>
                <p class="sub-text">
                    {{ __('Phone') }}: {{ $getSetting('company_telephone', '+880-96XXXXXXX') }}
                </p>
                <p class="sub-text">
                    {{ __('Email') }}: {{ $getSetting('company_email', 'company@email.com') }}
                </p>
            </div>

            <div class="header-right">
                <h1 class="title-h1">
                    {{ __('Payslip') }}
                </h1>
                <p class="subtitle-bold">
                    {{ $payrollEntry->payroll->title }}
                </p>
                <p class="sub-text">
                    {{ __('Pay Period') }}: {{ formatPayslipDate($payrollEntry->payroll->pay_period_start, $companySettings) }} - {{ formatPayslipDate($payrollEntry->payroll->pay_period_end, $companySettings) }}
                </p>
                @if(!empty($payrollEntry->payroll->pay_date))
                    <p class="sub-text">
                        {{ __('Pay Date') }}: {{ formatPayslipDate($payrollEntry->payroll->pay_date, $companySettings) }}
                    </p>
                @endif
            </div>
        </div>

        <div class="emp-box">
            <div class="emp-grid">
                <div class="emp-row">
                    <span class="emp-label">{{ __('Employee Name') }}:</span>
                    <span class="emp-val">{{ $employeeName }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">{{ __('Employee ID') }}:</span>
                    <span class="emp-val">{{ $employeeId }}</span>
                </div>

                <div class="emp-row">
                    <span class="emp-label">{{ __('Designation') }}:</span>
                    <span class="emp-val">{{ $designation }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">{{ __('Department') }}:</span>
                    <span class="emp-val">{{ $department }}</span>
                </div>

                <div class="emp-row">
                    <span class="emp-label">{{ __('Bank / MFS') }}:</span>
                    <span class="emp-val">{{ $bankName }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">{{ __('Account Number') }}:</span>
                    <span class="emp-val">{{ $accountNumber }}</span>
                </div>

                <div class="emp-row">
                    <span class="emp-label">{{ __('Working / Present Days') }}:</span>
                    <span class="emp-val">{{ $payrollEntry->present_days }} / {{ $payrollEntry->working_days }} Days</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">{{ __('Paid / LWP Leave') }}:</span>
                    <span class="emp-val">{{ $payrollEntry->paid_leave_days }} Paid / {{ $payrollEntry->unpaid_leave_days }} LWP</span>
                </div>
            </div>
        </div>

        <div class="ed-box">
            <div class="ed-grid">
                <div class="ed-col ed-col-left">
                    <div>
                        <div class="ed-header">
                            <span>{{ __('EARNINGS') }}</span>
                            <span>{{ __('AMOUNT') }}</span>
                        </div>
                        <div class="ed-body">
                            <div class="ed-row ed-row-main">
                                <span>{{ __('Basic Salary') }}</span>
                                <span>{{ formatPayslipCurrency($payrollEntry->basic_salary, $companySettings) }}</span>
                            </div>

                            @if(count($allowancesBreakdown) > 0)
                                @foreach($allowancesBreakdown as $name => $amount)
                                    <div class="ed-row ed-row-indent">
                                        <span>{{ $name }}</span>
                                        <span>{{ formatPayslipCurrency($amount, $companySettings) }}</span>
                                    </div>
                                @endforeach
                            @elseif($totalAllowances > 0)
                                <div class="ed-row ed-row-indent">
                                    <span>{{ __('Allowances') }}</span>
                                    <span>{{ formatPayslipCurrency($totalAllowances, $companySettings) }}</span>
                                </div>
                            @endif

                            @if(count($manualOvertimesBreakdown) > 0)
                                @foreach($manualOvertimesBreakdown as $name => $amount)
                                    <div class="ed-row ed-row-indent">
                                        <span>{{ $name }}</span>
                                        <span>{{ formatPayslipCurrency($amount, $companySettings) }}</span>
                                    </div>
                                @endforeach
                            @elseif($totalManualOvertimes > 0)
                                <div class="ed-row ed-row-indent">
                                    <span>{{ __('Manual Overtime') }}</span>
                                    <span>{{ formatPayslipCurrency($totalManualOvertimes, $companySettings) }}</span>
                                </div>
                            @endif

                            @if($attendanceOvertimeAmount > 0)
                                <div class="ed-row ed-row-indent">
                                    <span>{{ __('Attendance Overtime') }} ({{ $payrollEntry->attendance_overtime_hours ?: 0 }}h)</span>
                                    <span>{{ formatPayslipCurrency($attendanceOvertimeAmount, $companySettings) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ed-footer">
                        <span>{{ __('TOTAL EARNINGS') }}</span>
                        <span>{{ formatPayslipCurrency($totalEarnings, $companySettings) }}</span>
                    </div>
                </div>

                <div class="ed-col">
                    <div>
                        <div class="ed-header">
                            <span>{{ __('DEDUCTIONS') }}</span>
                            <span>{{ __('AMOUNT') }}</span>
                        </div>
                        <div class="ed-body">
                            @if($unpaidLeaveDeduction > 0)
                                <div class="ed-row">
                                    <span>{{ __('LWP Deduction') }} ({{ $payrollEntry->unpaid_leave_days }}d)</span>
                                    <span>{{ formatPayslipCurrency($unpaidLeaveDeduction, $companySettings) }}</span>
                                </div>
                            @endif

                            @if($halfDayDeduction > 0)
                                <div class="ed-row">
                                    <span>{{ __('Half Day Deduction') }} ({{ $payrollEntry->half_days }}d)</span>
                                    <span>{{ formatPayslipCurrency($halfDayDeduction, $companySettings) }}</span>
                                </div>
                            @endif

                            @if($absentDayDeduction > 0)
                                <div class="ed-row">
                                    <span>{{ __('Absent Deduction') }} ({{ $payrollEntry->absent_days }}d)</span>
                                    <span>{{ formatPayslipCurrency($absentDayDeduction, $companySettings) }}</span>
                                </div>
                            @endif

                            @if(count($deductionsBreakdown) > 0)
                                @foreach($deductionsBreakdown as $name => $amount)
                                    <div class="ed-row ed-row-indent">
                                        <span>{{ $name }}</span>
                                        <span>{{ formatPayslipCurrency($amount, $companySettings) }}</span>
                                    </div>
                                @endforeach
                            @elseif($totalOtherDeductions > 0)
                                <div class="ed-row ed-row-indent">
                                    <span>{{ __('Other Deductions') }}</span>
                                    <span>{{ formatPayslipCurrency($totalOtherDeductions, $companySettings) }}</span>
                                </div>
                            @endif

                            @if(count($loansBreakdown) > 0)
                                @foreach($loansBreakdown as $name => $amount)
                                    <div class="ed-row ed-row-indent">
                                        <span>{{ $name }}</span>
                                        <span>{{ formatPayslipCurrency($amount, $companySettings) }}</span>
                                    </div>
                                @endforeach
                            @elseif($totalLoans > 0)
                                <div class="ed-row ed-row-indent">
                                    <span>{{ __('Loan Installments') }}</span>
                                    <span>{{ formatPayslipCurrency($totalLoans, $companySettings) }}</span>
                                </div>
                            @endif

                            @if($totalDeductions == 0)
                                <div class="no-deductions">{{ __('No Deductions') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="ed-footer">
                        <span>{{ __('TOTAL DEDUCTIONS') }}</span>
                        <span>{{ formatPayslipCurrency($totalDeductions, $companySettings) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="net-card">
            <div class="net-top">
                <div>
                    <div class="net-title">{{ __('NET SALARY PAYABLE') }}</div>
                    <div class="net-subtitle">
                        {{ __('Gross Pay') }} ({{ formatPayslipCurrency($payrollEntry->gross_pay, $companySettings) }}) - {{ __('Deductions') }} ({{ formatPayslipCurrency($totalDeductions, $companySettings) }})
                    </div>
                </div>
                <div class="net-val">
                    {{ formatPayslipCurrency($payrollEntry->net_pay, $companySettings) }}
                </div>
            </div>
            <div class="words-box">
                <span class="words-title">{{ __('Amount in Words') }}: </span>
                <span class="words-val">{{ $amountInWords }}</span>
            </div>
        </div>

        <div class="sig-container">
            @if($showSignatures)
                <div class="sig-grid">
                    <div class="sig-box">
                        <div class="sig-space"></div>
                        <div class="sig-line">
                            {{ __('Employee Signature') }}
                            @if(!empty($hrTitle))<div class="sig-sub">&nbsp;</div>@endif
                        </div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-space"></div>
                        <div class="sig-line">
                            {{ __('Prepared By') }}
                            @if(!empty($hrTitle))<div class="sig-sub">&nbsp;</div>@endif
                        </div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-space">
                            @if($hrSignatureUrl)
                                <img src="{{ $hrSignatureUrl }}" alt="HR Signature" style="max-height: 2.25rem; max-width: 100%; object-fit: contain;" />
                            @endif
                        </div>
                        <div class="sig-line">
                            <div class="sig-name">{{ $hrName ?: __('Authorized Signatory') }}</div>
                            @if(!empty($hrTitle))<div class="sig-sub">{{ $hrTitle }}</div>@endif
                        </div>
                    </div>
                </div>
            @endif

            @if($payslipNote)
                <div class="payslip-note">
                    {!! $payslipNote !!}
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
