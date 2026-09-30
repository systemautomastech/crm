import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Search, FilterX, ArrowLeft, Target, TrendingUp, CheckCircle2, Users } from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { MultiSelectEnhanced } from '@/components/ui/multi-select-enhanced';

interface FilterOptions {
    pipelines: { value: number; label: string }[];
    stages: { value: number; label: string; pipeline_id: number }[];
    users: { value: number; label: string }[];
    sources: { value: number; label: string }[];
    labels: { value: number; label: string }[];
}

interface LeadRecord {
    id: number;
    lead_name: string;
    subject: string;
    email: string;
    phone: string;
    pipeline_name: string;
    stage_name: string;
    assigned_to: string;
    created_by_name: string;
    status: string;
    follow_up_date: string;
    created_at: string;
    age_days: number;
    task_count: number;
    call_count: number;
    email_count: number;
}

interface Summary {
    total_leads: number;
    active_leads: number;
    converted_leads: number;
    inactive_leads: number;
    conversion_rate: number;
    average_age_days: number;
}

const LeadReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [reportData, setReportData] = useState<LeadRecord[] | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [viewMode, setViewMode] = useState<'chart' | 'table'>('chart');
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);

    const [selectedPipeline, setSelectedPipeline] = useState<string>('all');
    const [selectedStage, setSelectedStage] = useState<string>('all');
    const [selectedUser, setSelectedUser] = useState<string>('all');
    const [selectedSource, setSelectedSource] = useState<string[]>([]);
    const [selectedLabel, setSelectedLabel] = useState<string[]>([]);
    const [selectedStatus, setSelectedStatus] = useState<string[]>([]);
    const [dateFrom, setDateFrom] = useState('');
    const [dateTo, setDateTo] = useState('');

    useEffect(() => {
        fetchFilterOptions();
        loadReport();
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const res = await axios.get(route('smart-reports.lead.filter-options'));
            if (res.data.success) setFilterOptions(res.data.data);
        } catch (error: any) {
            toast.error(error.response?.data?.message || t('Failed to load filter options'));
        }
    };

    const loadReport = async (overrides: Record<string, any> = {}) => {
        setIsLoading(true);
        try {
            const payload = {
                export_type: 'view',
                pipeline_ids: selectedPipeline !== 'all' ? [parseInt(selectedPipeline)] : null,
                stage_ids: selectedStage !== 'all' ? [parseInt(selectedStage)] : null,
                user_ids: selectedUser !== 'all' ? [parseInt(selectedUser)] : null,
                source_ids: selectedSource.length ? selectedSource.map(Number) : null,
                label_ids: selectedLabel.length ? selectedLabel.map(Number) : null,
                status: selectedStatus.length ? selectedStatus : null,
                date_from: dateFrom || null,
                date_to: dateTo || null,
                ...overrides,
            };

            const res = await axios.post(route('smart-reports.lead.generate'), payload);
            if (res.data.success) {
                setReportData(res.data.data.data || []);
                setSummary(res.data.data.summary || null);
            } else {
                setReportData([]);
                setSummary(null);
            }
        } catch (error: any) {
            setReportData([]);
            setSummary(null);
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        } finally {
            setIsLoading(false);
        }
    };

    const handleGenerateReport = () => loadReport();

    const clearFilters = () => {
        setSelectedPipeline('all');
        setSelectedStage('all');
        setSelectedUser('all');
        setSelectedSource([]);
        setSelectedLabel([]);
        setSelectedStatus([]);
        setDateFrom('');
        setDateTo('');
        loadReport({
            pipeline_ids: null,
            stage_ids: null,
            user_ids: null,
            source_ids: null,
            label_ids: null,
            status: null,
            date_from: null,
            date_to: null,
        });
    };

    const filteredData = useMemo(() => {
        if (!reportData) return [];
        if (!searchQuery.trim()) return reportData;
        const q = searchQuery.toLowerCase();
        return reportData.filter(item =>
            [item.lead_name, item.subject, item.email, item.assigned_to, item.pipeline_name, item.stage_name]
                .some(value => String(value || '').toLowerCase().includes(q))
        );
    }, [reportData, searchQuery]);

    const pageActions = (
        <div className="flex items-center gap-2">
            <ExportButton
                data={reportData || []}
                columns={[
                    { key: 'lead_name', header: t('Lead') },
                    { key: 'pipeline_name', header: t('Pipeline') },
                    { key: 'stage_name', header: t('Stage') },
                    { key: 'status', header: t('Status') },
                ]}
                filename="lead-report"
                title={t('Lead Report')}
            />
            <Button variant="outline" size="sm" onClick={() => router.visit(route('smart-reports.index'))}>
                <ArrowLeft className="h-4 w-4" />
                {t('Back')}
            </Button>
        </div>
    );

    const filtersPanel = (
        <Card>
            <CardContent className="px-4 py-3 space-y-3">
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Pipeline')}</label>
                        <Select value={selectedPipeline} onValueChange={v => { setSelectedPipeline(v); setSelectedStage('all'); }}>
                            <SelectTrigger><SelectValue placeholder={t('All Pipelines')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Pipelines')}</SelectItem>
                                {filterOptions?.pipelines.map(p => <SelectItem key={p.value} value={p.value.toString()}>{p.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Stage')}</label>
                        <Select value={selectedStage} onValueChange={setSelectedStage} disabled={selectedPipeline === 'all'}>
                            <SelectTrigger><SelectValue placeholder={selectedPipeline === 'all' ? t('Select Pipeline first') : t('Stage')} /></SelectTrigger>
                            <SelectContent searchable>
                                <SelectItem value="all">{t('All Stages')}</SelectItem>
                                {filterOptions?.stages.filter(s => s.pipeline_id === parseInt(selectedPipeline)).map(s => <SelectItem key={s.value} value={s.value.toString()}>{s.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('User')}</label>
                        <Select value={selectedUser} onValueChange={setSelectedUser}>
                            <SelectTrigger><SelectValue placeholder={t('All Users')} /></SelectTrigger>
                            <SelectContent searchable>
                                <SelectItem value="all">{t('All Users')}</SelectItem>
                                {filterOptions?.users.map(u => <SelectItem key={u.value} value={u.value.toString()}>{u.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Status')}</label>
                        <MultiSelectEnhanced options={[
                            { value: 'Active', label: t('Active') },
                            { value: 'Converted', label: t('Converted') },
                            { value: 'Inactive', label: t('Inactive') },
                        ]} value={selectedStatus} onValueChange={setSelectedStatus} placeholder={t('Select Status')} searchable />
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Source')}</label>
                        <MultiSelectEnhanced options={filterOptions?.sources || []} value={selectedSource} onValueChange={setSelectedSource} placeholder={t('Select Sources')} searchable />
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Label')}</label>
                        <MultiSelectEnhanced options={filterOptions?.labels || []} value={selectedLabel} onValueChange={setSelectedLabel} placeholder={t('Select Labels')} searchable />
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Date From')}</label>
                        <Input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} />
                    </div>
                    <div>
                        <label className="text-sm font-medium mb-1 block">{t('Date To')}</label>
                        <Input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} />
                    </div>
                </div>
                <div className="flex flex-wrap items-center justify-end gap-2">
                    <Button size="sm" onClick={handleGenerateReport}><Search className="h-4 w-4" /></Button>
                    <Button size="sm" variant="outline" onClick={clearFilters}><FilterX className="h-4 w-4" /></Button>
                </div>
            </CardContent>
        </Card>
    );

    if (isLoading && reportData === null) {
        return (
            <AuthenticatedLayout breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Lead Report') }]} pageTitle={t('Lead Report')} pageActions={pageActions}>
                <Head title={t('Lead Report')} />
                <div className="space-y-6">{filtersPanel}<Card><CardContent className="text-center py-12"><Target className="h-12 w-12 mx-auto text-muted-foreground mb-4" /><p className="text-lg font-medium text-muted-foreground">{t('Loading...')}</p></CardContent></Card></div>
            </AuthenticatedLayout>
        );
    }

    if (!reportData || reportData.length === 0) {
        return (
            <AuthenticatedLayout breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Lead Report') }]} pageTitle={t('Lead Report')} pageActions={pageActions}>
                <Head title={t('Lead Report')} />
                <div className="space-y-6">{filtersPanel}<Card><CardContent className="text-center py-12"><Target className="h-12 w-12 mx-auto text-muted-foreground mb-4" /><p className="text-lg font-medium text-muted-foreground">{t('No leads found for the selected filters.')}</p></CardContent></Card></div>
            </AuthenticatedLayout>
        );
    }

    const totalPages = Math.ceil(filteredData.length / perPage);
    const paginatedData = filteredData.slice((currentPage - 1) * perPage, currentPage * perPage);

    return (
        <AuthenticatedLayout breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Lead Report') }]} pageTitle={t('Lead Report')} pageActions={pageActions}>
            <Head title={t('Lead Report')} />
            <div className="space-y-6">
                {filtersPanel}
                {summary && (
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                        <Card className="bg-blue-50/50 border-blue-200"><CardContent className="py-3 px-4"><div className="flex items-center justify-between"><div className="flex items-center gap-2"><Target className="h-4 w-4 text-blue-600" /><span className="text-sm font-medium text-blue-700">{t('Total Leads')}</span></div><div className="text-xl font-bold text-blue-900">{summary.total_leads}</div></div></CardContent></Card>
                        <Card className="bg-green-50/50 border-green-200"><CardContent className="py-3 px-4"><div className="flex items-center justify-between"><div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-green-600" /><span className="text-sm font-medium text-green-700">{t('Converted')}</span></div><div className="text-xl font-bold text-green-900">{summary.converted_leads}</div></div></CardContent></Card>
                        <Card className="bg-amber-50/50 border-amber-200"><CardContent className="py-3 px-4"><div className="flex items-center justify-between"><div className="flex items-center gap-2"><Users className="h-4 w-4 text-amber-600" /><span className="text-sm font-medium text-amber-700">{t('Active')}</span></div><div className="text-xl font-bold text-amber-900">{summary.active_leads}</div></div></CardContent></Card>
                        <Card className="bg-purple-50/50 border-purple-200"><CardContent className="py-3 px-4"><div className="flex items-center justify-between"><div className="flex items-center gap-2"><TrendingUp className="h-4 w-4 text-purple-600" /><span className="text-sm font-medium text-purple-700">{t('Conversion Rate')}</span></div><div className="text-xl font-bold text-purple-900">{summary.conversion_rate}%</div></div></CardContent></Card>
                    </div>
                )}
                <Card>
                    <CardContent className="p-4 border-b bg-gray-50/50">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex-1 max-w-md relative"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input placeholder={t('Search...')} value={searchQuery} onChange={e => { setSearchQuery(e.target.value); setCurrentPage(1); }} className="pl-9" /></div>
                            <div className="flex items-center gap-2"><PerPageSelector routeName="smart-reports.lead.report" defaultValue={perPage.toString()} onPageChange={(val) => { setPerPage(Number(val)); setCurrentPage(1); }} /></div>
                        </div>
                    </CardContent>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 sticky top-0">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium">{t('Lead')}</th>
                                    <th className="px-4 py-3 text-left font-medium">{t('Pipeline / Stage')}</th>
                                    <th className="px-4 py-3 text-left font-medium">{t('Owner')}</th>
                                    <th className="px-4 py-3 text-left font-medium">{t('Status')}</th>
                                    <th className="px-4 py-3 text-left font-medium">{t('Follow Up')}</th>
                                    <th className="px-4 py-3 text-left font-medium">{t('Age')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {paginatedData.length === 0 ? <tr><td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">{t('No records found')}</td></tr> : paginatedData.map(item => <tr key={item.id} className="hover:bg-muted/30"><td className="px-4 py-3"><div className="font-semibold">{item.lead_name}</div><div className="text-xs text-muted-foreground">{item.subject || '-'}</div></td><td className="px-4 py-3"><div className="text-sm">{item.pipeline_name || '-'}</div><div className="text-xs text-muted-foreground">{item.stage_name || '-'}</div></td><td className="px-4 py-3"><div className="text-sm">{item.assigned_to || '-'}</div><div className="text-xs text-muted-foreground">{item.created_by_name || '-'}</div></td><td className="px-4 py-3"><span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${item.status === 'Converted' ? 'bg-green-100 text-green-700' : item.status === 'Active' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'}`}>{t(item.status)}</span></td><td className="px-4 py-3 text-sm">{item.follow_up_date || '-'}</td><td className="px-4 py-3 text-sm">{item.age_days} {t('days')}</td></tr>)}
                            </tbody>
                        </table>
                    </div>
                    <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                        <Pagination data={{ current_page: currentPage, last_page: totalPages, per_page: perPage, total: filteredData.length, from: ((currentPage - 1) * perPage) + 1, to: Math.min(currentPage * perPage, filteredData.length) }} onPageChange={(page) => setCurrentPage(page)} />
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
};

export default LeadReport;
