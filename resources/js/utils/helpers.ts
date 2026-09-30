import { usePage } from '@inertiajs/react';

// Add window type declaration
declare global {
  interface Window {
    location: Location;
  }
}

/**
 * Get company setting value
 */
const getCompanySetting = (key: string, pageProps?: any) => {
  try {
    // If pageProps is provided, use it; otherwise get from usePage
    let companySettings: Record<string, any> = {};
    let adminSettings: Record<string, any> = {};
    if (pageProps?.companyAllSetting || pageProps?.adminAllSetting) {
      companySettings = pageProps.companyAllSetting || {};
      adminSettings = pageProps.adminAllSetting || {};
    } else {
      const { props } = usePage();
      companySettings = (props as any).companyAllSetting || {};
      adminSettings = (props as any).adminAllSetting || {};
    }

    return companySettings[key] !== undefined && companySettings[key] !== null
      ? companySettings[key]
      : adminSettings[key] ?? null;
  } catch {
    return null;
  }
};

/**
 * Get admin setting value
 */
const getAdminSetting = (key: string, pageProps?: any) => {
  try {
    // If pageProps is provided, use it; otherwise get from usePage
    let adminSettings;
    if (pageProps?.adminAllSetting) {
      adminSettings = pageProps.adminAllSetting;
    } else {
      const { props } = usePage();
      adminSettings = (props as any).adminAllSetting || {};
    }

    return adminSettings[key];
  } catch {
    return null;
  }
};

/**
 * Safely parse date inputs into Date objects
 */
const parseDateInput = (date: string | Date): Date | null => {
  if (!date) return null;
  if (date instanceof Date) {
    return Number.isNaN(date.getTime()) ? null : date;
  }
  if (typeof date === 'string') {
    const trimmed = date.trim();
    if (!trimmed) return null;
    // YYYY-MM-DD date-only string: avoid UTC timezone day-shift
    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
      const [year, month, day] = trimmed.split('-').map(Number);
      return new Date(year, month - 1, day);
    }
    // Handle space-separated datetime e.g. "2026-09-02 15:30:00"
    const cleaned = trimmed.includes(' ') && !trimmed.includes('T') ? trimmed.replace(' ', 'T') : trimmed;
    const d = new Date(cleaned);
    return Number.isNaN(d.getTime()) ? null : d;
  }
  const d = new Date(date);
  return Number.isNaN(d.getTime()) ? null : d;
};

/**
 * Format date to readable format according to company date format settings
 */
const formatDate = (
  date: string | Date,
  pageProps?: any
): string => {
  if (!date) return '';

  const d = parseDateInput(date);
  if (!d) return '';

  const format =
    getCompanySetting('dateFormat', pageProps) || 'Y-m-d';

  const year = d.getFullYear();
  const monthIndex = d.getMonth();
  const month = String(monthIndex + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');

  const shortMonths = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ];

  const fullMonths = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  const replacements: Record<string, string> = {
    Y: String(year),
    y: String(year).slice(-2),
    m: month,
    n: String(monthIndex + 1),
    d: day,
    j: String(d.getDate()),
    M: shortMonths[monthIndex],
    F: fullMonths[monthIndex],
    f: shortMonths[monthIndex],
  };

  return format.replace(
    /Y|y|m|n|d|j|M|F|f/g,
    (token: string) => replacements[token] || token
  );
};

/**
 * Format time to readable format according to company time format settings
 */
const formatTime = (time: string, pageProps?: any): string => {
  if (!time) return '';
  const timeFormat = getCompanySetting('timeFormat', pageProps) || 'H:i';
  const parts = time.split(':');
  const h = parseInt(parts[0], 10);
  const m = String(parseInt(parts[1], 10) || 0).padStart(2, '0');

  if (Number.isNaN(h)) return '';

  if (timeFormat === 'g:i A' || timeFormat === 'h:i A' || timeFormat === 'g:i a' || timeFormat === 'h:i a') {
    const isLower = timeFormat.includes('a');
    const period = h >= 12 ? (isLower ? 'pm' : 'PM') : (isLower ? 'am' : 'AM');
    const hourVal = h === 0 ? 12 : h > 12 ? h - 12 : h;
    const displayHour = timeFormat.startsWith('h') ? String(hourVal).padStart(2, '0') : String(hourVal);
    return `${displayHour}:${m} ${period}`;
  }

  return timeFormat
    .replace('H', String(h).padStart(2, '0'))
    .replace('G', String(h))
    .replace('i', m);
};

