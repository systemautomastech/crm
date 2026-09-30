<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\EmployeeDocumentType;

class DestroyEmployeeDocumentType
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public EmployeeDocumentType $employeedocumenttype
    ) {}
}