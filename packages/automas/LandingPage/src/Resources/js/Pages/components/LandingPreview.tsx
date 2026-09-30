import React from 'react';
import { useTranslation } from 'react-i18next';

interface LandingPreviewProps {
  settings?: any;
}

export function LandingPreview({ settings }: LandingPreviewProps) {
  const { t } = useTranslation();

  // URL for the actual landing page
  const landingPageUrl = typeof route === 'function' ? route('landing.page') : '/';

  return (
    <div className="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white shadow-lg flex flex-col w-full">
      {/* Header Bar */}
      <div className="px-3.5 py-2 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
        <div className="text-xs font-bold text-white flex items-center gap-2">
          <span className="relative flex h-2 w-2">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          {t('Live Preview')}
        </div>
        <span className="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
          {t('Mobile Mode')}
        </span>
      </div>

      {/* iframe loading actual landing page in compact mobile mode */}
      <div className="w-full bg-slate-100 flex justify-center items-center p-2 min-h-[540px]">
        <div className="w-full max-w-[360px] h-[550px] bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
          <iframe
            src={landingPageUrl}
            title={t('Landing Page Mobile View')}
            className="w-full h-full border-0"
            style={{ width: '100%', height: '100%' }}
          />
        </div>
      </div>

      {/* Footer Info Bar */}
      <div className="bg-slate-50 px-3 py-2 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
        <span className="truncate text-[10px] font-mono text-slate-500">{landingPageUrl}</span>
        <div className="flex items-center gap-1 shrink-0 ml-2">
          <div className="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
          <span className="text-[11px] font-medium text-slate-600">{t('Live')}</span>
        </div>
      </div>
    </div>
  );
}