<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\CompanyPolicy;

class DestroyCompanyPolicy
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CompanyPolicy $companyPolicy
    ) {}
}
