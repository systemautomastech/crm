import React, { useState, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, Calendar as CalendarIcon, ArrowUpRight } from 'lucide-react';

export interface CalendarEventItem {
    id?: string | number;
    date?: string; // 'YYYY-MM-DD'
    startDate?: string;
    dateStr?: string;
    title?: string;
    number?: string;
    type?: string;
    typeLabel?: string;
    customer?: string;
    so_number?: string;
    subtitle?: string;
    time?: string;
    status?: string;
    url?: string;
    color?: string;
    [key: string]: any;
}

export interface CustomEventCalendarProps {
    title?: string;
    description?: string;
    events: CalendarEventItem[];
    headerIcon?: React.ReactNode;
    dotColor?: string;
    selectedDateTitlePrefix?: string;
    eventsSummaryText?: string;
    emptyText?: string;
    renderStatusBadge?: (status: string) => React.ReactNode;
    renderEventItem?: (event: CalendarEventItem) => React.ReactNode;
    className?: string;
}

export default function CustomEventCalendar({
    title = 'Calendar',
    description,
    events = [],
    headerIcon,
    dotColor = 'bg-emerald-500',
    selectedDateTitlePrefix,
    eventsSummaryText,
    emptyText,
    renderStatusBadge,
    renderEventItem,
    className = '',
}: CustomEventCalendarProps) {
    const { t } = useTranslation();

    const [currentMonth, setCurrentMonth] = useState<Date>(new Date());
    const todayStr = useMemo(() => new Date().toISOString().split('T')[0], []);
    const [selectedDate, setSelectedDate] = useState<string>(todayStr);

    // Generate calendar days for 5-6 week grid
    const calendarDays = useMemo(() => {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();

        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);

        const startingDayOfWeek = firstDayOfMonth.getDay(); // 0 = Sun
        const daysInMonth = lastDayOfMonth.getDate();

        const days: { dateStr: string; dayNum: number; isCurrentMonth: boolean }[] = [];

        // Previous month padding
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const d = new Date(year, month - 1, prevMonthLastDay - i);
            days.push({
                dateStr: d.toISOString().split('T')[0],
                dayNum: prevMonthLastDay - i,
                isCurrentMonth: false,
            });
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const d = new Date(year, month, day);
            days.push({
                dateStr: d.toISOString().split('T')[0],
                dayNum: day,
                isCurrentMonth: true,
            });
        }

        // Next month padding (total cells divisible by 7)
        const remainingCells = (7 - (days.length % 7)) % 7;
        for (let day = 1; day <= remainingCells; day++) {
            const d = new Date(year, month + 1, day);
            days.push({
                dateStr: d.toISOString().split('T')[0],
                dayNum: day,
                isCurrentMonth: false,
            });
        }

        return days;
    }, [currentMonth]);

    // Group events by YYYY-MM-DD
    const eventsByDate = useMemo(() => {
        const map = new Map<string, CalendarEventItem[]>();
        events.forEach((evt) => {
            const rawDate = evt.date || evt.startDate || evt.dateStr;
            if (!rawDate) return;
            const dStr = rawDate.split('T')[0];
            const existing = map.get(dStr) || [];
            existing.push(evt);
            map.set(dStr, existing);
        });
        return map;
    }, [events]);

    const selectedDateEvents = useMemo(() => {
        return eventsByDate.get(selectedDate) || [];
    }, [eventsByDate, selectedDate]);

    const nextMonth = () => {
        setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1));
    };

    const prevMonth = () => {
        setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1));
    };

    const monthName = currentMonth.toLocaleString('default', { month: 'long', year: 'numeric' });

    const defaultStatusBadge = (status: string) => {
        const s = status.toLowerCase();
        if (['delivered', 'completed', 'won', 'confirmed'].includes(s)) {
            return (
                <Badge className="bg-emerald-500/15 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                    {t(status.charAt(0).toUpperCase() + status.slice(1))}
                </Badge>
            );
        }
        if (['partial', 'partially_delivered', 'in_progress', 'pending'].includes(s)) {
            return (
                <Badge className="bg-amber-500/15 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                    {t(s === 'partially_delivered' ? 'Partially Delivered' : status.charAt(0).toUpperCase() + status.slice(1))}
                </Badge>
            );
        }
        if (['overdue', 'cancelled'].includes(s)) {
            return (
                <Badge variant="destructive">
                    {t(status.charAt(0).toUpperCase() + status.slice(1))}
                </Badge>
            );
        }
        return (
            <Badge variant="outline" className="text-muted-foreground">
                {t(status || 'Pending')}
            </Badge>
        );
    };

    return (
        <Card className={className}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                <div className="flex items-center gap-2.5">
                    {headerIcon}
                    <div>
                        <CardTitle className="text-base font-bold text-foreground">
                            {t(title)}
                        </CardTitle>
                        {description && (
                            <CardDescription className="text-xs text-muted-foreground mt-0.5">
                                {t(description)}
                            </CardDescription>
                        )}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <span className="text-sm font-semibold text-foreground">
                        {monthName}
                    </span>
                    <Button variant="outline" size="icon" className="h-8 w-8" onClick={prevMonth}>
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <Button variant="outline" size="icon" className="h-8 w-8" onClick={nextMonth}>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Calendar Grid */}
                    <div className="lg:col-span-2 space-y-2">
                        {/* Weekday Headers */}
                        <div className="grid grid-cols-7 text-center text-xs font-bold text-muted-foreground">
                            <span>{t('Sun')}</span>
                            <span>{t('Mon')}</span>
                            <span>{t('Tue')}</span>
                            <span>{t('Wed')}</span>
                            <span>{t('Thu')}</span>
                            <span>{t('Fri')}</span>
                            <span>{t('Sat')}</span>
                        </div>

                        {/* Days Grid */}
                        <div className="grid grid-cols-7 gap-1">
                            {calendarDays.map((cell, idx) => {
                                const dayEvents = eventsByDate.get(cell.dateStr) || [];
                                const isSelected = selectedDate === cell.dateStr;
                                const isToday = cell.dateStr === todayStr;

                                return (
                                    <button
                                        key={idx}
                                        onClick={() => setSelectedDate(cell.dateStr)}
                                        className={`flex min-h-[64px] flex-col justify-between rounded-lg border p-1.5 transition-all text-left ${
                                            isSelected
                                                ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                                : isToday
                                                ? 'border-blue-400 bg-blue-500/5'
                                                : cell.isCurrentMonth
                                                ? 'bg-card hover:bg-accent/50'
                                                : 'bg-muted/20 opacity-40'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className={`text-xs font-semibold ${isToday ? 'rounded-full bg-primary text-primary-foreground px-1.5 py-0.5' : ''}`}>
                                                {cell.dayNum}
                                            </span>
                                            {dayEvents.length > 0 && (
                                                <span className={`h-2 w-2 rounded-full ${dotColor}`} />
                                            )}
                                        </div>
                                        {dayEvents.length > 0 && (
                                            <div className="space-y-0.5 mt-1">
                                                <span className="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 truncate">
                                                    {dayEvents.length} {t('event(s)')}
                                                </span>
                                            </div>
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Selected Date Details Panel */}
                    <div className="rounded-lg border p-4 bg-muted/10 space-y-4">
                        <div>
                            <h3 className="font-bold text-sm text-foreground">
                                {selectedDateTitlePrefix || t('Events on')} {selectedDate}
                            </h3>
                            <p className="text-xs text-muted-foreground mt-0.5">
                                {selectedDateEvents.length} {eventsSummaryText || t('scheduled events')}
                            </p>
                        </div>

                        <div className="space-y-3 max-h-[320px] overflow-y-auto pr-1">
                            {selectedDateEvents.length > 0 ? (
                                selectedDateEvents.map((evt, idx) => {
                                    if (renderEventItem) {
                                        return renderEventItem(evt);
                                    }

                                    const typeVariant = evt.type === 'delivery_challan' ? 'default' : evt.badgeVariant || 'outline';
                                    const displayBadgeText = evt.typeLabel || (
                                        evt.type === 'delivery_challan'
                                            ? t('Challan')
                                            : evt.type === 'expected_so'
                                            ? t('Expected SO')
                                            : evt.type || t('Event')
                                    );

                                    return (
                                        <div key={evt.id || idx} className="rounded-lg border bg-card p-3 space-y-2 text-xs">
                                            <div className="flex items-center justify-between">
                                                <Badge variant={typeVariant}>
                                                    {displayBadgeText}
                                                </Badge>
                                                {evt.url ? (
                                                    <Link href={evt.url} className="text-primary font-medium hover:underline inline-flex items-center">
                                                        {evt.number || evt.title || t('View')} <ArrowUpRight className="ml-0.5 h-3 w-3" />
                                                    </Link>
                                                ) : (
                                                    <span className="font-semibold text-foreground">
                                                        {evt.number || evt.time || ''}
                                                    </span>
                                                )}
                                            </div>
                                            <div>
                                                <p className="font-semibold text-foreground">{evt.customer || evt.title}</p>
                                                {(evt.so_number || evt.subtitle) && (
                                                    <p className="text-muted-foreground mt-0.5">
                                                        {evt.so_number ? `${t('SO Ref:')} ${evt.so_number}` : evt.subtitle}
                                                    </p>
                                                )}
                                            </div>
                                            {evt.status && (
                                                <div className="flex items-center justify-between pt-1 border-t">
                                                    <span className="text-[11px] text-muted-foreground">{t('Status:')}</span>
                                                    {renderStatusBadge ? renderStatusBadge(evt.status) : defaultStatusBadge(evt.status)}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="flex h-40 flex-col items-center justify-center text-center text-xs text-muted-foreground">
                                    <CalendarIcon className="h-8 w-8 mb-2 opacity-40" />
                                    {emptyText || t('No delivery events scheduled on this date.')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
