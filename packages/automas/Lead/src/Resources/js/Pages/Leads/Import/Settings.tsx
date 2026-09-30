import { FormEvent, useMemo } from 'react';
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
    ChevronRight,
    Copy,
    GitBranch,
    LoaderCircle,
    Settings2,
    UserRoundCheck,
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
import { Label } from '@/components/ui/label';
import {
    RadioGroup,
    RadioGroupItem,
} from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Pipeline {
    id: number;
    name: string;
}

interface Stage {
    id: number;
    name: string;
    pipeline_id: number;
}

interface SettingsProps {
    leadImport: {
        uuid: string;
        original_filename: string;
        duplicate_strategy: string;
        assignment_range_count: number;
        column_mapping: Record<string, number>;
    };

    pipelines: Pipeline[];
    stages: Stage[];

    defaults: {
        pipeline_id: number;
        stage_id: number;
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
}

interface SettingsForm {
    pipeline_id: string;
    stage_id: string;
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
}

export default function Settings({
    leadImport,
    pipelines,
    stages,
    defaults,
}: SettingsProps) {
    const { t } = useTranslation();

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm<SettingsForm>({
        pipeline_id: String(defaults.pipeline_id),
        stage_id: String(defaults.stage_id),
        duplicate_by: defaults.duplicate_by,
        duplicate_strategy:
            defaults.duplicate_strategy,
        is_active: defaults.is_active,
    });

    const availableStages = useMemo(
        () =>
            stages.filter(
                (stage) =>
                    String(stage.pipeline_id) ===
                    data.pipeline_id
            ),
        [data.pipeline_id, stages]
    );

    const changePipeline = (
        pipelineId: string
    ): void => {
        const pipelineStages = stages.filter(
            (stage) =>
                String(stage.pipeline_id) ===
                pipelineId
        );

        setData({
            ...data,
            pipeline_id: pipelineId,
            stage_id: pipelineStages[0]
                ? String(pipelineStages[0].id)
                : '',
        });
    };

    const submit = (
        event: FormEvent
    ): void => {
        event.preventDefault();

        post(
            route(
                'lead.leads.import.settings.store',
                leadImport.uuid
            ),
            {
                preserveScroll: true,
            }
        );
    };

    const hasErrors =
        errors.pipeline_id ||
        errors.stage_id ||
        errors.duplicate_by ||
        errors.duplicate_strategy ||
        errors.assignment_ranges;

    const duplicateByOptions: Array<{
        value: SettingsForm['duplicate_by'];
        label: string;
        description: string;
    }> = [
        {
            value: 'phone',
            label: t('Phone'),
            description: t('Recommended'),
        },
        {
            value: 'email',
            label: t('Email'),
            description: t('Email must be available'),
        },
        {
            value: 'phone_or_email',
            label: t('Phone or Email'),
            description: t('Match either value'),
        },
        {
            value: 'none',
            label: t('Do not check'),
            description: t('Treat every row as new'),
        },
    ];

    const duplicateStrategyOptions: Array<{
        value: SettingsForm['duplicate_strategy'];
        label: string;
        description: string;
    }> = [
        {
            value: 'skip',
            label: t('Skip'),
            description: t('Keep existing lead'),
        },
        {
            value: 'update',
            label: t('Update'),
            description: t('Update mapped fields'),
        },
        {
            value: 'create',
            label: t('Create another'),
            description: t('Create a new lead'),
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
                    label: t('Settings'),
                },
            ]}
            pageTitle={t('Import Settings')}
            pageActions={
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={processing}
                    onClick={() =>
                        router.get(
                            route(
                                'lead.leads.import.preview',
                                leadImport.uuid
                            )
                        )
                    }
                >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    {t('Back to Mapping')}
                </Button>
            }
        >
            <Head title={t('Import Settings')} />

            <form
                onSubmit={submit}
                className="mx-auto space-y-4 pb-20"
            >
                {/* Compact file summary */}
                <div className="flex flex-col gap-3 rounded-lg border bg-card px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex min-w-0 items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                            <Settings2 className="h-4 w-4 text-primary" />
                        </div>

                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold">
                                {leadImport.original_filename}
                            </p>

                            <div className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span>
                                    {Object.keys(
                                        leadImport.column_mapping
                                    ).length}{' '}
                                    {t('mapped fields')}
                                </span>

                                <span className="hidden h-1 w-1 rounded-full bg-muted-foreground/40 sm:block" />

