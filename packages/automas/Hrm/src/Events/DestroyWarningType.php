<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\WarningType;

class DestroyWarningType
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public WarningType $warningType
    )
    {}
}