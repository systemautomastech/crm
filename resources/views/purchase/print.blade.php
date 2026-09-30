@php
    $companySettings = getCompanyAllSetting($invoice->created_by);
    $creatorId = $invoice->created_by ?? (function_exists('creatorId') ? creatorId() : auth()->id());
    $purchaseInvoiceSetting = $purchaseInvoiceSetting ?? \App\Models\PurchaseInvoiceSetup::getSettings($creatorId);

    /*
    |--------------------------------------------------------------------------
    | Image Helpers
    |--------------------------------------------------------------------------
    */

    $toDataUri = function ($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        $mime = mime_content_type($filePath) ?: 'image/jpeg';
        $data = file_get_contents($filePath);

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    };

    $getImagePath = function ($path) use ($toDataUri) {
        if (!$path) {
            return '';
        }

        $cleanPath = ltrim($path, '/');

        $localPaths = [
            storage_path('app/public/media/' . basename($cleanPath)),
            storage_path('app/public/' . $cleanPath),
            public_path('storage/media/' . basename($cleanPath)),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            public_path('uploads/' . $cleanPath),
        ];

        foreach ($localPaths as $localPath) {
            if (file_exists($localPath) && is_file($localPath)) {
                $dataUri = $toDataUri($localPath);

                if ($dataUri) {
                    return $dataUri;
                }
            }
        }

        if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
            return $cleanPath;
        }

        if (function_exists('getImageUrlPrefix')) {
            $prefix = getImageUrlPrefix();

            if ($prefix) {
                return rtrim($prefix, '/') . '/' . basename($cleanPath);
            }
        }

        return \Illuminate\Support\Facades\Storage::url($cleanPath);
    };

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */

    $showLogo = ($purchaseInvoiceSetting['purchase_invoice_show_logo'] ?? 'on') !== 'off';
    $customLogo = $purchaseInvoiceSetting['purchase_invoice_logo'] ?? '';
    $companyLogo = $companySettings['company_logo'] ?? ($companySettings['logo_dark'] ?? '');
    $logoToUse = $customLogo ?: $companyLogo;
    $logoUrl = $showLogo && $logoToUse ? $getImagePath($logoToUse) : '';

    $enableLetterhead = ($purchaseInvoiceSetting['purchase_invoice_enable_letterhead'] ?? 'off') === 'on';
    $bgLetterhead = $purchaseInvoiceSetting['purchase_invoice_bg_letterhead'] ?? '';
    $bgLetterheadUrl = $enableLetterhead && $bgLetterhead ? $getImagePath($bgLetterhead) : '';

    /*
    |--------------------------------------------------------------------------
    | Currency Helpers
    |--------------------------------------------------------------------------
    */

    $currencyCode = $companySettings['defaultCurrency'] ?? '';

    $formatNumber = function ($amount) use ($companySettings) {
        $amount = is_numeric($amount) ? (float) $amount : 0;
        $decimalPlaces = (int) ($companySettings['decimalFormat'] ?? 2);
        $decimalSeparator = $companySettings['decimalSeparator'] ?? '.';
        $thousandsSeparator = $companySettings['thousandsSeparator'] ?? ',';
        $floatNumber = ($companySettings['floatNumber'] ?? '1') !== '0';

        $number = $floatNumber ? $amount : floor($amount);

        return number_format(
            $number,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator === 'none' ? '' : $thousandsSeparator
        );
    };

    $formatCurrency = function ($amount) use ($companySettings) {
        $amount = is_numeric($amount) ? (float) $amount : 0;
        $decimalPlaces = (int) ($companySettings['decimalFormat'] ?? 2);
        $decimalSeparator = $companySettings['decimalSeparator'] ?? '.';
        $thousandsSeparator = $companySettings['thousandsSeparator'] ?? ',';
        $floatNumber = ($companySettings['floatNumber'] ?? '1') !== '0';
        $symbolSpace = ($companySettings['currencySymbolSpace'] ?? '0') === '1';
        $symbolPosition = $companySettings['currencySymbolPosition'] ?? 'before';

        $number = $floatNumber ? $amount : floor($amount);

        $formatted = number_format(
            $number,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator === 'none' ? '' : $thousandsSeparator
        );

        $symbol = $companySettings['currencySymbol'] ?? '$';
        $space = $symbolSpace ? ' ' : '';

        return $symbolPosition === 'before' ? "{$symbol}{$space}{$formatted}" : "{$formatted}{$space}{$symbol}";
    };

    $getAmountFontSize = function ($amountStr, $defaultSize = 10) {
        $cleanStr = strip_tags((string) $amountStr);
        $len = mb_strlen($cleanStr);
        if ($len > 18) {
            return '7.5px';
        }
        if ($len > 15) {
            return '8.5px';
        }
        if ($len > 12) {
            return '9.5px';
        }
        return $defaultSize . 'px';
    };

    /*
    |--------------------------------------------------------------------------
    | Date Helper
    |--------------------------------------------------------------------------
    */

    $formatDate = function ($date) use ($companySettings) {
        if (!$date) {
            return '';
        }

        $format = $companySettings['dateFormat'] ?? ($companySettings['date_format'] ?? 'Y-m-d');
        $format = preg_replace('/\bf\b/', 'M', $format);

        try {
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
                return $date->format($format);
            }
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return (string) $date;
        }
    };





    /*
    |--------------------------------------------------------------------------
    | Payment Allocations
    |--------------------------------------------------------------------------
    */

    $allPaymentAllocations = $invoice->paymentAllocations ?? ($invoice->payment_allocations ?? collect());

    $paymentAllocations = collect($allPaymentAllocations)
        ->filter(function ($allocation) {
            $status = $allocation->payment->status ?? ($allocation->status ?? 'cleared');
            return strtolower($status) === 'cleared';
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Tax Breakdown
    |--------------------------------------------------------------------------
    */

    $taxBreakdown = [];

    foreach ($invoice->items ?? [] as $item) {
        if (!empty($item->taxes) && count($item->taxes) > 0) {
            $itemSubtotal = $item->quantity * $item->unit_price - ($item->discount_amount ?? 0);

            foreach ($item->taxes as $tax) {
                $taxName = $tax->tax_name ?: __('Tax');
                $taxRate = (float) $tax->tax_rate;
                $taxAmount = $itemSubtotal * ($taxRate / 100);

                $key = $taxName;

                if (!isset($taxBreakdown[$key])) {
                    $taxBreakdown[$key] = [
                        'name' => $taxName,
                        'amount' => 0,
                    ];
                }

                $taxBreakdown[$key]['amount'] += $taxAmount;
            }
        } elseif ((float) $item->tax_amount > 0 || (float) $item->tax_percentage > 0) {
            $key = __('Tax');

            if (!isset($taxBreakdown[$key])) {
                $taxBreakdown[$key] = [
                    'name' => __('Tax'),
                    'amount' => 0,
                ];
            }

            $taxBreakdown[$key]['amount'] += (float) $item->tax_amount;
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Purchase_Invoice_{{ $invoice->invoice_number }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            box-sizing: border-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family:
                'Open Sans',
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                "Helvetica Neue",
                Arial,
                sans-serif !important;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff;
            color: #1e293b;
            font-family:
                'Open Sans',
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                "Helvetica Neue",
                Arial,
                sans-serif !important;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        .a4-page {
            position: relative;
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            padding: 30mm 14mm;
            margin: 0 auto;
            background: #ffffff;
            overflow: hidden;
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
            break-inside: avoid-page;
        }

        .a4-page:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
        }

        .letterhead-bg-layer {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            pointer-events: none;
        }

        .a4-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .page-top {
            width: 100%;
        }

        .page-footer {
            width: 100%;
            flex-shrink: 0;
        }

        .page-break-inside-avoid {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .rich-content {
            word-wrap: break-word !important;
            overflow-wrap: anywhere !important;
            word-break: break-word !important;
        }

        .rich-content * {
            word-wrap: break-word !important;
            overflow-wrap: anywhere !important;
            word-break: break-word !important;
        }

        .rich-content ul {
            list-style-type: disc !important;
            margin-left: 1.25rem !important;
            padding-left: 0 !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }

        .rich-content ol {
            list-style-type: decimal !important;
            margin-left: 1.25rem !important;
            padding-left: 0 !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }

        .rich-content li {
            display: list-item !important;
            margin-top: 0.1rem !important;
            margin-bottom: 0.1rem !important;
        }

        .rich-content p {
            margin-top: 0.15rem !important;
            margin-bottom: 0.15rem !important;
        }

        .rich-content p:first-child {
            margin-top: 0 !important;
        }

        .rich-content p:last-child {
            margin-bottom: 0 !important;
        }

        .rich-content strong,
        .rich-content b {
            font-weight: 700 !important;
        }

        .rich-content em,
        .rich-content i {
            font-style: italic !important;
        }

        .rich-content u {
            text-decoration: underline !important;
        }

        .rich-content s,
        .rich-content strike {
            text-decoration: line-through !important;
        }

        table {
            table-layout: fixed !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
        }

        th,
        td {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            box-sizing: border-box !important;
        }

        #invoice-source {
            display: none !important;
        }

        #invoice-pages {
            width: 100%;
        }

        .invoice-table {
            width: 100%;
            font-size: 10px;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid #94a3b8;
        }

        .invoice-table thead {
            display: table-header-group;
        }

        .invoice-table tbody tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        @media print {
            html,
            body {
                background: #ffffff !important;
            }

            .a4-page {
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 30mm 14mm !important;
                box-shadow: none !important;
            }

            .a4-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>

<body>

    {{-- ======================================================================
    SOURCE ROWS
    ======================================================================= --}}

    <div id="invoice-source">
        <table>
            <tbody id="invoice-source-rows">
                @foreach ($invoice->items ?? [] as $i => $item)
                    @php
                        $unitName =
                            $item->product?->unitRelation?->unit_name ??
                            (!is_numeric($item->product?->unit) ? $item->product?->unit : '');

                        $itemDesc =
                            $item->description ??
                            ($item->product?->long_description ?? ($item->product?->description ?? ''));
                    @endphp

                    <tr data-item-row data-index="{{ $i }}">
                        <td style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; vertical-align: top; color: #475569;">
                            {{ $i + 1 }}
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; vertical-align: top;">
                            <div style="font-weight: 600; color: #0f172a; line-height: 1.25; font-size: 10px;">
                                {{ $item->product->name ?? '' }}
                            </div>
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; vertical-align: top; color: #475569; font-size: 9px; line-height: 1.35;">
                            @if (!empty($itemDesc))
                                <div class="rich-content">
                                    {!! $itemDesc !!}
                                </div>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>

                        <td style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; vertical-align: top; color: #1e293b; font-weight: 500;">
                            {{ $item->quantity }}
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; vertical-align: top; color: #1e293b;">
                            {{ $formatNumber($item->unit_price) }}
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; vertical-align: top; color: #1e293b;">
                            @if (($item->discount_type ?? 'percentage') === 'fixed')
                                @if ((float) ($item->discount_amount ?? 0) > 0)
                                    <div style="font-weight: 500;">
                                        {{ $formatNumber($item->discount_amount) }}
                                    </div>
                                @else
                                    <span>-</span>
                                @endif
                            @else
                                @if ($item->discount_percentage > 0)
                                    <div>
                                        {{ (float) $item->discount_percentage }}%
                                    </div>
                                    @if ((float) ($item->discount_amount ?? 0) > 0)
                                        <div style="font-size: 8.5px; color: #64748b;">
                                            {{ $formatNumber($item->discount_amount) }}
                                        </div>
                                    @endif
                                @else
                                    <span>-</span>
                                @endif
                            @endif
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; vertical-align: top; color: #1e293b;">
                            @if (!empty($item->taxes) && count($item->taxes) > 0)
                                @foreach ($item->taxes as $tax)
                                    <div style="line-height: 1.25; color: #475569; font-size: 9px;">
                                        {{ $tax->tax_name }} ({{ $tax->tax_rate }}%)
                                    </div>
                                @endforeach
                            @elseif($item->tax_percentage > 0)
                                <div style="color: #475569; font-size: 9px;">
                                    {{ $item->tax_percentage }}%
                                </div>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>

                        <td style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; vertical-align: top; font-weight: 600; color: #0f172a;">
                            {{ $formatNumber($item->total_amount) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ======================================================================
    FIRST PAGE HEADER TEMPLATE
    ======================================================================= --}}

    <template id="invoice-first-header">
        <div class="header-flex-container flex justify-between items-start mb-5">
            <div class="w-1/2 relative">
                @if ($showLogo && $logoUrl)
                    <div class="mb-2" style="position: absolute; top: -75px; left: 0;">
                        <img src="{{ $logoUrl }}" alt="Logo" class="max-h-14 max-w-[200px] object-contain">
                    </div>
                @endif

                @if (!empty($purchaseInvoiceSetting['company_name']) || !empty($companySettings['company_name']))
                    <h1 class="text-xl sm:text-2xl font-bold mb-1.5 text-gray-900 leading-tight">
                        {{ $purchaseInvoiceSetting['company_name'] ?? $companySettings['company_name'] }}
                    </h1>
                @endif

                <div class="text-xs space-y-0.5 text-gray-600">
                    @if (!empty($companySettings['company_address']))
                        <p>{{ $companySettings['company_address'] }}</p>
                    @endif

                    @if (
                        !empty($companySettings['company_city']) ||
                        !empty($companySettings['company_state']) ||
                        !empty($companySettings['company_zipcode']))
                        <p>
                            {{ $companySettings['company_city'] ?? '' }}
                            {{ !empty($companySettings['company_state']) ? ', ' . $companySettings['company_state'] : '' }}
                            {{ $companySettings['company_zipcode'] ?? '' }}
                        </p>
                    @endif

                    @if (!empty($companySettings['company_country']))
                        <p>{{ $companySettings['company_country'] }}</p>
                    @endif

                    @if (!empty($companySettings['company_telephone']))
                        <p>{{ __('Phone') }}: {{ $companySettings['company_telephone'] }}</p>
                    @endif

                    @if (!empty($companySettings['company_email']))
                        <p>{{ __('Email') }}: {{ $companySettings['company_email'] }}</p>
                    @endif
                </div>
            </div>

            <div class="w-1/2 flex items-start justify-end gap-4 text-right">


                <div>
                    <div class="flex items-center justify-end gap-2.5 mb-1">
                        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                            {{ __('PURCHASE INVOICE') }}
                        </h2>
                    </div>

                    <p class="text-sm font-semibold text-gray-800">
                        #{{ $invoice->invoice_number }}
                    </p>

                    <div class="text-xs mt-2 space-y-0.5 text-gray-600">
                        <p>
                            <span class="font-medium text-gray-700">{{ __('Date') }}:</span>
                            {{ $formatDate($invoice->invoice_date) }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">{{ __('Due') }}:</span>
                            {{ $formatDate($invoice->due_date) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vendor / Warehouse Information --}}
        <div class="flex justify-between mb-5 pt-3 border-t border-gray-200">
            <div class="w-1/2">
                <h3 class="font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider">
                    {{ __('VENDOR / VENDOR DETAILS') }}
                </h3>

                <div class="text-xs space-y-0.5 text-gray-700">
                    <p class="font-semibold text-gray-900">
                        {{ $invoice->vendor->name ?? '-' }}
                    </p>

                    @if (!empty($invoice->vendor->email))
                        <p>{{ $invoice->vendor->email }}</p>
                    @endif

                    @if (!empty($invoice->vendorDetails?->billing_address))
                        <p>{{ $invoice->vendorDetails->billing_address['name'] ?? '' }}</p>
                        <p>{{ $invoice->vendorDetails->billing_address['address_line_1'] ?? '' }}</p>
                        <p>
                            {{ $invoice->vendorDetails->billing_address['city'] ?? '' }}
                            {{ !empty($invoice->vendorDetails->billing_address['state']) ? ', ' . $invoice->vendorDetails->billing_address['state'] : '' }}
                            {{ $invoice->vendorDetails->billing_address['zip_code'] ?? '' }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="text-right w-1/2">
                <h3 class="font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider">
                    {{ __('SHIP TO / WAREHOUSE') }}
                </h3>

                <div class="text-xs space-y-0.5 text-gray-700">
                    @if (!empty($invoice->warehouse))
                        <p class="font-semibold text-gray-900">{{ $invoice->warehouse->name }}</p>
                        @if (!empty($invoice->warehouse->address))
                            <p class="whitespace-pre-line">{{ $invoice->warehouse->address }}</p>
                        @endif
                    @elseif(!empty($invoice->vendorDetails?->shipping_address))
                        <p class="font-semibold text-gray-900">{{ $invoice->vendorDetails->shipping_address['name'] ?? '' }}</p>
                        <p>{{ $invoice->vendorDetails->shipping_address['address_line_1'] ?? '' }}</p>
                        <p>
                            {{ $invoice->vendorDetails->shipping_address['city'] ?? '' }}
                            {{ !empty($invoice->vendorDetails->shipping_address['state']) ? ', ' . $invoice->vendorDetails->shipping_address['state'] : '' }}
                            {{ $invoice->vendorDetails->shipping_address['zip_code'] ?? '' }}
                        </p>
                    @else
                        <p class="text-gray-500">{{ __('Company Warehouse') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </template>

    {{-- ======================================================================
    COMPACT HEADER TEMPLATE
    ======================================================================= --}}

    <template id="invoice-compact-header">
        <div class="relative flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
            @if ($showLogo && $logoUrl)
                <div class="mb-2" style="position: absolute; top: -75px; left: 0;">
                    <img src="{{ $logoUrl }}" alt="Logo" class="max-h-14 max-w-[200px] object-contain">
                </div>
            @endif

            <div>
                <span class="font-bold text-sm text-gray-900">
                    {{ __('PURCHASE INVOICE') }}: #{{ $invoice->invoice_number }}
                </span>
            </div>

            <div class="text-xs text-gray-600">
                <span>{{ __('Date') }}: {{ $formatDate($invoice->invoice_date) }}</span>
                |
                <span>{{ __('Vendor') }}: {{ $invoice->vendor->name ?? '' }}</span>
            </div>
        </div>
    </template>

    {{-- ======================================================================
    TABLE HEADER TEMPLATE
    ======================================================================= --}}

    <template id="invoice-table-head">
        <tr style="background-color: #e2e8f0; color: #0f172a; font-weight: 700;">
            <th style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; font-size: 9.5px; width: 5%;">
                {{ __('S/N') }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: left; font-size: 9.5px; width: 16%;">
                {{ __('Item/Service') }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: left; font-size: 9.5px; width: 29%;">
                {{ __('Description') }}
            </th>
            <th style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; font-size: 9.5px; width: 6%;">
                {{ __('Qty') }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; font-size: 9.5px; width: 11%;">
                {{ __('Price') }}{{ $currencyCode ? ' (' . $currencyCode . ')' : '' }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; font-size: 9.5px; width: 10%;">
                {{ __('Discount') }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; font-size: 9.5px; width: 10%;">
                {{ __('Tax/VAT') }}
            </th>
            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; font-size: 9.5px; width: 13%;">
                {{ __('Total') }}{{ $currencyCode ? ' (' . $currencyCode . ')' : '' }}
            </th>
        </tr>
    </template>

    {{-- ======================================================================
    SUMMARY TEMPLATE
    ======================================================================= --}}

    <template id="invoice-summary">
        <tfoot>
            <tr class="page-break-inside-avoid">
                <td colspan="5" style="border: 1px solid #94a3b8;"></td>
                <td colspan="2" style="padding: 5px 8px; font-weight: 600; color: #475569; border: 1px solid #94a3b8; text-align: right;">
                    {{ __('Subtotal') }}:
                </td>
                <td class="summary-amount-cell" style="padding: 5px 8px; text-align: right; font-weight: 600; color: #1e293b; border: 1px solid #94a3b8; font-size: {{ $getAmountFontSize($formatNumber($invoice->subtotal), 10) }}; white-space: nowrap;">
                    {{ $formatNumber($invoice->subtotal) }}
                </td>
            </tr>

            @if ($invoice->discount_amount > 0)
                <tr class="page-break-inside-avoid">
                    <td colspan="5" style="border: 1px solid #94a3b8;"></td>
                    <td colspan="2" style="padding: 5px 8px; font-weight: 600; color: #475569; border: 1px solid #94a3b8; text-align: right;">
                        {{ __('Discount') }}:
                    </td>
                    <td class="summary-amount-cell" style="padding: 5px 8px; text-align: right; font-weight: 600; border: 1px solid #94a3b8; font-size: {{ $getAmountFontSize('-' . $formatNumber($invoice->discount_amount), 10) }}; white-space: nowrap;">
                        {{ '(-) ' . $formatNumber($invoice->discount_amount) }}
                    </td>
                </tr>
            @endif

            @if (!empty($taxBreakdown))
                @foreach ($taxBreakdown as $taxInfo)
                    @if ($taxInfo['amount'] > 0)
                        <tr class="page-break-inside-avoid">
                            <td colspan="5" style="border: 1px solid #94a3b8;"></td>
                            <td colspan="2" style="padding: 5px 8px; font-weight: 600; color: #475569; border: 1px solid #94a3b8; text-align: right;">
                                {{ $taxInfo['name'] }}:
                            </td>
                            <td class="summary-amount-cell" style="padding: 5px 8px; text-align: right; font-weight: 600; color: #1e293b; border: 1px solid #94a3b8; font-size: {{ $getAmountFontSize($formatNumber($taxInfo['amount']), 10) }}; white-space: nowrap;">
                                {{ '(+) ' . $formatNumber($taxInfo['amount']) }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @elseif($invoice->tax_amount > 0)
                <tr class="page-break-inside-avoid">
                    <td colspan="5" style="border: 1px solid #94a3b8;"></td>
                    <td colspan="2" style="padding: 5px 8px; font-weight: 600; color: #475569; border: 1px solid #94a3b8; text-align: right;">
                        {{ __('Tax') }}:
                    </td>
                    <td class="summary-amount-cell" style="padding: 5px 8px; text-align: right; font-weight: 600; color: #1e293b; border: 1px solid #94a3b8; font-size: {{ $getAmountFontSize($formatNumber($invoice->tax_amount), 10) }}; white-space: nowrap;">
                        {{ '(+) ' . $formatNumber($invoice->tax_amount) }}
                    </td>
                </tr>
            @endif

            <tr class="page-break-inside-avoid" style="font-weight: 700;">
                <td colspan="5" style="border: 1px solid #94a3b8;"></td>
                <td colspan="2" style="padding: 6px 8px; font-size: 11px; color: #0f172a; border: 1px solid #94a3b8; text-align: right;">
                    {{ __('Total') }}:
                </td>
                <td class="summary-amount-cell" style="padding: 6px 8px; font-size: {{ $getAmountFontSize($formatNumber($invoice->total_amount), 11) }}; text-align: right; color: #0f172a; border: 1px solid #94a3b8; white-space: nowrap;">
                    {{ $formatNumber($invoice->total_amount) }}
                </td>
            </tr>


        </tfoot>
    </template>

    {{-- ======================================================================
    PAYMENT SUMMARY TEMPLATE
    ======================================================================= --}}

    @if (count($paymentAllocations) > 0)
        <template id="invoice-payment-summary">
            <div class="mb-4 mt-6 payment-summary-wrapper page-break-inside-avoid">
                <div style="font-size: 10px; font-weight: 700; color: #0f172a; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ __('Payment Summary') }}
                </div>

                <table style="width: 100%; font-size: 10px; table-layout: fixed; border-collapse: collapse; border: 1px solid #94a3b8;">
                    <thead>
                        <tr style="background-color: #e2e8f0; color: #0f172a; font-weight: 700;">
                            <th style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; font-size: 9.5px; width: 6%;">
                                {{ __('SN') }}
                            </th>
                            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: left; font-size: 9.5px; width: 26%;">
                                {{ __('Payment Date') }}
                            </th>
                            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: left; font-size: 9.5px; width: 44%;">
                                {{ __('Payment Method') }}
                            </th>
                            <th style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; font-size: 9.5px; width: 24%;">
                                {{ __('Allocated Amount') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($paymentAllocations as $pIdx => $alloc)
                            @php
                                $payment = $alloc->payment ?? null;
                                $bankAccount = $payment?->bankAccount ?? ($payment?->bank_account ?? null);

                                $method = !empty($bankAccount?->account_name)
                                    ? $bankAccount->account_name .
                                        (!empty($bankAccount->account_number) ? ' (' . $bankAccount->account_number . ')' : '')
                                    : ($payment?->payment_method ?: '-');

                                $paymentDate = $payment?->payment_date ?: $alloc->created_at;
                            @endphp

                            <tr class="page-break-inside-avoid">
                                <td style="padding: 6px 4px; border: 1px solid #94a3b8; text-align: center; vertical-align: top; color: #475569;">
                                    {{ $pIdx + 1 }}
                                </td>
                                <td style="padding: 6px 8px; border: 1px solid #94a3b8; vertical-align: top; color: #475569;">
                                    {{ $formatDate($paymentDate) }}
                                </td>
                                <td style="padding: 6px 8px; border: 1px solid #94a3b8; vertical-align: top; color: #0f172a; font-weight: 500;">
                                    {{ $method }}
                                </td>
                                <td style="padding: 6px 8px; border: 1px solid #94a3b8; text-align: right; vertical-align: top; font-weight: 600; color: #0f172a;">
                                    {{ $formatCurrency($alloc->allocated_amount ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </template>
    @endif

    {{-- ======================================================================
    LAST PAGE FOOTER TEMPLATE
    ======================================================================= --}}

    <template id="invoice-footer">
        <div>
            @if ($invoice->payment_terms)
                <div class="pt-1 text-xs text-gray-600 page-break-inside-avoid">
                    <span class="font-semibold text-gray-800">
                        {{ __('TERMS & CONDITIONS') }}:
                    </span>
                </div>

                <div class="pt-0.5 mb-1.5 text-xs text-gray-600 page-break-inside-avoid">
                    <div class="rich-content text-gray-600 inline-block">
                        {!! $invoice->payment_terms !!}
                    </div>
                </div>
            @endif

            <div class="border-t border-gray-300 pt-1.5 text-center text-xs text-gray-500 page-break-inside-avoid">
                <span>
                    {{ __('Thank you for your business!') }}
                </span>
            </div>
        </div>
    </template>

    {{-- ======================================================================
    GENERATED A4 PAGES
    ======================================================================= --}}

    <div id="invoice-pages"></div>

    {{-- ======================================================================
    PAYMENT STAMP & PAGINATION LOGIC
    ======================================================================= --}}

    <script>
        (() => {
            const sourceRows = Array.from(document.querySelectorAll('#invoice-source-rows > tr[data-item-row]'));
            const pages = document.getElementById('invoice-pages');
            const firstHeader = document.getElementById('invoice-first-header');
            const compactHeader = document.getElementById('invoice-compact-header');
            const tableHead = document.getElementById('invoice-table-head');
            const summary = document.getElementById('invoice-summary');
            const paymentSummary = document.getElementById('invoice-payment-summary');
            const footer = document.getElementById('invoice-footer');

            if (!pages) return;

            function createPage(first = false) {
                const page = document.createElement('div');
                page.className = 'a4-page';

                @if ($bgLetterheadUrl)
                    page.innerHTML = `<img src="{{ $bgLetterheadUrl }}" alt="Letterhead Background" class="letterhead-bg-layer">`;
                @endif

                const content = document.createElement('div');
                content.className = 'a4-content';

                const top = document.createElement('div');
                top.className = 'page-top';

                const header = first ? firstHeader.content.cloneNode(true) : compactHeader.content.cloneNode(true);
                top.appendChild(header);

                const table = document.createElement('table');
                table.className = 'invoice-table';

                const thead = document.createElement('thead');
                thead.appendChild(tableHead.content.cloneNode(true));

                const tbody = document.createElement('tbody');

                table.appendChild(thead);
                table.appendChild(tbody);

                top.appendChild(table);

                const bottom = document.createElement('div');
                bottom.className = 'page-footer';

                content.appendChild(top);
                content.appendChild(bottom);

                page.appendChild(content);



                pages.appendChild(page);

                return { page, content, top, bottom, table, tbody };
            }

            function getHeight(page) {
                return page.top.getBoundingClientRect().height;
            }

            function getAvailableHeight(page) {
                return page.content.getBoundingClientRect().height - page.bottom.getBoundingClientRect().height;
            }

            function fits(page) {
                return getHeight(page) <= getAvailableHeight(page) + 1;
            }

            function addRow(page, sourceRow) {
                const row = sourceRow.cloneNode(true);
                page.tbody.appendChild(row);
                return row;
            }

            function removeLastRow(page) {
                const row = page.tbody.lastElementChild;
                if (!row) return null;
                page.tbody.removeChild(row);
                return row;
            }

            function splitDescriptionRow(page, sourceRow) {
                const descContainer = sourceRow.querySelector('.rich-content');
                if (!descContainer) return null;

                const fullText = (descContainer.innerText || descContainer.textContent || '').trim();
                const wordCount = fullText ? fullText.split(/\s+/).length : 0;
                // Do not split small/medium descriptions; move the whole item to the next page instead
                if (fullText.length < 300 || wordCount < 45) {
                    return null;
                }

                const originalHtml = descContainer.innerHTML;
                const children = Array.from(descContainer.children);

                if (children.length > 1) {
                    for (let i = children.length - 1; i >= 1; i--) {
                        const topPart = children.slice(0, i);
                        const bottomPart = children.slice(i);

                        descContainer.innerHTML = topPart.map(el => el.outerHTML).join('');
                        const row = addRow(page, sourceRow);

                        if (fits(page)) {
                            const contRow = sourceRow.cloneNode(true);
                            const contDesc = contRow.querySelector('.rich-content');
                            contDesc.innerHTML = bottomPart.map(el => el.outerHTML).join('');

                            const cells = Array.from(contRow.children);
                            cells.forEach((cell, idx) => {
                                if (idx !== 2) cell.innerHTML = '';
                            });

                            return contRow;
                        } else {
                            page.tbody.removeChild(row);
                        }
                    }
                }

                const words = fullText.split(/\s+/);
                if (words.length <= 3) {
                    descContainer.innerHTML = originalHtml;
                    return null;
                }

                let low = 1;
                let high = words.length - 1;
                let bestCount = 0;

                while (low <= high) {
                    const mid = Math.floor((low + high) / 2);
                    descContainer.textContent = words.slice(0, mid).join(' ') + '...';

                    const row = addRow(page, sourceRow);
                    const isFit = fits(page);
                    page.tbody.removeChild(row);

                    if (isFit) {
                        bestCount = mid;
                        low = mid + 1;
                    } else {
                        high = mid - 1;
                    }
                }

                if (bestCount > 0) {
                    descContainer.textContent = words.slice(0, bestCount).join(' ') + '...';
                    addRow(page, sourceRow);

                    const contRow = sourceRow.cloneNode(true);
                    const contDesc = contRow.querySelector('.rich-content');
                    contDesc.textContent = '...' + words.slice(bestCount).join(' ');

                    const cells = Array.from(contRow.children);
                    cells.forEach((cell, idx) => {
                        if (idx !== 2) cell.innerHTML = '';
                    });

                    return contRow;
                }

                descContainer.innerHTML = originalHtml;
                return null;
            }

            function buildPages() {
                pages.innerHTML = '';
                const result = [];
                let page = createPage(true);
                result.push(page);

                const queue = sourceRows.map(r => r.cloneNode(true));
                let safetyCounter = 0;
                const maxSafety = sourceRows.length * 50 + 200;

                while (queue.length > 0) {
                    safetyCounter++;
                    if (safetyCounter > maxSafety) {
                        console.warn('Pagination safety limit reached.');
                        break;
                    }

                    const currentRow = queue.shift();
                    const addedNode = addRow(page, currentRow);

                    if (!fits(page)) {
                        page.tbody.removeChild(addedNode);

                        const contRow = splitDescriptionRow(page, currentRow);

                        if (contRow) {
                            page = createPage(false);
                            result.push(page);
                            queue.unshift(contRow);
                        } else {
                            if (page.tbody.children.length > 0) {
                                page = createPage(false);
                                result.push(page);
                                queue.unshift(currentRow);
                            } else {
                                addRow(page, currentRow);
                            }
                        }
                    }
                }

                return result;
            }

            function addSummaryContent(page) {
                if (summary) {
                    page.table.appendChild(summary.content.cloneNode(true));
                }
                if (paymentSummary) {
                    page.top.appendChild(paymentSummary.content.cloneNode(true));
                }
            }

            function addFooterContent(page) {
                if (footer) {
                    const footerWrapper = document.createElement('div');
                    footerWrapper.className = 'mt-6 terms-footer-wrapper page-break-inside-avoid';
                    footerWrapper.appendChild(footer.content.cloneNode(true));
                    page.top.appendChild(footerWrapper);
                }
            }

            function addLastPage() {
                let result = buildPages();
                let last = result[result.length - 1];

                addSummaryContent(last);

                if (!fits(last)) {
                    const oldSummary = last.table.querySelector('tfoot');
                    if (oldSummary) oldSummary.remove();

                    const oldPayment = last.top.querySelector('.mb-4.page-break-inside-avoid');
                    if (oldPayment) oldPayment.remove();

                    let movedRow = null;
                    if (last.tbody.children.length > 0) {
                        if (last.tbody.children.length === 1) {
                            const singleRow = last.tbody.firstElementChild;
                            const contRow = splitDescriptionRow(last, singleRow);
                            movedRow = contRow ? contRow : removeLastRow(last);
                        } else {
                            movedRow = removeLastRow(last);
                        }
                    }

                    const finalPage = createPage(false);
                    if (movedRow) {
                        addRow(finalPage, movedRow);
                    }

                    addSummaryContent(finalPage);
                    addFooterContent(finalPage);
                    result.push(finalPage);

                    if (last.tbody.children.length === 0 && result.length > 1) {
                        last.page.remove();
                        result = result.filter(p => p !== last);
                    }
                } else {
                    addFooterContent(last);

                    if (!fits(last)) {
                        const oldFooter = last.top.querySelector('.terms-footer-wrapper');
                        if (oldFooter) oldFooter.remove();

                        const termsPage = createPage(false);
                        if (termsPage.tbody.children.length === 0) {
                            termsPage.table.style.display = 'none';
                        }
                        if (footer) {
                            addFooterContent(termsPage);
                        }
                        result.push(termsPage);
                    }
                }

                result.forEach((page, index) => {
                    const psWrapper = page.top.querySelector('.payment-summary-wrapper');
                    if (psWrapper) {
                        const hasItemsOnPage = page.table.style.display !== 'none' && page.tbody.children.length > 0;
                        if (!hasItemsOnPage) {
                            psWrapper.classList.remove('mt-6');
                            psWrapper.classList.add('mt-0');
                        }
                    }

                    page.page.style.pageBreakAfter = index === result.length - 1 ? 'avoid' : 'always';
                });

                return result;
            }

            async function waitForPage() {
                if (document.fonts?.ready) {
                    await document.fonts.ready;
                }

                const images = Array.from(document.images);

                await Promise.all(
                    images.map((image) => {
                        if (image.complete) return Promise.resolve();

                        return new Promise((resolve) => {
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        });
                    })
                );

                await new Promise((resolve) =>
                    requestAnimationFrame(() => requestAnimationFrame(resolve))
                );
            }

            function fitSummaryAmounts() {
                const cells = document.querySelectorAll('.summary-amount-cell');
                cells.forEach((cell) => {
                    let currentSize = parseFloat(window.getComputedStyle(cell).fontSize) || 10;
                    const minSize = 6;
                    while (cell.scrollWidth > cell.clientWidth && currentSize > minSize) {
                        currentSize -= 0.5;
                        cell.style.fontSize = currentSize + 'px';
                    }
                });
            }

            async function start() {
                await waitForPage();
                addLastPage();
                fitSummaryAmounts();

                await new Promise((resolve) => requestAnimationFrame(resolve));

                window.invoicePagesReady = true;
                document.dispatchEvent(new Event('invoice-pages-ready'));
            }

            start();
        })();
    </script>

    {{-- ======================================================================
    PRINT / PDF LOADER & AUTO PRINT SCRIPT
    ======================================================================= --}}

    @if (empty($isServerPdf))
        <div id="downloading-loader" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ __('Generating PDF...') }}
                    </p>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const startPrint = () => {
                    window.onafterprint = function() {
                        window.close();
                    };
                    window.print();
                };

                if (window.invoicePagesReady) {
                    startPrint();
                    return;
                }

                document.addEventListener('invoice-pages-ready', startPrint, { once: true });
            });
        </script>
    @endif

</body>

</html>