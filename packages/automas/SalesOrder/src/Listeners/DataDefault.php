<?php

namespace Automas\SalesOrder\Listeners;

use App\Events\DefaultData;
use Automas\SalesOrder\Models\SalesOrderUtility;

class DataDefault
{
    public function __construct() {}

    public function handle(DefaultData $event): void
    {
        $companyId = $event->company_id;
        $modules   = $event->user_module ? explode(',', $event->user_module) : [];

        if (!empty($modules) && in_array('SalesOrder', $modules)) {
            SalesOrderUtility::defaultData($companyId);
        }
    }
}