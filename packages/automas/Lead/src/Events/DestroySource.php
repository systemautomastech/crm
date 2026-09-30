<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Source;
use Illuminate\Foundation\Events\Dispatchable;

class DestroySource
{
    use Dispatchable;

    public function __construct(
        public Source $source
    ) {}
}