<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\AwardType;

class DestroyAwardType
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public AwardType $awardType
    )
    {}
}