<?php

namespace App\Events;

use App\Models\ProposalSubject;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyProposalSubject
{
    use Dispatchable;

    public function __construct(
        public ProposalSubject $subject
    ) {}
}
