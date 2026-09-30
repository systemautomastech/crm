<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Automas\Hrm\Models\Event;

class CreateEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Request $request,
        public Event $event
    ) {}
}