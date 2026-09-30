"use client"

import * as React from "react"
import { Clock, ChevronUp, ChevronDown, RotateCcw, Check, X } from "lucide-react"
import { useTranslation } from 'react-i18next'
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { parseCallDuration, formatCallDuration, formatDigitalDuration } from "@/utils/helpers"

export interface DurationPickerProps {
  value?: string
  onChange: (value: string) => void
  placeholder?: string
  className?: string
  id?: string
  required?: boolean
  style?: React.CSSProperties
  disabled?: boolean
}

export function DurationPicker({
  value,
  onChange,
  placeholder,
  className,
  id,
  required,
  style,
  disabled
}: DurationPickerProps) {
  const { t } = useTranslation();
  const [open, setOpen] = React.useState(false);

  const initialParsed = React.useMemo(() => parseCallDuration(value), [value]);
  const [hour, setHour] = React.useState(initialParsed.hours);
  const [minute, setMinute] = React.useState(initialParsed.minutes);
  const [second, setSecond] = React.useState(initialParsed.seconds);

  const lastEmittedValueRef = React.useRef<string | undefined>(value);

  // Sync state with value prop when external value changes
  React.useEffect(() => {
    if (value !== lastEmittedValueRef.current) {
      const parsed = parseCallDuration(value);
      setHour(parsed.hours);
      setMinute(parsed.minutes);
      setSecond(parsed.seconds);
      lastEmittedValueRef.current = value;
    }
  }, [value]);

  // Sync values when popover opens
  React.useEffect(() => {
    if (open) {
      const parsed = parseCallDuration(value);
      setHour(parsed.hours);
      setMinute(parsed.minutes);
      setSecond(parsed.seconds);
    }
  }, [open, value]);

  const pad = (n: number) => String(n).padStart(2, '0');

  const increment = (current: number, max: number, setter: (val: number) => void) => {
    setter(current < max ? current + 1 : 0);
  };

  const decrement = (current: number, max: number, setter: (val: number) => void) => {
    setter(current > 0 ? current - 1 : max);
  };

  const handleInputChange = (rawVal: string, max: number, setter: (val: number) => void) => {
    const clean = rawVal.replace(/\D/g, '').slice(0, 2);
    if (clean === '') {
      setter(0);
      return;
    }
    const num = Math.min(max, parseInt(clean, 10) || 0);
    setter(num);
  };

  const handleKeyDown = (
    e: React.KeyboardEvent<HTMLInputElement>,
    current: number,
    max: number,
    setter: (val: number) => void
  ) => {
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      increment(current, max, setter);
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      decrement(current, max, setter);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      handleApply();
    }
  };

  const handleWheel = (
    e: React.WheelEvent<HTMLDivElement>,
    current: number,
    max: number,
    setter: (val: number) => void
  ) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.deltaY < 0) {
      increment(current, max, setter);
    } else if (e.deltaY > 0) {
      decrement(current, max, setter);
    }
  };

  const handleApply = () => {
    const totalSecs = hour * 3600 + minute * 60 + second;
    const formatted = totalSecs === 0 ? '' : `${pad(hour)}:${pad(minute)}:${pad(second)}`;
    lastEmittedValueRef.current = formatted;
    onChange(formatted);
    setOpen(false);
  };

  const handleClear = () => {
    setHour(0);
    setMinute(0);
    setSecond(0);
    lastEmittedValueRef.current = '';
    onChange('');
    setOpen(false);
  };

  const applyPreset = (presetSecs: number) => {
    const h = Math.floor(presetSecs / 3600);
    const m = Math.floor((presetSecs % 3600) / 60);
    const s = presetSecs % 60;
    setHour(h);
    setMinute(m);
    setSecond(s);
    const formatted = `${pad(h)}:${pad(m)}:${pad(s)}`;
    lastEmittedValueRef.current = formatted;
    onChange(formatted);
  };

  const presets = [
    { label: '30s', secs: 30 },
    { label: '1m', secs: 60 },
    { label: '2m', secs: 120 },
    { label: '5m', secs: 300 },
    { label: '10m', secs: 600 },
    { label: '15m', secs: 900 },
    { label: '30m', secs: 1800 },
    { label: '45m', secs: 2700 },
    { label: '1h', secs: 3600 },
  ];

  const currentTotal = hour * 3600 + minute * 60 + second;
  const currentFormatted = formatCallDuration(`${pad(hour)}:${pad(minute)}:${pad(second)}`);

  // Display value for trigger button
  const displayValue = React.useMemo(() => {
    if (!value) return null;
    const parsed = parseCallDuration(value);
    if (parsed.totalSeconds <= 0) return null;
    const digital = formatDigitalDuration(value);
    const human = formatCallDuration(value);
    return `${digital} (${human})`;
  }, [value]);

  return (
    <div className={cn("relative w-full", className)}>
      {id && <input id={id} type="hidden" value={value || ''} required={required} />}
      <Popover open={open && !disabled} onOpenChange={disabled ? undefined : setOpen}>
        <PopoverTrigger asChild>
          <Button
            type="button"
            variant="outline"
            className={cn(
              'w-full justify-start text-left font-normal h-10 pr-9',
              !displayValue && 'text-muted-foreground',
              disabled && 'opacity-50 cursor-not-allowed'
            )}
            style={style}
            disabled={disabled}
          >
            <Clock className="mr-2 h-4 w-4 text-muted-foreground shrink-0" />
            <span className="truncate">
              {displayValue || (placeholder || t('Select Duration'))}
            </span>
          </Button>
        </PopoverTrigger>

        <PopoverContent className="w-[310px] p-3 shadow-xl border rounded-xl" align="start">
          {/* Quick Presets */}
          <div className="pb-3 border-b">
            <div className="text-[11px] font-medium text-muted-foreground mb-1.5 flex items-center justify-between">
              <span>{t('Quick Presets')}</span>
              <span className="text-[10px] text-muted-foreground/70">{t('1-click select')}</span>
            </div>
            <div className="flex flex-wrap gap-1.5">
              {presets.map((p) => {
                const isSelected = currentTotal === p.secs;
                return (
                  <button
                    key={p.label}
                    type="button"
                    onClick={() => applyPreset(p.secs)}
                    className={cn(
                      "px-2.5 py-1 text-xs font-medium rounded-md border transition-all duration-150",
                      isSelected
                        ? "bg-primary text-primary-foreground border-primary font-semibold shadow-sm"
                        : "bg-background text-foreground hover:bg-accent hover:text-accent-foreground border-input"
                    )}
                  >
                    {p.label}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Custom Duration Steppers */}
          <div className="pt-3 pb-1">
            <div className="text-[11px] font-medium text-muted-foreground mb-2 text-center">
              {t('Custom Duration')}
            </div>
            <div className="flex items-center justify-center gap-2">
              {/* Hours */}
              <div className="flex flex-col items-center">
                <div
                  className="flex flex-col items-center bg-muted/30 hover:bg-muted/50 rounded-lg p-1 border transition-colors w-[72px]"
                  onWheel={(e) => handleWheel(e, hour, 23, setHour)}
                >
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => increment(hour, 23, setHour)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Increase hours')}
                  >
                    <ChevronUp className="h-4 w-4" />
                  </Button>
                  <input
                    type="text"
                    inputMode="numeric"
                    value={pad(hour)}
                    onFocus={(e) => e.target.select()}
                    onChange={(e) => handleInputChange(e.target.value, 23, setHour)}
                    onKeyDown={(e) => handleKeyDown(e, hour, 23, setHour)}
                    className="w-full text-center text-xl font-bold font-mono bg-transparent border-0 focus:ring-0 p-0 text-foreground selection:bg-primary/20"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => decrement(hour, 23, setHour)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Decrease hours')}
                  >
                    <ChevronDown className="h-4 w-4" />
                  </Button>
                </div>
                <span className="text-[11px] font-medium text-muted-foreground mt-1 select-none">
                  {t('Hours')}
                </span>
              </div>

              <span className="text-xl font-bold text-muted-foreground mb-5 select-none">:</span>

              {/* Minutes */}
              <div className="flex flex-col items-center">
                <div
                  className="flex flex-col items-center bg-muted/30 hover:bg-muted/50 rounded-lg p-1 border transition-colors w-[72px]"
                  onWheel={(e) => handleWheel(e, minute, 59, setMinute)}
                >
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => increment(minute, 59, setMinute)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Increase minutes')}
                  >
                    <ChevronUp className="h-4 w-4" />
                  </Button>
                  <input
                    type="text"
                    inputMode="numeric"
                    value={pad(minute)}
                    onFocus={(e) => e.target.select()}
                    onChange={(e) => handleInputChange(e.target.value, 59, setMinute)}
                    onKeyDown={(e) => handleKeyDown(e, minute, 59, setMinute)}
                    className="w-full text-center text-xl font-bold font-mono bg-transparent border-0 focus:ring-0 p-0 text-foreground selection:bg-primary/20"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => decrement(minute, 59, setMinute)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Decrease minutes')}
                  >
                    <ChevronDown className="h-4 w-4" />
                  </Button>
                </div>
                <span className="text-[11px] font-medium text-muted-foreground mt-1 select-none">
                  {t('Minutes')}
                </span>
              </div>

              <span className="text-xl font-bold text-muted-foreground mb-5 select-none">:</span>

              {/* Seconds */}
              <div className="flex flex-col items-center">
                <div
                  className="flex flex-col items-center bg-muted/30 hover:bg-muted/50 rounded-lg p-1 border transition-colors w-[72px]"
                  onWheel={(e) => handleWheel(e, second, 59, setSecond)}
                >
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => increment(second, 59, setSecond)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Increase seconds')}
                  >
                    <ChevronUp className="h-4 w-4" />
                  </Button>
                  <input
                    type="text"
                    inputMode="numeric"
                    value={pad(second)}
                    onFocus={(e) => e.target.select()}
                    onChange={(e) => handleInputChange(e.target.value, 59, setSecond)}
                    onKeyDown={(e) => handleKeyDown(e, second, 59, setSecond)}
                    className="w-full text-center text-xl font-bold font-mono bg-transparent border-0 focus:ring-0 p-0 text-foreground selection:bg-primary/20"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => decrement(second, 59, setSecond)}
                    className="h-6 w-full rounded hover:bg-background/80 text-muted-foreground hover:text-foreground"
                    title={t('Decrease seconds')}
                  >
                    <ChevronDown className="h-4 w-4" />
                  </Button>
                </div>
                <span className="text-[11px] font-medium text-muted-foreground mt-1 select-none">
                  {t('Seconds')}
                </span>
              </div>
            </div>
          </div>

          {/* Footer with Summary and Actions */}
          <div className="pt-3 mt-1 border-t flex items-center justify-between gap-2">
            <div className="text-xs truncate max-w-[140px]">
              {currentTotal > 0 ? (
                <div className="flex items-baseline gap-1 truncate" title={`${pad(hour)}:${pad(minute)}:${pad(second)} (${currentFormatted})`}>
                  <span className="font-mono font-semibold text-foreground shrink-0">
                    {pad(hour)}:{pad(minute)}:{pad(second)}
                  </span>
                  <span className="text-[11px] text-muted-foreground truncate">
                    ({currentFormatted})
                  </span>
                </div>
              ) : (
                <span className="text-muted-foreground text-xs">{t('0s')}</span>
              )}
            </div>
            <div className="flex items-center gap-1.5 shrink-0">
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={handleClear}
                disabled={currentTotal === 0}
                className="h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
              >
                <RotateCcw className="h-3 w-3 mr-1" />
                {t('Clear')}
              </Button>
              <Button
                type="button"
                size="sm"
                onClick={handleApply}
                className="h-8 px-3 text-xs font-medium"
              >
                <Check className="h-3.5 w-3.5 mr-1" />
                {t('Apply')}
              </Button>
            </div>
          </div>
        </PopoverContent>
      </Popover>

      {/* 1-Click Clear Button on Trigger */}
      {displayValue && !disabled && (
        <button
          type="button"
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            handleClear();
          }}
          className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors z-10 focus:outline-none"
          title={t('Clear')}
        >
          <X className="h-3.5 w-3.5" />
        </button>
      )}
    </div>
  );
}
