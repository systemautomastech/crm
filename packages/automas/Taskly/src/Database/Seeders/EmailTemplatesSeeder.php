<?php

namespace Automas\Taskly\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\User;

class EmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('type','company')->first();

        $emailTemplate = [
            'Create Project',
            'Project Task',
            'Project Assign to Client',
        ];
        $defaultTemplate = [
            'Create Project' => [
                'subject' => 'Project Created',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Name ": "name",
                    "Budget": "budget",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">إشعار المشروع</p>
                    </div>

                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>مرحباً،</p>

                    <p>
                    أخبار رائعة! تم إنشاء مشروع جديد لك بواسطة <strong>{company_name}</strong>.
                    يمكنك مراجعة التفاصيل أدناه.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">

                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">تفاصيل المشروع</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>الاسم</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{name}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>الميزانية</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>تاريخ البدء</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>تاريخ الانتهاء</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>

                    </div>

                    <p>
                    يمكنك تسجيل الدخول إلى التطبيق لمتابعة تقدم المشروع.
                    </p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    عرض المشروع
                    </a>
                    </div>

                    <p>إذا كنت بحاجة إلى أي مساعدة، فلا تتردد في التواصل معنا.</p>

                    <p style="margin-top:25px;">
                    مع أطيب التحيات،<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">
                    <p style="margin:4px 0;">تم إرسال هذا البريد من <strong>{app_name}</strong></p>
                    <p style="margin:4px 0;">© {company_name} جميع الحقوق محفوظة</p>
                    </div>

                    </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Projektmeddelelse</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Hej,</p>

                    <p>
                    Gode nyheder! Et nyt projekt er blevet oprettet for dig af <strong>{company_name}</strong>.
                    </p>

                    <h3 style="color:#1f4fff;">Projektdetaljer</h3>

                    <p><strong>Navn:</strong> {name}</p>
                    <p><strong>Budget:</strong> {budget}</p>
                    <p><strong>Startdato:</strong> {start_date}</p>
                    <p><strong>Slutdato:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;">Se projekt</a>
                    </div>

                    <p>Med venlig hilsen,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    Denne e-mail blev sendt fra <strong>{app_name}</strong><br>
                    © {company_name}
                    </div>

                    </div>
                    </div>',


                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Projektbenachrichtigung</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Hallo,</p>

                    <p>
                    Gute Nachrichten! Ein neues Projekt wurde von <strong>{company_name}</strong> für Sie erstellt.
                    </p>

                    <p><strong>Name:</strong> {name}</p>
                    <p><strong>Budget:</strong> {budget}</p>
                    <p><strong>Startdatum:</strong> {start_date}</p>
                    <p><strong>Enddatum:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">Projekt ansehen</a>
                    </div>

                    <p>Mit freundlichen Grüßen,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    Diese E-Mail wurde von <strong>{app_name}</strong> gesendet
                    </div>

                    </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">
                    
                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">Project Notification</p>
                    </div>
                    
                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>Hello,</p>

                    <p>
                    Great news! A new project has been created for you by <strong>{company_name}</strong>.
                    You can review the details below.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">

                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">Project Details</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>Name</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{name}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Budget</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Start Date</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>End Date</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>

                    </div>

                    <p>
                    You can log in to the application to check updates, track progress, and manage project activities.
                    </p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    View Project
                    </a>
                    </div>

                    <p>If you need any assistance, feel free to contact us anytime.</p>

                    <p style="margin-top:25px;">
                    Best Regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">

                    <p style="margin:4px 0;">This email was sent from <strong>{app_name}</strong></p>
                    <p style="margin:4px 0;">© {company_name} | All Rights Reserved</p>

                    </div>

                    </div>
                    </div>',
                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Notificación de Proyecto</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Hola,</p>

                    <p>
                    ¡Buenas noticias! Un nuevo proyecto ha sido creado para usted por <strong>{company_name}</strong>.
                    </p>

                    <p><strong>Nombre:</strong> {name}</p>
                    <p><strong>Presupuesto:</strong> {budget}</p>
                    <p><strong>Fecha de inicio:</strong> {start_date}</p>
                    <p><strong>Fecha de finalización:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">Ver Proyecto</a>
                    </div>

                    <p>Saludos cordiales,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    Este correo fue enviado desde <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">Notification de projet</p>
                    </div>

                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>Bonjour,</p>

                    <p>
                    Bonne nouvelle ! Un nouveau projet a été créé pour vous par <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">

                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">Détails du projet</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>Nom</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{name}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Budget</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Date de début</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Date de fin</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>
                    </div>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    Voir le projet
                    </a>
                    </div>

                    <p>Cordialement,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">
                    <p>Ce mail a été envoyé depuis <strong>{app_name}</strong></p>
                    <p>© {company_name} Tous droits réservés</p>
                    </div>

                    </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Notifica Progetto</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Ciao,</p>

                    <p>Un nuovo progetto è stato creato per te da <strong>{company_name}</strong>.</p>

                    <p><strong>Nome:</strong> {name}</p>
                    <p><strong>Budget:</strong> {budget}</p>
                    <p><strong>Data di inizio:</strong> {start_date}</p>
                    <p><strong>Data di fine:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">Visualizza progetto</a>
                    </div>

                    <p>Cordiali saluti,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    Questa email è stata inviata da <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>プロジェクト通知</p>
                    </div>

                    <div style="padding:35px;">

                    <p>こんにちは、</p>

                    <p><strong>{company_name}</strong> により新しいプロジェクトが作成されました。</p>

                    <p><strong>名前:</strong> {name}</p>
                    <p><strong>予算:</strong> {budget}</p>
                    <p><strong>開始日:</strong> {start_date}</p>
                    <p><strong>終了日:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">プロジェクトを見る</a>
                    </div>

                    <p><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    このメールは <strong>{app_name}</strong> から送信されました
                    </div>

                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Projectmelding</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Hallo,</p>

                    <p>Er is een nieuw project aangemaakt door <strong>{company_name}</strong>.</p>

                    <p><strong>Naam:</strong> {name}</p>
                    <p><strong>Budget:</strong> {budget}</p>
                    <p><strong>Startdatum:</strong> {start_date}</p>
                    <p><strong>Einddatum:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">Project bekijken</a>
                    </div>

                    <p>Met vriendelijke groet,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    Deze e-mail is verzonden via <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">
                    
                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">Powiadomienie o projekcie</p>
                    </div>
                    
                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>Witaj <strong>{name}</strong>,</p>

                    <p>
                    Dobra wiadomość! Nowy projekt został utworzony dla Ciebie przez <strong>{company_name}</strong>.
                    Możesz sprawdzić szczegóły poniżej.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">
                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">Szczegóły projektu</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>Budżet</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Data rozpoczęcia</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Data zakończenia</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>
                    </div>

                    <p>Możesz zalogować się do aplikacji, aby śledzić postęp projektu.</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    Zobacz projekt
                    </a>
                    </div>

                    <p>Z poważaniem,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">
                    <p>Ta wiadomość została wysłana z <strong>{app_name}</strong></p>
                    <p>© {company_name} Wszelkie prawa zastrzeżone</p>
                    </div>

                    </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">
                    
                    <div style="background:#1f4fff;padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Notificação de Projeto</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Olá <strong>{name}</strong>,</p>

                    <p>
                    Boas notícias! Um novo projeto foi criado para você por <strong>{company_name}</strong>.
                    </p>

                    <p><strong>Orçamento:</strong> {budget}</p>
                    <p><strong>Data de início:</strong> {start_date}</p>
                    <p><strong>Data de término:</strong> {end_date}</p>

                    <div style="text-align:center;margin:30px;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;">Ver projeto</a>
                    </div>

                    <p>Atenciosamente,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:15px;">
                    Este e-mail foi enviado de <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">
                    
                    <div style="background:#1f4fff;padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Notificação de Projeto</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Olá <strong>{name}</strong>,</p>

                    <p>
                    Boas notícias! Um novo projeto foi criado para você por <strong>{company_name}</strong>.
                    </p>

                    <p><strong>Orçamento:</strong> {budget}</p>
                    <p><strong>Data de início:</strong> {start_date}</p>
                    <p><strong>Data de término:</strong> {end_date}</p>

                    <div style="text-align:center;margin:30px;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;">Ver projeto</a>
                    </div>

                    <p>Atenciosamente,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:15px;">
                    Este e-mail foi enviado de <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">
                    
                    <div style="background:#1f4fff;padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>Уведомление о проекте</p>
                    </div>

                    <div style="padding:35px;">

                    <p>Здравствуйте <strong>{name}</strong>,</p>

                    <p>
                    Хорошие новости! Новый проект был создан для вас компанией <strong>{company_name}</strong>.
                    </p>

                    <p><strong>Бюджет:</strong> {budget}</p>
                    <p><strong>Дата начала:</strong> {start_date}</p>
                    <p><strong>Дата окончания:</strong> {end_date}</p>

                    <div style="text-align:center;margin:30px;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;">Просмотреть проект</a>
                    </div>

                    <p>С уважением,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:15px;">
                    Это письмо было отправлено из <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',

                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;" dir="rtl">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e4e7f2;">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2>{app_name}</h2>
                    <p>התראת פרויקט</p>
                    </div>

                    <div style="padding:35px;">

                    <p>שלום,</p>

                    <p>פרויקט חדש נוצר עבורך על ידי <strong>{company_name}</strong>.</p>

                    <p><strong>שם:</strong> {name}</p>
                    <p><strong>תקציב:</strong> {budget}</p>
                    <p><strong>תאריך התחלה:</strong> {start_date}</p>
                    <p><strong>תאריך סיום:</strong> {end_date}</p>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;">צפה בפרויקט</a>
                    </div>

                    <p>בברכה,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="text-align:center;font-size:13px;color:#777;padding:18px;">
                    האימייל נשלח מ <strong>{app_name}</strong>
                    </div>

                    </div>
                    </div>',

                   'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">Proje Bildirimi</p>
                    </div>

                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>Merhaba,</p>

                    <p>
                    Harika haber! <strong>{company_name}</strong> tarafından sizin için yeni bir proje oluşturuldu.
                    Detayları aşağıda inceleyebilirsiniz.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">

                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">Proje Detayları</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>İsim</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{name}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Bütçe</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Başlangıç Tarihi</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>Bitiş Tarihi</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>
                    </div>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    Projeyi Görüntüle
                    </a>
                    </div>

                    <p>Herhangi bir yardıma ihtiyacınız olursa bizimle iletişime geçebilirsiniz.</p>

                    <p style="margin-top:25px;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">

                    <p style="margin:4px 0;">Bu e-posta <strong>{app_name}</strong> üzerinden gönderildi</p>
                    <p style="margin:4px 0;">© {company_name} | Tüm hakları saklıdır</p>

                    </div>

                    </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#1f4fff,#4f73ff);padding:28px;text-align:center;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;letter-spacing:0.5px;">{app_name}</h2>
                    <p style="margin:8px 0 0 0;font-size:14px;opacity:0.9;">项目通知</p>
                    </div>

                    <div style="padding:35px;color:#333;font-size:15px;line-height:1.7;">

                    <p>您好，</p>

                    <p>
                    好消息！<strong>{company_name}</strong> 已为您创建了一个新的项目。
                    您可以在下面查看详细信息。
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e5e9ff;border-radius:10px;padding:20px;margin:25px 0;">

                    <h3 style="margin-top:0;margin-bottom:15px;color:#1f4fff;font-size:18px;">项目详情</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:10px 0;color:#666;"><strong>名称</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{name}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>预算</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{budget}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>开始日期</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{start_date}</td>
                    </tr>

                    <tr style="border-top:1px dashed #e2e5f3;">
                    <td style="padding:10px 0;color:#666;"><strong>结束日期</strong></td>
                    <td style="padding:10px 0;text-align:right;color:#111;">{end_date}</td>
                    </tr>

                    </table>
                    </div>

                    <div style="text-align:center;margin:35px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                    查看项目
                    </a>
                    </div>

                    <p>如果您需要任何帮助，请随时联系我们。</p>

                    <p style="margin-top:25px;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f3f5fb;padding:18px;text-align:center;font-size:13px;color:#777;border-top:1px solid #e4e7f2;">

                    <p style="margin:4px 0;">此邮件由 <strong>{app_name}</strong> 发送</p>
                    <p style="margin:4px 0;">© {company_name} | 版权所有</p>

                    </div>

                    </div>
                    </div>',
                ],
            ],

            'Project Task' => [
                'subject' => 'Project Task Created',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Name": "name",
                    "Milestone Name": "milestone_name",
                    "Title": "title",
                    "Duration": "duration"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">تحديث مهمة جديدة</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    مرحبًا،
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    تم تعيين مهمة جديدة كجزء من مرحلة المشروع الخاصة بك. فيما يلي تفاصيل المهمة التي شاركتها <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    تفاصيل المهمة
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">اسم المشروع</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">المرحلة</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">عنوان المهمة</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">المدة المقدرة</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    يمكنك مراجعة هذه المهمة وتتبع تقدمها في أي وقت من خلال لوحة التحكم الخاصة بك.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    فتح لوحة التحكم
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    إذا كانت لديك أي أسئلة بخصوص هذه المهمة، فلا تتردد في التواصل معنا.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    مع أطيب التحيات,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • مدعوم بواسطة {app_name}
                    </div>

                    </div>
                    </div>',
                                        'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Ny opgaveopdatering</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">
                    <p style="font-size:15px;color:#333;margin:0 0 18px;">Hej,</p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    En ny opgave er blevet tildelt som en del af din projektmilepæl. Nedenfor er opgavedetaljerne delt af <strong>{company_name}</strong>.
                    </p>

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Opgavedetaljer
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Projektnavn</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Milepæl</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Opgavetitel</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Estimeret varighed</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>
                    </table>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Du kan gennemgå denne opgave og følge dens fremskridt fra dit dashboard når som helst.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Åbn dashboard
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Hvis du har spørgsmål til denne opgave, er du velkommen til at kontakte os.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Drevet af {app_name}
                    </div>

                    </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Neue Aufgabenaktualisierung</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Eine neue Aufgabe wurde als Teil Ihres Projektmeilensteins zugewiesen. Nachfolgend finden Sie die Aufgabendetails, die von <strong>{company_name}</strong> geteilt wurden.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Aufgabendetails
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Projektname</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Meilenstein</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Aufgabentitel</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Geschätzte Dauer</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Sie können diese Aufgabe jederzeit überprüfen und ihren Fortschritt über Ihr Dashboard verfolgen.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Dashboard öffnen
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Wenn Sie Fragen zu dieser Aufgabe haben, können Sie sich jederzeit an uns wenden.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'en' =>'<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">New Task Update</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hello,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    A new task has been assigned as part of your project milestone. Below are the task details shared by <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Task Details
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Project Name</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Milestone</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Task Title</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Estimated Duration</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    You can review this task and track its progress anytime from your dashboard.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Open Dashboard
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    If you have any questions regarding this task, feel free to reach out to us.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Best regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nueva actualización de tarea</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hola,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Se ha asignado una nueva tarea como parte del hito de su proyecto. A continuación se muestran los detalles de la tarea compartidos por <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Detalles de la tarea
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nombre del proyecto</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Hito</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Título de la tarea</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Duración estimada</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Puede revisar esta tarea y seguir su progreso en cualquier momento desde su panel de control.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir panel
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Si tiene alguna pregunta sobre esta tarea, no dude en ponerse en contacto con nosotros.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saludos cordiales,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Desarrollado por {app_name}
                    </div>

                    </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nouvelle mise à jour de tâche</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Bonjour,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Une nouvelle tâche a été attribuée dans le cadre de l\étape de votre projet. Vous trouverez ci-dessous les détails de la tâche partagés par <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Détails de la tâche
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nom du projet</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Étape</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Titre de la tâche</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Durée estimée</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Vous pouvez consulter cette tâche et suivre sa progression à tout moment depuis votre tableau de bord.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ouvrir le tableau de bord
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Si vous avez des questions concernant cette tâche, n\hésitez pas à nous contacter.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Propulsé par {app_name}
                    </div>

                    </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Aggiornamento Nuova Attività</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Ciao,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Una nuova attività è stata assegnata come parte della milestone del tuo progetto. Di seguito trovi i dettagli dell\'attività condivisi da <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Dettagli Attività
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nome Progetto</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Milestone</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Titolo Attività</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Durata Stimata</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Puoi rivedere questa attività e monitorarne i progressi in qualsiasi momento dalla tua dashboard.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Apri Dashboard
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se hai domande riguardo a questa attività, non esitare a contattarci.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">新しいタスクの更新</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    こんにちは、
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    プロジェクトのマイルストーンの一部として、新しいタスクが割り当てられました。以下は <strong>{company_name}</strong> によって共有されたタスクの詳細です。
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    タスクの詳細
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">プロジェクト名</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">マイルストーン</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">タスクタイトル</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">推定期間</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    このタスクはダッシュボードからいつでも確認し、進捗を追跡することができます。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    ダッシュボードを開く
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    このタスクに関してご質問がある場合は、お気軽にお問い合わせください。
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nieuwe taakupdate</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Er is een nieuwe taak toegewezen als onderdeel van uw projectmijlpaal. Hieronder vindt u de taakdetails die zijn gedeeld door <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Taakdetails
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Projectnaam</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Mijlpaal</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Taaktitel</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Geschatte duur</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    U kunt deze taak bekijken en de voortgang op elk moment volgen via uw dashboard.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Dashboard openen
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Als u vragen heeft over deze taak, neem dan gerust contact met ons op.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nowa aktualizacja zadania</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Witaj,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Nowe zadanie zostało przypisane jako część kamienia milowego Twojego projektu. Poniżej znajdują się szczegóły zadania udostępnione przez <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Szczegóły zadania
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nazwa projektu</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Kamień milowy</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Tytuł zadania</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Szacowany czas trwania</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Możesz w każdej chwili przejrzeć to zadanie i śledzić jego postęp w swoim panelu.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Otwórz panel
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Jeśli masz pytania dotyczące tego zadania, skontaktuj się z nami.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nova atualização de tarefa</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Uma nova tarefa foi atribuída como parte do marco do seu projeto. Abaixo estão os detalhes da tarefa compartilhados por <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Detalhes da tarefa
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nome do projeto</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Marco</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Título da tarefa</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Duração estimada</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Você pode revisar esta tarefa e acompanhar seu progresso a qualquer momento através do seu painel.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir painel
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se você tiver alguma dúvida sobre esta tarefa, sinta-se à vontade para entrar em contato conosco.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Nova atualização de tarefa</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Uma nova tarefa foi atribuída como parte do marco do seu projeto. Abaixo estão os detalhes da tarefa compartilhados por <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Detalhes da tarefa
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Nome do projeto</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Marco</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Título da tarefa</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Duração estimada</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Você pode revisar esta tarefa e acompanhar seu progresso a qualquer momento através do seu painel.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir painel
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se você tiver alguma dúvida sobre esta tarefa, sinta-se à vontade para entrar em contato conosco.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Новое обновление задачи</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Здравствуйте,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Новая задача была назначена в рамках этапа вашего проекта. Ниже приведены детали задачи, предоставленные <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Детали задачи
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Название проекта</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Этап</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Название задачи</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Оценочная продолжительность</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Вы можете просмотреть эту задачу и отслеживать её прогресс в любое время через вашу панель управления.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Открыть панель управления
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Если у вас есть вопросы по этой задаче, пожалуйста, свяжитесь с нами.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">עדכון משימה חדשה</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    שלום,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    משימה חדשה הוקצתה כחלק מאבן הדרך של הפרויקט שלך. להלן פרטי המשימה ששותפו על ידי <strong>{company_name}</strong>.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    פרטי המשימה
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">שם הפרויקט</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">אבן דרך</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">כותרת המשימה</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">משך זמן משוער</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    ניתן לעיין במשימה זו ולעקוב אחר ההתקדמות שלה בכל עת מלוח הבקרה שלך.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    פתח לוח בקרה
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    אם יש לך שאלות לגבי משימה זו, אל תהסס לפנות אלינו.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Yeni Görev Güncellemesi</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Merhaba,
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Proje kilometre taşınızın bir parçası olarak yeni bir görev atanmıştır. Aşağıda <strong>{company_name}</strong> tarafından paylaşılan görev detaylarını bulabilirsiniz.
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    Görev Detayları
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">Proje Adı</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Kilometre Taşı</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Görev Başlığı</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Tahmini Süre</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Bu görevi inceleyebilir ve ilerlemesini istediğiniz zaman kontrol panelinizden takip edebilirsiniz.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Kontrol Panelini Aç
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Bu görevle ilgili herhangi bir sorunuz varsa bizimle iletişime geçmekten çekinmeyin.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#2f6fed;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">新的任务更新</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    您好，
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    作为您项目里程碑的一部分，已分配一个新的任务。以下是由 <strong>{company_name}</strong> 分享的任务详情。
                    </p>

                    <div style="background:#f7f9ff;border:1px solid #e6ebff;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#2f6fed;font-size:18px;">
                    任务详情
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">
                    <tr>
                    <td style="padding:8px 0;width:40%;font-weight:600;">项目名称</td>
                    <td style="padding:8px 0;">{name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">里程碑</td>
                    <td style="padding:8px 0;">{milestone_name}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">任务标题</td>
                    <td style="padding:8px 0;">{title}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">预计时长</td>
                    <td style="padding:8px 0;">{duration}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    您可以随时从您的仪表板查看此任务并跟踪其进度。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#2f6fed;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    打开仪表板
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    如果您对该任务有任何疑问，请随时与我们联系。
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f7f8fc;text-align:center;padding:18px;font-size:12px;color:#888;">
                    © {company_name} • Powered by {app_name}
                    </div>

                    </div>
                    </div>',
                ],
            ],

            'Project Assign to Client' => [
                'subject' => 'Project Assignment Confirmation',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Name": "name",
                    "Budget": "budget",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 تم تعيين المشروع بنجاح</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">رحلة مشروعك معنا تبدأ الآن!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">مرحبًا <strong>{name}</strong>,</p>

                                <p>
                                    أخبار رائعة! 🎉 تم تعيين مشروعك بنجاح بواسطة <strong>{company_name}</strong>.
                                    فريقنا متحمس لبدء العمل معك وتقديم أفضل النتائج.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 الميزانية:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 تاريخ البدء:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 تاريخ الانتهاء المتوقع:</strong> {end_date}</p>
                                </div>

                                <p>
                                    سيبدأ فريقنا العمل على المشروع وفقًا للجدول الزمني المحدد.
                                    يمكنك مراجعة تفاصيل المشروع أو متابعة التقدم في أي وقت.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        عرض المشروع
                                    </a>
                                </div>

                                <p>
                                    إذا كان لديك أي أسئلة أو متطلبات إضافية، فلا تتردد في التواصل معنا في أي وقت.
                                    نتطلع إلى تعاون ناجح معك! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    مع أطيب التحيات,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projektet er blevet tildelt</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Din projektrejse med os starter nu!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Hej <strong>{name}</strong>,</p>

                                <p>
                                    Gode nyheder! 🎉 Dit projekt er blevet tildelt af <strong>{company_name}</strong>.
                                    Vores team glæder sig til at arbejde sammen med dig og levere gode resultater.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Startdato:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Forventet slutdato:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Vores team vil begynde arbejdet på projektet i henhold til den planlagte tidslinje.
                                    Du kan til enhver tid få adgang til dit arbejdsområde for at følge projektets fremskridt.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Se projekt
                                    </a>
                                </div>

                                <p>
                                    Hvis du har spørgsmål eller yderligere krav, er du velkommen til at kontakte os når som helst.
                                    Vi ser frem til et godt samarbejde! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Med venlig hilsen,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projekt erfolgreich zugewiesen</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Ihre Projektreise mit uns beginnt jetzt!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Hallo <strong>{name}</strong>,</p>

                                <p>
                                    Gute Nachrichten! 🎉 Ihr Projekt wurde erfolgreich von <strong>{company_name}</strong> zugewiesen.
                                    Unser Team freut sich darauf, mit Ihnen zu arbeiten und hervorragende Ergebnisse zu liefern.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Startdatum:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Voraussichtliches Enddatum:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Unser Team wird gemäß dem geplanten Zeitplan mit der Arbeit am Projekt beginnen.
                                    Sie können jederzeit auf Ihren Arbeitsbereich zugreifen, um Details einzusehen.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Projekt ansehen
                                    </a>
                                </div>

                                <p>
                                    Wenn Sie Fragen oder zusätzliche Anforderungen haben, können Sie sich jederzeit an uns wenden.
                                    Wir freuen uns auf eine erfolgreiche Zusammenarbeit! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Mit freundlichen Grüßen,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Project Successfully Assigned</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Your project journey with us begins now!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Hello <strong>{name}</strong>,</p>

                                <p>
                                    Great news! 🎉 Your project has been successfully assigned by <strong>{company_name}</strong>.  
                                    Our team is excited to start working with you and deliver excellent results.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Start Date:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Expected End Date:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Our team will begin working on the project according to the scheduled timeline.  
                                    If you would like to review project details or stay updated on progress, you can access your workspace anytime.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        View Project
                                    </a>
                                </div>

                                <p>
                                    If you have any questions or additional requirements, feel free to reach out to us anytime.  
                                    We’re looking forward to a successful collaboration! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Best Regards,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Proyecto asignado con éxito</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">¡Tu proyecto con nosotros comienza ahora!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Hola <strong>{name}</strong>,</p>

                                <p>
                                    ¡Grandes noticias! 🎉 Tu proyecto ha sido asignado con éxito por <strong>{company_name}</strong>.
                                    Nuestro equipo está entusiasmado por comenzar a trabajar contigo y ofrecer excelentes resultados.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Presupuesto:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Fecha de inicio:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Fecha estimada de finalización:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Nuestro equipo comenzará a trabajar en el proyecto según el cronograma planificado.
                                    Puedes revisar los detalles del proyecto o seguir el progreso en cualquier momento.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Ver proyecto
                                    </a>
                                </div>

                                <p>
                                    Si tienes alguna pregunta o requisito adicional, no dudes en contactarnos en cualquier momento.
                                    ¡Esperamos una colaboración exitosa! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Saludos cordiales,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projet attribué avec succès</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Votre projet avec nous commence maintenant !</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Bonjour <strong>{name}</strong>,</p>

                                <p>
                                    Excellente nouvelle ! 🎉 Votre projet a été attribué avec succès par <strong>{company_name}</strong>.
                                    Notre équipe est ravie de commencer à travailler avec vous et de fournir d\excellents résultats.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget :</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Date de début :</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Date de fin prévue :</strong> {end_date}</p>
                                </div>

                                <p>
                                    Notre équipe commencera à travailler sur le projet selon le calendrier prévu.
                                    Vous pouvez consulter les détails du projet ou suivre l\avancement à tout moment.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Voir le projet
                                    </a>
                                </div>

                                <p>
                                    Si vous avez des questions ou des exigences supplémentaires, n\hésitez pas à nous contacter à tout moment.
                                    Nous sommes impatients de collaborer avec vous ! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Cordialement,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Progetto assegnato con successo</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Il tuo progetto con noi inizia ora!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Ciao <strong>{name}</strong>,</p>

                                <p>
                                    Ottime notizie! 🎉 Il tuo progetto è stato assegnato con successo da <strong>{company_name}</strong>.
                                    Il nostro team è entusiasta di iniziare a lavorare con te e di offrire risultati eccellenti.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Data di inizio:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Data di fine prevista:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Il nostro team inizierà a lavorare al progetto secondo il programma stabilito.
                                    Puoi accedere alla tua area di lavoro in qualsiasi momento per vedere i dettagli del progetto.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Visualizza progetto
                                    </a>
                                </div>

                                <p>
                                    Se hai domande o ulteriori richieste, non esitare a contattarci in qualsiasi momento.
                                    Non vediamo l\ora di collaborare con te! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Cordiali saluti,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 プロジェクトが正常に割り当てられました</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">あなたのプロジェクトが今、私たちと共に始まります！</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">こんにちは <strong>{name}</strong> 様,</p>

                                <p>
                                    素晴らしいお知らせです！ 🎉 あなたのプロジェクトは <strong>{company_name}</strong> により正常に割り当てられました。
                                    私たちのチームはあなたと協力して素晴らしい成果を提供できることを楽しみにしています。
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 予算:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 開始日:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 予定終了日:</strong> {end_date}</p>
                                </div>

                                <p>
                                    私たちのチームは、予定されたスケジュールに従ってプロジェクト作業を開始します。
                                    いつでもワークスペースにアクセスしてプロジェクトの進捗を確認できます。
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        プロジェクトを見る
                                    </a>
                                </div>

                                <p>
                                    ご質問や追加のご要望がございましたら、いつでもお気軽にご連絡ください。
                                    今後の協力を楽しみにしております！ 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    敬具,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Project succesvol toegewezen</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Uw projectreis met ons begint nu!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Hallo <strong>{name}</strong>,</p>

                                <p>
                                    Goed nieuws! 🎉 Uw project is succesvol toegewezen door <strong>{company_name}</strong>.
                                    Ons team kijkt ernaar uit om met u samen te werken en uitstekende resultaten te leveren.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budget:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Startdatum:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Verwachte einddatum:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Ons team zal beginnen met werken aan het project volgens de geplande tijdlijn.
                                    U kunt op elk moment uw werkruimte openen om de projectdetails te bekijken.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Bekijk project
                                    </a>
                                </div>

                                <p>
                                    Als u vragen of aanvullende wensen heeft, neem gerust op elk moment contact met ons op.
                                    We kijken uit naar een succesvolle samenwerking! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Met vriendelijke groet,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projekt został pomyślnie przypisany</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Twoja współpraca z nami właśnie się rozpoczyna!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Witaj <strong>{name}</strong>,</p>

                                <p>
                                    Świetna wiadomość! 🎉 Twój projekt został pomyślnie przypisany przez <strong>{company_name}</strong>.
                                    Nasz zespół z radością rozpocznie pracę i dostarczy doskonałe rezultaty.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Budżet:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Data rozpoczęcia:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Przewidywana data zakończenia:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Nasz zespół rozpocznie pracę nad projektem zgodnie z zaplanowanym harmonogramem.
                                    W każdej chwili możesz sprawdzić szczegóły projektu lub jego postęp.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Zobacz projekt
                                    </a>
                                </div>

                                <p>
                                    Jeśli masz pytania lub dodatkowe wymagania, skontaktuj się z nami w dowolnym momencie.
                                    Cieszymy się na owocną współpracę! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Z poważaniem,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projeto atribuído com sucesso</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">A sua jornada de projeto conosco começa agora!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Olá <strong>{name}</strong>,</p>

                                <p>
                                    Boas notícias! 🎉 Seu projeto foi atribuído com sucesso por <strong>{company_name}</strong>.
                                    Nossa equipe está animada para começar a trabalhar com você e entregar excelentes resultados.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Orçamento:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Data de início:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Data prevista de conclusão:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Nossa equipe começará a trabalhar no projeto conforme o cronograma planejado.
                                    Você pode acessar seu espaço de trabalho a qualquer momento para acompanhar o progresso.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Ver projeto
                                    </a>
                                </div>

                                <p>
                                    Se tiver alguma dúvida ou requisito adicional, sinta-se à vontade para entrar em contato conosco a qualquer momento.
                                    Estamos ansiosos por uma colaboração de sucesso! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Atenciosamente,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Projeto atribuído com sucesso</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">A sua jornada de projeto conosco começa agora!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Olá <strong>{name}</strong>,</p>

                                <p>
                                    Boas notícias! 🎉 Seu projeto foi atribuído com sucesso por <strong>{company_name}</strong>.
                                    Nossa equipe está animada para começar a trabalhar com você e entregar excelentes resultados.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Orçamento:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Data de início:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Data prevista de conclusão:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Nossa equipe começará a trabalhar no projeto conforme o cronograma planejado.
                                    Você pode acessar seu espaço de trabalho a qualquer momento para acompanhar o progresso.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Ver projeto
                                    </a>
                                </div>

                                <p>
                                    Se tiver alguma dúvida ou requisito adicional, sinta-se à vontade para entrar em contato conosco a qualquer momento.
                                    Estamos ansiosos por uma colaboração de sucesso! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Atenciosamente,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Проект успешно назначен</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Ваше сотрудничество с нами начинается прямо сейчас!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Здравствуйте, <strong>{name}</strong>,</p>

                                <p>
                                    Отличные новости! 🎉 Ваш проект был успешно назначен компанией <strong>{company_name}</strong>.
                                    Наша команда рада начать работу с вами и предоставить отличные результаты.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Бюджет:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Дата начала:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Предполагаемая дата завершения:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Наша команда начнет работу над проектом в соответствии с запланированным графиком.
                                    Вы можете в любое время просматривать детали проекта и следить за его прогрессом.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Посмотреть проект
                                    </a>
                                </div>

                                <p>
                                    Если у вас есть вопросы или дополнительные требования, пожалуйста, свяжитесь с нами в любое время.
                                    Мы с нетерпением ждем успешного сотрудничества! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    С уважением,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 הפרויקט הוקצה בהצלחה</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">המסע של הפרויקט שלך איתנו מתחיל עכשיו!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">שלום <strong>{name}</strong>,</p>

                                <p>
                                    חדשות מצוינות! 🎉 הפרויקט שלך הוקצה בהצלחה על ידי <strong>{company_name}</strong>.
                                    הצוות שלנו מתרגש להתחיל לעבוד איתך ולהביא תוצאות מצוינות.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 תקציב:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 תאריך התחלה:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 תאריך סיום צפוי:</strong> {end_date}</p>
                                </div>

                                <p>
                                    הצוות שלנו יתחיל לעבוד על הפרויקט בהתאם ללוח הזמנים שנקבע.
                                    תוכל לגשת לסביבת העבודה שלך בכל עת כדי לעקוב אחר התקדמות הפרויקט.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        צפייה בפרויקט
                                    </a>
                                </div>

                                <p>
                                    אם יש לך שאלות או דרישות נוספות, אל תהסס לפנות אלינו בכל עת.
                                    נשמח לשיתוף פעולה מוצלח! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    בברכה,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 Proje başarıyla atandı</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">Bizimle proje yolculuğunuz şimdi başlıyor!</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">Merhaba <strong>{name}</strong>,</p>

                                <p>
                                    Harika haber! 🎉 Projeniz <strong>{company_name}</strong> tarafından başarıyla atanmıştır.
                                    Ekibimiz sizinle çalışmaya başlamak ve mükemmel sonuçlar sunmak için heyecanlı.
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 Bütçe:</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 Başlangıç Tarihi:</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 Tahmini Bitiş Tarihi:</strong> {end_date}</p>
                                </div>

                                <p>
                                    Ekibimiz planlanan zaman çizelgesine göre proje üzerinde çalışmaya başlayacaktır.
                                    Proje detaylarını incelemek veya ilerlemeyi takip etmek için çalışma alanınıza istediğiniz zaman erişebilirsiniz.
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        Projeyi Görüntüle
                                    </a>
                                </div>

                                <p>
                                    Herhangi bir sorunuz veya ek gereksiniminiz varsa bizimle istediğiniz zaman iletişime geçebilirsiniz.
                                    Başarılı bir iş birliği için sabırsızlanıyoruz! 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    Saygılarımızla,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
                            
                            <div style="background:#4f46e5;padding:28px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;font-weight:600;">🚀 项目已成功分配</h1>
                                <p style="margin:6px 0 0 0;font-size:14px;opacity:0.9;">您与我们的项目合作现在正式开始！</p>
                            </div>

                            <div style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
                                
                                <p style="margin-top:0;">您好 <strong>{name}</strong>,</p>

                                <p>
                                    好消息！🎉 您的项目已由 <strong>{company_name}</strong> 成功分配。
                                    我们的团队非常期待与您合作，并为您提供卓越的成果。
                                </p>

                                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:22px 0;">
                                    <p style="margin:6px 0;"><strong>💰 预算：</strong> {budget}</p>
                                    <p style="margin:6px 0;"><strong>📅 开始日期：</strong> {start_date}</p>
                                    <p style="margin:6px 0;"><strong>🏁 预计结束日期：</strong> {end_date}</p>
                                </div>

                                <p>
                                    我们的团队将按照计划时间表开始项目工作。
                                    您可以随时访问您的工作空间查看项目详情或跟踪进度。
                                </p>

                                <div style="text-align:center;margin:30px 0;">
                                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                                        查看项目
                                    </a>
                                </div>

                                <p>
                                    如果您有任何问题或额外需求，请随时与我们联系。
                                    期待与您开展成功的合作！ 🤝
                                </p>

                                <p style="margin-bottom:0;">
                                    此致敬礼,<br>
                                    <strong>{company_name}</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                ],
            ],

        ];
        foreach($emailTemplate as $eTemp)
        {
            
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','Taskly')->exists();
            if(!$table)
            {
                $emailtemplate=  EmailTemplate::create(
                    [
                    'name' => $eTemp,
                    'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas ERP',
                    'module_name' => 'Taskly',
                    'created_by' => $admin->id,
                    'creator_id' => $admin->id,
                    ]
                );
                foreach($defaultTemplate[$eTemp]['lang'] as $lang => $content)
                {
                    EmailTemplateLang::create(
                        [
                            'parent_id' => $emailtemplate->id,
                            'lang' => $lang,
                            'subject' => $defaultTemplate[$eTemp]['subject'],
                            'variables' => $defaultTemplate[$eTemp]['variables'],
                            'content' => $content,
                        ]
                    );
                }
            }
        }
    }
}