<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;

class RejectProposal
{
    use Dispatchable;

    public function __construct(
        public Proposal $proposal
    ) {}
}
