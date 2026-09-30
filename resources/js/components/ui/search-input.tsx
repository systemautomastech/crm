import { Search, X } from "lucide-react";
import { Input } from "./input";
import { Button } from "./button";
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';

interface SearchInputProps {
  value: string;
  onChange: (value: string) => void;
  onSearch: () => void;
  placeholder?: string;
  className?: string;
}

export function SearchInput({ 
  value, 
  onChange, 
  onSearch,
  placeholder,
  className = "w-80"
}: SearchInputProps) {
  const { t } = useTranslation();

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      onSearch();
    }
  };

  const handleClear = () => {
    onChange('');
    const urlParams = new URLSearchParams(window.location.search);
    const params: Record<string, string> = {};
    let keyToRemove = '';
    
    // Find which key has the current value and build params in one loop
    urlParams.forEach((paramValue, key) => {
      if (paramValue === value && !keyToRemove) {
        keyToRemove = key;
      } else {
        params[key] = paramValue;
      }
    });
    
    router.get(window.location.pathname, params, {
      preserveState: true,
      replace: true
    });
  };

  return (
    <div className="flex items-center gap-2">
      <div className="relative">
        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          placeholder={placeholder || t('Search...')}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          onKeyPress={handleKeyPress}
          className={`pl-10 ${value ? 'pr-10' : ''} ${className}`}
        />
        {value && (
          <Button
            variant="ghost"
            size="sm"
            onClick={handleClear}
            className="absolute right-1 top-1/2 transform -translate-y-1/2 h-6 w-6 p-0 text-muted-foreground hover:text-foreground"
          >
            <X className="h-4 w-4" />
          </Button>
        )}
      </div>
      <Button onClick={() => onSearch()}>{t('Search')}</Button>
    </div>
  );
}