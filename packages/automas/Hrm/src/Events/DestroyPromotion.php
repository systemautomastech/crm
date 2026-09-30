<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Promotion;

class DestroyPromotion
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Promotion $promotion
    )
    {}
}