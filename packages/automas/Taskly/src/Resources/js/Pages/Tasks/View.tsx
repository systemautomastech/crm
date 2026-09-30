import { useState, useEffect, useCallback } from 'react';
import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { getImagePath } from '@/utils/helpers';
import { User, Image, File, FileText, Video, Music, Download, Eye, Trash2 } from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { DatePicker } from "@/components/ui/date-picker";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { ProjectTask, Project, Milestone, TaskStage } from './types';
import axios from 'axios';
import { toast } from 'sonner';
import { formatDate, downloadFile } from '@/utils/helpers';
import { useFormFields } from '@/hooks/useFormFields';
import MediaPicker from "@/components/MediaPicker";

interface ViewTaskProps {
    task: { id: number } | ProjectTask;
    project?: Project;
    milestones?: Milestone[];
    teamMembers?: Array<{ id: number; name: string; }>;
    taskStages?: TaskStage[];
    onClose?: () => void;
}

export default function View({ task, project, milestones = [], teamMembers = [], taskStages = [], onClose }: ViewTaskProps) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [taskData, setTaskData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const customFields = useFormFields('getCustomFields', { module: 'Taskly', sub_module: 'Tasks', id: task.id }, () => {}, {}, 'view', t);

    const canManageComments = auth.user?.permissions?.includes('manage-project-task-comments');
    const canManageSubtasks = auth.user?.permissions?.includes('manage-project-subtask');
    const canEditTask = auth.user?.permissions?.includes('edit-project-task');

    const fetchTaskData = useCallback(async () => {
        try {
            const response = await axios.get(route('project.tasks.show', task.id));
            setTaskData(response.data.task);
        } catch (error) {
            toast.error(t('Failed to load task data'));
        } finally {
            setLoading(false);
        }
    }, [task.id]);

    useEffect(() => {
        fetchTaskData();
    }, [fetchTaskData]);

    if (loading) {
        return (
            <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{t('Task Details')}</DialogTitle>
                </DialogHeader>
                <div className="flex items-center justify-center py-8">
                    <p className="text-sm text-gray-500">{t('Loading task details...')}</p>
                </div>
            </DialogContent>
        );
    }

    if (!taskData) return null;

    // Find milestone
    const milestone = milestones.find(m => m.id === taskData.milestone_id);

    // Find stage
    const stage = taskStages.find(s => s.id === taskData.stage_id);

    const assignedUsers = taskData.assignedUsers || [];
    const taskFiles = taskData.files || [];

    const visibleTabs = [
        canManageComments ? 'comments' : null,
        canManageSubtasks ? 'subtasks' : null,
        canEditTask ? 'files' : null,
    ].filter(Boolean) as string[];

    const defaultTab = visibleTabs[0] || 'comments';
    const tabCount = visibleTabs.length;

    return (
        <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{t('Task Details')}</DialogTitle>
            </DialogHeader>

            <div className="space-y-6">
                {/* Title and Priority */}
                <div className="flex items-start justify-between">
                    <div className="flex-1">
                        <h3 className="text-lg font-semibold text-gray-900">{taskData.title}</h3>
                    </div>
                    <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                            taskData.priority === 'Low' ? 'bg-green-100 text-green-800' :
                            taskData.priority === 'Medium' ? 'bg-yellow-100 text-yellow-800' :
                            taskData.priority === 'High' ? 'bg-red-100 text-red-800' :
                            'bg-red-100 text-red-800'
                        }`}>
                            {t(taskData.priority)}
                    </span>
                </div>

                {/* Description */}
                {taskData.description && (
                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{t('Description')}</h4>
                        <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-md">{taskData.description}</p>
                    </div>
                )}

                {/* Details Grid */}
                <div className="grid grid-cols-2 gap-6">
                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{t('Project')}</h4>
                        <p className="text-sm text-gray-900">{taskData.project?.name || project?.name || '-'}</p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{t('Milestone')}</h4>
                        <p className="text-sm text-gray-900">{milestone?.title || '-'}</p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{t('Stage')}</h4>
                        {taskData.stage?.name ? (
                            <span className="px-2 py-1 rounded-full text-sm" style={{ backgroundColor: `${taskData.stage?.color || '#e5e7eb'}30`, color: '#374151' }}>
                                {t(taskData.stage.name)}
                            </span>
                        ) : stage?.name ? (
                            <span className="px-2 py-1 rounded-full text-sm" style={{ backgroundColor: `${stage?.color || '#e5e7eb'}30`, color: '#374151' }}>
                                {t(stage.name)}
                            </span>
                        ) : (
                            <span className="text-sm text-gray-900">-</span>
                        )}
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{t('Duration')}</h4>
                        <p className="text-sm text-gray-900">
                            {taskData.start_date && taskData.end_date
                                ? `${formatDate(taskData.start_date)} - ${formatDate(taskData.end_date)}`
                                : taskData.duration || '-'
                            }
                        </p>
                    </div>
                </div>

                {/* Assigned Users */}
                <div>
                    <h4 className="text-sm font-medium text-gray-700 mb-3">{t('Assigned To')}</h4>
                    {assignedUsers.length > 0 ? (
                        <div className="flex flex-wrap gap-2">
                            {assignedUsers.map((user, index) => (
                                <div key={index} className="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-md">
                                    <div className="h-8 w-8 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                        {user.avatar ? (
                                            <img
                                                src={getImagePath(user.avatar)}
                                                alt={user.name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <User className="h-6 w-6 text-gray-400" />
                                        )}
                                    </div>
                                    <span className="text-sm text-gray-900">{user.name}</span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500">{t('No users assigned')}</p>
                    )}
                </div>

                {/* Custom Fields */}
                {customFields.length > 0 && (
                    <div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {customFields.map((field, index) => (
                                <div key={index} className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700 mb-2">{field.label}</label>
                                    <div className="text-sm text-gray-900">
                                        {field.component}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Tabs Section */}
                {tabCount > 0 && (
                    <Tabs defaultValue={defaultTab} className="w-full">
                        <TabsList className={`grid w-full ${
                            tabCount === 3 ? 'grid-cols-3' :
                            tabCount === 2 ? 'grid-cols-2' :
                            'grid-cols-1'
                        }`}>
                            {canManageComments && (
                                <TabsTrigger value="comments">{t('Comments')}</TabsTrigger>
                            )}
                            {canManageSubtasks && (
                                <TabsTrigger value="subtasks">{t('Subtasks')}</TabsTrigger>
                            )}
                            {canEditTask && (
                                <TabsTrigger value="files">{t('Files')}</TabsTrigger>
                            )}
                        </TabsList>

                        {canManageComments && (
                            <TabsContent value="comments" className="space-y-4">
                                <CommentsTab taskId={taskData.id} />
                            </TabsContent>
                        )}

                        {canManageSubtasks && (
                            <TabsContent value="subtasks" className="space-y-4">
                                <SubtasksTab taskId={taskData.id} />
                            </TabsContent>
                        )}

                        {canEditTask && (
                            <TabsContent value="files" className="space-y-4">
                                <FilesTab taskId={taskData.id} files={taskFiles} canEditTask={canEditTask} onRefetch={fetchTaskData} />
                            </TabsContent>
                        )}
                    </Tabs>
                )}
            </div>
        </DialogContent>
    );
}

function CommentsTab({ taskId }: { taskId: number }) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [comment, setComment] = useState('');
    const [comments, setComments] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [loadingComments, setLoadingComments] = useState(true);

    const [deleteState, setDeleteState] = useState({ isOpen: false, commentId: null, message: '' });

    const openDeleteDialog = (commentId: number) => {
        setDeleteState({
            isOpen: true,
            commentId,
            message: t('Are you sure you want to delete this comment?')
        });
    };

    const closeDeleteDialog = () => {
        setDeleteState({ isOpen: false, commentId: null, message: '' });
    };

    const confirmDelete = async () => {
        if (!deleteState.commentId) return;

        try {
            const response = await axios.delete(route('project.tasks.comments.destroy', deleteState.commentId));
            if (response.data.message) {
                toast.success(t(response.data.message));
            }
            fetchComments();
            closeDeleteDialog();
        } catch (error) {
            toast.error(t('Failed to delete comment'));
        }
    };

    const fetchComments = async () => {
        try {
            const response = await axios.get(route('project.tasks.comments.index', taskId));
            setComments(response.data.comments);
        } catch (error) {
            toast.error(t('Failed to load comments'));
        } finally {
            setLoadingComments(false);
        }
    };

    useEffect(() => {
        fetchComments();
    }, []);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!comment.trim()) return;

        setLoading(true);
        try {
            const response = await axios.post(route('project.tasks.comments.store', taskId), { comment });
            setComment('');
            if (response.data.message) {
                toast.success(t(response.data.message));
            }
            fetchComments();
        } catch (error) {
            toast.error(t('Failed to add comment'));
        } finally {
            setLoading(false);
        }
    };



    return (
        <div className="space-y-4">
            <form onSubmit={handleSubmit} className="space-y-3">
                <div>
                    <Label htmlFor="comment">{t('Add Comment')}</Label>
                    <Textarea
                        id="comment"
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        placeholder={t('Enter your comment...')}
                        rows={3}
                    />
                </div>
                <Button type="submit" disabled={loading || !comment.trim()}>
                    {loading ? t('Adding...') : t('Add Comment')}
                </Button>
            </form>

            {loadingComments ? (
                <div className="text-center py-4">
                    <p className="text-sm text-gray-500">{t('Loading comments...')}</p>
                </div>
            ) : comments.length > 0 ? (
                <div className="space-y-3">
                    {comments.map((comment) => (
                        <div key={comment.id} className="bg-gray-50 p-3 rounded-md">
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="h-8 w-8 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                        {comment.user.avatar ? (
                                            <img
                                                src={getImagePath(comment.user.avatar)}
                                                alt={comment.user.name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <User className="h-3 w-3 text-gray-400" />
                                        )}
                                    </div>
                                    <span className="text-sm font-medium text-gray-900">{comment.user.name}</span>
                                    <span className="text-xs text-gray-500">
                                        {formatDate(comment.created_at)}
                                    </span>
                                </div>
                                {auth.user?.permissions?.includes('delete-project-task-comments') && (
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => openDeleteDialog(comment.id)}
                                                    className="h-6 w-6 p-0 text-red-600 hover:text-red-700 mt-1"
                                                >
                                                    <Trash2 className="h-3 w-3" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{t('Delete')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                )}
                            </div>
                            <p className="text-sm text-gray-700">{comment.comment}</p>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-4">
                    <p className="text-sm text-gray-500">{t('No comments yet')}</p>
                </div>
            )}

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Comment')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </div>
    );
}

function SubtasksTab({ taskId }: { taskId: number }) {
    const { t } = useTranslation();
    const { auth } = usePage<any>().props;
    const [name, setName] = useState('');
    const [dueDate, setDueDate] = useState('');
    const [subtasks, setSubtasks] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [loadingSubtasks, setLoadingSubtasks] = useState(true);

    const fetchSubtasks = async () => {
        try {
            const response = await axios.get(route('project.tasks.subtasks.index', taskId));
            setSubtasks(response.data.subtasks);
        } catch (error) {
            toast.error(t('Failed to load subtasks'));
        } finally {
            setLoadingSubtasks(false);
        }
    };

    useEffect(() => {
        fetchSubtasks();
    }, []);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!name.trim()) return;

        setLoading(true);
        try {
            const response = await axios.post(route('project.tasks.subtasks.store', taskId), {
                name,
                due_date: dueDate || null
            });
            setName('');
            setDueDate('');
            if (response.data.message) {
                toast.success(t(response.data.message));
            }
            fetchSubtasks();
        } catch (error) {
            toast.error(t('Failed to add subtask'));
        } finally {
            setLoading(false);
        }
    };

    const handleToggle = async (subtaskId: number) => {
        try {
            await axios.patch(route('project.tasks.subtasks.toggle', subtaskId));
            fetchSubtasks();
        } catch (error) {
            toast.error(t('Failed to update subtask'));
        }
    };

    return (
        <div className="space-y-4">
            <form onSubmit={handleSubmit} className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                    <div>
                        <Label htmlFor="subtask-name">{t('Subtask Name')}</Label>
                        <Input
                            id="subtask-name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder={t('Enter subtask name...')}
                        />
                    </div>
                    <div>
                        <Label>{t('Due Date')}</Label>
                        <DatePicker
                            value={dueDate}
                            onChange={(value) => setDueDate(value)}
                            placeholder={t('Select due date')}
                        />
                    </div>
                </div>
                <Button type="submit" disabled={loading || !name.trim()}>
                    {loading ? t('Adding...') : t('Add Subtask')}
                </Button>
            </form>

            {loadingSubtasks ? (
                <div className="text-center py-4">
                    <p className="text-sm text-gray-500">{t('Loading subtasks...')}</p>
                </div>
            ) : subtasks.length > 0 ? (
                <div className="space-y-3">
                    {subtasks.map((subtask) => (
                        <div key={subtask.id} className="flex items-center gap-3 p-3 bg-gray-50 rounded-md">
                            <Checkbox
                                checked={subtask.is_completed}
                                onCheckedChange={() => handleToggle(subtask.id)}
                            />
                            <div className="flex-1">
                                <p className={`text-sm ${subtask.is_completed ? 'line-through text-gray-500' : 'text-gray-900'}`}>
                                    {subtask.name}
                                </p>
                                {subtask.due_date && (
                                    <p className="text-xs text-gray-500">
                                        {t('Due')}: {formatDate(subtask.due_date)}
                                    </p>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-4">
                    <p className="text-sm text-gray-500">{t('No subtasks yet')}</p>
                </div>
            )}
        </div>
    );
}

function FilesTab({ taskId, files, canEditTask, onRefetch }: { taskId: number; files: any[]; canEditTask: boolean; onRefetch: () => void }) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const [selectedImages, setSelectedImages] = useState<string[]>([]);

    const getFileIcon = (fileName: string) => {
        const ext = fileName.split('.').pop()?.toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext || '')) {
            return <Image className="h-5 w-5 text-blue-500" />;
        } else if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'].includes(ext || '')) {
            return <Video className="h-5 w-5 text-purple-500" />;
        } else if (['mp3', 'wav', 'flac', 'aac', 'ogg'].includes(ext || '')) {
            return <Music className="h-5 w-5 text-green-500" />;
        } else if (['txt', 'doc', 'docx', 'pdf', 'rtf'].includes(ext || '')) {
            return <FileText className="h-5 w-5 text-red-500" />;
        } else {
            return <File className="h-5 w-5 text-gray-500" />;
        }
    };

    const isImage = (fileName: string) => {
        const ext = fileName.split('.').pop()?.toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext || '');
    };

    const handleFileUpload = async (value: string | string[]) => {
        const items = Array.isArray(value) ? value : [value].filter(Boolean);
        if (items.length === 0) return;

        setUploading(true);
        try {
            const response = await axios.post(route('project.tasks.files.store', taskId), { images: items });
            toast.success(t(response.data.message || 'Files uploaded successfully.'));
            setSelectedImages([]);
            onRefetch();
        } catch (error: any) {
            toast.error(error.response?.status === 403 ? t('Permission denied') : t('Failed to upload files'));
        } finally {
            setUploading(false);
        }
    };

    const handleDeleteFile = async (fileId: number) => {
        try {
            const response = await axios.delete(route('project.tasks.files.delete', fileId));
            toast.success(t(response.data.message || 'The file has been deleted.'));
            onRefetch();
        } catch (error: any) {
            toast.error(error.response?.status === 403 ? t('Permission denied') : t('Failed to delete file'));
        }
    };

    return (
        <div className="space-y-4">
            {canEditTask && (
                <div>
                    <MediaPicker
                        value={selectedImages}
                        onChange={(value) => {
                            const items = Array.isArray(value) ? value : [value].filter(Boolean);
                            setSelectedImages(items);
                            if (items.length > 0) {
                                handleFileUpload(items);
                            }
                        }}
                        multiple={true}
                        placeholder={t('Select files')}
                        showPreview={false}
                        label=""
                    />
                </div>
            )}

            {files && files.length > 0 ? (
                <div className="space-y-2 max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100">
                    {files.map((file) => {
                        const imageUrl = getImagePath(file.file_path);
                        return (
                            <div key={file.id} className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border hover:bg-gray-100 transition-colors group">
                                <div className="flex-shrink-0">
                                    {isImage(file.file_path) ? (
                                        <img
                                            src={imageUrl}
                                            alt={file.file_name}
                                            className="w-10 h-10 object-cover rounded border"
                                        />
                                    ) : (
                                        <div className="w-10 h-10 bg-white rounded border flex items-center justify-center">
                                            {getFileIcon(file.file_name)}
                                        </div>
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium text-gray-900 truncate" title={file.file_name}>
                                        {file.file_name}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        {file.file_name.split('.').pop()?.toUpperCase()} file
                                    </p>
                                </div>
                                <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => window.open(imageUrl, '_blank')}
                                                    className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{t('View')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => downloadFile(imageUrl)}
                                                    className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                                >
                                                    <Download className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{t('Download')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    {canEditTask && (
                                        <TooltipProvider>
                                            <Tooltip delayDuration={0}>
                                                <TooltipTrigger asChild>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleDeleteFile(file.id)}
                                                        className="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>{t('Delete')}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            ) : (
                <div className="text-center py-8 text-gray-500 flex flex-col items-center justify-center">
                    <File className="h-12 w-12 mx-auto mb-2 opacity-50" />
                    <p className="text-sm">{t('No files uploaded yet')}</p>
                </div>
            )}
        </div>
    );
}
