import React from 'react';
import { useTranslation } from 'react-i18next';
import {
  Bell,
  LayoutDashboard,
  Settings,
  UserRound,
  Users,
} from 'lucide-react';

import { getImagePath } from '@/utils/helpers';

interface ThemePreviewProps {
  logoDark?: string;
  logoLight?: string;
  themeColor?: string;
  customColor?: string;
  sidebarVariant?: string;
  sidebarStyle?: string;
  sidebarTextColor?: string;
  layoutDirection?: string;
  themeMode?: string;
}

const themeColors = {
  blue: '#3b82f6',
  green: '#10b981',
  purple: '#8b5cf6',
  orange: '#f97316',
  red: '#ef4444',
};

const hexToRgba = (hex: string, opacity: number): string => {
  const cleanHex = hex.replace('#', '');

  const normalizedHex =
    cleanHex.length === 3
      ? cleanHex
        .split('')
        .map((character) => character + character)
        .join('')
      : cleanHex;

  if (!/^[0-9a-fA-F]{6}$/.test(normalizedHex)) {
    return `rgba(16, 185, 129, ${opacity})`;
  }

  const number = Number.parseInt(normalizedHex, 16);
  const red = (number >> 16) & 255;
  const green = (number >> 8) & 255;
  const blue = number & 255;

  return `rgba(${red}, ${green}, ${blue}, ${opacity})`;
};

