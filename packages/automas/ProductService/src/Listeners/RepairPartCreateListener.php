<?php

namespace Automas\ProductService\Listeners;

use Automas\RepairManagementSystem\Events\UpdateRepairOrderSteps;
use Automas\ProductService\Models\WarehouseStock;

class RepairPartCreateListener
{
    public function handle(UpdateRepairOrderSteps $event)
    {
        if ($event->response != 4) {
            return;
        }

        foreach ($event->repair_order_request->repairParts()->get() as $part) {
            $stock = WarehouseStock::where('product_id', $part->product_id)
                ->where('quantity', '>=', $part->quantity)
                ->first();

            if ($stock) {
                $stock->decrement('quantity', $part->quantity);
            }
        }
    }
}
