import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import MediaPicker from '@/components/MediaPicker';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { EditCompanyPolicyProps, EditCompanyPolicyFormData } from './types';
import { usePage } from '@inertiajs/react';

export default function EditCompanyPolicy({ companyPolicy, onSuccess }: EditCompanyPolicyProps) {
    const { branches } = usePage<any>().props;
    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm<EditCompanyPolicyFormData>({
        branch_id: companyPolicy.branch_id?.toString() || '',
        title: companyPolicy.title || '',
        description: companyPolicy.description ?? '',
        attachment: companyPolicy.attachment || ''
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('hrm.company-policies.update', companyPolicy.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Edit Company Policy')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="branch_id">{t('Branch')}</Label>
                    <Select value={data.branch_id?.toString() || ''} onValueChange={(value) => setData('branch_id', value)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select Branch')} />
                        </SelectTrigger>
                        <SelectContent searchable={true}>
                            {branches?.map((item: any) => (
                                <SelectItem key={item.id} value={item.id.toString()}>
                                    {item.branch_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.branch_id} />
                </div>

                <div>
                    <Label htmlFor="title" required>{t('Title')}</Label>
                    <Input
                        id="title"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        placeholder={t('Enter Title')}
                        required
                    />
                    <InputError message={errors.title} />
                </div>

                <div>
                    <Label htmlFor="description">{t('Description')}</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('Enter Description')}
                        rows={4}
                    />
                    <InputError message={errors.description} />
                </div>

                <div>
                    <MediaPicker
                        label={t('Attachment')}
                        value={data.attachment}
                        onChange={(value) => setData('attachment', Array.isArray(value) ? value[0] || '' : value)}
                        placeholder={t('Select Attachment...')}
                        showPreview={true}
                        multiple={false}
                    />
                    <InputError message={errors.attachment} />
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Updating...') : t('Update')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}