                                <span>
                                    {
                                        leadImport.assignment_range_count
                                    }{' '}
                                    {t('assignment ranges')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Badge
                        variant="outline"
                        className="w-fit border-emerald-500/30 bg-emerald-500/5 text-emerald-700 dark:text-emerald-400"
                    >
                        <CheckCircle2 className="mr-1 h-3.5 w-3.5" />
                        {t('Mapping Ready')}
                    </Badge>
                </div>

                {/* Validation errors */}
                {hasErrors && (
                    <div
                        role="alert"
                        className="flex items-start gap-3 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-destructive"
                    >
                        <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />

                        <div className="space-y-1 text-sm">
                            {errors.pipeline_id && (
                                <p>{errors.pipeline_id}</p>
                            )}

                            {errors.stage_id && (
                                <p>{errors.stage_id}</p>
                            )}

                            {errors.duplicate_by && (
                                <p>{errors.duplicate_by}</p>
                            )}

                            {errors.duplicate_strategy && (
                                <p>
                                    {
                                        errors.duplicate_strategy
                                    }
                                </p>
                            )}

                            {errors.assignment_ranges && (
                                <p>
                                    {
                                        errors.assignment_ranges
                                    }
                                </p>
                            )}
                        </div>
                    </div>
                )}

                <div className="grid gap-4 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
                    {/* Pipeline and stage */}
                    <Card className="h-fit shadow-none">
                        <CardHeader className="border-b px-4 py-3">
                            <CardTitle className="flex items-center gap-2 text-sm font-semibold">
                                <GitBranch className="h-4 w-4 text-primary" />
                                {t('Pipeline and Stage')}
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="space-y-4 p-4">
                            <div>
                                <Label
                                    htmlFor="pipeline"
                                    className="text-xs font-medium"
                                >
                                    {t('Pipeline')}
                                    <span className="ml-1 text-destructive">
                                        *
                                    </span>
                                </Label>

                                <Select
                                    value={data.pipeline_id}
                                    disabled={processing}
                                    onValueChange={changePipeline}
                                >
                                    <SelectTrigger
                                        id="pipeline"
                                        className="mt-1.5"
                                    >
                                        <SelectValue
                                            placeholder={t(
                                                'Select pipeline'
                                            )}
                                        />
                                    </SelectTrigger>

                                    <SelectContent searchable>
                                        {pipelines.map(
                                            (pipeline) => (
                                                <SelectItem
                                                    key={
                                                        pipeline.id
                                                    }
                                                    value={String(
                                                        pipeline.id
                                                    )}
                                                >
                                                    {
                                                        pipeline.name
                                                    }
                                                </SelectItem>
                                            )
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label
                                    htmlFor="stage"
                                    className="text-xs font-medium"
                                >
                                    {t('Initial Stage')}
                                    <span className="ml-1 text-destructive">
                                        *
                                    </span>
                                </Label>

                                <Select
                                    value={data.stage_id}
                                    disabled={
                                        processing ||
                                        availableStages.length ===
                                            0
                                    }
                                    onValueChange={(value) =>
                                        setData(
                                            'stage_id',
                                            value
                                        )
                                    }
                                >
                                    <SelectTrigger
                                        id="stage"
                                        className="mt-1.5"
                                    >
                                        <SelectValue
                                            placeholder={t(
                                                'Select stage'
                                            )}
                                        />
                                    </SelectTrigger>

                                    <SelectContent searchable>
                                        {availableStages.map(
                                            (stage) => (
                                                <SelectItem
                                                    key={
                                                        stage.id
                                                    }
                                                    value={String(
                                                        stage.id
                                                    )}
                                                >
                                                    {stage.name}
                                                </SelectItem>
                                            )
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>

                            <p className="text-xs leading-5 text-muted-foreground">
                                {t(
                                    'Imported leads will start in this pipeline and stage.'
                                )}
                            </p>

                            <div className="flex items-start gap-2 rounded-md border border-amber-500/30 bg-amber-500/5 px-3 py-2.5 text-xs text-amber-800 dark:text-amber-300">
                                <UserRoundCheck className="mt-0.5 h-3.5 w-3.5 shrink-0" />

                                <span>
                                    {t(
                                        'Only rows covered by a valid user assignment range will be imported.'
                                    )}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Duplicate settings */}
                    <Card className="shadow-none">
                        <CardHeader className="border-b px-4 py-3">
                            <CardTitle className="flex items-center gap-2 text-sm font-semibold">
                                <Copy className="h-4 w-4 text-primary" />
                                {t('Duplicate Handling')}
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="space-y-5 p-4">
                            <div>
                                <div className="mb-2 flex items-center justify-between gap-3">
                                    <Label className="text-xs font-medium">
                                        {t('Detect duplicates by')}
                                    </Label>

                                    <span className="text-[11px] text-muted-foreground">
                                        {t('Choose one')}
                                    </span>
                                </div>

                                <RadioGroup
                                    value={data.duplicate_by}
                                    disabled={processing}
                                    onValueChange={(value) =>
                                        setData(
                                            'duplicate_by',
                                            value as SettingsForm['duplicate_by']
                                        )
                                    }
                                    className="grid gap-2 sm:grid-cols-2"
                                >
                                    {duplicateByOptions.map(
                                        (option) => {
                                            const selected =
                                                data.duplicate_by ===
                                                option.value;

                                            return (
                                                <Label
                                                    key={
                                                        option.value
                                                    }
                                                    htmlFor={`duplicate-by-${option.value}`}
                                                    className={[
                                                        'flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2.5 transition-colors',
                                                        selected
                                                            ? 'border-primary bg-primary/5'
                                                            : 'hover:bg-muted/30',
                                                        processing
                                                            ? 'cursor-not-allowed opacity-60'
                                                            : '',
                                                    ].join(' ')}
                                                >
                                                    <RadioGroupItem
                                                        id={`duplicate-by-${option.value}`}
                                                        value={
                                                            option.value
                                                        }
                                                    />

                                                    <span className="min-w-0">
                                                        <span className="block text-sm font-medium">
                                                            {
                                                                option.label
                                                            }
                                                        </span>

                                                        <span className="block truncate text-xs font-normal text-muted-foreground">
                                                            {
                                                                option.description
                                                            }
                                                        </span>
                                                    </span>
                                                </Label>
                                            );
                                        }
                                    )}
                                </RadioGroup>
                            </div>

                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center justify-between gap-3">
                                    <Label className="text-xs font-medium">
                                        {t(
                                            'When a duplicate is found'
                                        )}
                                    </Label>

                                    {data.duplicate_by ===
                                        'none' && (
                                        <span className="text-[11px] text-muted-foreground">
                                            {t('Not applicable')}
                                        </span>
                                    )}
                                </div>

                                <RadioGroup
                                    value={
                                        data.duplicate_strategy
                                    }
                                    disabled={
                                        processing ||
                                        data.duplicate_by ===
                                            'none'
                                    }
                                    onValueChange={(value) =>
                                        setData(
                                            'duplicate_strategy',
                                            value as SettingsForm['duplicate_strategy']
                                        )
                                    }
                                    className="grid gap-2 sm:grid-cols-3"
                                >
                                    {duplicateStrategyOptions.map(
                                        (option) => {
                                            const selected =
                                                data.duplicate_strategy ===
                                                option.value;

                                            return (
                                                <Label
                                                    key={
                                                        option.value
                                                    }
                                                    htmlFor={`duplicate-${option.value}`}
                                                    className={[
                                                        'flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2.5 transition-colors',
                                                        selected
                                                            ? 'border-primary bg-primary/5'
                                                            : 'hover:bg-muted/30',
                                                        processing ||
                                                        data.duplicate_by ===
                                                            'none'
                                                            ? 'cursor-not-allowed opacity-50'
                                                            : '',
                                                    ].join(' ')}
                                                >
                                                    <RadioGroupItem
                                                        id={`duplicate-${option.value}`}
                                                        value={
                                                            option.value
                                                        }
                                                    />

                                                    <span className="min-w-0">
                                                        <span className="block text-sm font-medium">
                                                            {
                                                                option.label
                                                            }
                                                        </span>

                                                        <span className="block truncate text-xs font-normal text-muted-foreground">
                                                            {
                                                                option.description
                                                            }
                                                        </span>
                                                    </span>
                                                </Label>
                                            );
                                        }
                                    )}
                                </RadioGroup>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Compact warning */}
                <div className="flex items-start gap-2 rounded-lg border-l-4 border-amber-500 bg-amber-500/5 px-4 py-3 text-sm">
                    <AlertCircle className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />

                    <div>
                        <span className="font-medium text-foreground">
                            {t('User assignment is required.')}
                        </span>{' '}

                        <span className="text-muted-foreground">
                            {t(
                                'Rows without a matching user assignment range will be skipped and included in the final report.'
                            )}
                        </span>
                    </div>
                </div>

                {/* Sticky actions */}
                <div className="sticky bottom-0 z-20 -mx-4 border-t bg-background/95 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-background/85 sm:mx-0 sm:rounded-lg sm:border">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400">
                            <CheckCircle2 className="h-4 w-4 shrink-0" />

                            <span>
                                {t(
                                    'Settings are ready to review.'
                                )}
                            </span>
                        </div>

                        <div className="flex items-center justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={processing}
                                onClick={() =>
                                    router.get(
                                        route(
                                            'lead.leads.import.preview',
                                            leadImport.uuid
                                        )
                                    )
                                }
                            >
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('Back')}
                            </Button>

                            <Button
                                type="submit"
                                disabled={
                                    processing ||
                                    !data.pipeline_id ||
                                    !data.stage_id
                                }
                            >
                                {processing ? (
                                    <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                ) : (
                                    <ChevronRight className="mr-2 h-4 w-4" />
                                )}

                                {t('Review Import')}
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}