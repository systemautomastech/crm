<?php

namespace Automas\Account\Listeners;

use App\Events\DefaultData;
use Automas\Account\Helpers\AccountUtility;
use Automas\Test\Models\TestItem;

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
            if (in_array("Account", $user_module))
            {
                AccountUtility::defaultdata($company_id);
                AccountUtility::GivePermissionToVendor($company_id);
            }
        }
    }
}
