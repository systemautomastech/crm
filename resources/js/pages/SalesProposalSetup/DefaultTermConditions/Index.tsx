import React, { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Save } from 'lucide-react';
import { toast } from 'sonner';
import RichTextEditor from '@/components/ui/rich-text-editor';
import { useTranslation } from 'react-i18next';

interface DefaultTermConditionsProps {
    settings?: Record<string, any> | null;
}

export default function DefaultTermConditions({ settings }: DefaultTermConditionsProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing } = useForm({
        settings: {
            default_terms: settings?.default_terms ??
                '<h2>Terms & Conditions</h2><p>1. Proposal is valid for 30 days from issuance.<br/>2. Payment terms: 50% deposit upon acceptance, 50% on project completion.</p>',
        }
    });

    useEffect(() => {
        if (settings) {
            setData('settings', {
                default_terms: settings.default_terms ?? '',
            });
        }
    }, [settings]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        post(route('proposal-setup.update'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(t('Terms & Conditions saved successfully.'));
            },
            onError: () => {
                toast.error(t('Failed to save terms & conditions.'));
            },
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div>
                <h3 className="text-lg font-medium">{t('Default Terms & Conditions')}</h3>
            </div>

            <div className="space-y-2">
                <RichTextEditor
                    content={data.settings.default_terms}
                    onChange={(val) => setData('settings', { ...data.settings, default_terms: val })}
                    placeholder={t('Enter default proposal terms & conditions...')}
                />
            </div>

            <div className="flex justify-end pt-4 border-t">
                <Button type="submit" size="sm" disabled={processing} className="gap-2">
                    <Save className="h-4 w-4" />
                    {t('Save Terms')}
                </Button>
            </div>
        </form>
    );
}