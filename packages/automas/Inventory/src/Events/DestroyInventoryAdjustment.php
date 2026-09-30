<?php

namespace Automas\Inventory\Events;

use Automas\Inventory\Models\InventoryAdjustment;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyInventoryAdjustment
{
    use Dispatchable;

    public function __construct(
        public InventoryAdjustment $adjustment
    ) {}
}