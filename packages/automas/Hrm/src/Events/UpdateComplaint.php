<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Automas\Hrm\Models\Complaint;

class UpdateComplaint
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Request $request,
        public Complaint $complaint
    ) {

    }
}