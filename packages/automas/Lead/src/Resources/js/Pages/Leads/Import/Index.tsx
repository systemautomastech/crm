import {
    ChangeEvent,
    DragEvent,
    FormEvent,
    useRef,
    useState,
} from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Download,
    Eye,
    FileSpreadsheet,
    LoaderCircle,
    Upload,
    X,
    Zap,
} from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import {
    RadioGroup,
    RadioGroupItem,
} from '@/components/ui/radio-group';

type ImportMode = 'preview' | 'direct';

interface UploadForm {
    file: File | null;
    mode: ImportMode;
}

interface ImportPageProps {
    downloadLink?: string;
}

const MAX_FILE_SIZE = 100 * 1024 * 1024;

export default function Index({ downloadLink = '' }: ImportPageProps) {
    const { t } = useTranslation();

    const inputRef = useRef<HTMLInputElement>(null);

    const [isDragging, setIsDragging] = useState(false);
    const [clientError, setClientError] = useState('');

    const {
        data,
        setData,
        post,
        processing,
        progress,
        errors,
        clearErrors,
        reset,
    } = useForm<UploadForm>({
        file: null,
        mode: 'preview',
    });

    const validateFile = (file: File): boolean => {
        setClientError('');
        clearErrors('file');

        const extension = file.name
            .split('.')
            .pop()
            ?.toLowerCase();

        if (!extension || !['csv', 'txt'].includes(extension)) {
            setClientError(
                t('Please select a CSV file.')
            );

            return false;
        }

        if (file.size <= 0) {
            setClientError(
                t('The selected CSV file is empty.')
            );

            return false;
        }

        if (file.size > MAX_FILE_SIZE) {
            setClientError(
                t('The CSV file may not be larger than 100 MB.')
            );

            return false;
        }

        return true;
    };

    const selectFile = (file?: File): void => {
        if (!file || !validateFile(file)) {
            return;
        }

        setData('file', file);
    };

    const handleFileInput = (
        event: ChangeEvent<HTMLInputElement>
    ): void => {
        selectFile(event.target.files?.[0]);
    };

    const handleDrop = (
        event: DragEvent<HTMLDivElement>
    ): void => {
        event.preventDefault();
        setIsDragging(false);

        const files = Array.from(
            event.dataTransfer.files
        );

        if (files.length > 1) {
            setClientError(
                t('Please upload only one CSV file at a time.')
            );

            return;
        }

        selectFile(files[0]);
    };

    const removeFile = (): void => {
        reset('file');
        clearErrors('file');
        setClientError('');

        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };

    const submit = (event: FormEvent): void => {
        event.preventDefault();

        if (!data.file) {
            setClientError(
                t('Please select a CSV file.')
            );

            return;
        }

        post(route('lead.leads.import.upload'), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes < 1024) {
            return `${bytes} B`;
        }

        const kilobytes = bytes / 1024;

        if (kilobytes < 1024) {
            return `${kilobytes.toFixed(1)} KB`;
        }

        return `${(kilobytes / 1024).toFixed(1)} MB`;
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label: t('CRM'),
                    url: route('lead.index'),
                },
                {
                    label: t('Leads'),
                    url: route('lead.leads.index'),
                },
                {
                    label: t('Bulk Import'),
                },
            ]}
            pageTitle={t('Import Leads')}
            pageActions={
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={processing}
                    onClick={() =>
                        router.get(
                            route('lead.leads.index')
                        )
                    }
                >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    {t('Back')}
                </Button>
            }
        >
            <Head title={t('Import Leads')} />

            <form
                onSubmit={submit}
                className="mx-auto space-y-4"
            >
                {(clientError || errors.file) && (
                    <div
                        role="alert"
                        className="flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive"
                    >
                        <AlertCircle className="h-4 w-4 shrink-0" />

                        <span>
                            {clientError || errors.file}
                        </span>
                    </div>
                )}

                <div className="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                    <Card className="shadow-none">
                        <CardHeader className="border-b px-5 py-4">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Upload className="h-4 w-4 text-primary" />
                                {t('CSV File')}
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="p-5">
                            <input
                                ref={inputRef}
                                type="file"
                                accept=".csv,.txt,text/csv,text/plain"
                                className="hidden"
                                disabled={processing}
                                onChange={handleFileInput}
                            />

                            {!data.file ? (
                                <div
                                    role="button"
                                    tabIndex={processing ? -1 : 0}
                                    className={[
                                        'flex min-h-[190px] cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed px-6 text-center outline-none transition-colors',
                                        isDragging
                                            ? 'border-primary bg-primary/5'
                                            : 'hover:border-primary/60 hover:bg-muted/20',
                                        processing
                                            ? 'cursor-not-allowed opacity-60'
                                            : 'focus-visible:ring-2 focus-visible:ring-primary',
                                    ].join(' ')}
                                    onClick={() => {
                                        if (!processing) {
                                            inputRef.current?.click();
                                        }
                                    }}
                                    onKeyDown={(event) => {
                                        if (
                                            !processing
                                            && (
                                                event.key === 'Enter'
                                                || event.key === ' '
                                            )
                                        ) {
                                            event.preventDefault();
                                            inputRef.current?.click();
                                        }
                                    }}
                                    onDragEnter={(event) => {
                                        event.preventDefault();

                                        if (!processing) {
                                            setIsDragging(true);
                                        }
                                    }}
                                    onDragOver={(event) => {
                                        event.preventDefault();

                                        if (!processing) {
                                            setIsDragging(true);
                                        }
                                    }}
                                    onDragLeave={(event) => {
                                        event.preventDefault();
                                        setIsDragging(false);
                                    }}
                                    onDrop={(event) => {
                                        if (!processing) {
                                            handleDrop(event);
                                        }
                                    }}
                                >
                                    <div className="mb-3 flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10">
                                        <FileSpreadsheet className="h-5 w-5 text-primary" />
                                    </div>

                                    <p className="text-sm font-medium">
                                        {isDragging
                                            ? t('Drop the file here')
                                            : t('Drop CSV here or click to browse')}
                                    </p>

                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {t('CSV or TXT · Maximum 100 MB')}
                                    </p>
                                </div>
                            ) : (
                                <div className="flex min-h-[190px] items-center">
                                    <div className="w-full rounded-lg border bg-muted/20 p-4">
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                                                <FileSpreadsheet className="h-5 w-5 text-primary" />
                                            </div>

                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <p className="truncate text-sm font-medium">
                                                        {data.file.name}
                                                    </p>

                                                    <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-600" />
                                                </div>

                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {formatFileSize(
                                                        data.file.size
                                                    )}
                                                </p>
                                            </div>

                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                disabled={processing}
                                                onClick={removeFile}
                                                aria-label={t('Remove file')}
                                            >
                                                <X className="h-4 w-4" />
                                            </Button>
                                        </div>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className="mt-4 w-full"
                                            disabled={processing}
                                            onClick={() =>
                                                inputRef.current?.click()
                                            }
                                        >
                                            {t('Change File')}
                                        </Button>
                                    </div>
                                </div>
                            )}

                            <div className="mt-4 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <span>
                                    {t('Required:')}
                                </span>

                                {[
                                    t('Name'),
                                    t('Subject'),
                                    t('Phone'),
                                ].map((field) => (
                                    <span
                                        key={field}
                                        className="rounded border bg-muted/30 px-2 py-1 font-medium text-foreground"
                                    >
                                        {field}
                                    </span>
                                ))}
                            </div>

                            <div className="mt-4 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <Download className="h-3 w-3" />
                                <span>
                                    {t('Download sample CSV file:')}
                                </span>
                                <a
                                    href={downloadLink || route('lead.leads.import.sample')}
                                    download="sample_lead_import.csv"
                                    className="inline-flex items-center rounded-md border bg-muted/30 px-3 py-1.5 font-medium text-foreground transition-colors hover:bg-muted/50"
                                >
                                    {t('Download')}
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-none">
                        <CardHeader className="border-b px-5 py-4">
                            <CardTitle className="text-base">
                                {t('Import Method')}
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="p-5">
                            <RadioGroup
                                value={data.mode}
                                disabled={processing}
                                onValueChange={(value) =>
                                    setData(
                                        'mode',
                                        value as ImportMode
                                    )
                                }
                                className="space-y-3"
                            >
                                <Label
                                    htmlFor="preview-mode"
                                    className={[
                                        'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                                        data.mode === 'preview'
                                            ? 'border-primary bg-primary/5'
                                            : 'hover:bg-muted/20',
                                        processing
                                            ? 'cursor-not-allowed opacity-60'
                                            : '',
                                    ].join(' ')}
                                >
                                    <RadioGroupItem
                                        id="preview-mode"
                                        value="preview"
                                        className="mt-0.5"
                                    />

                                    <Eye className="mt-0.5 h-4 w-4 shrink-0 text-primary" />

                                    <span className="min-w-0">
                                        <span className="flex items-center gap-2 text-sm font-medium">
                                            {t('Preview first')}

                                            <span className="rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary">
                                                {t('Recommended')}
                                            </span>
                                        </span>

                                        <span className="mt-1 block text-xs font-normal leading-5 text-muted-foreground">
                                            {t(
                                                'Review rows and map columns before importing.'
                                            )}
                                        </span>
                                    </span>
                                </Label>

                                <Label
                                    htmlFor="direct-mode"
                                    className={[
                                        'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                                        data.mode === 'direct'
                                            ? 'border-primary bg-primary/5'
                                            : 'hover:bg-muted/20',
                                        processing
                                            ? 'cursor-not-allowed opacity-60'
                                            : '',
                                    ].join(' ')}
                                >
                                    <RadioGroupItem
                                        id="direct-mode"
                                        value="direct"
                                        className="mt-0.5"
                                    />

                                    <Zap className="mt-0.5 h-4 w-4 shrink-0 text-primary" />

                                    <span className="min-w-0">
                                        <span className="text-sm font-medium">
                                            {t('Import directly')}
                                        </span>

                                        <span className="mt-1 block text-xs font-normal leading-5 text-muted-foreground">
                                            {t(
                                                'Use automatic mapping and process immediately.'
                                            )}
                                        </span>
                                    </span>
                                </Label>
                            </RadioGroup>

                            {errors.mode && (
                                <p className="mt-3 text-sm text-destructive">
                                    {errors.mode}
                                </p>
                            )}

                            <div className="mt-4 rounded-lg bg-muted/30 px-4 py-3">
                                <div className="flex items-center justify-between gap-4 text-sm">
                                    <span className="text-muted-foreground">
                                        {t('Selected file')}
                                    </span>

                                    <span className="max-w-[180px] truncate font-medium">
                                        {data.file
                                            ? data.file.name
                                            : t('None')}
                                    </span>
                                </div>

                                <div className="mt-2 flex items-center justify-between gap-4 text-sm">
                                    <span className="text-muted-foreground">
                                        {t('Mode')}
                                    </span>

                                    <span className="font-medium">
                                        {data.mode === 'preview'
                                            ? t('Preview')
                                            : t('Direct')}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {processing && (
                    <Card className="shadow-none">
                        <CardContent className="p-4">
                            <div className="mb-2 flex items-center justify-between gap-3">
                                <div className="flex items-center gap-2">
                                    <LoaderCircle className="h-4 w-4 animate-spin text-primary" />

                                    <span className="text-sm font-medium">
                                        {t('Uploading CSV...')}
                                    </span>
                                </div>

                                <span className="text-sm font-medium">
                                    {progress?.percentage ?? 0}%
                                </span>
                            </div>

                            <Progress
                                value={progress?.percentage ?? 0}
                                className="h-2"
                            />
                        </CardContent>
                    </Card>
                )}

                <div className="flex items-center justify-end gap-3 border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        disabled={processing}
                        onClick={() =>
                            router.get(
                                route('lead.leads.index')
                            )
                        }
                    >
                        {t('Cancel')}
                    </Button>

                    <Button
                        type="submit"
                        disabled={!data.file || processing}
                    >
                        {processing ? (
                            <>
                                <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                {t('Uploading...')}
                            </>
                        ) : (
                            <>
                                {data.mode === 'preview'
                                    ? t('Upload and Preview')
                                    : t('Start Import')}

                                <ArrowRight className="ml-2 h-4 w-4" />
                            </>
                        )}
                    </Button>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}