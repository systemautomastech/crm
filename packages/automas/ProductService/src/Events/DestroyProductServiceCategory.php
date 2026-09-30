<?php

namespace Automas\ProductService\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\ProductService\Models\ProductServiceCategory;

class DestroyProductServiceCategory
{
    use Dispatchable;

    public function __construct(
        public ProductServiceCategory $itemCategory,
    ) {}
}
