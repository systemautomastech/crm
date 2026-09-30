<?php

namespace Automas\Inventory\Providers;

use App\Events\PostPurchaseInvoice;
use App\Events\PostSalesInvoice;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\Inventory\Listeners\CreateProductServiceItemLis;
use Automas\Inventory\Listeners\PostPurchaseInvoiceLis;
use Automas\Inventory\Listeners\PostSalesInvoiceLis;
use Automas\Inventory\Listeners\UpdateProductServiceItemLis;
use Automas\ProductService\Events\CreateProductServiceItem;
use Automas\ProductService\Events\UpdateProductServiceItem;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [        
        PostPurchaseInvoice::class => [
            PostPurchaseInvoiceLis::class,
        ],
        PostSalesInvoice::class => [
            PostSalesInvoiceLis::class,
        ],
        CreateProductServiceItem::class => [
            CreateProductServiceItemLis::class,
        ],
        UpdateProductServiceItem::class => [
            UpdateProductServiceItemLis::class,
        ],
    ];
}