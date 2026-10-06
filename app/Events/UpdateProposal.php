<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateProposal
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Proposal $proposal
    ) {}
}
