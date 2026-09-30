"use client"

import * as React from "react"
import { Calendar as CalendarIcon } from "lucide-react"
import ReactDatePicker from "react-datepicker"
import "react-datepicker/dist/react-datepicker.css"
import { useTranslation } from 'react-i18next'

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DatePickerProps {
  value?: string
  onChange: (value: string) => void
  placeholder?: string
  className?: string
  id?: string
  required?: boolean
  maxDate?: Date
  minDate?: Date
  showYearDropdown?: boolean
  showMonthDropdown?: boolean
  style?: React.CSSProperties
  disabled?: boolean
  filterDate?: (date: Date) => boolean
}

export function DatePicker({
  value,
  onChange,
  placeholder,
  className,
  id,
  required,
  maxDate,
  minDate,
  showYearDropdown = true,
  showMonthDropdown = true,
  style,
  disabled = false,
  filterDate
}: DatePickerProps) {
  const { t } = useTranslation();
  const [open, setOpen] = React.useState(false)

  // Normalize the value to YYYY-MM-DD format on mount and when value changes
  React.useEffect(() => {
    if (value && (value.includes('T') || value.includes('Z'))) {
      const normalized = normalizeDate(value)
      if (normalized !== value) {
        onChange(normalized)
      }
    }
  }, [value])

  const normalizeDate = (val: string): string => {
    if (!val) return ''
    
    // If it's a UTC timestamp from backend
    if (val.includes('T') && val.includes('Z')) {
      const utcDate = new Date(val)
      const year = utcDate.getFullYear()
      const month = String(utcDate.getMonth() + 1).padStart(2, '0')
      const day = String(utcDate.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }
    
    // Already in YYYY-MM-DD format
    return val.split('T')[0]
  }

  const parseValue = (val?: string): Date | null => {
    if (!val) return null
    
    // If it's a UTC timestamp from backend (contains T and Z)
    if (val.includes('T') && val.includes('Z')) {
      // Parse the UTC timestamp and convert to local timezone
      const utcDate = new Date(val)
      // Extract local date components (this automatically applies timezone offset)
      const year = utcDate.getFullYear()
      const month = utcDate.getMonth()
      const day = utcDate.getDate()
      // Create date in local timezone at noon
      return new Date(year, month, day, 12, 0, 0)
    }
    
    // For plain YYYY-MM-DD strings
    const dateStr = val.split('T')[0]
    const [year, month, day] = dateStr.split('-').map(Number)
    return new Date(year, month - 1, day, 12, 0, 0)
  }

  const formatValue = (date: Date | null) => {
    if (!date) return ''
    const options: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }
    return date.toLocaleDateString('en-US', options)
  }

  const selectedDate = parseValue(value)

  const handleChange = (date: Date | null) => {
    if (date) {
      // Always use local date parts to avoid timezone issues
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const dateStr = `${year}-${month}-${day}`
      onChange(dateStr)
      setOpen(false)
    } else {
      onChange('')
    }
  }

  return (
    <div className={cn("w-full", className)}>
      {id && <input id={id} type="hidden" value={value || ''} required={required} />}
      <Popover open={open} onOpenChange={setOpen}>
        <PopoverTrigger asChild>
          <Button
            variant="outline"
            className={cn(
              'w-full justify-start text-left font-normal h-10',
              !value && 'text-muted-foreground'
            )}
            style={style}
            disabled={disabled}
          >
            <CalendarIcon className="mr-2 h-4 w-4" />
            {value && selectedDate ? formatValue(selectedDate) : (placeholder || t('Select date'))}
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-auto p-0" align="start">
          <div className="date-picker-wrapper">
            <ReactDatePicker
              selected={selectedDate}
              onChange={handleChange}
              inline
              showPopperArrow={false}
              maxDate={maxDate}
              minDate={minDate}
              showYearDropdown={showYearDropdown}
              showMonthDropdown={showMonthDropdown}
              dropdownMode="select"
              yearDropdownItemNumber={100}
              filterDate={filterDate}
            />
          </div>
        </PopoverContent>
      </Popover>

      <style>{`
        .date-picker-wrapper .react-datepicker {
          font-family: inherit;
          border: none;
          background: hsl(var(--background));
          color: hsl(var(--foreground));
        }
        .date-picker-wrapper .react-datepicker__header {
          background: hsl(var(--background));
          border-bottom: 1px solid hsl(var(--border));
          border-radius: 0;
        }
        .date-picker-wrapper .react-datepicker__current-month,
        .date-picker-wrapper .react-datepicker__day-name {
          color: hsl(var(--foreground));
          font-weight: 500;
        }
        .date-picker-wrapper .react-datepicker__day {
          color: hsl(var(--foreground));
          border-radius: 6px;
        }
        .date-picker-wrapper .react-datepicker__day:hover {
          background: hsl(var(--accent));
          color: hsl(var(--accent-foreground));
        }
        .date-picker-wrapper .react-datepicker__day--selected {
          background: hsl(var(--primary));
          color: hsl(var(--primary-foreground));
        }
        .date-picker-wrapper .react-datepicker__navigation {
          border: none;
          border-radius: 6px;
        }
        .date-picker-wrapper .react-datepicker__navigation:hover {
          background: hsl(var(--accent));
        }
        .date-picker-wrapper .react-datepicker__navigation-icon::before {
          border-color: hsl(var(--foreground));
        }
        .date-picker-wrapper .react-datepicker__day--outside-month {
          color: hsl(var(--muted-foreground));
        }
        .date-picker-wrapper .react-datepicker__day--disabled {
          color: hsl(var(--muted-foreground));
          opacity: 0.5;
        }
        .date-picker-wrapper .react-datepicker__month-container {
          background: hsl(var(--background));
        }
        .date-picker-wrapper .react-datepicker__header__dropdown {
          display: flex;
          gap: 8px;
          justify-content: center;
          padding: 8px 0;
        }
        .date-picker-wrapper .react-datepicker__month-dropdown-container,
        .date-picker-wrapper .react-datepicker__year-dropdown-container {
          margin: 0;
        }
        .date-picker-wrapper .react-datepicker__year-select,
        .date-picker-wrapper .react-datepicker__month-select {
          background: hsl(var(--background));
          color: hsl(var(--foreground));
          border: 1px solid hsl(var(--border));
          border-radius: 6px;
          padding: 6px 32px 6px 12px;
          font-size: 13px;
          font-weight: 500;
          cursor: pointer;
          outline: none;
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 8px center;
          background-size: 12px;
          min-width: 80px;
        }
        .date-picker-wrapper .react-datepicker__month-select {
          min-width: 100px;
        }
        .date-picker-wrapper .react-datepicker__year-select:hover,
        .date-picker-wrapper .react-datepicker__month-select:hover {
          background-color: hsl(var(--accent));
          border-color: hsl(var(--border));
        }
        .date-picker-wrapper .react-datepicker__year-select:focus,
        .date-picker-wrapper .react-datepicker__month-select:focus {
          border-color: hsl(var(--ring));
          box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2);
        }
      `}</style>
    </div>
  )
}
