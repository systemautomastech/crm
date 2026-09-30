import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from 'sonner';
import { Zap, Save } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';

interface SuperAdminAIAdvisorSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function SuperAdminAIAdvisorSettings({ userSettings = {}, auth }: SuperAdminAIAdvisorSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const canEdit = auth?.user?.permissions?.includes('manage-ai-business-advisor-settings');

  const [settings, setSettings] = useState({
    ai_advisor_enabled: userSettings?.ai_advisor_enabled === 'on',
    ai_advisor_retention_days: userSettings?.ai_advisor_retention_days || '90'
  });

  useEffect(() => {
    setSettings({
      ai_advisor_enabled: userSettings?.ai_advisor_enabled === 'on',
      ai_advisor_retention_days: userSettings?.ai_advisor_retention_days || '90'
    });
  }, [userSettings]);

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({
      ...prev,
      ai_advisor_enabled: checked
    }));
  };

  const handleRetentionDaysChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    if (value === '' || /^\d+$/.test(value)) {
      setSettings(prev => ({
        ...prev,
        ai_advisor_retention_days: value
      }));
    }
  };

  const saveSettings = () => {
    if (!settings.ai_advisor_retention_days || parseInt(settings.ai_advisor_retention_days) < 1) {
      toast.error(t('Data retention days must be at least 1'));
      return;
    }

    setIsLoading(true);

    router.post(route('ai-advisor.setting.store'), {
      ai_advisor_enabled: settings.ai_advisor_enabled ? 'on' : 'off',
      ai_advisor_retention_days: settings.ai_advisor_retention_days
    }, {
      preserveScroll: true,
      onSuccess: (page) => {
        setIsLoading(false);
        const successMessage = (page.props.flash as any)?.success;
        const errorMessage = (page.props.flash as any)?.error;

        if (successMessage) {
          toast.success(successMessage);
        } else if (errorMessage) {
          toast.error(errorMessage);
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save settings');
        toast.error(errorMessage);
      }
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle className="flex items-center gap-2 text-lg">
            <Zap className="h-5 w-5" />
            {t('AI Advisor Settings')}
          </CardTitle>
          <p className="text-sm text-muted-foreground mt-1">
            {t('Configure global settings for AI Business Advisor')}
          </p>
        </div>
        {canEdit && (
          <Button onClick={saveSettings} disabled={isLoading} size="sm">
            <Save className="h-4 w-4 mr-2" />
            {isLoading ? t('Saving...') : t('Save Changes')}
          </Button>
        )}
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          <div className="flex items-center justify-between p-3 border rounded-lg">
            <div>
              <Label htmlFor="ai_advisor_enabled" className="text-base font-medium">
                {t('Enable AI Advisor')}
              </Label>
              <p className="text-sm text-muted-foreground mt-1">
                {t('Enable or disable AI Business Advisor globally for all users')}
              </p>
            </div>
            <Switch
              id="ai_advisor_enabled"
              checked={settings.ai_advisor_enabled}
              onCheckedChange={handleSwitchChange}
              disabled={!canEdit}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="ai_advisor_retention_days" className="text-base font-medium">
              {t('Data Retention Days')}
            </Label>
            <p className="text-sm text-muted-foreground">
              {t('Number of days to retain analysis records. Records older than this will be automatically deleted.')}
            </p>
            <Input
              id="ai_advisor_retention_days"
              type="number"
              min="1"
              max="3650"
              value={settings.ai_advisor_retention_days}
              onChange={handleRetentionDaysChange}
              disabled={!canEdit}
              className="max-w-xs"
            />
            <p className="text-xs text-muted-foreground mt-1">
              {t('Minimum: 1 day, Maximum: 3650 days (10 years)')}
            </p>
          </div>

          <div className="space-y-4 pt-4 border-t">
            <div>
              <Label className="text-base font-medium">{t('AI Advisor Cronjob Instruction')}</Label>
              <div className="space-y-2 text-sm text-muted-foreground mt-2">
                <p>{t('1. To enable automatic analysis generation, set up a cron job that runs daily.')}</p>
                <div className="bg-muted p-3 rounded text-xs font-mono">
                  {`0 0 * * * cd /path/to/domain && php artisan ai-advisor:generate >/dev/null 2>&1`}
                </div>
                <p>{t('2. Example url as')}:</p>
                <div className="bg-muted p-3 rounded text-xs font-mono">
                  {`/usr/local/bin/ea-php82 /home/project/public_html/domain.com/artisan ai-advisor:generate`}
                </div>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
