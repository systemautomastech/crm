<?php

namespace Automas\BiometricAttendance\Listeners;

use Automas\Hrm\Events\CreateEmployee;
use Automas\Hrm\Events\UpdateEmployee;
use Automas\Hrm\Models\Employee;

class SaveBiometricFieldValues
{
    public function handle($event)
    {
        if ($event instanceof CreateEmployee || $event instanceof UpdateEmployee) {
            $request = $event->request;
            $employee = $event->employee;
            
            if ($request->has('biometric_emp_id')) {
                // Ensure biometric_emp_id is fillable
                $employee->fillable(array_merge($employee->getFillable(), ['biometric_emp_id']));
                
                $employee->update([
                    'biometric_emp_id' => $request->biometric_emp_id
                ]);
                
                // Refresh to get updated value
                $employee->refresh();
            }
        }
    }
}