export interface ParsedDuration {
  hours: number;
  minutes: number;
  seconds: number;
  totalSeconds: number;
}

/**
 * Parse any duration representation (seconds, HH:mm:ss, HH:mm, or malformed) into structured duration
 */
const parseCallDuration = (raw: string | number | null | undefined): ParsedDuration => {
  if (raw === null || raw === undefined || raw === '') {
    return { hours: 0, minutes: 0, seconds: 0, totalSeconds: 0 };
  }

  const str = String(raw).trim();
  if (!str || str === '0') {
    return { hours: 0, minutes: 0, seconds: 0, totalSeconds: 0 };
  }

  // Handle malformed strings from previous bugs like "47:undefined"
  if (str.includes(':undefined')) {
    const numPart = str.replace(':undefined', '').trim();
    const total = parseInt(numPart, 10) || 0;
    return {
      hours: Math.floor(total / 3600),
      minutes: Math.floor((total % 3600) / 60),
      seconds: total % 60,
      totalSeconds: total,
    };
  }

  // Pure integer (e.g. PBX seconds like "47")
  if (/^\d+$/.test(str)) {
    const total = parseInt(str, 10) || 0;
    return {
      hours: Math.floor(total / 3600),
      minutes: Math.floor((total % 3600) / 60),
      seconds: total % 60,
      totalSeconds: total,
    };
  }

  // Colon-separated: HH:mm:ss or HH:mm
  if (str.includes(':')) {
    const parts = str.split(':').map((p) => parseInt(p, 10) || 0);
    if (parts.length >= 3) {
      const [h, m, s] = parts;
      const total = h * 3600 + m * 60 + s;
      return { hours: h, minutes: m, seconds: s, totalSeconds: total };
    } else if (parts.length === 2) {
      const [p0, p1] = parts;
      const total = p0 * 3600 + p1 * 60;
      return { hours: p0, minutes: p1, seconds: 0, totalSeconds: total };
    }
  }

  // Human string formats like "1h 30m 15s", "15m", "47s"
  let total = 0;
  const hMatch = str.match(/(\d+)\s*h/i);
  const mMatch = str.match(/(\d+)\s*m/i);
  const sMatch = str.match(/(\d+)\s*s/i);

  if (hMatch || mMatch || sMatch) {
    if (hMatch) total += parseInt(hMatch[1], 10) * 3600;
    if (mMatch) total += parseInt(mMatch[1], 10) * 60;
    if (sMatch) total += parseInt(sMatch[1], 10);
    return {
      hours: Math.floor(total / 3600),
      minutes: Math.floor((total % 3600) / 60),
      seconds: total % 60,
      totalSeconds: total,
    };
  }

  const fallback = parseInt(str, 10) || 0;
  return {
    hours: Math.floor(fallback / 3600),
    minutes: Math.floor((fallback % 3600) / 60),
    seconds: fallback % 60,
    totalSeconds: fallback,
  };
};

/**
 * Format call duration into human-readable format, e.g. "47s", "15m", "15m 30s", "1h 30m"
 */
const formatCallDuration = (raw: string | number | null | undefined): string => {
  const { hours, minutes, seconds, totalSeconds } = parseCallDuration(raw);
  if (totalSeconds <= 0) return '-';

  const parts: string[] = [];
  if (hours > 0) {
    parts.push(`${hours}h`);
  }
  if (minutes > 0) {
    parts.push(`${minutes}m`);
  }
  if (seconds > 0 || (hours === 0 && minutes === 0)) {
    parts.push(`${seconds}s`);
  }

  return parts.join(' ');
};

/**
 * Format call duration into digital timer string "HH:mm:ss", e.g. "00:00:47", "00:15:00"
 */
const formatDigitalDuration = (raw: string | number | null | undefined): string => {
  const { hours, minutes, seconds } = parseCallDuration(raw);
  const pad = (n: number) => String(n).padStart(2, '0');
  return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
};


