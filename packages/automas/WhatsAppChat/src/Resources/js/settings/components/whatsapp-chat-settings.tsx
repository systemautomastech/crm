import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'sonner';
import { MessageCircle, Save, Eye, EyeOff } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';

interface WhatsAppChatSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

interface WhatsAppChatSettings {
  whatsappchat_enabled: boolean;
  whatsappchat_phone_number_id: string;
  whatsappchat_access_token: string;
}

export default function WhatsAppChatSettings({ userSettings = {}, auth }: WhatsAppChatSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showToken, setShowToken] = useState(false);
  const canEdit = auth?.user?.permissions?.includes('edit-whatsapp-chat-settings');
  
  const [settings, setSettings] = useState<WhatsAppChatSettings>({
    whatsappchat_enabled: userSettings?.whatsappchat_enabled === 'on',
    whatsappchat_phone_number_id: userSettings?.whatsappchat_phone_number_id || '',
    whatsappchat_access_token: userSettings?.whatsappchat_access_token || '',
  });

  const [webhookUrl, setWebhookUrl] = useState('');

  useEffect(() => {
    setSettings({
      whatsappchat_enabled: userSettings?.whatsappchat_enabled === 'on',
      whatsappchat_phone_number_id: userSettings?.whatsappchat_phone_number_id || '',
      whatsappchat_access_token: userSettings?.whatsappchat_access_token || '',
    });
  }, [userSettings]);

  useEffect(() => {
    const dynamicSlug = auth?.user?.slug || 'default';
    setWebhookUrl(route('whatsappchat.webhook', { slug: dynamicSlug }));
  }, [auth]);

  const handleSettingsChange = (field: string, value: string | boolean) => {
    setSettings(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('whatsapp-chat.settings.update'), {
      settings: {
        ...settings,
        whatsappchat_enabled: settings.whatsappchat_enabled ? 'on' : 'off'
      }
    }, {
      preserveScroll: true,
      onSuccess: (page) => {
        setIsLoading(false);
        const successMessage = (page.props.flash as any)?.success;
        const errorMessage = (page.props.flash as any)?.error;

        if (successMessage) {
          toast.success(successMessage);
          router.reload({ only: ['globalSettings'] });
        } else if (errorMessage) {
          toast.error(errorMessage);
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save WhatsApp Chat settings');
        toast.error(errorMessage);
      }
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="order-1 rtl:order-2">
          <CardTitle className="flex items-center gap-2 text-lg">
            <MessageCircle className="h-5 w-5" />
            {t('WhatsApp Chat Settings')}
          </CardTitle>
          <p className="text-sm text-muted-foreground mt-1">
            {t('Configure WhatsApp Chat integration and webhook settings')}
          </p>
        </div>
        {canEdit && (
          <Button className="order-2 rtl:order-1" onClick={saveSettings} disabled={isLoading} size="sm">
            <Save className="h-4 w-4 mr-2" />
            {isLoading ? t('Saving...') : t('Save Changes')}
          </Button>
        )}
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          {/* Enable/Disable WhatsApp Chat */}
          <div className="flex items-center justify-between p-4 border rounded-lg">
            <div>
              <Label htmlFor="whatsappchat_enabled" className="text-base font-medium">
                {t('Enable WhatsApp Chat')}
              </Label>
              <p className="text-sm text-muted-foreground mt-1">
                {t('Enable or disable WhatsApp Chat integration')}
              </p>
            </div>
            <Switch
              id="whatsappchat_enabled"
              checked={settings.whatsappchat_enabled}
              onCheckedChange={(checked) => handleSettingsChange('whatsappchat_enabled', checked)}
              disabled={!canEdit}
            />
          </div>

          {settings.whatsappchat_enabled && (
            <>
              <div className="mb-6">
                <span className="font-semibold text-red-600 text-sm">{t('Note: ')}</span>
                <span className="text-sm">{t('Use this webhook URL in your WhatsApp Business API configuration')}</span>
                <div className="flex items-center gap-2 mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                  <code className="bg-white px-3 py-2 rounded text-sm flex-1 border">
                    {webhookUrl}
                  </code>
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      navigator.clipboard.writeText(webhookUrl);
                      toast.success(t('URL copied to clipboard'));
                    }}
                    className="h-8 px-3 text-xs"
                  >
                    {t('Copy')}
                  </Button>
                </div>
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="space-y-6">
                  {/* Phone Number ID */}
                  <div className="space-y-3">
                    <Label htmlFor="whatsappchat_phone_number_id">{t('Phone Number ID')}</Label>
                    <Input
                      id="whatsappchat_phone_number_id"
                      value={settings.whatsappchat_phone_number_id}
                      onChange={(e) => handleSettingsChange('whatsappchat_phone_number_id', e.target.value)}
                      placeholder={t('Enter Phone Number ID')}
                      disabled={!canEdit}
                    />                   
                  </div>

                  {/* Access Token */}
                  <div className="space-y-3">
                    <Label htmlFor="whatsappchat_access_token">{t('Access Token')}</Label>
                    <div className="relative">
                      <Input
                        id="whatsappchat_access_token"
                        type={showToken ? 'text' : 'password'}
                        value={settings.whatsappchat_access_token}
                        onChange={(e) => handleSettingsChange('whatsappchat_access_token', e.target.value)}
                        placeholder={t('Enter Access Token')}
                        disabled={!canEdit}
                        className="pr-10"
                      />
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                        onClick={() => setShowToken(!showToken)}
                      >
                        {showToken ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </Button>
                    </div>                   
                  </div>
                </div>

                {/* Right Side - Guide */}
                <div className="border rounded-lg p-4 bg-blue-50/50 border-blue-200">
                  <h4 className="font-medium mb-2 text-blue-900">
                    {t('Setup Instructions')}
                  </h4>
                  <div className="space-y-2 text-sm text-blue-800">
                    <p>{t('1. Go to')} <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" className="text-blue-600 underline hover:text-blue-800">{t('Facebook Developers')}</a> {t('and create a WhatsApp Business app')}</p>
                    <p>{t('2. Configure WhatsApp Business API')}</p>
                    <p>{t('3. Get Phone Number ID from settings')}</p>
                    <p>{t('4. Generate Access Token for your app')}</p>
                    <p>{t('5. Add the webhook URL shown above to your app configuration')}</p>
                    <p>{t('6. Save settings to enable WhatsApp Chat integration')}</p>
                  </div>
                </div>
              </div>
            </>
          )}
        </div>
      </CardContent>
    </Card>
  );
}