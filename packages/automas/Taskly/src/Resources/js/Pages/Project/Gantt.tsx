import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatDate } from '@/utils/helpers';
import { Calendar, Clock, User, Flag, BarChart3, ChevronLeft, ChevronRight } from "lucide-react";
import { useRef, useMemo, useCallback, useState } from 'react';

interface GanttTask {
    id: number;
    title: string;
    start_date: string | null;
    end_date: string | null;
    duration: string | null;
    priority: string;
    stage: { id: number; name: string; color: string } | null;
    milestone: { id: number; title: string } | null;
    assigned_users: Array<{ id: number; name: string; avatar: string | null }>;
    description: string | null;
}

interface GanttMilestone {
    id: number;
    title: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
}

interface GanttProps {
    project: {
        id: number;
        name: string;
        start_date: string | null;
        end_date: string | null;
    };
    tasks: GanttTask[];
    milestones: GanttMilestone[];
}

export default function Gantt() {
    const { t } = useTranslation();
    const { project, tasks, milestones } = usePage<GanttProps>().props;
    const leftRef = useRef<HTMLDivElement>(null);
    const rightRef = useRef<HTMLDivElement>(null);
    const isScrolling = useRef(false);
    const [currentMonth, setCurrentMonth] = useState(new Date());

    const validTasks = tasks.filter(t => t.start_date && t.end_date);
    
    const previousMonth = () => {
        const newMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1);
        setCurrentMonth(newMonth);
        router.get(route('project.gantt.index', project.id), { 
            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
        }, {
            preserveState: true,
            replace: true
        });
    };

    const nextMonth = () => {
        const newMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1);
        setCurrentMonth(newMonth);
        router.get(route('project.gantt.index', project.id), { 
            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
        }, {
            preserveState: true,
            replace: true
        });
    };

    const { minDate, maxDate, totalDays } = useMemo(() => {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        const min = new Date(firstDay);
        const max = new Date(lastDay);

        const days = Math.max(1, Math.ceil((max.getTime() - min.getTime()) / (1000 * 60 * 60 * 24)));
        return { minDate: min, maxDate: max, totalDays: days };
    }, [currentMonth]);

    // Build days array for current month
    const days = useMemo(() => {
        const d: Date[] = [];
        let current = new Date(minDate);
        while (current <= maxDate) {
            d.push(new Date(current));
            current.setDate(current.getDate() + 1);
        }
        return d;
    }, [minDate, maxDate]);

    const getDaysOffset = useCallback((dateStr: string) => {
        const date = new Date(dateStr);
        return (date.getTime() - minDate.getTime()) / (1000 * 60 * 60 * 24);
    }, [minDate]);

    const getDayPosition = useCallback((dateStr: string) => {
        const offset = getDaysOffset(dateStr);
        return (offset / totalDays) * 100;
    }, [getDaysOffset, totalDays]);

    const getDayWidth = useCallback((startStr: string, endStr: string) => {
        const startOffset = getDaysOffset(startStr);
        const endOffset = getDaysOffset(endStr);
        const span = Math.max(1, endOffset - startOffset + 1);
        return (span / totalDays) * 100;
    }, [getDaysOffset, totalDays]);

    // Scroll sync with RAF
    const syncScroll = useCallback((source: 'left' | 'right') => {
        if (isScrolling.current) return;
        isScrolling.current = true;
        requestAnimationFrame(() => {
            if (source === 'left' && rightRef.current && leftRef.current) {
                rightRef.current.scrollTop = leftRef.current.scrollTop;
            } else if (source === 'right' && leftRef.current && rightRef.current) {
                leftRef.current.scrollTop = rightRef.current.scrollTop;
            }
            isScrolling.current = false;
        });
    }, []);

    const handleLeftScroll = useCallback(() => syncScroll('left'), [syncScroll]);
    const handleRightScroll = useCallback(() => syncScroll('right'), [syncScroll]);

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'High': return 'bg-red-500';
            case 'Medium': return 'bg-amber-500';
            case 'Low': return 'bg-emerald-500';
            default: return 'bg-blue-500';
        }
    };

    const renderTooltip = (task: GanttTask) => (
        <div className="space-y-2.5">
            <p className="font-semibold text-sm">{task.title}</p>
            {task.description && <p className="text-xs text-gray-500 line-clamp-2">{task.description}</p>}
            <div className="flex items-center gap-2 text-xs text-gray-600">
                <Calendar className="h-3.5 w-3.5 text-gray-400" />
                <span>{formatDate(task.start_date!)} – {formatDate(task.end_date!)}</span>
            </div>
            <div className="flex items-center gap-2">
                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold text-white ${getPriorityColor(task.priority)}`}>
                    {task.priority}
                </span>
                {task.stage && (
                    <Badge variant="outline" className="text-[10px] h-5" style={{ borderColor: task.stage.color, color: task.stage.color }}>
                        {task.stage.name}
                    </Badge>
                )}
            </div>
            {task.assigned_users.length > 0 && (
                <div className="flex items-center gap-1.5 text-xs text-gray-500 pt-1 border-t">
                    <User className="h-3.5 w-3.5 text-gray-400" />
                    <span>{task.assigned_users.map(u => u.name).join(', ')}</span>
                </div>
            )}
            {task.milestone && (
                <div className="text-[10px] text-gray-400">Milestone: {task.milestone.title}</div>
            )}
        </div>
    );

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Projects'), url: route('project.index') },
                { label: project.name, url: route('project.show', project.id) },
                { label: t('Gantt Chart') }
            ]}
            pageTitle={`${project.name} – ${t('Gantt Chart')}`}
        >
            <Head title={`${project.name} – ${t('Gantt Chart')}`} />

            <div className="space-y-5">
                {/* Summary Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-blue-50 rounded-xl">
                                <BarChart3 className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Total Tasks')}</p>
                                <p className="text-sm font-bold text-gray-800">{tasks.length}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-emerald-50 rounded-xl">
                                <Calendar className="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Project Start')}</p>
                                <p className="text-sm font-semibold text-gray-800">{project.start_date ? formatDate(project.start_date) : '–'}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-orange-50 rounded-xl">
                                <Clock className="h-5 w-5 text-orange-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Project End')}</p>
                                <p className="text-sm font-semibold text-gray-800">{project.end_date ? formatDate(project.end_date) : '–'}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-purple-50 rounded-xl">
                                <Flag className="h-5 w-5 text-purple-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Milestones')}</p>
                                <p className="text-sm font-bold text-gray-800">{milestones.length}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Gantt Chart */}
                <Card className="overflow-hidden border shadow-sm">
                    <CardHeader className="border-b bg-gray-50/80 flex flex-row items-center justify-between py-3 px-5">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-blue-100 rounded-xl">
                                <BarChart3 className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle className="text-base font-bold">{t('Gantt Chart')}</CardTitle>
                                <p className="text-xs text-gray-500 font-medium">{project.name}</p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <div className="w-44">
                                <Select
                                    value={currentMonth.getMonth().toString()}
                                    onValueChange={(value) => {
                                        const monthIndex = parseInt(value);
                                        const newMonth = new Date(currentMonth.getFullYear(), monthIndex);
                                        setCurrentMonth(newMonth);
                                        router.get(route('project.gantt.index', project.id), {
                                            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
                                        }, {
                                            preserveState: true,
                                            replace: true
                                        });
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select Month')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Array.from({ length: 12 }, (_, i) => (
                                            <SelectItem key={i} value={i.toString()}>
                                                {new Date(2024, i, 1).toLocaleString('default', { month: 'long' })}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="w-28">
                                <Select
                                    value={currentMonth.getFullYear().toString()}
                                    onValueChange={(value) => {
                                        const year = parseInt(value);
                                        const newMonth = new Date(year, currentMonth.getMonth());
                                        setCurrentMonth(newMonth);
                                        router.get(route('project.gantt.index', project.id), {
                                            month: `${newMonth.getFullYear()}-${String(newMonth.getMonth() + 1).padStart(2, '0')}-01`
                                        }, {
                                            preserveState: true,
                                            replace: true
                                        });
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select Year')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Array.from({ length: 11 }, (_, i) => {
                                            const year = 2017 + i;
                                            return (
                                                <SelectItem key={year} value={year.toString()}>
                                                    {year}
                                                </SelectItem>
                                            );
                                        })}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button variant="outline" size="sm" onClick={previousMonth}>
                                <ChevronLeft className="h-4 w-4" />
                            </Button>
                            <Button variant="outline" size="sm" onClick={nextMonth}>
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        {validTasks.length === 0 ? (
                            <div className="text-center py-16 text-gray-400">
                                <Calendar className="h-14 w-14 mx-auto mb-4 opacity-30" />
                                <p className="text-lg font-semibold text-gray-500">{t('No scheduled tasks')}</p>
                                <p className="text-sm mt-1">{t('Tasks with start and end dates will appear here.')}</p>
                            </div>
                        ) : (
                            <div className="flex overflow-auto border rounded-lg max-h-[600px]">
                                {/* ─── Left Panel: Task List ─── */}
                                <div className="w-72 shrink-0 border-r bg-white flex flex-col shadow-[2px_0_8px_rgba(0,0,0,0.03)] z-20">
                                    <div className="h-12 border-b bg-gray-50/80 flex items-center px-4">
                                        <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{t('Task Name')}</span>
                                    </div>
                                    <div
                                        ref={leftRef}
                                        onScroll={handleLeftScroll}
                                        className="flex-1 overflow-y-auto overflow-x-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                                    >
                                        {validTasks.map(task => (
                                            <div
                                                key={`tl-${task.id}`}
                                                className="h-12 border-b border-gray-100 flex items-center px-4 gap-3 hover:bg-blue-50/40 transition-colors cursor-pointer group"
                                            >
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-semibold text-gray-800 truncate group-hover:text-blue-700 transition-colors">
                                                        {task.title}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* ─── Right Panel: Current Month Timeline ─── */}
                                <div
                                    ref={rightRef}
                                    onScroll={handleRightScroll}
                                    className="flex-1 bg-white overflow-y-auto overflow-x-hidden relative [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                                >
                                    {/* Day header */}
                                    <div className="flex h-12 border-b bg-gray-50/80 sticky top-0 z-30">
                                            {days.map((day, i) => {
                                                const isWeekend = day.getDay() === 0 || day.getDay() === 6;
                                                const isToday = day.toDateString() === new Date().toDateString();
                                                return (
                                                    <div
                                                        key={i}
                                                        className={`flex-1 border-r flex flex-col items-center justify-center min-w-0 ${isWeekend ? 'bg-gray-50/60' : ''} ${isToday ? 'bg-blue-50/60' : ''}`}
                                                    >
                                                        <span className={`text-xs font-bold ${isToday ? 'text-blue-600' : 'text-gray-700'}`}>
                                                            {day.getDate()}
                                                        </span>
                                                        <span className={`text-[9px] uppercase font-semibold ${isToday ? 'text-blue-500' : 'text-gray-400'}`}>
                                                            {day.toLocaleDateString('en-US', { weekday: 'narrow' })}
                                                        </span>
                                                    </div>
                                                );
                                            })}
                                        </div>

                                    <div className="relative">
                                        {/* Weekend columns background */}
                                        <div className="absolute inset-0 flex z-0 pointer-events-none">
                                            {days.map((day, i) => {
                                                const isWeekend = day.getDay() === 0 || day.getDay() === 6;
                                                return (
                                                    <div
                                                        key={i}
                                                        className={`flex-1 border-r ${isWeekend ? 'bg-gray-50/40' : 'border-gray-100'}`}
                                                    />
                                                );
                                            })}
                                        </div>

                                        {/* Today line */}
                                        {(() => {
                                            const today = new Date();
                                            if (today < minDate || today > maxDate) return null;
                                            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                                            const todayOffset = getDaysOffset(todayStr);
                                            if (todayOffset < 0 || todayOffset >= totalDays) return null;
                                            const colWidth = 100 / totalDays;
                                            return (
                                                <div
                                                    className="absolute top-0 bottom-0 z-20 pointer-events-none flex flex-col items-center"
                                                    style={{ left: `${todayOffset * colWidth}%` }}
                                                >
                                                    <div className="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-b shadow-sm whitespace-nowrap">
                                                        {t('Today')}
                                                    </div>
                                                    <div className="w-px flex-1 bg-red-400/40 border-l border-dashed border-red-400" />
                                                </div>
                                            );
                                        })()}

                                        {/* Task bars */}
                                        {validTasks.map(task => {
                                            const leftPercent = getDayPosition(task.start_date!);
                                            const widthPercent = getDayWidth(task.start_date!, task.end_date!);
                                            const barColor = task.stage?.color || '#3b82f6';

                                            return (
                                                <div
                                                    className="relative h-12 border-b border-gray-100 hover:bg-blue-50/30 transition-colors group"
                                                >
                                                    {/* Hover highlight */}
                                                    <div className="absolute inset-0 group-hover:bg-blue-50/30 transition-colors" />

                                                    <TooltipProvider>
                                                        <Tooltip delayDuration={0}>
                                                            <TooltipTrigger asChild>
                                                                <div
                                                                    className="absolute top-2.5 bottom-2.5 rounded-full shadow-sm cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center px-3 overflow-hidden border border-white/40 z-10"
                                                                    style={{
                                                                        left: `${leftPercent}%`,
                                                                        width: `${widthPercent}%`,
                                                                        backgroundColor: `${barColor}15`,
                                                                        borderLeft: `3px solid ${barColor}`,
                                                                        minWidth: '3px',
                                                                    }}
                                                                >
                                                                    {widthPercent > 4 && (
                                                                        <span className="text-xs font-bold truncate" style={{ color: barColor }}>
                                                                            {task.title}
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            </TooltipTrigger>
                                                            <TooltipContent side="top" className="max-w-xs p-3">
                                                                {renderTooltip(task)}
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
