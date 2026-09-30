<?php

namespace Automas\ProductService\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Automas\ProductService\Models\ProductServiceUnit;

class DestroyProductServiceUnit
{
    use Dispatchable;

    public function __construct(
        public ProductServiceUnit $unit,
    ) {}
}
