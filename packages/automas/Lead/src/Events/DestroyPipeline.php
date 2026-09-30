<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Pipeline;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyPipeline
{
    use Dispatchable;

    public function __construct(
        public Pipeline $pipeline
    ) {}
}