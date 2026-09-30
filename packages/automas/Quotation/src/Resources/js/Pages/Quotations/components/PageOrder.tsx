import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import RichTextEditor from '@/components/ui/rich-text-editor';
import MediaPicker from '@/components/MediaPicker';
import {
    FileText,
    Plus,
    Trash2,
    Pencil,
    GripVertical,
    ArrowUp,
    ArrowDown,
    Layers,
    Sparkles,
    BookOpen,
    Info,
    Image as ImageIcon,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { toast } from 'sonner';
import { replaceUserShortcodes } from '../utils/quotationShortcodes';

export interface QuotationSectionItem {
    id: string;
    title: string;
    content: string;
    page_type?: string;
    background_image?: string;
    order: number;
    default_page_id?: number;
}

interface QuotationDefaultPage {
    id: number;
    title: string;
    content: string;
    page_type?: string;
    background_image?: string;
    sort_order?: number;
    creator_id?: number;
    created_by?: number;
}

interface Props {
    sections: QuotationSectionItem[];
    setSections: React.Dispatch<React.SetStateAction<QuotationSectionItem[]>>;
    defaultPages?: QuotationDefaultPage[];
    quotationSetting?: any;
}

const defaultQuotationVariables: Record<string, string> = {
    'App Name': 'app_name',
    'Company Name': 'company_name',
    'Company Logo': 'company_logo',
    'Quotation Logo': 'quotation_logo',
    'Company Email': 'company_email',
    'Company Phone': 'company_phone',
    'Company Address': 'company_address',
    'Company Website': 'company_website',
    'User Name': 'user_name',
    'User Email': 'user_email',
    'User Phone': 'user_phone',
    'Subject': 'quotation_subject',
    'Quotation Number': 'quotation_number',
    'Quotation Date': 'quotation_date',
    'Due Date': 'due_date',
    'Customer Name': 'customer_name',
    'Customer Email': 'customer_email',
    'Customer Phone': 'customer_phone',
    'Customer Address': 'customer_address',
    'Total Amount': 'total_amount',
    'Sub Total': 'sub_total',
    'Total Tax': 'total_tax',
    'Total Discount': 'total_discount',
};

const DYNAMIC_PAGE_TYPES = new Set(['otc', 'mrc', 'other-details']);

const isDynamicPage = (pageType?: string) => DYNAMIC_PAGE_TYPES.has(pageType || '');

const getDynamicSectionTargetId = (pageType?: string) => {
    switch (pageType) {
        case 'otc': return 'otc-section';
        case 'mrc': return 'mrc-section';
        case 'other-details': return 'other-details-section';
        default: return null;
    }
};

export default function PageOrder({ sections, setSections, defaultPages = [], quotationSetting: propSetting }: Props) {
    const { t } = useTranslation();
    const pageProps = usePage<any>().props;
    const settings = propSetting || pageProps.quotationSetting || {};

    const templateColor = settings?.template_color || '#E9591C';

    // Modal state
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState<'add' | 'edit'>('add');
    const [addTab, setAddTab] = useState<'existing' | 'new'>('existing');

    // Page Form state
    const [modalTitle, setModalTitle] = useState('');
    const [modalBackground, setModalBackground] = useState('');
    const [modalPageType, setModalPageType] = useState('general');
    const [selectedDefaultPage, setSelectedDefaultPage] = useState<QuotationDefaultPage | null>(null);
    const [editingSection, setEditingSection] = useState<QuotationSectionItem | null>(null);

    // Editor mode & content type ('text' | 'html')
    const [contentEditorType, setContentEditorType] = useState<'text' | 'html'>('text');
    const [textContent, setTextContent] = useState('');
    const [htmlContent, setHtmlContent] = useState('');
    const [editorKey, setEditorKey] = useState(0);
    const [modalBgType, setModalBgType] = useState<'default' | 'custom'>('default');

    // Drag and drop state
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

    const handleCopyVariable = async (variableKey: string) => {
        const textToCopy = `{${variableKey}}`;

        try {
            await navigator.clipboard.writeText(textToCopy);
            toast.success(t('Variable copied: {{var}}', { var: textToCopy }));
        } catch {
            toast.error(t('Failed to copy variable.'));
        }
    };

    const handleEditorTypeChange = (val: 'text' | 'html') => {
        setContentEditorType(val);
        if (val === 'text') {
            setTextContent('');
            setEditorKey((prev) => prev + 1);
        } else {
            setHtmlContent('');
        }
    };

    const resetModalData = () => {
        setSelectedDefaultPage(null);
        setModalTitle('');
        setTextContent('');
        setHtmlContent('');
        setContentEditorType('text');
        setModalBackground('');
        setModalBgType('default');
        setModalPageType('general');
        setEditorKey((prev) => prev + 1);
    };

    const populateModalData = (title: string, rawContent: string, bg: string, pageType: string) => {
        setModalTitle(title);
        const isHtml = pageType === 'html' || pageType === 'custom';
        if (isHtml) {
            setContentEditorType('html');
            setHtmlContent(rawContent || '');
            setTextContent('');
        } else {
            setContentEditorType('text');
            setTextContent(rawContent || '');
            setHtmlContent('');
        }
        setEditorKey((prev) => prev + 1);
        setModalBackground(bg || '');
        setModalBgType(Boolean(bg && String(bg).trim() !== '') ? 'custom' : 'default');
        setModalPageType(pageType || 'general');
    };

    // Open Add Modal
    const handleOpenAddModal = () => {
        setModalMode('add');
        const customDefaultPages = defaultPages.filter((p) => !isDynamicPage(p.page_type));
        const authUser = pageProps?.auth?.user;
        if (customDefaultPages && customDefaultPages.length > 0) {
            setAddTab('existing');
            const firstPage = customDefaultPages[0];
            setSelectedDefaultPage(firstPage);
            populateModalData(
                firstPage.title || '',
                replaceUserShortcodes(firstPage.content, authUser) || '',
                firstPage.background_image || '',
                firstPage.page_type || 'content'
            );
        } else {
            setAddTab('new');
            setSelectedDefaultPage(null);
            populateModalData('', '', '', 'content');
        }
        setIsModalOpen(true);
    };

    // Select existing default page in Add Modal
    const handleSelectDefaultPage = (page: QuotationDefaultPage) => {
        const authUser = pageProps?.auth?.user;
        setSelectedDefaultPage(page);
        populateModalData(
            page.title,
            replaceUserShortcodes(page.content, authUser) || '',
            page.background_image || '',
            page.page_type || 'general'
        );
    };

    // Open Edit Modal / Jump to section for OTC/MRC/Other Details
    const handleOpenEditModal = (sec: QuotationSectionItem) => {
        if (isDynamicPage(sec.page_type)) {
            const targetId = getDynamicSectionTargetId(sec.page_type);
            const el = targetId ? document.getElementById(targetId) : null;
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-2', 'ring-primary', 'transition-all');
                setTimeout(() => el.classList.remove('ring-2', 'ring-primary'), 2000);
            }
            return;
        }

        setModalMode('edit');
        setEditingSection(sec);
        populateModalData(
            sec.title,
            sec.content || '',
            sec.background_image || '',
            sec.page_type || 'general'
        );
        setIsModalOpen(true);
    };

    // Save/Confirm Page in Modal
    const handleSaveModal = () => {
        if (!modalTitle.trim()) {
            toast.error(t('Please enter a page title.'));
            return;
        }

        const finalContent = contentEditorType === 'html' ? htmlContent : textContent;
        const isDynamic = isDynamicPage(modalPageType);
        const determinedPageType = isDynamic
            ? modalPageType
            : contentEditorType === 'html'
                ? 'html'
                : 'general';

        if (modalMode === 'add') {
            const newSection: QuotationSectionItem = {
                id: typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : `sec-${Date.now()}`,
                default_page_id: addTab === 'existing' && selectedDefaultPage ? selectedDefaultPage.id : undefined,
                title: modalTitle.trim(),
                content: finalContent,
                page_type: determinedPageType,
                background_image: modalBackground,
                order: sections.length + 1,
            };

            setSections((prev) => [...prev, newSection]);
            toast.success(t('Page added to Quotation order.'));
        } else if (modalMode === 'edit' && editingSection) {
            setSections((prev) =>
                prev.map((s) =>
                    s.id === editingSection.id
                        ? {
                            ...s,
                            title: modalTitle.trim(),
                            content: finalContent,
                            page_type: determinedPageType,
                            background_image: modalBackground,
                        }
                        : s
                )
            );
            toast.success(t('Page updated.'));
        }

        setIsModalOpen(false);
        setEditingSection(null);
    };

    // Delete section / Jump to section if dynamic
    const handleRemoveSection = (sec: QuotationSectionItem) => {
        if (isDynamicPage(sec.page_type)) {
            const targetId = getDynamicSectionTargetId(sec.page_type);
            const el = targetId ? document.getElementById(targetId) : null;
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-4', 'ring-rose-500/50', 'transition-all');
                setTimeout(() => el.classList.remove('ring-4', 'ring-rose-500/50'), 2500);
            }
            const sectionName = sec.title || (sec.page_type === 'otc' ? 'One-Time Charges (OTC)' : (sec.page_type === 'mrc' ? 'Monthly Recurring Charges (MRC)' : 'Other Details'));
            toast.info(t('To remove {{section}}, please delete all items or clear content from that section above.', { section: sectionName }));
            return;
        }

        setSections((prev) => {
            const filtered = prev.filter((s) => s.id !== sec.id);
            return filtered.map((item, idx) => ({ ...item, order: idx + 1 }));
        });
        toast.success(t('Page removed.'));
    };

    // Move Up / Down
    const handleMove = (index: number, direction: 'up' | 'down') => {
        const targetIndex = direction === 'up' ? index - 1 : index + 1;
        if (targetIndex < 0 || targetIndex >= sections.length) return;

        const updated = [...sections];
        const [moved] = updated.splice(index, 1);
        updated.splice(targetIndex, 0, moved);
        const reordered = updated.map((item, idx) => ({ ...item, order: idx + 1 }));
        setSections(reordered);
    };

    // Drag handlers
    const handleDragStart = (index: number) => {
        setDraggedIndex(index);
    };

    const handleDragOver = (e: React.DragEvent, targetIndex: number) => {
        e.preventDefault();
        if (draggedIndex === null || draggedIndex === targetIndex) return;

        const updated = [...sections];
        const [dragged] = updated.splice(draggedIndex, 1);
        updated.splice(targetIndex, 0, dragged);
        const reordered = updated.map((item, idx) => ({ ...item, order: idx + 1 }));
        setDraggedIndex(targetIndex);
        setSections(reordered);
    };

    const handleDragEnd = () => {
        setDraggedIndex(null);
    };

    return (
        <Card className="shadow-sm border">
            <div className="p-6 space-y-4">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
                    <div>
                        <h3 className="text-lg font-semibold flex items-center gap-2 text-slate-900 dark:text-slate-100">
                            <Layers className="h-5 w-5 text-primary" />
                            {t('Page Order')}
                        </h3>
                        <p className="text-xs text-muted-foreground mt-0.5">
                            {t('Organize, add, edit, or reorder Quotation pages.')}
                        </p>
                    </div>
                    <Button
                        type="button"
                        onClick={handleOpenAddModal}
                        variant="default"
                        size="sm"
                        className="gap-2 shrink-0"
                    >
                        <Plus className="h-4 w-4" />
                        {t('Add Page')}
                    </Button>
                </div>

                {/* Compact Cards Grid / Reorder List */}
                {sections.length === 0 ? (
                    <div className="border-2 border-dashed rounded-xl p-8 text-center bg-muted/10 space-y-2">
                        <FileText className="h-8 w-8 mx-auto text-muted-foreground/60" />
                        <p className="text-sm font-medium">{t('No pages in order yet.')}</p>
                        <p className="text-xs text-muted-foreground">
                            {t('Click "+ Add Page" to add existing templates or custom pages.')}
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        {sections.map((sec, index) => {
                            const isDynamicSection = isDynamicPage(sec.page_type);
                            return (
                                <div
                                    key={sec.id}
                                    draggable
                                    onDragStart={() => handleDragStart(index)}
                                    onDragOver={(e) => handleDragOver(e, index)}
                                    onDragEnd={handleDragEnd}
                                    className={cn(
                                        "group relative bg-card border rounded-xl p-3 flex flex-col justify-between gap-3 shadow-2xs hover:shadow-sm transition-all select-none",
                                        draggedIndex === index ? "opacity-40 border-dashed border-primary ring-2 ring-primary/20" : "hover:border-primary/50"
                                    )}
                                >
                                    {/* Top Card Header */}
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex items-center gap-2 min-w-0">
                                            <div
                                                className="cursor-grab active:cursor-grabbing p-0.5 text-muted-foreground hover:text-foreground shrink-0"
                                                title={t('Drag to reorder')}
                                            >
                                                <GripVertical className="h-4 w-4" />
                                            </div>
                                            <Badge variant="outline" className="h-5 px-1.5 text-[10px] font-bold bg-muted/50 shrink-0">
                                                #{index + 1}
                                            </Badge>
                                            <h4 className="text-xs font-bold truncate text-slate-900 dark:text-slate-100" title={sec.title}>
                                                {sec.title}
                                            </h4>
                                        </div>
                                        {sec.page_type === 'otc' && (
                                            <Badge variant="secondary" className="text-[9px] px-1.5 py-0 h-4 bg-purple-500/10 text-purple-600 border-purple-200 shrink-0">
                                                {t('OTC')}
                                            </Badge>
                                        )}
                                        {sec.page_type === 'mrc' && (
                                            <Badge variant="secondary" className="text-[9px] px-1.5 py-0 h-4 bg-blue-500/10 text-blue-600 border-blue-200 shrink-0">
                                                {t('MRC')}
                                            </Badge>
                                        )}
                                        {sec.page_type === 'other-details' && (
                                            <Badge variant="secondary" className="text-[9px] px-1.5 py-0 h-4 bg-emerald-500/10 text-emerald-600 border-emerald-200 shrink-0">
                                                {t('Other')}
                                            </Badge>
                                        )}
                                    </div>

                                    {/* Card Footer Actions */}
                                    <div className="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                                        {/* Up/Down buttons */}
                                        <div className="flex items-center gap-0.5">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 text-slate-500 hover:text-slate-900 disabled:opacity-30"
                                                onClick={() => handleMove(index, 'up')}
                                                disabled={index === 0}
                                                title={t('Move Up')}
                                            >
                                                <ArrowUp className="h-3 w-3" />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 text-slate-500 hover:text-slate-900 disabled:opacity-30"
                                                onClick={() => handleMove(index, 'down')}
                                                disabled={index === sections.length - 1}
                                                title={t('Move Down')}
                                            >
                                                <ArrowDown className="h-3 w-3" />
                                            </Button>
                                        </div>

                                        {/* Action Icons: Edit, Delete */}
                                        <div className="flex items-center gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 text-slate-500 hover:text-blue-600 hover:bg-blue-50"
                                                onClick={() => handleOpenEditModal(sec)}
                                                title={isDynamicSection ? t('Jump to Section') : t('Edit Page')}
                                            >
                                                <Pencil className="h-3.5 w-3.5" />
                                            </Button>

                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                                onClick={() => handleRemoveSection(sec)}
                                                title={isDynamicSection ? t('Go to Section to Remove') : t('Remove Page')}
                                            >
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* FULL-FEATURED PAGE CREATION & EDIT MODAL (Identical to Default Pages Create View) */}
            <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
                <DialogContent className="max-w-6xl max-h-[94vh] flex flex-col p-0 gap-0 overflow-hidden bg-background border-border shadow-2xl [&>button]:top-4.5 [&>button]:right-4.5">
                    {/* Modal Header */}
                    <DialogHeader className="p-4 sm:pl-6 sm:pr-14 border-b bg-background flex flex-row items-center justify-between space-y-0 shrink-0 gap-4">
                        <div className="flex items-center gap-3 min-w-0">
                            <div className="p-2 rounded-lg bg-primary/10 text-primary shrink-0">
                                {modalMode === 'add' ? <BookOpen className="h-5 w-5" /> : <Pencil className="h-5 w-5 text-blue-600" />}
                            </div>
                            <div className="min-w-0">
                                <DialogTitle className="text-base font-semibold truncate">
                                    {modalMode === 'add' ? t('Add Page to Quotation') : t('Edit Quotation Page')}
                                </DialogTitle>
                                <p className="text-xs text-muted-foreground truncate">
                                    {modalMode === 'add'
                                        ? t('Create a custom Quotation page or load from existing default pages.')
                                        : t('Customize title, background, and content for this page.')}
                                </p>
                            </div>
                        </div>

                        {modalMode === 'add' && defaultPages.length > 0 && (
                            <div className="flex items-center bg-muted/60 p-1 rounded-lg border border-border shrink-0">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setAddTab('existing');
                                        const availablePages = defaultPages.filter((p) => !isDynamicPage(p.page_type));
                                        if (availablePages.length > 0) {
                                            handleSelectDefaultPage(availablePages[0]);
                                        }
                                    }}
                                    className={cn(
                                        "px-3 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5",
                                        addTab === 'existing'
                                            ? "bg-background text-primary shadow-xs font-bold"
                                            : "text-muted-foreground hover:text-foreground"
                                    )}
                                >
                                    <Layers className="h-3.5 w-3.5" />
                                    {t('Choose from Default Pages')}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setAddTab('new');
                                        resetModalData();
                                    }}
                                    className={cn(
                                        "px-3 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5",
                                        addTab === 'new'
                                            ? "bg-background text-primary shadow-xs font-bold"
                                            : "text-muted-foreground hover:text-foreground"
                                    )}
                                >
                                    <Sparkles className="h-3.5 w-3.5" />
                                    {t('New Custom Page')}
                                </button>
                            </div>
                        )}
                    </DialogHeader>

                    {/* Modal Body - 2 Column Layout */}
                    <div className="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50/50 dark:bg-slate-950/50">
                        <div className="grid grid-cols-12 gap-6">
                            {/* Left Column: Existing Templates list (if add) & Variables */}
                            <div className="col-span-12 lg:col-span-3 space-y-4">
                                {modalMode === 'add' && addTab === 'existing' && defaultPages.filter((p) => !isDynamicPage(p.page_type)).length > 0 && (
                                    <Card className="shadow-xs border">
                                        <CardHeader className="p-3 pb-2 border-b">
                                            <CardTitle className="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                                                <Layers className="h-3.5 w-3.5 text-primary" />
                                                {t('Existing Pages')}
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="p-2 space-y-1 max-h-[220px] overflow-y-auto">
                                            {defaultPages.filter((p) => !isDynamicPage(p.page_type)).map((page) => {
                                                const isSelected = selectedDefaultPage?.id === page.id;
                                                const isAlreadyAdded = sections.some(s => s.title.toLowerCase() === page.title.toLowerCase());

                                                return (
                                                    <button
                                                        key={page.id}
                                                        type="button"
                                                        onClick={() => handleSelectDefaultPage(page)}
                                                        className={cn(
                                                            "w-full text-left p-2 rounded-lg border text-xs font-medium transition-all flex items-center justify-between gap-1.5",
                                                            isSelected
                                                                ? "bg-primary text-primary-foreground border-primary shadow-xs font-semibold"
                                                                : "bg-card hover:bg-accent border-border"
                                                        )}
                                                    >
                                                        <div className="flex items-center gap-1 min-w-0">
                                                            <span className="truncate">{page.title}</span>
                                                            {page.created_by !== undefined && page.creator_id !== undefined && page.created_by === page.creator_id && (
                                                                <span className={cn(
                                                                    "text-[8px] font-semibold uppercase px-1 py-0.2 rounded border shrink-0",
                                                                    isSelected ? "bg-primary-foreground/20 text-primary-foreground border-primary-foreground/30" : "bg-amber-500/10 text-amber-600 border-amber-500/20"
                                                                )}>
                                                                    {t('Company')}
                                                                </span>
                                                            )}
                                                        </div>
                                                        {isAlreadyAdded && (
                                                            <Badge
                                                                variant="secondary"
                                                                className={cn(
                                                                    "text-[9px] px-1 py-0 h-4 shrink-0",
                                                                    isSelected ? "bg-primary-foreground/20 text-primary-foreground" : "bg-green-100 text-green-700"
                                                                )}
                                                            >
                                                                {t('Added')}
                                                            </Badge>
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </CardContent>
                                    </Card>
                                )}

                                {/* Variables Card */}
                                <Card className="shadow-xs border">
                                    <CardHeader className="p-3 pb-1.5">
                                        <CardTitle className="text-base font-semibold">{t('Variables')}</CardTitle>
                                    </CardHeader>
                                    <CardContent className="p-3 pt-0">
                                        <div className="grid grid-cols-1 gap-1 text-xs">
                                            {Object.entries(defaultQuotationVariables).map(([key, value]) => (
                                                <div
                                                    key={key}
                                                    className="flex items-center justify-between group cursor-pointer hover:bg-muted/60 py-1 px-1.5 rounded transition-colors leading-tight"
                                                    onClick={() => handleCopyVariable(value)}
                                                    title={t('Click to copy')}
                                                >
                                                    <span className="text-muted-foreground">{key}:</span>
                                                    <span className="text-primary font-mono font-medium group-hover:underline">
                                                        {`{${value}}`}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            {/* Right Column: Page Form & Multi-tab Editor */}
                            <div className="col-span-12 lg:col-span-9 space-y-4">
                                <Card className="shadow-xs border">
                                    <CardContent className="p-4 sm:p-6 space-y-4">
                                        {/* Page Title */}
                                        <div className="space-y-1.5">
                                            <Label htmlFor="modal-page-title" className="text-sm font-medium">{t('Page Title')}</Label>
                                            <Input
                                                id="modal-page-title"
                                                value={modalTitle}
                                                onChange={(e) => setModalTitle(e.target.value)}
                                                placeholder={t('Enter page title')}
                                                required
                                            />
                                        </div>

                                        {/* Background Image Selection */}
                                        <div className="border rounded-lg p-3.5 space-y-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-2">
                                                    <ImageIcon className="h-4 w-4 text-primary" />
                                                    <span className="text-xs font-bold text-slate-800 dark:text-slate-200">
                                                        {t("Page Background Image")}
                                                    </span>
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    className="h-7 text-xs"
                                                    onClick={() => {
                                                        if (modalBgType === 'custom') {
                                                            setModalBgType('default');
                                                            setModalBackground('');
                                                        } else {
                                                            setModalBgType('custom');
                                                            setModalBackground('');
                                                        }
                                                    }}
                                                >
                                                    {modalBgType === 'custom'
                                                        ? t("Use Default Background")
                                                        : t("Upload Custom Background")}
                                                </Button>
                                            </div>

                                            {modalBgType === 'custom' && (
                                                <div className="pt-2 border-t space-y-2">
                                                    <MediaPicker
                                                        id="bg-image-quotation-modal"
                                                        value={modalBackground}
                                                        onChange={(url) =>
                                                            setModalBackground(
                                                                typeof url === "string" ? url : url[0] || ""
                                                            )
                                                        }
                                                        placeholder={t("Choose background image from library...")}
                                                        showPreview={true}
                                                    />
                                                    <div className="inline-flex items-center gap-1.5 text-[11px] text-amber-700 dark:text-amber-300 bg-amber-500/10 px-2.5 py-1 rounded border border-amber-500/20">
                                                        <Info className="h-3.5 w-3.5 shrink-0 text-amber-600" />
                                                        <span>
                                                            {t("Recommended size: A4 210mm × 297mm (JPG, PNG, WebP).")}
                                                        </span>
                                                    </div>
                                                </div>
                                            )}
                                        </div>

                                        {/* Page Content Editor */}
                                        <div className="space-y-2 pt-1">
                                            <div className="flex items-center gap-4">
                                                <Label
                                                    htmlFor="modal-page-content"
                                                    className="text-sm font-bold text-slate-800 dark:text-slate-200"
                                                >
                                                    {t("Page Content")}
                                                </Label>
                                                <div className="flex items-center gap-2">
                                                    <Select
                                                        value={contentEditorType}
                                                        onValueChange={(val: "text" | "html") =>
                                                            handleEditorTypeChange(val)
                                                        }
                                                    >
                                                        <SelectTrigger className="h-9 w-[130px] text-xs font-medium">
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="text" className="text-xs">
                                                                {t("Text Editor")}
                                                            </SelectItem>
                                                            <SelectItem value="html" className="text-xs">
                                                                {t("HTML Page")}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>

                                            {/* TEXT EDITOR */}
                                            {contentEditorType === "text" && (
                                                <div
                                                    style={{
                                                        "--template-color": templateColor,
                                                    } as React.CSSProperties}
                                                >
                                                    <RichTextEditor
                                                        key={editorKey}
                                                        content={textContent}
                                                        onChange={(val) => setTextContent(val)}
                                                        placeholder={t("Enter page content with HTML and variables")}
                                                        className="border rounded-lg bg-white shadow-xs overflow-hidden min-h-[320px]"
                                                    />
                                                </div>
                                            )}

                                            {/* HTML PAGE */}
                                            {contentEditorType === "html" && (
                                                <div className="border rounded-lg overflow-hidden bg-white border-slate-200 shadow-xs">
                                                    <Textarea
                                                        id="modal-content-html"
                                                        value={htmlContent}
                                                        onChange={(e) => setHtmlContent(e.target.value)}
                                                        placeholder={t("Paste or write full HTML and CSS here...")}
                                                        rows={15}
                                                        className="font-mono text-xs leading-relaxed bg-white text-slate-900 border-none focus-visible:ring-0 focus-visible:outline-none min-h-[320px] resize-y p-4 selection:bg-primary/20 rounded-none"
                                                        spellCheck={false}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>

                    {/* Modal Footer */}
                    <DialogFooter className="p-4 sm:px-6 border-t bg-background flex flex-row items-center justify-end gap-2 shrink-0">
                        <Button type="button" variant="outline" onClick={() => setIsModalOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button type="button" onClick={handleSaveModal} className="gap-1.5">
                            {modalMode === 'add' ? (
                                <>
                                    <Plus className="h-4 w-4" />
                                    {t('Add to Quotation')}
                                </>
                            ) : (
                                <>
                                    <Pencil className="h-4 w-4" />
                                    {t('Save Changes')}
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Card>
    );
}
