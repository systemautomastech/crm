import React, { useState, useEffect } from 'react';
import { Input } from './input';
import { Label } from './label';
import InputError from './input-error';
import { useTranslation } from 'react-i18next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './select';

export interface CountryCode {
    code: string;
    dial: string;
    flag: string;
    name: string;
}

export const COUNTRY_CODES: CountryCode[] = [
    { code: 'BD', dial: '+880', flag: '🇧🇩', name: 'Bangladesh' },
    { code: 'AF', dial: '+93', flag: '🇦🇫', name: 'Afghanistan' },
    { code: 'AL', dial: '+355', flag: '🇦🇱', name: 'Albania' },
    { code: 'DZ', dial: '+213', flag: '🇩🇿', name: 'Algeria' },
    { code: 'AD', dial: '+376', flag: '🇦🇩', name: 'Andorra' },
    { code: 'AO', dial: '+244', flag: '🇦🇴', name: 'Angola' },
    { code: 'AR', dial: '+54', flag: '🇦🇷', name: 'Argentina' },
    { code: 'AM', dial: '+374', flag: '🇦🇲', name: 'Armenia' },
    { code: 'AU', dial: '+61', flag: '🇦🇺', name: 'Australia' },
    { code: 'AT', dial: '+43', flag: '🇦🇹', name: 'Austria' },
    { code: 'AZ', dial: '+994', flag: '🇦🇿', name: 'Azerbaijan' },
    { code: 'BH', dial: '+973', flag: '🇧🇭', name: 'Bahrain' },
    { code: 'BY', dial: '+375', flag: '🇧🇾', name: 'Belarus' },
    { code: 'BE', dial: '+32', flag: '🇧🇪', name: 'Belgium' },
    { code: 'BZ', dial: '+501', flag: '🇧🇿', name: 'Belize' },
    { code: 'BJ', dial: '+229', flag: '🇧🇯', name: 'Benin' },
    { code: 'BT', dial: '+975', flag: '🇧🇹', name: 'Bhutan' },
    { code: 'BO', dial: '+591', flag: '🇧🇴', name: 'Bolivia' },
    { code: 'BA', dial: '+387', flag: '🇧🇦', name: 'Bosnia and Herzegovina' },
    { code: 'BW', dial: '+267', flag: '🇧🇼', name: 'Botswana' },
    { code: 'BR', dial: '+55', flag: '🇧🇷', name: 'Brazil' },
    { code: 'BN', dial: '+673', flag: '🇧🇳', name: 'Brunei' },
    { code: 'BG', dial: '+359', flag: '🇧🇬', name: 'Bulgaria' },
    { code: 'BF', dial: '+226', flag: '🇧🇫', name: 'Burkina Faso' },
    { code: 'BI', dial: '+257', flag: '🇧🇮', name: 'Burundi' },
    { code: 'KH', dial: '+855', flag: '🇰🇭', name: 'Cambodia' },
    { code: 'CM', dial: '+237', flag: '🇨🇲', name: 'Cameroon' },
    { code: 'CA', dial: '+1', flag: '🇨🇦', name: 'Canada' },
    { code: 'CL', dial: '+56', flag: '🇨🇱', name: 'Chile' },
    { code: 'CN', dial: '+86', flag: '🇨🇳', name: 'China' },
    { code: 'CO', dial: '+57', flag: '🇨🇴', name: 'Colombia' },
    { code: 'CR', dial: '+506', flag: '🇨🇷', name: 'Costa Rica' },
    { code: 'HR', dial: '+385', flag: '🇭🇷', name: 'Croatia' },
    { code: 'CU', dial: '+53', flag: '🇨🇺', name: 'Cuba' },
    { code: 'CY', dial: '+357', flag: '🇨🇾', name: 'Cyprus' },
    { code: 'CZ', dial: '+420', flag: '🇨🇿', name: 'Czech Republic' },
    { code: 'DK', dial: '+45', flag: '🇩🇰', name: 'Denmark' },
    { code: 'DJ', dial: '+253', flag: '🇩🇯', name: 'Djibouti' },
    { code: 'DO', dial: '+1', flag: '🇩🇴', name: 'Dominican Republic' },
    { code: 'EC', dial: '+593', flag: '🇪🇨', name: 'Ecuador' },
    { code: 'EG', dial: '+20', flag: '🇪🇬', name: 'Egypt' },
    { code: 'SV', dial: '+503', flag: '🇸🇻', name: 'El Salvador' },
    { code: 'EE', dial: '+372', flag: '🇪🇪', name: 'Estonia' },
    { code: 'ET', dial: '+251', flag: '🇪🇹', name: 'Ethiopia' },
    { code: 'FJ', dial: '+679', flag: '🇫🇯', name: 'Fiji' },
    { code: 'FI', dial: '+358', flag: '🇫🇮', name: 'Finland' },
    { code: 'FR', dial: '+33', flag: '🇫🇷', name: 'France' },
    { code: 'GA', dial: '+241', flag: '🇬🇦', name: 'Gabon' },
    { code: 'GM', dial: '+220', flag: '🇬🇲', name: 'Gambia' },
    { code: 'GE', dial: '+995', flag: '🇬🇪', name: 'Georgia' },
    { code: 'DE', dial: '+49', flag: '🇩🇪', name: 'Germany' },
    { code: 'GH', dial: '+233', flag: '🇬🇭', name: 'Ghana' },
    { code: 'GR', dial: '+30', flag: '🇬🇷', name: 'Greece' },
    { code: 'GT', dial: '+502', flag: '🇬🇹', name: 'Guatemala' },
    { code: 'GN', dial: '+224', flag: '🇬🇳', name: 'Guinea' },
    { code: 'GY', dial: '+592', flag: '🇬🇾', name: 'Guyana' },
    { code: 'HT', dial: '+509', flag: '🇭🇹', name: 'Haiti' },
    { code: 'HN', dial: '+504', flag: '🇭🇳', name: 'Honduras' },
    { code: 'HK', dial: '+852', flag: '🇭🇰', name: 'Hong Kong' },
    { code: 'HU', dial: '+36', flag: '🇭🇺', name: 'Hungary' },
    { code: 'IS', dial: '+354', flag: '🇮🇸', name: 'Iceland' },
    { code: 'IN', dial: '+91', flag: '🇮🇳', name: 'India' },
    { code: 'ID', dial: '+62', flag: '🇮🇩', name: 'Indonesia' },
    { code: 'IR', dial: '+98', flag: '🇮🇷', name: 'Iran' },
    { code: 'IQ', dial: '+964', flag: '🇮🇶', name: 'Iraq' },
    { code: 'IE', dial: '+353', flag: '🇮🇪', name: 'Ireland' },
    { code: 'IL', dial: '+972', flag: '🇮🇱', name: 'Israel' },
    { code: 'IT', dial: '+39', flag: '🇮🇹', name: 'Italy' },
    { code: 'JM', dial: '+1', flag: '🇯🇲', name: 'Jamaica' },
    { code: 'JP', dial: '+81', flag: '🇯🇵', name: 'Japan' },
    { code: 'JO', dial: '+962', flag: '🇯🇴', name: 'Jordan' },
    { code: 'KZ', dial: '+7', flag: '🇰🇿', name: 'Kazakhstan' },
    { code: 'KE', dial: '+254', flag: '🇰🇪', name: 'Kenya' },
    { code: 'KR', dial: '+82', flag: '🇰🇷', name: 'South Korea' },
    { code: 'KW', dial: '+965', flag: '🇰🇼', name: 'Kuwait' },
    { code: 'KG', dial: '+996', flag: '🇰🇬', name: 'Kyrgyzstan' },
    { code: 'LA', dial: '+856', flag: '🇱🇦', name: 'Laos' },
    { code: 'LV', dial: '+371', flag: '🇱🇻', name: 'Latvia' },
    { code: 'LB', dial: '+961', flag: '🇱🇧', name: 'Lebanon' },
    { code: 'LY', dial: '+218', flag: '🇱🇾', name: 'Libya' },
    { code: 'LT', dial: '+370', flag: '🇱🇹', name: 'Lithuania' },
    { code: 'LU', dial: '+352', flag: '🇱🇺', name: 'Luxembourg' },
    { code: 'MO', dial: '+853', flag: '🇲🇴', name: 'Macau' },
    { code: 'MY', dial: '+60', flag: '🇲🇾', name: 'Malaysia' },
    { code: 'MV', dial: '+960', flag: '🇲🇻', name: 'Maldives' },
    { code: 'MT', dial: '+356', flag: '🇲🇹', name: 'Malta' },
    { code: 'MX', dial: '+52', flag: '🇲🇽', name: 'Mexico' },
    { code: 'MD', dial: '+373', flag: '🇲🇩', name: 'Moldova' },
    { code: 'MC', dial: '+377', flag: '🇲🇨', name: 'Monaco' },
    { code: 'MN', dial: '+976', flag: '🇲🇳', name: 'Mongolia' },
    { code: 'ME', dial: '+382', flag: '🇲🇪', name: 'Montenegro' },
    { code: 'MA', dial: '+212', flag: '🇲🇦', name: 'Morocco' },
    { code: 'MZ', dial: '+258', flag: '🇲🇿', name: 'Mozambique' },
    { code: 'MM', dial: '+95', flag: '🇲🇲', name: 'Myanmar' },
    { code: 'NA', dial: '+264', flag: '🇳🇦', name: 'Namibia' },
    { code: 'NP', dial: '+977', flag: '🇳🇵', name: 'Nepal' },
    { code: 'NL', dial: '+31', flag: '🇳🇱', name: 'Netherlands' },
    { code: 'NZ', dial: '+64', flag: '🇳🇿', name: 'New Zealand' },
    { code: 'NI', dial: '+505', flag: '🇳🇮', name: 'Nicaragua' },
    { code: 'NG', dial: '+234', flag: '🇳🇬', name: 'Nigeria' },
    { code: 'NO', dial: '+47', flag: '🇳🇴', name: 'Norway' },
    { code: 'OM', dial: '+968', flag: '🇴🇲', name: 'Oman' },
    { code: 'PK', dial: '+92', flag: '🇵🇰', name: 'Pakistan' },
    { code: 'PS', dial: '+970', flag: '🇵🇸', name: 'Palestine' },
    { code: 'PA', dial: '+507', flag: '🇵🇦', name: 'Panama' },
    { code: 'PY', dial: '+595', flag: '🇵🇾', name: 'Paraguay' },
    { code: 'PE', dial: '+51', flag: '🇵🇪', name: 'Peru' },
    { code: 'PH', dial: '+63', flag: '🇵🇭', name: 'Philippines' },
    { code: 'PL', dial: '+48', flag: '🇵🇱', name: 'Poland' },
    { code: 'PT', dial: '+351', flag: '🇵🇹', name: 'Portugal' },
    { code: 'QA', dial: '+974', flag: '🇶🇦', name: 'Qatar' },
    { code: 'RO', dial: '+40', flag: '🇷🇴', name: 'Romania' },
    { code: 'RU', dial: '+7', flag: '🇷🇺', name: 'Russia' },
    { code: 'RW', dial: '+250', flag: '🇷🇼', name: 'Rwanda' },
    { code: 'SA', dial: '+966', flag: '🇸🇦', name: 'Saudi Arabia' },
    { code: 'SN', dial: '+221', flag: '🇸🇳', name: 'Senegal' },
    { code: 'RS', dial: '+381', flag: '🇷🇸', name: 'Serbia' },
    { code: 'SG', dial: '+65', flag: '🇸🇬', name: 'Singapore' },
    { code: 'SK', dial: '+421', flag: '🇸🇰', name: 'Slovakia' },
    { code: 'SI', dial: '+386', flag: '🇸🇮', name: 'Slovenia' },
    { code: 'ZA', dial: '+27', flag: '🇿🇦', name: 'South Africa' },
    { code: 'ES', dial: '+34', flag: '🇪🇸', name: 'Spain' },
    { code: 'LK', dial: '+94', flag: '🇱🇰', name: 'Sri Lanka' },
    { code: 'SD', dial: '+249', flag: '🇸🇩', name: 'Sudan' },
    { code: 'SE', dial: '+46', flag: '🇸🇪', name: 'Sweden' },
    { code: 'CH', dial: '+41', flag: '🇨🇭', name: 'Switzerland' },
    { code: 'SY', dial: '+963', flag: '🇸🇾', name: 'Syria' },
    { code: 'TW', dial: '+886', flag: '🇹🇼', name: 'Taiwan' },
    { code: 'TJ', dial: '+992', flag: '🇹🇯', name: 'Tajikistan' },
    { code: 'TZ', dial: '+255', flag: '🇹🇿', name: 'Tanzania' },
    { code: 'TH', dial: '+66', flag: '🇹🇭', name: 'Thailand' },
    { code: 'TN', dial: '+216', flag: '🇹🇳', name: 'Tunisia' },
    { code: 'TR', dial: '+90', flag: '🇹🇷', name: 'Turkey' },
    { code: 'UG', dial: '+256', flag: '🇺🇬', name: 'Uganda' },
    { code: 'UA', dial: '+380', flag: '🇺🇦', name: 'Ukraine' },
    { code: 'AE', dial: '+971', flag: '🇦🇪', name: 'United Arab Emirates' },
    { code: 'GB', dial: '+44', flag: '🇬🇧', name: 'United Kingdom' },
    { code: 'US', dial: '+1', flag: '🇺🇸', name: 'United States' },
    { code: 'UY', dial: '+598', flag: '🇺🇾', name: 'Uruguay' },
    { code: 'UZ', dial: '+998', flag: '🇺🇿', name: 'Uzbekistan' },
    { code: 'VE', dial: '+58', flag: '🇻🇪', name: 'Venezuela' },
    { code: 'VN', dial: '+84', flag: '🇻🇳', name: 'Vietnam' },
    { code: 'YE', dial: '+967', flag: '🇾🇪', name: 'Yemen' },
    { code: 'ZM', dial: '+260', flag: '🇿🇲', name: 'Zambia' },
    { code: 'ZW', dial: '+263', flag: '🇿🇼', name: 'Zimbabwe' },
];

