<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Automas\Hrm\Models\AnnouncementCategory;

class CreateAnnouncementCategory
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Request $request,
        public AnnouncementCategory $announcementCategory
    ) {}
}