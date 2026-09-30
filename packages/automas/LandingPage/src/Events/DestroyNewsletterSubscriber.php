<?php

namespace Automas\LandingPage\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\LandingPage\Models\NewsletterSubscriber;

class DestroyNewsletterSubscriber
{
    use Dispatchable;

    public function __construct(
        public NewsletterSubscriber $subscriber,
    ) {}
}