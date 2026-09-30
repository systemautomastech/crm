import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Megaphone } from 'lucide-react';
import { Announcement } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    announcement: Announcement;
}

export default function View({ announcement }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Megaphone className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Announcement Details')}</DialogTitle>
                        <p className="text-sm text-muted-foreground">{announcement.title}</p>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Category')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{announcement.announcement_category?.announcement_category || '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{announcement.start_date ? formatDate(announcement.start_date) : '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{announcement.end_date ? formatDate(announcement.end_date) : '-'}</p>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Approved By')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{announcement.approved_by?.name || '-'}</p>
                    </div>
                    
                    
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Priority')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                announcement.priority === 'low' ? 'bg-slate-100 text-slate-800' :
                                announcement.priority === 'medium' ? 'bg-blue-100 text-blue-800' :
                                announcement.priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                announcement.priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(announcement.priority ? announcement.priority.charAt(0).toUpperCase() + announcement.priority.slice(1) : '-')}
                            </span>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                        <div className="p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                announcement.status === 'active' ? 'bg-green-100 text-green-700' :
                                announcement.status === 'inactive' ? 'bg-red-100 text-red-700' :
                                announcement.status === 'draft' ? 'bg-blue-100 text-blue-700' :
                                'bg-gray-100 text-gray-800'
                            }`}>
                                {t(announcement.status ? announcement.status.charAt(0).toUpperCase() + announcement.status.slice(1) : 'Draft')}
                            </span>
                        </div>
                    </div>
                </div>
                
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Departments')}</label>
                        <div className="p-1 rounded">
                            <div className="flex flex-wrap gap-1">
                                {announcement.departments.map((dept: any) => (
                                    <span key={dept.id} className="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium">
                                        {dept.department_name || dept.name}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{announcement.description}</p>
                    </div>
            </div>
        </DialogContent>
    );
}