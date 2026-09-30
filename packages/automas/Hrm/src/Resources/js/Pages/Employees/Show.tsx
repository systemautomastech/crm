import { Head, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Eye, FileText } from 'lucide-react';
import { formatDate, getImagePath, formatCurrency } from '@/utils/helpers';
import { useFormFields } from '@/hooks/useFormFields';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

export default function Show() {
    const { employee, documents } = usePage<any>().props;
    const { t } = useTranslation();

    const customFields = useFormFields('getCustomFields', { module: 'Hrm', sub_module: 'Employee', id: employee.id }, () => {}, {}, 'view', t);


    const getGenderText = (gender: string) => {
        // Handle both old numeric values and new string values
        const genders: any = { "0": "Male", "1": "Female", "2": "Other" };
        return genders[gender] || gender || "Male";
    };

    const getEmploymentTypeText = (type: string) => {
        const types: any = { "0": "Full Time", "1": "Part Time", "2": "Temporary", "3": "Contract" };
        return types[type] || type;
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Hrm'), url: route('hrm.index') },
                { label: t('Employees'), url: route('hrm.employees.index') },
                { label: employee.user?.name }
            ]}
            pageTitle={t('Employee Details')}
        >
            <Head title={t('Employee Details')} />

            <div className="space-y-3 sm:space-y-6">
                {/* Profile Section - Mobile/Tablet */}
                <div className="xl:hidden">
                    <Card className="shadow-sm">
                        <CardContent className="p-3 sm:p-6">
                            <div className="flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4 text-center sm:text-left">
                                <img
                                    src={employee.user?.avatar ? getImagePath(employee.user.avatar) : '/default-avatar.png'}
                                    alt={employee.user?.name || 'Employee'}
                                    className="w-16 h-16 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-gray-100 flex-shrink-0"
                                    onError={(e) => { e.currentTarget.src = '/default-avatar.png'; }}
                                />
                                <div className="min-w-0 flex-1 w-full">
                                    <h3 className="text-base sm:text-xl font-semibold mb-1">{employee.user?.name}</h3>
                                    <p className="text-muted-foreground text-xs sm:text-base mb-2">{employee.user?.email}</p>
                                    <p className="text-xs text-muted-foreground mb-2">{t('ID')}: {employee.employee_id}</p>
                                    
                                    {/* Key Info Grid - Mobile */}
                                    <div className="grid grid-cols-2 gap-2 sm:gap-3 text-left">
                                        <div>
                                            <p className="text-xs text-muted-foreground">{t('Branch')}</p>
                                            <p className="font-medium text-xs sm:text-sm truncate">{employee.branch?.branch_name}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">{t('Department')}</p>
                                            <p className="font-medium text-xs sm:text-sm truncate">{employee.department?.department_name}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">{t('Designation')}</p>
                                            <p className="font-medium text-xs sm:text-sm truncate">{employee.designation?.designation_name}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">{t('Gender')}</p>
                                            <p className="font-medium text-xs sm:text-sm">{t(getGenderText(employee.gender))}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {customFields.length > 0 && (
                                <div className="grid grid-cols-2 gap-2 sm:gap-3 border-t pt-3 mt-3">
                                    {customFields.map((field, index) => (
                                        <div key={index}>
                                            <p className="text-xs text-muted-foreground">{field.label}</p>
                                            <div className="font-medium text-xs sm:text-sm">{field.component}</div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Desktop Layout */}
                <div className="hidden xl:block">
                    <div className="grid xl:grid-cols-4 gap-6">
                        {/* Left Sidebar - Profile */}
                        <div className="xl:col-span-1">
                            <Card className="shadow-sm">
                                <CardContent className="p-6 text-center">
                                    <div className="mb-6">
                                        <img
                                            src={employee.user?.avatar ? getImagePath(employee.user.avatar) : '/default-avatar.png'}
                                            alt={employee.user?.name || 'Employee'}
                                            className="w-24 h-24 rounded-full object-cover mx-auto border-4 border-gray-100"
                                            onError={(e) => { e.currentTarget.src = '/default-avatar.png'; }}
                                        />
                                    </div>
                                    <h3 className="text-xl font-semibold mb-2">{employee.user?.name}</h3>
                                    <p className="text-muted-foreground mb-4">{employee.user?.email}</p>

                                    <div className="space-y-4 text-left">
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Employee ID')}</p>
                                            <p className="font-medium">{employee.employee_id}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Date of Birth')}</p>
                                            <p className="font-medium">{formatDate(employee.date_of_birth)}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Gender')}</p>
                                            <p className="font-medium">{t(getGenderText(employee.gender))}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Branch')}</p>
                                            <p className="font-medium">{employee.branch?.branch_name}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Department')}</p>
                                            <p className="font-medium">{employee.department?.department_name}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">{t('Designation')}</p>
                                            <p className="font-medium">{employee.designation?.designation_name}</p>
                                        </div>
                                        {customFields.length > 0 && customFields.map((field, index) => (
                                            <div key={index}>
                                                <p className="text-sm text-muted-foreground">{field.label}</p>
                                                <div className="font-medium">{field.component}</div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Right Content - Tabs */}
                        <div className="xl:col-span-3">
                            <Card className="shadow-sm">
                                <CardContent className="p-6">
                                    <Tabs defaultValue="employment" className="w-full">
                                        <TabsList className="grid w-full grid-cols-5">
                                            <TabsTrigger value="employment">{t('Employment')}</TabsTrigger>
                                            <TabsTrigger value="contact">{t('Contact')}</TabsTrigger>
                                            <TabsTrigger value="banking">{t('Banking')}</TabsTrigger>
                                            <TabsTrigger value="hours">{t('Hours & Rates')}</TabsTrigger>
                                            <TabsTrigger value="documents">{t('Documents')}</TabsTrigger>
                                        </TabsList>

                                        <TabsContent value="employment" className="space-y-6 mt-6">
                                            <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Employment Type')}</p>
                                                    <p className="font-medium">{t(getEmploymentTypeText(employee.employment_type))}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Date of Joining')}</p>
                                                    <p className="font-medium">{formatDate(employee.date_of_joining)}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Shift')}</p>
                                                    <p className="font-medium">{employee.shift?.shift_name || '-'}</p>
                                                </div>
                                            </div>
                                        </TabsContent>

                                        <TabsContent value="contact" className="space-y-6 mt-6">
                                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Address Line 1')}</p>
                                                    <p className="font-medium">{employee.address_line_1}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Address Line 2')}</p>
                                                    <p className="font-medium">{employee.address_line_2 || '-'}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('City')}</p>
                                                    <p className="font-medium">{employee.city}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('State')}</p>
                                                    <p className="font-medium">{employee.state}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Country')}</p>
                                                    <p className="font-medium">{employee.country}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Postal Code')}</p>
                                                    <p className="font-medium">{employee.postal_code}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Emergency Contact Name')}</p>
                                                    <p className="font-medium">{employee.emergency_contact_name}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Emergency Contact Relationship')}</p>
                                                    <p className="font-medium">{employee.emergency_contact_relationship}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Emergency Contact Number')}</p>
                                                    <p className="font-medium">{employee.emergency_contact_number}</p>
                                                </div>
                                            </div>
                                        </TabsContent>

                                        <TabsContent value="banking" className="space-y-6 mt-6">
                                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Bank Name')}</p>
                                                    <p className="font-medium">{employee.bank_name}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Account Holder Name')}</p>
                                                    <p className="font-medium">{employee.account_holder_name}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Account Number')}</p>
                                                    <p className="font-medium break-all">{employee.account_number}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Bank Identifier Code')}</p>
                                                    <p className="font-medium break-all">{employee.bank_identifier_code}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Bank Branch')}</p>
                                                    <p className="font-medium">{employee.bank_branch}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Tax Payer ID')}</p>
                                                    <p className="font-medium break-all">{employee.tax_payer_id || '-'}</p>
                                                </div>
                                            </div>
                                        </TabsContent>

                                        <TabsContent value="hours" className="space-y-6 mt-6">
                                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Hours Per Day')}</p>
                                                    <p className="font-medium">{employee.hours_per_day || '-'}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Days Per Week')}</p>
                                                    <p className="font-medium">{employee.days_per_week || '-'}</p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-muted-foreground mb-1">{t('Rate Per Hour')}</p>
                                                    <p className="font-medium">{employee.rate_per_hour ? formatCurrency(employee.rate_per_hour) : '-'}</p>
                                                </div>
                                            </div>
                                        </TabsContent>

                                        <TabsContent value="documents" className="space-y-6 mt-6">
                                            {documents && documents.length > 0 ? (
                                                <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                                    {documents.map((doc: any, index: number) => (
                                                        <Card key={doc.id || index} className="p-4">
                                                            <div className="flex justify-between items-center">
                                                                <div className="flex-1 min-w-0">
                                                                    <p className="font-medium truncate">{doc.document_name || doc.title || 'Document'}</p>
                                                                    <p className="text-sm text-muted-foreground truncate">
                                                                        {doc.file_path ? doc.file_path.split('/').pop() : doc.document ? doc.document.split('/').pop() : 'No file'}
                                                                    </p>
                                                                    {doc.document_type && (
                                                                        <Badge variant="secondary" className="mt-1 text-xs">
                                                                            {doc.document_type}
                                                                        </Badge>
                                                                    )}
                                                                </div>
                                                                {(doc.file_path || doc.document) && (
                                                                    <TooltipProvider>
                                                                        <Tooltip delayDuration={0}>
                                                                            <TooltipTrigger asChild>
                                                                                <Button
                                                                                    variant="ghost"
                                                                                    size="sm"
                                                                                    asChild
                                                                                    className="h-9 w-9 p-0 text-green-600 hover:text-green-700"
                                                                                >
                                                                                    <a
                                                                                        href={getImagePath(doc.file_path || doc.document)}
                                                                                        target="_blank"
                                                                                        rel="noopener noreferrer"
                                                                                    >
                                                                                        <Eye className="h-4 w-4" />
                                                                                    </a>
                                                                                </Button>
                                                                            </TooltipTrigger>
                                                                            <TooltipContent>
                                                                                <p>{t('View')}</p>
                                                                            </TooltipContent>
                                                                        </Tooltip>
                                                                    </TooltipProvider>
                                                                )}
                                                            </div>
                                                        </Card>
                                                    ))}
                                                </div>
                                            ) : (
                                                <div className="flex flex-col items-center justify-center py-12 text-center">
                                                    <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                        <FileText className="h-8 w-8 text-gray-400" />
                                                    </div>
                                                    <h3 className="text-lg font-medium text-gray-900 mb-2">{t('No Documents Found')}</h3>
                                                    <p className="text-sm text-muted-foreground max-w-sm">
                                                        {t('No documents have been uploaded for this employee yet.')}
                                                    </p>
                                                </div>
                                            )}
                                        </TabsContent>
                                    </Tabs>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                {/* Mobile/Tablet Content - Tabs */}
                <div className="xl:hidden">
                    <Card className="shadow-sm">
                        <CardContent className="p-2 sm:p-4">
                            <Tabs defaultValue="employment" className="w-full">
                                <TabsList className="flex w-full overflow-x-auto scrollbar-none gap-0.5 p-0.5">
                                    <TabsTrigger value="employment" className="flex-shrink-0 px-2 py-1.5 text-xs sm:text-sm whitespace-nowrap">{t('Job')}</TabsTrigger>
                                    <TabsTrigger value="contact" className="flex-shrink-0 px-2 py-1.5 text-xs sm:text-sm whitespace-nowrap">{t('Contact')}</TabsTrigger>
                                    <TabsTrigger value="banking" className="flex-shrink-0 px-2 py-1.5 text-xs sm:text-sm whitespace-nowrap">{t('Bank')}</TabsTrigger>
                                    <TabsTrigger value="hours" className="flex-shrink-0 px-2 py-1.5 text-xs sm:text-sm whitespace-nowrap">{t('Hours')}</TabsTrigger>
                                    <TabsTrigger value="documents" className="flex-shrink-0 px-2 py-1.5 text-xs sm:text-sm whitespace-nowrap">{t('Docs')}</TabsTrigger>
                                </TabsList>

                                <TabsContent value="employment" className="space-y-3 mt-3">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Employment Type')}</p>
                                            <p className="font-medium text-sm">{t(getEmploymentTypeText(employee.employment_type))}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Date of Joining')}</p>
                                            <p className="font-medium text-sm">{formatDate(employee.date_of_joining)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Shift')}</p>
                                            <p className="font-medium text-sm">{employee.shift?.shift_name || 'N/A'}</p>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="contact" className="space-y-3 mt-3">
                                    <div className="space-y-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Address Line 1')}</p>
                                            <p className="font-medium text-sm break-words">{employee.address_line_1}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Address Line 2')}</p>
                                            <p className="font-medium text-sm break-words">{employee.address_line_2 || '-'}</p>
                                        </div>
                                        <div className="grid grid-cols-2 gap-3">
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('City')}</p>
                                                <p className="font-medium text-sm">{employee.city}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('State')}</p>
                                                <p className="font-medium text-sm">{employee.state}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Country')}</p>
                                                <p className="font-medium text-sm">{employee.country}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Postal Code')}</p>
                                                <p className="font-medium text-sm">{employee.postal_code}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Emergency Contact')}</p>
                                            <div className="space-y-1">
                                                <p className="font-medium text-sm">{employee.emergency_contact_name}</p>
                                                <p className="text-xs text-muted-foreground">{employee.emergency_contact_relationship}</p>
                                                <p className="text-sm">{employee.emergency_contact_number}</p>
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="banking" className="space-y-3 mt-3">
                                    <div className="space-y-3">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Bank Name')}</p>
                                                <p className="font-medium text-sm">{employee.bank_name}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Account Holder')}</p>
                                                <p className="font-medium text-sm">{employee.account_holder_name}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Account Number')}</p>
                                            <p className="font-medium text-sm break-all">{employee.account_number}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Bank Identifier Code')}</p>
                                            <p className="font-medium text-sm break-all">{employee.bank_identifier_code}</p>
                                        </div>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Bank Branch')}</p>
                                                <p className="font-medium text-sm">{employee.bank_branch}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-muted-foreground mb-1">{t('Tax Payer ID')}</p>
                                                <p className="font-medium text-sm break-all">{employee.tax_payer_id || '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="hours" className="space-y-3 mt-3">
                                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Hours Per Day')}</p>
                                            <p className="font-medium text-sm">{employee.hours_per_day || 'N/A'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Days Per Week')}</p>
                                            <p className="font-medium text-sm">{employee.days_per_week || 'N/A'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground mb-1">{t('Rate Per Hour')}</p>
                                            <p className="font-medium text-sm">{employee.rate_per_hour ? formatCurrency(employee.rate_per_hour) : 'N/A'}</p>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="documents" className="space-y-2 mt-3">
                                    {documents && documents.length > 0 ? (
                                        <div className="space-y-2">
                                            {documents.map((doc: any, index: number) => (
                                                <Card key={doc.id || index} className="p-2">
                                                    <div className="flex justify-between items-start gap-2">
                                                        <div className="min-w-0 flex-1">
                                                            <p className="font-medium text-xs truncate">{doc.document_name || doc.title || 'Document'}</p>
                                                            <p className="text-xs text-muted-foreground truncate mt-0.5">
                                                                {doc.file_path ? doc.file_path.split('/').pop() : doc.document ? doc.document.split('/').pop() : 'No file'}
                                                            </p>
                                                            {doc.document_type && (
                                                                <Badge variant="secondary" className="mt-1 text-xs px-1 py-0.5">
                                                                    {doc.document_type}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        {(doc.file_path || doc.document) && (
                                                            <TooltipProvider>
                                                                <Tooltip delayDuration={0}>
                                                                    <TooltipTrigger asChild>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            asChild
                                                                            className="h-6 w-6 p-0 text-green-600 hover:text-green-700"
                                                                        >
                                                                            <a
                                                                                href={getImagePath(doc.file_path || doc.document)}
                                                                                target="_blank"
                                                                                rel="noopener noreferrer"
                                                                            >
                                                                                <Eye className="h-3 w-3" />
                                                                            </a>
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <p>{t('View')}</p>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            </TooltipProvider>
                                                        )}
                                                    </div>
                                                </Card>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-4 text-muted-foreground">
                                            <p className="text-xs">{t('No documents uploaded.')}</p>
                                        </div>
                                    )}
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
