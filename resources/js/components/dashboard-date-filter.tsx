import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Calendar as CalendarIcon, ChevronDown, Check, Filter } from 'lucide-react';
import { Button } from "@/components/ui/button";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import { DateRangePicker } from "@/components/ui/date-range-picker";

export type FilterPeriod = 'this_month' | 'prev_month' | 'last_3_months' | 'last_6_months' | 'custom';

interface DashboardDateFilterProps {
    className?: string;
    onFilterChange?: (period: FilterPeriod, startDate?: string, endDate?: string) => void;
}

export default function DashboardDateFilter({ className = '', onFilterChange }: DashboardDateFilterProps) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [showCustomRange, setShowCustomRange] = useState(false);

    // Read initial filter from URL params if present
    const searchParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
    const initialPeriod = (searchParams.get('period') as FilterPeriod) || 'this_month';
    const initialStartDate = searchParams.get('start_date') || '';
    const initialEndDate = searchParams.get('end_date') || '';

    const [selectedPeriod, setSelectedPeriod] = useState<FilterPeriod>(initialPeriod);
    const [startDate, setStartDate] = useState(initialStartDate);
    const [endDate, setEndDate] = useState(initialEndDate);

    useEffect(() => {
        if (selectedPeriod === 'custom') {
            setShowCustomRange(true);
        }
    }, [selectedPeriod]);

    const periodLabels: Record<FilterPeriod, string> = {
        this_month: t('This Month'),
        prev_month: t('Previous Month'),
        last_3_months: t('Last 3 Months'),
        last_6_months: t('Last 6 Months'),
        custom: t('Custom Range'),
    };

    const handleSelectPeriod = (period: FilterPeriod) => {
        setSelectedPeriod(period);

        if (period === 'custom') {
            setShowCustomRange(true);
            return;
        }

        setShowCustomRange(false);
        setOpen(false);

        if (onFilterChange) {
            onFilterChange(period);
        } else {
            // Default behavior: Inertia reload with query params
            router.get(
                window.location.pathname,
                { period },
                { preserveState: true, preserveScroll: true, replace: true }
            );
        }
    };

    const handleDateRangePickerChange = (rangeValue: string) => {
        const [start, end] = rangeValue.split(' - ');
        setStartDate(start || '');
        setEndDate(end || '');
    };

    const handleApplyCustomRange = (e: React.FormEvent) => {
        e.preventDefault();
        if (!startDate || !endDate) return;

        setOpen(false);

        if (onFilterChange) {
            onFilterChange('custom', startDate, endDate);
        } else {
            router.get(
                window.location.pathname,
                { period: 'custom', start_date: startDate, end_date: endDate },
                { preserveState: true, preserveScroll: true, replace: true }
            );
        }
    };

    const getActiveButtonLabel = () => {
        if (selectedPeriod === 'custom' && startDate && endDate) {
            return `${startDate} - ${endDate}`;
        }
        return periodLabels[selectedPeriod] || t('This Month');
    };

    const dateRangePickerValue = startDate && endDate ? `${startDate} - ${endDate}` : (startDate ? `${startDate} - ` : '');

    return (
        <div className={`relative inline-block ${className}`}>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-9 px-3 bg-white border-slate-200 hover:bg-slate-50 hover:text-slate-900 font-bold text-xs text-slate-700 shadow-2xs rounded-lg flex items-center gap-2"
                    >
                        <Filter className="h-3.5 w-3.5 text-blue-600" />
                        <span>{getActiveButtonLabel()}</span>
                        <ChevronDown className={`h-3.5 w-3.5 text-slate-400 transition-transform ${open ? 'rotate-180' : ''}`} />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-72 p-2" align="end">
                    <div className="space-y-1">
                        <div className="px-2 py-1 text-[11px] font-black uppercase text-slate-400 tracking-wider">
                            {t('Filter Period')}
                        </div>

                        {(['this_month', 'prev_month', 'last_3_months', 'last_6_months'] as FilterPeriod[]).map((periodKey) => {
                            const isSelected = selectedPeriod === periodKey;
                            return (
                                <button
                                    key={periodKey}
                                    type="button"
                                    onClick={() => handleSelectPeriod(periodKey)}
                                    className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs font-semibold transition-colors ${
                                        isSelected
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                                    }`}
                                >
                                    <div className="flex items-center gap-2">
                                        <CalendarIcon className="h-3.5 w-3.5 text-slate-400" />
                                        <span>{periodLabels[periodKey]}</span>
                                    </div>
                                    {isSelected && <Check className="h-3.5 w-3.5 text-blue-600" />}
                                </button>
                            );
                        })}

                        <div className="my-1 border-t border-slate-100" />

                        {/* Custom Date Filter Option */}
                        <button
                            type="button"
                            onClick={() => {
                                setSelectedPeriod('custom');
                                setShowCustomRange(true);
                            }}
                            className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs font-semibold transition-colors ${
                                selectedPeriod === 'custom'
                                    ? 'bg-blue-50 text-blue-700'
                                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                            }`}
                        >
                            <div className="flex items-center gap-2">
                                <CalendarIcon className="h-3.5 w-3.5 text-slate-400" />
                                <span>{t('Custom Date Range')}</span>
                            </div>
                            {selectedPeriod === 'custom' && <Check className="h-3.5 w-3.5 text-blue-600" />}
                        </button>

                        {/* Custom Date Range Picker form */}
                        {showCustomRange && (
                            <form onSubmit={handleApplyCustomRange} className="mt-2 p-2 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                <div>
                                    <label className="text-[10px] font-bold text-slate-500 block mb-1">{t('Select Date Range')}</label>
                                    <DateRangePicker
                                        value={dateRangePickerValue}
                                        onChange={handleDateRangePickerChange}
                                        placeholder={t('Pick start and end date')}
                                        className="w-full text-xs"
                                    />
                                </div>
                                <Button type="submit" size="sm" className="w-full h-7 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white mt-1">
                                    {t('Apply Filter')}
                                </Button>
                            </form>
                        )}
                    </div>
                </PopoverContent>
            </Popover>
        </div>
    );
}
