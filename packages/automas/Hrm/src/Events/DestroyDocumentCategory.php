<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\DocumentCategory;

class DestroyDocumentCategory
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public DocumentCategory $documentCategory
    )
    {}
}