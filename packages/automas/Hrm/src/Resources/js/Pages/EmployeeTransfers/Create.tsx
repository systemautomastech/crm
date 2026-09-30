import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { DatePicker } from '@/components/ui/date-picker';
import { Textarea } from '@/components/ui/textarea';
import MediaPicker from '@/components/MediaPicker';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CreateEmployeeTransferProps, CreateEmployeeTransferFormData } from './types';
import { usePage, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Create({ onSuccess }: CreateEmployeeTransferProps) {
    const { employees, branches, departments, designations, auth } = usePage<any>().props;
    const [filteredToDepartments, setFilteredToDepartments] = useState<any[]>([]);
    const [filteredToDesignations, setFilteredToDesignations] = useState<any[]>([]);
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm<CreateEmployeeTransferFormData>({
        employee_id: '',
        to_branch_id: '',
        to_department_id: '',
        to_designation_id: '',
        effective_date: '',
        reason: '',
        document: '',
    });

    // Handle dependent dropdowns
    useEffect(() => {
        if (data.to_branch_id) {
            const branchDepartments = departments.filter((dept: any) => dept.branch_id.toString() === data.to_branch_id);
            setFilteredToDepartments(branchDepartments);
            // Clear department if it doesn't belong to selected branch
            if (data.to_department_id) {
                const departmentExists = branchDepartments.find((dept: any) => dept.id.toString() === data.to_department_id);
                if (!departmentExists) {
                    setData('to_department_id', '');
                    setData('to_designation_id', '');
                }
            }
        } else {
            setFilteredToDepartments([]);
            if (data.to_department_id) setData('to_department_id', '');
            if (data.to_designation_id) setData('to_designation_id', '');
        }
    }, [data.to_branch_id]);

    useEffect(() => {
        if (data.to_department_id) {
            const departmentDesignations = designations.filter((desig: any) => desig.department_id.toString() === data.to_department_id);
            setFilteredToDesignations(departmentDesignations);
            // Clear designation if it doesn't belong to selected department
            if (data.to_designation_id) {
                const designationExists = departmentDesignations.find((desig: any) => desig.id.toString() === data.to_designation_id);
                if (!designationExists) {
                    setData('to_designation_id', '');
                }
            }
        } else {
            setFilteredToDesignations([]);
            if (data.to_designation_id) setData('to_designation_id', '');
        }
    }, [data.to_department_id]);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('hrm.employee-transfers.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Create Employee Transfer')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="employee_id" required>{t('Employee')}</Label>
                    <Select value={data.employee_id?.toString() || ''} onValueChange={(value) => setData('employee_id', value)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select Employee')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {employees?.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.employee_id} />
                    {employees?.length === 0 && auth?.user?.permissions?.includes('create-employees') && (
                        <p className="text-xs text-gray-500 mt-1">
                            {t('Create employee here.')} <button type="button" onClick={() => router.get(route('hrm.employees.index'))} className="text-blue-600 hover:underline">{t('Create employee')}</button>
                        </p>
                    )}
                </div>
                
                <div>
                    <Label htmlFor="to_branch_id" required>{t('To Branch')} </Label>
                    <Select value={data.to_branch_id?.toString() || ''} onValueChange={(value) => setData('to_branch_id', value)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select To Branch')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {branches?.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.branch_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.to_branch_id} />
                    {branches?.length === 0 && auth?.user?.permissions?.includes('create-branches') && (
                        <p className="text-xs text-gray-500 mt-1">
                            {t('Create branch here.')} <button type="button" onClick={() => router.get(route('hrm.branches.index'))} className="text-blue-600 hover:underline">{t('Create branch')}</button>
                        </p>
                    )}
                </div>
                
                <div>
                    <Label htmlFor="to_department_id" required>{t('To Department')} </Label>
                    <Select 
                        value={data.to_department_id?.toString() || ''} 
                        onValueChange={(value) => setData('to_department_id', value)}
                        disabled={!data.to_branch_id}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder={data.to_branch_id ? t('Select To Department') : t('Select To Branch first')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {filteredToDepartments?.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.department_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.to_department_id} />
                    {(() => {
                        const branchDepartments = data.to_branch_id ? departments.filter((dept: any) => dept.branch_id.toString() === data.to_branch_id) : [];
                        return ((data.to_branch_id && branchDepartments.length === 0) || (!data.to_branch_id && departments?.length === 0)) && auth?.user?.permissions?.includes('create-departments');
                    })() && (
                        <p className="text-xs text-gray-500 mt-1">
                            {t('Create department here.')} <button type="button" onClick={() => router.get(route('hrm.departments.index'))} className="text-blue-600 hover:underline">{t('Create department')}</button>
                        </p>
                    )}
                </div>
                
                <div>
                    <Label htmlFor="to_designation_id" required>{t('To Designation')} </Label>
                    <Select 
                        value={data.to_designation_id?.toString() || ''} 
                        onValueChange={(value) => setData('to_designation_id', value)}
                        disabled={!data.to_department_id}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder={data.to_department_id ? t('Select To Designation') : t('Select To Department first')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {filteredToDesignations?.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.designation_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.to_designation_id} />
                    {(() => {
                        const departmentDesignations = data.to_department_id ? designations.filter((desig: any) => desig.department_id.toString() === data.to_department_id) : [];
                        return ((data.to_department_id && departmentDesignations.length === 0) || (!data.to_department_id && designations?.length === 0)) && auth?.user?.permissions?.includes('create-designations');
                    })() && (
                        <p className="text-xs text-gray-500 mt-1">
                            {t('Create designation here.')} <button type="button" onClick={() => router.get(route('hrm.designations.index'))} className="text-blue-600 hover:underline">{t('Create designation')}</button>
                        </p>
                    )}
                </div>
                
                <div>
                    <Label required>{t('Effective Date')}</Label>
                    <DatePicker
                        value={data.effective_date}
                        onChange={(date) => setData('effective_date', date)}
                        placeholder={t('Select Effective Date')}
                    />
                    <InputError message={errors.effective_date} />
                </div>
                
                <div>
                    <Label htmlFor="reason" required>{t('Reason')}</Label>
                    <Textarea
                        id="reason"
                        value={data.reason}
                        onChange={(e) => setData('reason', e.target.value)}
                        placeholder={t('Enter Reason')}
                        rows={3}
                        required
                    />
                    <InputError message={errors.reason} />
                </div>
                
                <div>
                    <MediaPicker
                        label={t('Document')}
                        value={data.document}
                        onChange={(value) => setData('document', Array.isArray(value) ? value[0] || '' : value)}
                        placeholder={t('Select Document...')}
                        showPreview={true}
                        multiple={false}
                    />
                    <InputError message={errors.document} />
                </div>
                
                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
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