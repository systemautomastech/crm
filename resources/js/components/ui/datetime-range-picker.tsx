"use client"

import * as React from "react"
import { Calendar as CalendarIcon } from "lucide-react"
import DatePicker from "react-datepicker"
import "react-datepicker/dist/react-datepicker.css"
import { useTranslation } from 'react-i18next'

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DateTimeRangePickerProps {
  value?: string
  onChange: (value: string) => void
  placeholder?: string
  className?: string
  triggerClassName?: string
  id?: string
  required?: boolean
  disabled?: boolean
  timeFormat?: string
  dateFormat?: string
  mode?: 'single' | 'range'
}

export function DateTimeRangePicker({
  value,
  onChange,
  placeholder,
  className,
  triggerClassName,
  id,
  required,
  disabled = false,
  timeFormat = "HH:mm",
  dateFormat = "MMM d, yyyy h:mm aa",
  mode = "range"
}: DateTimeRangePickerProps) {
  const { t } = useTranslation();
  const [open, setOpen] = React.useState(false)
  const [startDate, setStartDate] = React.useState<Date | null>(null)
  const [endDate, setEndDate] = React.useState<Date | null>(null)

  // Normalize datetime on mount and when value changes
  React.useEffect(() => {
    if (value && (value.includes('T') || value.includes('Z'))) {
      const normalized = normalizeDateTimeValue(value)
      if (normalized !== value) {
        onChange(normalized)
        return
      }
    }
    
    if (value) {
      if (mode === 'single') {
        setStartDate(new Date(value.replace(' ', 'T')))
        setEndDate(null)
      } else {
        const [start, end] = value.split(' - ')
        setStartDate(start ? new Date(start.replace(' ', 'T')) : null)
        setEndDate(end ? new Date(end.replace(' ', 'T')) : null)
      }
    } else {
      setStartDate(null)
      setEndDate(null)
    }
  }, [value, mode])

  const normalizeDateTimeValue = (val: string): string => {
    const normalizeDateTime = (dateTimeStr: string): string => {
      if (!dateTimeStr) return ''
      if (dateTimeStr.includes('Z')) {
        const utcDate = new Date(dateTimeStr)
        const year = utcDate.getFullYear()
        const month = String(utcDate.getMonth() + 1).padStart(2, '0')
        const day = String(utcDate.getDate()).padStart(2, '0')
        const hours = String(utcDate.getHours()).padStart(2, '0')
        const minutes = String(utcDate.getMinutes()).padStart(2, '0')
        return `${year}-${month}-${day} ${hours}:${minutes}`
      }
      return dateTimeStr
    }

    if (mode === 'single') {
      return normalizeDateTime(val)
    } else {
      const [start, end] = val.split(' - ')
      return `${normalizeDateTime(start)} - ${normalizeDateTime(end)}`
    }
  }

  const formatValue = (startDate: Date | null, endDate: Date | null) => {
    const options: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }
    if (mode === 'single') {
      return startDate ? startDate.toLocaleDateString('en-US', options) : ''
    }
    if (!startDate || !endDate) return ''
    return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`
  }

  const formatDate = (date: Date) => {
    const dateStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    const timeStr = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
    return `${dateStr} ${timeStr}`
  }

  const handleStartDateChange = (date: Date | null) => {
    if (date) {
      setStartDate(date)
      if (mode === 'single') {
        onChange(formatDate(date))
        setOpen(false)
      } else if (endDate) {
        onChange(`${formatDate(date)} - ${formatDate(endDate)}`)
      } else {
        onChange(`${formatDate(date)} - `)
      }
    }
  }

  const handleEndDateChange = (date: Date | null) => {
    if (date && startDate) {
      setEndDate(date)
      onChange(`${formatDate(startDate)} - ${formatDate(date)}`)
      setOpen(false)
    }
  }

  return (
    <div className={cn("w-full", className)} onWheel={(e) => {
      // Allow wheel events to bubble up when popover is closed
      if (!open) {
        e.stopPropagation();
      }
    }}>
      <Popover open={disabled ? false : open} onOpenChange={disabled ? undefined : setOpen} modal={false}>
        <PopoverTrigger asChild>
          <Button
            type="button"
            variant="outline"
            disabled={disabled}
            className={cn(
              'w-full justify-start text-left font-normal h-10',
              !value && 'text-muted-foreground',
              disabled && 'opacity-60 cursor-not-allowed bg-muted pointer-events-none',
              triggerClassName
            )}
          >
            <CalendarIcon className="mr-2 h-4 w-4" />
            {value && startDate && (mode === 'single' || endDate) ? formatValue(startDate, endDate) : (placeholder || (mode === 'single' ? t('Select date time') : t('Select date time range')))}
          </Button>
        </PopoverTrigger>
        <PopoverContent 
          className="w-auto p-0" 
          align="start"
          onWheel={(e) => {
            // Allow wheel events to bubble up for modal scrolling
            e.stopPropagation();
          }}
        >
          <div className="datetime-range-wrapper">
            {mode === 'single' ? (
              <div className="p-3">
                <div className="text-sm font-medium mb-2 text-center">{t('Select Date & Time')}</div>
                <DatePicker
                  selected={startDate}
                  onChange={handleStartDateChange}
                  showTimeSelect
                  timeFormat={timeFormat}
                  timeIntervals={15}
                  timeCaption="Time"
                  dateFormat={dateFormat}
                  inline
                />
              </div>
            ) : (
              <div className="flex">
                <div className="p-3 border-r border-border">
                  <div className="text-sm font-medium mb-2 text-center">{t('Start Date & Time')}</div>
                  <DatePicker
                    selected={startDate}
                    onChange={handleStartDateChange}
                    showTimeSelect
                    timeFormat={timeFormat}
                    timeIntervals={15}
                    timeCaption="Time"
                    dateFormat={dateFormat}
                    inline
                    maxDate={endDate || undefined}
                  />
                </div>
                <div className="p-3">
                  <div className="text-sm font-medium mb-2 text-center">{t('End Date & Time')}</div>
                  <DatePicker
                    selected={endDate}
                    onChange={handleEndDateChange}
                    showTimeSelect
                    timeFormat={timeFormat}
                    timeIntervals={15}
                    timeCaption="Time"
                    dateFormat={dateFormat}
                    inline
                    minDate={startDate || undefined}
                  />
                </div>
              </div>
            )}
          </div>
        </PopoverContent>
      </Popover>

      <style>{`
        .datetime-range-wrapper .react-datepicker {
          font-family: inherit;
          border: none;
          background: hsl(var(--background));
          color: hsl(var(--foreground));
        }
        .datetime-range-wrapper .react-datepicker__header {
          background: hsl(var(--background));
          border-bottom: 1px solid hsl(var(--border));
          border-radius: 0;
        }
        .datetime-range-wrapper .react-datepicker__current-month,
        .datetime-range-wrapper .react-datepicker__day-name {
          color: hsl(var(--foreground));
          font-weight: 500;
        }
        .datetime-range-wrapper .react-datepicker__day {
          color: hsl(var(--foreground));
          border-radius: 6px;
        }
        .datetime-range-wrapper .react-datepicker__day:hover {
          background: hsl(var(--accent));
          color: hsl(var(--accent-foreground));
        }
        .datetime-range-wrapper .react-datepicker__day--selected {
          background: hsl(var(--primary));
          color: hsl(var(--primary-foreground));
        }
        .datetime-range-wrapper .react-datepicker__navigation {
          border: none;
          border-radius: 6px;
        }
        .datetime-range-wrapper .react-datepicker__navigation:hover {
          background: hsl(var(--accent));
        }
        .datetime-range-wrapper .react-datepicker__navigation-icon::before {
          border-color: hsl(var(--foreground));
        }
        .datetime-range-wrapper .react-datepicker__day--outside-month {
          color: hsl(var(--muted-foreground));
        }
        .datetime-range-wrapper .react-datepicker__day--disabled {
          color: hsl(var(--muted-foreground));
          opacity: 0.5;
        }
        .datetime-range-wrapper .react-datepicker__time-container {
          background: hsl(var(--background));
          border-left: 1px solid hsl(var(--border));
        }
        .datetime-range-wrapper .react-datepicker__time {
          background: hsl(var(--background));
        }
        .datetime-range-wrapper .react-datepicker__time-box {
          background: hsl(var(--background));
        }
        .datetime-range-wrapper .react-datepicker__time-list-item {
          color: hsl(var(--foreground));
        }
        .datetime-range-wrapper .react-datepicker__time-list-item:hover {
          background: hsl(var(--accent));
        }
        .datetime-range-wrapper .react-datepicker__time-list-item--selected {
          background: hsl(var(--primary));
          color: hsl(var(--primary-foreground));
        }
        .datetime-range-wrapper .react-datepicker__time-name {
          color: hsl(var(--foreground));
        }
      `}</style>
    </div>
  )
}