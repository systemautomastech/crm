import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { CreateShiftProps, CreateShiftFormData } from './types';
import { usePage } from '@inertiajs/react';
import { TimePicker } from '@/components/ui/time-picker';

export default function Create({ onSuccess }: CreateShiftProps) {
    const { users } = usePage<any>().props;

    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm<CreateShiftFormData>({
        shift_name: '',
        start_time: '',
        end_time: '',
        break_start_time: '',
        break_end_time: '',
        is_night_shift: false,
    });



    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('hrm.shifts.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Create Shift')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="shift_name">{t('Shift Name')}</Label>
                    <Input
                        id="shift_name"
                        type="text"
                        value={data.shift_name}
                        onChange={(e) => setData('shift_name', e.target.value)}
                        placeholder={t('Enter Shift Name')}
                        required
                    />
                    <InputError message={errors.shift_name} />
                </div>
                
                <div>
                    <Label htmlFor="start_time">{t('Start Time')}</Label>
                    <TimePicker
                        id="start_time"
                        value={data.start_time}
                        onChange={(value) => setData('start_time', value)}
                        placeholder={t('Start Time')}
                        required
                    />
                    <InputError message={errors.start_time} />
                </div>
                
                <div>
                    <Label htmlFor="end_time">{t('End Time')}</Label>
                      <TimePicker
                        id="end_time"
                        value={data.end_time}
                        onChange={(value) => setData('end_time', value)}
                        placeholder={t('End Time')}
                        required
                    />
                    <InputError message={errors.end_time} />
                </div>
                
                <div>
                    <Label htmlFor="break_start_time">{t('Break Start Time')}</Label>
                    <TimePicker
                        id="break_start_time"
                        value={data.break_start_time}
                        onChange={(value) => setData('break_start_time', value)}
                        placeholder={t('Break Start Time')}
                        required
                    />
                    <InputError message={errors.break_start_time} />
                </div>
                
                <div>
                    <Label htmlFor="break_end_time">{t('Break End Time')}</Label>
                    <TimePicker
                        id="break_end_time"
                        value={data.break_end_time}
                        onChange={(value) => setData('break_end_time', value)}
                        placeholder={t('Break End Time')}
                        required
                    />
                    <InputError message={errors.break_end_time} />
                </div>
                
                <div className="flex items-center space-x-2">
                    <Checkbox
                        id="is_night_shift"
                        checked={data.is_night_shift || false}
                        onCheckedChange={(checked) => setData('is_night_shift', !!checked)}
                    />
                    <Label htmlFor="is_night_shift" className="cursor-pointer">{t('Is Night Shift')}</Label>
                    <InputError message={errors.is_night_shift} />
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