<?php

namespace Automas\Inventory\Events;

use Automas\Inventory\Models\InventoryItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateInventoryItem
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public InventoryItem $item
    ) {}
}
