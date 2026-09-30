<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Deal;
use Automas\Lead\Models\DealStage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class DealMoved
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Deal $deal,
        public DealStage $oldStage
    ) {}
}