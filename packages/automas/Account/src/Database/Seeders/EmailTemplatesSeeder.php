<?php

namespace Automas\Account\Database\Seeders;

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
            'Customer Payment',
            'Vendor Payment',
            'Debit Note Approval',
            'Credit Note Approval',
        ];
        $defaultTemplate = [
            'Customer Payment' => [
                'subject' => 'Customer Payment Cleared',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Payment Number ": "payment_number",
                    "Payment Date": "payment_date",
                    "Customer Name": "customer_name",
                    "Payment Amount": "payment_amount",
                    "Reference Number": "reference_number"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">تم استلام الدفعة</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    مرحباً {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    شكراً لدفعتك. لقد استلمنا دفعتك بنجاح، ويمكنك الاطلاع على التفاصيل أدناه.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    تفاصيل الدفع
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">رقم الدفع</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">تاريخ الدفع</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">مبلغ الدفع</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">رقم المرجع</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    إذا كان لديك أي استفسار بخصوص هذه الدفعة، فلا تتردد في التواصل معنا.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    عرض في {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    مع أطيب التحيات،<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Betaling modtaget</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hej {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Tak for din betaling. Vi har modtaget din betaling, og detaljerne kan ses nedenfor.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Betalingsoplysninger
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Betalingsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsdato</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsbeløb</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referencenummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Hvis du har spørgsmål vedrørende denne betaling, er du velkommen til at kontakte os.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Se i {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Zahlung erhalten</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Vielen Dank für Ihre Zahlung. Wir haben Ihre Zahlung erfolgreich erhalten. Die Details finden Sie unten.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Zahlungsdetails
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Zahlungsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Zahlungsdatum</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Zahlungsbetrag</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referenznummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Wenn Sie Fragen zu dieser Zahlung haben, können Sie uns gerne kontaktieren.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    In {app_name} ansehen
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Payment Received</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hello {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Thank you for your payment. We have successfully received your payment and the details are listed below.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Payment Details
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Payment Number</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Payment Date</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Payment Amount</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Reference Number</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    If you have any questions regarding this payment, please feel free to contact us.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    View in {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Best regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pago recibido</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hola {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Gracias por su pago. Hemos recibido su pago correctamente y los detalles se muestran a continuación.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalles del Pago
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número de Pago</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Fecha de Pago</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Monto del Pago</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de Referencia</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Si tiene alguna pregunta sobre este pago, no dude en contactarnos.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ver en {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saludos cordiales,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Paiement reçu</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Bonjour {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Merci pour votre paiement. Nous avons bien reçu votre paiement et les détails sont indiqués ci-dessous.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Détails du paiement
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numéro de paiement</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Date de paiement</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Montant du paiement</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numéro de référence</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Si vous avez des questions concernant ce paiement, n\'hésitez pas à nous contacter.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Voir dans {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento ricevuto</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Ciao {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Grazie per il tuo pagamento. Abbiamo ricevuto con successo il tuo pagamento e i dettagli sono riportati di seguito.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Dettagli del pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numero pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Importo pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numero di riferimento</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Se hai domande riguardo a questo pagamento, non esitare a contattarci.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Visualizza in {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">お支払いを受け取りました</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    こんにちは {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    お支払いありがとうございます。お支払いを正常に受け取りました。詳細は以下の通りです。
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    支払い詳細
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">支払い番号</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">支払い日</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">支払い金額</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">参照番号</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    この支払いについてご質問がある場合は、お気軽にお問い合わせください。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    {app_name}で表示
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Betaling ontvangen</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Bedankt voor uw betaling. Wij hebben uw betaling succesvol ontvangen. Hieronder vindt u de details.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Betalingsgegevens
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Betalingsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betaaldatum</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsbedrag</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referentienummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Als u vragen heeft over deze betaling, neem dan gerust contact met ons op.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Bekijken in {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Płatność otrzymana</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Witaj {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Dziękujemy za Twoją płatność. Otrzymaliśmy Twoją płatność pomyślnie. Szczegóły znajdują się poniżej.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Szczegóły płatności
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numer płatności</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data płatności</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Kwota płatności</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numer referencyjny</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Jeśli masz jakiekolwiek pytania dotyczące tej płatności, skontaktuj się z nami.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Zobacz w {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Платеж получен</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Здравствуйте {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Спасибо за ваш платеж. Мы успешно получили ваш платеж. Подробности указаны ниже.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Детали платежа
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Номер платежа</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Дата платежа</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Сумма платежа</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Номер ссылки</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Если у вас есть вопросы по этому платежу, пожалуйста, свяжитесь с нами.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Открыть в {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento recebido</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Obrigado pelo seu pagamento. Recebemos seu pagamento com sucesso. Os detalhes estão abaixo.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalhes do pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número do pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data do pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Valor do pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de referência</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Se você tiver alguma dúvida sobre este pagamento, sinta-se à vontade para entrar em contato conosco.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ver em {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento recebido</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Obrigado pelo seu pagamento. Recebemos seu pagamento com sucesso. Os detalhes estão abaixo.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalhes do pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número do pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data do pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Valor do pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de referência</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Se você tiver alguma dúvida sobre este pagamento, sinta-se à vontade para entrar em contato conosco.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ver em {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Ödeme Alındı</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Merhaba {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Ödemeniz için teşekkür ederiz. Ödemenizi başarıyla aldık. Detaylar aşağıda yer almaktadır.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Ödeme Detayları
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Ödeme Numarası</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Ödeme Tarihi</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Ödeme Tutarı</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referans Numarası</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Bu ödeme hakkında herhangi bir sorunuz varsa, lütfen bizimle iletişime geçmekten çekinmeyin.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    {app_name} içinde görüntüle
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">已收到付款</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    您好 {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    感谢您的付款。我们已成功收到您的付款，详细信息如下所示。
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    付款详情
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">付款编号</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">付款日期</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">付款金额</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">参考编号</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    如果您对本次付款有任何疑问，请随时与我们联系。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    在 {app_name} 中查看
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">התשלום התקבל</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    שלום {customer_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    תודה על התשלום שלך. קיבלנו את התשלום בהצלחה. הפרטים מופיעים למטה.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    פרטי התשלום
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">מספר תשלום</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">תאריך תשלום</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">סכום התשלום</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">מספר אסמכתא</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    אם יש לך שאלות לגבי תשלום זה, אל תהסס לפנות אלינו.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    צפה ב-{app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Vendor Payment' => [
                'subject' => 'Vendor Payment Cleared',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Payment Number ": "payment_number",
                    "Payment Date": "payment_date",
                    "Vendor Name": "vendor_name",
                    "Payment Amount": "payment_amount",
                    "Reference Number": "reference_number"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">تمت تسوية دفعة المورد</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    مرحباً {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    نود إعلامك بأنه تم معالجة دفعتك بنجاح من قبل <strong>{company_name}</strong>. تفاصيل الدفع موضحة أدناه.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    تفاصيل الدفع
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">رقم الدفع</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">تاريخ الدفع</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">مبلغ الدفع</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">الرقم المرجعي</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    يمكنك عرض المزيد من التفاصيل حول هذه الدفعة من خلال تسجيل الدخول إلى حسابك.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    فتح {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    إذا كان لديك أي استفسار بخصوص هذه الدفعة، فلا تتردد في التواصل معنا.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    مع أطيب التحيات,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Leverandørbetaling gennemført</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hej {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Dette er for at informere dig om, at din betaling er blevet behandlet med succes af <strong>{company_name}</strong>. Betalingsoplysningerne er angivet nedenfor.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Betalingsoplysninger
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Betalingsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsdato</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsbeløb</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referencenummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Du kan se flere detaljer om denne betaling ved at logge ind på din konto.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Åbn {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Hvis du har spørgsmål vedrørende denne betaling, er du velkommen til at kontakte os.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Med venlig hilsen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Lieferantenzahlung abgeschlossen</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Wir möchten Sie darüber informieren, dass Ihre Zahlung erfolgreich von <strong>{company_name}</strong> verarbeitet wurde. Die Zahlungsdetails finden Sie unten.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Zahlungsdetails
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Zahlungsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Zahlungsdatum</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Zahlungsbetrag</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referenznummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Sie können weitere Details zu dieser Zahlung einsehen, indem Sie sich in Ihr Konto einloggen.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    {app_name} öffnen
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Wenn Sie Fragen zu dieser Zahlung haben, können Sie uns gerne kontaktieren.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Vendor Payment Cleared</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hello {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    This is to inform you that your payment has been successfully processed by <strong>{company_name}</strong>. The payment details are provided below.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Payment Details
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Payment Number</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Payment Date</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Payment Amount</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Reference Number</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    You can view more details about this payment by logging into your account.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Open {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    If you have any questions regarding this payment, please feel free to contact us.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Best regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pago al proveedor completado</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hola {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Le informamos que su pago ha sido procesado con éxito por <strong>{company_name}</strong>. Los detalles del pago se muestran a continuación.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalles del pago
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número de pago</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Fecha de pago</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Monto del pago</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de referencia</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Puede ver más detalles sobre este pago iniciando sesión en su cuenta.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Si tiene alguna pregunta sobre este pago, no dude en contactarnos.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saludos cordiales,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Paiement du fournisseur effectué</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Bonjour {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Nous vous informons que votre paiement a été traité avec succès par <strong>{company_name}</strong>. Les détails du paiement sont indiqués ci-dessous.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Détails du paiement
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numéro de paiement</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Date de paiement</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Montant du paiement</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numéro de référence</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Vous pouvez consulter plus de détails sur ce paiement en vous connectant à votre compte.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Ouvrir {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Si vous avez des questions concernant ce paiement, n’hésitez pas à nous contacter.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento al fornitore completato</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Ciao {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Ti informiamo che il tuo pagamento è stato elaborato con successo da <strong>{company_name}</strong>. I dettagli del pagamento sono riportati di seguito.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Dettagli del pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numero pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Importo pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numero di riferimento</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Puoi visualizzare maggiori dettagli su questo pagamento accedendo al tuo account.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Apri {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se hai domande riguardo a questo pagamento, non esitare a contattarci.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">ベンダー支払いが完了しました</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    こんにちは {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    <strong>{company_name}</strong> により、あなたへの支払いが正常に処理されたことをお知らせします。支払いの詳細は以下の通りです。
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    支払い詳細
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">支払い番号</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">支払い日</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">支払い金額</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">参照番号</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    この支払いの詳細は、アカウントにログインして確認できます。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    {app_name} を開く
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    この支払いについてご質問がある場合は、お気軽にお問い合わせください。
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Leveranciersbetaling voltooid</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Hallo {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Hierbij informeren wij u dat uw betaling succesvol is verwerkt door <strong>{company_name}</strong>. De betalingsgegevens vindt u hieronder.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Betalingsgegevens
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Betalingsnummer</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsdatum</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Betalingsbedrag</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referentienummer</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    U kunt meer details over deze betaling bekijken door in te loggen op uw account.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Open {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Als u vragen heeft over deze betaling, neem dan gerust contact met ons op.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Płatność dla dostawcy zrealizowana</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Witaj {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Informujemy, że Twoja płatność została pomyślnie przetworzona przez <strong>{company_name}</strong>. Szczegóły płatności znajdują się poniżej.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Szczegóły płatności
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Numer płatności</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data płatności</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Kwota płatności</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Numer referencyjny</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Możesz zobaczyć więcej szczegółów dotyczących tej płatności, logując się na swoje konto.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Otwórz {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Jeśli masz jakiekolwiek pytania dotyczące tej płatności, skontaktuj się z nami.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Платеж поставщику выполнен</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Здравствуйте {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Сообщаем вам, что ваш платеж был успешно обработан компанией <strong>{company_name}</strong>. Детали платежа приведены ниже.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Детали платежа
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Номер платежа</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Дата платежа</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Сумма платежа</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Номер ссылки</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Вы можете просмотреть больше информации об этом платеже, войдя в свой аккаунт.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Открыть {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Если у вас есть вопросы по этому платежу, пожалуйста, свяжитесь с нами.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento ao fornecedor concluído</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Informamos que seu pagamento foi processado com sucesso por <strong>{company_name}</strong>. Os detalhes do pagamento estão abaixo.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalhes do pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número do pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data do pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Valor do pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de referência</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Você pode ver mais detalhes sobre este pagamento acessando sua conta.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se tiver alguma dúvida sobre este pagamento, não hesite em nos contatar.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Pagamento ao fornecedor concluído</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Olá {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Informamos que seu pagamento foi processado com sucesso por <strong>{company_name}</strong>. Os detalhes do pagamento estão abaixo.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Detalhes do pagamento
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Número do pagamento</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Data do pagamento</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Valor do pagamento</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Número de referência</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Você pode ver mais detalhes sobre este pagamento acessando sua conta.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    Abrir {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Se tiver alguma dúvida sobre este pagamento, não hesite em nos contatar.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">Tedarikçi Ödemesi Tamamlandı</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    Merhaba {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    Ödemenizin <strong>{company_name}</strong> tarafından başarıyla işlendiğini bildirmekten memnuniyet duyarız. Ödeme detayları aşağıda verilmiştir.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    Ödeme Detayları
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">Ödeme Numarası</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Ödeme Tarihi</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Ödeme Tutarı</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">Referans Numarası</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    Bu ödeme hakkında daha fazla detayı hesabınıza giriş yaparak görüntüleyebilirsiniz.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    {app_name} Aç
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    Bu ödeme hakkında herhangi bir sorunuz varsa lütfen bizimle iletişime geçin.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">供应商付款已完成</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    您好 {vendor_name}，
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    我们很高兴通知您，您的付款已由 <strong>{company_name}</strong> 成功处理。付款详情如下。
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    付款详情
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">付款编号</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">付款日期</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">付款金额</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">参考编号</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    您可以登录您的账户查看此付款的更多详细信息。
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    打开 {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    如果您对该付款有任何疑问，请随时与我们联系。
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7f2;box-shadow:0 8px 22px rgba(0,0,0,0.06);">

                    <div style="background:#16a34a;color:#ffffff;padding:26px;text-align:center;">
                    <h1 style="margin:0;font-size:22px;">תשלום לספק הושלם</h1>
                    <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">{app_name}</p>
                    </div>

                    <div style="padding:34px;">

                    <p style="font-size:15px;color:#333;margin:0 0 18px;">
                    שלום {vendor_name},
                    </p>

                    <p style="font-size:15px;color:#444;line-height:1.6;margin-bottom:22px;">
                    ברצוננו להודיע לך כי התשלום שלך עובד בהצלחה על ידי <strong>{company_name}</strong>. פרטי התשלום מופיעים להלן.
                    </p>

                    <div style="background:#f7fff9;border:1px solid #dcfce7;border-radius:10px;padding:22px;margin-bottom:28px;">

                    <h3 style="margin-top:0;margin-bottom:18px;color:#16a34a;font-size:18px;">
                    פרטי התשלום
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#444;">

                    <tr>
                    <td style="padding:8px 0;width:45%;font-weight:600;">מספר תשלום</td>
                    <td style="padding:8px 0;">{payment_number}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">תאריך תשלום</td>
                    <td style="padding:8px 0;">{payment_date}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">סכום התשלום</td>
                    <td style="padding:8px 0;">{payment_amount}</td>
                    </tr>

                    <tr>
                    <td style="padding:8px 0;font-weight:600;">מספר אסמכתא</td>
                    <td style="padding:8px 0;">{reference_number}</td>
                    </tr>

                    </table>

                    </div>

                    <p style="font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;">
                    ניתן לצפות בפרטים נוספים על תשלום זה על ידי כניסה לחשבון שלך.
                    </p>

                    <div style="text-align:center;margin-bottom:30px;">
                    <a href="{app_url}" 
                    style="background:#16a34a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:600;display:inline-block;">
                    פתח את {app_name}
                    </a>
                    </div>

                    <p style="font-size:14px;color:#555;">
                    אם יש לך שאלות בנוגע לתשלום זה, אנא אל תהסס לפנות אלינו.
                    </p>

                    <p style="font-size:14px;color:#555;margin-top:26px;">
                    בברכה,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Debit Note Approval' => [
                'subject' => 'Debit Note Approved',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Debit Note Number": "debit_note_number",
                    "Debit Note Date": "debit_note_date",
                    "Vendor Name": "vendor_name",
                    "Invoice Number": "invoice_number",
                    "Return Number": "return_number",
                    "Reason": "reason",
                    "Total Amount": "total_amount"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    تمت الموافقة على إشعار الخصم
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>عزيزي <strong>{vendor_name}</strong>,</p>

                    <p>نود إعلامك بأنه تمت <strong style="color:#059669;">الموافقة بنجاح</strong> على إشعار الخصم الذي قدمته. يرجى الاطلاع على التفاصيل أدناه للرجوع إليها.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>رقم إشعار الخصم:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>تاريخ إشعار الخصم:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>رقم الفاتورة:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>رقم الإرجاع:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>السبب:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>المبلغ الإجمالي:</strong> {total_amount}</p>

                    </div>

                    <p>إذا كنت بحاجة إلى أي توضيح إضافي بخصوص إشعار الخصم هذا، فلا تتردد في التواصل معنا.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">فتح {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">شكرًا لتعاونك المستمر مع <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">مع أطيب التحيات,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    هذه رسالة إشعار تلقائية من {app_name}.
                    </div>

                    </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Debitnota godkendt
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Kære <strong>{vendor_name}</strong>,</p>

                    <p>Vi vil gerne informere dig om, at din indsendte debitnota er blevet <strong style="color:#059669;">godkendt</strong>. Se venligst detaljerne nedenfor.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Debitnota nummer:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Dato for debitnota:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Fakturanummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Returneringsnummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Årsag:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Samlet beløb:</strong> {total_amount}</p>

                    </div>

                    <p>Hvis du har brug for yderligere oplysninger om denne debitnota, er du velkommen til at kontakte os.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Åbn {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Tak for dit fortsatte samarbejde med <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Med venlig hilsen,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Dette er en automatisk meddelelse fra {app_name}.
                    </div>

                    </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Debitnota godkendt
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Kære <strong>{vendor_name}</strong>,</p>

                    <p>Vi vil gerne informere dig om, at din indsendte debitnota er blevet <strong style="color:#059669;">godkendt</strong>. Se venligst detaljerne nedenfor.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Debitnota nummer:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Dato for debitnota:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Fakturanummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Returneringsnummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Årsag:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Samlet beløb:</strong> {total_amount}</p>

                    </div>

                    <p>Hvis du har brug for yderligere oplysninger om denne debitnota, er du velkommen til at kontakte os.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Åbn {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Tak for dit fortsatte samarbejde med <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Med venlig hilsen,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Dette er en automatisk meddelelse fra {app_name}.
                    </div>

                    </div>

                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Debit Note Approved
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Dear <strong>{vendor_name}</strong>,</p>

                    <p>We would like to inform you that your submitted debit note has been <strong style="color:#059669;">successfully approved</strong>. Please find the details below for your reference.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Debit Note Number:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Debit Note Date:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Invoice Number:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Return Number:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Reason:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Total Amount:</strong> {total_amount}</p>

                    </div>

                    <p>If you need any further clarification regarding this debit note, please feel free to contact us.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Open {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Thank you for your continued partnership with <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Warm regards,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    This is an automated notification from {app_name}.
                    </div>
                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Débito Aprobada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Estimado/a <strong>{vendor_name}</strong>,</p>

                    <p>Nos gustaría informarle que su nota de débito enviada ha sido <strong style="color:#059669;">aprobada con éxito</strong>. Por favor, consulte los detalles a continuación.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número de nota de débito:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Fecha de la nota de débito:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número de factura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de devolución:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Monto total:</strong> {total_amount}</p>

                    </div>

                    <p>Si necesita más información sobre esta nota de débito, no dude en ponerse en contacto con nosotros.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Gracias por su continua colaboración con <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Saludos cordiales,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Esta es una notificación automática de {app_name}.
                    </div>

                    </div>

                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Note de débit approuvée
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Cher/Chère <strong>{vendor_name}</strong>,</p>

                    <p>Nous souhaitons vous informer que votre note de débit soumise a été <strong style="color:#059669;">approuvée avec succès</strong>. Veuillez consulter les détails ci-dessous pour votre référence.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numéro de note de débit :</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Date de la note de débit :</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numéro de facture :</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numéro de retour :</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Raison :</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Montant total :</strong> {total_amount}</p>

                    </div>

                    <p>Si vous avez besoin de plus d’informations concernant cette note de débit, n’hésitez pas à nous contacter.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Ouvrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Merci pour votre collaboration continue avec <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Cordialement,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Ceci est une notification automatique de {app_name}.
                    </div>

                    </div>

                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota di debito approvata
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro/a <strong>{vendor_name}</strong>,</p>

                    <p>Desideriamo informarti che la nota di debito inviata è stata <strong style="color:#059669;">approvata con successo</strong>. Di seguito trovi i dettagli per riferimento.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numero nota di debito:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data nota di debito:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numero fattura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numero reso:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Importo totale:</strong> {total_amount}</p>

                    </div>

                    <p>Se hai bisogno di ulteriori chiarimenti riguardo a questa nota di debito, non esitare a contattarci.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Apri {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Grazie per la tua continua collaborazione con <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Cordiali saluti,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Questa è una notifica automatica da {app_name}.
                    </div>

                    </div>

                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    デビットノートが承認されました
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>{vendor_name} 様,</p>

                    <p>ご提出いただいたデビットノートが <strong style="color:#059669;">正常に承認されました</strong> ことをお知らせいたします。以下の詳細をご確認ください。</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>デビットノート番号:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>デビットノート日付:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>請求書番号:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>返品番号:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>理由:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>合計金額:</strong> {total_amount}</p>

                    </div>

                    <p>このデビットノートに関してご不明な点がございましたら、お気軽にお問い合わせください。</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{app_name} を開く</a>
                    </p>

                    <p style="margin-top:30px;"><strong>{company_name}</strong> との継続的なご協力に感謝いたします。</p>

                    <p style="margin-top:20px;">よろしくお願いいたします。<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    これは {app_name} からの自動通知です。
                    </div>

                    </div>

                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Debetnota goedgekeurd
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Beste <strong>{vendor_name}</strong>,</p>

                    <p>Wij willen u informeren dat uw ingediende debetnota <strong style="color:#059669;">succesvol is goedgekeurd</strong>. Bekijk hieronder de details ter referentie.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Debetnota nummer:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Datum debetnota:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Factuurnummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Retournummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Reden:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Totaalbedrag:</strong> {total_amount}</p>

                    </div>

                    <p>Als u meer informatie nodig heeft over deze debetnota, neem dan gerust contact met ons op.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Open {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Bedankt voor uw voortdurende samenwerking met <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Met vriendelijke groet,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Dit is een automatische melding van {app_name}.
                    </div>

                    </div>

                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota obciążeniowa zatwierdzona
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Szanowny/a <strong>{vendor_name}</strong>,</p>

                    <p>Chcielibyśmy poinformować, że przesłana przez Ciebie nota obciążeniowa została <strong style="color:#059669;">pomyślnie zatwierdzona</strong>. Szczegóły znajdują się poniżej.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numer noty obciążeniowej:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data noty obciążeniowej:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numer faktury:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numer zwrotu:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Powód:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Łączna kwota:</strong> {total_amount}</p>

                    </div>

                    <p>Jeśli potrzebujesz dodatkowych informacji dotyczących tej noty obciążeniowej, skontaktuj się z nami.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Otwórz {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Dziękujemy za dalszą współpracę z <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Z poważaniem,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    To jest automatyczne powiadomienie z {app_name}.
                    </div>

                    </div>

                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Débito Aprovada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro(a) <strong>{vendor_name}</strong>,</p>

                    <p>Gostaríamos de informar que a sua nota de débito enviada foi <strong style="color:#059669;">aprovada com sucesso</strong>. Consulte os detalhes abaixo para referência.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número da nota de débito:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data da nota de débito:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número da fatura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de devolução:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Valor total:</strong> {total_amount}</p>

                    </div>

                    <p>Se precisar de mais esclarecimentos sobre esta nota de débito, não hesite em contactar-nos.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Obrigado pela sua contínua colaboração com <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Atenciosamente,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Esta é uma notificação automática de {app_name}.
                    </div>

                    </div>

                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Débito Aprovada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro(a) <strong>{vendor_name}</strong>,</p>

                    <p>Gostaríamos de informar que a sua nota de débito enviada foi <strong style="color:#059669;">aprovada com sucesso</strong>. Consulte os detalhes abaixo para referência.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número da nota de débito:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data da nota de débito:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número da fatura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de devolução:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Valor total:</strong> {total_amount}</p>

                    </div>

                    <p>Se precisar de mais esclarecimentos sobre esta nota de débito, não hesite em contactar-nos.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Obrigado pela sua contínua colaboração com <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Atenciosamente,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Esta é uma notificação automática de {app_name}.
                    </div>

                    </div>

                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Дебетовая нота одобрена
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Уважаемый(ая) <strong>{vendor_name}</strong>,</p>

                    <p>Сообщаем вам, что представленная вами дебетовая нота была <strong style="color:#059669;">успешно одобрена</strong>. Пожалуйста, ознакомьтесь с деталями ниже.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Номер дебетовой ноты:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Дата дебетовой ноты:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Номер счета:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Номер возврата:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Причина:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Общая сумма:</strong> {total_amount}</p>

                    </div>

                    <p>Если вам нужна дополнительная информация по данной дебетовой ноте, пожалуйста, свяжитесь с нами.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Открыть {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Спасибо за ваше дальнейшее сотрудничество с <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">С уважением,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Это автоматическое уведомление от {app_name}.
                    </div>

                    </div>

                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    הערת חיוב אושרה
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>שלום <strong>{vendor_name}</strong>,</p>

                    <p>ברצוננו להודיע כי הערת החיוב שהגשת <strong style="color:#059669;">אושרה בהצלחה</strong>. אנא עיין בפרטים להלן.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>מספר הערת חיוב:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>תאריך הערת חיוב:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>מספר חשבונית:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>מספר החזרה:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>סיבה:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>סכום כולל:</strong> {total_amount}</p>

                    </div>

                    <p>אם אתה זקוק למידע נוסף לגבי הערת חיוב זו, אל תהסס לפנות אלינו.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">פתח את {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">תודה על שיתוף הפעולה המתמשך עם <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">בברכה,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    זוהי הודעה אוטומטית מ-{app_name}.
                    </div>

                    </div>

                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Borç Dekontu Onaylandı
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Sayın <strong>{vendor_name}</strong>,</p>

                    <p>Göndermiş olduğunuz borç dekontunun <strong style="color:#059669;">başarıyla onaylandığını</strong> bildirmek isteriz. Lütfen aşağıdaki detayları inceleyiniz.</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Borç Dekontu Numarası:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Borç Dekontu Tarihi:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Fatura Numarası:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>İade Numarası:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Sebep:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Toplam Tutar:</strong> {total_amount}</p>

                    </div>

                    <p>Bu borç dekontu hakkında daha fazla bilgiye ihtiyaç duyarsanız lütfen bizimle iletişime geçmekten çekinmeyin.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{app_name} Aç</a>
                    </p>

                    <p style="margin-top:30px;"><strong>{company_name}</strong> ile devam eden iş birliğiniz için teşekkür ederiz.</p>

                    <p style="margin-top:20px;">Saygılarımızla,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    Bu, {app_name} tarafından gönderilen otomatik bir bildirimdir.
                    </div>

                    </div>

                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#10b981,#059669);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    借项通知单已批准
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>尊敬的 <strong>{vendor_name}</strong>,</p>

                    <p>我们很高兴地通知您，您提交的借项通知单已<strong style="color:#059669;">成功获得批准</strong>。请查看以下详细信息以供参考。</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>借项通知单编号:</strong> {debit_note_number}</p>
                    <p style="margin:6px 0;"><strong>借项通知单日期:</strong> {debit_note_date}</p>
                    <p style="margin:6px 0;"><strong>发票编号:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>退货编号:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>原因:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>总金额:</strong> {total_amount}</p>

                    </div>

                    <p>如果您需要有关此借项通知单的更多信息，请随时与我们联系。</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#10b981;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">打开 {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">感谢您一直以来与 <strong>{company_name}</strong> 的合作。</p>

                    <p style="margin-top:20px;">此致敬礼,<br>
                    <strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f9fafb;color:#6b7280;text-align:center;padding:16px;font-size:13px;border-top:1px solid #e5e7eb;">
                    这是来自 {app_name} 的自动通知。
                    </div>

                    </div>

                    </div>',
                ],
            ],
            'Credit Note Approval' => [
                'subject' => 'Credit Note Approved',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Credit Note Number": "credit_note_number",
                    "Credit Note Date": "credit_note_date",
                    "Customer Name": "customer_name",
                    "Invoice Number": "invoice_number",
                    "Return Number": "return_number",
                    "Reason": "reason",
                    "Total Amount": "total_amount"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    تمت الموافقة على إشعار الدائن
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>عزيزي <strong>{customer_name}</strong>,</p>

                    <p>يسعدنا إبلاغك بأن طلب إشعار الدائن الخاص بك قد تم <strong style="color:#4f46e5;">الموافقة عليه بنجاح</strong>. تم تسجيل إشعار الدائن في نظامنا.</p>

                    <p>يرجى مراجعة التفاصيل أدناه للرجوع إليها:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>رقم إشعار الدائن:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>تاريخ إشعار الدائن:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>رقم الفاتورة:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>رقم الإرجاع:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>السبب:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>المبلغ الإجمالي:</strong> {total_amount}</p>

                    </div>

                    <p>سيتم تطبيق إشعار الدائن هذا وفقًا للشروط المتفق عليها ويمكن استخدامه في المعاملات المستقبلية أو التعديلات إن وجدت.</p>

                    <p>إذا كنت بحاجة إلى أي مساعدة أو لديك أسئلة بخصوص إشعار الدائن هذا، فلا تتردد في التواصل مع فريق الدعم.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">فتح {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">شكرًا لاختيارك <strong>{company_name}</strong>. نحن نقدر عملك معنا.</p>

                    <p style="margin-top:20px;">مع أطيب التحيات,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Kreditnota godkendt
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Kære <strong>{customer_name}</strong>,</p>

                    <p>Vi er glade for at informere dig om, at din kreditnota-anmodning er blevet <strong style="color:#4f46e5;">godkendt</strong>. Kreditnotaen er blevet behandlet og registreret i vores system.</p>

                    <p>Se venligst detaljerne nedenfor:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Kreditnota nummer:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Kreditnota dato:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Faktura nummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Returneringsnummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Årsag:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Samlet beløb:</strong> {total_amount}</p>

                    </div>

                    <p>Denne kreditnota vil blive anvendt i henhold til de aftalte vilkår og kan bruges til fremtidige transaktioner eller justeringer.</p>

                    <p>Hvis du har spørgsmål vedrørende denne kreditnota, er du velkommen til at kontakte vores supportteam.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Åbn {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Tak fordi du valgte <strong>{company_name}</strong>. Vi sætter stor pris på dit samarbejde.</p>

                    <p style="margin-top:20px;">Med venlig hilsen,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Gutschrift genehmigt
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Sehr geehrte/r <strong>{customer_name}</strong>,</p>

                    <p>Wir freuen uns, Ihnen mitteilen zu können, dass Ihre Gutschriftsanfrage <strong style="color:#4f46e5;">erfolgreich genehmigt</strong> wurde. Die Gutschrift wurde in unserem System verarbeitet und erfasst.</p>

                    <p>Bitte prüfen Sie die folgenden Details:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Gutschriftnummer:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Datum der Gutschrift:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Rechnungsnummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Rücksendenummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Grund:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Gesamtbetrag:</strong> {total_amount}</p>

                    </div>

                    <p>Diese Gutschrift wird gemäß den vereinbarten Bedingungen angewendet und kann für zukünftige Transaktionen oder Anpassungen verwendet werden.</p>

                    <p>Wenn Sie Fragen zu dieser Gutschrift haben, wenden Sie sich bitte an unser Support-Team.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{app_name} öffnen</a>
                    </p>

                    <p style="margin-top:30px;">Vielen Dank, dass Sie sich für <strong>{company_name}</strong> entschieden haben.</p>

                    <p style="margin-top:20px;">Mit freundlichen Grüßen,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Credit Note Approved
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Dear <strong>{customer_name}</strong>,</p>

                    <p>We are pleased to inform you that your credit note request has been <strong style="color:#4f46e5;">successfully approved</strong>. The credit note has been processed and recorded in our system.</p>

                    <p>Please review the details below for your reference:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Credit Note Number:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Credit Note Date:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Invoice Number:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Return Number:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Reason:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Total Amount:</strong> {total_amount}</p>

                    </div>

                    <p>This credit note will be applied according to the agreed terms and can be used against future transactions or adjustments where applicable.</p>

                    <p>If you need any assistance or have questions regarding this credit note, please feel free to contact our support team.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Open {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Thank you for choosing <strong>{company_name}</strong>. We truly value your business.</p>

                    <p style="margin-top:20px;">Warm regards,<br>
                    <strong>{company_name}</strong></p>
                    </div>
                    </div>
                    </div>',

                   'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Crédito Aprobada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Estimado/a <strong>{customer_name}</strong>,</p>

                    <p>Nos complace informarle que su solicitud de nota de crédito ha sido <strong style="color:#4f46e5;">aprobada con éxito</strong>. La nota de crédito ha sido procesada y registrada en nuestro sistema.</p>

                    <p>Por favor revise los detalles a continuación:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número de nota de crédito:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Fecha de la nota de crédito:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número de factura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de devolución:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Monto total:</strong> {total_amount}</p>

                    </div>

                    <p>Esta nota de crédito se aplicará de acuerdo con los términos acordados y podrá utilizarse en futuras transacciones o ajustes.</p>

                    <p>Si necesita ayuda o tiene alguna pregunta sobre esta nota de crédito, no dude en contactar con nuestro equipo de soporte.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Gracias por elegir <strong>{company_name}</strong>. Valoramos mucho su preferencia.</p>

                    <p style="margin-top:20px;">Saludos cordiales,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Note de Crédit Approuvée
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Cher/Chère <strong>{customer_name}</strong>,</p>

                    <p>Nous sommes heureux de vous informer que votre demande de note de crédit a été <strong style="color:#4f46e5;">approuvée avec succès</strong>. La note de crédit a été traitée et enregistrée dans notre système.</p>

                    <p>Veuillez consulter les détails ci-dessous pour votre référence :</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numéro de Note de Crédit :</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Date de la Note de Crédit :</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numéro de Facture :</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numéro de Retour :</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Raison :</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Montant Total :</strong> {total_amount}</p>

                    </div>

                    <p>Cette note de crédit sera appliquée conformément aux conditions convenues et pourra être utilisée pour de futures transactions ou ajustements si applicable.</p>

                    <p>Si vous avez des questions concernant cette note de crédit, n\'hésitez pas à contacter notre équipe d\'assistance.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Ouvrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Merci d\'avoir choisi <strong>{company_name}</strong>. Nous apprécions sincèrement votre confiance.</p>

                    <p style="margin-top:20px;">Cordialement,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota di Credito Approvata
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro/a <strong>{customer_name}</strong>,</p>

                    <p>Siamo lieti di informarti che la tua richiesta di nota di credito è stata <strong style="color:#4f46e5;">approvata con successo</strong>. La nota di credito è stata elaborata e registrata nel nostro sistema.</p>

                    <p>Di seguito trovi i dettagli per riferimento:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numero Nota di Credito:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data Nota di Credito:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numero Fattura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numero Reso:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Importo Totale:</strong> {total_amount}</p>

                    </div>

                    <p>Questa nota di credito verrà applicata secondo i termini concordati e potrà essere utilizzata per future transazioni o rettifiche.</p>

                    <p>Se hai domande riguardo a questa nota di credito, non esitare a contattare il nostro team di supporto.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Apri {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Grazie per aver scelto <strong>{company_name}</strong>. Apprezziamo molto la tua fiducia.</p>

                    <p style="margin-top:20px;">Cordiali saluti,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    クレジットノートが承認されました
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>{customer_name} 様</p>

                    <p>お客様のクレジットノート申請が <strong style="color:#4f46e5;">正常に承認されました</strong> ことをお知らせいたします。クレジットノートは処理され、当社システムに記録されました。</p>

                    <p>詳細は以下をご確認ください：</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>クレジットノート番号:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>クレジットノート日付:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>請求書番号:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>返品番号:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>理由:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>合計金額:</strong> {total_amount}</p>

                    </div>

                    <p>このクレジットノートは合意された条件に従って適用され、将来の取引や調整に使用できます。</p>

                    <p>ご不明点がございましたら、サポートチームまでお気軽にお問い合わせください。</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{app_name} を開く</a>
                    </p>

                    <p style="margin-top:30px;"><strong>{company_name}</strong> をご利用いただきありがとうございます。</p>

                    <p style="margin-top:20px;">よろしくお願いいたします。<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Creditnota Goedgekeurd
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Beste <strong>{customer_name}</strong>,</p>

                    <p>Wij informeren u graag dat uw aanvraag voor een creditnota <strong style="color:#4f46e5;">succesvol is goedgekeurd</strong>. De creditnota is verwerkt en geregistreerd in ons systeem.</p>

                    <p>Bekijk hieronder de details ter referentie:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Creditnota Nummer:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Creditnota Datum:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Factuurnummer:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Retournummer:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Reden:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Totaalbedrag:</strong> {total_amount}</p>

                    </div>

                    <p>Deze creditnota wordt toegepast volgens de overeengekomen voorwaarden en kan worden gebruikt voor toekomstige transacties of correcties.</p>

                    <p>Als u vragen heeft over deze creditnota, neem dan gerust contact op met ons ondersteuningsteam.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Open {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Bedankt dat u voor <strong>{company_name}</strong> heeft gekozen.</p>

                    <p style="margin-top:20px;">Met vriendelijke groet,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota Kredytowa Zatwierdzona
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Szanowny/a <strong>{customer_name}</strong>,</p>

                    <p>Z przyjemnością informujemy, że Twoja prośba o notę kredytową została <strong style="color:#4f46e5;">pomyślnie zatwierdzona</strong>. Nota kredytowa została przetworzona i zapisana w naszym systemie.</p>

                    <p>Poniżej znajdują się szczegóły do Twojej wiadomości:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Numer Noty Kredytowej:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data Noty Kredytowej:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Numer Faktury:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Numer Zwrotu:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Powód:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Łączna Kwota:</strong> {total_amount}</p>

                    </div>

                    <p>Ta nota kredytowa zostanie zastosowana zgodnie z uzgodnionymi warunkami i może być użyta w przyszłych transakcjach lub korektach.</p>

                    <p>Jeśli masz pytania dotyczące tej noty kredytowej, skontaktuj się z naszym zespołem wsparcia.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Otwórz {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Dziękujemy za wybór <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Z poważaniem,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Crédito Aprovada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro(a) <strong>{customer_name}</strong>,</p>

                    <p>Temos o prazer de informar que sua solicitação de nota de crédito foi <strong style="color:#4f46e5;">aprovada com sucesso</strong>. A nota de crédito foi processada e registrada em nosso sistema.</p>

                    <p>Veja os detalhes abaixo para referência:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número da Nota de Crédito:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data da Nota de Crédito:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número da Fatura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de Devolução:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Valor Total:</strong> {total_amount}</p>

                    </div>

                    <p>Esta nota de crédito será aplicada conforme os termos acordados e poderá ser utilizada em futuras transações ou ajustes.</p>

                    <p>Se precisar de ajuda ou tiver dúvidas sobre esta nota de crédito, entre em contato com nossa equipe de suporte.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Obrigado por escolher <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Atenciosamente,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Nota de Crédito Aprovada
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Caro(a) <strong>{customer_name}</strong>,</p>

                    <p>Temos o prazer de informar que sua solicitação de nota de crédito foi <strong style="color:#4f46e5;">aprovada com sucesso</strong>. A nota de crédito foi processada e registrada em nosso sistema.</p>

                    <p>Veja os detalhes abaixo para referência:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Número da Nota de Crédito:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Data da Nota de Crédito:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Número da Fatura:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Número de Devolução:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Motivo:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Valor Total:</strong> {total_amount}</p>

                    </div>

                    <p>Esta nota de crédito será aplicada conforme os termos acordados e poderá ser utilizada em futuras transações ou ajustes.</p>

                    <p>Se precisar de ajuda ou tiver dúvidas sobre esta nota de crédito, entre em contato com nossa equipe de suporte.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Abrir {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Obrigado por escolher <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">Atenciosamente,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Кредит-нота Одобрена
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Уважаемый(ая) <strong>{customer_name}</strong>,</p>

                    <p>Мы рады сообщить, что ваш запрос на кредит-ноту был <strong style="color:#4f46e5;">успешно одобрен</strong>. Кредит-нота была обработана и зарегистрирована в нашей системе.</p>

                    <p>Пожалуйста, ознакомьтесь с деталями ниже:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Номер Кредит-ноты:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Дата Кредит-ноты:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Номер Счета:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>Номер Возврата:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Причина:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Общая Сумма:</strong> {total_amount}</p>

                    </div>

                    <p>Эта кредит-нота будет применена в соответствии с согласованными условиями и может быть использована для будущих транзакций или корректировок.</p>

                    <p>Если у вас возникнут вопросы, пожалуйста, свяжитесь с нашей службой поддержки.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Открыть {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">Спасибо, что выбрали <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">С уважением,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    אישור זיכוי
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>שלום <strong>{customer_name}</strong>,</p>

                    <p>אנו שמחים להודיע כי בקשת הזיכוי שלך <strong style="color:#4f46e5;">אושרה בהצלחה</strong>. הזיכוי עובד ונרשם במערכת שלנו.</p>

                    <p>להלן הפרטים לעיונך:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>מספר זיכוי:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>תאריך זיכוי:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>מספר חשבונית:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>מספר החזרה:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>סיבה:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>סכום כולל:</strong> {total_amount}</p>

                    </div>

                    <p>זיכוי זה יוחל בהתאם לתנאים שסוכמו וניתן להשתמש בו בעסקאות עתידיות או בהתאמות.</p>

                    <p>אם יש לך שאלות, אנא פנה לצוות התמיכה שלנו.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">פתח את {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">תודה שבחרת ב- <strong>{company_name}</strong>.</p>

                    <p style="margin-top:20px;">בברכה,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',

                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    Kredi Notu Onaylandı
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>Sayın <strong>{customer_name}</strong>,</p>

                    <p>Kredi notu talebinizin <strong style="color:#4f46e5;">başarıyla onaylandığını</strong> bildirmekten memnuniyet duyarız. Kredi notu işlenmiş ve sistemimize kaydedilmiştir.</p>

                    <p>Lütfen aşağıdaki detayları inceleyiniz:</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>Kredi Notu Numarası:</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>Kredi Notu Tarihi:</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>Fatura Numarası:</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>İade Numarası:</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>Neden:</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>Toplam Tutar:</strong> {total_amount}</p>

                    </div>

                    <p>Bu kredi notu, üzerinde anlaşılan şartlara göre uygulanacak ve gelecekteki işlemler veya düzeltmeler için kullanılabilecektir.</p>

                    <p>Bu kredi notu hakkında herhangi bir sorunuz varsa lütfen destek ekibimizle iletişime geçin.</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">{app_name} Aç</a>
                    </p>

                    <p style="margin-top:30px;"><strong>{company_name}</strong> tercih ettiğiniz için teşekkür ederiz.</p>

                    <p style="margin-top:20px;">Saygılarımızla,<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',
                    
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    <div style="background:linear-gradient(90deg,#6366f1,#4f46e5);color:#ffffff;padding:26px 30px;font-size:20px;font-weight:600;">
                    贷记通知单已批准
                    </div>

                    <div style="padding:32px 30px;color:#374151;font-size:15px;line-height:1.7;">

                    <p>尊敬的 <strong>{customer_name}</strong>，</p>

                    <p>我们很高兴地通知您，您的贷记通知单申请已<strong style="color:#4f46e5;">成功批准</strong>。该贷记通知单已处理并记录在我们的系统中。</p>

                    <p>请查看以下详细信息：</p>

                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:20px 0;">

                    <p style="margin:6px 0;"><strong>贷记通知单编号：</strong> {credit_note_number}</p>
                    <p style="margin:6px 0;"><strong>贷记通知单日期：</strong> {credit_note_date}</p>
                    <p style="margin:6px 0;"><strong>发票编号：</strong> {invoice_number}</p>
                    <p style="margin:6px 0;"><strong>退货编号：</strong> {return_number}</p>
                    <p style="margin:6px 0;"><strong>原因：</strong> {reason}</p>
                    <p style="margin:6px 0;font-size:16px;"><strong>总金额：</strong> {total_amount}</p>

                    </div>

                    <p>该贷记通知单将根据双方约定的条款进行应用，并可用于未来的交易或调整。</p>

                    <p>如果您对此贷记通知单有任何疑问，请随时联系我们的支持团队。</p>

                    <p style="margin-top:25px;">
                    <a href="{app_url}" style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">打开 {app_name}</a>
                    </p>

                    <p style="margin-top:30px;">感谢您选择 <strong>{company_name}</strong>。</p>

                    <p style="margin-top:20px;">此致敬礼，<br>
                    <strong>{company_name}</strong></p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
        ];
        foreach($emailTemplate as $eTemp)
        {
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','Account')->exists();
            if(!$table)
            {
                $emailtemplate=  EmailTemplate::create(
                    [
                    'name' => $eTemp,
                    'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas ERP',
                    'module_name' => 'Account',
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