export function ThemePreview({
  logoDark,
  logoLight,
  themeColor = 'green',
  customColor = '#10b981',
  sidebarVariant = 'inset',
  sidebarStyle = 'plain',
  sidebarTextColor = '#ffffff',
  layoutDirection = 'ltr',
  themeMode = 'light',
}: ThemePreviewProps) {
  const { t } = useTranslation();

  const primaryColor =
    themeColor === 'custom'
      ? customColor
      : themeColors[themeColor as keyof typeof themeColors] ||
      themeColors.green;

  const prefersDark =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;

  const isDark =
    themeMode === 'dark' ||
    (themeMode === 'system' && prefersDark);

  const isRTL = layoutDirection === 'rtl';
  const hasColoredSidebar =
    sidebarStyle === 'colored' ||
    sidebarStyle === 'gradient';

  const currentLogo = isDark ? logoLight : logoDark;

  const sidebarBackground = (): React.CSSProperties => {
    if (sidebarStyle === 'colored') {
      return {
        backgroundColor: primaryColor,
      };
    }

    if (sidebarStyle === 'gradient') {
      return {
        background: `linear-gradient(
          145deg,
          ${primaryColor} 0%,
          ${hexToRgba(primaryColor, 0.72)} 100%
        )`,
      };
    }

    return {};
  };

  const sidebarColor = hasColoredSidebar
    ? sidebarTextColor
    : isDark
      ? '#f9fafb'
      : '#374151';

  const sidebarBorderColor = hasColoredSidebar
    ? hexToRgba(sidebarTextColor, 0.15)
    : undefined;

  const menuItems = [
    {
      label: t('Dashboard'),
      icon: LayoutDashboard,
      active: true,
    },
    {
      label: t('Customers'),
      icon: Users,
      active: false,
    },
    {
      label: t('Users'),
      icon: UserRound,
      active: false,
    },
    {
      label: t('Settings'),
      icon: Settings,
      active: false,
    },
  ];

  return (
    <div
      dir={isRTL ? 'rtl' : 'ltr'}
      className={`overflow-hidden rounded-lg border text-xs shadow-sm transition-all duration-300 ${isDark
          ? 'border-gray-700 bg-gray-900 text-white'
          : 'border-gray-200 bg-white text-gray-900'
        }`}
      style={
        {
          '--preview-primary': primaryColor,
        } as React.CSSProperties
      }
    >
      {/* Top bar */}
      <div
        className={`flex h-11 items-center justify-between border-b px-3 ${isDark
            ? 'border-gray-700 bg-gray-800'
            : 'border-gray-200 bg-white'
          }`}
      >
        <div className="flex items-center gap-2">
          <div
            className="h-5 w-5 rounded-md"
            style={{
              backgroundColor: hexToRgba(primaryColor, 0.18),
            }}
          />

          <span className="font-semibold">
            {t('Dashboard')}
          </span>
        </div>

        <div className="flex items-center gap-2">
          <div
            className={`flex h-6 w-6 items-center justify-center rounded-md ${isDark ? 'bg-gray-700' : 'bg-gray-100'
              }`}
          >
            <Bell className="h-3 w-3" />
          </div>

          <div
            className="h-6 w-6 rounded-full"
            style={{
              backgroundColor: hexToRgba(primaryColor, 0.3),
              border: `1px solid ${hexToRgba(
                primaryColor,
                0.5
              )}`,
            }}
          />
        </div>
      </div>

      {/* Main area */}
      <div
        className={`flex h-56 ${isRTL ? 'flex-row-reverse' : ''
          }`}
      >
        {/* Sidebar */}
        <aside
          className={`flex shrink-0 flex-col gap-3 border-r p-2 transition-all duration-300 ${sidebarVariant === 'minimal' ? 'w-14' : 'w-28'
            } ${sidebarVariant === 'floating'
              ? 'm-2 rounded-lg border shadow-sm'
              : ''
            } ${!hasColoredSidebar && isDark
              ? 'border-gray-700 bg-gray-800'
              : ''
            } ${!hasColoredSidebar && !isDark
              ? 'border-gray-200 bg-gray-50'
              : ''
            }`}
          style={{
            ...sidebarBackground(),
            color: sidebarColor,
            borderColor: sidebarBorderColor,
          }}
        >
          {/* Logo */}
          <div className="flex h-7 items-center justify-center">
            {currentLogo ? (
              <img
                src={getImagePath(currentLogo)}
                alt={t('Logo')}
                className="max-h-6 max-w-full object-contain"
              />
            ) : (
              <div className="flex items-center gap-1.5">
                <div
                  className="h-5 w-5 rounded-md"
                  style={{
                    backgroundColor: hasColoredSidebar
                      ? sidebarTextColor
                      : primaryColor,
                    opacity: 0.9,
                  }}
                />

                {sidebarVariant !== 'minimal' && (
                  <span
                    className="text-[9px] font-bold"
                    style={{ color: sidebarColor }}
                  >
                    ERP
                  </span>
                )}
              </div>
            )}
          </div>

          {/* Menu */}
          <nav className="space-y-1">
            {menuItems.map((item) => {
              const Icon = item.icon;

              return (
                <div
                  key={item.label}
                  className={`flex h-7 items-center rounded-md px-2 ${sidebarVariant === 'minimal'
                      ? 'justify-center'
                      : 'gap-2'
                    }`}
                  style={{
                    color: sidebarColor,
                    backgroundColor: item.active
                      ? hasColoredSidebar
                        ? hexToRgba(sidebarTextColor, 0.2)
                        : hexToRgba(primaryColor, 0.14)
                      : 'transparent',
                  }}
                >
                  <Icon
                    className="h-3.5 w-3.5 shrink-0"
                    style={{
                      color: item.active
                        ? hasColoredSidebar
                          ? sidebarTextColor
                          : primaryColor
                        : sidebarColor,
                      opacity: item.active ? 1 : 0.72,
                    }}
                  />

                  {sidebarVariant !== 'minimal' && (
                    <span
                      className="truncate text-[9px] font-medium"
                      style={{
                        color: sidebarColor,
                        opacity: item.active ? 1 : 0.78,
                      }}
                    >
                      {item.label}
                    </span>
                  )}
                </div>
              );
            })}
          </nav>

          <div className="mt-auto">
            <div
              className="h-px w-full"
              style={{
                backgroundColor: hasColoredSidebar
                  ? hexToRgba(sidebarTextColor, 0.2)
                  : isDark
                    ? '#374151'
                    : '#e5e7eb',
              }}
            />

            <div
              className={`mt-2 flex items-center ${sidebarVariant === 'minimal'
                  ? 'justify-center'
                  : 'gap-2'
                }`}
            >
              <div
                className="h-5 w-5 shrink-0 rounded-full"
                style={{
                  backgroundColor: hasColoredSidebar
                    ? hexToRgba(sidebarTextColor, 0.25)
                    : hexToRgba(primaryColor, 0.2),
                }}
              />

              {sidebarVariant !== 'minimal' && (
                <div className="min-w-0 flex-1 space-y-1">
                  <div
                    className="h-1.5 w-full rounded"
                    style={{
                      backgroundColor: sidebarColor,
                      opacity: 0.65,
                    }}
                  />

                  <div
                    className="h-1 w-2/3 rounded"
                    style={{
                      backgroundColor: sidebarColor,
                      opacity: 0.3,
                    }}
                  />
                </div>
              )}
            </div>
          </div>
        </aside>

        {/* Dashboard content */}
        <main className="min-w-0 flex-1 space-y-3 p-3">
          <div>
            <div
              className={`h-2 w-24 rounded ${isDark ? 'bg-gray-600' : 'bg-gray-300'
                }`}
            />

            <div
              className={`mt-1.5 h-1.5 w-16 rounded ${isDark ? 'bg-gray-700' : 'bg-gray-200'
                }`}
            />
          </div>

          <div className="grid grid-cols-3 gap-2">
            {[0.18, 0.12, 0.08].map((opacity, index) => (
              <div
                key={index}
                className={`rounded-md border p-2 ${isDark
                    ? 'border-gray-700 bg-gray-800'
                    : 'border-gray-200 bg-white'
                  }`}
              >
                <div
                  className="mb-2 h-4 w-4 rounded"
                  style={{
                    backgroundColor: hexToRgba(
                      primaryColor,
                      opacity
                    ),
                  }}
                />

                <div
                  className={`h-1.5 w-full rounded ${isDark ? 'bg-gray-600' : 'bg-gray-300'
                    }`}
                />
              </div>
            ))}
          </div>

          <div
            className={`rounded-md border p-2 ${isDark
                ? 'border-gray-700 bg-gray-800'
                : 'border-gray-200 bg-white'
              }`}
          >
            <div className="mb-3 flex items-center justify-between">
              <div
                className={`h-1.5 w-16 rounded ${isDark ? 'bg-gray-600' : 'bg-gray-300'
                  }`}
              />

              <div
                className="h-1.5 w-8 rounded"
                style={{
                  backgroundColor: hexToRgba(
                    primaryColor,
                    0.45
                  ),
                }}
              />
            </div>

            <div className="flex h-14 items-end gap-1">
              {[35, 55, 42, 75, 60, 90, 68, 82].map(
                (height, index) => (
                  <div
                    key={index}
                    className="flex-1 rounded-t-sm"
                    style={{
                      height: `${height}%`,
                      backgroundColor:
                        index === 5
                          ? primaryColor
                          : hexToRgba(primaryColor, 0.28),
                    }}
                  />
                )
              )}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}