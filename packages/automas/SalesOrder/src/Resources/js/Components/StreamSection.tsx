import React, { useState, useEffect } from 'react';
import { useForm, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { MessageSquare, Paperclip, ChevronLeft, ChevronRight, Edit2, Trash2, MoreVertical } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { SearchInput } from '@/components/ui/search-input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatDate, formatDateTime } from '@/utils/helpers';

interface Stream {
    id: number;
    remark: string | object;
    file_upload?: string;
    created_at: string;
    creator_id?: number;
    creator?: {
        name: string;
    };
}

interface StreamSectionProps {
    moduleType: 'account' | 'contact' | 'opportunity' | 'case';
    moduleName: string;
    moduleId: number;
    streams: Stream[];
    imageUrlPrefix?: string;
    auth?: { user?: { name?: string } };
}

export default function StreamSection({ 
    moduleType, 
    moduleName, 
    moduleId, 
    streams,
    imageUrlPrefix,
    auth 
}: StreamSectionProps) {
    const { t } = useTranslation();
    const { flash, auth: pageAuth } = usePage().props as any;
    const [commentSearch, setCommentSearch] = useState('');
    const [commentPage, setCommentPage] = useState(1);
    const [commentPerPage, setCommentPerPage] = useState(10);
    const [editingComment, setEditingComment] = useState<number | null>(null);
    const [editText, setEditText] = useState('');
    const [deleteStreamId, setDeleteStreamId] = useState<number | null>(null);


    
    const { data, setData, post, processing, errors, reset } = useForm({
        stream_comment: '',
        attachment: null as File | null,
        log_type: `${moduleType} comment`
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        post(route('sales.streamstore', [moduleType, moduleName, moduleId]), {
            onSuccess: () => {
                reset();
                const fileInput = document.getElementById('attachment') as HTMLInputElement;
                if (fileInput) fileInput.value = '';
            },
            preserveScroll: true
        });
    };

    const handleEdit = (streamId: number, currentText: string) => {
        setEditingComment(streamId);
        setEditText(currentText);
    };

    const handleSaveEdit = (streamId: number) => {
        router.put(route('sales.streamupdate', streamId), {
            stream_comment: editText
        }, {
            onSuccess: () => {
                setEditingComment(null);
                setEditText('');
            },
            preserveScroll: true
        });
    };

    const handleDelete = (streamId: number) => {
        router.delete(route('sales.streamdelete', streamId), {
            onSuccess: () => {
                setDeleteStreamId(null);
            },
            preserveScroll: true
        });
    };

    const parseRemark = (remark: string | object) => {
        if (typeof remark === 'string') {
            return { stream_comment: remark };
        }
        return remark;
    };

    const canEditDelete = (stream: Stream) => {
        // Company users can edit/delete all comments
        if (pageAuth?.user?.type === 'company') {
            return true;
        }
        // Users can only edit/delete their own comments
        return stream.creator_id === pageAuth?.user?.id;
    };

    const filteredComments = streams.filter((stream) => {
        const remark = parseRemark(stream.remark);
        return remark.stream_comment?.toLowerCase().includes(commentSearch.toLowerCase()) ||
               stream.creator?.name?.toLowerCase().includes(commentSearch.toLowerCase());
    });

    const paginatedComments = {
        data: filteredComments.slice((commentPage - 1) * commentPerPage, commentPage * commentPerPage),
        total: filteredComments.length,
        last_page: Math.ceil(filteredComments.length / commentPerPage)
    };

    return (
        <Card>
            <CardHeader className="p-3 sm:p-6">
                <div className="flex flex-col gap-3">
                    <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
                        <MessageSquare className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                        {t('Comments')}
                        {streams.length > 0 && (
                            <Badge variant="secondary" className="ml-2 text-xs">
                                {streams.length}
                            </Badge>
                        )}
                    </CardTitle>
                    <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <div className="flex-1">
                            <SearchInput
                                value={commentSearch}
                                onChange={(value) => {
                                    setCommentSearch(value);
                                    setCommentPage(1);
                                }}
                                placeholder={t('Search comments...')}
                                className="w-full"
                            />
                        </div>
                        <div className="w-full sm:w-auto sm:min-w-[140px]">
                            <Select value={commentPerPage.toString()} onValueChange={(value) => {
                                setCommentPerPage(Number(value));
                                setCommentPage(1);
                            }}>
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="5">{t('5 per page')}</SelectItem>
                                    <SelectItem value="10">{t('10 per page')}</SelectItem>
                                    <SelectItem value="20">{t('20 per page')}</SelectItem>
                                    <SelectItem value="50">{t('50 per page')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-3 sm:p-4">
                {/* Add Comment Form */}
                <div className="border border-gray-200 rounded-lg p-3 sm:p-4 mb-4">
                    <form onSubmit={handleSubmit} className="space-y-3 sm:space-y-4">
                        <div className="flex flex-col sm:flex-row sm:items-start gap-3">
                            <div className="flex items-center gap-3 sm:flex-col sm:items-center">
                                <Avatar className="h-8 w-8">
                                    <AvatarFallback className="text-xs sm:text-sm bg-primary/10 text-primary">
                                        {auth?.user?.name?.charAt(0)?.toUpperCase() || 'U'}
                                    </AvatarFallback>
                                </Avatar>
                                <p className="text-sm sm:text-base font-medium sm:hidden">{t('Add Comment')}</p>
                            </div>
                            <div className="flex-1 space-y-3">
                                <p className="hidden sm:block text-sm font-medium">{t('Add Comment')}</p>
                                <Textarea
                                    id="stream_comment"
                                    value={data.stream_comment}
                                    onChange={(e) => setData('stream_comment', e.target.value)}
                                    placeholder={t('Write your comment...')}
                                    rows={3}
                                    required
                                    className="resize-none border-gray-200 focus:border-blue-300 focus:ring-blue-200 w-full"
                                />
                                {errors.stream_comment && (
                                    <p className="text-sm text-red-600">{errors.stream_comment}</p>
                                )}
                                <div className="space-y-2">
                                    <Label htmlFor="attachment" className="text-sm font-medium text-gray-500">{t('Attachment')}</Label>
                                    <Input
                                        id="attachment"
                                        type="file"
                                        onChange={(e) => setData('attachment', e.target.files?.[0] || null)}
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                        className="w-full"
                                    />
                                    {errors.attachment && (
                                        <p className="text-sm text-red-600">{errors.attachment}</p>
                                    )}
                                </div>
                                <div className="flex justify-end">
                                    <Button type="submit" disabled={processing} size="sm" className="w-full sm:w-auto px-6">
                                        {processing ? t('Adding...') : t('Add Comment')}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {/* Comments List */}
                {paginatedComments.data.length > 0 ? (
                    <>
                        <div className="space-y-4">
                            {paginatedComments.data.map((stream) => {
                                const remark = parseRemark(stream.remark);
                                return (
                                    <div key={stream.id} className="relative bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow group overflow-hidden">
                                        <div className="p-4">
                                            <div className="flex items-start gap-3">
                                                <Avatar className="h-8 w-8">
                                                    <AvatarFallback className="text-xs bg-primary/10 text-primary">
                                                        {stream.creator?.name?.charAt(0)?.toUpperCase() || 'U'}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center justify-between mb-2">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-medium text-foreground">
                                                                {stream.creator?.name || t('Unknown User')}
                                                            </span>
                                                            <span className="text-xs text-muted-foreground">
                                                                {formatDateTime(stream.created_at)}
                                                            </span>
                                                        </div>
                                                        {canEditDelete(stream) && (
                                                            <DropdownMenu>
                                                                <DropdownMenuTrigger asChild>
                                                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                                        <MoreVertical className="h-4 w-4" />
                                                                    </Button>
                                                                </DropdownMenuTrigger>
                                                                <DropdownMenuContent align="end">
                                                                    <DropdownMenuItem onClick={() => handleEdit(stream.id, remark.stream_comment || '')}>
                                                                        <Edit2 className="h-4 w-4 mr-2" />
                                                                        {t('Edit')}
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem 
                                                                        onClick={() => setDeleteStreamId(stream.id)}
                                                                        className="text-red-600 focus:text-red-600"
                                                                    >
                                                                        <Trash2 className="h-4 w-4 mr-2" />
                                                                        {t('Delete')}
                                                                    </DropdownMenuItem>
                                                                </DropdownMenuContent>
                                                            </DropdownMenu>
                                                        )}
                                                    </div>
                                                    
                                                    {editingComment === stream.id ? (
                                                        <div className="space-y-2">
                                                            <Textarea
                                                                value={editText}
                                                                onChange={(e) => setEditText(e.target.value)}
                                                                className="resize-none"
                                                                rows={3}
                                                            />
                                                            <div className="flex justify-end gap-2">
                                                                <Button size="sm" onClick={() => handleSaveEdit(stream.id)}>
                                                                    {t('Save')}
                                                                </Button>
                                                                <Button 
                                                                    size="sm" 
                                                                    variant="outline" 
                                                                    onClick={() => setEditingComment(null)}
                                                                >
                                                                    {t('Cancel')}
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <div className="text-sm text-foreground whitespace-pre-wrap bg-muted/50 p-3 rounded-md border-l-4 border-primary">
                                                            {remark.stream_comment}
                                                        </div>
                                                    )}
                                                    
                                                    {stream.file_upload && (
                                                        <div className="mt-2 flex items-center gap-2 text-sm text-primary">
                                                            <Paperclip className="h-4 w-4" />
                                                            <a 
                                                                href={`${imageUrlPrefix}/${stream.file_upload}`} 
                                                                target="_blank" 
                                                                rel="noopener noreferrer"
                                                                className="hover:underline"
                                                            >
                                                                {t('View Attachment')}
                                                            </a>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                        
                        {paginatedComments.last_page > 1 && (
                            <div className="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t gap-3">
                                <p className="text-sm text-muted-foreground text-center sm:text-left">
                                    {t('Showing')} {((commentPage - 1) * commentPerPage) + 1} {t('to')} {Math.min(commentPage * commentPerPage, paginatedComments.total)} {t('of')} {paginatedComments.total} {t('comments')}
                                </p>
                                <div className="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCommentPage(commentPage - 1)}
                                        disabled={commentPage === 1}
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        <span className="hidden sm:inline">{t('Previous')}</span>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCommentPage(commentPage + 1)}
                                        disabled={commentPage === paginatedComments.last_page}
                                    >
                                        <span className="hidden sm:inline">{t('Next')}</span>
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </>
                ) : (
                    <div className="text-center py-8 text-muted-foreground">
                        <MessageSquare className="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
                        <p>{t('No comments yet')}</p>
                        <p className="text-sm">{t('Be the first to add a comment')}</p>
                    </div>
                )}
                
                {/* Delete Confirmation Dialog */}
                <AlertDialog open={deleteStreamId !== null} onOpenChange={() => setDeleteStreamId(null)}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>{t('Delete Comment')}</AlertDialogTitle>
                            <AlertDialogDescription>
                                {t('Are you sure you want to delete this comment? This action cannot be undone.')}
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>{t('Cancel')}</AlertDialogCancel>
                            <AlertDialogAction 
                                onClick={() => deleteStreamId && handleDelete(deleteStreamId)}
                                className="bg-red-600 hover:bg-red-700"
                            >
                                {t('Delete')}
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </CardContent>
        </Card>
    );
}