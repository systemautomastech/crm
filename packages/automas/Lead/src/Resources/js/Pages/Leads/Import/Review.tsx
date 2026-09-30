import { useMemo } from 'react';
import {
    Head,
    router,
    useForm,
} from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    Copy,
    FileSpreadsheet,
    GitBranch,
    LoaderCircle,
    Play,
    ShieldCheck,
    UserRoundCheck,
    UserRoundX,
} from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface AssignmentUser {
    id: number;
    name: string;
    email?: string | null;
}

interface AssignmentRange {
    from_row: number;
    to_row: number;
    effective_rows: number;
    user: AssignmentUser | null;
}

interface ReviewProps {
    leadImport: {
        uuid: string;
        original_filename: string;
        file_size: number;
        status: string;
        column_mapping: Record<string, number>;
        duplicate_strategy: string;
    };

    summary: {
        total_rows: number;
        assigned_rows: number;
        skipped_unassigned_rows: number;
        mapped_fields: number;

        pipeline: {
            id: number;
            name: string;
        };

        stage: {
            id: number;
            name: string;
        };

        duplicate_by:
            | 'phone'
            | 'email'
            | 'phone_or_email'
            | 'none';

        duplicate_strategy:
            | 'skip'
            | 'update'
            | 'create';

        is_active: boolean;
    };

    assignmentRanges: AssignmentRange[];
}

