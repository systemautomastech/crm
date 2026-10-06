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
        $admin = User::where('type', 'superadmin')->first();

        $emailTemplate = [
            'New User',
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

        foreach ($emailTemplate as $eTemp) {
            $table = EmailTemplate::where('name', $eTemp)->where('module_name', 'general')->exists();
            if (!$table) {
                $emailtemplate = EmailTemplate::create(
                    [
                        'name' => $eTemp,
                        'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas CRM',
                        'module_name' => 'general',
                        'created_by' => $admin->id,
                        'creator_id' => $admin->id,
                    ]
                );
                foreach ($defaultTemplate[$eTemp]['lang'] as $lang => $content) {
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
