import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useForm, usePage } from '@inertiajs/react';
import {
    CheckSquare,
    DollarSign,
    GitBranch,
    Globe,
    Layers,
    Loader2,
    Mail,
    Package,
    Phone,
    Plus,
    Tag,
    User,
} from 'lucide-react';

import { Deal } from '../types';
import LabelView from '../LabelView';

import { formatCurrency, formatDateTime, getImagePath } from '@/utils/helpers';
import { useFormFields } from '@/hooks/useFormFields';

import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label as FormLabel } from '@/components/ui/label';
import { RichTextEditor } from '@/components/ui/rich-text-editor';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface GeneralProps {
    deal: Deal;
    onStatusChange: (status: string) => void;
}

interface DealLabel {
    id: number;
    name: string;
    color: string;
    pipeline_id?: number | string | null;
}

interface PageProps {
    auth?: {
        user?: {
            permissions?: string[];
        };
    };
    labels?: DealLabel[];
}

const parseIdList = (value: unknown): number[] => {
    if (!value) {
        return [];
    }

    let rawValues: unknown[] = [];

    if (Array.isArray(value)) {
        rawValues = value;
    } else if (typeof value === 'string') {
        const trimmed = value.trim();

        if (!trimmed) {
            return [];
        }

        try {
            const parsed = JSON.parse(trimmed);
            rawValues = Array.isArray(parsed) ? parsed : [parsed];
        } catch {
            rawValues = trimmed.split(',');
        }
    } else {
        rawValues = [value];
    }

    return [
        ...new Set(
            rawValues
                .flatMap((item) =>
                    String(item)
                        .replace(/^\[/, '')
                        .replace(/\]$/, '')
                        .replace(/"/g, '')
                        .split(','),
                )
                .map((item) => Number(item.trim()))
                .filter((item) => Number.isInteger(item) && item > 0),
        ),
    ];
};

const countItems = (value: unknown): number => parseIdList(value).length;

const stripHtmlAndDecode = (html: string): string => {
    if (!html) {
        return '';
    }

    return html
        .replace(/<[^>]*>/g, '')
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&nbsp;/g, ' ');
};

