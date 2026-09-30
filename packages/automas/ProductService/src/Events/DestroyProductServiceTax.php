<?php

namespace Automas\ProductService\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\ProductService\Models\ProductServiceTax;

class DestroyProductServiceTax
{
    use Dispatchable;

    public function __construct(
        public ProductServiceTax $tax,
    ) {}
}
