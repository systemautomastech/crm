import { useForm, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MultiSelectEnhanced } from '@/components/ui/multi-select-enhanced';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import InputError from '@/components/ui/input-error';
import { CreateProjectTaskFormData, Project, Milestone, TaskStage } from './types';
import { useFormFields } from '@/hooks/useFormFields';

interface CreateProps {
    onSuccess: () => void;
    project?: Project;
    milestones: Milestone[];
    teamMembers: Array<{
        id: number;
        name: string;
    }>;
    taskStages?: TaskStage[];
    preSelectedStageId?: number;
}

export default function Create({ onSuccess, project, milestones, teamMembers, taskStages = [], preSelectedStageId }: CreateProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const { data, setData, post, processing, errors } = useForm<CreateProjectTaskFormData>({
        project_id: project?.id || 0,
        milestone_id: undefined,
        title: '',
        priority: 'Medium',
        assigned_to: [],
        duration: '',
        description: '',
        stage_id: preSelectedStageId || (taskStages.length > 0 ? taskStages[0].id : undefined)
    });

    // Calendar sync fields
    const calendarFields = useFormFields('getCalendarSyncFields', data, setData, errors, 'create', t, 'Taskly');

    // AI hooks for title and description fields
    const titleAI = useFormFields('aiField', data, setData, errors, 'create', 'title', 'Title', 'taskly', 'task');
    const descriptionAI = useFormFields('aiField', data, setData, errors, 'create', 'description', 'Description', 'taskly', 'task');
    const customFields = useFormFields('getCustomFields', { ...data, module: 'Taskly', sub_module: 'Tasks' }, setData, errors, 'create', t);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('project.tasks.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Create Task')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="project">{t('Project')}</Label>
                    <Input
                        id="project"
                        value={project?.name || ''}
                        disabled
                        className="bg-gray-50"
                        required
                    />
                </div>

                <div>
                    <Label htmlFor="milestone_id" required>{t('Milestone')}</Label>
                    <Select value={data.milestone_id?.toString() || ''} onValueChange={(value) => setData('milestone_id', value ? parseInt(value) : undefined)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select milestone')} />
                        </SelectTrigger>
                        <SelectContent>
                            {milestones.map((milestone) => (
                                <SelectItem key={milestone.id} value={milestone.id.toString()}>
                                    {milestone.title}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {milestones.length === 0 && auth?.user?.permissions?.includes('create-project-milestone') && (
                        <p className="text-xs text-gray-500 mb-1">
                            {t('Create milestone here.')} <button type="button" onClick={(e) => { e.preventDefault(); router.get(route('project.show', project?.id)); }} className="text-blue-600 hover:underline">{t('Create milestone')}</button>
                        </p>
                    )}
                    <InputError message={errors.milestone_id} />
                </div>

                <div>
                    <div className="flex gap-2 items-end">
                        <div className="flex-1">
                            <Label htmlFor="title">{t('Title')}</Label>
                            <Input
                                id="title"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                placeholder={t('Enter task title')}
                                required
                            />
                            <InputError message={errors.title} />
                        </div>
                        {titleAI.map(field => <div key={field.id}>{field.component}</div>)}
                    </div>
                </div>

                <div>
                    <Label htmlFor="priority">{t('Priority')}</Label>
                    <Select value={data.priority} onValueChange={(value) => setData('priority', value as 'High' | 'Medium' | 'Low')}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="High">{t('High')}</SelectItem>
                            <SelectItem value="Medium">{t('Medium')}</SelectItem>
                            <SelectItem value="Low">{t('Low')}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.priority} />
                </div>

                <div>
                    <Label htmlFor="stage_id">{t('Stage')}</Label>
                    <Select value={data.stage_id?.toString() || ''} onValueChange={(value) => setData('stage_id', value ? parseInt(value) : undefined)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select stage')} />
                        </SelectTrigger>
                        <SelectContent>
                            {taskStages?.map((stage) => (
                                <SelectItem key={stage.id} value={stage.id.toString()}>
                                    {stage.name}
                                </SelectItem>
                            )) || []}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.stage_id} />
                </div>

                <div>
                    <Label required>{t('Assign To')}</Label>
                    <MultiSelectEnhanced
                        options={teamMembers.map(member => ({
                            value: member.id.toString(),
                            label: member.name
                        }))}
                        value={data.assigned_to?.map(id => id.toString()) || []}
                        onValueChange={(values) => setData('assigned_to', values.map(v => parseInt(v)))}
                        placeholder={t('Select team members')}
                        searchable={true}
                    />
                    <InputError message={errors.assigned_to} />
                </div>

                <div>
                    <Label required>{t('Duration')}</Label>
                    <DateRangePicker
                        value={data.duration || ''}
                        onChange={(value) => setData('duration', value)}
                        placeholder={t('Select duration dates')}
                    />
                    <InputError message={errors.duration} />
                </div>

                <div>
                    <div className="flex items-center justify-between mb-2">
                        <Label htmlFor="description">{t('Description')}</Label>
                        <div className="flex gap-2">
                            {descriptionAI.map(field => <div key={field.id}>{field.component}</div>)}
                        </div>
                    </div>
                    <Textarea
                        id="description"
                        rows={3}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('Enter task description')}
                        required
                    />
                    <InputError message={errors.description} />
                </div>

                {/* Calendar Sync Fields */}
                {calendarFields.map((field) => (
                    <div key={field.id}>
                        {field.component}
                    </div>
                ))}

                {   customFields.length > 0 && (
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-1">
                            {customFields.map((field) => (
                                <div key={field.id}>
                                    {field.component}
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Creating...') : t('Create')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}