export default function General({
    deal,
    onStatusChange,
}: GeneralProps) {
    const { t } = useTranslation();
    const { auth, labels = [] } = usePage<PageProps>().props;

    const [isChangingStatus, setIsChangingStatus] = useState(false);
    const [emailModalOpen, setEmailModalOpen] = useState(false);
    const [discussionModalOpen, setDiscussionModalOpen] = useState(false);
    const [labelModalOpen, setLabelModalOpen] = useState(false);
    const [emailEditorKey, setEmailEditorKey] = useState(0);

    const {
        data: emailForm,
        setData: setEmailData,
        post: postEmail,
        processing: emailProcessing,
        errors: emailErrors,
        reset: resetEmail,
    } = useForm({
        to: '',
        subject: '',
        description: '',
    });

    const {
        data: notesForm,
        setData: setNotesData,
        put: putNotes,
        processing: notesProcessing,
    } = useForm({
        notes: deal.notes || '',
    });

    const {
        data: discussionForm,
        setData: setDiscussionData,
        post: postDiscussion,
        processing: discussionProcessing,
        errors: discussionErrors,
        reset: resetDiscussion,
    } = useForm({
        message: '',
    });

    const customFields = useFormFields(
        'getCustomFields',
        {
            ...deal,
            module: 'Lead',
            sub_module: 'Deal',
            id: deal.id,
        },
        () => { },
        {},
        'view',
        t,
    );

    const emailSubjectAI = useFormFields(
        'aiField',
        emailForm,
        (field, value) => {
            setEmailData(field as keyof typeof emailForm, value as never);
        },
        {},
        'create',
        'subject',
        'Subject',
        'lead',
        'deal_email',
    );

    const emailDescriptionAI = useFormFields(
        'aiField',
        emailForm,
        (field, value) => {
            setEmailData(field as keyof typeof emailForm, value as never);
            setEmailEditorKey((previous) => previous + 1);
        },
        {},
        'create',
        'description',
        'Description',
        'lead',
        'deal_email',
    );

    const selectedLabelIds = parseIdList(deal.labels);

    const assignedLabels = labels.filter((label) =>
        selectedLabelIds.includes(Number(label.id)),
    );

    const sourcesCount = countItems(deal.sources);
    const productsCount = countItems(deal.products);

    const canEditDeals =
        auth?.user?.permissions?.includes('edit-deals') ?? false;

    const handleEmailSubmit = (event: React.FormEvent): void => {
        event.preventDefault();

        postEmail(route('lead.deals.store-email', deal.id), {
            preserveScroll: true,
            onSuccess: () => {
                resetEmail();
                setEmailModalOpen(false);
            },
        });
    };

    const handleDiscussionSubmit = (
        event: React.FormEvent,
    ): void => {
        event.preventDefault();

        postDiscussion(route('lead.deals.store-discussion', deal.id), {
            preserveScroll: true,
            onSuccess: () => {
                resetDiscussion();
                setDiscussionModalOpen(false);
            },
        });
    };

    const handleStatusChange = (status: string): void => {
        setIsChangingStatus(true);
        onStatusChange(status);

        window.setTimeout(() => {
            setIsChangingStatus(false);
        }, 1000);
    };

    return (
        <div className="space-y-8">
            {/* Stats */}
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <Mail className="h-5 w-5 text-blue-500" />
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {deal.emails?.length ?? 0}
                        </p>
                        <p className="text-xs text-gray-500">
                            {t('Emails')}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50">
                        <Globe className="h-5 w-5 text-green-500" />
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {sourcesCount}
                        </p>
                        <p className="text-xs text-gray-500">
                            {t('Sources')}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                        <Package className="h-5 w-5 text-orange-500" />
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {productsCount}
                        </p>
                        <p className="text-xs text-gray-500">
                            {t('Products')}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                        <CheckSquare className="h-5 w-5 text-violet-500" />
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {deal.tasks?.length ?? 0}
                        </p>
                        <p className="text-xs text-gray-500">
                            {t('Tasks')}
                        </p>
                    </div>
                </div>
            </div>

            {/* Header and details */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-5">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-3">
                                <h1 className="text-lg font-bold text-gray-900">
                                    {deal.name}
                                </h1>

                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-medium ${deal.status === 'Won'
                                        ? 'bg-green-100 text-green-800'
                                        : deal.status === 'Loss'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-blue-100 text-blue-800'
                                        }`}
                                >
                                    {deal.status}
                                </span>
                            </div>
                        </div>

                        {/* Maximum 50% width on desktop; labels wrap */}
                        <div className="w-full sm:w-[50%] sm:max-w-[50%]">
                            <div className="flex flex-wrap items-center justify-start gap-2 sm:justify-end">
                                {assignedLabels.map((label) => (
                                    <span
                                        key={label.id}
                                        className="inline-flex items-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium text-white shadow-sm"
                                        style={{
                                            backgroundColor: label.color,
                                        }}
                                    >
                                        {label.name}
                                    </span>
                                ))}

                                <TooltipProvider>
                                    <Tooltip delayDuration={0}>
                                        <TooltipTrigger asChild>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setLabelModalOpen(true)
                                                }
                                                className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:border-primary hover:bg-primary/10 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
                                                aria-label={t(
                                                    'Manage Labels',
                                                )}
                                            >
                                                <Tag className="h-3.5 w-3.5" />
                                            </button>
                                        </TooltipTrigger>

                                        <TooltipContent>
                                            <p>{t('Manage Labels')}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>{canEditDeals && (
                                    <Select
                                        value={deal.status}
                                        onValueChange={handleStatusChange}
                                        disabled={isChangingStatus}
                                    >
                                        <SelectTrigger className="h-8 w-36 bg-white shadow-sm">
                                            {isChangingStatus ? (
                                                <Loader2 className="h-4 w-4 animate-spin" />
                                            ) : (
                                                <SelectValue />
                                            )}
                                        </SelectTrigger>

                                        <SelectContent>
                                            <SelectItem value="Won">
                                                {t('Won')}
                                            </SelectItem>
                                            <SelectItem value="Loss">
                                                {t('Loss')}
                                            </SelectItem>
                                            <SelectItem value="Active">
                                                {t('Active')}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="px-6 py-5">
                    <div className="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50">
                                <DollarSign className="h-4 w-4 text-green-500" />
                            </div>
                            <div>
                                <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {t('Price')}
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {deal.price
                                        ? formatCurrency(deal.price)
                                        : '-'}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                                <Phone className="h-4 w-4 text-blue-500" />
                            </div>
                            <div>
                                <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {t('Phone')}
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {deal.phone || '-'}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                                <User className="h-4 w-4 text-violet-500" />
                            </div>
                            <div>
                                <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {t('Creator')}
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {deal.creator?.name || '-'}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                                <GitBranch className="h-4 w-4 text-indigo-500" />
                            </div>
                            <div>
                                <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {t('Pipeline')}
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {deal.pipeline?.name || '-'}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                                <Layers className="h-4 w-4 text-blue-500" />
                            </div>
                            <div>
                                <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {t('Stage')}
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {deal.stage?.name || '-'}
                                </p>
                            </div>
                        </div>

                        {customFields.map((field, index) => (
                            <div
                                key={field.id ?? index}
                                className="flex items-start gap-3"
                            >
                                <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                                    <span className="text-xs font-bold text-gray-400">
                                        #
                                    </span>
                                </div>
                                <div>
                                    <p className="mb-0.5 text-xs uppercase tracking-wide text-gray-400">
                                        {field.label}
                                    </p>
                                    <div className="text-sm font-medium text-gray-800">
                                        {field.component}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Notes */}
            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">
                    {t('Notes')}
                </h3>

                <div className="rounded-lg bg-gray-50 p-4">
                    <RichTextEditor
                        content={notesForm.notes}
                        onChange={(content) =>
                            setNotesData('notes', content)
                        }
                        placeholder={t('Add notes...')}
                        className="min-h-[300px]"
                    />
                </div>

                <div className="mt-4 flex justify-end">
                    <Button
                        type="button"
                        disabled={notesProcessing}
                        onClick={() =>
                            putNotes(
                                route('lead.deals.update-notes', deal.id),
                                {
                                    preserveScroll: true,
                                },
                            )
                        }
                    >
                        {notesProcessing
                            ? t('Saving...')
                            : t('Save')}
                    </Button>
                </div>
            </div>

            {/* Emails and discussions */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-gray-900">
                            {t('Emails')}
                        </h3>

                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        size="sm"
                                        onClick={() =>
                                            setEmailModalOpen(true)
                                        }
                                    >
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Send Email')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>

                    <div className="max-h-[400px] space-y-3 overflow-y-auto">
                        {deal.emails && deal.emails.length > 0 ? (
                            deal.emails.map((email: any, index: number) => {
                                const cleanText = stripHtmlAndDecode(
                                    email.description,
                                );

                                return (
                                    <div
                                        key={email.id ?? index}
                                        className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                                    >
                                        <div className="mb-3 flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div className="rounded-full bg-gray-100 p-1">
                                                    <Mail className="h-3 w-3 text-gray-600" />
                                                </div>
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900">
                                                        {email.to}
                                                    </p>
                                                    <p className="text-xs text-gray-500">
                                                        {formatDateTime(
                                                            email.created_at,
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="rounded-lg border border-gray-100 bg-white p-3">
                                            <h4 className="mb-2 text-sm font-semibold text-gray-800">
                                                {email.subject}
                                            </h4>
                                            <div className="whitespace-pre-wrap text-xs leading-relaxed text-gray-700">
                                                {cleanText}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })
                        ) : (
                            <p className="py-4 text-center text-sm text-gray-500">
                                {t('No emails found')}
                            </p>
                        )}
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-gray-900">
                            {t('Discussions')}
                        </h3>

                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        size="sm"
                                        onClick={() =>
                                            setDiscussionModalOpen(true)
                                        }
                                    >
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Add Message')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>

                    <div className="max-h-[400px] space-y-3 overflow-y-auto">
                        {deal.discussions &&
                            deal.discussions.length > 0 ? (
                            deal.discussions.map(
                                (discussion: any, index: number) => (
                                    <div
                                        key={discussion.id ?? index}
                                        className="rounded-lg border border-gray-100 bg-gray-50 p-3"
                                    >
                                        <div className="mb-2 flex items-center gap-2">
                                            <Avatar className="h-7 w-7">
                                                {discussion.creator
                                                    ?.avatar ? (
                                                    <img
                                                        src={getImagePath(
                                                            discussion
                                                                .creator
                                                                .avatar,
                                                        )}
                                                        alt={
                                                            discussion
                                                                .creator
                                                                .name
                                                        }
                                                        className="h-full w-full rounded-full object-cover"
                                                    />
                                                ) : (
                                                    <AvatarFallback className="bg-primary/10 text-xs">
                                                        {discussion.creator
                                                            ?.name
                                                            ?.charAt(0)
                                                            .toUpperCase() ||
                                                            '?'}
                                                    </AvatarFallback>
                                                )}
                                            </Avatar>

                                            <span className="text-xs font-medium text-gray-700">
                                                {discussion.creator?.name ||
                                                    t('Unknown')}
                                            </span>

                                            <span className="ml-auto text-xs text-gray-400">
                                                {formatDateTime(
                                                    discussion.created_at,
                                                )}
                                            </span>
                                        </div>

                                        <p className="whitespace-pre-wrap text-sm leading-relaxed">
                                            {discussion.comment}
                                        </p>
                                    </div>
                                ),
                            )
                        ) : (
                            <p className="py-4 text-center text-sm text-gray-500">
                                {t('No discussions found')}
                            </p>
                        )}
                    </div>
                </div>
            </div>

            {/* Email modal */}
            <Dialog
                open={emailModalOpen}
                onOpenChange={(open) => {
                    setEmailModalOpen(open);

                    if (!open) {
                        resetEmail();
                    }
                }}
            >
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{t('Send Email')}</DialogTitle>
                    </DialogHeader>

                    <form
                        onSubmit={handleEmailSubmit}
                        className="space-y-4"
                    >
                        <div>
                            <FormLabel htmlFor="to" required>
                                {t('To')}
                            </FormLabel>
                            <Input
                                id="to"
                                type="email"
                                value={emailForm.to}
                                onChange={(event) =>
                                    setEmailData(
                                        'to',
                                        event.target.value,
                                    )
                                }
                                placeholder={t('Enter email address')}
                            />
                            <InputError message={emailErrors.to} />
                        </div>

                        <div className="flex items-end gap-2">
                            <div className="flex-1">
                                <FormLabel htmlFor="subject" required>
                                    {t('Subject')}
                                </FormLabel>
                                <Input
                                    id="subject"
                                    type="text"
                                    value={emailForm.subject}
                                    onChange={(event) =>
                                        setEmailData(
                                            'subject',
                                            event.target.value,
                                        )
                                    }
                                    placeholder={t('Enter subject')}
                                />
                                <InputError
                                    message={emailErrors.subject}
                                />
                            </div>

                            {emailSubjectAI.map((field) => (
                                <div key={field.id}>
                                    {field.component}
                                </div>
                            ))}
                        </div>

                        <div>
                            <div className="mb-2 flex items-center justify-between">
                                <FormLabel htmlFor="description" required>
                                    {t('Description')}
                                </FormLabel>

                                <div className="flex gap-2">
                                    {emailDescriptionAI.map((field) => (
                                        <div key={field.id}>
                                            {field.component}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <RichTextEditor
                                key={`email-editor-${emailEditorKey}`}
                                content={emailForm.description}
                                onChange={(content) =>
                                    setEmailData(
                                        'description',
                                        content,
                                    )
                                }
                                placeholder={t('Enter email content')}
                                className="mt-1"
                            />

                            <InputError
                                message={emailErrors.description}
                            />
                        </div>

                        <div className="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setEmailModalOpen(false);
                                    resetEmail();
                                }}
                            >
                                {t('Cancel')}
                            </Button>

                            <Button
                                type="submit"
                                disabled={emailProcessing}
                            >
                                {emailProcessing
                                    ? t('Sending...')
                                    : t('Send Email')}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Discussion modal */}
            <Dialog
                open={discussionModalOpen}
                onOpenChange={(open) => {
                    setDiscussionModalOpen(open);

                    if (!open) {
                        resetDiscussion();
                    }
                }}
            >
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Add Message')}</DialogTitle>
                    </DialogHeader>

                    <form
                        onSubmit={handleDiscussionSubmit}
                        className="space-y-4"
                    >
                        <div>
                            <FormLabel htmlFor="message" required>
                                {t('Message')}
                            </FormLabel>

                            <Textarea
                                id="message"
                                value={discussionForm.message}
                                onChange={(event) =>
                                    setDiscussionData(
                                        'message',
                                        event.target.value,
                                    )
                                }
                                placeholder={t('Enter your message')}
                                rows={3}
                            />

                            <InputError
                                message={discussionErrors.message}
                            />
                        </div>

                        <div className="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setDiscussionModalOpen(false);
                                    resetDiscussion();
                                }}
                            >
                                {t('Cancel')}
                            </Button>

                            <Button
                                type="submit"
                                disabled={discussionProcessing}
                            >
                                {discussionProcessing
                                    ? t('Saving...')
                                    : t('Save')}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Label modal */}
            <Dialog
                open={labelModalOpen}
                onOpenChange={setLabelModalOpen}
            >
                {labelModalOpen && (
                    <LabelView
                        deal={deal}
                        onSuccess={() => setLabelModalOpen(false)}
                    />
                )}
            </Dialog>
        </div>
    );
}