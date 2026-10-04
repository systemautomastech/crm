<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\EmailTemplateLang;
use App\Mail\CommonEmailTemplate;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'from',
        'module_name',
        'creator_id',
        'created_by',
    ];

    public function template()
    {
        return $this->hasOne('App\Models\UserEmailTemplate'::class, 'template_id', 'id')->where('user_id', '=', Auth::user()->id);
    }

    public function templateLangs()
    {
        return $this->hasMany(EmailTemplateLang::class, 'parent_id');
    }

    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj, $user_id = null, $creator_id = null)
    {
        if (!empty($user_id)) {
            $usr = User::where('id', $user_id)->first();
        } else {
            $usr = Auth::user();
        }

        //Remove Current Login user Email don't send mail to them

        $mailTo = array_values($mailTo);
        // find template is exist or not in our record
        $template = EmailTemplate::where('name', $emailTemplate)->first();
        if (isset($template) && !empty($template)) {

            $lang = company_setting('defaultLanguage',$usr->id) ?? 'en';

            // get email content language base
            $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $lang)->first();

            if ($content) {
                $content->from = $template->from;
            } else {
                return [
                    'is_success' => false,
                    'error' => __('Email template content not found'),
                ];
            }

            if (!empty($content->content)) {
                $content->content = self::replaceVariable($content->content, $obj);
                $content->subject = self::replaceVariable($content->subject, $obj);
                // send email
                $companySettings = getCompanyAllSetting($user_id);
                if (!empty($companySettings['email_fromAddress'] ?? '')) {
                    if (!empty($user_id)) {
                        $setconfing = SetConfigEmail($user_id);
                    } else {
                        $setconfing = SetConfigEmail();
                    }
                    if ($setconfing == true) {
                        try {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $user_id));
                        } catch (\Exception $e) {
                            $error = $e->getMessage();
                        }
                    } else {
                        $error = __('Something went wrong please try again ');
                    }
                } else {
                    $error = __('E-Mail has been not sent due to SMTP configuration');
                }

                if (isset($error)) {
                    $arReturn = [
                        'is_success' => false,
                        'error' => $error,
                    ];
                } else {
                    $arReturn = [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            } else {
                $arReturn = [
                    'is_success' => false,
                    'error' => __('Mail not send, email is empty'),
                ];
            }
            return $arReturn;
        } else {
            return [
                'is_success' => false,
                'error' => __('Mail not send, email not found'),
            ];
        }
    }    

    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{app_name}',
            '{app_url}',
            '{company_name}',

            '{name}',
            '{email}',
            '{password}',

            '{invoice_number}',
            '{warehouse_name}',
            '{total_amount}',
            '{discount_amount}',

            '{sales_customer_name}',           
           
            '{return_number}',
            '{return_date}',
           
            '{purchase_vendor_name}',

            '{status}',

            '{plan_name}',
            '{plan_price}',
            '{plan_duration}',
                
            '{item_name}',
            '{item_details}',

            '{budget}',
            '{end_date}',

            '{milestone_name}',
            '{title}',
            '{duration}',
            
            '{employee_name}',
            '{award_type}',
            '{award_date}',

            '{previous_branch_name}',
            '{previous_department_name}',
            '{previous_designation_name}',
            '{current_branch_name}',
            '{current_department_name}',
            '{current_designation_name}',
            '{effective_date}',
            '{reason}',
            '{last_working_date}',
            
            '{warning_type_name}',
            '{subject}',

            '{from_branch_name}',
            '{from_department_name}',
            '{from_designation_name}',
            '{to_branch_name}',
            '{to_department_name}',
            '{to_designation_name}',
            '{transfer_date}',

            '{leave_type}',
            '{total_days}',

            '{payment_number}',
            '{payment_date}',
            '{customer_name}',
            '{payment_amount}',
            '{reference_number}',
           
            '{vendor_name}',

            '{payroll_frequency}',
            '{pay_period_start}',
            '{pay_period_end}',
            '{pay_date}',
            '{basic_salary}',
            '{total_allowances}',
            '{total_loans}',
            '{gross_pay}',
            '{net_pay}',
            '{working_days}',
            '{present_days}',
            '{absent_days}',
            '{half_days}',
            '{paid_leave_days}',
            '{unpaid_leave_days}',
            '{overtime_hours}',

            '{debit_note_number}',
            '{debit_note_date}',
            '{credit_note_number}',
            '{credit_note_date}',

            '{file_name}',
            '{file_size}',
            '{download_link}',

            '{appointment_name}',
            '{appointment_user_name}',
            '{appointment_user_email}',
            '{appointment_date}',
            '{appointment_time}',
            '{appointment_number}',
            '{appointment_status}',
            '{callback_date}',
            '{callback_time}',
            '{callback_reason}',
            '{callback_status}',

            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{deal_old_stage}',
            '{deal_new_stage}',

            '{task_name}',
            '{task_priority}',
            '{task_status}',

            '{lead_name}',
            '{lead_email}',
            '{lead_subject}',
            '{follow_up_date}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{lead_old_stage}',
            '{lead_new_stage}',

            '{lead_email_subject}',
            '{lead_email_description}',

            '{deal_email_subject}',
            '{deal_email_description}',

            '{tracking_id}',
            '{tracking_url}',
            '{package_title}',


            '{candidate_name}',
            '{candidate_email}',
            '{job_title}',
            '{tracking_link}',
            '{position}',
            '{salary}',
            '{start_date}',
            '{download_url}',

            '{invoice_id}',
            '{invoice_tenant}',
            '{invoice_status}',
            '{invoice_sub_total}',
            '{created_at}',

            '{doctor_name}',
            '{doctor_email}',
            '{doctor_id}',
            '{specialization}',
            '{patient_name}',
            '{patient_email}',
            '{patient_id}',
            '{bed_number}',
            '{ward_name}',
            '{bed_type}',
            '{admission_date}',
            '{discharge_date}',

            '{ticket_name}',
            '{ticket_id}',
            '{ticket_url}',
            '{ticket_password}',
            '{ticket_description}',
            '{ticket_category}',
            '{ticket_priority}',
            '{reply_description}',

            '{child_name}',
            '{parent_name}',
            '{inquiry_date}',
            '{inquiry_status}',
            '{parent_email}',
            '{login_link}',

            '{request_customer_name}',
            '{request_customer_email}',
            '{request_customer_phone}',
            '{request_date}',
            '{request_time}',
            '{request_location}',
            '{request_pickup_point}',
            '{request_category_type}',
            '{request_category}',

            '{request_id}',

            '{meeting_title}',
            '{meeting_date}',
            '{meeting_time}',
            '{meeting_location}',
            '{meeting_description}',
            '{organizer_name}',
            '{attendees_list}',

            '{account_name}',
            '{account_email}',
            '{account_phone}',
            '{account_website}',
            '{account_type}',
            '{account_industry}',
            '{billing_address}',
            '{billing_city}',
            '{billing_state}',
            '{billing_country}',
            '{billing_postal_code}',
            '{account_description}',
            '{assigned_user}',
            '{created_by}',

            '{opportunity_name}',
            '{opportunity_amount}',
            '{opportunity_expected_amount}',
            '{opportunity_probability}',
            '{opportunity_close_date}',
            '{opportunity_next_followup_date}',
            '{opportunity_lead_source}',
            '{opportunity_next_step}',
            '{opportunity_description}',
            '{opportunity_account}',
            '{opportunity_contact}',
            '{opportunity_stage}',

            '{opportunity_new_stage}',

            '{quote_number}',
            '{quote_name}',
            '{quote_amount}',
            '{quote_date}',
            '{quote_expiry_date}',
            '{quote_status}',
            '{quote_old_status}',
            '{quote_account}',
            '{quote_opportunity}',

            '{order_number}',
            '{order_name}',
            '{order_amount}',
            '{order_date}',
            '{order_status}',
            '{order_old_status}',
            '{order_account}',
            '{order_opportunity}',
            '{order_quote}',

            '{contact_name}',
            '{contact_email}',
            '{contact_phone}',
            '{contact_department}',
            '{contact_account}',
            '{contact_address}',
            '{contact_city}',
            '{contact_state}',
            '{contact_country}',
            '{contact_postal_code}',
        ];
        $arrValue    = [
            'app_name' => '-',
            'app_url' => '-',
            'company_name' => '-',

            'name' => '-',
            'email' => '-',
            'password' => '-',

            'invoice_number' => '-',
            'warehouse_name' => '-',
            'total_amount' => '-',
            'discount_amount' => '-',

            'sales_customer_name' => '-',       
           
            'return_number' => '-',
            'return_date' => '-',
           
            'purchase_vendor_name' => '-',

            'status' => '-',

            'plan_name' => '-',
            'plan_price' => '-',
            'plan_duration' => '-',

            'item_name' => '-',
            'item_details' => '-',

            'budget' => '-',
            'end_date' => '-',

            'milestone_name' => '-',
            'title' => '-',
            'duration' => '-',

            'employee_name' => '-',
            'award_type' => '-',
            'award_date' => '-',
            
            'previous_branch_name' => '-',
            'previous_department_name' => '-',
            'previous_designation_name' => '-',
            'current_branch_name' => '-',
            'current_department_name' => '-',
            'current_designation_name' => '-',
            'effective_date' => '-',
            'reason' => '-',
            'last_working_date' => '-',

            'warning_type_name' => '-',
            'subject' => '-',

            'from_branch_name' => '-',
            'from_department_name' => '-',
            'from_designation_name' => '-',
            'to_branch_name' => '-',
            'to_department_name' => '-',
            'to_designation_name' => '-',
            'transfer_date' => '-',

            'leave_type' => '-',
            'total_days' => '-',
            
            'payment_number' => '-',
            'payment_date' => '-',
            'customer_name' => '-',
            'payment_amount' => '-',
            'reference_number' => '-',

            'vendor_name' => '-',

            'payroll_frequency' => '-',
            'pay_period_start' => '-',
            'pay_period_end' => '-',
            'pay_date' => '-',
            'basic_salary' => '-',
            'total_allowances' => '-',
            'total_loans' => '-',
            'gross_pay' => '-',
            'net_pay' => '-',
            'working_days' => '-',
            'present_days' => '-',
            'absent_days' => '-',
            'half_days' => '-',
            'paid_leave_days' => '-',
            'unpaid_leave_days' => '-',
            'overtime_hours' => '-',

            'debit_note_number' => '-',
            'debit_note_date' => '-',
            'credit_note_number' => '-',
            'credit_note_date' => '-',
            
            'file_name' => '-',
            'file_size' => '-',
            'download_link' => '-',


            'appointment_name' => '-',
            'appointment_user_name' => '-',
            'appointment_user_email' => '-',
            'appointment_date' => '-',
            'appointment_time' => '-',
            'appointment_number' => '-',
            'appointment_status' => '-',
            'callback_date' => '-',
            'callback_time' => '-',
            'callback_reason' => '-',
            'callback_status' => '-',

            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'deal_old_stage' => '-',
            'deal_new_stage' => '-',

            'task_name' => '-',
            'task_priority' => '-',
            'task_status' => '-',

            'lead_name' => '-',
            'lead_email' => '-',
            'lead_subject' => '-',
            'follow_up_date' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'lead_old_stage' => '-',
            'lead_new_stage' => '-',

            'lead_email_subject' => '-',
            'lead_email_description' => '-',

            'deal_email_subject' => '-',
            'deal_email_description' => '-',

            'tracking_id' => '-',
            'tracking_url' => '-',
            'package_title' => '-',

            'candidate_name' => '-',
            'candidate_email' => '-',
            'job_title' => '-',
            'tracking_link' => '-',
            'position' => '-',
            'salary' => '-',
            'start_date' => '-',
            'download_url' => '-',

            'invoice_id' => '-',
            'invoice_tenant' => '-',
            'invoice_status' => '-',
            'invoice_sub_total' => '-',
            'created_at' => '-',

            'doctor_name' => '-',
            'doctor_email' => '-',
            'doctor_id' => '-',
            'specialization' => '-',
            'patient_name' => '-',
            'patient_email' => '-',
            'patient_id' => '-',
            'bed_number' => '-',
            'ward_name' => '-',
            'bed_type' => '-',
            'admission_date' => '-',
            'discharge_date' => '-',

            'ticket_name' => '-',
            'ticket_id' => '-',
            'ticket_url' => '-',
            'ticket_password' => '-',
            'ticket_description' => '-',
            'ticket_category' => '-',
            'ticket_priority' => '-',
            'reply_description' => '-',

            'child_name' => '-',
            'parent_name' => '-',
            'inquiry_date' => '-',
            'inquiry_status' => '-',
            'parent_email' => '-',
            'login_link' => '-',

            'request_customer_name'     => '-',
            'request_customer_email'    => '-',
            'request_customer_phone'    => '-',
            'request_date'              => '-',
            'request_time'              => '-',
            'request_location'          => '-',
            'request_pickup_point'      => '-',
            'request_category_type'     => '-',
            'request_category'          => '-',

            'request_id'     => '-',

            'meeting_title'    => '-',
            'meeting_date'    => '-',
            'meeting_time'    => '-',
            'meeting_location'    => '-',
            'meeting_description'    => '-',
            'organizer_name'    => '-',
            'attendees_list'    => '-',

            'account_name' => '-',
            'account_email' => '-',
            'account_phone' => '-',
            'account_website' => '-',
            'account_type' => '-',
            'account_industry' => '-',
            'billing_address' => '-',
            'billing_city' => '-',
            'billing_state' => '-',
            'billing_country' => '-',
            'billing_postal_code' => '-',
            'account_description' => '-',
            'assigned_user' => '-',
            'created_by' => '-',

            'opportunity_name' => '-',
            'opportunity_amount' => '-',
            'opportunity_expected_amount' => '-',
            'opportunity_probability' => '-',
            'opportunity_close_date' => '-',
            'opportunity_next_followup_date' => '-',
            'opportunity_lead_source' => '-',
            'opportunity_next_step' => '-',
            'opportunity_description' => '-',
            'opportunity_account' => '-',
            'opportunity_contact' => '-',
            'opportunity_stage' => '-',

            'opportunity_new_stage' => '-',

            'quote_number' => '-',
            'quote_name' => '-',
            'quote_amount' => '-',
            'quote_date' => '-',
            'quote_expiry_date' => '-',
            'quote_status' => '-',
            'quote_old_status' => '-',
            'quote_account' => '-',
            'quote_opportunity' => '-',

            'order_number' => '-',
            'order_name' => '-',
            'order_amount' => '-',
            'order_date' => '-',
            'order_status' => '-',
            'order_old_status' => '-',
            'order_account' => '-',
            'order_opportunity' => '-',
            'order_quote' => '-',

            'contact_name' => '-',
            'contact_email' => '-',
            'contact_phone' => '-',
            'contact_department' => '-',
            'contact_account' => '-',
            'contact_address' => '-',
            'contact_city' => '-',
            'contact_state' => '-',
            'contact_country' => '-',
            'contact_postal_code' => '-',
        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
            if ($key === 'ticket_password') {
                $arrValue['password'] = $val;
            }
            if ($key === 'password') {
                $arrValue['ticket_password'] = $val;
            }
        }
        $arrValue['app_name']     = env('APP_NAME');
        if (is_null($arrValue['company_name']) || $arrValue['company_name'] == '-') {
            $companySettings = getCompanyAllSetting();
            $arrValue['company_name'] = $companySettings['company_name'] ?? '--';
        }
        $arrValue['app_url']      = env('APP_URL');


        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function workflowsendEmail($action, $workflow)
    {
        $to = isset($action->config['to']) ? $action->config['to'] : null;
        $companySettings = getCompanyAllSetting();

        if(!empty($to))
        {
            $content = [
                'from' => !empty(company_setting('company_name')) ? company_setting('company_name') : env('APP_NAME'),
                'subject' => $workflow->submodule . ' ' . __('Workflow Notification'),
                'content'=> $action->message,
            ];

            $content = (object)$content;
            $user_id = Auth::user()->id;

            if (!isset($companySettings['email_fromAddress']) || empty($companySettings['email_fromAddress'])) {
                throw new \Exception(__('E-Mail has been not sent due to SMTP configuration'));
            }

            $setconfing = SetConfigEmail();
            if ($setconfing != true) {
                throw new \Exception(__('Something went wrong please try again'));
            }

            Mail::to($to)->send(new CommonEmailTemplate($content, $user_id));
        }
    }

    public static function reminderSendEmail($reminder, $moduleData)
    {
        $actionConfig = is_array($reminder->action_config) ? $reminder->action_config[$reminder->actions] ?? [] : [];
        $to = $actionConfig['email'] ?? null;
        $companySettings = getCompanyAllSetting($reminder->created_by);

        if(!empty($to))
        {
            $content = [
                'from' => !empty(company_setting('company_name', $reminder->created_by)) 
                    ? company_setting('company_name', $reminder->created_by) 
                    : env('APP_NAME'),
                'subject' => $reminder->name . ' - ' . __('Reminder Notification'),
                'content' => $actionConfig['message'] ?? '',
            ];
            $content = (object)$content;

            if (!isset($companySettings['email_fromAddress']) || empty($companySettings['email_fromAddress'])) {
                throw new \Exception(__('E-Mail has been not sent due to SMTP configuration'));
            }

            $setconfing = SetConfigEmail($reminder->created_by);
            if ($setconfing != true) {
                throw new \Exception(__('Something went wrong please try again'));
            }

            Mail::to($to)->send(new CommonEmailTemplate($content, $reminder->created_by));
        }
    }
}
