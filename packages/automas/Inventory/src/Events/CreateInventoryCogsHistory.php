<?php

namespace Automas\Inventory\Events;

use App\Models\SalesInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Automas\Inventory\Models\InventoryItem;

class CreateInventoryCogsHistory
{
    use Dispatchable;

    public function __construct(
        public InventoryItem $inventoryItem,
        public SalesInvoice $salesInvoice
    ) {}
}