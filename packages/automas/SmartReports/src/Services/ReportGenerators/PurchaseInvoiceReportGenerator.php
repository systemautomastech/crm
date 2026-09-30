<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use App\Models\PurchaseInvoice;
use App\Models\User;
use App\Models\Warehouse;

class PurchaseInvoiceReportGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = PurchaseInvoice::query()
            ->join('users', 'purchase_invoices.vendor_id', '=', 'users.id')
            ->leftJoin('warehouses', 'purchase_invoices.warehouse_id', '=', 'warehouses.id')
            ->where('purchase_invoices.created_by', creatorId())
            ->select(
                'purchase_invoices.*',
                'users.name as vendor_name',
                'users.email as vendor_email',
                DB::raw('COALESCE(warehouses.name, "-") as warehouse_name')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('purchase_invoices.invoice_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['vendor_ids'])) {
            $query->whereIn('purchase_invoices.vendor_id', $filters['vendor_ids']);
        }

        if (!empty($filters['warehouse_ids'])) {
            $query->whereIn('purchase_invoices.warehouse_id', $filters['warehouse_ids']);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('purchase_invoices.status', $filters['status']);
        }

        return $query->orderBy('purchase_invoices.invoice_date', 'desc')
            ->orderBy('purchase_invoices.invoice_number', 'desc')
            ->get();
    }

    public function getSummary($data)
    {
        return [
            'total_invoices'        => $data->count(),
            'total_amount'          => round($data->sum('total_amount'), 2),
            'total_paid'            => round($data->sum('paid_amount'), 2),
            'total_balance'         => round($data->sum('balance_amount'), 2),
            'total_tax'             => round($data->sum('tax_amount'), 2),
            'average_invoice_value' => $data->count() > 0 ? round($data->sum('total_amount') / $data->count(), 2) : 0,
        ];
    }

    public function getFilterOptions()
    {
        $invoiceQuery = PurchaseInvoice::where('created_by', creatorId());

        $vendors = User::whereIn('id', (clone $invoiceQuery)->pluck('vendor_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($v) => ['value' => $v->id, 'label' => $v->name . ' (' . $v->email . ')']);

        $warehouses = Warehouse::whereIn('id', (clone $invoiceQuery)->pluck('warehouse_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($w) => ['value' => $w->id, 'label' => $w->name]);

        return [
            'vendors'    => $vendors,
            'warehouses' => $warehouses,
        ];
    }
}
