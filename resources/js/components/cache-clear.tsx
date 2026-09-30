import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { RefreshCcw } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';

interface CacheSettingsProps {
    auth?: any;
}

export function CacheClear({ auth }: CacheSettingsProps) {
    const { t } = useTranslation();
    const [isClearing, setIsClearing] = useState(false);

    const canEdit = auth?.user?.permissions?.includes('clear-cache');

    const handleClearCache = (): void => {
        if (isClearing) return;

        setIsClearing(true);

        router.post(
            route('settings.cache.clear'),
            {},
            {
                preserveScroll: true,

                onSuccess: (page) => {
                    const flash = page.props.flash as
                        | {
                              success?: string;
                              error?: string;
                          }
                        | undefined;

                    if (flash?.success) {
                        toast.success(flash.success);
                        return;
                    }

                    if (flash?.error) {
                        toast.error(flash.error);
                        return;
                    }

                    toast.success(t('Cache cleared successfully'));
                },

                onError: (errors) => {
                    const errorMessage =
                        typeof errors.error === 'string'
                            ? errors.error
                            : Object.values(errors)
                                  .flat()
                                  .filter(
                                      (message): message is string =>
                                          typeof message === 'string'
                                  )
                                  .join(', ');

                    toast.error(
                        errorMessage || t('Failed to clear cache')
                    );
                },

                onFinish: () => {
                    setIsClearing(false);
                },
            }
        );
    };

    return (
        <>
            {canEdit && (
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={handleClearCache}
                    disabled={isClearing}
                    title={t('Clear cache')}
                    aria-label={t('Clear cache')}
                >
                    <RefreshCcw
                        className={isClearing ? 'animate-spin' : ''}
                    />
                </Button>
            )}
        </>
    );
}