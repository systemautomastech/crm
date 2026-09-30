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

interface FacebookChatSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

interface FacebookChatSettings {
  facebookchat_enabled: boolean;
  facebook_client_id: string;
  facebook_access_token: string;
}

export default function FacebookChatSettings({ userSettings = {}, auth }: FacebookChatSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showToken, setShowToken] = useState(false);
  const canEdit = auth?.user?.permissions?.includes('edit-facebook-chat-settings');
  
  const [settings, setSettings] = useState<FacebookChatSettings>({
    facebookchat_enabled: userSettings?.facebookchat_enabled === 'on',
    facebook_client_id: userSettings?.facebook_client_id || '',
    facebook_access_token: userSettings?.facebook_access_token || '',
  });

  const [webhookUrl, setWebhookUrl] = useState('');

  useEffect(() => {
    setSettings({
      facebookchat_enabled: userSettings?.facebookchat_enabled === 'on',
      facebook_client_id: userSettings?.facebook_client_id || '',
      facebook_access_token: userSettings?.facebook_access_token || '',
    });
  }, [userSettings]);

  useEffect(() => {
    setWebhookUrl(route('meta.callback'));
  }, [auth]);

  const handleSettingsChange = (field: string, value: string | boolean) => {
    setSettings(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('facebook-chat.settings.update'), {
      settings: {
        ...settings,
        facebookchat_enabled: settings.facebookchat_enabled ? 'on' : 'off'
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
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save Facebook Chat settings');
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
            {t('Facebook Chat Settings')}
          </CardTitle>
          <p className="text-sm text-muted-foreground mt-1">
            {t('Configure Facebook Chat integration and webhook settings')}
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
          {/* Enable/Disable Facebook Chat */}
          <div className="flex items-center justify-between p-4 border rounded-lg">
            <div>
              <Label htmlFor="facebookchat_enabled" className="text-base font-medium">
                {t('Enable Facebook Chat')}
              </Label>
              <p className="text-sm text-muted-foreground mt-1">
                {t('Enable or disable Facebook Chat integration')}
              </p>
            </div>
            <Switch
              id="facebookchat_enabled"
              checked={settings.facebookchat_enabled}
              onCheckedChange={(checked) => handleSettingsChange('facebookchat_enabled', checked)}
              disabled={!canEdit}
            />
          </div>

          {settings.facebookchat_enabled && (
            <>
              <div className="mb-6">
                <span className="font-semibold text-red-600 text-sm">{t('Note: ')}</span>
                <span className="text-sm">{t('Use this webhook URL in your Facebook App configuration')}</span>
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
                  {/* Facebook Client ID */}
                  <div className="space-y-3">
                    <Label htmlFor="facebook_client_id">{t('Facebook Client ID')}</Label>
                    <Input
                      id="facebook_client_id"
                      value={settings.facebook_client_id}
                      onChange={(e) => handleSettingsChange('facebook_client_id', e.target.value)}
                      placeholder={t('Enter Facebook Client ID')}
                      disabled={!canEdit}
                    />                   
                  </div>

                  {/* Facebook Access Token */}
                  <div className="space-y-3">
                    <Label htmlFor="facebook_access_token">{t('Facebook Access Token')}</Label>
                    <div className="relative">
                      <Input
                        id="facebook_access_token"
                        type={showToken ? 'text' : 'password'}
                        value={settings.facebook_access_token}
                        onChange={(e) => handleSettingsChange('facebook_access_token', e.target.value)}
                        placeholder={t('Enter Facebook Access Token')}
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
                    <p>{t('1. Go to')} <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" className="text-blue-600 underline hover:text-blue-800">{t('Facebook Developers')}</a> {t('and create a Facebook app')}</p>
                    <p>{t('2. Configure Messenger Platform')}</p>
                    <p>{t('3. Get Client ID from app settings')}</p>
                    <p>{t('4. Generate Access Token for your page')}</p>
                    <p>{t('5. Add the webhook URL shown above to your app configuration')}</p>
                    <p>{t('6. Save settings to enable Facebook Chat integration')}</p>
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