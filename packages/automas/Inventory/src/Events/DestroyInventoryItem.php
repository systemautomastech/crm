<?php

namespace Automas\Inventory\Events;

use Automas\Inventory\Models\InventoryItem;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyInventoryItem
{
    use Dispatchable;

    public function __construct(
        public InventoryItem $item
    ) {}
}
