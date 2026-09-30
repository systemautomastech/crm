<?php

namespace App\Events;

use App\Models\ProposalSubject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateProposalSubject
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public ProposalSubject $subject
    ) {}
}
