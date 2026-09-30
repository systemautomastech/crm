<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyUserDeal
{
    use Dispatchable;

    public function __construct(
        public Deal $deal,
    ) {}
}