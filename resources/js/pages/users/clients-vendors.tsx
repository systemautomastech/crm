import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Dialog } from "@/components/ui/dialog";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { DataTable } from "@/components/ui/data-table";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from "@/components/ui/per-page-selector";
import { Pagination } from "@/components/ui/pagination";
import { ConfirmationDialog } from "@/components/ui/confirmation-dialog";
import NoRecordsFound from '@/components/no-records-found';
import { Plus, Edit as EditIcon, Trash2, Eye, Building2, User as UserIcon, Users, Store } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Badge } from "@/components/ui/badge";

import CreateCustomer from '../../../../packages/automas/Account/src/Resources/js/Pages/Customers/Create';
import EditCustomer from '../../../../packages/automas/Account/src/Resources/js/Pages/Customers/Edit';
import ViewCustomer from '../../../../packages/automas/Account/src/Resources/js/Pages/Customers/View';

import CreateVendor from '../../../../packages/automas/Account/src/Resources/js/Pages/Vendors/Create';
import EditVendor from '../../../../packages/automas/Account/src/Resources/js/Pages/Vendors/Edit';
import ViewVendor from '../../../../packages/automas/Account/src/Resources/js/Pages/Vendors/View';

import CreateUser from './create';
import EditUser from './edit';

interface ClientsVendorsProps {
    isAccountActive: boolean;
    customers?: any;
    vendors?: any;
    clientUsers?: any[];
    vendorUsers?: any[];
    clients?: any;
    clientRole?: any;
    vendorRole?: any;
    auth: {
        user: {
            permissions: string[];
        };
    };
    [key: string]: any;
}

