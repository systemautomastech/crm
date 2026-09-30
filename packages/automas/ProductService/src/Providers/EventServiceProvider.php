<?php

namespace Automas\ProductService\Providers;

use App\Events\PostPurchaseInvoice;
use App\Events\ApprovePurchaseReturn;
use App\Events\CompleteSalesReturn;
use App\Events\PostSalesInvoice;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\Pos\Events\CreatePos;
use Automas\Pos\Events\CompletePosReturn;
use Automas\ProductService\Listeners\PostPurchaseInvoiceListener;
use Automas\ProductService\Listeners\ApprovePurchaseReturnListener;
use Automas\ProductService\Listeners\CompleteSalesReturnListener;
use Automas\ProductService\Listeners\PosCreateListener;
use Automas\ProductService\Listeners\CompletePosReturnListener;
use Automas\ProductService\Listeners\ConvertSalesRetainerListener;
use Automas\ProductService\Listeners\PostSalesInvoiceListener;
use Automas\ProductService\Listeners\RepairPartCreateListener;
use Automas\RepairManagementSystem\Events\UpdateRepairOrderSteps;
use Automas\Retainer\Events\ConvertSalesRetainer;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostPurchaseInvoice::class => [
            PostPurchaseInvoiceListener::class,
        ],
        PostSalesInvoice::class => [
            PostSalesInvoiceListener::class,
        ],
        ApprovePurchaseReturn::class => [
            ApprovePurchaseReturnListener::class,
        ],
        CompleteSalesReturn::class => [
            CompleteSalesReturnListener::class,
        ],
        CreatePos::class => [
            PosCreateListener::class,
        ],
        CompletePosReturn::class => [
            CompletePosReturnListener::class,
        ],
        ConvertSalesRetainer::class => [
            ConvertSalesRetainerListener::class,
        ],
        UpdateRepairOrderSteps::class => [
            RepairPartCreateListener::class,
        ],
    ];
}
