<?php

namespace Automas\Taskly\Listeners;

use App\Events\DefaultData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Automas\Taskly\Models\TaskStage;
use Automas\Taskly\Models\BugStage;

class DataDefault
{
    public function __construct()
    {
        //
    }

    public function handle(DefaultData $event)
    {
        $company_id = $event->company_id;
        $user_module = $event->user_module ? explode(',', $event->user_module) : [];
        if(!empty($user_module))
        {
            if (in_array("Taskly", $user_module))
            {
                TaskStage::defaultdata($company_id);
                BugStage::defaultdata($company_id);
            }
        }
    }
}