export default function ClientsVendors() {
    const { t } = useTranslation();
    const props = usePage<ClientsVendorsProps>().props;
    const {
        isAccountActive,
        customers,
        vendors,
        clientUsers = [],
        vendorUsers = [],
        clients,
        clientRole,
        vendorRole,
        auth
    } = props;

    useFlashMessages();

    const [activeTab, setActiveTab] = useState<'clients' | 'vendors'>('clients');

    // Customer / Vendor modal states (Account Active)
    const [customerModal, setCustomerModal] = useState<{
        isOpen: boolean;
        mode: 'create' | 'edit' | 'view' | '';
        data: any;
    }>({ isOpen: false, mode: '', data: null });

    const [vendorModal, setVendorModal] = useState<{
        isOpen: boolean;
        mode: 'create' | 'edit' | 'view' | '';
        data: any;
    }>({ isOpen: false, mode: '', data: null });

    // User modal states (Account Inactive)
    const [userModal, setUserModal] = useState<{
        isOpen: boolean;
        mode: 'create' | 'edit' | '';
        userType: 'client' | 'vendor';
        data: any;
    }>({ isOpen: false, mode: '', userType: 'client', data: null });

    // Delete handler for Customers
    const deleteCustomer = useDeleteHandler({
        routeName: 'account.customers.destroy',
        defaultMessage: t('Are you sure you want to delete this customer?')
    });

    // Delete handler for Vendors
    const deleteVendor = useDeleteHandler({
        routeName: 'account.vendors.destroy',
        defaultMessage: t('Are you sure you want to delete this vendor?')
    });

    // Delete handler for Basic Users
    const deleteUser = useDeleteHandler({
        routeName: 'users.destroy',
        defaultMessage: t('Are you sure you want to delete this user?')
    });

    // Search and filter handling
    const urlParams = new URLSearchParams(window.location.search);
    const [search, setSearch] = useState(urlParams.get('search') || '');

    const handleSearch = (value: string) => {
        setSearch(value);
        router.get(
            route('users.clients-vendors'),
            { search: value, tab: activeTab },
            { preserveState: true, replace: true }
        );
    };

    // Role mapping for basic user modal
    const clientRolesMap = clientRole ? { [clientRole.id]: clientRole.label || 'Client' } : {};
    const vendorRolesMap = vendorRole ? { [vendorRole.id]: vendorRole.label || 'Vendor' } : {};

    // Customer Columns (Account Active)
    const customerColumns = [
        {
            key: 'customer_code',
            header: t('Code'),
            render: (_: any, row: any) => (
                <span className="font-semibold text-primary">{row.customer_code}</span>
            )
        },
        {
            key: 'company_name',
            header: t('Company Name'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-2">
                    <Building2 className="h-4 w-4 text-muted-foreground shrink-0" />
                    <span className="font-medium text-foreground">{row.company_name}</span>
                </div>
            )
        },
        {
            key: 'contact_person_name',
            header: t('Contact Person'),
            render: (_: any, row: any) => row.contact_person_name || '-'
        },
        {
            key: 'contact_person_email',
            header: t('Email'),
            render: (_: any, row: any) => row.contact_person_email || '-'
        },
        {
            key: 'contact_person_mobile',
            header: t('Mobile'),
            render: (_: any, row: any) => row.contact_person_mobile || '-'
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-1">
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0"
                                    onClick={() => setCustomerModal({ isOpen: true, mode: 'view', data: row })}
                                >
                                    <Eye className="h-4 w-4 text-slate-500" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('View')}</p></TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0"
                                    onClick={() => setCustomerModal({ isOpen: true, mode: 'edit', data: row })}
                                >
                                    <EditIcon className="h-4 w-4 text-blue-600" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                    onClick={() => deleteCustomer.openDeleteDialog(row.id)}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
        }
    ];

    // Vendor Columns (Account Active)
    const vendorColumns = [
        {
            key: 'vendor_code',
            header: t('Code'),
            render: (_: any, row: any) => (
                <span className="font-semibold text-primary">{row.vendor_code}</span>
            )
        },
        {
            key: 'company_name',
            header: t('Company Name'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-2">
                    <Store className="h-4 w-4 text-muted-foreground shrink-0" />
                    <span className="font-medium text-foreground">{row.company_name}</span>
                </div>
            )
        },
        {
            key: 'contact_person_name',
            header: t('Contact Person'),
            render: (_: any, row: any) => row.contact_person_name || '-'
        },
        {
            key: 'contact_person_email',
            header: t('Email'),
            render: (_: any, row: any) => row.contact_person_email || '-'
        },
        {
            key: 'contact_person_mobile',
            header: t('Mobile'),
            render: (_: any, row: any) => row.contact_person_mobile || '-'
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-1">
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0"
                                    onClick={() => setVendorModal({ isOpen: true, mode: 'view', data: row })}
                                >
                                    <Eye className="h-4 w-4 text-slate-500" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('View')}</p></TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0"
                                    onClick={() => setVendorModal({ isOpen: true, mode: 'edit', data: row })}
                                >
                                    <EditIcon className="h-4 w-4 text-blue-600" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                    onClick={() => deleteVendor.openDeleteDialog(row.id)}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
        }
    ];

    // User Columns (Account Inactive)
    const userColumns = [
        {
            key: 'name',
            header: t('Name'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-2">
                    <UserIcon className="h-4 w-4 text-muted-foreground shrink-0" />
                    <span className="font-medium text-foreground">{row.name}</span>
                </div>
            )
        },
        {
            key: 'email',
            header: t('Email'),
            render: (_: any, row: any) => row.email
        },
        {
            key: 'mobile_no',
            header: t('Mobile'),
            render: (_: any, row: any) => row.mobile_no || '-'
        },
        {
            key: 'type',
            header: t('Type'),
            render: (_: any, row: any) => (
                <Badge variant="outline" className="capitalize">
                    {row.type}
                </Badge>
            )
        },
        {
            key: 'is_enable_login',
            header: t('Status'),
            render: (_: any, row: any) => (
                <Badge className={row.is_enable_login ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}>
                    {row.is_enable_login ? t('Active') : t('Disabled')}
                </Badge>
            )
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, row: any) => (
                <div className="flex items-center gap-1">
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0"
                                    onClick={() => setUserModal({ isOpen: true, mode: 'edit', userType: row.type === 'vendor' ? 'vendor' : 'client', data: row })}
                                >
                                    <EditIcon className="h-4 w-4 text-blue-600" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                    onClick={() => deleteUser.openDeleteDialog(row.id)}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('User Management'), url: route('users.index') },
                { label: t('Clients & Vendors') },
            ]}
            pageTitle={t('Clients & Vendors')}
            pageActions={
                <div className="flex items-center gap-2">
                    {isAccountActive ? (
                        activeTab === 'clients' ? (
                            <Button size="sm" onClick={() => setCustomerModal({ isOpen: true, mode: 'create', data: null })}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('Create Customer')}
                            </Button>
                        ) : (
                            <Button size="sm" onClick={() => setVendorModal({ isOpen: true, mode: 'create', data: null })}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('Create Vendor')}
                            </Button>
                        )
                    ) : (
                        activeTab === 'clients' ? (
                            <Button size="sm" onClick={() => setUserModal({ isOpen: true, mode: 'create', userType: 'client', data: null })}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('Create Client User')}
                            </Button>
                        ) : (
                            <Button size="sm" onClick={() => setUserModal({ isOpen: true, mode: 'create', userType: 'vendor', data: null })}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('Create Vendor User')}
                            </Button>
                        )
                    )}
                </div>
            }
        >
            <Head title={t('Clients & Vendors')} />

            <div className="space-y-4">
                <Tabs value={activeTab} onValueChange={(val) => setActiveTab(val as 'clients' | 'vendors')}>
                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <TabsList>
                            <TabsTrigger value="clients" className="flex items-center gap-2">
                                <Building2 className="h-4 w-4" />
                                <span>{t('Clients / Customers')}</span>
                            </TabsTrigger>
                            <TabsTrigger value="vendors" className="flex items-center gap-2">
                                <Store className="h-4 w-4" />
                                <span>{t('Vendors')}</span>
                            </TabsTrigger>
                        </TabsList>

                        <div className="w-full sm:w-72">
                            <SearchInput
                                value={search}
                                onChange={(val) => handleSearch(val)}
                                onSearch={() => handleSearch(search)}
                                placeholder={t('Search...')}
                            />
                        </div>
                    </div>

                    {/* ── CLIENTS / CUSTOMERS TAB CONTENT ───────────────────────────── */}
                    <TabsContent value="clients" className="pt-4 space-y-4">
                        <Card>
                            <CardContent className="p-0">
                                {isAccountActive ? (
                                    <DataTable
                                        data={customers?.data || []}
                                        columns={customerColumns}
                                        emptyState={
                                            <NoRecordsFound
                                                icon={Building2}
                                                title={t('No customers found')}
                                                description={t('Get started by adding your first customer.')}
                                                onCreateClick={() => setCustomerModal({ isOpen: true, mode: 'create', data: null })}
                                                createButtonText={t('Create Customer')}
                                            />
                                        }
                                    />
                                ) : (
                                    <DataTable
                                        data={clients?.data || []}
                                        columns={userColumns}
                                        emptyState={
                                            <NoRecordsFound
                                                icon={UserIcon}
                                                title={t('No client users found')}
                                                description={t('Get started by creating a client user.')}
                                                onCreateClick={() => setUserModal({ isOpen: true, mode: 'create', userType: 'client', data: null })}
                                                createButtonText={t('Create Client User')}
                                            />
                                        }
                                    />
                                )}
                            </CardContent>
                            <CardContent className="border-t bg-muted/20 px-4 py-2">
                                <Pagination
                                    data={isAccountActive ? customers : clients}
                                    routeName="users.clients-vendors"
                                />
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* ── VENDORS TAB CONTENT ─────────────────────────────────────── */}
                    <TabsContent value="vendors" className="pt-4 space-y-4">
                        <Card>
                            <CardContent className="p-0">
                                {isAccountActive ? (
                                    <DataTable
                                        data={vendors?.data || []}
                                        columns={vendorColumns}
                                        emptyState={
                                            <NoRecordsFound
                                                icon={Store}
                                                title={t('No vendors found')}
                                                description={t('Get started by adding your first vendor.')}
                                                onCreateClick={() => setVendorModal({ isOpen: true, mode: 'create', data: null })}
                                                createButtonText={t('Create Vendor')}
                                            />
                                        }
                                    />
                                ) : (
                                    <DataTable
                                        data={vendors?.data || []}
                                        columns={userColumns}
                                        emptyState={
                                            <NoRecordsFound
                                                icon={UserIcon}
                                                title={t('No vendor users found')}
                                                description={t('Get started by creating a vendor user.')}
                                                onCreateClick={() => setUserModal({ isOpen: true, mode: 'create', userType: 'vendor', data: null })}
                                                createButtonText={t('Create Vendor User')}
                                            />
                                        }
                                    />
                                )}
                            </CardContent>
                            <CardContent className="border-t bg-muted/20 px-4 py-2">
                                <Pagination
                                    data={isAccountActive ? vendors : vendors}
                                    routeName="users.clients-vendors"
                                />
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* ── ACCOUNT MODULE CUSTOMER MODALS ───────────────────────────── */}
            {isAccountActive && (
                <>
                    <Dialog
                        open={customerModal.isOpen && customerModal.mode === 'create'}
                        onOpenChange={(open) => !open && setCustomerModal({ isOpen: false, mode: '', data: null })}
                    >
                        {customerModal.isOpen && customerModal.mode === 'create' && (
                            <CreateCustomer
                                users={clientUsers}
                                auth={auth}
                                onSuccess={() => setCustomerModal({ isOpen: false, mode: '', data: null })}
                            />
                        )}
                    </Dialog>

                    <Dialog
                        open={customerModal.isOpen && customerModal.mode === 'edit'}
                        onOpenChange={(open) => !open && setCustomerModal({ isOpen: false, mode: '', data: null })}
                    >
                        {customerModal.isOpen && customerModal.mode === 'edit' && customerModal.data && (
                            <EditCustomer
                                customer={customerModal.data}
                                onSuccess={() => setCustomerModal({ isOpen: false, mode: '', data: null })}
                            />
                        )}
                    </Dialog>

                    <Dialog
                        open={customerModal.isOpen && customerModal.mode === 'view'}
                        onOpenChange={(open) => !open && setCustomerModal({ isOpen: false, mode: '', data: null })}
                    >
                        {customerModal.isOpen && customerModal.mode === 'view' && customerModal.data && (
                            <ViewCustomer customer={customerModal.data} />
                        )}
                    </Dialog>

                    {/* VENDOR MODALS */}
                    <Dialog
                        open={vendorModal.isOpen && vendorModal.mode === 'create'}
                        onOpenChange={(open) => !open && setVendorModal({ isOpen: false, mode: '', data: null })}
                    >
                        {vendorModal.isOpen && vendorModal.mode === 'create' && (
                            <CreateVendor
                                users={vendorUsers}
                                auth={auth}
                                onSuccess={() => setVendorModal({ isOpen: false, mode: '', data: null })}
                            />
                        )}
                    </Dialog>

                    <Dialog
                        open={vendorModal.isOpen && vendorModal.mode === 'edit'}
                        onOpenChange={(open) => !open && setVendorModal({ isOpen: false, mode: '', data: null })}
                    >
                        {vendorModal.isOpen && vendorModal.mode === 'edit' && vendorModal.data && (
                            <EditVendor
                                vendor={vendorModal.data}
                                onSuccess={() => setVendorModal({ isOpen: false, mode: '', data: null })}
                            />
                        )}
                    </Dialog>

                    <Dialog
                        open={vendorModal.isOpen && vendorModal.mode === 'view'}
                        onOpenChange={(open) => !open && setVendorModal({ isOpen: false, mode: '', data: null })}
                    >
                        {vendorModal.isOpen && vendorModal.mode === 'view' && vendorModal.data && (
                            <ViewVendor vendor={vendorModal.data} />
                        )}
                    </Dialog>
                </>
            )}

            {/* ── BASIC USER MODALS (ACCOUNT INACTIVE) ──────────────────────── */}
            {!isAccountActive && (
                <>
                    <Dialog
                        open={userModal.isOpen && userModal.mode === 'create'}
                        onOpenChange={(open) => !open && setUserModal({ isOpen: false, mode: '', userType: 'client', data: null })}
                    >
                        {userModal.isOpen && userModal.mode === 'create' && (
                            <CreateUser
                                roles={userModal.userType === 'vendor' ? vendorRolesMap : clientRolesMap}
                                onSuccess={() => setUserModal({ isOpen: false, mode: '', userType: 'client', data: null })}
                            />
                        )}
                    </Dialog>

                    <Dialog
                        open={userModal.isOpen && userModal.mode === 'edit'}
                        onOpenChange={(open) => !open && setUserModal({ isOpen: false, mode: '', userType: 'client', data: null })}
                    >
                        {userModal.isOpen && userModal.mode === 'edit' && userModal.data && (
                            <EditUser
                                user={userModal.data}
                                roles={userModal.userType === 'vendor' ? vendorRolesMap : clientRolesMap}
                                onSuccess={() => setUserModal({ isOpen: false, mode: '', userType: 'client', data: null })}
                            />
                        )}
                    </Dialog>
                </>
            )}

            {/* CONFIRMATION DIALOGS */}
            <ConfirmationDialog
                open={deleteCustomer.deleteState.isOpen}
                onOpenChange={deleteCustomer.closeDeleteDialog}
                title={t('Delete Customer')}
                message={deleteCustomer.deleteState.message}
                confirmText={t('Delete')}
                onConfirm={deleteCustomer.confirmDelete}
                variant="destructive"
            />

            <ConfirmationDialog
                open={deleteVendor.deleteState.isOpen}
                onOpenChange={deleteVendor.closeDeleteDialog}
                title={t('Delete Vendor')}
                message={deleteVendor.deleteState.message}
                confirmText={t('Delete')}
                onConfirm={deleteVendor.confirmDelete}
                variant="destructive"
            />

            <ConfirmationDialog
                open={deleteUser.deleteState.isOpen}
                onOpenChange={deleteUser.closeDeleteDialog}
                title={t('Delete User')}
                message={deleteUser.deleteState.message}
                confirmText={t('Delete')}
                onConfirm={deleteUser.confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