interface CountryPhoneInputProps {
    label?: string;
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    error?: string;
    className?: string;
    id?: string;
    required?: boolean;
    readOnly?: boolean;
    style?: React.CSSProperties;
}

export function CountryPhoneInput({
    label,
    value,
    onChange,
    placeholder,
    error,
    className,
    id = 'phone_number',
    required,
    readOnly,
    style
}: CountryPhoneInputProps) {
    const { t } = useTranslation();
    const [selectedDial, setSelectedDial] = useState<string>('+880');
    const [phoneNumber, setPhoneNumber] = useState<string>('');

    // Auto detect user location / country code on initial load
    useEffect(() => {
        const detectLocation = async () => {
            try {
                // Try browser timezone first
                const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (timeZone) {
                    if (timeZone.includes('Dhaka') || timeZone.includes('Asia/Dhaka')) {
                        setSelectedDial('+880');
                        return;
                    } else if (timeZone.includes('Kolkata') || timeZone.includes('Asia/Kolkata')) {
                        setSelectedDial('+91');
                        return;
                    } else if (timeZone.includes('London') || timeZone.includes('Europe/London')) {
                        setSelectedDial('+44');
                        return;
                    } else if (timeZone.includes('New_York') || timeZone.includes('America/')) {
                        setSelectedDial('+1');
                        return;
                    }
                }

                // Fallback IP lookup
                const res = await fetch('https://ipapi.co/json/');
                const data = await res.json();
                if (data && data.country_calling_code) {
                    const matched = COUNTRY_CODES.find(c => c.dial === data.country_calling_code || c.code === data.country_code);
                    if (matched) {
                        setSelectedDial(matched.dial);
                    }
                }
            } catch (e) {
                // Default to BD +880
                setSelectedDial('+880');
            }
        };

        detectLocation();
    }, []);

    // Parse incoming full value if supplied
    useEffect(() => {
        if (!value) {
            setPhoneNumber('');
            return;
        }

        const matchedCountry = COUNTRY_CODES.find(c => value.startsWith(c.dial));
        if (matchedCountry) {
            setSelectedDial(matchedCountry.dial);
            let rest = value.substring(matchedCountry.dial.length);
            if (matchedCountry.dial === '+880' && rest.startsWith('0')) {
                rest = rest.substring(1);
            }
            setPhoneNumber(rest);
        } else {
            setPhoneNumber(value);
        }
    }, [value]);

    const handleDialChange = (newDial: string) => {
        setSelectedDial(newDial);
        updateFullValue(newDial, phoneNumber);
    };

    const handleNumberChange = (inputVal: string) => {
        let cleanVal = inputVal.replace(/[^0-9]/g, '');
        // For BD, if user types leading 0, strip it since dial is +880
        if (selectedDial === '+880' && cleanVal.startsWith('0')) {
            cleanVal = cleanVal.substring(1);
        }
        setPhoneNumber(cleanVal);
        updateFullValue(selectedDial, cleanVal);
    };

    const updateFullValue = (dial: string, num: string) => {
        if (!num) {
            onChange('');
        } else {
            onChange(`${dial}${num}`);
        }
    };

    const currentCountry = COUNTRY_CODES.find(c => c.dial === selectedDial) || COUNTRY_CODES[0];

    return (
        <div className="space-y-1">
            {label && <Label htmlFor={id} required={required}>{label}</Label>}
            <div className="flex gap-2">
                {/* Country Code Dropdown with Flag Image */}
                <Select value={selectedDial} onValueChange={handleDialChange} disabled={readOnly}>
                    <SelectTrigger className="w-[115px] flex-shrink-0 bg-white dark:bg-slate-900 border-input">
                        <SelectValue>
                            <span className="flex items-center gap-1.5 text-xs font-semibold">
                                <img
                                    src={`https://flagcdn.com/w20/${currentCountry.code.toLowerCase()}.png`}
                                    srcSet={`https://flagcdn.com/w40/${currentCountry.code.toLowerCase()}.png 2x`}
                                    width="20"
                                    height="15"
                                    alt={currentCountry.name}
                                    className="inline-block object-cover rounded-sm shadow-xs"
                                />
                                <span>{currentCountry.dial}</span>
                            </span>
                        </SelectValue>
                    </SelectTrigger>
                    <SelectContent className="max-h-60 overflow-y-auto">
                        {COUNTRY_CODES.map((country) => (
                            <SelectItem key={country.code} value={country.dial} className="text-xs">
                                <span className="flex items-center gap-2">
                                    <img
                                        src={`https://flagcdn.com/w20/${country.code.toLowerCase()}.png`}
                                        srcSet={`https://flagcdn.com/w40/${country.code.toLowerCase()}.png 2x`}
                                        width="20"
                                        height="15"
                                        alt={country.name}
                                        className="inline-block object-cover rounded-sm shadow-xs"
                                    />
                                    <span className="font-semibold">{country.dial}</span>
                                    <span className="text-muted-foreground truncate">({country.name})</span>
                                </span>
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                {/* Phone Number Input */}
                <Input
                    id={id}
                    type="tel"
                    value={phoneNumber}
                    onChange={(e) => handleNumberChange(e.target.value)}
                    placeholder={placeholder || (selectedDial === '+880' ? '1712345678' : '1234567890')}
                    className={`flex-1 ${className || ''} ${readOnly ? 'bg-gray-50' : ''}`}
                    required={required}
                    readOnly={readOnly}
                    style={style}
                />
            </div>
            <InputError message={error} />
        </div>
    );
}