/**
 * Format date and time to readable format according to company date and time format settings
 */
const formatDateTime = (date: string | Date, pageProps?: any): string => {
  if (!date) return '';

  const d = parseDateInput(date);
  if (!d) return '';

  const formattedDate = formatDate(d, pageProps);
  const formattedTime = formatTimeFromDate(d, pageProps);

  return `${formattedDate} ${formattedTime}`.trim();
};

/**
 * Extract time from a datetime and format it according to company time settings
 */
const formatTimeFromDate = (date: string | Date, pageProps?: any): string => {
  if (!date) return '';

  const d = parseDateInput(date);
  if (!d) return '';

  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');

  return formatTime(`${hours}:${minutes}`, pageProps);
};

/**
 * Get full image path
 */
const getImagePath = (path: string, pageProps?: any): string => {
  if (!path || typeof path !== 'string') return '';
  if (path.startsWith('http')) return path;

  // Handle automas package paths or storage/media paths - use baseUrl directly
  if (path.includes('packages/automas') || path.includes('storage/app/public/media')) {
    let baseUrl;
    if (pageProps?.baseUrl) {
      baseUrl = pageProps.baseUrl;
    } else {
      const { props } = usePage();
      baseUrl = (props as any).baseUrl || window.location.origin;
    }
    const cleanPath = path.startsWith('/') ? path : '/' + path;
    return `${baseUrl}${cleanPath}`;
  }

  try {
    let imageUrlPrefix;
    if (pageProps?.imageUrlPrefix) {
      imageUrlPrefix = pageProps.imageUrlPrefix;
    } else {
      const { props } = usePage();
      imageUrlPrefix = (props as any).imageUrlPrefix || '';
    }

    if (!imageUrlPrefix) return '';

    const prefixEndsWithSlash = imageUrlPrefix.endsWith('/');
    const pathStartsWithSlash = path.startsWith('/');

    if (prefixEndsWithSlash && pathStartsWithSlash) {
      return imageUrlPrefix + path.substring(1);
    } else if (!prefixEndsWithSlash && !pathStartsWithSlash) {
      return imageUrlPrefix + '/' + path;
    } else {
      return imageUrlPrefix + path;
    }
  } catch {
    return '';
  }
};

// const getSoundPath = (path: string, pageProps?: any): string => {
//   if (!path || typeof path !== 'string') return '';
//   if (path.startsWith('http')) return path;

//   // Handle automas package paths or storage/sounds paths - use baseUrl directly
//   if (path.includes('packages/automas') || path.includes('storage/app/public/sounds')) {
//     let baseUrl;
//     if (pageProps?.baseUrl) {
//       baseUrl = pageProps.baseUrl;
//     } else {
//       const { props } = usePage();
//       baseUrl = (props as any).baseUrl || window.location.origin;
//     }
//     const cleanPath = path.startsWith('/') ? path : '/' + path;
//     return `${baseUrl}${cleanPath}`;
//   }

//   try {
//     let imageUrlPrefix;
//     if (pageProps?.imageUrlPrefix) {
//       imageUrlPrefix = pageProps.imageUrlPrefix;
//     } else {
//       const { props } = usePage();
//       imageUrlPrefix = (props as any).imageUrlPrefix || '';
//     }

//     if (!imageUrlPrefix) return '';

//     const prefixEndsWithSlash = imageUrlPrefix.endsWith('/');
//     const pathStartsWithSlash = path.startsWith('/');

//     if (prefixEndsWithSlash && pathStartsWithSlash) {
//       return imageUrlPrefix + path.substring(1);
//     } else if (!prefixEndsWithSlash && !pathStartsWithSlash) {
//       return imageUrlPrefix + '/' + path;
//     } else {
//       return imageUrlPrefix + path;
//     }
//   } catch {
//     return '';
//   }
// };

/**
 * Format currency based on saved settings
 */