export default function Review({
    leadImport,
    summary,
    assignmentRanges,
}: ReviewProps) {
    const { t } = useTranslation();

    const canStartImport =
        summary.total_rows > 0 &&
        summary.assigned_rows > 0 &&
        summary.mapped_fields >= 3;

    const assignedPercentage = useMemo(() => {
        if (summary.total_rows <= 0) {
            return 0;
        }

        return Math.round(
            (
                summary.assigned_rows /
                summary.total_rows
            ) * 100
        );
    }, [
        summary.assigned_rows,
        summary.total_rows,
    ]);

    const formatNumber = (
        value: number
    ): string => {
        return new Intl.NumberFormat().format(
            value
        );
    };

    const formatFileSize = (
        bytes: number
    ): string => {
        if (!bytes || bytes <= 0) {
            return '0 B';
        }

        if (bytes < 1024) {
            return `${bytes} B`;
        }

        const kilobytes = bytes / 1024;

        if (kilobytes < 1024) {
            return `${kilobytes.toFixed(1)} KB`;
        }

        return `${(
            kilobytes / 1024
        ).toFixed(1)} MB`;
    };

    const duplicateByLabel = {
        phone: t('Phone'),
        email: t('Email'),
        phone_or_email: t('Phone or Email'),
        none: t('No duplicate check'),
    }[summary.duplicate_by];

    const duplicateStrategyLabel = {
        skip: t('Skip duplicate'),
        update: t('Update existing'),
        create: t('Create another lead'),
    }[summary.duplicate_strategy];

    const {
        post,
        processing,
        errors,
    } = useForm({});

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label: t('CRM'),
                    url: route('lead.index'),
                },
                {
                    label: t('Leads'),
                    url: route(
                        'lead.leads.index'
                    ),
                },
                {
                    label: t('Bulk Import'),
                    url: route(
                        'lead.leads.import.index'
                    ),
                },
                {
                    label: t('Review'),
                },
            ]}
            pageTitle={t('Review Import')}
            pageActions={
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={processing}
                    onClick={() =>
                        router.get(
                            route(
                                'lead.leads.import.settings',
                                leadImport.uuid
                            )
                        )
                    }
                >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    {t('Back to Settings')}
                </Button>
            }
        >
            <Head title={t('Review Import')} />

            <div className="mx-auto space-y-4 pb-20">
                {/* File summary */}
                <div className="flex flex-col gap-3 rounded-lg border bg-card px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex min-w-0 items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                            <FileSpreadsheet className="h-4 w-4 text-primary" />
                        </div>

                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold">
                                {leadImport.original_filename}
                            </p>

                            <div className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span>
                                    {formatFileSize(
                                        leadImport.file_size
                                    )}
                                </span>

                                <span className="hidden h-1 w-1 rounded-full bg-muted-foreground/40 sm:block" />

                                <span>
                                    {formatNumber(
                                        summary.total_rows
                                    )}{' '}
                                    {t('rows')}
                                </span>

                                <span className="hidden h-1 w-1 rounded-full bg-muted-foreground/40 sm:block" />

                                <span>
                                    {summary.mapped_fields}{' '}
                                    {t('mapped fields')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Badge
                        variant="outline"
                        className={[
                            'w-fit',
                            canStartImport
                                ? 'border-emerald-500/30 bg-emerald-500/5 text-emerald-700 dark:text-emerald-400'
                                : 'border-destructive/30 bg-destructive/5 text-destructive',
                        ].join(' ')}
                    >
                        {canStartImport ? (
                            <CheckCircle2 className="mr-1 h-3.5 w-3.5" />
                        ) : (
                            <AlertCircle className="mr-1 h-3.5 w-3.5" />
                        )}

                        {canStartImport
                            ? t('Ready to Import')
                            : t('Not Ready')}
                    </Badge>
                </div>

                {/* Import error */}
                {errors.import && (
                    <div
                        role="alert"
                        className="flex items-start gap-2 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive"
                    >
                        <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{errors.import}</span>
                    </div>
                )}

                {/* Compact statistics */}
                <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs text-muted-foreground">
                            {t('Total Rows')}
                        </p>

                        <p className="mt-1 text-xl font-semibold">
                            {formatNumber(
                                summary.total_rows
                            )}
                        </p>
                    </div>

                    <div className="rounded-lg border bg-card p-4">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="text-xs text-muted-foreground">
                                    {t('Will Import')}
                                </p>

                                <p className="mt-1 text-xl font-semibold text-emerald-700 dark:text-emerald-400">
                                    {formatNumber(
                                        summary.assigned_rows
                                    )}
                                </p>

                                <p className="mt-0.5 text-[11px] text-muted-foreground">
                                    {assignedPercentage}%
                                </p>
                            </div>

                            <UserRoundCheck className="h-4 w-4 text-emerald-600" />
                        </div>
                    </div>

                    <div className="rounded-lg border bg-card p-4">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="text-xs text-muted-foreground">
                                    {t('Will Skip')}
                                </p>

                                <p className="mt-1 text-xl font-semibold text-amber-700 dark:text-amber-400">
                                    {formatNumber(
                                        summary.skipped_unassigned_rows
                                    )}
                                </p>

                                <p className="mt-0.5 text-[11px] text-muted-foreground">
                                    {t('No assigned user')}
                                </p>
                            </div>

                            <UserRoundX className="h-4 w-4 text-amber-600" />
                        </div>
                    </div>

                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs text-muted-foreground">
                            {t('Mapped Fields')}
                        </p>

                        <p className="mt-1 text-xl font-semibold">
                            {summary.mapped_fields}
                        </p>
                    </div>
                </div>

                {/* Skipped rows warning */}
                {summary.skipped_unassigned_rows >
                    0 && (
                    <div className="flex items-start gap-2 rounded-lg border-l-4 border-amber-500 bg-amber-500/5 px-4 py-3 text-sm">
                        <AlertCircle className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />

                        <div>
                            <span className="font-medium text-foreground">
                                {formatNumber(
                                    summary.skipped_unassigned_rows
                                )}{' '}
                                {t(
                                    'rows will be skipped.'
                                )}
                            </span>{' '}

                            <span className="text-muted-foreground">
                                {t(
                                    'These rows are outside all user assignment ranges.'
                                )}
                            </span>
                        </div>
                    </div>
                )}

                <div className="grid gap-4 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)]">
                    {/* Import settings */}
                    <Card className="h-fit shadow-none">
                        <CardHeader className="border-b px-4 py-3">
                            <CardTitle className="text-sm font-semibold">
                                {t('Import Settings')}
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="divide-y p-0">
                            <div className="px-4 py-3">
                                <div className="mb-2 flex items-center gap-2">
                                    <GitBranch className="h-4 w-4 text-primary" />

                                    <p className="text-xs font-medium">
                                        {t('Destination')}
                                    </p>
                                </div>

                                <dl className="space-y-2 text-sm">
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            {t('Pipeline')}
                                        </dt>

                                        <dd className="truncate font-medium">
                                            {
                                                summary.pipeline
                                                    .name
                                            }
                                        </dd>
                                    </div>

                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            {t('Initial Stage')}
                                        </dt>

                                        <dd className="truncate font-medium">
                                            {summary.stage.name}
                                        </dd>
                                    </div>

                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            {t('Lead Status')}
                                        </dt>

                                        <dd>
                                            <Badge
                                                variant={
                                                    summary.is_active
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                                className="h-5 text-[10px]"
                                            >
                                                {summary.is_active
                                                    ? t('Active')
                                                    : t('Inactive')}
                                            </Badge>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div className="px-4 py-3">
                                <div className="mb-2 flex items-center gap-2">
                                    <Copy className="h-4 w-4 text-primary" />

                                    <p className="text-xs font-medium">
                                        {t('Duplicate Handling')}
                                    </p>
                                </div>

                                <dl className="space-y-2 text-sm">
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            {t('Detect By')}
                                        </dt>

                                        <dd className="text-right font-medium">
                                            {duplicateByLabel}
                                        </dd>
                                    </div>

                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            {t('Action')}
                                        </dt>

                                        <dd className="text-right font-medium">
                                            {summary.duplicate_by ===
                                            'none'
                                                ? t(
                                                      'Not applicable'
                                                  )
                                                : duplicateStrategyLabel}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Assignment plan */}
                    <Card className="min-w-0 shadow-none">
                        <CardHeader className="border-b px-4 py-3">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-semibold">
                                    <UserRoundCheck className="h-4 w-4 text-primary" />
                                    {t('User Assignment Plan')}
                                </CardTitle>

                                <Badge variant="outline">
                                    {assignmentRanges.length}{' '}
                                    {t('ranges')}
                                </Badge>
                            </div>
                        </CardHeader>

                        <CardContent className="p-0">
                            <div className="max-h-[360px] overflow-auto">
                                <table className="w-full min-w-[560px] text-sm">
                                    <thead className="sticky top-0 z-10 bg-muted/95 text-xs">
                                        <tr className="border-b">
                                            <th className="px-4 py-2.5 text-left font-medium">
                                                {t('Range')}
                                            </th>

                                            <th className="px-4 py-2.5 text-left font-medium">
                                                {t('Assigned User')}
                                            </th>

                                            <th className="px-4 py-2.5 text-right font-medium">
                                                {t('Rows')}
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody className="divide-y">
                                        {assignmentRanges.map(
                                            (
                                                range,
                                                index
                                            ) => (
                                                <tr
                                                    key={`${range.from_row}-${range.to_row}-${index}`}
                                                    className="hover:bg-muted/20"
                                                >
                                                    <td className="px-4 py-3">
                                                        <span className="font-medium">
                                                            {
                                                                range.from_row
                                                            }
                                                            {' – '}
                                                            {
                                                                range.to_row
                                                            }
                                                        </span>
                                                    </td>

                                                    <td className="px-4 py-3">
                                                        <div className="min-w-0">
                                                            <p
                                                                className={[
                                                                    'truncate font-medium',
                                                                    !range.user
                                                                        ? 'text-destructive'
                                                                        : '',
                                                                ].join(
                                                                    ' '
                                                                )}
                                                            >
                                                                {range
                                                                    .user
                                                                    ?.name ||
                                                                    t(
                                                                        'Invalid user'
                                                                    )}
                                                            </p>

                                                            {range
                                                                .user
                                                                ?.email && (
                                                                <p className="truncate text-xs text-muted-foreground">
                                                                    {
                                                                        range
                                                                            .user
                                                                            .email
                                                                    }
                                                                </p>
                                                            )}
                                                        </div>
                                                    </td>

                                                    <td className="px-4 py-3 text-right font-medium">
                                                        {formatNumber(
                                                            range.effective_rows
                                                        )}
                                                    </td>
                                                </tr>
                                            )
                                        )}

                                        {assignmentRanges.length ===
                                            0 && (
                                            <tr>
                                                <td
                                                    colSpan={
                                                        3
                                                    }
                                                    className="px-4 py-10 text-center text-sm text-muted-foreground"
                                                >
                                                    {t(
                                                        'No assignment ranges found.'
                                                    )}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Background processing notice */}
                {/* <div className="flex items-start gap-2 rounded-lg border bg-muted/20 px-4 py-3 text-xs text-muted-foreground">
                    <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                    <span>
                        {t(
                            'The import will run in the background. You can leave this page after starting it.'
                        )}
                    </span>
                </div> */}

                {/* Sticky actions */}
                <div className="sticky bottom-0 z-20 -mx-4 border-t bg-background/95 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-background/85 sm:mx-0">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        {canStartImport ? (
                            <div className="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400">
                                <CheckCircle2 className="h-4 w-4 shrink-0" />

                                <span>
                                    {formatNumber(
                                        summary.assigned_rows
                                    )}{' '}
                                    {t(
                                        'rows are ready for import.'
                                    )}
                                </span>
                            </div>
                        ) : (
                            <div className="flex items-center gap-2 text-xs text-destructive">
                                <AlertCircle className="h-4 w-4 shrink-0" />

                                <span>
                                    {t(
                                        'No assigned rows are available to import.'
                                    )}
                                </span>
                            </div>
                        )}

                        <div className="flex items-center justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={processing}
                                onClick={() =>
                                    router.get(
                                        route(
                                            'lead.leads.import.settings',
                                            leadImport.uuid
                                        )
                                    )
                                }
                            >
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('Back')}
                            </Button>

                            <Button
                                type="button"
                                disabled={
                                    !canStartImport ||
                                    processing
                                }
                                onClick={() =>
                                    post(
                                        route(
                                            'lead.leads.import.start',
                                            leadImport.uuid
                                        ),
                                        {
                                            preserveScroll: true,
                                        }
                                    )
                                }
                            >
                                {processing ? (
                                    <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                ) : (
                                    <Play className="mr-2 h-4 w-4" />
                                )}

                                {processing
                                    ? t(
                                          'Starting Import...'
                                      )
                                    : t('Start Import')}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}