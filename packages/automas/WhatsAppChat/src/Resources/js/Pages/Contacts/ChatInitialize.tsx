import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Check, ChevronDown, Search } from "lucide-react";
import { cn } from "@/lib/utils";
import InputError from "@/components/ui/input-error";
import { ChatInitializeProps, ChatInitializeFormData } from './types';
import { useState, useEffect } from 'react';

export default function ChatInitialize({ onSuccess, users }: ChatInitializeProps) {
    const { t } = useTranslation();
    const [selectedType, setSelectedType] = useState<'choose_from_users' | 'custom'>('choose_from_users');
    const [open, setOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState('');
    const [search, setSearch] = useState('');
    
    const { data, setData, post, processing, errors } = useForm<ChatInitializeFormData>({
        type: 'choose_from_users',
        user_id: undefined,
        name: '',
        contact_no: '',
        message: '',
    });

    useEffect(() => {
        setData('type', selectedType);
        if (selectedType === 'custom') {
            setData('contact_no', '');
            setData('user_id', undefined);
            setSelectedUser('');
        }
    }, [selectedType]);

    const handleUserSelect = (userId: string, userName: string) => {
        setData('user_id', parseInt(userId));
        setSelectedUser(userName);
        setOpen(false);
        setSearch('');
        
        // Extract name from userName (format: "Name - Phone")
        const userInfo = userName.split(' - ');
        const name = userInfo[0];
        const contactNo = userInfo[1];
        
        setData('name', name); // Set the name
        
        if (contactNo && contactNo.trim() !== '' && contactNo !== 'undefined') {
            setData('contact_no', contactNo);
        } else {
            setData('contact_no', '');
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('send.whatsappchat.message'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    const filteredUsers = Object.entries(users).filter(([id, name]) => 
        (!search || name.toLowerCase().includes(search.toLowerCase()))
    );

    return (
        <DialogContent className="max-w-md">
            <DialogHeader>
                <DialogTitle>{t('Chat with new user')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label>{t('Type')}</Label>
                    <RadioGroup 
                        value={selectedType} 
                        onValueChange={(value) => setSelectedType(value as 'choose_from_users' | 'custom')}
                        className="flex gap-4 mt-2"
                    >
                        <div className="flex items-center space-x-2">
                            <RadioGroupItem value="choose_from_users" id="choose_from_users" />
                            <Label htmlFor="choose_from_users">{t('Choose From Users')}</Label>
                        </div>
                        <div className="flex items-center space-x-2">
                            <RadioGroupItem value="custom" id="custom" />
                            <Label htmlFor="custom">{t('Custom')}</Label>
                        </div>
                    </RadioGroup>
                </div>

                {selectedType === 'choose_from_users' ? (
                    <div>
                        <Label htmlFor="user_id">{t('Select User')}</Label>
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    aria-expanded={open}
                                    className="w-full justify-between"
                                >
                                    {selectedUser || t('Select User')}
                                    <ChevronDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[var(--radix-popover-trigger-width)] p-0">
                                <div className="flex flex-col">
                                    <div className="flex items-center border-b px-3 py-2">
                                        <Search className="mr-2 h-4 w-4 shrink-0 opacity-50" />
                                        <Input
                                            placeholder={t('Search users...')}
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="h-8 border-0 p-0 focus:border-primary"
                                        />
                                    </div>
                                    <div className="max-h-[200px] overflow-y-auto">
                                        {filteredUsers.length === 0 ? (
                                            <div className="py-6 text-center text-sm text-muted-foreground">
                                                {t('No user found.')}
                                            </div>
                                        ) : (
                                            filteredUsers.map(([id, name]) => (
                                                <div
                                                    key={id}
                                                    className={cn(
                                                        "relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground",
                                                        selectedUser === name && "bg-accent text-accent-foreground"
                                                    )}
                                                    onClick={() => handleUserSelect(id, name)}
                                                >
                                                    <Check
                                                        className={cn(
                                                            "mr-2 h-4 w-4",
                                                            selectedUser === name ? "opacity-100" : "opacity-0"
                                                        )}
                                                    />
                                                    {name}
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.user_id} />
                    </div>
                ) : (
                    <div>
                        <Label htmlFor="name">{t('Name')}</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder={t('Enter Name')}
                        />
                        <InputError message={errors.name} />
                    </div>
                )}

                <div>
                    <Label htmlFor="contact_no">{t('Contact')}</Label>
                    <Input
                        id="contact_no"
                        value={data.contact_no}
                        onChange={(e) => setData('contact_no', e.target.value)}
                        placeholder="919999999999"
                        required
                    />
                    <p className="text-xs text-red-500 mt-1">
                        {t('Please use with country code. (ex. 91)')}
                    </p>
                    <InputError message={errors.contact_no} />
                </div>

                <div>
                    <Label htmlFor="message">{t('Message')}</Label>
                    <Textarea
                        id="message"
                        value={data.message}
                        onChange={(e) => setData('message', e.target.value)}
                        placeholder={t('Type Message')}
                        required
                        rows={3}
                    />
                    <InputError message={errors.message} />
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onSuccess()}>
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