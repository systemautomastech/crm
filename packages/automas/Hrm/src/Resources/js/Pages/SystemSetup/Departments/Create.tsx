import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm, router } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePage } from '@inertiajs/react';

import { CreateDepartmentProps, DepartmentFormData } from './types';

export default function Create({ onSuccess, branches }: CreateDepartmentProps) {
    const { auth } = usePage<any>().props;
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm<DepartmentFormData>({
        department_name: '',
        branch_id: '',
        employee_prefix: 'EMP',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('hrm.departments.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Create Department')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="department_name">{t('Name')}</Label>
                    <Input
                        id="department_name"
                        type="text"
                        value={data.department_name}
                        onChange={(e) => setData('department_name', e.target.value)}
                        placeholder={t('Enter Department Name')}
                        required
                    />
                    <InputError message={errors.department_name} />
                </div>

                <div>
                    <Label htmlFor="branch_id" required>{t('Branch')}</Label>
                    <Select value={data.branch_id?.toString() || ''} onValueChange={(value) => setData('branch_id', value)} required>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select Branch')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {branches.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.branch_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.branch_id} />
                    {branches?.length === 0 && auth?.user?.permissions?.includes('create-branches') && (
                        <p className="text-xs text-gray-500 mt-1">
                            {t('Create branch here.')} <button type="button" onClick={(e) => { e.preventDefault(); router.get(route('hrm.branches.index')); }} className="text-blue-600 hover:underline">{t('Create branch')}</button>
                        </p>
                    )}
                </div>

                <div>
                    <Label htmlFor="employee_prefix">{t('Employee ID Prefix')}</Label>
                    <Input
                        id="employee_prefix"
                        name="employee_prefix"
                        type="text"
                        value={data.employee_prefix}
                        onChange={(e) =>
                            setData('employee_prefix', e.target.value)
                        }
                        placeholder={t('Leave blank to use EMP')}
                        maxLength={5}
                    />

                    <InputError message={errors.employee_prefix} />
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onSuccess()}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Creating...') : t('Create')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}