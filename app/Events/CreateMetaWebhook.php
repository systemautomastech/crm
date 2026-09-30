<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CreateMetaWebhook
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
    ){}
}
