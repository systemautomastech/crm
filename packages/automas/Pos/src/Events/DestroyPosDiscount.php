<?php

namespace Automas\Pos\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Pos\Models\PosDiscount;

class DestroyPosDiscount
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PosDiscount $discount
    ) {}
}