const formatCurrency = (amount: number | string, pageProps?: any): string => {
  try {
    const num = Number(amount) || 0;
    const decimalPlaces = parseInt(getCompanySetting('decimalFormat', pageProps) || '2');
    const decimalSeparator = getCompanySetting('decimalSeparator', pageProps) || '.';
    const thousandsSeparator = getCompanySetting('thousandsSeparator', pageProps) || ',';
    const floatNumber = getCompanySetting('floatNumber', pageProps) !== '0';
    const currencySymbolSpace = getCompanySetting('currencySymbolSpace', pageProps) === '1';
    const currencySymbolPosition = getCompanySetting('currencySymbolPosition', pageProps) || 'before';

    let finalAmount = floatNumber ? num : Math.floor(num);
    const parts = Number(finalAmount).toFixed(decimalPlaces).split('.');

    if (thousandsSeparator !== 'none') {
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
    }

    const formattedNumber = parts.join(decimalSeparator);
    const symbol = getCurrencySymbol(pageProps);
    const space = currencySymbolSpace ? ' ' : '';

    return currencySymbolPosition === 'before'
      ? `${symbol}${space}${formattedNumber}`
      : `${formattedNumber}${space}${symbol}`;
  } catch {
    return `$${Number(amount).toFixed(2)}`;
  }
};

const formatAdminCurrency = (amount: number | string, pageProps?: any): string => {
  try {
    const num = Number(amount) || 0;
    const decimalPlaces = parseInt(getAdminSetting('decimalFormat', pageProps) || '2');
    const decimalSeparator = getAdminSetting('decimalSeparator', pageProps) || '.';
    const thousandsSeparator = getAdminSetting('thousandsSeparator', pageProps) || ',';
    const floatNumber = getAdminSetting('floatNumber', pageProps) !== '0';
    const currencySymbolSpace = getAdminSetting('currencySymbolSpace', pageProps) === '1';
    const currencySymbolPosition = getAdminSetting('currencySymbolPosition', pageProps) || 'before';

    let finalAmount = floatNumber ? num : Math.floor(num);
    const parts = Number(finalAmount).toFixed(decimalPlaces).split('.');

    if (thousandsSeparator !== 'none') {
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
    }

    const formattedNumber = parts.join(decimalSeparator);
    const symbol = getAdminSetting('currencySymbol', pageProps) || '$';
    const space = currencySymbolSpace ? ' ' : '';

    return currencySymbolPosition === 'before'
      ? `${symbol}${space}${formattedNumber}`
      : `${formattedNumber}${space}${symbol}`;
  } catch {
    return `$${Number(amount).toFixed(2)}`;
  }
};

/**
 * Get currency symbol from settings
 */
const getCurrencySymbol = (pageProps?: any): string => {
  try {
    return getCompanySetting('currencySymbol', pageProps) || '$';
  } catch {
    return '$';
  }
};

const getAdminCurrencySymbol = (pageProps?: any): string => {
  try {
    return getAdminSetting('currencySymbol', pageProps) || '$';
  } catch {
    return '$';
  }
};

/**
 * Check if a package is active
 */
const isPackageActive = (packageName: string, pageProps?: any): boolean => {
  try {
    let activatedPackages;
    if (pageProps?.auth?.user?.activatedPackages) {
      activatedPackages = pageProps.auth.user.activatedPackages;
    } else {
      const { props } = usePage();
      activatedPackages = (props as any).auth?.user?.activatedPackages || [];
    }
    return activatedPackages.includes(packageName);
  } catch {
    return false;
  }
};
const formatStorage = (kb: number) => {
  if (kb >= 1024 * 1024) {
    return `${(kb / (1024 * 1024)).toFixed(1)} GB`;
  } else if (kb >= 1024) {
    return `${(kb / 1024).toFixed(1)} MB`;
  } else {
    return `${kb} GB`;
  }
};


/**
 * Get package favicon by package name
 */
const getPackageFavicon = (packageName: string, pageProps?: any): string | undefined => {
  try {
    let packages;

    if (pageProps?.packages) {
      packages = pageProps.packages;
    } else {
      const { props } = usePage();
      packages = (props as any).packages || [];
    }

    const packageData = packages.find((pkg: any) => pkg.name === packageName);
    return packageData?.image || undefined;
  } catch {
    return undefined;
  }
};

/**
 * Get package alias name by package name
 */
