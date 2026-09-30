import { useTranslation } from 'react-i18next';
import {
    CheckSquare,
    Mail,
    Phone,
    Users,
    MessageSquare,
    Upload,
    Activity as ActivityIcon,
    GitBranch,
    FileText,
    Tag,
    Package,
    Globe,
    Briefcase,
    UserPlus,
    ArrowRight,
    User as UserIcon,
    Calendar
} from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { formatDateTime } from '@/utils/helpers';
import { Lead } from '../types';

interface ActivityProps {
    lead: Lead;
}

export default function Activity({ lead }: ActivityProps) {
    const { t } = useTranslation();

    const getActivityIcon = (logType: string = '', remark: string = '') => {
        const text = `${logType} ${remark}`.toLowerCase();
        if (text.includes('task')) return <CheckSquare className="h-4 w-4 text-emerald-600" />;
        if (text.includes('email')) return <Mail className="h-4 w-4 text-blue-600" />;
        if (text.includes('call')) return <Phone className="h-4 w-4 text-purple-600" />;
        if (text.includes('user') || text.includes('assign')) return <Users className="h-4 w-4 text-indigo-600" />;
        if (text.includes('discussion') || text.includes('comment')) return <MessageSquare className="h-4 w-4 text-amber-600" />;
        if (text.includes('file') || text.includes('upload')) return <Upload className="h-4 w-4 text-cyan-600" />;
        if (text.includes('move') || text.includes('stage') || text.includes('pipeline')) return <GitBranch className="h-4 w-4 text-orange-600" />;
        if (text.includes('note')) return <FileText className="h-4 w-4 text-yellow-600" />;
        if (text.includes('label') || text.includes('tag')) return <Tag className="h-4 w-4 text-pink-600" />;
        if (text.includes('product')) return <Package className="h-4 w-4 text-teal-600" />;
        if (text.includes('source')) return <Globe className="h-4 w-4 text-sky-600" />;
        if (text.includes('deal') || text.includes('convert')) return <Briefcase className="h-4 w-4 text-emerald-700" />;
        if (text.includes('create lead') || text.includes('create')) return <UserPlus className="h-4 w-4 text-primary" />;
        return <ActivityIcon className="h-4 w-4 text-primary" />;
    };

    const getActivityBg = (logType: string = '', remark: string = '') => {
        const text = `${logType} ${remark}`.toLowerCase();
        if (text.includes('task')) return 'bg-emerald-50 border-emerald-100';
        if (text.includes('email')) return 'bg-blue-50 border-blue-100';
        if (text.includes('call')) return 'bg-purple-50 border-purple-100';
        if (text.includes('user') || text.includes('assign')) return 'bg-indigo-50 border-indigo-100';
        if (text.includes('discussion') || text.includes('comment')) return 'bg-amber-50 border-amber-100';
        if (text.includes('file') || text.includes('upload')) return 'bg-cyan-50 border-cyan-100';
        if (text.includes('move') || text.includes('stage') || text.includes('pipeline')) return 'bg-orange-50 border-orange-100';
        if (text.includes('note')) return 'bg-yellow-50 border-yellow-100';
        if (text.includes('label') || text.includes('tag')) return 'bg-pink-50 border-pink-100';
        if (text.includes('product')) return 'bg-teal-50 border-teal-100';
        if (text.includes('source')) return 'bg-sky-50 border-sky-100';
        if (text.includes('deal') || text.includes('convert')) return 'bg-emerald-50 border-emerald-100';
        return 'bg-primary/5 border-primary/10';
    };

    return (
        <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {lead.activities && lead.activities.length > 0 ? (
                    lead.activities.map((activity: any, index: number) => {
                        let parsed: any = {};
                        try {
                            parsed = JSON.parse(activity.remark || '{}');
                        } catch {
                            parsed = { title: activity.remark };
                        }

                        const userName = activity.user?.name || parsed.user_name || '';
                        const title = parsed.title || activity.log_type || t('Activity');
                        const changes = Array.isArray(parsed.changes) ? parsed.changes : [];
                        const oldStatus = parsed.old_status;
                        const newStatus = parsed.new_status;

                        return (
                            <div
                                key={index}
                                className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow transition-shadow flex flex-col justify-between"
                            >
                                <div>
                                    {/* Top Row: Icon, Title, and Log Type */}
                                    <div className="flex items-start gap-3">
                                        <div className={`w-9 h-9 rounded-lg border flex items-center justify-center flex-shrink-0 ${getActivityBg(activity.log_type, activity.remark)}`}>
                                            {getActivityIcon(activity.log_type, activity.remark)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                    {t(activity.log_type || 'Activity')}
                                                </span>
                                            </div>
                                            <h4 className="text-sm font-medium text-gray-900 mt-1 leading-snug break-words">
                                                {/<[a-z][\s\S]*>/i.test(title) ? (
                                                    <span className="[&_p]:inline [&_p]:m-0" dangerouslySetInnerHTML={{ __html: title }} />
                                                ) : (
                                                    title
                                                )}
                                            </h4>
                                        </div>
                                    </div>

                                    {/* Stage Movement Visual */}
                                    {oldStatus && newStatus && (
                                        <div className="mt-3 p-2 bg-gray-50 rounded-lg border border-gray-100 flex items-center gap-2 text-xs">
                                            <span className="px-2 py-0.5 rounded bg-white text-gray-600 font-medium border border-gray-200">
                                                {oldStatus}
                                            </span>
                                            <ArrowRight className="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                            <span className="px-2 py-0.5 rounded bg-primary/10 text-primary font-semibold border border-primary/20">
                                                {newStatus}
                                            </span>
                                        </div>
                                    )}

                                    {/* Field Changes Breakdown */}
                                    {changes.length > 0 && (
                                        <div className="mt-3 p-2.5 bg-gray-50/80 rounded-lg border border-gray-100 space-y-1.5">
                                            <p className="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                                                {t('Changes')}
                                            </p>
                                            <div className="space-y-1">
                                                {changes.map((change: any, idx: number) => {
                                                    const stripHtml = (html: string) => String(html || '').replace(/<[^>]*>/g, '').trim();

                                                    const renderValue = (val: any, isOld: boolean) => {
                                                        if (val === null || val === undefined || val === '' || val === '-') {
                                                            return <span className={isOld ? "text-gray-400 line-through" : "font-medium text-emerald-700"}>-</span>;
                                                        }
                                                        const strVal = String(val);
                                                        const plainText = stripHtml(strVal);
                                                        const hasHtml = /<[a-z][\s\S]*>/i.test(strVal);

                                                        if (hasHtml) {
                                                            return (
                                                                <span
                                                                    className={`truncate max-w-[130px] inline-block align-bottom [&_p]:inline [&_p]:m-0 [&_div]:inline [&_div]:m-0 [&_ul]:inline [&_ol]:inline ${
                                                                        isOld ? "text-gray-400 line-through" : "font-medium text-emerald-700"
                                                                    }`}
                                                                    title={plainText}
                                                                    dangerouslySetInnerHTML={{ __html: strVal }}
                                                                />
                                                            );
                                                        }

                                                        return (
                                                            <span
                                                                className={`truncate max-w-[130px] inline-block ${
                                                                    isOld ? "text-gray-400 line-through" : "font-medium text-emerald-700"
                                                                }`}
                                                                title={strVal}
                                                            >
                                                                {strVal}
                                                            </span>
                                                        );
                                                    };

                                                    return (
                                                        <div key={idx} className="text-xs flex items-start gap-1.5 py-0.5 border-b border-gray-200/50 last:border-0">
                                                            <span className="text-gray-500 font-medium min-w-[90px] flex-shrink-0">
                                                                {change.field}:
                                                            </span>
                                                            <div className="flex items-center gap-1.5 flex-wrap min-w-0">
                                                                {renderValue(change.old, true)}
                                                                <ArrowRight className="h-3 w-3 text-gray-400 flex-shrink-0" />
                                                                {renderValue(change.new, false)}
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Footer: Who changed and When */}
                                <div className="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                    <div className="flex items-center gap-1.5 truncate mr-2" title={userName ? `${t('By')} ${userName}` : ''}>
                                        <UserIcon className="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                        <span className="truncate font-medium text-gray-700">
                                            {userName ? userName : t('System')}
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-1 flex-shrink-0 text-gray-400">
                                        <Calendar className="h-3 w-3" />
                                        <span>{formatDateTime(activity.created_at)}</span>
                                    </div>
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <div className="col-span-full flex items-center justify-center min-h-[400px]">
                        <NoRecordsFound
                            icon={ActivityIcon}
                            title={t('No Activities found')}
                            description={t('Activities will appear here when actions are performed.')}
                            className="h-auto"
                        />
                    </div>
                )}
            </div>
        </div>
    );
}