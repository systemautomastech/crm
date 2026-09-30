<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\IpRestrict;

class DestroyIpRestrict
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public IpRestrict $ipRestrict
    )
    {}
}