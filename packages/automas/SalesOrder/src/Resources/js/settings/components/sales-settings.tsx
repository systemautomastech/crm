import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'sonner';
import { TrendingUp, Save } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';

interface SalesSettings {
  quote_prefix: string;
  order_prefix: string;
  [key: string]: any;
}

interface SalesSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function SalesSettings({ userSettings, auth }: SalesSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const canEdit = auth?.user?.permissions?.includes('edit-sales-settings');
  const [settings, setSettings] = useState<SalesSettings>({
    quote_prefix: userSettings?.quote_prefix || userSettings?.['quote_prefix'] || 'QUO',
    order_prefix: userSettings?.order_prefix || userSettings?.['order_prefix'] || 'ORD',
    case_prefix: userSettings?.case_prefix || userSettings?.['case_prefix'] || 'CASE',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        quote_prefix: userSettings?.quote_prefix || userSettings?.['quote_prefix'] || 'QUO',
        order_prefix: userSettings?.order_prefix || userSettings?.['order_prefix'] || 'ORD',
        case_prefix: userSettings?.case_prefix || userSettings?.['case_prefix'] || 'CASE',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('salesorder.settings.update'), {
      settings: settings
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
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save sales settings');
        toast.error(errorMessage);
      }
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle className="flex items-center gap-2 text-lg">
            <TrendingUp className="h-5 w-5" />
            {t('Sales Settings')}
          </CardTitle>
          <p className="text-sm text-muted-foreground mt-1">
            {t('Configure sales module settings and prefixes')}
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
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="quote_prefix">{t('Quote Prefix')}</Label>
                <Input
                  id="quote_prefix"
                  name="quote_prefix"
                  type="text"
                  value={settings.quote_prefix}
                  onChange={handleInputChange}
                  placeholder={t('Enter quote prefix')}
                  disabled={!canEdit}
                />
                <p className="text-xs text-muted-foreground">
                  {t('Prefix for quote numbers')}
                </p>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="order_prefix">{t('Order Prefix')}</Label>
                <Input
                  id="order_prefix"
                  name="order_prefix"
                  type="text"
                  value={settings.order_prefix}
                  onChange={handleInputChange}
                  placeholder={t('Enter order prefix')}
                  disabled={!canEdit}
                />
                <p className="text-xs text-muted-foreground">
                  {t('Prefix for order numbers')}
                </p>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

              
              <div className="space-y-2">
                <Label htmlFor="case_prefix">{t('Case Prefix')}</Label>
                <Input
                  id="case_prefix"
                  name="case_prefix"
                  type="text"
                  value={settings.case_prefix}
                  onChange={handleInputChange}
                  placeholder={t('Enter case prefix')}
                  disabled={!canEdit}
                />
                <p className="text-xs text-muted-foreground">
                  {t('Prefix for case numbers')}
                </p>
              </div>
            </div>
          </div>

          <div className="border rounded-lg p-4 bg-muted/30">
            <h4 className="font-medium mb-3 flex items-center gap-2">
              <TrendingUp className="h-4 w-4" />
              {t('Preview')}
            </h4>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">{t('Quote')}:</span>
                <span className="font-mono font-medium">{settings.quote_prefix}0000001</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">{t('Order')}:</span>
                <span className="font-mono font-medium">{settings.order_prefix}0000001</span>
              </div>

              <div className="flex justify-between">
                <span className="text-muted-foreground">{t('Case')}:</span>
                <span className="font-mono font-medium">{settings.case_prefix}0000001</span>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}