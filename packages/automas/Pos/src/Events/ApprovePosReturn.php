<?php

namespace Automas\Pos\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Pos\Models\PosReturn;

class ApprovePosReturn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PosReturn $return
    ) {}
}
