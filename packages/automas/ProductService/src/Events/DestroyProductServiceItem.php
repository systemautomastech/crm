<?php

namespace Automas\ProductService\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\ProductService\Models\ProductServiceItem;

class DestroyProductServiceItem
{
    use Dispatchable;

    public function __construct(
        public ProductServiceItem $item,
    ) {}
}
