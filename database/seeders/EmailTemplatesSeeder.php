<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\User;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('type','superadmin')->first();

        $emailTemplate = [
            'New User',
            'Sales Invoice',
            'Sales Invoice Return',
            'Purchase Invoice',
            'Purchase Invoice Return',
            'Helpdesk Ticket',
            'Helpdesk Ticket Reply', 
            'Proposal Sent', 
            'Proposal Approval', 
            'Plan Purchase', 
        ];

        $defaultTemplate = [
            'New User' => [
                'subject' => 'Login Detail',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Name": "name",
                    "Email": "email",
                    "Password": "password"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">مرحبًا بك في {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    تمت إضافتك إلى {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;direction:rtl;text-align:right;">
                                <p style="margin:0 0 15px;font-size:15px;">مرحبًا <strong>{name}</strong>،</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    تمت إضافتك كمستخدم في <strong>{company_name}</strong>. فيما يلي تفاصيل تسجيل الدخول الخاصة بك:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 رابط التطبيق:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 البريد الإلكتروني:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 كلمة المرور:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    يرجى تسجيل الدخول وتغيير كلمة المرور بعد أول تسجيل دخول.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    تسجيل الدخول إلى النظام
                                </a>
                            </div>
                        </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Velkommen til {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Du er blevet tilføjet til {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Hej <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Du er blevet tilføjet som bruger i <strong>{company_name}</strong>. Her er dine loginoplysninger:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 App URL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Adgangskode:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Log venligst ind og ændr din adgangskode efter første login.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Log ind på systemet
                                </a>
                            </div>
                        </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Willkommen bei {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Sie wurden zu {company_name} hinzugefügt
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Hallo <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Sie wurden als Benutzer zu <strong>{company_name}</strong> hinzugefügt. Hier sind Ihre Zugangsdaten:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 App URL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 E-Mail:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Passwort:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Bitte melden Sie sich an und ändern Sie Ihr Passwort nach der ersten Anmeldung.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Zum System anmelden
                                </a>
                            </div>
                        </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Welcome to {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    You have been added to {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Hello <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    You have been added as a user in <strong>{company_name}</strong>. 
                                    Below are your login details to access the system:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 App URL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Password:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Please login using the above credentials and update your password after first login.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Login to System
                                </a>
                            </div>
                        </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Bienvenido a {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Has sido agregado a {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Hola <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Has sido agregado como usuario en <strong>{company_name}</strong>. A continuación, tus datos de acceso:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL de la aplicación:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Correo electrónico:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Contraseña:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Por favor, inicia sesión y cambia tu contraseña después del primer acceso.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Iniciar sesión
                                </a>
                            </div>
                        </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Bienvenue sur {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Vous avez été ajouté à {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Bonjour <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Vous avez été ajouté en tant qu\'utilisateur dans <strong>{company_name}</strong>. Voici vos informations de connexion :
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL de l\'application :</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email :</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Mot de passe :</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Veuillez vous connecter et modifier votre mot de passe après la première connexion.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Se connecter au système
                                </a>
                            </div>
                        </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Benvenuto su {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Sei stato aggiunto a {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Ciao <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Sei stato aggiunto come utente in <strong>{company_name}</strong>. Di seguito i tuoi dati di accesso:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL dell\'app:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Password:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Effettua l\'accesso e modifica la password dopo il primo accesso.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Accedi al sistema
                                </a>
                            </div>
                        </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">{app_name}へようこそ 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    {company_name} に追加されました
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">こんにちは <strong>{name}</strong>様、</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    あなたは <strong>{company_name}</strong> のユーザーとして追加されました。以下がログイン情報です：
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 アプリURL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 メールアドレス:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 パスワード:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    初回ログイン後にパスワードを変更してください。
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    システムにログイン
                                </a>
                            </div>
                        </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Welkom bij {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Je bent toegevoegd aan {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Hallo <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Je bent toegevoegd als gebruiker in <strong>{company_name}</strong>. Hieronder staan je inloggegevens:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 App URL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 E-mail:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Wachtwoord:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Log in en wijzig je wachtwoord na de eerste keer inloggen.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Inloggen op het systeem
                                </a>
                            </div>
                        </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Witamy w {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Zostałeś dodany do {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Cześć <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Zostałeś dodany jako użytkownik w <strong>{company_name}</strong>. Poniżej znajdują się Twoje dane logowania:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL aplikacji:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Hasło:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Zaloguj się i zmień hasło po pierwszym logowaniu.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Zaloguj się do systemu
                                </a>
                            </div>
                        </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Bem-vindo ao {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Você foi adicionado à {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Olá <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Você foi adicionado como usuário em <strong>{company_name}</strong>. Abaixo estão seus dados de acesso:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL do aplicativo:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Senha:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Faça login e altere sua senha após o primeiro acesso.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Acessar o sistema
                                </a>
                            </div>
                        </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Bem-vindo ao {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Você foi adicionado à {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Olá <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Você foi adicionado como usuário em <strong>{company_name}</strong>. Veja abaixo seus dados de acesso:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL do aplicativo:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Email:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Senha:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Faça login e altere sua senha após o primeiro acesso.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Acessar o sistema
                                </a>
                            </div>
                        </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">Добро пожаловать в {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    Вы были добавлены в {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Здравствуйте, <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    Вы были добавлены как пользователь в <strong>{company_name}</strong>. Ниже приведены ваши данные для входа:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 URL приложения:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 Электронная почта:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Пароль:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Пожалуйста, войдите в систему и измените пароль после первого входа.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Войти в систему
                                </a>
                            </div>
                        </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">ברוך הבא ל-{app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    נוספת ל-{company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;direction:rtl;text-align:right;">
                                <p style="margin:0 0 15px;font-size:15px;">שלום <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    נוספת כמשתמש ב-<strong>{company_name}</strong>. להלן פרטי ההתחברות שלך:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 קישור לאפליקציה:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 אימייל:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 סיסמה:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    אנא התחבר ושנה את הסיסמה לאחר הכניסה הראשונה.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    כניסה למערכת
                                </a>
                            </div>
                        </div>
                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">{app_name}\'e Hoş Geldiniz 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    {company_name}\'e eklendiniz
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">Merhaba <strong>{name}</strong>,</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    <strong>{company_name}</strong> sistemine kullanıcı olarak eklendiniz. Aşağıda giriş bilgileriniz bulunmaktadır:
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 Uygulama URL:</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 E-posta:</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 Şifre:</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    Lütfen giriş yaptıktan sonra şifrenizi değiştirin.
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    Sisteme Giriş Yap
                                </a>
                            </div>
                        </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
                            
                            <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:30px 20px;text-align:center;color:#ffffff;">
                                <h1 style="margin:0;font-size:24px;">欢迎使用 {app_name} 👋</h1>
                                <p style="margin:8px 0 0;font-size:14px;opacity:0.9;">
                                    您已被添加到 {company_name}
                                </p>
                            </div>

                            <div style="padding:30px 25px;color:#374151;line-height:1.6;">
                                <p style="margin:0 0 15px;font-size:15px;">您好，<strong>{name}</strong>，</p>

                                <p style="margin:0 0 20px;font-size:14px;">
                                    您已被添加为 <strong>{company_name}</strong> 的用户。以下是您的登录信息：
                                </p>

                                <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:10px;padding:20px;margin:20px 0;">
                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>🌐 应用地址：</strong><br>
                                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                                    </p>

                                    <p style="margin:0 0 10px;font-size:14px;">
                                        <strong>📧 邮箱：</strong><br>
                                        {email}
                                    </p>

                                    <p style="margin:0;font-size:14px;">
                                        <strong>🔐 密码：</strong><br>
                                        {password}
                                    </p>
                                </div>

                                <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">
                                    请登录后尽快修改您的密码。
                                </p>
                            </div>

                            <div style="text-align:center;padding:20px;">
                                <a href="{app_url}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;font-size:14px;font-weight:500;">
                                    登录系统
                                </a>
                            </div>
                        </div>
                    </div>',
                ],
            ],
            'Sales Invoice' => [
                'subject' => 'Sales Invoice Created',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Invoice Number": "invoice_number",
                    "Customer Name": "sales_customer_name",
                    "Warehouse Name": "warehouse_name",
                    "Total Amount ": "total_amount",
                    "Discount Amount" : "discount_amount"
                  }',
                  'lang' => [
                    'ar' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">مرحبًا، {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">مرحبًا بك في {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">نأمل أن تصلك هذه الرسالة وأنت بخير! يرجى العثور على تفاصيل فاتورتك أدناه.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>رقم الفاتورة:</strong> {invoice_number}<br>
                    <strong>المستودع:</strong> {warehouse_name}<br>
                    <strong>المبلغ الإجمالي:</strong> {total_amount}<br>
                    <strong>مبلغ الخصم:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    لا تتردد في التواصل معنا إذا كان لديك أي أسئلة.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    شكرًا لك،,<br>
                    مع أطيب التحيات،<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'da' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hej, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Velkommen til {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Jeg håber, denne e-mail finder dig vel. Nedenfor finder du detaljerne for din faktura.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Fakturanummer:</strong> {invoice_number}<br>
                    <strong>Lager:</strong> {warehouse_name}<br>
                    <strong>Samlet beløb:</strong> {total_amount}<br>
                    <strong>Rabatbeløb:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Du er velkommen til at kontakte os, hvis du har spørgsmål.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Tak,<br>
                    Med venlig hilsen,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'de' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hallo, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Willkommen bei {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Ich hoffe, diese E-Mail erreicht Sie wohlbehalten. Nachfolgend finden Sie die Details Ihrer Rechnung.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Rechnungsnummer:</strong> {invoice_number}<br>
                    <strong>Lager:</strong> {warehouse_name}<br>
                    <strong>Gesamtbetrag:</strong> {total_amount}<br>
                    <strong>Rabattbetrag:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Bitte zögern Sie nicht, uns bei Fragen zu kontaktieren.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Vielen Dank,<br>
                    Mit freundlichen Grüßen,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                   'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hi, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Welcome to {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hope this email finds you well! Please find the details of your invoice below.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Invoice Number:</strong> {invoice_number}<br>
                    <strong>Warehouse:</strong> {warehouse_name}<br>
                    <strong>Total Amount:</strong> {total_amount}<br>
                    <strong>Discount Amount:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Feel free to reach out if you have any questions.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Thank You,<br>
                    Regards,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',
                    
                    'es' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hola, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Bienvenido a {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Esperamos que este correo le encuentre bien. A continuación encontrará los detalles de su factura.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Número de factura:</strong> {invoice_number}<br>
                    <strong>Almacén:</strong> {warehouse_name}<br>
                    <strong>Importe total:</strong> {total_amount}<br>
                    <strong>Importe del descuento:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    No dude en contactarnos si tiene alguna pregunta.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Gracias,<br>
                    Saludos,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'fr' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Bonjour, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Bienvenue chez {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Nous espérons que cet e-mail vous trouve bien. Veuillez trouver ci-dessous les détails de votre facture.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Numéro de facture:</strong> {invoice_number}<br>
                    <strong>Entrepôt:</strong> {warehouse_name}<br>
                    <strong>Montant total:</strong> {total_amount}<br>
                    <strong>Montant de la remise:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Nhésitez pas à nous contacter si vous avez des questions.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Merci,<br>
                    Cordialement,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'he' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">שלום, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">ברוכים הבאים ל {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">מקווים שהאימייל הזה מוצא אותך בטוב. להלן פרטי החשבונית שלך..</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>מספר חשבונית:</strong> {invoice_number}<br>
                    <strong>מחסן:</strong> {warehouse_name}<br>
                    <strong>סכום כולל:</strong> {total_amount}<br>
                    <strong>סכום הנחה:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    אל תהסס לפנות אלינו אם יש לך שאלות.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    תודה,<br>
                    בברכה,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'it' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Ciao, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Benvenuto in {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Speriamo che questa email la trovi bene. Di seguito trova i dettagli della sua fattura.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Numero fattura:</strong> {invoice_number}<br>
                    <strong>Magazzino:</strong> {warehouse_name}<br>
                    <strong>Importo totale:</strong> {total_amount}<br>
                    <strong>Importo dello sconto:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Non esiti a contattarci per qualsiasi domanda.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Grazie,<br>
                    Cordiali saluti,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                   'ja' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">こんにちは、{sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">{app_name}へようこそ</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">お元気でお過ごしのことと思います。以下に請求書の詳細をご確認ください。</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>請求書番号:</strong> {invoice_number}<br>
                    <strong>倉庫:</strong> {warehouse_name}<br>
                    <strong>合計金額:</strong> {total_amount}<br>
                    <strong>割引金額:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    ご不明な点がございましたら、お気軽にお問い合わせください。
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    ありがとうございます。<br>
                    よろしくお願いいたします。<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'nl' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Hallo, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Welkom bij {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Ik hoop dat deze e-mail u goed bereikt. Hieronder vindt u de details van uw factuur.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Factuurnummer:</strong> {invoice_number}<br>
                    <strong>Magazijn:</strong> {warehouse_name}<br>
                    <strong>Totaalbedrag:</strong> {total_amount}<br>
                    <strong>Kortingsbedrag:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Neem gerust contact met ons op als u vragen heeft.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Dank u,<br>
                    Met vriendelijke groet,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'pl' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Cześć, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Witamy w {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Mamy nadzieję, że ten e-mail zastaje Cię w dobrym zdrowiu. Poniżej znajdziesz szczegóły swojej faktury.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Numer faktury:</strong> {invoice_number}<br>
                    <strong>Magazyn:</strong> {warehouse_name}<br>
                    <strong>Łączna kwota:</strong> {total_amount}<br>
                    <strong>Kwota rabatu:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    W razie pytań prosimy o kontakt.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Dziękujemy,<br>
                    Z poważaniem,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',


                    'ru' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Здравствуйте, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Добро пожаловать в {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Надеемся, что это письмо находит вас в хорошем состоянии. Ниже приведены детали вашего счета.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Номер счета:</strong> {invoice_number}<br>
                    <strong>Склад:</strong> {warehouse_name}<br>
                    <strong>Общая сумма:</strong> {total_amount}<br>
                    <strong>Сумма скидки:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Пожалуйста, свяжитесь с нами, если у вас возникнут вопросы.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Спасибо,<br>
                    С уважением,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',


                    'pt' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Olá, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Bem-vindo a {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Esperamos que este e-mail o encontre bem. Abaixo estão os detalhes da sua fatura.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Número da fatura:</strong> {invoice_number}<br>
                    <strong>Armazém:</strong> {warehouse_name}<br>
                    <strong>Valor total:</strong> {total_amount}<br>
                    <strong>Valor do desconto:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Sinta-se à vontade para entrar em contacto se tiver alguma dúvida.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Obrigado,<br>
                    Com os melhores cumprimentos,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'pt-BR' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Olá, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Bem-vindo à {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Esperamos que este e-mail o encontre bem. Abaixo estão os detalhes da sua fatura.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Número da fatura:</strong> {invoice_number}<br>
                    <strong>Armazém:</strong> {warehouse_name}<br>
                    <strong>Valor total:</strong> {total_amount}<br>
                    <strong>Valor do desconto:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Sinta-se à vontade para entrar em contato se tiver alguma dúvida.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Obrigado,<br>
                    Atenciosamente,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'tr' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Merhaba, {sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">{app_name}\'e hoş geldiniz</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">Umarız bu e-posta sizi iyi bulur. Aşağıda faturanızın detaylarını bulabilirsiniz.</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>Fatura Numarası:</strong> {invoice_number}<br>
                    <strong>Depo:</strong> {warehouse_name}<br>
                    <strong>Toplam Tutar:</strong> {total_amount}<br>
                    <strong>İndirim Tutarı:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Herhangi bir sorunuz varsa bizimle iletişime geçebilirsiniz.
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    Teşekkür ederiz,<br>
                    Saygılarımızla,<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',

                    'zh' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">您好，{sales_customer_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">欢迎来到 {app_name}</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">希望此邮件能让您一切安好。以下是您的发票详情。</span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    <strong>发票编号:</strong> {invoice_number}<br>
                    <strong>仓库:</strong> {warehouse_name}<br>
                    <strong>总金额:</strong> {total_amount}<br>
                    <strong>折扣金额:</strong> {discount_amount}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    如果您有任何问题，请随时与我们联系。
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    谢谢，<br>
                    此致敬礼，<br>
                    {company_name}
                    </span></p>

                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; background-color: #f8f8f8;">
                    {app_url}
                    </span></p>',
                ],
            ],
            'Sales Invoice Return' => [
                'subject' => 'Invoice Return',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Return Number": "return_number",
                    "Return Date": "return_date",
                    "Customer Name": "sales_customer_name",
                    "Warehouse Name": "warehouse_name",
                    "Reason": "reason",
                    "Total Amount": "total_amount"
                  }',
                'lang' => [
                    'ar' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    مرحبا <strong>{sales_customer_name}</strong>،
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    نود إعلامك بأنه تم <strong>إرجاع فاتورة المبيعات</strong> بنجاح في <strong>{app_name}</strong>.
                    يرجى الاطلاع على تفاصيل الإرجاع أدناه.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">الحقل</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">رقم الإرجاع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">تاريخ الإرجاع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">اسم العميل</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">المستودع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">السبب</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">المبلغ الإجمالي</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    إذا كان لديك أي أسئلة بخصوص هذا الإرجاع، فلا تتردد في التواصل مع <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    شكرًا لاستخدامك <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    مع التحية،<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'da' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hej <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Vi vil gerne informere dig om, at en <strong>Retur af salgsfaktura</strong> er blevet behandlet med succes i <strong>{app_name}</strong>.
                    Se venligst returdetaljerne nedenfor.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Felt</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detaljer</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Returnummer</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Returdato</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Kundenavn</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Lager</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Årsag</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Samlet beløb</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Hvis du har spørgsmål vedrørende denne retur, er du velkommen til at kontakte <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Tak fordi du bruger <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'de' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wir möchten Sie darüber informieren, dass eine <strong>Rückgabe einer Verkaufsrechnung</strong> erfolgreich in <strong>{app_name}</strong> verarbeitet wurde.
                    Die Rückgabedetails finden Sie unten.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Feld</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Rücksendenummer</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Rücksendedatum</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Kundenname</td><td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Lager</td><td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Grund</td><td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Gesamtbetrag</td><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td></tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Wenn Sie Fragen zu dieser Rückgabe haben, wenden Sie sich bitte an <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Vielen Dank für die Nutzung von <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'en' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hello <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    We would like to inform you that a <strong>Sales Invoice Return</strong> has been successfully processed in <strong>{app_name}</strong>.  
                    Please find the return details below.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Field</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Return Number</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Return Date</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Customer Name</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Warehouse</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Reason</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Total Amount</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    If you have any questions regarding this return, please feel free to contact <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Thank you for using <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Regards,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'es' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hola <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nos gustaría informarle que una <strong>Devolución de factura de venta</strong> ha sido procesada con éxito en <strong>{app_name}</strong>.
                    A continuación encontrará los detalles de la devolución.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalles</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número de devolución</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Fecha de devolución</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nombre del cliente</td><td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Almacén</td><td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td><td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Importe total</td><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td></tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Si tiene alguna pregunta sobre esta devolución, no dude en contactar con <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Gracias por usar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Saludos,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'fr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Bonjour <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nous souhaitons vous informer qu’un <strong>Retour de facture de vente</strong> a été traité avec succès dans <strong>{app_name}</strong>.
                    Veuillez trouver les détails du retour ci-dessous.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Champ</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Détails</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numéro de retour</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Date de retour</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nom du client</td><td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Entrepôt</td><td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Raison</td><td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Montant total</td><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td></tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Si vous avez des questions concernant ce retour, n’hésitez pas à contacter <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Merci d’utiliser <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'he' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    שלום <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    ברצוננו להודיע לך כי <strong>החזרת חשבונית מכירה</strong> עובדה בהצלחה ב־<strong>{app_name}</strong>.
                    להלן פרטי ההחזרה.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">שדה</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">פרטים</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">מספר החזרה</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">תאריך החזרה</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">שם הלקוח</td><td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">מחסן</td><td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">סיבה</td><td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">סכום כולל</td><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td></tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    אם יש לך שאלות לגבי החזרה זו, אנא פנה אל <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    תודה על השימוש ב־<strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'it' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Ciao <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Desideriamo informarti che un <strong>Reso fattura di vendita</strong> è stato elaborato con successo in <strong>{app_name}</strong>.
                    Di seguito trovi i dettagli del reso.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Dettagli</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numero reso</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data reso</td><td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome cliente</td><td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazzino</td><td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td></tr>
                    <tr><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td><td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td></tr>
                    <tr style="background-color:#fafafa;"><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Importo totale</td><td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td></tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Se hai domande su questo reso, contatta <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Grazie per aver utilizzato <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'ja' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    こんにちは <strong>{sales_customer_name}</strong> 様、
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> にて <strong>販売請求書返品</strong> が正常に処理されたことをお知らせいたします。  
                    返品の詳細は以下をご確認ください。
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">項目</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">詳細</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">返品番号</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">返品日</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">顧客名</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">倉庫</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">理由</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">合計金額</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    返品についてご質問がございましたら、<strong>{company_name}</strong> までお気軽にお問い合わせください。
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    <strong>{app_name}</strong> をご利用いただきありがとうございます。
                    </p>

                    <p style="font-size:14px;color:#666;">
                    敬具<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'nl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    We willen u informeren dat een <strong>Verkoopfactuur Retour</strong> succesvol is verwerkt in <strong>{app_name}</strong>.  
                    Hieronder vindt u de retourgegevens.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Veld</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Retournummer</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Retourdatum</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Klantnaam</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazijn</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Reden</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Totaalbedrag</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Als u vragen heeft over deze retourzending, neem dan gerust contact op met <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Bedankt voor het gebruik van <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'pl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Witaj <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Informujemy, że <strong>Zwrot faktury sprzedaży</strong> został pomyślnie przetworzony w <strong>{app_name}</strong>.  
                    Szczegóły zwrotu znajdują się poniżej.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Pole</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Szczegóły</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numer zwrotu</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data zwrotu</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nazwa klienta</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazyn</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Powód</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Łączna kwota</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    W przypadku pytań dotyczących tego zwrotu prosimy o kontakt z <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Dziękujemy za korzystanie z <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Pozdrawiamy,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'ru' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Здравствуйте, <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Сообщаем вам, что <strong>возврат счета продажи</strong> был успешно обработан в <strong>{app_name}</strong>.  
                    Пожалуйста, ознакомьтесь с деталями возврата ниже.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Поле</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Детали</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Номер возврата</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Дата возврата</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Имя клиента</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Склад</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Причина</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Общая сумма</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Если у вас есть вопросы по поводу этого возврата, пожалуйста свяжитесь с <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Спасибо за использование <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>',

                   'pt' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma <strong>Devolução de Fatura de Venda</strong> foi processada com sucesso em <strong>{app_name}</strong>.  
                    Por favor, veja os detalhes da devolução abaixo.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalhes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número da Devolução</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data da Devolução</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome do Cliente</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Armazém</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Valor Total</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Se tiver alguma dúvida sobre esta devolução, por favor contacte <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Obrigado por utilizar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Com os melhores cumprimentos,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'pt-BR' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma <strong>Devolução de Fatura de Venda</strong> foi processada com sucesso em <strong>{app_name}</strong>.  
                    Por favor, veja os detalhes da devolução abaixo.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalhes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número da Devolução</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data da Devolução</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome do Cliente</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Armazém</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Valor Total</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Se você tiver alguma dúvida sobre esta devolução, entre em contato com <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Obrigado por usar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    
                    'tr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Merhaba <strong>{sales_customer_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> içerisinde bir <strong>Satış Faturası İadesi</strong> başarıyla işlendiğini bildirmek isteriz.  
                    İade detaylarını aşağıda bulabilirsiniz.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Alan</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detaylar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">İade Numarası</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">İade Tarihi</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Müşteri Adı</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Depo</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Sebep</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Toplam Tutar</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Bu iade hakkında herhangi bir sorunuz varsa lütfen <strong>{company_name}</strong> ile iletişime geçin.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    <strong>{app_name}</strong> kullandığınız için teşekkür ederiz.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>',
                    
                    'zh' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    您好 <strong>{sales_customer_name}</strong>，
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    我们想通知您，<strong>销售发票退货</strong> 已在 <strong>{app_name}</strong> 中成功处理。  
                    请查看以下退货详情。
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">字段</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">详情</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">退货编号</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">退货日期</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">客户名称</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{sales_customer_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">仓库</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">原因</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">总金额</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    如果您对本次退货有任何疑问，请联系 <strong>{company_name}</strong>。
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    感谢您使用 <strong>{app_name}</strong>。
                    </p>

                    <p style="font-size:14px;color:#666;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>',

                ],
            ],
            'Purchase Invoice' => [
                'subject' => 'Purchase Invoice Created',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Invoice Number": "invoice_number",
                    "Vandor Name": "purchase_vendor_name",
                    "Warehouse Name": "warehouse_name",
                    "Total Amount": "total_amount",
                    "Discount Amount": "discount_amount"
                  }',
                'lang' => [
                    'ar' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    مرحبًا <strong>{purchase_vendor_name}</strong>،

                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    نود إعلامك بأنه تم إنشاء فاتورة شراء في <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>تفاصيل الفاتورة:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>رقم الفاتورة:</strong> {invoice_number}</li>
                    <li><strong>اسم المورد:</strong> {purchase_vendor_name}</li>
                    <li><strong>اسم المستودع:</strong> {warehouse_name}</li>
                    <li><strong>قيمة الخصم:</strong> {discount_amount}</li>
                    <li><strong>المبلغ الإجمالي:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    إذا كانت لديك أي أسئلة، فلا تتردد في التواصل مع <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    شكرًا لك،<br>
                    <strong>{company_name}</strong>
                    </p>',

                   'da' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hej <strong>{purchase_vendor_name}</strong>,

                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Vi vil gerne informere dig om, at der er blevet oprettet en købsfaktura i <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Fakturadetaljer:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Fakturanummer:</strong> {invoice_number}</li>
                    <li><strong>Leverandørnavn:</strong> {purchase_vendor_name}</li>
                    <li><strong>Lagerets navn:</strong> {warehouse_name}</li>
                    <li><strong>Rabatbeløb:</strong> {discount_amount}</li>
                    <li><strong>Samlet beløb:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Hvis du har spørgsmål, er du velkommen til at kontakte <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Tak,<br>
                    <strong>{company_name}</strong>
                    </p>',



                    'de' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{purchase_vendor_name}</strong>,

                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wir möchten Sie darüber informieren, dass in <strong>{app_name}</strong> eine Einkaufsrechnung erstellt wurde.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Rechnungsdetails:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Rechnungsnummer:</strong> {invoice_number}</li>
                    <li><strong>Lieferantenname:</strong> {purchase_vendor_name}</li>
                    <li><strong>Lagername:</strong> {warehouse_name}</li>
                    <li><strong>Rabattbetrag:</strong> {discount_amount}</li>
                    <li><strong>Gesamtbetrag:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wenn Sie Fragen haben, können Sie sich gerne an <strong>{company_name}</strong> wenden.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Vielen Dank,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'en' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hello <strong>{purchase_vendor_name}</strong>,

                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    We would like to inform you that a purchase invoice has been generated in <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Invoice Details:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Invoice Number:</strong> {invoice_number}</li>
                    <li><strong>Vendor Name:</strong> {purchase_vendor_name}</li>
                    <li><strong>Warehouse Name:</strong> {warehouse_name}</li>
                    <li><strong>Discount Amount:</strong> {discount_amount}</li>
                    <li><strong>Total Amount:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    If you have any questions, please feel free to contact <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Thank you,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'es' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hola <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nos gustaría informarle que se ha generado una factura de compra en <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Detalles de la factura:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Número de factura:</strong> {invoice_number}</li>
                    <li><strong>Nombre del proveedor:</strong> {purchase_vendor_name}</li>
                    <li><strong>Nombre del almacén:</strong> {warehouse_name}</li>
                    <li><strong>Monto de descuento:</strong> {discount_amount}</li>
                    <li><strong>Monto total:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Si tiene alguna pregunta, no dude en contactar con <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Gracias,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'fr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Bonjour <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nous souhaitons vous informer qu\'une facture d\'achat a été générée dans <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Détails de la facture :</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Numéro de facture :</strong> {invoice_number}</li>
                    <li><strong>Nom du fournisseur :</strong> {purchase_vendor_name}</li>
                    <li><strong>Nom de l\'entrepôt :</strong> {warehouse_name}</li>
                    <li><strong>Montant de la remise :</strong> {discount_amount}</li>
                    <li><strong>Montant total :</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Si vous avez des questions, n\'hésitez pas à contacter <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Merci,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'he' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    שלום <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    אנו רוצים להודיע לך כי נוצרה חשבונית רכישה ב־<strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>פרטי החשבונית:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>מספר חשבונית:</strong> {invoice_number}</li>
                    <li><strong>שם הספק:</strong> {purchase_vendor_name}</li>
                    <li><strong>שם המחסן:</strong> {warehouse_name}</li>
                    <li><strong>סכום הנחה:</strong> {discount_amount}</li>
                    <li><strong>סכום כולל:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    אם יש לך שאלות, אל תהסס ליצור קשר עם <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    תודה,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'it' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Ciao <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Desideriamo informarti che è stata generata una fattura di acquisto in <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Dettagli della fattura:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Numero fattura:</strong> {invoice_number}</li>
                    <li><strong>Nome del fornitore:</strong> {purchase_vendor_name}</li>
                    <li><strong>Nome del magazzino:</strong> {warehouse_name}</li>
                    <li><strong>Importo sconto:</strong> {discount_amount}</li>
                    <li><strong>Importo totale:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Se hai domande, non esitare a contattare <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Grazie,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'ja' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    こんにちは <strong>{purchase_vendor_name}</strong> 様,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> にて仕入請求書が作成されましたのでお知らせいたします。
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>請求書の詳細:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>請求書番号:</strong> {invoice_number}</li>
                    <li><strong>仕入先名:</strong> {purchase_vendor_name}</li>
                    <li><strong>倉庫名:</strong> {warehouse_name}</li>
                    <li><strong>割引額:</strong> {discount_amount}</li>
                    <li><strong>合計金額:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    ご不明な点がございましたら、<strong>{company_name}</strong> までお問い合わせください。
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    ありがとうございます。<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'nl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wij willen u informeren dat er een inkoopfactuur is gegenereerd in <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Factuurdetails:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Factuurnummer:</strong> {invoice_number}</li>
                    <li><strong>Leveranciersnaam:</strong> {purchase_vendor_name}</li>
                    <li><strong>Magazijnnaam:</strong> {warehouse_name}</li>
                    <li><strong>Kortingsbedrag:</strong> {discount_amount}</li>
                    <li><strong>Totaalbedrag:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Als u vragen heeft, neem dan gerust contact op met <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Bedankt,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'pl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Witaj <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Chcielibyśmy poinformować, że w <strong>{app_name}</strong> została wygenerowana faktura zakupu.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Szczegóły faktury:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Numer faktury:</strong> {invoice_number}</li>
                    <li><strong>Nazwa dostawcy:</strong> {purchase_vendor_name}</li>
                    <li><strong>Nazwa magazynu:</strong> {warehouse_name}</li>
                    <li><strong>Kwota rabatu:</strong> {discount_amount}</li>
                    <li><strong>Kwota całkowita:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Jeśli masz pytania, skontaktuj się z <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Dziękujemy,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'pt' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma fatura de compra foi gerada em <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Detalhes da fatura:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Número da fatura:</strong> {invoice_number}</li>
                    <li><strong>Nome do fornecedor:</strong> {purchase_vendor_name}</li>
                    <li><strong>Nome do armazém:</strong> {warehouse_name}</li>
                    <li><strong>Valor do desconto:</strong> {discount_amount}</li>
                    <li><strong>Valor total:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Se tiver alguma dúvida, entre em contato com <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Obrigado,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'pt-BR' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma fatura de compra foi gerada no <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Detalhes da fatura:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Número da fatura:</strong> {invoice_number}</li>
                    <li><strong>Nome do fornecedor:</strong> {purchase_vendor_name}</li>
                    <li><strong>Nome do armazém:</strong> {warehouse_name}</li>
                    <li><strong>Valor do desconto:</strong> {discount_amount}</li>
                    <li><strong>Valor total:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Se tiver alguma dúvida, entre em contato com <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Obrigado,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'ru' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Здравствуйте <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Сообщаем вам, что в <strong>{app_name}</strong> был создан счет на покупку.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Детали счета:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Номер счета:</strong> {invoice_number}</li>
                    <li><strong>Имя поставщика:</strong> {purchase_vendor_name}</li>
                    <li><strong>Название склада:</strong> {warehouse_name}</li>
                    <li><strong>Сумма скидки:</strong> {discount_amount}</li>
                    <li><strong>Общая сумма:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Если у вас есть вопросы, свяжитесь с <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Спасибо,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'tr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Merhaba <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> içinde bir satın alma faturası oluşturulduğunu bildirmek isteriz.
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>Fatura Detayları:</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>Fatura Numarası:</strong> {invoice_number}</li>
                    <li><strong>Tedarikçi Adı:</strong> {purchase_vendor_name}</li>
                    <li><strong>Depo Adı:</strong> {warehouse_name}</li>
                    <li><strong>İndirim Tutarı:</strong> {discount_amount}</li>
                    <li><strong>Toplam Tutar:</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Herhangi bir sorunuz varsa <strong>{company_name}</strong> ile iletişime geçebilirsiniz.
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    Teşekkürler,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'zh' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    您好 <strong>{purchase_vendor_name}</strong>，
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    我们想通知您，在 <strong>{app_name}</strong> 中已生成一张采购发票。
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>发票详情：</strong>
                    </p>

                    <ul style="font-size:15px;color:#333;line-height:1.8;padding-left:20px;">
                    <li><strong>发票编号：</strong> {invoice_number}</li>
                    <li><strong>供应商名称：</strong> {purchase_vendor_name}</li>
                    <li><strong>仓库名称：</strong> {warehouse_name}</li>
                    <li><strong>折扣金额：</strong> {discount_amount}</li>
                    <li><strong>总金额：</strong> {total_amount}</li>
                    </ul>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    如果您有任何问题，请联系 <strong>{company_name}</strong>。
                    </p>

                    <p style="font-size:15px;color:#333;margin-top:15px;">
                    谢谢，<br>
                    <strong>{company_name}</strong>
                    </p>',
                ],
            ],
            'Purchase Invoice Return' => [
                'subject' => 'Invoice Return',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Return Number": "return_number",
                    "Return Date": "return_date",
                    "Vendor Name": "purchase_vendor_name",
                    "Warehouse Name": "warehouse_name",
                    "Reason": "reason",
                    "Total Amount": "total_amount"
                  }',
                  'lang' => [
                    'ar' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    مرحبًا <strong>{purchase_vendor_name}</strong>،
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    نود إعلامك بأنه تم بنجاح معالجة <strong>إرجاع فاتورة الشراء</strong> في <strong>{app_name}</strong>.
                    يرجى الاطلاع على تفاصيل الإرجاع أدناه.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">الحقل</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">رقم الإرجاع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">تاريخ الإرجاع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">اسم المورد</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">المستودع</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">السبب</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">المبلغ الإجمالي</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    إذا كان لديك أي استفسار بخصوص هذا الإرجاع، فلا تتردد في التواصل مع <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    شكرًا لاستخدامك <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    مع التحية،<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'da' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hej <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Vi vil gerne informere dig om, at en <strong>Retur af købsfaktura</strong> er blevet behandlet med succes i <strong>{app_name}</strong>.
                    Se venligst returdetaljerne nedenfor.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Felt</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detaljer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Returnummer</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Returdato</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Leverandørnavn</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Lager</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Årsag</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Samlet beløb</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Hvis du har spørgsmål vedrørende denne retur, er du velkommen til at kontakte <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Tak fordi du bruger <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'de' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wir möchten Sie darüber informieren, dass eine <strong>Rückgabe der Einkaufsrechnung</strong> erfolgreich in <strong>{app_name}</strong> verarbeitet wurde.
                    Bitte finden Sie unten die Rückgabedetails.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Feld</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Rückgabenummer</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Rückgabedatum</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Lieferantenname</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Lager</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Grund</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Gesamtbetrag</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Wenn Sie Fragen zu dieser Rückgabe haben, können Sie sich gerne an <strong>{company_name}</strong> wenden.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Vielen Dank, dass Sie <strong>{app_name}</strong> verwenden.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'en' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hello <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    We would like to inform you that a <strong>Sales Invoice Return</strong> has been successfully processed in <strong>{app_name}</strong>.
                    Please find the return details below.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Field</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Return Number</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Return Date</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Vendor Name</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Warehouse</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Reason</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Total Amount</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    If you have any questions regarding this return, please feel free to contact <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Thank you for using <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Regards,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'es' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hola <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nos gustaría informarle que una <strong>Devolución de factura de compra</strong> ha sido procesada con éxito en <strong>{app_name}</strong>.
                    Por favor, encuentre los detalles de la devolución a continuación.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número de devolución</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Fecha de devolución</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nombre del proveedor</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Almacén</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Razón</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Monto total</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Si tiene alguna pregunta sobre esta devolución, no dude en ponerse en contacto con <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Gracias por usar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Saludos,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'fr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Bonjour <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Nous souhaitons vous informer qu\'un <strong>Retour de facture d\achat</strong> a été traité avec succès dans <strong>{app_name}</strong>.
                    Veuillez trouver les détails du retour ci-dessous.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Champ</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numéro de retour</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Date de retour</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nom du fournisseur</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Entrepôt</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Raison</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Montant total</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Si vous avez des questions concernant ce retour, veuillez contacter <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Merci d\'utiliser <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>',


                   'it' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Ciao <strong>{purchase_vendor_name}</strong>,
                    </p>
                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Ti informiamo che un <strong>Reso della fattura di acquisto</strong> è stato elaborato con successo in <strong>{app_name}</strong>.
                    </p>
                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                    <thead>
                    <tr style="background-color:#f5f5f5;">
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                    <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Dettagli</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numero di reso</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data di reso</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome fornitore</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazzino</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                    </tr>
                    <tr>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                    </tr>
                    <tr style="background-color:#fafafa;">
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Importo totale</td>
                    <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                    </tr>
                    </tbody>
                    </table>
                    <p style="font-size:15px;color:#333;margin-top:18px;">
                    Se hai domande contatta <strong>{company_name}</strong>.
                    </p>
                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Grazie per utilizzare <strong>{app_name}</strong>.
                    </p>
                    <p style="font-size:14px;color:#666;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'ja' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    こんにちは <strong>{purchase_vendor_name}</strong> 様、
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> にて <strong>仕入請求書返品</strong> が正常に処理されたことをお知らせいたします。
                    以下に返品の詳細をご確認ください。
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">項目</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">返品番号</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">返品日</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">仕入先名</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">倉庫</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">理由</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">合計金額</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    この返品に関してご不明な点がございましたら、<strong>{company_name}</strong> までお気軽にお問い合わせください。
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    <strong>{app_name}</strong> をご利用いただきありがとうございます。
                    </p>

                    <p style="font-size:14px;color:#666;">
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'nl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Wij willen u informeren dat een <strong>Retour inkoopfactuur</strong> succesvol is verwerkt in <strong>{app_name}</strong>.
                    Bekijk hieronder de retourdetails.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Veld</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Retournummer</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Retourdatum</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Leveranciersnaam</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazijn</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Reden</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Totaalbedrag</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Als u vragen heeft over deze retour, neem dan gerust contact op met <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Bedankt voor het gebruik van <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'pl' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Witaj <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Chcielibyśmy poinformować, że <strong>Zwrot faktury zakupu</strong> został pomyślnie przetworzony w <strong>{app_name}</strong>.
                    Poniżej znajdują się szczegóły zwrotu.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Pole</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Szczegóły</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Numer zwrotu</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data zwrotu</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nazwa dostawcy</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Magazyn</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Powód</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Łączna kwota</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Jeśli masz jakiekolwiek pytania dotyczące tego zwrotu, skontaktuj się z <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Dziękujemy za korzystanie z <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'ru' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Здравствуйте, <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Сообщаем вам, что <strong>Возврат счета-фактуры покупки</strong> был успешно обработан в <strong>{app_name}</strong>.
                    Пожалуйста, ознакомьтесь с деталями возврата ниже.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Поле</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Детали</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Номер возврата</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Дата возврата</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Имя поставщика</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Склад</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Причина</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Общая сумма</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Если у вас есть вопросы по этому возврату, пожалуйста, свяжитесь с <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Спасибо за использование <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'pt' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma <strong>Devolução da fatura de compra</strong> foi processada com sucesso no <strong>{app_name}</strong>.
                    Por favor, veja os detalhes da devolução abaixo.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número da Devolução</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data da Devolução</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome do Fornecedor</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Armazém</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Valor Total</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Se tiver alguma dúvida sobre esta devolução, não hesite em contactar <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Obrigado por usar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'pt-BR' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    Gostaríamos de informar que uma <strong>Devolução da fatura de compra</strong> foi processada com sucesso no <strong>{app_name}</strong>.
                    Por favor, veja os detalhes da devolução abaixo.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Campo</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Número da Devolução</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Data da Devolução</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Nome do Fornecedor</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Armazém</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Motivo</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Valor Total</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Se tiver alguma dúvida sobre esta devolução, não hesite em contactar <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    Obrigado por usar <strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>',


                    'he' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    שלום <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    ברצוננו להודיע לך כי <strong>החזרת חשבונית מכירה</strong>החזרת חשבונית רכישה<strong>{app_name}</strong>.
                    אנא עיין בפרטי ההחזרה להלן.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">שדה</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">פרטים</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">מספר החזרה</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">תאריך החזרה</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">שם הספק</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">מחסן</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">סיבה</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">סכום כולל</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    אם יש לך שאלות בנוגע להחזרה זו, אנא צור קשר עם <strong>{company_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    תודה על השימוש ב-<strong>{app_name}</strong>.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'tr' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Merhaba <strong>{purchase_vendor_name}</strong>,
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    <strong>{app_name}</strong> içinde bir <strong>Satın Alma Faturası İadesi</strong> başarıyla işlendiğini size bildirmek isteriz.
                    Lütfen aşağıdaki iade detaylarını inceleyin.
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Alan</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">Detaylar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">İade Numarası</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">İade Tarihi</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Tedarikçi Adı</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Depo</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Sebep</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">Toplam Tutar</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    Bu iade ile ilgili herhangi bir sorunuz varsa, lütfen <strong>{company_name}</strong> ile iletişime geçmekten çekinmeyin.
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    <strong>{app_name}</strong> kullandığınız için teşekkür ederiz.
                    </p>

                    <p style="font-size:14px;color:#666;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>',

                    'zh' => '<p style="font-size:15px;color:#333;margin-bottom:10px;">
                    您好 <strong>{purchase_vendor_name}</strong>，
                    </p>

                    <p style="font-size:15px;color:#333;line-height:1.6;">
                    我们想通知您，在 <strong>{app_name}</strong> 中一笔<strong>采购发票退回</strong>已成功处理。
                    请查看以下退货详情。
                    </p>

                    <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:18px;font-size:14px;color:#333;">
                        <thead>
                            <tr style="background-color:#f5f5f5;">
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">字段</th>
                                <th align="left" style="padding:10px;border:1px solid #e5e5e5;">详情</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">退货编号</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_number}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">退货日期</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{return_date}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">供应商名称</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{purchase_vendor_name}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">仓库</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{warehouse_name}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">原因</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;">{reason}</td>
                            </tr>
                            <tr style="background-color:#fafafa;">
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">总金额</td>
                                <td style="padding:10px;border:1px solid #e5e5e5;font-weight:600;">{total_amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="font-size:15px;color:#333;margin-top:18px;line-height:1.6;">
                    如果您对此退货有任何疑问，请随时联系 <strong>{company_name}</strong>。
                    </p>

                    <p style="font-size:14px;color:#666;margin-top:20px;">
                    感谢您使用 <strong>{app_name}</strong>。
                    </p>

                    <p style="font-size:14px;color:#666;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>',
                ],
            ],
            'Helpdesk Ticket' =>
            [
                'subject' => 'Helpdesk Ticket Created',
                'variables' => '{
                        "App Name": "app_name",
                        "Company Name" : "company_name",
                        "App Url": "app_url",
                        "Ticket Name": "ticket_name",
                        "Ticket ID": "ticket_id",
                        "Ticket URL" : "ticket_url",
                        "Ticket Description": "ticket_description",
                        "Ticket Category": "ticket_category",
                        "Ticket Priority": "ticket_priority"
                    }',
                    'lang' => [
                        'ar' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        مرحبًا ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        مرحبًا بك في <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        تم إنشاء تذكرة الدعم الخاصة بك بنجاح. سيقوم فريق الدعم لدينا بمراجعتها والرد عليك قريبًا.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>معرّف التذكرة :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>الوصف :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        يمكنك متابعة تقدم التذكرة الخاصة بك في أي وقت باستخدام الزر أدناه.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        عرض التذكرة
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        أو افتح التطبيق من هنا: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        شكرًا لتواصلك معنا. سيقوم فريقنا بمساعدتك في أقرب وقت ممكن.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        مع أطيب التحيات,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',


                        'da' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Hej ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Velkommen til <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Din supportticket er blevet oprettet med succes. Vores supportteam vil gennemgå den og vende tilbage til dig snart.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Ticket ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Beskrivelse :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Du kan følge status på din ticket når som helst ved at bruge knappen nedenfor.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Se Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Eller åbn applikationen her: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Tak fordi du kontaktede os. Vores team vil hjælpe dig så hurtigt som muligt.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Med venlig hilsen,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        
                        'de' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Hallo ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Willkommen bei <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Ihr Support-Ticket wurde erfolgreich erstellt. Unser Support-Team wird es prüfen und sich bald bei Ihnen melden.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Ticket ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Beschreibung :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Sie können den Fortschritt Ihres Tickets jederzeit über die untenstehende Schaltfläche verfolgen.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Ticket anzeigen
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Oder öffnen Sie die Anwendung hier: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Vielen Dank, dass Sie uns kontaktiert haben. Unser Team wird Ihnen so schnell wie möglich helfen.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Mit freundlichen Grüßen,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'en' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Hello ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Welcome to <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Your support ticket has been successfully created. Our support team will review it and get back to you shortly.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Ticket ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Description :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        You can track the progress of your ticket anytime using the button below.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        View Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Or open the application here: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Thank you for contacting us. Our team will assist you as soon as possible.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Best Regards,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'es' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Hola ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Bienvenido a <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Tu ticket de soporte se ha creado correctamente. Nuestro equipo de soporte lo revisará y se pondrá en contacto contigo pronto.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID del Ticket :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Descripción :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Puedes seguir el progreso de tu ticket en cualquier momento usando el botón a continuación.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Ver Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        O abre la aplicación aquí: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Gracias por contactarnos. Nuestro equipo te ayudará lo antes posible.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Saludos cordiales,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',


                        'fr' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Bonjour ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Bienvenue sur <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Votre ticket de support a été créé avec succès. Notre équipe de support va lexaminer et vous répondra bientôt.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID du Ticket :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Description :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Vous pouvez suivre lavancement de votre ticket à tout moment en utilisant le bouton ci-dessous.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Voir le Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Ou ouvrez lapplication ici : 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Merci de nous avoir contactés. Notre équipe vous assistera dès que possible.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Cordialement,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',


                        'it' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Ciao ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Benvenuto su <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Il tuo ticket di supporto è stato creato con successo. Il nostro team di supporto lo esaminerà e ti risponderà a breve.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID Ticket :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Descrizione :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Puoi monitorare lo stato del tuo ticket in qualsiasi momento utilizzando il pulsante qui sotto.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Visualizza Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Oppure apri lapplicazione qui: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Grazie per averci contattato. Il nostro team ti assisterà il prima possibile.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Cordiali saluti,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'ja' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        こんにちは  さん,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        <strong>{app_name}</strong> へようこそ 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        サポートチケットが正常に作成されました。サポートチームが内容を確認し、まもなくご連絡いたします。
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>チケットID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>説明 :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        以下のボタンを使用して、いつでもチケットの進捗状況を確認できます。
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        チケットを見る
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        またはこちらからアプリケーションを開いてください: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        お問い合わせいただきありがとうございます。サポートチームができるだけ早く対応いたします。
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        よろしくお願いいたします,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'nl' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Hallo ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Welkom bij <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Je supportticket is succesvol aangemaakt. Ons supportteam zal het bekijken en binnenkort contact met je opnemen.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Ticket ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Beschrijving :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Je kunt de voortgang van je ticket op elk moment volgen via de onderstaande knop.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Bekijk Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Of open de applicatie hier: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Bedankt dat je contact met ons hebt opgenomen. Ons team helpt je zo snel mogelijk.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Met vriendelijke groet,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'pl' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Witaj ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Witamy w <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Twoje zgłoszenie do pomocy technicznej zostało pomyślnie utworzone. Nasz zespół wsparcia wkrótce je przeanalizuje i skontaktuje się z Tobą.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID Zgłoszenia :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Opis :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Możesz w każdej chwili śledzić status swojego zgłoszenia, korzystając z poniższego przycisku.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Zobacz Zgłoszenie
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Lub otwórz aplikację tutaj: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Dziękujemy za kontakt z nami. Nasz zespół pomoże Ci tak szybko, jak to możliwe.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Pozdrawiamy,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'ru' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Здравствуйте ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Добро пожаловать в <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Ваш запрос в службу поддержки был успешно создан. Наша команда поддержки рассмотрит его и скоро свяжется с вами.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID Тикета :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Описание :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Вы можете отслеживать статус вашего тикета в любое время с помощью кнопки ниже.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Просмотреть Тикет
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Или откройте приложение здесь: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Спасибо, что связались с нами. Наша команда поможет вам как можно скорее.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        С уважением,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'pt' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Olá ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Bem-vindo ao <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Seu ticket de suporte foi criado com sucesso. Nossa equipe de suporte irá analisá-lo e retornará em breve.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID do Ticket :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Descrição :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Você pode acompanhar o progresso do seu ticket a qualquer momento usando o botão abaixo.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Ver Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Ou abra a aplicação aqui: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Obrigado por entrar em contato conosco. Nossa equipe irá ajudá-lo o mais rápido possível.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Atenciosamente,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'tr'=>'<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Merhaba ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        <strong>{app_name}</strong> uygulamasına hoş geldiniz 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Destek talebiniz başarıyla oluşturuldu. Destek ekibimiz talebinizi inceleyecek ve kısa süre içinde size geri dönüş yapacaktır.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Talep ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Açıklama :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Aşağıdaki butonu kullanarak talebinizin durumunu istediğiniz zaman takip edebilirsiniz.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Talebi Görüntüle
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Veya uygulamayı buradan açın: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Bizimle iletişime geçtiğiniz için teşekkür ederiz. Ekibimiz size mümkün olan en kısa sürede yardımcı olacaktır.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Saygılarımızla,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'pt-BR' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Olá ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Bem-vindo ao <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Seu ticket de suporte foi criado com sucesso. Nossa equipe de suporte irá analisá-lo e retornará em breve.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>ID do Ticket :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Descrição :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Você pode acompanhar o progresso do seu ticket a qualquer momento usando o botão abaixo.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Ver Ticket
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Ou abra a aplicação aqui: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Obrigado por entrar em contato conosco. Nossa equipe irá ajudá-lo o mais rápido possível.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Atenciosamente,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'he' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        שלום ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        ברוכים הבאים ל־<strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        כרטיס התמיכה שלך נוצר בהצלחה. צוות התמיכה שלנו יבדוק אותו ויחזור אליך בקרוב.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>מזהה כרטיס :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>תיאור :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        תוכל לעקוב אחר התקדמות הכרטיס שלך בכל עת באמצעות הכפתור למטה.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        הצג כרטיס
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        או פתח את האפליקציה כאן: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        תודה שפנית אלינו. הצוות שלנו יסייע לך בהקדם האפשרי.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        בברכה,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'tr' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        Merhaba ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        <strong>{app_name}</strong> uygulamasına hoş geldiniz 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Destek talebiniz başarıyla oluşturuldu. Destek ekibimiz talebinizi inceleyecek ve kısa süre içinde size geri dönüş yapacaktır.
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>Talep ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>Açıklama :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        Aşağıdaki butonu kullanarak talebinizin durumunu istediğiniz zaman takip edebilirsiniz.
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        Talebi Görüntüle
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        Veya uygulamayı buradan açın: 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        Bizimle iletişime geçtiğiniz için teşekkür ederiz. Ekibimiz size mümkün olan en kısa sürede yardımcı olacaktır.
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        Saygılarımızla,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',

                        'zh' => '<div style="font-family: Arial, Helvetica, sans-serif; background:#f7f8fc; padding:25px;">

                        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:8px;padding:25px;border:1px solid #e6e8f0;">

                        <p style="font-size:18px;color:#333;margin-bottom:15px;">
                        您好 ,
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        欢迎使用 <strong>{app_name}</strong> 🎉
                        </p>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        您的支持工单已成功创建。我们的支持团队将会查看您的请求并尽快与您联系。
                        </p>

                        <div style="background:#f1f3ff;border-left:4px solid #6676ef;padding:15px;margin:20px 0;border-radius:5px;">
                        <p style="margin:0;font-size:14px;color:#333;">
                        <strong>工单 ID :</strong> {ticket_id}
                        </p>

                        <p style="margin-top:8px;font-size:14px;color:#333;">
                        <strong>描述 :</strong> {ticket_description}
                        </p>
                        </div>

                        <p style="font-size:15px;color:#555;line-height:1.7;">
                        您可以随时使用下面的按钮查看您的工单状态。
                        </p>

                        <p style="text-align:center;margin:25px 0;">
                        <a href="{ticket_url}" target="_blank"
                        style="background:#6676ef;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:6px;font-size:15px;font-weight:bold;">
                        查看工单
                        </a>
                        </p>

                        <p style="font-size:14px;color:#666;">
                        或在这里打开应用： 
                        <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                        </p>

                        <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                        <p style="font-size:14px;color:#777;">
                        感谢您联系我们。我们的团队会尽快为您提供帮助。
                        </p>

                        <p style="font-size:14px;color:#333;margin-top:15px;">
                        此致敬礼,<br>
                        <strong>{company_name}</strong><br>
                        {app_name}
                        </p>

                        </div>
                        </div>',
                    ],
            ],
            'Helpdesk Ticket Reply' => [
                'subject' => 'Helpdesk Ticket Reply',
                'variables' => '{
                        "App Name" : "app_name",
                        "Company Name" : "company_name",
                        "App Url": "app_url",
                        "Ticket Name" : "ticket_name",
                        "Ticket Id" : "ticket_id",
                        "Ticket URL" : "ticket_url",
                        "Ticket Description" : "ticket_description"
                    }',
                'lang' => [
                    'ar' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • إشعار تذكرة الدعم
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    مرحباً،
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    تم استلام طلب الدعم الخاص بك بنجاح من قبل <strong>{company_name}</strong>.  
                    سيقوم فريقنا بمراجعة المشكلة والرد عليك في أقرب وقت ممكن.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>رقم التذكرة :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>عنوان التذكرة :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>الوصف :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    يمكنك متابعة التحديثات أو الرد على هذه التذكرة في أي وقت باستخدام الزر أدناه.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    عرض التذكرة
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    إذا كنت بحاجة إلى مزيد من المساعدة، فلا تتردد في التواصل من خلال التطبيق.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',

                    'da' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Support Ticket Notifikation
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hej,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Din supportanmodning er blevet modtaget af <strong>{company_name}</strong>.  
                    Vores team vil gennemgå problemet og vende tilbage til dig så hurtigt som muligt.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket Titel :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Beskrivelse :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Du kan følge opdateringer eller svare på denne ticket når som helst via knappen nedenfor.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Se Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Hvis du har brug for yderligere hjælp, er du velkommen til at kontakte os via applikationen.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',

                    'de' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Support Ticket Benachrichtigung
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Ihre Support-Anfrage wurde erfolgreich von <strong>{company_name}</strong> empfangen.  
                    Unser Team wird das Problem prüfen und sich so schnell wie möglich bei Ihnen melden.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket Titel :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Beschreibung :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Sie können Updates verfolgen oder jederzeit über die Schaltfläche unten auf dieses Ticket antworten.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Ticket anzeigen
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Wenn Sie weitere Unterstützung benötigen, können Sie uns jederzeit über die Anwendung kontaktieren.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    
                    'en' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Support Ticket Notification
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hello,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Your support request has been successfully received by <strong>{company_name}</strong>.  
                    Our team will review the issue and respond as soon as possible.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket Title :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Description :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    You can track updates or reply to this ticket anytime using the button below.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    View Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    If you need further assistance, feel free to reach out through the application.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',

                    'es' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Notificación de Ticket de Soporte
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hola,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Su solicitud de soporte ha sido recibida con éxito por <strong>{company_name}</strong>.  
                    Nuestro equipo revisará el problema y le responderá lo antes posible.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID del Ticket :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Título del Ticket :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Descripción :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Puede seguir las actualizaciones o responder a este ticket en cualquier momento usando el botón de abajo.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Ver Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Si necesita más ayuda, no dude en comunicarse a través de la aplicación.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'fr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Notification de Ticket de Support
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Bonjour,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Votre demande de support a été reçue avec succès par <strong>{company_name}</strong>.  
                    Notre équipe examinera le problème et vous répondra dès que possible.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID du Ticket :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Titre du Ticket :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Description :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Vous pouvez suivre les mises à jour ou répondre à ce ticket à tout moment en utilisant le bouton ci-dessous.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Voir le Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Si vous avez besoin d\une assistance supplémentaire, nhésitez pas à nous contacter via lapplication.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'it' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Notifica Ticket di Supporto
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Ciao,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    La tua richiesta di supporto è stata ricevuta con successo da <strong>{company_name}</strong>.  
                    Il nostro team esaminerà il problema e ti risponderà il prima possibile.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID Ticket :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Titolo Ticket :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Descrizione :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Puoi monitorare gli aggiornamenti o rispondere a questo ticket in qualsiasi momento utilizzando il pulsante qui sotto.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Visualizza Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Se hai bisogno di ulteriore assistenza, non esitare a contattarci tramite l\applicazione.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'ja' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • サポートチケット通知
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    こんにちは、
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    あなたのサポートリクエストは <strong>{company_name}</strong> に正常に送信されました。  
                    サポートチームが問題を確認し、できるだけ早くご連絡いたします。
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>チケットID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>チケットタイトル :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>説明 :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    以下のボタンを使用して、いつでもチケットの更新状況を確認したり返信することができます。
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    チケットを見る
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    さらにサポートが必要な場合は、アプリケーションからお気軽にお問い合わせください。
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'nl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Support Ticket Meldingsbericht
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Hallo,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Uw supportverzoek is succesvol ontvangen door <strong>{company_name}</strong>.  
                    Ons team zal het probleem bekijken en zo snel mogelijk contact met u opnemen.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Ticket Titel :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Beschrijving :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    U kunt updates volgen of op elk moment op dit ticket reageren via de onderstaande knop.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Bekijk Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Als u verdere hulp nodig heeft, neem dan gerust contact met ons op via de applicatie.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'pl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Powiadomienie o Zgłoszeniu Wsparcia
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Witaj,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Twoje zgłoszenie wsparcia zostało pomyślnie otrzymane przez <strong>{company_name}</strong>.  
                    Nasz zespół przeanalizuje problem i odpowie tak szybko, jak to możliwe.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID zgłoszenia :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Tytuł zgłoszenia :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Opis :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Możesz śledzić aktualizacje lub odpowiedzieć na to zgłoszenie w dowolnym momencie, korzystając z poniższego przycisku.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Zobacz zgłoszenie
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Jeśli potrzebujesz dodatkowej pomocy, skontaktuj się z nami za pośrednictwem aplikacji.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'ru' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Уведомление о Тикете Поддержки
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Здравствуйте,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Ваш запрос в службу поддержки был успешно получен компанией <strong>{company_name}</strong>.  
                    Наша команда рассмотрит проблему и ответит вам как можно скорее.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID тикета :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Название тикета :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Описание :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Вы можете отслеживать обновления или ответить на этот тикет в любое время, используя кнопку ниже.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Просмотреть тикет
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Если вам нужна дополнительная помощь, пожалуйста, свяжитесь с нами через приложение.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'pt' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Notificação de Ticket de Suporte
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Sua solicitação de suporte foi recebida com sucesso por <strong>{company_name}</strong>.  
                    Nossa equipe analisará o problema e responderá o mais rápido possível.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID do Ticket :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Título do Ticket :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Descrição :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Você pode acompanhar as atualizações ou responder a este ticket a qualquer momento usando o botão abaixo.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Ver Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Se precisar de mais assistência, sinta-se à vontade para entrar em contato através do aplicativo.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Notificação de Ticket de Suporte
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Olá,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Sua solicitação de suporte foi recebida com sucesso por <strong>{company_name}</strong>.  
                    Nossa equipe analisará o problema e responderá o mais rápido possível.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>ID do Ticket :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Título do Ticket :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Descrição :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Você pode acompanhar as atualizações ou responder a este ticket a qualquer momento usando o botão abaixo.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Ver Ticket
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Se precisar de mais assistência, sinta-se à vontade para entrar em contato através do aplicativo.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'he' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • התראה על כרטיס תמיכה
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    שלום,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    בקשת התמיכה שלך התקבלה בהצלחה על ידי <strong>{company_name}</strong>.  
                    הצוות שלנו יבדוק את הבעיה ויחזור אליך בהקדם האפשרי.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>מזהה כרטיס :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>כותרת הכרטיס :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>תיאור :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    תוכל לעקוב אחר עדכונים או להשיב לכרטיס זה בכל עת באמצעות הכפתור למטה.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    צפה בכרטיס
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    אם אתה זקוק לעזרה נוספת, אל תהסס לפנות אלינו דרך האפליקציה.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'tr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • Destek Talebi Bildirimi
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    Merhaba,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    Destek talebiniz <strong>{company_name}</strong> tarafından başarıyla alınmıştır.  
                    Ekibimiz sorunu inceleyecek ve en kısa sürede size geri dönüş yapacaktır.
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Talep ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Talep Başlığı :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>Açıklama :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    Aşağıdaki düğmeyi kullanarak bu talebin güncellemelerini takip edebilir veya istediğiniz zaman yanıt verebilirsiniz.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    Talebi Görüntüle
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    Daha fazla yardıma ihtiyacınız olursa uygulama üzerinden bizimle iletişime geçebilirsiniz.
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                    'zh' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

                    <div style="background:#4f46e5;color:#ffffff;padding:18px 25px;font-size:18px;font-weight:bold;">
                    {app_name} • 支持工单通知
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:15px;color:#333;margin-bottom:10px;">
                    您好，
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;">
                    您的支持请求已成功被 <strong>{company_name}</strong> 接收。  
                    我们的团队将会检查该问题，并尽快与您联系。
                    </p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:18px;margin-bottom:20px;">

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>工单 ID :</strong> {ticket_id}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>工单标题 :</strong> {ticket_name}
                    </p>

                    <p style="margin:6px 0;font-size:14px;color:#444;">
                    <strong>描述 :</strong> {ticket_description}
                    </p>

                    </div>

                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                    您可以通过下面的按钮随时查看更新或回复该工单。
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <a href="{ticket_url}" style="background:#4f46e5;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    查看工单
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;">
                    如果您需要进一步的帮助，请随时通过应用程序与我们联系。
                    </p>

                    </div>

                    <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:15px 25px;font-size:13px;color:#777;text-align:center;">
                    {company_name} • <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_name}</a>
                    </div>

                    </div>

                    </div>',
                ],
            ],
            'Proposal Sent' => [
                'subject' => 'Proposal Sent',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Proposal Number": "proposal_number",
                    "Customer Name": "sales_customer_name",
                    "Total Amount ": "total_amount",
                    "Discount Amount" : "discount_amount"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 إشعار عرض السعر
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 مرحبًا، {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    تم إنشاء عرض سعر جديد لك في <strong>{app_name}</strong>.  
                    يرجى مراجعة تفاصيل العرض أدناه.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 رقم عرض السعر:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 المبلغ الإجمالي:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ قيمة الخصم:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    إذا كان لديك أي أسئلة أو تحتاج إلى مساعدة إضافية، فلا تتردد في التواصل معنا في أي وقت.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    فتح التطبيق
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    شكرًا لك،<br>
                    مع التحية،<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'da' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Tilbudsmeddelelse
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Hej, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Et nyt tilbud er blevet oprettet til dig i <strong>{app_name}</strong>.  
                    Se venligst tilbudsoplysningerne nedenfor.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Tilbudsnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Samlet beløb:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Rabatbeløb:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Hvis du har spørgsmål eller har brug for yderligere hjælp, er du velkommen til at kontakte os når som helst.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Åbn applikation
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Tak,<br>
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'de' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Angebotsbenachrichtigung
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Hallo, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Ein neues Angebot wurde für Sie in <strong>{app_name}</strong> erstellt.  
                    Bitte überprüfen Sie die Angebotsdetails unten.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Angebotsnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Gesamtbetrag:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Rabattbetrag:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Wenn Sie Fragen haben oder weitere Unterstützung benötigen, können Sie uns jederzeit kontaktieren.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Anwendung öffnen
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Danke,<br>
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                   'en' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Proposal Notification
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Hi, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    A new proposal has been created for you in <strong>{app_name}</strong>.  
                    Please review the proposal details below.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Proposal Number:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Total Amount:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Discount Amount:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    If you have any questions or need further assistance, feel free to contact us anytime.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Open Application
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Thank You,<br>
                    Regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    
                    'es' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Notificación de Propuesta
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Hola, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Se ha creado una nueva propuesta para usted en <strong>{app_name}</strong>.  
                    Por favor revise los detalles de la propuesta a continuación.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número de Propuesta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Monto Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Monto de Descuento:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Si tiene alguna pregunta o necesita más ayuda, no dude en contactarnos en cualquier momento.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir Aplicación
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Gracias,<br>
                    Saludos,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'fr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Notification de Proposition
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Bonjour, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Une nouvelle proposition a été créée pour vous dans <strong>{app_name}</strong>.  
                    Veuillez consulter les détails de la proposition ci-dessous.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numéro de Proposition :</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Montant Total :</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Montant de la Remise :</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Si vous avez des questions ou avez besoin d\aide supplémentaire, n\hésitez pas à nous contacter à tout moment.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Ouvrir l\application
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Merci,<br>
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'he' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 הודעת הצעה
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 שלום, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    נוצרה עבורך הצעה חדשה ב-<strong>{app_name}</strong>.  
                    אנא בדוק את פרטי ההצעה למטה.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 מספר הצעה:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 סכום כולל:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ סכום הנחה:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    אם יש לך שאלות או שאתה זקוק לעזרה נוספת, אל תהסס לפנות אלינו בכל עת.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    פתח את האפליקציה
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    תודה,<br>
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Notifica di Proposta
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Ciao, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    È stata creata una nuova proposta per te in <strong>{app_name}</strong>.  
                    Consulta i dettagli della proposta qui sotto.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numero Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Importo Totale:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Importo Sconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Se hai domande o hai bisogno di ulteriore assistenza, non esitare a contattarci in qualsiasi momento.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Apri Applicazione
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Grazie,<br>
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                   'ja' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 提案通知
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 こんにちは、{sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    <strong>{app_name}</strong>で新しい提案が作成されました。  
                    以下の提案詳細をご確認ください。
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 提案番号:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 合計金額:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ 割引金額:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    ご質問や追加のサポートが必要な場合は、いつでもお気軽にお問い合わせください。
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    アプリを開く
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    ありがとうございます。<br>
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'nl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Voorstelmelding
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Hallo, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Er is een nieuw voorstel voor u aangemaakt in <strong>{app_name}</strong>.  
                    Bekijk hieronder de details van het voorstel.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Voorstelnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Totaalbedrag:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Kortingsbedrag:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Als u vragen heeft of verdere hulp nodig heeft, neem dan gerust op elk moment contact met ons op.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Applicatie openen
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Bedankt,<br>
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Powiadomienie o ofercie
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Witaj, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Nowa oferta została utworzona dla Ciebie w <strong>{app_name}</strong>.  
                    Prosimy o zapoznanie się z poniższymi szczegółami oferty.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numer oferty:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Całkowita kwota:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Kwota rabatu:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Jeśli masz jakiekolwiek pytania lub potrzebujesz dodatkowej pomocy, skontaktuj się z nami w dowolnym momencie.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Otwórz aplikację
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Dziękujemy,<br>
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',


                    'ru' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Уведомление о предложении
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Здравствуйте, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Для вас было создано новое предложение в <strong>{app_name}</strong>.  
                    Пожалуйста, ознакомьтесь с деталями предложения ниже.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Номер предложения:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Общая сумма:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Сумма скидки:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Если у вас есть вопросы или вам нужна дополнительная помощь, пожалуйста, свяжитесь с нами в любое время.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Открыть приложение
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Спасибо,<br>
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',


                    'pt' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Notificação de Proposta
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Olá, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Uma nova proposta foi criada para você em <strong>{app_name}</strong>.  
                    Por favor, revise os detalhes da proposta abaixo.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número da Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Valor Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Valor do Desconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Se você tiver alguma dúvida ou precisar de mais assistência, sinta-se à vontade para nos contatar a qualquer momento.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir Aplicação
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Obrigado,<br>
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Notificação de Proposta
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Olá, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Uma nova proposta foi criada para você em <strong>{app_name}</strong>.  
                    Por favor, revise os detalhes da proposta abaixo.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número da Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Valor Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ Valor do Desconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Se você tiver alguma dúvida ou precisar de mais assistência, sinta-se à vontade para nos contatar a qualquer momento.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir Aplicação
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Obrigado,<br>
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'tr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 Teklif Bildirimi
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 Merhaba, {sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    <strong>{app_name}</strong> içinde sizin için yeni bir teklif oluşturuldu.  
                    Lütfen aşağıdaki teklif detaylarını inceleyin.
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Teklif Numarası:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Toplam Tutar:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ İndirim Tutarı:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Herhangi bir sorunuz varsa veya ek yardıma ihtiyacınız olursa, bizimle istediğiniz zaman iletişime geçebilirsiniz.
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    Uygulamayı Aç
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Teşekkür ederiz,<br>
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(90deg,#6676ef,#7f8cff);padding:18px 25px;color:#ffffff;font-size:18px;font-weight:600;">
                    📄 提案通知
                    </div>

                    <div style="padding:25px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    👋 您好，{sales_customer_name}
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    系统已在 <strong>{app_name}</strong> 中为您创建了新的提案。  
                    请查看下面的提案详情。
                    </p>

                    <div style="background:#f6f7ff;border:1px solid #e6e8f0;padding:18px;margin:22px 0;border-radius:8px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 提案编号：</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 总金额：</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷️ 折扣金额：</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    如果您有任何问题或需要进一步的帮助，请随时与我们联系。
                    </p>

                    <div style="margin:25px 0;text-align:center;">
                    <a href="{app_url}" style="background:#6676ef;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-size:14px;font-weight:600;display:inline-block;">
                    打开应用
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    谢谢，<br>
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#777;margin-top:10px;">
                    <a href="{app_url}" style="color:#6676ef;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Proposal Approval' => [
                'subject' => 'Proposal Approval',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Proposal Number": "proposal_number",
                    "Customer Name": "sales_customer_name",
                    "Total Amount ": "total_amount",
                    "Discount Amount" : "discount_amount",
                    "Status": "status"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 إشعار حالة العرض
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    مرحباً {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    قام عميلك <strong>{sales_customer_name}</strong> بمراجعة العرض الذي تم إنشاؤه في <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    العرض: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    فيما يلي تفاصيل العرض:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 رقم العرض:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 اسم العميل:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 المبلغ الإجمالي:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 قيمة الخصم:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    يمكنك فتح التطبيق لعرض المزيد من التفاصيل أو اتخاذ إجراء إضافي إذا لزم الأمر.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    فتح التطبيق
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    شكراً لك،<br>
                    مع التحية،<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'da' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Forslagsstatus Meddelelse
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Hej {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Din kunde <strong>{sales_customer_name}</strong> har gennemgået forslaget oprettet i <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Forslag: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Her er detaljerne for forslaget:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Forslagsnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Kundenavn:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Samlet beløb:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Rabatbeløb:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Du kan åbne applikationen for at se flere detaljer eller foretage yderligere handlinger.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Åbn Applikation
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Tak,<br>
                    Med venlig hilsen,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'de' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Benachrichtigung zum Angebotsstatus
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Hallo {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Ihr Kunde <strong>{sales_customer_name}</strong> hat das in <strong>{app_name}</strong> erstellte Angebot überprüft.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Angebot: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Nachfolgend finden Sie die Details des Angebots:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Angebotsnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Kundenname:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Gesamtbetrag:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Rabattbetrag:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Sie können die Anwendung öffnen, um weitere Details anzuzeigen oder bei Bedarf weitere Maßnahmen zu ergreifen.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Anwendung öffnen
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Vielen Dank,<br>
                    Mit freundlichen Grüßen,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'en' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Proposal Status Notification
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Hello {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Your customer <strong>{sales_customer_name}</strong> has reviewed the proposal created in <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Proposal: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Below are the details of the proposal:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Proposal Number:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Customer Name:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Total Amount:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Discount Amount:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    You can open the application to view more details or take further action if required.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Open Application
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Thank You,<br>
                    Regards,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'es' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Notificación del Estado de la Propuesta
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Hola {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Su cliente <strong>{sales_customer_name}</strong> ha revisado la propuesta creada en <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Propuesta: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    A continuación se muestran los detalles de la propuesta:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número de Propuesta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nombre del Cliente:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Importe Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Importe de Descuento:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Puede abrir la aplicación para ver más detalles o realizar acciones adicionales si es necesario.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Abrir Aplicación
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Gracias,<br>
                    Saludos,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'fr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Notification du Statut de la Proposition
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Bonjour {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Votre client <strong>{sales_customer_name}</strong> a examiné la proposition créée dans <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Proposition : {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Vous trouverez ci-dessous les détails de la proposition :
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numéro de Proposition :</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nom du Client :</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Montant Total :</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Montant de la Remise :</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Vous pouvez ouvrir l\application pour voir plus de détails ou effectuer d\autres actions si nécessaire.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Ouvrir l’Application
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Merci,<br>
                    Cordialement,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'it' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Notifica dello Stato della Proposta
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Ciao {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Il tuo cliente <strong>{sales_customer_name}</strong> ha esaminato la proposta creata in <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Proposta: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Di seguito sono riportati i dettagli della proposta:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numero della Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nome Cliente:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Importo Totale:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Importo dello Sconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Puoi aprire l\'applicazione per visualizzare maggiori dettagli o eseguire ulteriori azioni se necessario.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Apri Applicazione
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Grazie,<br>
                    Cordiali saluti,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 提案ステータス通知
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    こんにちは {company_name} 様
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    お客様 <strong>{sales_customer_name}</strong> が <strong>{app_name}</strong> で作成された提案を確認しました。
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    提案: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    以下は提案の詳細です:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 提案番号:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 顧客名:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 合計金額:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 割引金額:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    アプリケーションを開いて詳細を確認したり、必要に応じて追加の操作を行うことができます。
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    アプリケーションを開く
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    ありがとうございます。<br>
                    よろしくお願いいたします。<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Voorstel Statusmelding
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Hallo {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Uw klant <strong>{sales_customer_name}</strong> heeft het voorstel dat is aangemaakt in <strong>{app_name}</strong> beoordeeld.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Voorstel: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Hieronder vindt u de details van het voorstel:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Voorstelnummer:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Klantnaam:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Totaalbedrag:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Kortingsbedrag:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    U kunt de applicatie openen om meer details te bekijken of verdere acties te ondernemen indien nodig.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Applicatie Openen
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Bedankt,<br>
                    Met vriendelijke groet,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Powiadomienie o Statusie Oferty
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Witaj {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Twój klient <strong>{sales_customer_name}</strong> sprawdził ofertę utworzoną w <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Oferta: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Poniżej znajdują się szczegóły oferty:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Numer Oferty:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nazwa Klienta:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Całkowita Kwota:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Kwota Rabatu:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Możesz otworzyć aplikację, aby zobaczyć więcej szczegółów lub podjąć dalsze działania w razie potrzeby.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Otwórz Aplikację
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Dziękujemy,<br>
                    Pozdrawiamy,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'pt' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Notificação de Status da Proposta
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Olá {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Seu cliente <strong>{sales_customer_name}</strong> revisou a proposta criada em <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Proposta: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Abaixo estão os detalhes da proposta:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número da Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nome do Cliente:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Valor Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Valor do Desconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Você pode abrir o aplicativo para ver mais detalhes ou tomar outras ações, se necessário.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Abrir Aplicação
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Obrigado,<br>
                    Atenciosamente,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Notificação de Status da Proposta
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Olá {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Seu cliente <strong>{sales_customer_name}</strong> revisou a proposta criada em <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Proposta: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Abaixo estão os detalhes da proposta:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Número da Proposta:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Nome do Cliente:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Valor Total:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Valor do Desconto:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Você pode abrir o aplicativo para ver mais detalhes ou tomar outras ações, se necessário.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Abrir Aplicação
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Obrigado,<br>
                    Atenciosamente,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'ru' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Уведомление о Статусе Предложения
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Здравствуйте, {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Ваш клиент <strong>{sales_customer_name}</strong> просмотрел предложение, созданное в <strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Предложение: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Ниже приведены детали предложения:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Номер Предложения:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Имя Клиента:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Общая Сумма:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 Сумма Скидки:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Вы можете открыть приложение, чтобы просмотреть дополнительные детали или выполнить дальнейшие действия при необходимости.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Открыть Приложение
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Спасибо,<br>
                    С уважением,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'he' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 התראה על סטטוס ההצעה
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    שלום {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    הלקוח שלך <strong>{sales_customer_name}</strong> בדק את ההצעה שנוצרה ב־<strong>{app_name}</strong>.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    הצעה: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    להלן פרטי ההצעה:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 מספר הצעה:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 שם הלקוח:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 סכום כולל:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 סכום הנחה:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    באפשרותך לפתוח את האפליקציה כדי לצפות בפרטים נוספים או לבצע פעולות נוספות במידת הצורך.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    פתח את האפליקציה
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    תודה,<br>
                    בברכה,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                   'tr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 Teklif Durumu Bildirimi
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    Merhaba {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Müşteriniz <strong>{sales_customer_name}</strong>, <strong>{app_name}</strong> içinde oluşturulan teklifi incelemiştir.
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    Teklif: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Aşağıda teklifin detaylarını bulabilirsiniz:
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 Teklif Numarası:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 Müşteri Adı:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 Toplam Tutar:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 İndirim Tutarı:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    Daha fazla ayrıntı görmek veya gerekli işlemleri yapmak için uygulamayı açabilirsiniz.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    Uygulamayı Aç
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    Teşekkür ederiz,<br>
                    Saygılarımızla,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px;">

                    <div style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 25px rgba(0,0,0,0.06);">

                    <div style="background:linear-gradient(90deg,#5b6cff,#7f8cff);padding:20px 28px;color:#ffffff;font-size:20px;font-weight:600;">
                    📊 提案状态通知
                    </div>

                    <div style="padding:28px;">

                    <p style="font-size:20px;color:#333;margin-bottom:15px;font-weight:600;">
                    您好 {company_name},
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    您的客户 <strong>{sales_customer_name}</strong> 已查看在 <strong>{app_name}</strong> 中创建的提案。
                    </p>

                    <div style="text-align:center;margin:25px 0;">
                    <span style="display:inline-block;background:#f1f3ff;color:#444;border:1px solid #dcdff5;padding:10px 22px;border-radius:25px;font-size:14px;font-weight:600;">
                    提案: {status}
                    </span>
                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    以下是提案的详细信息：
                    </p>

                    <div style="background:#f7f8ff;border:1px solid #e6e8f0;border-radius:10px;padding:20px;margin-top:18px;">

                    <p style="margin:0;font-size:14px;color:#333;">
                    <strong>📑 提案编号:</strong> {proposal_number}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>👤 客户名称:</strong> {sales_customer_name}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>💰 总金额:</strong> {total_amount}
                    </p>

                    <p style="margin-top:10px;font-size:14px;color:#333;">
                    <strong>🏷 折扣金额:</strong> {discount_amount}
                    </p>

                    </div>

                    <p style="font-size:15px;color:#555;line-height:1.7;margin-top:20px;">
                    您可以打开应用程序查看更多详细信息或根据需要采取进一步操作。
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#5b6cff;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    打开应用程序
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:25px 0;">

                    <p style="font-size:14px;color:#333;">
                    谢谢，<br>
                    此致敬礼，<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:8px;">
                    <a href="{app_url}" style="color:#5b6cff;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Plan Purchase' => [
                'subject' => 'Plan Purchase',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Plan Name": "plan_name",
                    "Plan Price": "plan_price",
                    "Plan Duration": "plan_duration"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 إشعار شراء خطة جديدة
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    مرحباً مدير النظام،
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    أخبار رائعة! قامت شركة بالاشتراك بنجاح في خطة جديدة على <strong>{app_name}</strong>. فيما يلي تفاصيل عملية الشراء.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 اسم الشركة:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 اسم الخطة:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 سعر الخطة:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ مدة الخطة:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    تم تسجيل عملية الشراء هذه بنجاح في النظام. يمكنك مراجعة حساب الشركة وإدارة تفاصيل الاشتراك من لوحة الإدارة.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 5px 14px rgba(0,0,0,0.12);">
                    فتح لوحة الإدارة
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    شكراً لك،<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'da' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Ny plan købsmeddelelse
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Hej Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Gode nyheder! En virksomhed har med succes abonneret på en ny plan på <strong>{app_name}</strong>. Nedenfor er detaljerne for købet.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Firmanavn:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Plan navn:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Plan pris:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Plan varighed:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    Dette køb er blevet registreret i systemet. Du kan gennemgå virksomhedens konto og administrere abonnementsdetaljer fra adminpanelet.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Åbn adminpanel
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Tak,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'de' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">
                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Neue Plan-Kaufbenachrichtigung
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Hallo Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Großartige Neuigkeiten! Ein Unternehmen hat erfolgreich einen neuen Plan auf <strong>{app_name}</strong> abonniert. Unten finden Sie die Details des Kaufs.
                    </p>

                    <p><strong>🏢 Firmenname:</strong> {company_name}</p>
                    <p><strong>📦 Planname:</strong> {plan_name}</p>
                    <p><strong>💳 Planpreis:</strong> {plan_price}</p>
                    <p><strong>⏳ Plandauer:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Admin-Panel öffnen
                    </a>
                    </div>

                    <hr>

                    <p>Vielen Dank,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'en' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 New Plan Purchase Notification
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Hello Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Great news! A company has successfully subscribed to a new plan on <strong>{app_name}</strong>. Below are the details of the purchase.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Company Name:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Plan Name:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Plan Price:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Plan Duration:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    This purchase has been recorded successfully in the system. You can review the company account and manage subscription details from the admin panel.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;box-shadow:0 5px 14px rgba(0,0,0,0.12);">
                    Open Admin Panel
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Thank You,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'es' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Notificación de compra de nuevo plan
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Hola Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    ¡Buenas noticias! Una empresa se ha suscrito con éxito a un nuevo plan en <strong>{app_name}</strong>. A continuación se muestran los detalles de la compra.
                    </p>

                    <p><strong>🏢 Nombre de la empresa:</strong> {company_name}</p>
                    <p><strong>📦 Nombre del plan:</strong> {plan_name}</p>
                    <p><strong>💳 Precio del plan:</strong> {plan_price}</p>
                    <p><strong>⏳ Duración del plan:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Abrir panel de administración
                    </a>
                    </div>

                    <hr>

                    <p>Gracias,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'fr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Notification d\'achat d\'un nouveau plan
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Bonjour Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Excellente nouvelle ! Une entreprise s\'est abonnée avec succès à un nouveau plan sur <strong>{app_name}</strong>. Voici les détails de l\'achat.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Nom de l\'entreprise :</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Nom du plan :</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Prix du plan :</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Durée du plan :</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    Cet achat a été enregistré avec succès dans le système. Vous pouvez consulter le compte de l\'entreprise et gérer les détails de l\'abonnement depuis le panneau d\'administration.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ouvrir le panneau d\'administration
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Merci,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'he' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 התראה על רכישת תוכנית חדשה
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    שלום מנהל מערכת,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    חדשות טובות! חברה נרשמה בהצלחה לתוכנית חדשה ב-<strong>{app_name}</strong>. להלן פרטי הרכישה.
                    </p>

                    <p><strong>🏢 שם החברה:</strong> {company_name}</p>
                    <p><strong>📦 שם התוכנית:</strong> {plan_name}</p>
                    <p><strong>💳 מחיר התוכנית:</strong> {plan_price}</p>
                    <p><strong>⏳ משך התוכנית:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    פתח את פאנל הניהול
                    </a>
                    </div>

                    <hr>

                    <p>תודה,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Notifica di acquisto di un nuovo piano
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Ciao Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Ottime notizie! Un\azienda ha sottoscritto con successo un nuovo piano su <strong>{app_name}</strong>. Di seguito i dettagli dell\\acquisto.
                    </p>

                    <p><strong>🏢 Nome azienda:</strong> {company_name}</p>
                    <p><strong>📦 Nome del piano:</strong> {plan_name}</p>
                    <p><strong>💳 Prezzo del piano:</strong> {plan_price}</p>
                    <p><strong>⏳ Durata del piano:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Apri pannello admin
                    </a>
                    </div>

                    <hr>

                    <p>Grazie,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 新しいプラン購入のお知らせ
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    スーパー管理者様、
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    お知らせです！企業が <strong>{app_name}</strong> で新しいプランを正常に購入しました。以下に購入の詳細を示します。
                    </p>

                    <p><strong>🏢 会社名:</strong> {company_name}</p>
                    <p><strong>📦 プラン名:</strong> {plan_name}</p>
                    <p><strong>💳 プラン価格:</strong> {plan_price}</p>
                    <p><strong>⏳ プラン期間:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    管理パネルを開く
                    </a>
                    </div>

                    <hr>

                    <p>ありがとうございます、<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Nieuwe plan aankoopmelding
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Hallo Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Goed nieuws! Een bedrijf heeft succesvol een nieuw plan geabonneerd op <strong>{app_name}</strong>. Hieronder staan de details van de aankoop.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Bedrijfsnaam:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Plan naam:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Plan prijs:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Plan duur:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    Deze aankoop is succesvol geregistreerd in het systeem. U kunt het bedrijfsaccount bekijken en abonnementsdetails beheren via het adminpaneel.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Adminpaneel openen
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Bedankt,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Powiadomienie o zakupie nowego planu
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Witaj Super Adminie,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Świetna wiadomość! Firma pomyślnie zasubskrybowała nowy plan w <strong>{app_name}</strong>. Poniżej znajdują się szczegóły zakupu.
                    </p>

                    <p><strong>🏢 Nazwa firmy:</strong> {company_name}</p>
                    <p><strong>📦 Nazwa planu:</strong> {plan_name}</p>
                    <p><strong>💳 Cena planu:</strong> {plan_price}</p>
                    <p><strong>⏳ Czas trwania planu:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Otwórz panel administratora
                    </a>
                    </div>

                    <hr>

                    <p>Dziękujemy,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                                        'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Notificação de compra de novo plano
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Olá Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Boas notícias! Uma empresa assinou com sucesso um novo plano no <strong>{app_name}</strong>. Abaixo estão os detalhes da compra.
                    </p>

                    <p><strong>🏢 Nome da empresa:</strong> {company_name}</p>
                    <p><strong>📦 Nome do plano:</strong> {plan_name}</p>
                    <p><strong>💳 Preço do plano:</strong> {plan_price}</p>
                    <p><strong>⏳ Duração do plano:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Abrir painel admin
                    </a>
                    </div>

                    <hr>

                    <p>Obrigado,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Notificação de compra de novo plano
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Olá Super Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Boas notícias! Uma empresa assinou com sucesso um novo plano no <strong>{app_name}</strong>. Abaixo estão os detalhes da compra.
                    </p>

                    <p><strong>🏢 Nome da empresa:</strong> {company_name}</p>
                    <p><strong>📦 Nome do plano:</strong> {plan_name}</p>
                    <p><strong>💳 Preço do plano:</strong> {plan_price}</p>
                    <p><strong>⏳ Duração do plano:</strong> {plan_duration}</p>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;">
                    Abrir painel admin
                    </a>
                    </div>

                    <hr>

                    <p>Obrigado,<br><strong>{app_name}</strong></p>

                    <p>
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'ru' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Уведомление о покупке нового плана
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Здравствуйте, Супер Администратор,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Отличные новости! Компания успешно оформила подписку на новый план в <strong>{app_name}</strong>. Ниже приведены детали покупки.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Название компании:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Название плана:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Цена плана:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Срок действия плана:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    Эта покупка была успешно зарегистрирована в системе. Вы можете просмотреть аккаунт компании и управлять деталями подписки через панель администратора.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Открыть панель администратора
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Спасибо,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'tr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 Yeni Plan Satın Alma Bildirimi
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    Merhaba Süper Admin,
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    Harika haber! Bir şirket <strong>{app_name}</strong> üzerinde yeni bir plana başarıyla abone oldu. Satın alma detayları aşağıda verilmiştir.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 Şirket Adı:</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 Plan Adı:</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 Plan Fiyatı:</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ Plan Süresi:</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    Bu satın alma işlemi sistemde başarıyla kaydedildi. Şirket hesabını inceleyebilir ve abonelik detaylarını yönetim panelinden yönetebilirsiniz.
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Yönetim Panelini Aç
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    Teşekkürler,<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                    'zh' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 12px 30px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#4f46e5,#6366f1);padding:22px 30px;color:#ffffff;font-size:22px;font-weight:600;">
                    🚀 新套餐购买通知
                    </div>

                    <div style="padding:30px;">

                    <p style="font-size:20px;color:#222;margin-bottom:12px;font-weight:600;">
                    您好，超级管理员，
                    </p>

                    <p style="font-size:15px;color:#555;line-height:1.7;">
                    好消息！某公司已成功在 <strong>{app_name}</strong> 上订阅了一个新套餐。以下是购买详情。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin-top:22px;">

                    <p style="margin:0;font-size:15px;color:#333;">
                    <strong>🏢 公司名称：</strong> {company_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>📦 套餐名称：</strong> {plan_name}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>💳 套餐价格：</strong> {plan_price}
                    </p>

                    <p style="margin-top:10px;font-size:15px;color:#333;">
                    <strong>⏳ 套餐时长：</strong> {plan_duration}
                    </p>

                    </div>

                    <div style="margin-top:26px;padding:18px;background:#eef2ff;border-radius:10px;border:1px dashed #c7d2fe;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.6;">
                    该购买已成功记录在系统中。您可以在管理面板中查看公司账户并管理订阅详情。
                    </p>
                    </div>

                    <div style="text-align:center;margin-top:30px;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    打开管理面板
                    </a>
                    </div>

                    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                    <p style="font-size:14px;color:#444;">
                    谢谢，<br>
                    <strong>{app_name}</strong>
                    </p>

                    <p style="font-size:13px;color:#888;margin-top:6px;">
                    <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
              
        ];

        foreach($emailTemplate as $eTemp)
        {
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','general')->exists();
            if(!$table)
            {
                $emailtemplate=  EmailTemplate::create(
                    [
                        'name' => $eTemp,
                        'from' =>  !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas ERP',
                        'module_name' => 'general',
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
