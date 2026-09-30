import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/ui/input-error';
import { useFormFields } from '@/hooks/useFormFields';

interface CreatePosCounterFormData {
    name: string;
    code: string;
    status: boolean;
    description: string;
}

interface CreateProps {
    onSuccess?: () => void;
}

export default function Create({ onSuccess }: CreateProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm<CreatePosCounterFormData>({
        name: '',
        code: '',
        status: true,
        description: '',
    });

    // Bank Account Field
    const bankAccountField = useFormFields('bankAccountField', data, setData, errors);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('pos.billing-counters.store'), {
            onSuccess: () => {
                onSuccess?.();
            },
        });
    };

    return (
        <DialogContent className="max-w-lg">
            <DialogHeader>
                <DialogTitle>{t('Create POS Billing Counter')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="name">{t('Counter Name')}</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder={t('Enter Counter name')}
                        required
                    />
                    <InputError message={errors.name} />
                </div>

                <div>
                    <Label htmlFor="code">{t('Counter Code')}</Label>
                    <Input
                        id="code"
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value)}
                        placeholder={t('Enter unique Counter code')}
                        required
                    />
                    <InputError message={errors.code} />
                </div>

                {bankAccountField.map((field) => (
                    <div key={field.id}>
                        {field.component}
                    </div>
                ))}

                <div>
                    <Label htmlFor="description">{t('Description')}</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('Enter description (optional)')}
                        rows={3}
                    />
                    <InputError message={errors.description} />
                </div>

                <div>
                    <Label htmlFor="status">{t('Status')}</Label>
                    <div className="flex items-center space-x-2 mt-2">
                        <Switch
                            id="status"
                            checked={data.status === true}
                            onCheckedChange={(checked) => setData('status', checked)}
                        />
                        <Label htmlFor="status" className="text-sm font-normal">
                            {data.status === true ? t('Active') : t('Inactive')}
                        </Label>
                    </div>
                    <InputError message={errors.status} />
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
