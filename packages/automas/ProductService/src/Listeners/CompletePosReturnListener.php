<?php

namespace Automas\ProductService\Listeners;

use Automas\Pos\Events\CompletePosReturn;
use Automas\ProductService\Models\WarehouseStock;

class CompletePosReturnListener
{
    public function handle(CompletePosReturn $event)
    {
        $posReturn = $event->return;
        foreach ($posReturn->items()->get() as $item) {
            $stock = WarehouseStock::where('warehouse_id', $posReturn->warehouse_id)
                ->where('product_id', $item->product_id)
                ->first();
            if ($stock) {
                $stock->increment('quantity', $item->return_quantity);
            } else {
                WarehouseStock::create([
                    'warehouse_id' => $posReturn->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->return_quantity,
                    'created_by' => $posReturn->created_by
                ]);
            }
        }
    }
}
