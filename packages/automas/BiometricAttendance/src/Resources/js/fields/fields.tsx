import React from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';

const BiometricEmployeeIdComponent = ({ data, setData, errors }: any) => {
    const { t } = useTranslation();

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setData('biometric_emp_id', e.target.value);
    };

    return (
        <div>
            <Label htmlFor="biometric_emp_id">{t('Biometric Employee Id')}</Label>
            <Input
                id="biometric_emp_id"
                name="biometric_emp_id"
                type="text"
                value={data.biometric_emp_id || ''}
                onChange={handleChange}
                placeholder={t('Enter biometric employee id')}
            />
            <InputError message={errors.biometric_emp_id} />
        </div>
    );
};

export const biometricEmployeeIdFields = (data: any, setData: any, errors: any, mode: string = 'create') => {
    const { auth } = usePage().props as any;
    
    // Check if BiometricAttendance package is available and active
    const isBiometricPackageActive = auth?.user?.activatedPackages?.includes('BiometricAttendance');
    
    // Return empty array if package not active
    if (!isBiometricPackageActive) {
        return [];
    }

    return [{
        id: 'biometric_emp_id_field',
        order: 50,
        component: (
            <BiometricEmployeeIdComponent
                key="biometric_emp_id_field"
                data={data}
                setData={setData}
                errors={errors}
            />
        )
    }];
};