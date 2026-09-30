import { Input } from './input';
import { Label } from './label';
import InputError from './input-error';
import { useTranslation } from 'react-i18next';
import { useEffect } from 'react';

interface SlugInputProps {
    label?: string;
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    error?: string;
    className?: string;
    id?: string;
    required?: boolean;
    disabled?: boolean;
    autoGenerate?: boolean;
    sourceValue?: string;
}

export function SlugInputComponent({
    label,
    value,
    onChange,
    placeholder,
    error,
    className,
    id,
    required,
    disabled,
    autoGenerate = true,
    sourceValue
}: SlugInputProps) {
    const { t } = useTranslation();

    const generateSlug = (text: string) => {
        return text
            .toLowerCase()
            .replace(/[^a-z\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    };

    const handleChange = (newValue: string) => {
        if (!disabled) {
            const slug = generateSlug(newValue);
            onChange(slug);
        }
    };

    // Auto-generate slug from source value whenever it changes
    useEffect(() => {
        if (autoGenerate && sourceValue !== undefined) {
            const autoSlug = sourceValue ? generateSlug(sourceValue) : '';
            onChange(autoSlug);
        }
    }, [sourceValue, autoGenerate]);

    return (
        <div>
            {label && <Label htmlFor={id} required={required}>{label}</Label>}
            <Input
                id={id}
                type="text"
                value={value}
                onChange={(e) => !disabled && handleChange(e.target.value)}
                placeholder={placeholder || t('url-friendly-slug')}
                className={className}
                pattern="^[a-z\-]{1,50}$"
                required={required}
                disabled={disabled}
            />
            <p className="text-xs text-muted-foreground mt-1">{t('Only lowercase letters and hyphens allowed')}</p>
            <InputError message={error} />
        </div>
    );
}