<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;

class SentProposal
{
    use Dispatchable;

    public function __construct(
        public Proposal $proposal
    ) {}
}
