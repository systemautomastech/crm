<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Branch;

class DestroyBranch
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Branch $branch
    )
    {}
}