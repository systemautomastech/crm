import { useState, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MultiSelectEnhanced } from '@/components/ui/multi-select-enhanced';
import InputError from '@/components/ui/input-error';
import { useFormFields } from '@/hooks/useFormFields';
import axios from 'axios';
import { toast } from 'sonner';

interface EditBugProps {
    onSuccess: () => void;
    bug: { id: number };
    project?: { id: number; name: string; };
    teamMembers: Array<{ id: number; name: string; }>;
    bugStages: Array<{ id: number; name: string; }>;
}

export default function Edit({ onSuccess, bug, project, teamMembers, bugStages }: EditBugProps) {
    const { t } = useTranslation();
    const [bugData, setBugData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [bugLoaded, setBugLoaded] = useState(false);
    const { data, setData, put, processing, errors } = useForm({
        title: '',
        priority: 'Medium',
        assigned_to: [] as string[],
        stage_id: undefined as number | undefined,
        description: '',
    });

    const updateData = (key: string, value: any) => {
        setData(key as any, value);
    };

    // AI hooks for title and description fields
    const titleAI = useFormFields('aiField', data, updateData, {}, 'edit', 'title', 'Title', 'taskly', 'bug');
    const descriptionAI = useFormFields('aiField', data, updateData, {}, 'edit', 'description', 'Description', 'taskly', 'bug');
    const customFields = useFormFields('getCustomFields', { ...data, module: 'Taskly', sub_module: 'Bugs', id: bugLoaded ? bug.id : null }, updateData, {}, 'edit', t);

    useEffect(() => {
        const fetchBugData = async () => {
            try {
                const response = await axios.get(route('project.bugs.show', bug.id));
                const fetchedBug = response.data.bug;
                setBugData(fetchedBug);

                // Get assigned user IDs
                const assignedIds = fetchedBug.assignedUsers?.map((user: any) => user.id.toString()) || [];

                setData({
                    title: fetchedBug.title || '',
                    priority: fetchedBug.priority || 'Medium',
                    assigned_to: assignedIds,
                    stage_id: fetchedBug.stage_id || (bugStages.length > 0 ? bugStages[0].id : undefined),
                    description: fetchedBug.description || '',
                });
                setBugLoaded(true);
            } catch (error) {
                toast.error(t('Failed to load bug data'));
            } finally {
                setLoading(false);
            }
        };

        fetchBugData();
    }, [bug.id, bugStages]);

    if (loading) {
        return (
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{t('Edit Bug')}</DialogTitle>
                </DialogHeader>
                <div className="flex items-center justify-center py-8">
                    <p className="text-sm text-gray-500">{t('Loading bug data...')}</p>
                </div>
            </DialogContent>
        );
    }

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('project.bugs.update', bug.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Edit Bug')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <div className="flex gap-2 items-end">
                        <div className="flex-1">
                            <Label htmlFor="edit_title">{t('Title')}</Label>
                            <Input
                                id="edit_title"
                                value={data.title}
                                onChange={(e) => updateData('title', e.target.value)}
                                placeholder={t('Enter bug title')}
                                required
                            />
                            <InputError message={errors.title} />
                        </div>
                        {titleAI.map(field => <div key={field.id}>{field.component}</div>)}
                    </div>
                </div>

                <div>
                    <Label required>{t('Priority')}</Label>
                    <Select value={data.priority} onValueChange={(value) => updateData('priority', value)}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Low">{t('Low')}</SelectItem>
                            <SelectItem value="Medium">{t('Medium')}</SelectItem>
                            <SelectItem value="High">{t('High')}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.priority} />
                </div>

                <div>
                    <Label required>{t('Assign To')}</Label>
                    <MultiSelectEnhanced
                        options={teamMembers.map(member => ({ value: member.id.toString(), label: member.name }))}
                        value={data.assigned_to || []}
                        onValueChange={(values) => updateData('assigned_to', values)}
                        placeholder={t('Select team members')}
                        searchable={true}
                    />
                    <InputError message={errors.assigned_to} />
                </div>

                <div>
                    <Label>{t('Status')}</Label>
                    <Select value={data.stage_id?.toString() || ''} onValueChange={(value) => updateData('stage_id', value ? parseInt(value) : undefined)}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {bugStages.map((stage) => (
                                <SelectItem key={stage.id} value={stage.id.toString()}>{stage.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.stage_id} />
                </div>

                <div>
                    <div className="flex items-center justify-between mb-2">
                        <Label htmlFor="edit_description">{t('Description')}</Label>
                        <div className="flex gap-2">
                            {descriptionAI.map(field => <div key={field.id}>{field.component}</div>)}
                        </div>
                    </div>
                    <Textarea
                        id="edit_description"
                        value={data.description}
                        onChange={(e) => updateData('description', e.target.value)}
                        placeholder={t('Enter bug description')}
                        rows={3}
                        required
                    />
                    <InputError message={errors.description} />
                </div>

                {customFields.length > 0 && (
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
                    <Button type="button" variant="outline" onClick={() => onSuccess()}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Updating...') : t('Update')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}