const getPackageAlias = (packageName: string, pageProps?: any): string | undefined => {
  try {
    let packages;
    if (pageProps?.packages) {
      packages = pageProps.packages;
    } else {
      // Only call usePage if pageProps not provided
      const { props } = usePage();
      packages = (props as any).packages || [];
    }

    const packageData = packages.find((pkg: any) => pkg.name === packageName);
    return packageData?.alias || packageName;
  } catch {
    return packageName;
  }
};

/**
 * Get enabled packages names
 */
const adminPackages = (pageProps?: any): string[] => {
  try {
    let packages;
    if (pageProps?.packages) {
      packages = pageProps.packages;
    } else {
      const { props } = usePage();
      packages = (props as any).packages || [];
    }

    return packages.filter((pkg: any) => pkg.is_enable === true).map((pkg: any) => pkg.name);
  } catch {
    return [];
  }
};

/**
 * Format file size in bytes to human readable format
 */
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/** Convert file to base64 string
 */
const convertFileToBase64 = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = error => reject(error);
  });
};

/**
 * Extract file extension from base64 string
 */
const getBase64FileExtension = (base64String: string): string => {
  const mimeExtensions: Record<string, string> = {
    'image/jpeg': 'jpg',
    'image/jpg': 'jpg',
    'image/png': 'png',
    'image/gif': 'gif',
    'image/svg+xml': 'svg',
    'application/pdf': 'pdf',
    'application/msword': 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
    'application/vnd.ms-excel': 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
    'text/plain': 'txt'
  };

  const match = base64String.match(/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).*,.*/);
  if (match) {
    const mimeType = match[1];
    return mimeExtensions[mimeType] || mimeType.split('/')[1] || 'png';
  }
  return 'png';
};


/**
 * Download any file (PDF, image, ZIP, etc.) from a URL.
 * Automatically extracts filename from Content-Disposition header.
 */

const downloadFile = (url: string): void => {
  // Create a temporary link
  const link = document.createElement('a');
  link.href = url;
  link.target = '_blank';  // opens in new tab
  link.rel = 'noopener noreferrer';

  // Optional: if it's a direct downloadable file, force download
  if (url.match(/\.(pdf|jpg|png|jpeg|docx|zip)$/i)) {
    link.download = '';
  } else {
    link.download = url.split('/').pop() || '';
  }

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

/**
 * Get subscription details for a user (similar to Laravel SubscriptionDetails function)
 */
interface SubscriptionDetail {
  status: boolean;
  active_plan?: number;
  billing_type?: string;
  plan_expire_date?: string;
  total_user?: string | number;
  total_storage?: string;
  seeder_run?: boolean;
}

const getSubscriptionDetails = (userId?: number, pageProps?: any): SubscriptionDetail => {
  const data: SubscriptionDetail = {
    status: false
  };

  try {
    let user;
    if (pageProps?.auth?.user) {
      user = pageProps.auth.user;
    } else {
      const { props } = usePage();
      user = (props as any).auth?.user;
    }

    if (!user) {
      return data;
    }


    if (user.active_plan && user.active_plan !== 0) {
      data.status = true;
      data.active_plan = user.active_plan;
      data.billing_type = user.billing_type || 'monthly';
      data.plan_expire_date = user.plan_expire_date;
      data.total_user = user.total_user === -1 ? 'Unlimited' : (user.total_user === 0 ? '0' : (user.total_user || 'Unlimited'));
      data.total_storage = user.storage_limit ? formatStorage(user.storage_limit) : '0';
      data.seeder_run = user.seeder_run;
    }

    return data;
  } catch {
    return data;
  }
};

export {
  formatDate,
  formatTime,
  formatDateTime,
  formatTimeFromDate,
  getImagePath,
  formatCurrency,
  formatAdminCurrency,
  getCurrencySymbol,
  getAdminCurrencySymbol,
  isPackageActive,
  getCompanySetting,
  getAdminSetting,
  formatStorage,
  formatFileSize,
  getPackageFavicon,
  getPackageAlias,
  adminPackages,
  convertFileToBase64,
  getBase64FileExtension,
  downloadFile,
  getSubscriptionDetails,
  parseCallDuration,
  formatCallDuration,
  formatDigitalDuration
};
