import { useState, useEffect, useRef } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import axios from 'axios';
import Pusher from 'pusher-js';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { MessageCircle, Send, Search, User as UserIcon, Smile, Paperclip, X } from "lucide-react";
import { getImagePath, getAdminSetting } from '@/utils/helpers';
import { EmojiPicker } from '@/components/ui/emoji-picker';

interface FacebookContact {
    id: number;
    name: string;
    user_name: string;
    sender_id: string;
    profile_image?: string;
    last_message?: string;
    last_message_time?: string;
    unseen_count: number;
    status?: string;
    created_at: string;
}

interface FacebookChatIndexProps {
    contacts: FacebookContact[];
    auth: any;
}

export default function Index() {
    const pageProps = usePage<FacebookChatIndexProps>().props;
    const { contacts, auth } = pageProps;
    const { t } = useTranslation();
    const [selectedContact, setSelectedContact] = useState<FacebookContact | null>(null);
    const [newMessage, setNewMessage] = useState('');
    const [searchQuery, setSearchQuery] = useState('');
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [chatMessages, setChatMessages] = useState<any[]>([]);
    const [contactsList, setContactsList] = useState<FacebookContact[]>(contacts || []);
    const messagesContainerRef = useRef<HTMLDivElement>(null);

    useFlashMessages();

    // Sort contacts by last message time (most recent first)
    const sortedContacts = contactsList.sort((a, b) => {
        const aTime = a.last_message_time ? new Date(a.last_message_time).getTime() : 0;
        const bTime = b.last_message_time ? new Date(b.last_message_time).getTime() : 0;
        return bTime - aTime;
    });

    const filteredContacts = sortedContacts.filter((contact: any) =>
        (contact.name || '').toLowerCase().includes(searchQuery.toLowerCase()) ||
        (contact.user_name || '').includes(searchQuery)
    );

    const handleContactSelect = async (contact: any) => {
        setSelectedContact(contact);
        // Load messages for this contact
        try {
            const response = await axios.post(route('facebook-chat.get-chat'), {
                contact_id: contact.id
            });

            if (response.data.status === 1) {
                setChatMessages(response.data.chats || []);
            }
        } catch (error) {
        }
    };

    const handleEmojiSelect = (emoji: string) => {
        setNewMessage(prev => prev + emoji);
        setShowEmojiPicker(false);
    };

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setSelectedFile(file);
            e.target.value = '';
        }
    };

    const removeSelectedFile = () => {
        setSelectedFile(null);
    };

    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if ((!newMessage.trim() && !selectedFile) || !selectedContact) return;

        const formData = new FormData();
        formData.append('contact_id', selectedContact.id.toString());
        if (newMessage.trim()) formData.append('message', newMessage.trim());
        if (selectedFile) formData.append('attachment', selectedFile);

        try {
            await axios.post(route('facebook-chat.send-message'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            });

            await handleContactSelect(selectedContact);
            setNewMessage('');
            setSelectedFile(null);
        } catch (error) {
        }
    };

    const getFileIcon = (fileName: string) => {
        const ext = fileName.split('.').pop()?.toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext || '')) return '🖼️';
        if (['mp4', 'avi', 'mov', 'wmv'].includes(ext || '')) return '🎥';
        if (['pdf'].includes(ext || '')) return '📄';
        if (['doc', 'docx'].includes(ext || '')) return '📝';
        return '📎';
    };

    useEffect(() => {
        setContactsList(contacts || []);
    }, [contacts]);

    useEffect(() => {
        if (messagesContainerRef.current && chatMessages.length > 0) {
            messagesContainerRef.current.scrollTop = messagesContainerRef.current.scrollHeight;
        }
    }, [chatMessages]);

    // Pusher real-time messaging
    useEffect(() => {
        if (!auth.user?.id) return;

        const pusherKey = getAdminSetting('pusher_app_key', pageProps) || import.meta.env.VITE_PUSHER_APP_KEY;
        const pusherCluster = getAdminSetting('pusher_app_cluster', pageProps) || import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1';

        if (!pusherKey) return;

        let pusher: Pusher | null = null;
        let channel: any = null;

        try {
            pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                disableStats: true
            });

            const channelName = `facebook-chat-${auth.user.id}`;
            const eventName = `facebook-chat-event-${auth.user.id}`;

            channel = pusher.subscribe(channelName);

            channel.bind(eventName, (data: any) => {
                // Only update count for incoming messages (not sent by us)
                if (!data.is_send) {
                    setContactsList(prev => {
                        const updated = prev.map(contact =>
                            contact.id === data.id
                                ? {
                                    ...contact,
                                    last_message: data.message,
                                    last_message_time: new Date().toISOString(),
                                    unseen_count: selectedContact?.id === data.id ? 0 : (contact.unseen_count || 0) + 1
                                  }
                                : contact
                        );
                        return updated;
                    });
                }

                if (selectedContact?.id === data.id) {
                    const newMessage = {
                        id: data.message_id,
                        message: data.message,
                        image_path: data.image_path,
                        image_path_type: data.image_path_type,
                        is_send: data.is_send || false,
                        created_at: new Date().toISOString()
                    };
                    setChatMessages(prev => [...prev, newMessage]);
                }
            });

        } catch (error) {
        }

        return () => {
            if (pusher) {
                try {
                    if (channel) {
                        channel.unbind();
                        pusher.unsubscribe(channel.name);
                    }
                    if (pusher.connection.state === 'connected') {
                        pusher.disconnect();
                    }
                } catch (e) {
                    // Ignore cleanup errors
                }
            }
        };
    }, [auth.user?.id]);

    useEffect(() => {
        setContactsList(contacts || []);
    }, [contacts]);

    // Auto mark as seen after 2 seconds of viewing chat
    useEffect(() => {
        if (selectedContact && selectedContact.unseen_count > 0) {
            const timer = setTimeout(() => {
                setContactsList(prev =>
                    prev.map(c =>
                        c.id === selectedContact.id
                            ? { ...c, unseen_count: 0 }
                            : c
                    )
                );

                fetch(route('facebook-chat.mark-seen'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ contact_id: selectedContact.id }),
                }).catch(error => 'Error marking as seen:');
            }, 2000);

            return () => clearTimeout(timer);
        }
    }, [selectedContact]);

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Facebook Chat')}]}
            pageTitle={t('Manage Facebook Chat')}
        >
            <Head title={t('Facebook Chat')} />

            <div className="flex gap-6 h-[calc(100vh-100px)]">
                <Card className="w-80 flex flex-col h-full">
                    <CardHeader className="pb-3 flex-shrink-0">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <MessageCircle className="h-5 w-5 text-primary" />
                            </div>
                            <h2 className="font-semibold text-gray-900">{t('Facebook')}</h2>
                        </div>

                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                placeholder={t('Search contacts...')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-10"
                            />
                        </div>
                    </CardHeader>
                    <CardContent className="flex-1 p-0 overflow-hidden">
                        <div
                            className="h-full overflow-y-auto"
                            style={{
                                scrollbarWidth: 'none',
                                msOverflowStyle: 'none'
                            } as React.CSSProperties}
                        >
                            <div>
                                {filteredContacts.map((contact: FacebookContact) => (
                                    <div
                                        key={contact.id}
                                        onClick={() => handleContactSelect(contact)}
                                        className={`group flex items-center gap-3 p-3 cursor-pointer transition-all duration-150 border-b border-gray-100 ${
                                            selectedContact?.id === contact.id
                                                ? 'bg-gray-100 border-r-4 border-primary'
                                                : 'hover:bg-gray-50'
                                        }`}
                                    >
                                        <div className="relative">
                                            <Avatar className="h-12 w-12">
                                                {contact.profile_image ? (
                                                    <AvatarImage src={contact.profile_image} alt={contact.name} />
                                                ) : (
                                                    <AvatarFallback className="bg-gradient-to-br from-primary/20 to-primary/10">
                                                        <UserIcon className="h-6 w-6 text-primary" />
                                                    </AvatarFallback>
                                                )}
                                            </Avatar>
                                            <div className={`absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white ${
                                                contact.status === 'active' ? 'bg-primary' : 'bg-gray-400'
                                            }`} />
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between mb-1">
                                                <p className="font-semibold text-sm text-gray-900 truncate">{contact.name}</p>

                                                <div className="flex items-center gap-1">
                                                    {contact.last_message_time && (
                                                        <span className="text-xs text-gray-500">
                                                            {new Date(contact.last_message_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                        </span>
                                                    )}
                                                    {contact.unseen_count > 0 && (
                                                        <div className="bg-primary text-white rounded-full min-w-[18px] h-[18px] flex items-center justify-center text-xs font-medium">
                                                            {contact.unseen_count > 9 ? '9+' : contact.unseen_count}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            <p className="text-xs text-gray-500 mb-1">@{contact.user_name}</p>
                                            <div className="flex items-center justify-between">
                                                <p className="text-xs text-gray-500 truncate flex-1">
                                                    {contact.last_message ? (
                                                        contact.last_message.length > 20 ?
                                                            contact.last_message.substring(0, 20) + '...' :
                                                            contact.last_message
                                                    ) : t('No messages yet')}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                <div className={`text-center py-12 text-gray-500 ${filteredContacts.length === 0 ? '' : 'hidden'}`}>
                                    <div className="p-4 bg-gray-50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                        <MessageCircle className="h-8 w-8 text-gray-300" />
                                    </div>
                                    <p className="text-sm font-medium">{t('No contacts found')}</p>
                                    <p className="text-xs mt-1">{t('Try adjusting your search')}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card className="flex-1 flex flex-col">
                    <CardHeader className={`pb-3 ${selectedContact ? 'border-b' : 'hidden'}`}>
                        <div className="flex items-center gap-3">
                            <Avatar className="h-10 w-10">
                                {selectedContact?.profile_image ? (
                                    <AvatarImage src={selectedContact.profile_image} alt={selectedContact.name} />
                                ) : (
                                    <AvatarFallback className="bg-gradient-to-br from-primary/20 to-primary/10">
                                        <UserIcon className="h-5 w-5 text-primary" />
                                    </AvatarFallback>
                                )}
                            </Avatar>
                            <div className="flex-1">
                                <h3 className="font-semibold text-gray-900">{selectedContact?.name || selectedContact?.user_name || ''}</h3>
                                <p className="text-sm text-gray-500">@{selectedContact?.user_name || ''}</p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="flex-1 p-0 flex flex-col">
                        <div className={selectedContact ? '' : 'hidden'}>
                            <div
                                className="flex-1 p-6 h-[calc(100vh-300px)] overflow-y-auto"
                                ref={messagesContainerRef}
                                style={{
                                    scrollbarWidth: 'none',
                                    msOverflowStyle: 'none'
                                } as React.CSSProperties}
                            >
                                <div className="space-y-4 min-h-full">
                                    {chatMessages.length > 0 ? chatMessages.map((message: any) => {
                                        const isOwnMessage = message.is_send;
                                        return (
                                            <div key={message.id} className={`flex mb-2 items-end gap-2 ${isOwnMessage ? 'justify-end' : 'justify-start'}`}>
                                                <div className="max-w-[70%]">
                                                    <div
                                                        className={`relative px-3 py-2 rounded-lg shadow-sm ${
                                                            isOwnMessage
                                                                ? 'bg-primary text-white rounded-br-sm'
                                                                : 'bg-white text-gray-900 border rounded-bl-sm'
                                                        }`}
                                                    >
                                                        {message.message && <p className="text-sm leading-relaxed">{message.message}</p>}
                                                        {message.image_path && (
                                                            <div className="mt-2">
                                                                {message.image_path_type?.is_image || (!message.image_path_type && /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(message.image_path)) ? (
                                                                    <img
                                                                        src={getImagePath(message.image_path, pageProps)}
                                                                        alt="Facebook attachment"
                                                                        className="max-w-xs rounded-lg cursor-pointer"
                                                                        onClick={() => window.open(getImagePath(message.image_path, pageProps), '_blank')}
                                                                    />
                                                                ) : message.image_path_type?.is_video || (!message.image_path_type && /\.(mp4|avi|mov|wmv)$/i.test(message.image_path)) ? (
                                                                    <video
                                                                        src={getImagePath(message.image_path, pageProps)}
                                                                        controls
                                                                        className="max-w-xs rounded-lg"
                                                                    />
                                                                ) : (
                                                                    <div className="flex items-center gap-2 p-2 bg-gray-100 rounded-lg max-w-xs">
                                                                        <div className="text-2xl">📎</div>
                                                                        <div className="flex-1 min-w-0">
                                                                            <p className="text-sm font-medium truncate">{message.image_path.split('/').pop()}</p>
                                                                            <p className="text-xs text-gray-500">{t('Document')}</p>
                                                                        </div>
                                                                        <Button
                                                                            size="sm"
                                                                            variant="ghost"
                                                                            onClick={() => window.open(getImagePath(message.image_path, pageProps), '_blank')}
                                                                            className="h-8 w-8 p-0"
                                                                        >
                                                                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                            </svg>
                                                                        </Button>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        )}
                                                        <div className="flex items-center justify-end gap-1 mt-1">
                                                            <span className={`text-xs ${
                                                                isOwnMessage ? 'text-primary-100' : 'text-gray-500'
                                                            }`}>
                                                                {new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    }) : (
                                        <div className="flex items-center justify-center h-full text-gray-500">
                                            <p>{t('No messages yet. Start the conversation!')}</p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            <Separator />

                            <div className="p-6">
                                <div className="relative">
                                    <EmojiPicker
                                        onEmojiSelect={handleEmojiSelect}
                                        className={`absolute bottom-full mb-2 right-0 ${showEmojiPicker ? '' : 'hidden'}`}
                                    />
                                    <form onSubmit={handleSendMessage} className="relative">
                                        <div className="flex items-center gap-3">
                                            <div className="relative flex-1">
                                                <div className="relative">
                                                    {selectedFile && (
                                                        <div className="absolute left-3 top-1/2 -translate-y-1/2 flex items-center gap-1 bg-gray-100 rounded px-2 py-1 z-10">
                                                            <span className="text-xs">{getFileIcon(selectedFile.name)}</span>
                                                            <span className="text-xs font-medium truncate max-w-20">{selectedFile.name}</span>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={removeSelectedFile}
                                                                className="h-4 w-4 p-0 text-gray-400 hover:text-red-500"
                                                            >
                                                                <X className="h-2 w-2" />
                                                            </Button>
                                                        </div>
                                                    )}
                                                    <Input
                                                        value={newMessage}
                                                        onChange={(e) => setNewMessage(e.target.value)}
                                                        placeholder={selectedFile ? t('Add a caption...') : t('Type a message...')}
                                                        className={`pr-20 py-3 ${selectedFile ? 'pl-32' : ''}`}
                                                    />
                                                </div>
                                                <div className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                                        className="h-8 w-8 p-0 text-gray-400 hover:text-gray-600"
                                                    >
                                                        <Smile className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => fileInputRef.current?.click()}
                                                        className="h-8 w-8 p-0 text-gray-400 hover:text-gray-600"
                                                    >
                                                        <Paperclip className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                            <Button
                                                type="submit"
                                                disabled={!newMessage.trim() && !selectedFile}
                                                className="h-10 w-10 p-0 bg-primary hover:bg-primary/90"
                                            >
                                                <Send className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </form>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        onChange={handleFileSelect}
                                        className="hidden"
                                        accept="image/*,video/*,.pdf,.doc,.docx"
                                    />
                                </div>
                            </div>
                        </div>
                        <div className={`flex-1 flex items-center justify-center ${selectedContact ? 'hidden' : ''}`}>
                            <div className="text-center text-gray-500">
                                <div className="p-6 bg-primary/5 rounded-full w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                                    <MessageCircle className="h-12 w-12 text-primary/60" />
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 mb-2">{t('Select a contact')}</h3>
                                <p className="text-gray-500">{t('Choose a contact from the list to start messaging')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

        </AuthenticatedLayout>
    );
}
