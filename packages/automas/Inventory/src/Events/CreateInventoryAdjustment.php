<?php

namespace Automas\Inventory\Events;

use Automas\Inventory\Models\InventoryAdjustment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateInventoryAdjustment
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public InventoryAdjustment $adjustment
    ) {}
}