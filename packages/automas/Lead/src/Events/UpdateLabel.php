<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Label;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateLabel
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Label $label
    ) {}
}