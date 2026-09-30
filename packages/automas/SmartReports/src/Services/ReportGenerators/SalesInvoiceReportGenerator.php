<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;

class SalesInvoiceReportGenerator
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
        $query = SalesInvoice::query()
            ->join('users', 'sales_invoices.customer_id', '=', 'users.id')
            ->leftJoin('warehouses', 'sales_invoices.warehouse_id', '=', 'warehouses.id')
            ->where('sales_invoices.created_by', creatorId())
            ->select(
                'sales_invoices.*',
                'users.name as customer_name',
                'users.email as customer_email',
                DB::raw('COALESCE(warehouses.name, "-") as warehouse_name')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('sales_invoices.invoice_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['customer_ids'])) {
            $query->whereIn('sales_invoices.customer_id', $filters['customer_ids']);
        }

        if (!empty($filters['warehouse_ids'])) {
            $query->whereIn('sales_invoices.warehouse_id', $filters['warehouse_ids']);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('sales_invoices.status', $filters['status']);
        }

        return $query->orderBy('sales_invoices.invoice_date', 'desc')
            ->orderBy('sales_invoices.invoice_number', 'desc')
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
        $invoiceQuery = SalesInvoice::where('created_by', creatorId());

        $customers = User::whereIn('id', (clone $invoiceQuery)->pluck('customer_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($c) => ['value' => $c->id, 'label' => $c->name . ' (' . $c->email . ')']);

        $warehouses = Warehouse::whereIn('id', (clone $invoiceQuery)->pluck('warehouse_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($w) => ['value' => $w->id, 'label' => $w->name]);

        return [
            'customers'  => $customers,
            'warehouses' => $warehouses,
        ];
    }
}
