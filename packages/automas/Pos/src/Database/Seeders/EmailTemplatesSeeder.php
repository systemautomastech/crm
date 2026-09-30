<?php

namespace Automas\Pos\Database\Seeders;

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
            'Create POS',
            'POS Return',
        ];
        $defaultTemplate = [
            'Create POS' => [
                'subject' => 'POS Created',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Customer Name ": "sales_customer_name",
                    "Warehouse Name": "warehouse_name",
                    "Total Amount": "total_amount",
                    "Discount Amount": "discount_amount",
                    "Item Details": "item_details"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">تأكيد أمر الشراء</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>مرحبًا <strong>{sales_customer_name}</strong>،</p>

                    <p>
                    يسرنا إبلاغك بأن <strong>{company_name}</strong> قد أنشأت أمر شراء جديد لك.  
                    فيما يلي التفاصيل الكاملة لطلبك.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>المستودع:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">تفاصيل العناصر</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">ملخص الطلب</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>الخصم</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>المبلغ الإجمالي</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    يمكنك عرض تفاصيل الطلب الكاملة في أي وقت من خلال النقر على الزر أدناه.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    عرض أمر الشراء
                    </a>
                    </p>

                    <p>
                    إذا كان لديك أي استفسار بخصوص هذا الطلب، فلا تتردد في التواصل معنا في أي وقت.  
                    نحن نقدر عملك معنا.
                    </p>

                    <p style="margin-top:30px;">
                    مع أطيب التحيات،<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    تم إرسال هذا البريد عبر {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'da' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">
                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">
                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Bekræftelse af indkøbsordre</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">
                    <p>Hej <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Vi er glade for at informere dig om, at <strong>{company_name}</strong> har oprettet en ny indkøbsordre til dig.  
                    Nedenfor finder du de fulde detaljer om din ordre.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Lager:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Varedetaljer</h3>
                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Ordreoversigt</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <tr>
                    <td style="padding:8px 0;"><strong>Rabat</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Samlet beløb</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>
                    </table>
                    </div>

                    <p>Du kan til enhver tid se de fulde ordredetaljer ved at klikke på knappen nedenfor.</p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Se indkøbsordre
                    </a>
                    </p>

                    <p>Hvis du har spørgsmål til denne ordre, er du velkommen til at kontakte os.</p>

                    <p style="margin-top:30px;">Med venlig hilsen,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Denne e-mail blev sendt via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'de' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">
                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">
                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Bestätigung der Bestellung</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">
                    <p>Hallo <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Wir freuen uns, Ihnen mitteilen zu können, dass <strong>{company_name}</strong> eine neue Bestellung für Sie erstellt hat.  
                    Nachfolgend finden Sie die vollständigen Details Ihrer Bestellung.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Lager:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Artikeldetails</h3>
                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Bestellübersicht</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <tr>
                    <td style="padding:8px 0;"><strong>Rabatt</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Gesamtbetrag</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>
                    </table>
                    </div>

                    <p>Sie können die vollständigen Bestelldetails jederzeit über den untenstehenden Button ansehen.</p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Bestellung ansehen
                    </a>
                    </p>

                    <p>Bei Fragen zu dieser Bestellung können Sie uns jederzeit kontaktieren.</p>

                    <p style="margin-top:30px;">Mit freundlichen Grüßen,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Diese E-Mail wurde über {app_name} gesendet.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'en' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Purchase Order Confirmation</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Hello <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    We are pleased to inform you that <strong>{company_name}</strong> has created a new Purchase Order for you.  
                    Below are the complete details of your order.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Warehouse:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Item Details</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Order Summary</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Discount</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Total Amount</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    You can view the full order details anytime by clicking the button below.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    View Purchase Order
                    </a>
                    </p>

                    <p>
                    If you have any questions about this order, feel free to contact us anytime.  
                    We truly appreciate your business.
                    </p>

                    <p style="margin-top:30px;">
                    Best Regards,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    This email was sent via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'es' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">
                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">
                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Confirmación de Orden de Compra</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">
                    <p>Hola <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Nos complace informarle que <strong>{company_name}</strong> ha creado una nueva orden de compra para usted.  
                    A continuación encontrará los detalles completos de su pedido.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Almacén:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Detalles del artículo</h3>
                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Resumen del pedido</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <tr>
                    <td style="padding:8px 0;"><strong>Descuento</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Monto total</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>
                    </table>
                    </div>

                    <p>Puedes ver todos los detalles de tu pedido en cualquier momento haciendo clic en el botón a continuación.</p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Ver Orden de Compra
                    </a>
                    </p>

                    <p>Si tienes alguna pregunta sobre este pedido, no dudes en contactarnos.</p>

                    <p style="margin-top:30px;">Saludos cordiales,<br><strong>{company_name}</strong></p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Este correo fue enviado a través de {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                        'fr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Confirmation du bon de commande</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Bonjour <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Nous avons le plaisir de vous informer que <strong>{company_name}</strong> a créé un nouveau bon de commande pour vous.  
                    Vous trouverez ci-dessous les détails complets de votre commande.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Entrepôt :</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Détails des articles</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Résumé de la commande</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Remise</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Montant total</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Vous pouvez consulter les détails complets de la commande à tout moment en cliquant sur le bouton ci-dessous.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Voir le bon de commande
                    </a>
                    </p>

                    <p>
                    Si vous avez des questions concernant cette commande, n’hésitez pas à nous contacter.
                    </p>

                    <p style="margin-top:30px;">
                    Cordialement,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Cet e-mail a été envoyé via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'it' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Conferma Ordine di Acquisto</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Ciao <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Siamo lieti di informarti che <strong>{company_name}</strong> ha creato un nuovo ordine di acquisto per te.  
                    Di seguito trovi i dettagli completi del tuo ordine.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Magazzino:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Dettagli Articolo</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Riepilogo Ordine</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Sconto</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Importo Totale</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Puoi visualizzare tutti i dettagli dell\ordine in qualsiasi momento cliccando sul pulsante qui sotto.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Visualizza Ordine
                    </a>
                    </p>

                    <p>
                    Se hai domande riguardo questo ordine, non esitare a contattarci.
                    </p>

                    <p style="margin-top:30px;">
                    Cordiali saluti,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Questa email è stata inviata tramite {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">購買注文の確認</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>{sales_customer_name} 様</p>

                    <p>
                    <strong>{company_name}</strong> が新しい購買注文を作成したことをお知らせいたします。  
                    以下にご注文の詳細をご確認ください。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>倉庫:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">商品詳細</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">注文概要</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>割引</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>合計金額</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    下のボタンをクリックすると、いつでも注文の詳細を確認できます。
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    注文を見る
                    </a>
                    </p>

                    <p>
                    ご不明な点がございましたら、お気軽にお問い合わせください。
                    </p>

                    <p style="margin-top:30px;">
                    よろしくお願いいたします。<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    このメールは {app_name} を通じて送信されました。<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Bevestiging van Inkooporder</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Hallo <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    We informeren u graag dat <strong>{company_name}</strong> een nieuwe inkooporder voor u heeft aangemaakt.  
                    Hieronder vindt u de volledige details van uw bestelling.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Magazijn:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Artikelgegevens</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Besteloverzicht</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Korting</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Totaalbedrag</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    U kunt de volledige bestelgegevens op elk moment bekijken door op de onderstaande knop te klikken.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Bekijk Inkooporder
                    </a>
                    </p>

                    <p>
                    Als u vragen heeft over deze bestelling, neem dan gerust contact met ons op.
                    </p>

                    <p style="margin-top:30px;">
                    Met vriendelijke groet,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Deze e-mail is verzonden via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Potwierdzenie Zamówienia Zakupu</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Witaj <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Z przyjemnością informujemy, że <strong>{company_name}</strong> utworzyła nowe zamówienie zakupu dla Ciebie.  
                    Poniżej znajdują się pełne szczegóły Twojego zamówienia.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Magazyn:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Szczegóły Produktu</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Podsumowanie Zamówienia</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Rabat</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Kwota całkowita</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Możesz zobaczyć pełne szczegóły zamówienia w dowolnym momencie, klikając poniższy przycisk.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Zobacz Zamówienie
                    </a>
                    </p>

                    <p>
                    Jeśli masz pytania dotyczące tego zamówienia, skontaktuj się z nami w dowolnym momencie.
                    </p>

                    <p style="margin-top:30px;">
                    Z poważaniem,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Ten e-mail został wysłany za pośrednictwem {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'ru' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Подтверждение Заказа на Покупку</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Здравствуйте, <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Мы рады сообщить вам, что <strong>{company_name}</strong> создала для вас новый заказ на покупку.  
                    Ниже приведены полные детали вашего заказа.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Склад:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Детали Товара</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Сводка Заказа</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Скидка</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Общая сумма</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Вы можете просмотреть полные детали заказа в любое время, нажав кнопку ниже.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Просмотреть Заказ
                    </a>
                    </p>

                    <p>
                    Если у вас есть вопросы по этому заказу, пожалуйста, свяжитесь с нами в любое время.
                    </p>

                    <p style="margin-top:30px;">
                    С уважением,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Это письмо было отправлено через {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                    'pt' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Confirmação do Pedido de Compra</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Olá <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Temos o prazer de informar que <strong>{company_name}</strong> criou um novo pedido de compra para você.  
                    Abaixo estão os detalhes completos do seu pedido.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Armazém:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Detalhes do Item</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Resumo do Pedido</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Desconto</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Valor Total</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Você pode visualizar os detalhes completos do pedido a qualquer momento clicando no botão abaixo.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Ver Pedido de Compra
                    </a>
                    </p>

                    <p>
                    Se tiver alguma dúvida sobre este pedido, não hesite em entrar em contato conosco.
                    </p>

                    <p style="margin-top:30px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Este e-mail foi enviado via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',


                    'pt-BR' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Confirmação do Pedido de Compra</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Olá <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    Temos o prazer de informar que <strong>{company_name}</strong> criou um novo pedido de compra para você.  
                    Abaixo estão os detalhes completos do seu pedido.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Armazém:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Detalhes do Item</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Resumo do Pedido</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>Desconto</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Valor Total</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Você pode visualizar os detalhes completos do pedido a qualquer momento clicando no botão abaixo.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Ver Pedido de Compra
                    </a>
                    </p>

                    <p>
                    Se tiver alguma dúvida sobre este pedido, não hesite em entrar em contato conosco.
                    </p>

                    <p style="margin-top:30px;">
                    Atenciosamente,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Este e-mail foi enviado via {app_name}.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',


                    'tr' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">Satın Alma Siparişi Onayı</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>Merhaba <strong>{sales_customer_name}</strong>,</p>

                    <p>
                    <strong>{company_name}</strong> tarafından sizin için yeni bir satın alma siparişi oluşturulduğunu bildirmekten memnuniyet duyarız.  
                    Aşağıda siparişinizin tüm detaylarını bulabilirsiniz.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>Depo:</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">Ürün Detayları</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">Sipariş Özeti</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>İndirim</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>Toplam Tutar</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    Aşağıdaki butona tıklayarak siparişinizin tüm detaylarını istediğiniz zaman görüntüleyebilirsiniz.
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    Satın Alma Siparişini Görüntüle
                    </a>
                    </p>

                    <p>
                    Bu sipariş hakkında herhangi bir sorunuz varsa, bizimle iletişime geçmekten çekinmeyin.
                    </p>

                    <p style="margin-top:30px;">
                    Saygılarımızla,<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    Bu e-posta {app_name} aracılığıyla gönderilmiştir.<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:40px;">

                    <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#1f4fff;color:#ffffff;padding:24px;text-align:center;">
                    <h2 style="margin:0;">采购订单确认</h2>
                    <p style="margin:6px 0 0;font-size:14px;">{app_name}</p>
                    </div>

                    <div style="padding:30px;color:#333333;line-height:1.6;">

                    <p>您好 <strong>{sales_customer_name}</strong>，</p>

                    <p>
                    我们很高兴地通知您，<strong>{company_name}</strong> 已为您创建了一份新的采购订单。  
                    以下是您订单的完整详细信息。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:16px;margin:20px 0;">
                    <strong>仓库：</strong> {warehouse_name}
                    </div>

                    <div style="margin:25px 0;">
                    <h3 style="color:#1f4fff;margin-bottom:10px;">商品详情</h3>

                    <div style="border:1px solid #e6e8f0;border-radius:10px;padding:15px;background:#fafbff;">
                    {item_details}
                    </div>
                    </div>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px;margin:25px 0;">
                    <h3 style="margin-top:0;color:#1f4fff;">订单摘要</h3>

                    <table style="width:100%;border-collapse:collapse;font-size:14px;">

                    <tr>
                    <td style="padding:8px 0;"><strong>折扣</strong></td>
                    <td style="padding:8px 0;text-align:right;">{discount_amount}</td>
                    </tr>

                    <tr style="border-top:1px solid #e6e8f0;">
                    <td style="padding:12px 0;font-size:16px;"><strong>总金额</strong></td>
                    <td style="padding:12px 0;font-size:16px;text-align:right;color:#1f9254;">
                    <strong>{total_amount}</strong>
                    </td>
                    </tr>

                    </table>
                    </div>

                    <p>
                    您可以随时点击下面的按钮查看订单的完整详情。
                    </p>

                    <p style="text-align:center;margin:30px 0;">
                    <a href="{app_url}" style="background:#1f4fff;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;display:inline-block;">
                    查看采购订单
                    </a>
                    </p>

                    <p>
                    如果您对该订单有任何疑问，请随时联系我们。
                    </p>

                    <p style="margin-top:30px;">
                    此致敬礼，<br>
                    <strong>{company_name}</strong>
                    </p>

                    </div>

                    <div style="background:#f1f3f9;padding:16px;text-align:center;font-size:12px;color:#666;">
                    此邮件通过 {app_name} 发送。<br>
                    <a href="{app_url}" style="color:#1f4fff;text-decoration:none;">{app_url}</a>
                    </div>

                    </div>
                    </div>',
                ],
            ],
            'POS Return' => [
                'subject' => 'POS Return Confirmation',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Return Number": "return_number",
                    "Return Date": "return_date",
                    "Customer Name": "customer_name",
                    "Warehouse Name": "warehouse_name",
                    "Reason": "reason",
                    "Total Amount": "total_amount"
                }',
                'lang' => [
                    'ar' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;direction:rtl;text-align:right;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    تأكيد إرجاع نقطة البيع
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    تم إنشاء ومعالجة طلب إرجاع نقطة البيع الخاص بك بنجاح.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    مرحباً <strong>{customer_name}</strong>,
                                </p>
                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    شكراً لتواصلك مع <strong>{company_name}</strong>.
                                    تم تسجيل طلب إرجاع نقطة البيع الخاص بك بنجاح.
                                    فيما يلي تفاصيل الإرجاع للرجوع إليها.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                رقم الإرجاع
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                تاريخ الإرجاع
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                اسم العميل
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                المستودع
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                السبب
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                المبلغ الإجمالي
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:left;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="margin-top:35px;text-align:center;">
                                    <a href="{app_url}"
                                    style="display:inline-block;background:#ef4444;color:#ffffff;
                                            text-decoration:none;padding:14px 28px;border-radius:12px;
                                            font-size:15px;font-weight:600;">
                                        فتح {app_name}
                                    </a>
                                </div>

                                <p style="margin:35px 0 0;font-size:14px;color:#64748b;line-height:1.8;">
                                    إذا كان لديك أي استفسار بخصوص هذا الإرجاع،
                                    يرجى التواصل مع فريق الدعم.
                                </p>

                            </div>
                        </div>
                    </div>',

                    'da' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS-returbekræftelse
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Din POS-retur er blevet oprettet og behandlet.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Hej <strong>{customer_name}</strong>,
                                </p>
                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Tak fordi du kontaktede <strong>{company_name}</strong>.
                                    Din POS-returanmodning er blevet registreret.
                                    Nedenfor finder du returdetaljerne.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Returnummer
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Returdato
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Kundenavn
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Lager
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Årsag
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Samlet beløb
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'de' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS-Rückgabebestätigung
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Ihre POS-Rückgabe wurde erfolgreich erstellt und bearbeitet.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Hallo <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Vielen Dank, dass Sie <strong>{company_name}</strong> kontaktiert haben.
                                    Ihre POS-Rückgabeanfrage wurde erfolgreich registriert.
                                    Nachfolgend finden Sie die Rückgabedetails.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Rückgabenummer
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Rückgabedatum
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Kundenname
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Lager
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Grund
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Gesamtbetrag
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'en' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS Return Confirmation
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Your POS return has been successfully created and processed.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Hello <strong>{customer_name}</strong>,
                                </p>
                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Thank you for contacting <strong>{company_name}</strong>.
                                    Your POS return request has been recorded successfully.
                                    Below are the return details for your reference.
                                </p>
                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Return Number
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Return Date
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Customer Name
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Warehouse
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Reason
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Total Amount
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="margin-top:35px;text-align:center;">
                                    <a href="{app_url}"
                                    style="display:inline-block;background:#ef4444;color:#ffffff;
                                            text-decoration:none;padding:14px 28px;border-radius:12px;
                                            font-size:15px;font-weight:600;">
                                        Open {app_name}
                                    </a>
                                </div>
                                <p style="margin:35px 0 0;font-size:14px;color:#64748b;line-height:1.8;">
                                    If you have any questions regarding this return,
                                    please contact our support team.
                                </p>

                            </div>
                        </div>
                    </div>',

                    'es' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Confirmación de devolución POS
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Su devolución POS ha sido creada y procesada correctamente.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Hola <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Gracias por contactar con <strong>{company_name}</strong>.
                                    Su solicitud de devolución POS ha sido registrada correctamente.
                                    A continuación encontrará los detalles de la devolución.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Número de devolución
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Fecha de devolución
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nombre del cliente
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Almacén
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Motivo
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Importe total
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'fr' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Confirmation du retour POS
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Votre retour POS a été créé et traité avec succès.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Bonjour <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Merci d\'avoir contacté <strong>{company_name}</strong>.
                                    Votre demande de retour POS a été enregistrée avec succès.
                                    Vous trouverez ci-dessous les détails du retour.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Numéro de retour
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Date du retour
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nom du client
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Entrepôt
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Raison
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Montant total
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'it' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Conferma reso POS
                                </h1>
                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Il tuo reso POS è stato creato ed elaborato con successo.
                                </p>
                            </div>

                            <div style="padding:40px;">
                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Ciao <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Grazie per aver contattato <strong>{company_name}</strong>.
                                    La tua richiesta di reso POS è stata registrata con successo.
                                    Di seguito trovi i dettagli del reso.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Numero reso
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Data reso
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nome cliente
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Magazzino
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Motivo
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Importo totale
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'ja' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS返品確認
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    POS返品が正常に作成および処理されました。
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    <strong>{customer_name}</strong> 様
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    <strong>{company_name}</strong> にお問い合わせいただきありがとうございます。
                                    POS返品リクエストが正常に登録されました。
                                    以下に返品の詳細をご案内いたします。
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                返品番号
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                返品日
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                顧客名
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                倉庫
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                理由
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                合計金額
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'nl' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS-retourbevestiging
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Uw POS-retour is succesvol aangemaakt en verwerkt.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Hallo <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Bedankt voor uw contact met <strong>{company_name}</strong>.
                                    Uw POS-retouraanvraag is succesvol geregistreerd.
                                    Hieronder vindt u de retourgegevens.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Retournummer
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Retourdatum
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Klantnaam
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Magazijn
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Reden
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Totaalbedrag
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',
                    'pl' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Potwierdzenie zwrotu POS
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Twój zwrot POS został pomyślnie utworzony i przetworzony.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Witaj <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Dziękujemy za kontakt z <strong>{company_name}</strong>.
                                    Twoje zgłoszenie zwrotu POS zostało pomyślnie zarejestrowane.
                                    Poniżej znajdują się szczegóły zwrotu.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Numer zwrotu
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Data zwrotu
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nazwa klienta
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Magazyn
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Powód
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Łączna kwota
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'ru' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Подтверждение возврата POS
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Ваш возврат POS был успешно создан и обработан.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Здравствуйте, <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Спасибо за обращение в <strong>{company_name}</strong>.
                                    Ваш запрос на возврат POS был успешно зарегистрирован.
                                    Ниже приведены детали возврата.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Номер возврата
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Дата возврата
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Имя клиента
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Склад
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Причина
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Общая сумма
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'pt' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Confirmação de devolução POS
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Sua devolução POS foi criada e processada com sucesso.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Olá <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Obrigado por entrar em contato com <strong>{company_name}</strong>.
                                    Sua solicitação de devolução POS foi registrada com sucesso.
                                    Abaixo estão os detalhes da devolução para sua referência.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Número da devolução
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Data da devolução
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nome do cliente
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Armazém
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Motivo
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Valor total
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',


                    'pt-BR' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    Confirmação de devolução POS
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    Sua devolução POS foi criada e processada com sucesso.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Olá <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    Obrigado por entrar em contato com <strong>{company_name}</strong>.
                                    Sua solicitação de devolução POS foi registrada com sucesso.
                                    Abaixo estão os detalhes da devolução para sua referência.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Número da devolução
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Data da devolução
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Nome do cliente
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Armazém
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Motivo
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Valor total
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'tr' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS İade Onayı
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    POS iadeniz başarıyla oluşturuldu ve işlendi.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    Merhaba <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    <strong>{company_name}</strong> ile iletişime geçtiğiniz için teşekkür ederiz.
                                    POS iade talebiniz başarıyla kaydedildi.
                                    Aşağıda iade detaylarını bulabilirsiniz.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                İade Numarası
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                İade Tarihi
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Müşteri Adı
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Depo
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Sebep
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                Toplam Tutar
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'zh' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    POS退货确认
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    您的POS退货已成功创建并处理。
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    您好，<strong>{customer_name}</strong>
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    感谢您联系 <strong>{company_name}</strong>。
                                    您的POS退货请求已成功记录。
                                    以下是退货详情供您参考。
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                退货编号
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                退货日期
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                客户名称
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                仓库
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                原因
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:right;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                总金额
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:right;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',

                    'he' => '<div style="margin:0;padding:40px 20px;background:#f4f7fb;font-family:\'Segoe UI\',Arial,sans-serif;direction:rtl;text-align:right;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:20px;
                                    overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">

                            <div style="background:linear-gradient(135deg,#ef4444 0%,#f97316 100%);
                                        padding:32px 40px;color:#ffffff;">
                                <h1 style="margin:0;font-size:28px;font-weight:700;">
                                    אישור החזרת POS
                                </h1>

                                <p style="margin:10px 0 0;font-size:15px;line-height:1.7;opacity:0.95;">
                                    החזרת ה-POS שלך נוצרה ועובדה בהצלחה.
                                </p>
                            </div>

                            <div style="padding:40px;">

                                <p style="margin:0 0 20px;font-size:16px;color:#334155;line-height:1.8;">
                                    שלום <strong>{customer_name}</strong>,
                                </p>

                                <p style="margin:0 0 30px;font-size:15px;color:#475569;line-height:1.8;">
                                    תודה שפנית אל <strong>{company_name}</strong>.
                                    בקשת החזרת ה-POS שלך נרשמה בהצלחה.
                                    להלן פרטי ההחזרה לעיונך.
                                </p>

                                <div style="background:#f8fafc;border:1px solid #e2e8f0;
                                            border-radius:16px;padding:28px;">

                                    <table style="width:100%;border-collapse:collapse;">

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                מספר החזרה
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {return_number}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                תאריך החזרה
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {return_date}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                שם הלקוח
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {customer_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                מחסן
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {warehouse_name}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                סיבה
                                            </td>

                                            <td style="padding:12px 0;font-size:14px;
                                                    color:#0f172a;font-weight:600;text-align:left;">
                                                {reason}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding:12px 0;font-size:14px;color:#64748b;">
                                                סכום כולל
                                            </td>

                                            <td style="padding:12px 0;font-size:16px;
                                                    color:#dc2626;font-weight:700;text-align:left;">
                                                {total_amount}
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>',
                ],
            ],
        ];
        foreach($emailTemplate as $eTemp)
        {
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','Pos')->exists();
            if(!$table)
            {
                $emailtemplate=  EmailTemplate::create(
                    [
                    'name' => $eTemp,
                    'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas ERP',
                    'module_name' => 'Pos',
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