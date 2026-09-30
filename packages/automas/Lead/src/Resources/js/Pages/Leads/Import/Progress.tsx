import { useEffect, useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    AlertCircle,
    CheckCircle2,
    Clock3,
    FileSpreadsheet,
    LoaderCircle,
    RefreshCw,
    Rows3,
    UserRoundX,
    XCircle,
} from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress as ProgressBar } from '@/components/ui/progress';

interface LeadImportProgress {
    uuid: string;
    original_filename: string;

    status:
        | 'pending'
        | 'preparing'
        | 'queued'
        | 'processing'
        | 'completed'
        | 'completed_with_errors'
        | 'failed'
        | 'cancelled'
        | string;

    total_rows: number;
    total_chunks: number;
    completed_chunks: number;

    processed_rows: number;
    inserted_rows: number;
    updated_rows: number;
    duplicate_rows: number;
    skipped_rows: number;
    skipped_unassigned_rows: number;
    failed_rows: number;

    failure_message?: string | null;
}

interface FailureItem {
    id: number;
    row_number: number;
    row_data: Record<string, any> | string[] | null;
    errors: string[];
}

interface ProgressProps {
    leadImport: LeadImportProgress;
    failures?: FailureItem[];
}

export default function Progress({
    leadImport,
    failures = [],
}: ProgressProps) {
    const { t } = useTranslation();

    const [importData, setImportData] = useState<LeadImportProgress>(leadImport);
    const [failureData, setFailureData] = useState<FailureItem[]>(failures);

    const isFinished = [
        'completed',
        'completed_with_errors',
        'failed',
        'cancelled',
    ].includes(importData.status);

    const rowPercentage = useMemo(() => {
        if (importData.total_rows <= 0) {
            return 0;
        }

        return Math.min(
            100,
            Math.round(
                (
                    importData.processed_rows /
                    importData.total_rows
                ) * 100
            )
        );
    }, [
        importData.processed_rows,
        importData.total_rows,
    ]);

    const chunkPercentage = useMemo(() => {
        if (importData.total_chunks <= 0) {
            return 0;
        }

        return Math.min(
            100,
            Math.round(
                (
                    importData.completed_chunks /
                    importData.total_chunks
                ) * 100
            )
        );
    }, [
        importData.completed_chunks,
        importData.total_chunks,
    ]);

    const fetchStatusData = async () => {
        try {
            const response = await fetch(
                route('lead.leads.import.status-data', importData.uuid),
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );
            if (response.ok) {
                const result = await response.json();
                if (result.leadImport) {
                    setImportData(result.leadImport);
                }
                if (result.failures) {
                    setFailureData(result.failures);
                }
            }
        } catch (err) {
            // Ignore temporary network errors during polling
        }
    };

    useEffect(() => {
        if (isFinished) {
            return;
        }

        const interval = window.setInterval(() => {
            fetchStatusData();
        }, 2000);

        return () => {
            window.clearInterval(interval);
        };
    }, [isFinished, importData.uuid]);

    const formatNumber = (
        value: number
    ): string => {
        return new Intl.NumberFormat().format(
            value || 0
        );
    };

    const statusLabel: Record<string, string> = {
        pending: t('Pending'),
        preparing: t('Preparing CSV'),
        queued: t('Queued'),
        processing: t('Processing'),
        completed: t('Completed'),
        completed_with_errors: t(
            'Completed with errors'
        ),
        failed: t('Failed'),
        cancelled: t('Cancelled'),
    };

    const statusClass: Record<string, string> = {
        pending:
            'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-400',
        preparing:
            'border-blue-500/30 bg-blue-500/10 text-blue-700 dark:text-blue-400',
        queued:
            'border-indigo-500/30 bg-indigo-500/10 text-indigo-700 dark:text-indigo-400',
        processing:
            'border-primary/30 bg-primary/10 text-primary',
        completed:
            'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
        completed_with_errors:
            'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-400',
        failed:
            'border-destructive/30 bg-destructive/10 text-destructive',
        cancelled:
            'border-border bg-muted text-muted-foreground',
    };

    const statusIcon = () => {
        if (importData.status === 'completed') {
            return (
                <CheckCircle2 className="mr-1 h-3.5 w-3.5" />
            );
        }

        if (importData.status === 'failed') {
            return (
                <XCircle className="mr-1 h-3.5 w-3.5" />
            );
        }

        if (
            importData.status === 'pending' ||
            importData.status === 'queued'
        ) {
            return (
                <Clock3 className="mr-1 h-3.5 w-3.5" />
            );
        }

        if (
            importData.status === 'cancelled'
        ) {
            return (
                <XCircle className="mr-1 h-3.5 w-3.5" />
            );
        }

        return (
            <LoaderCircle className="mr-1 h-3.5 w-3.5 animate-spin" />
        );
    };

    const statistics = [
        {
            label: t('Imported'),
            value: importData.inserted_rows,
            valueClass:
                'text-emerald-700 dark:text-emerald-400',
        },
        {
            label: t('Updated'),
            value: importData.updated_rows,
            valueClass:
                'text-blue-700 dark:text-blue-400',
        },
        {
            label: t('Duplicates'),
            value: importData.duplicate_rows,
            valueClass:
                'text-amber-700 dark:text-amber-400',
        },
        {
            label: t('Failed'),
            value: importData.failed_rows,
            valueClass: 'text-destructive',
        },
        {
            label: t('No Assigned User'),
            value:
                importData.skipped_unassigned_rows,
            valueClass:
                'text-amber-700 dark:text-amber-400',
        },
        {
            label: t('Total Skipped'),
            value: importData.skipped_rows,
            valueClass: 'text-foreground',
        },
    ];

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
                    label: t('Progress'),
                },
            ]}
            pageTitle={t(
                'Lead Import Progress'
            )}
            pageActions={
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() =>
                        router.get(
                            route(
                                'lead.leads.index'
                            )
                        )
                    }
                >
                    {t('Back to Leads')}
                </Button>
            }
        >
            <Head
                title={t(
                    'Lead Import Progress'
                )}
            />

            <div className="mx-auto space-y-4">
                {/* File and status */}
                <div className="flex flex-col gap-3 rounded-lg border bg-card px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex min-w-0 items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                            <FileSpreadsheet className="h-4 w-4 text-primary" />
                        </div>

                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold">
                                {
                                    importData.original_filename
                                }
                            </p>

                            <div className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span>
                                    {formatNumber(
                                        importData.total_rows
                                    )}{' '}
                                    {t('rows')}
                                </span>

                                <span className="hidden h-1 w-1 rounded-full bg-muted-foreground/40 sm:block" />

                                <span>
                                    {formatNumber(
                                        importData.total_chunks
                                    )}{' '}
                                    {t('chunks')}
                                </span>

                                <span className="hidden h-1 w-1 rounded-full bg-muted-foreground/40 sm:block" />

                                <span>
                                    {formatNumber(
                                        importData.processed_rows
                                    )}{' '}
                                    {t('processed')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Badge
                        variant="outline"
                        className={[
                            'w-fit',
                            statusClass[
                                importData.status
                            ] ??
                                'border-border bg-muted text-muted-foreground',
                        ].join(' ')}
                    >
                        {statusIcon()}

                        {statusLabel[
                            importData.status
                        ] ?? importData.status}
                    </Badge>
                </div>

                {/* Failure message */}
                {importData.failure_message && (
                    <div
                        role="alert"
                        className="flex items-start gap-3 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-destructive"
                    >
                        <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />

                        <div>
                            <p className="text-sm font-medium">
                                {t('Import failed')}
                            </p>

                            <p className="mt-0.5 text-sm">
                                {
                                    importData.failure_message
                                }
                            </p>
                        </div>
                    </div>
                )}

                {/* Main progress */}
                <div className="rounded-lg border bg-card p-4">
                    <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm font-medium">
                                {t('Import Progress')}
                            </p>

                            <p className="mt-0.5 text-xs text-muted-foreground">
                                {formatNumber(
                                    importData.processed_rows
                                )}
                                {' / '}
                                {formatNumber(
                                    importData.total_rows
                                )}{' '}
                                {t('rows processed')}
                            </p>
                        </div>

                        <p className="text-xl font-semibold">
                            {rowPercentage}%
                        </p>
                    </div>

                    <ProgressBar
                        value={rowPercentage}
                        className="h-2"
                    />

                    <div className="mt-4 grid gap-3 border-t pt-4 sm:grid-cols-2">
                        <div className="flex items-center justify-between rounded-md bg-muted/30 px-3 py-2">
                            <span className="text-xs text-muted-foreground">
                                {t('Rows processed')}
                            </span>

                            <span className="text-sm font-medium">
                                {formatNumber(
                                    importData.processed_rows
                                )}
                                {' / '}
                                {formatNumber(
                                    importData.total_rows
                                )}
                            </span>
                        </div>

                        <div className="flex items-center justify-between rounded-md bg-muted/30 px-3 py-2">
                            <span className="text-xs text-muted-foreground">
                                {t('Chunks completed')}
                            </span>

                            <span className="text-sm font-medium">
                                {formatNumber(
                                    importData.completed_chunks
                                )}
                                {' / '}
                                {formatNumber(
                                    importData.total_chunks
                                )}
                                {' · '}
                                {chunkPercentage}%
                            </span>
                        </div>
                    </div>
                </div>

                {/* Statistics */}
                <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                    {statistics.map(
                        (statistic) => (
                            <div
                                key={
                                    statistic.label
                                }
                                className="rounded-lg border bg-card p-3"
                            >
                                <p className="truncate text-xs text-muted-foreground">
                                    {statistic.label}
                                </p>

                                <p
                                    className={[
                                        'mt-1 text-lg font-semibold',
                                        statistic.valueClass,
                                    ].join(' ')}
                                >
                                    {formatNumber(
                                        statistic.value
                                    )}
                                </p>
                            </div>
                        )
                    )}
                </div>

                {/* Compact skip details */}
                <div className="grid gap-3 sm:grid-cols-2">
                    <div className="flex items-center justify-between rounded-lg border bg-card px-4 py-3">
                        <div>
                            <p className="text-xs text-muted-foreground">
                                {t(
                                    'Skipped: No Assigned User'
                                )}
                            </p>

                            <p className="mt-1 text-lg font-semibold text-amber-700 dark:text-amber-400">
                                {formatNumber(
                                    importData.skipped_unassigned_rows
                                )}
                            </p>
                        </div>

                        <UserRoundX className="h-5 w-5 text-amber-600" />
                    </div>

                    <div className="flex items-center justify-between rounded-lg border bg-card px-4 py-3">
                        <div>
                            <p className="text-xs text-muted-foreground">
                                {t('Total Skipped')}
                            </p>

                            <p className="mt-1 text-lg font-semibold">
                                {formatNumber(
                                    importData.skipped_rows
                                )}
                            </p>
                        </div>

                        <Rows3 className="h-5 w-5 text-muted-foreground" />
                    </div>
                </div>

                {/* Running notice */}
                {!isFinished && (
                    <div className="flex items-center gap-3 rounded-lg border-l-4 border-primary bg-primary/5 px-4 py-3">
                        <LoaderCircle className="h-4 w-4 shrink-0 animate-spin text-primary" />

                        <p className="text-sm">
                            <span className="font-medium">
                                {t(
                                    'Import is running in the background.'
                                )}
                            </span>{' '}

                            <span className="text-muted-foreground">
                                {t(
                                    'Progress updates live automatically.'
                                )}
                            </span>
                        </p>
                    </div>
                )}

                {/* Completed notice */}
                {importData.status ===
                    'completed' && (
                    <div className="flex items-center gap-3 rounded-lg border-l-4 border-emerald-500 bg-emerald-500/5 px-4 py-3">
                        <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-600" />

                        <p className="text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            {t(
                                'The lead import completed successfully.'
                            )}
                        </p>
                    </div>
                )}

                {/* Completed with errors */}
                {importData.status ===
                    'completed_with_errors' && (
                    <div className="flex items-center gap-3 rounded-lg border-l-4 border-amber-500 bg-amber-500/5 px-4 py-3">
                        <AlertCircle className="h-4 w-4 shrink-0 text-amber-600" />

                        <p className="text-sm font-medium text-amber-700 dark:text-amber-400">
                            {t(
                                'The import completed with some errors.'
                            )}
                        </p>
                    </div>
                )}

                {/* Failed / Skipped Rows breakdown table */}
                {failureData && failureData.length > 0 && (
                    <div className="rounded-lg border bg-card p-4 space-y-3">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-sm font-semibold text-destructive flex items-center gap-2">
                                    <AlertCircle className="h-4 w-4" />
                                    {t('Unimported / Failed Rows Details')}
                                </h3>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {t('List of rows that were skipped or encountered errors during processing.')}
                                </p>
                            </div>
                            <Badge variant="outline" className="text-destructive border-destructive/30">
                                {failureData.length} {t('records')}
                            </Badge>
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <table className="w-full text-left text-xs">
                                <thead className="bg-muted/50 text-muted-foreground uppercase text-[10px] tracking-wider border-b">
                                    <tr>
                                        <th className="px-3 py-2 w-20">{t('Row #')}</th>
                                        <th className="px-3 py-2">{t('Error Reason')}</th>
                                        <th className="px-3 py-2">{t('Provided Row Values')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {failureData.map((item) => {
                                        const valuesStr = Array.isArray(item.row_data)
                                            ? item.row_data.join(', ')
                                            : typeof item.row_data === 'object' && item.row_data !== null
                                            ? Object.entries(item.row_data)
                                                  .map(([k, v]) => `${k}: ${v}`)
                                                  .join(' | ')
                                            : String(item.row_data || '');

                                        return (
                                            <tr key={item.id} className="hover:bg-muted/30">
                                                <td className="px-3 py-2 font-mono font-medium text-foreground">
                                                    #{item.row_number}
                                                </td>
                                                <td className="px-3 py-2 font-medium text-destructive">
                                                    {Array.isArray(item.errors)
                                                        ? item.errors.join(', ')
                                                        : item.errors}
                                                </td>
                                                <td className="px-3 py-2 text-muted-foreground font-mono truncate max-w-xs">
                                                    {valuesStr}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="sticky bottom-0 z-20 -mx-4 border-t bg-background/95 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-background/85 sm:mx-0 sm:rounded-lg sm:border">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-xs text-muted-foreground">
                            {isFinished
                                ? t(
                                      'Import processing has finished.'
                                  )
                                : t(
                                      'Progress updates live automatically.'
                                  )}
                        </div>

                        <div className="flex items-center justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.get(
                                        route(
                                            'lead.leads.index'
                                        )
                                    )
                                }
                            >
                                {t('Back to Leads')}
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                onClick={fetchStatusData}
                            >
                                <RefreshCw className="mr-2 h-4 w-4" />
                                {t('Refresh')}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}