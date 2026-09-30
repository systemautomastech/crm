<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Event;

class DestroyEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Event $event
    )
    {}
}