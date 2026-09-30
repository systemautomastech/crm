<?php

namespace Automas\Hrm\Database\Seeders;

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
            'Create Award',
            'Promotions Approval',
            'Resignations Status',
            'Warning Approval',
            'Transfers Approval',
            'Leave Status',
            'Payroll Processed'
        ];
        $defaultTemplate = [
            'Create Award' => [
                'subject' => 'Award Created',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Employee Name ": "employee_name",
                    "Award Type": "award_type",
                    "Award Date": "award_date"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 تهانينا {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>مرحبًا <strong>{employee_name}</strong>,</p>

                    <p>
                    يسرّنا إبلاغك بأنه تم تكريمك بجائزة 
                    <strong>{award_type}</strong> في <strong>{company_name}</strong>.
                    </p>

                    <p>
                    إن تفانيك والتزامك ومساهماتك كان لها تأثير كبير على فريقنا. 
                    هذا التكريم يعكس تقديرنا لجهودك وتميّزك المستمر.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">تفاصيل الجائزة</div>

                    <div>🏅 <strong>نوع الجائزة:</strong> {award_type}</div>
                    <div>📅 <strong>تاريخ الجائزة:</strong> {award_date}</div>

                    </div>

                    <p>
                    نحن نقدر حقًا تفانيك والطاقة الإيجابية التي تقدمها إلى <strong>{company_name}</strong>. 
                    تهانينا مرة أخرى على هذا الإنجاز المستحق!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    عرض في {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    مع أطيب التحيات,<br>
                    <strong>فريق {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Tillykke {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hej <strong>{employee_name}</strong>,</p>

                    <p>
                    Vi er glade for at informere dig om, at du er blevet hædret med 
                    <strong>{award_type}</strong> prisen hos <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Din dedikation, dit engagement og dine bidrag har haft en stor betydning for vores team. 
                    Denne anerkendelse viser vores værdsættelse af din indsats og det høje niveau, du konstant leverer.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Prisdetaljer</div>

                    <div>🏅 <strong>Pristype:</strong> {award_type}</div>
                    <div>📅 <strong>Prisdato:</strong> {award_date}</div>

                    </div>

                    <p>
                    Vi sætter stor pris på din dedikation og den positive energi, du bringer til <strong>{company_name}</strong>. 
                    Tillykke endnu en gang med denne velfortjente anerkendelse!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Se i {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Med venlig hilsen,<br>
                    <strong>{app_name} Team</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Herzlichen Glückwunsch {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hallo <strong>{employee_name}</strong>,</p>

                    <p>
                    Wir freuen uns, Ihnen mitteilen zu können, dass Sie mit der Auszeichnung 
                    <strong>{award_type}</strong> bei <strong>{company_name}</strong> geehrt wurden.
                    </p>

                    <p>
                    Ihr Engagement, Ihre Hingabe und Ihre Beiträge haben einen bedeutenden Einfluss auf unser Team. 
                    Diese Anerkennung spiegelt unsere Wertschätzung für Ihre hervorragende Arbeit wider.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Auszeichnungsdetails</div>

                    <div>🏅 <strong>Auszeichnung:</strong> {award_type}</div>
                    <div>📅 <strong>Datum der Auszeichnung:</strong> {award_date}</div>

                    </div>

                    <p>
                    Wir schätzen Ihr Engagement und die positive Energie, die Sie zu <strong>{company_name}</strong> beitragen, sehr. 
                    Nochmals herzlichen Glückwunsch zu dieser wohlverdienten Auszeichnung!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    In {app_name} ansehen
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Mit freundlichen Grüßen,<br>
                    <strong>{app_name} Team</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Congratulations {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hello <strong>{employee_name}</strong>,</p>

                    <p>
                    We are delighted to inform you that you have been honored with the 
                    <strong>{award_type}</strong> award at <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Your dedication, commitment, and contributions have made a meaningful impact on our team. 
                    This recognition reflects our appreciation for the effort and excellence you consistently demonstrate.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Award Details</div>

                    <div>🏅 <strong>Award Type:</strong> {award_type}</div>
                    <div>📅 <strong>Award Date:</strong> {award_date}</div>

                    </div>

                    <p>
                    We truly appreciate your dedication and the positive energy you bring to <strong>{company_name}</strong>. 
                    Congratulations once again on this well-deserved achievement!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    View in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Best regards,<br>
                    <strong>{app_name} Team</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 ¡Felicidades {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hola <strong>{employee_name}</strong>,</p>

                    <p>
                    Nos complace informarte que has sido reconocido con el premio 
                    <strong>{award_type}</strong> en <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Tu dedicación, compromiso y contribuciones han tenido un impacto significativo en nuestro equipo. 
                    Este reconocimiento refleja nuestro agradecimiento por tu esfuerzo y excelencia constante.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Detalles del Premio</div>

                    <div>🏅 <strong>Tipo de premio:</strong> {award_type}</div>
                    <div>📅 <strong>Fecha del premio:</strong> {award_date}</div>

                    </div>

                    <p>
                    Agradecemos sinceramente tu dedicación y la energía positiva que aportas a <strong>{company_name}</strong>. 
                    ¡Felicidades nuevamente por este logro tan merecido!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver en {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Saludos cordiales,<br>
                    <strong>Equipo de {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Félicitations {employee_name} !
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Bonjour <strong>{employee_name}</strong>,</p>

                    <p>
                    Nous sommes ravis de vous informer que vous avez reçu la récompense 
                    <strong>{award_type}</strong> chez <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Votre dévouement, votre engagement et vos contributions ont eu un impact significatif sur notre équipe. 
                    Cette reconnaissance reflète notre appréciation pour vos efforts et votre excellence constante.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Détails de la récompense</div>

                    <div>🏅 <strong>Type de récompense :</strong> {award_type}</div>
                    <div>📅 <strong>Date de la récompense :</strong> {award_date}</div>

                    </div>

                    <p>
                    Nous apprécions sincèrement votre dévouement et l\énergie positive que vous apportez à <strong>{company_name}</strong>. 
                    Encore félicitations pour cette réussite bien méritée !
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Voir dans {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Cordialement,<br>
                    <strong>Équipe {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Congratulazioni {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Ciao <strong>{employee_name}</strong>,</p>

                    <p>
                    Siamo lieti di informarti che hai ricevuto il premio 
                    <strong>{award_type}</strong> presso <strong>{company_name}</strong>.
                    </p>

                    <p>
                    La tua dedizione, il tuo impegno e i tuoi contributi hanno avuto un impatto significativo sul nostro team. 
                    Questo riconoscimento riflette il nostro apprezzamento per il tuo impegno e la tua eccellenza costante.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Dettagli del premio</div>

                    <div>🏅 <strong>Tipo di premio:</strong> {award_type}</div>
                    <div>📅 <strong>Data del premio:</strong> {award_date}</div>

                    </div>

                    <p>
                    Apprezziamo davvero la tua dedizione e l’energia positiva che porti a <strong>{company_name}</strong>. 
                    Congratulazioni ancora per questo meritato riconoscimento!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Visualizza in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Cordiali saluti,<br>
                    <strong>Team {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 おめでとうございます {employee_name} さん！
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>こんにちは <strong>{employee_name}</strong> さん、</p>

                    <p>
                    <strong>{company_name}</strong> にて、<strong>{award_type}</strong> を受賞されたことをお知らせいたします。
                    </p>

                    <p>
                    あなたの献身、努力、そしてチームへの貢献は大きな影響を与えています。  
                    この表彰は、あなたの継続的な努力と優れた成果に対する感謝の気持ちを表しています。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">賞の詳細</div>

                    <div>🏅 <strong>賞の種類:</strong> {award_type}</div>
                    <div>📅 <strong>受賞日:</strong> {award_date}</div>

                    </div>

                    <p>
                    <strong>{company_name}</strong> にもたらしてくれる前向きなエネルギーと努力に心より感謝します。  
                    この素晴らしい成果に改めてお祝い申し上げます！
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    {app_name} で表示
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    よろしくお願いいたします。<br>
                    <strong>{app_name} チーム</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Gefeliciteerd {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hallo <strong>{employee_name}</strong>,</p>

                    <p>
                    We zijn verheugd je te informeren dat je de 
                    <strong>{award_type}</strong> prijs hebt ontvangen bij <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Je toewijding, inzet en bijdragen hebben een grote impact gehad op ons team. 
                    Deze erkenning weerspiegelt onze waardering voor je inspanningen en voortdurende excellentie.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Prijsdetails</div>

                    <div>🏅 <strong>Type prijs:</strong> {award_type}</div>
                    <div>📅 <strong>Datum van de prijs:</strong> {award_date}</div>

                    </div>

                    <p>
                    We waarderen je toewijding en de positieve energie die je naar <strong>{company_name}</strong> brengt enorm. 
                    Nogmaals gefeliciteerd met deze welverdiende prestatie!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Bekijken in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Met vriendelijke groet,<br>
                    <strong>{app_name} Team</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Gratulacje {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Witaj <strong>{employee_name}</strong>,</p>

                    <p>
                    Z przyjemnością informujemy, że otrzymałeś nagrodę 
                    <strong>{award_type}</strong> w <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Twoje zaangażowanie, poświęcenie oraz wkład w pracę zespołu mają ogromne znaczenie. 
                    To wyróżnienie jest wyrazem naszego uznania dla Twojego wysiłku i ciągłej doskonałości.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Szczegóły nagrody</div>

                    <div>🏅 <strong>Rodzaj nagrody:</strong> {award_type}</div>
                    <div>📅 <strong>Data nagrody:</strong> {award_date}</div>

                    </div>

                    <p>
                    Naprawdę doceniamy Twoje zaangażowanie oraz pozytywną energię, którą wnosisz do <strong>{company_name}</strong>. 
                    Jeszcze raz gratulujemy tego w pełni zasłużonego osiągnięcia!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Zobacz w {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Z poważaniem,<br>
                    <strong>Zespół {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Поздравляем {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Здравствуйте, <strong>{employee_name}</strong>,</p>

                    <p>
                    Мы рады сообщить, что вы были награждены премией 
                    <strong>{award_type}</strong> в компании <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Ваша преданность делу, усердие и вклад оказали значительное влияние на нашу команду. 
                    Это признание отражает нашу благодарность за ваши усилия и постоянное стремление к совершенству.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Детали награды</div>

                    <div>🏅 <strong>Тип награды:</strong> {award_type}</div>
                    <div>📅 <strong>Дата награды:</strong> {award_date}</div>

                    </div>

                    <p>
                    Мы искренне ценим вашу преданность и позитивную энергию, которую вы приносите в <strong>{company_name}</strong>. 
                    Еще раз поздравляем с этим заслуженным достижением!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Открыть в {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    С уважением,<br>
                    <strong>Команда {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Parabéns {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Olá <strong>{employee_name}</strong>,</p>

                    <p>
                    Temos o prazer de informar que você recebeu o prêmio 
                    <strong>{award_type}</strong> na <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Sua dedicação, comprometimento e contribuições tiveram um impacto significativo em nossa equipe. 
                    Este reconhecimento reflete nossa gratidão pelo seu esforço e excelência contínua.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Detalhes do prêmio</div>

                    <div>🏅 <strong>Tipo de prêmio:</strong> {award_type}</div>
                    <div>📅 <strong>Data do prêmio:</strong> {award_date}</div>

                    </div>

                    <p>
                    Agradecemos sinceramente sua dedicação e a energia positiva que você traz para <strong>{company_name}</strong>. 
                    Parabéns novamente por esta conquista tão merecida!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver no {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Atenciosamente,<br>
                    <strong>Equipe {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Parabéns {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Olá <strong>{employee_name}</strong>,</p>

                    <p>
                    Temos o prazer de informar que você recebeu o prêmio 
                    <strong>{award_type}</strong> na <strong>{company_name}</strong>.
                    </p>

                    <p>
                    Sua dedicação, comprometimento e contribuições tiveram um impacto significativo em nossa equipe. 
                    Este reconhecimento reflete nossa gratidão pelo seu esforço e excelência contínua.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Detalhes do prêmio</div>

                    <div>🏅 <strong>Tipo de prêmio:</strong> {award_type}</div>
                    <div>📅 <strong>Data do prêmio:</strong> {award_date}</div>

                    </div>

                    <p>
                    Agradecemos sinceramente sua dedicação e a energia positiva que você traz para <strong>{company_name}</strong>. 
                    Parabéns novamente por esta conquista tão merecida!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver no {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Atenciosamente,<br>
                    <strong>Equipe {app_name}</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 Tebrikler {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Merhaba <strong>{employee_name}</strong>,</p>

                    <p>
                    <strong>{company_name}</strong> bünyesinde <strong>{award_type}</strong> ödülünü kazandığınızı bildirmekten mutluluk duyuyoruz.
                    </p>

                    <p>
                    Gösterdiğiniz özveri, bağlılık ve katkılar ekibimiz üzerinde önemli bir etki yarattı. 
                    Bu ödül, sürekli gösterdiğiniz çaba ve mükemmelliğe duyduğumuz takdirin bir göstergesidir.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">Ödül Detayları</div>

                    <div>🏅 <strong>Ödül Türü:</strong> {award_type}</div>
                    <div>📅 <strong>Ödül Tarihi:</strong> {award_date}</div>

                    </div>

                    <p>
                    <strong>{company_name}</strong> için gösterdiğiniz özveri ve getirdiğiniz pozitif enerji için gerçekten minnettarız. 
                    Bu hak edilmiş başarı için tekrar tebrikler!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    {app_name} içinde görüntüle
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Saygılarımızla,<br>
                    <strong>{app_name} Ekibi</strong>
                    </p>

                    </div>
                    </div>
                    </div>',

                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 恭喜 {employee_name}！
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>您好 <strong>{employee_name}</strong>，</p>

                    <p>
                    我们很高兴地通知您，您在 <strong>{company_name}</strong> 获得了 <strong>{award_type}</strong> 奖项。
                    </p>

                    <p>
                    您的奉献精神、努力工作以及对团队的贡献产生了重要影响。  
                    这一表彰体现了我们对您持续努力和卓越表现的认可与感谢。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">奖项详情</div>

                    <div>🏅 <strong>奖项类型：</strong> {award_type}</div>
                    <div>📅 <strong>获奖日期：</strong> {award_date}</div>

                    </div>

                    <p>
                    我们真诚感谢您为 <strong>{company_name}</strong> 带来的奉献和积极能量。  
                    再次祝贺您获得这一实至名归的成就！
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    在 {app_name} 中查看
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    此致敬礼，<br>
                    <strong>{app_name} 团队</strong>
                    </p>

                    </div>
                    </div>
                    </div>',


                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">

                    <div style="background:#4f46e5;color:#ffffff;padding:28px 30px;font-size:22px;font-weight:600;">
                    🏆 מזל טוב {employee_name}!
                    </div>

                    <div style="padding:32px 30px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>שלום <strong>{employee_name}</strong>,</p>

                    <p>
                    אנו שמחים להודיע לך כי זכית בפרס 
                    <strong>{award_type}</strong> ב<strong>{company_name}</strong>.
                    </p>

                    <p>
                    המסירות, המחויבות והתרומה שלך השפיעו רבות על הצוות שלנו.  
                    הכרה זו משקפת את הערכתנו למאמציך ולהצטיינות המתמשכת שלך.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:10px;padding:18px 20px;margin:25px 0;">
                    <div style="font-weight:600;margin-bottom:10px;color:#4f46e5;">פרטי הפרס</div>

                    <div>🏅 <strong>סוג הפרס:</strong> {award_type}</div>
                    <div>📅 <strong>תאריך הפרס:</strong> {award_date}</div>

                    </div>

                    <p>
                    אנו מעריכים מאוד את המסירות והאנרגיה החיובית שאתה מביא ל<strong>{company_name}</strong>.  
                    שוב מזל טוב על ההישג הראוי הזה!
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:500;display:inline-block;">
                    צפה ב {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    בברכה,<br>
                    <strong>צוות {app_name}</strong>
                    </p>
                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Promotions Approval' => [
                'subject' => 'Promotions Approved',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Employee Name ":"employee_name",
                    "Previous Branch Name": "previous_branch_name",
                    "Previous Department Name": "previous_department_name",
                    "Previous Designation Name": "previous_designation_name",
                    "Current Branch Name": "current_branch_name",
                    "Current Department Name": "current_department_name",
                    "Current Designation Name": "current_designation_name",
                    "Effective Date": "effective_date",
                    "Reason": "reason"
                }',
                'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 تهانينا على ترقيتك!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>مرحبًا، <strong>{employee_name}</strong></p>

                    <p>
                    يسعدنا أن نعلن عن إنجاز مهم في مسيرتك المهنية في <strong>{company_name}</strong>.
                    إن تفانيك والتزامك ومساهماتك المميزة قد أكسبتك هذه الترقية المستحقة.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    تفاصيل الترقية
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>الفرع السابق:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>القسم السابق:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>المسمى الوظيفي السابق:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>الفرع الجديد:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>القسم الجديد:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>المسمى الوظيفي الجديد:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>تاريخ السريان:</strong> {effective_date}</div>
                    <div>📝 <strong>سبب الترقية:</strong> {reason}</div>

                    </div>

                    <p>
                    تعكس هذه الترقية الثقة الكبيرة التي تضعها <strong>{company_name}</strong> في قدراتك.
                    نحن نقدر حقًا تفانيك والقيمة التي تضيفها إلى مؤسستنا.
                    </p>

                    <p>
                    نحن واثقون أنك ستواصل تحقيق المزيد من النجاحات وإلهام من حولك في دورك الجديد.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    عرض في {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    نتمنى لك المزيد من النجاح في منصبك الجديد.<br><br>

                    مع أطيب التحيات،<br> <strong>فريق {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Tillykke med din forfremmelse!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hej, <strong>{employee_name}</strong></p>

                    <p>
                    Vi er glade for at kunne annoncere en spændende milepæl i din karriere hos <strong>{company_name}</strong>.
                    Din dedikation, engagement og fremragende bidrag har givet dig denne velfortjente forfremmelse.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Forfremmelsesdetaljer
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Tidligere afdeling:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Tidligere afdeling:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Tidligere stilling:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Ny afdeling:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Ny afdeling:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Ny stilling:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Ikrafttrædelsesdato:</strong> {effective_date}</div>
                    <div>📝 <strong>Årsag til forfremmelse:</strong> {reason}</div>

                    </div>

                    <p>
                    Denne forfremmelse afspejler den tillid, som <strong>{company_name}</strong> har til dine evner.
                    Vi værdsætter virkelig din indsats og den værdi, du bringer til vores organisation.
                    </p>

                    <p>
                    Vi er sikre på, at du fortsat vil opnå stor succes og inspirere andre i din nye rolle.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Se i {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Vi ønsker dig fortsat succes i din nye stilling.<br><br>

                    Med venlig hilsen,<br> <strong>{app_name} Team</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Herzlichen Glückwunsch zu Ihrer Beförderung!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hallo, <strong>{employee_name}</strong></p>

                    <p>
                    Wir freuen uns, einen wichtigen Meilenstein in Ihrer beruflichen Laufbahn bei <strong>{company_name}</strong> bekannt zu geben.
                    Ihr Engagement und Ihre hervorragenden Leistungen haben Ihnen diese wohlverdiente Beförderung eingebracht.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Beförderungsdetails
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Vorherige Filiale:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Vorherige Abteilung:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Vorherige Position:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Neue Filiale:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Neue Abteilung:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Neue Position:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Gültig ab:</strong> {effective_date}</div>
                    <div>📝 <strong>Grund der Beförderung:</strong> {reason}</div>

                    </div>

                    <p>
                    Diese Beförderung spiegelt das Vertrauen wider, das <strong>{company_name}</strong> in Ihre Fähigkeiten hat.
                    Wir schätzen Ihr Engagement und Ihren Beitrag zu unserem Unternehmen sehr.
                    </p>

                    <p>
                    Wir sind überzeugt, dass Sie auch in Ihrer neuen Rolle weiterhin große Erfolge erzielen werden.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    In {app_name} anzeigen
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Wir wünschen Ihnen weiterhin viel Erfolg in Ihrer neuen Position.<br><br>

                    Mit freundlichen Grüßen,<br> <strong>{app_name} Team</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Congratulations on Your Promotion!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hello,<strong>{employee_name}</strong></p>

                    <p>
                    We are delighted to announce an exciting milestone in your professional journey at <strong>{company_name}</strong>.
                    Your dedication, commitment, and outstanding contributions have earned you a well-deserved promotion.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Promotion Details
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Previous Branch:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Previous Department:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Previous Designation:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>New Branch:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>New Department:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>New Designation:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Effective Date:</strong> {effective_date}</div>
                    <div>📝 <strong>Reason for Promotion:</strong> {reason}</div>

                    </div>

                    <p>
                    This promotion reflects the trust and confidence that <strong>{company_name}</strong> has in your abilities.
                    We truly appreciate your dedication and the value you bring to our organization.
                    </p>

                    <p>
                    We are confident that you will continue to achieve great success and inspire those around you in your new role.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    View in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Wishing you continued success in your new position.<br><br>

                    Best regards,<br> <strong>{app_name} Team</strong>

                    </p>

                    </div>
                    </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 ¡Felicidades por tu promoción!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hola, <strong>{employee_name}</strong></p>

                    <p>
                    Nos complace anunciar un importante logro en tu trayectoria profesional en <strong>{company_name}</strong>.
                    Tu dedicación, compromiso y excelentes contribuciones te han hecho merecedor de esta promoción.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Detalles de la promoción
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Sucursal anterior:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Departamento anterior:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Puesto anterior:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nueva sucursal:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Nuevo departamento:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Nuevo puesto:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Fecha efectiva:</strong> {effective_date}</div>
                    <div>📝 <strong>Motivo de la promoción:</strong> {reason}</div>

                    </div>

                    <p>
                    Esta promoción refleja la confianza que <strong>{company_name}</strong> tiene en tus habilidades.
                    Agradecemos sinceramente tu dedicación y el valor que aportas a nuestra organización.
                    </p>

                    <p>
                    Estamos seguros de que continuarás logrando grandes éxitos e inspirando a otros en tu nuevo rol.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver en {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Te deseamos mucho éxito en tu nuevo puesto.<br><br>

                    Saludos cordiales,<br> <strong>Equipo {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Félicitations pour votre promotion !
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Bonjour, <strong>{employee_name}</strong></p>

                    <p>
                    Nous sommes ravis d’annoncer une étape importante dans votre parcours professionnel chez <strong>{company_name}</strong>.
                    Votre dévouement, votre engagement et vos contributions remarquables vous ont valu cette promotion bien méritée.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Détails de la promotion
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Ancienne agence :</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Ancien département :</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Ancien poste :</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nouvelle agence :</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Nouveau département :</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Nouveau poste :</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Date d\'entrée en vigueur :</strong> {effective_date}</div>
                    <div>📝 <strong>Raison de la promotion :</strong> {reason}</div>

                    </div>

                    <p>
                    Cette promotion reflète la confiance que <strong>{company_name}</strong> accorde à vos compétences.
                    Nous apprécions sincèrement votre dévouement et la valeur que vous apportez à notre organisation.
                    </p>

                    <p>
                    Nous sommes convaincus que vous continuerez à connaître de grands succès et à inspirer ceux qui vous entourent dans votre nouveau rôle.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Voir dans {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Nous vous souhaitons beaucoup de succès dans votre nouveau poste.<br><br>

                    Cordialement,<br> <strong>Équipe {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Congratulazioni per la tua promozione!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Ciao, <strong>{employee_name}</strong></p>

                    <p>
                    Siamo lieti di annunciare un importante traguardo nel tuo percorso professionale presso <strong>{company_name}</strong>.
                    La tua dedizione, il tuo impegno e i tuoi eccellenti contributi ti hanno fatto guadagnare questa meritata promozione.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Dettagli della promozione
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Filiale precedente:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Dipartimento precedente:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Ruolo precedente:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nuova filiale:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Nuovo dipartimento:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Nuovo ruolo:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Data effettiva:</strong> {effective_date}</div>
                    <div>📝 <strong>Motivo della promozione:</strong> {reason}</div>

                    </div>

                    <p>
                    Questa promozione riflette la fiducia che <strong>{company_name}</strong> ripone nelle tue capacità.
                    Apprezziamo sinceramente la tua dedizione e il valore che apporti alla nostra organizzazione.
                    </p>

                    <p>
                    Siamo certi che continuerai a raggiungere grandi successi e a ispirare chi ti circonda nel tuo nuovo ruolo.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Visualizza in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Ti auguriamo grande successo nel tuo nuovo ruolo.<br><br>

                    Cordiali saluti,<br> <strong>Team {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 ご昇進おめでとうございます！
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>こんにちは、<strong>{employee_name}</strong> さん</p>

                    <p>
                    <strong>{company_name}</strong> におけるあなたのキャリアにおいて、重要な節目を迎えられたことをお知らせできることを嬉しく思います。
                    あなたの献身、努力、そして優れた貢献が認められ、この度の昇進につながりました。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    昇進の詳細
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>以前の支店:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>以前の部署:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>以前の役職:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>新しい支店:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>新しい部署:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>新しい役職:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>発効日:</strong> {effective_date}</div>
                    <div>📝 <strong>昇進理由:</strong> {reason}</div>

                    </div>

                    <p>
                    この昇進は、<strong>{company_name}</strong> があなたの能力を高く評価し信頼している証です。
                    組織にもたらしてくださる価値とご尽力に心より感謝いたします。
                    </p>

                    <p>
                    新しい役職においてもさらなる成功を収め、周囲に良い影響を与えてくださることを確信しています。
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    {app_name} で表示
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    新しい役職でのさらなるご成功をお祈りいたします。<br><br>

                    敬具<br> <strong>{app_name} チーム</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Gefeliciteerd met je promotie!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Hallo, <strong>{employee_name}</strong></p>

                    <p>
                    We zijn verheugd een belangrijke mijlpaal in jouw professionele reis bij <strong>{company_name}</strong> aan te kondigen.
                    Jouw toewijding, inzet en uitstekende bijdragen hebben geleid tot deze welverdiende promotie.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Promotiedetails
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Vorige vestiging:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Vorige afdeling:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Vorige functie:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nieuwe vestiging:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Nieuwe afdeling:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Nieuwe functie:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Ingangsdatum:</strong> {effective_date}</div>
                    <div>📝 <strong>Reden voor promotie:</strong> {reason}</div>

                    </div>

                    <p>
                    Deze promotie weerspiegelt het vertrouwen dat <strong>{company_name}</strong> heeft in jouw vaardigheden.
                    Wij waarderen je inzet en de waarde die je toevoegt aan onze organisatie.
                    </p>

                    <p>
                    We zijn ervan overtuigd dat je veel succes zult behalen en anderen zult inspireren in je nieuwe rol.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Bekijken in {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Wij wensen je veel succes in je nieuwe functie.<br><br>

                    Met vriendelijke groet,<br> <strong>{app_name} Team</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Gratulacje z okazji awansu!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Witaj, <strong>{employee_name}</strong></p>

                    <p>
                    Z przyjemnością ogłaszamy ważny etap w Twojej karierze zawodowej w <strong>{company_name}</strong>.
                    Twoje zaangażowanie, wysiłek oraz znakomite osiągnięcia zostały docenione i zaowocowały zasłużonym awansem.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Szczegóły awansu
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Poprzedni oddział:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Poprzedni dział:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Poprzednie stanowisko:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nowy oddział:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Nowy dział:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Nowe stanowisko:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Data wejścia w życie:</strong> {effective_date}</div>
                    <div>📝 <strong>Powód awansu:</strong> {reason}</div>

                    </div>

                    <p>
                    Ten awans odzwierciedla zaufanie, jakim <strong>{company_name}</strong> darzy Twoje umiejętności.
                    Doceniamy Twoje zaangażowanie oraz wartość, jaką wnosisz do naszej organizacji.
                    </p>

                    <p>
                    Jesteśmy przekonani, że w nowej roli osiągniesz jeszcze większe sukcesy i będziesz inspiracją dla innych.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Zobacz w {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Życzymy Ci dalszych sukcesów na nowym stanowisku.<br><br>

                    Z poważaniem,<br> <strong>Zespół {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Parabéns pela sua promoção!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Olá, <strong>{employee_name}</strong></p>

                    <p>
                    Temos o prazer de anunciar um marco importante na sua jornada profissional na <strong>{company_name}</strong>.
                    Sua dedicação, comprometimento e excelentes contribuições resultaram nesta promoção mais do que merecida.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Detalhes da promoção
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Filial anterior:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Departamento anterior:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Cargo anterior:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nova filial:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Novo departamento:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Novo cargo:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Data de vigência:</strong> {effective_date}</div>
                    <div>📝 <strong>Motivo da promoção:</strong> {reason}</div>

                    </div>

                    <p>
                    Esta promoção reflete a confiança que a <strong>{company_name}</strong> deposita em suas habilidades.
                    Agradecemos sinceramente sua dedicação e o valor que você traz para nossa organização.
                    </p>

                    <p>
                    Temos certeza de que você continuará alcançando grandes conquistas e inspirando aqueles ao seu redor em sua nova função.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver em {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Desejamos muito sucesso em sua nova função.<br><br>

                    Atenciosamente,<br> <strong>Equipe {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Parabéns pela sua promoção!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Olá, <strong>{employee_name}</strong></p>

                    <p>
                    Temos o prazer de anunciar um marco importante na sua jornada profissional na <strong>{company_name}</strong>.
                    Sua dedicação, comprometimento e excelentes contribuições resultaram nesta promoção mais do que merecida.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Detalhes da promoção
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Filial anterior:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Departamento anterior:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Cargo anterior:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Nova filial:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Novo departamento:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Novo cargo:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Data de vigência:</strong> {effective_date}</div>
                    <div>📝 <strong>Motivo da promoção:</strong> {reason}</div>

                    </div>

                    <p>
                    Esta promoção reflete a confiança que a <strong>{company_name}</strong> deposita em suas habilidades.
                    Agradecemos sinceramente sua dedicação e o valor que você traz para nossa organização.
                    </p>

                    <p>
                    Temos certeza de que você continuará alcançando grandes conquistas e inspirando aqueles ao seu redor em sua nova função.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" 
                    style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Ver em {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Desejamos muito sucesso em sua nova função.<br><br>

                    Atenciosamente,<br> <strong>Equipe {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Поздравляем с повышением!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Здравствуйте, <strong>{employee_name}</strong></p>

                    <p>
                    Мы рады объявить о важной вехе в вашей профессиональной карьере в <strong>{company_name}</strong>.
                    Ваше усердие, преданность делу и выдающийся вклад привели к этому заслуженному повышению.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Детали повышения
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Предыдущий филиал:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Предыдущий отдел:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Предыдущая должность:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Новый филиал:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Новый отдел:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Новая должность:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Дата вступления в силу:</strong> {effective_date}</div>
                    <div>📝 <strong>Причина повышения:</strong> {reason}</div>

                    </div>

                    <p>
                    Это повышение отражает доверие, которое <strong>{company_name}</strong> оказывает вашим навыкам.
                    Мы искренне ценим вашу преданность и вклад в развитие нашей организации.
                    </p>

                    <p>
                    Мы уверены, что на новой должности вы добьётесь ещё больших успехов и будете вдохновлять окружающих.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    Открыть в {app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Желаем вам дальнейших успехов на новой должности.<br><br>

                    С уважением,<br> <strong>Команда {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 ברכות על הקידום שלך!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>שלום, <strong>{employee_name}</strong></p>

                    <p>
                    אנו שמחים להודיע על אבן דרך חשובה במסע המקצועי שלך ב-<strong>{company_name}</strong>.
                    המסירות, המחויבות והתרומה המשמעותית שלך הובילו לקידום המגיע לך בהחלט.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    פרטי הקידום
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>סניף קודם:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>מחלקה קודמת:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>תפקיד קודם:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>סניף חדש:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>מחלקה חדשה:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>תפקיד חדש:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>תאריך כניסה לתוקף:</strong> {effective_date}</div>
                    <div>📝 <strong>סיבת הקידום:</strong> {reason}</div>

                    </div>

                    <p>
                    קידום זה משקף את האמון ש-<strong>{company_name}</strong> נותנת ביכולות שלך.
                    אנו מעריכים מאוד את המסירות והערך שאתה מביא לארגון.
                    </p>

                    <p>
                    אנו בטוחים שתמשיך להצליח ולהוות השראה לאחרים בתפקידך החדש.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    צפה ב-{app_name}
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    מאחלים לך הצלחה רבה בתפקידך החדש.<br><br>

                    בברכה,<br> <strong>צוות {app_name}</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                                        'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 Terfiniz Kutlu Olsun!
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>Merhaba, <strong>{employee_name}</strong></p>

                    <p>
                    <strong>{company_name}</strong> bünyesindeki profesyonel yolculuğunuzda önemli bir kilometre taşını duyurmaktan mutluluk duyuyoruz.
                    Gösterdiğiniz özveri, bağlılık ve üstün katkılarınız bu hak edilmiş terfi ile ödüllendirildi.
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    Terfi Detayları
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>Önceki Şube:</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Önceki Departman:</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>Önceki Pozisyon:</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>Yeni Şube:</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>Yeni Departman:</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>Yeni Pozisyon:</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>Yürürlük Tarihi:</strong> {effective_date}</div>
                    <div>📝 <strong>Terfi Nedeni:</strong> {reason}</div>

                    </div>

                    <p>
                    Bu terfi, <strong>{company_name}</strong> şirketinin yeteneklerinize duyduğu güveni göstermektedir.
                    Kuruluşumuza kattığınız değer ve özveriniz için teşekkür ederiz.
                    </p>

                    <p>
                    Yeni görevinizde büyük başarılara ulaşacağınıza ve çevrenize ilham vermeye devam edeceğinize inanıyoruz.
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    {app_name} içinde görüntüle
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    Yeni görevinizde başarılar dileriz.<br><br>

                    Saygılarımızla,<br> <strong>{app_name} Ekibi</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">

                    <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.05);">

                    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;padding:30px 35px;font-size:24px;font-weight:600;">
                    🚀 恭喜您获得晋升！
                    </div>

                    <div style="padding:35px;color:#333333;font-size:15px;line-height:1.7;">

                    <p>您好，<strong>{employee_name}</strong></p>

                    <p>
                    我们很高兴地宣布您在 <strong>{company_name}</strong> 职业发展中的一个重要里程碑。
                    您的敬业精神、努力付出以及卓越贡献使您获得了这次实至名归的晋升。
                    </p>

                    <div style="background:#f8f9ff;border:1px solid #e6e8f0;border-radius:12px;padding:22px;margin:25px 0;">

                    <div style="font-weight:600;font-size:16px;color:#4f46e5;margin-bottom:12px;">
                    晋升详情
                    </div>

                    <div style="margin-bottom:6px;">🏢 <strong>之前的分支机构：</strong> {previous_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>之前的部门：</strong> {previous_department_name}</div>
                    <div style="margin-bottom:6px;">💼 <strong>之前的职位：</strong> {previous_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📍 <strong>新的分支机构：</strong> {current_branch_name}</div>
                    <div style="margin-bottom:6px;">🏬 <strong>新的部门：</strong> {current_department_name}</div>
                    <div style="margin-bottom:6px;">⭐ <strong>新的职位：</strong> {current_designation_name}</div>

                    <hr style="border:none;border-top:1px solid #e6e8f0;margin:15px 0;">

                    <div style="margin-bottom:6px;">📅 <strong>生效日期：</strong> {effective_date}</div>
                    <div>📝 <strong>晋升原因：</strong> {reason}</div>

                    </div>

                    <p>
                    这次晋升体现了 <strong>{company_name}</strong> 对您能力的信任。
                    我们真诚感谢您的付出以及为组织带来的价值。
                    </p>

                    <p>
                    我们相信您将在新的岗位上继续取得更大的成功，并激励身边的同事。
                    </p>

                    <div style="margin:30px 0;text-align:center;">
                    <a href="{app_url}" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:14px 30px;border-radius:8px;font-weight:500;display:inline-block;">
                    在 {app_name} 中查看
                    </a>
                    </div>

                    <p style="margin-top:30px;">
                    祝您在新的职位上取得更大的成功。<br><br>

                    此致敬礼，<br> <strong>{app_name} 团队</strong>

                    </p>

                    </div>
                    </div>
                    </div>',
                ],
            ],
            'Resignations Status' => [
                'subject' => 'Resignations Status Updated',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Employee Name ":"employee_name",
                    "Last Working Date": "last_working_date",
                    "Reason": "reason",
                    "Status": "status"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - تحديث حالة الاستقالة
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>مرحبًا <strong>{employee_name}</strong>،</p>

                        <p>
                            تمت مراجعة طلب الاستقالة الخاص بك في <strong>{company_name}</strong> من قبل الإدارة.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>الحالة:</strong> {status}
                        </p>

                        <p>
                            <strong>آخر يوم عمل:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>السبب / التعليق:</strong> {reason}
                        </p>

                        <p>
                            يرجى تسجيل الدخول إلى التطبيق إذا كنت بحاجة إلى مزيد من التفاصيل أو التحديثات.
                        </p>

                        <p style="margin-top:25px;">
                            مع التحية،<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        يمكنك الوصول إلى حسابك هنا:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Opdatering af opsigelsesstatus
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hej <strong>{employee_name}</strong>,</p>

                        <p>
                            Din opsigelsesanmodning hos <strong>{company_name}</strong> er blevet gennemgået af ledelsen.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Sidste arbejdsdag:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Årsag / Kommentar:</strong> {reason}
                        </p>

                        <p>
                            Log venligst ind i applikationen, hvis du har brug for flere detaljer eller opdateringer.
                        </p>

                        <p style="margin-top:25px;">
                            Med venlig hilsen,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Få adgang til din konto her:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aktualisierung des Kündigungsstatus
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>
                            Ihr Kündigungsantrag bei <strong>{company_name}</strong> wurde von der Geschäftsleitung geprüft.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Letzter Arbeitstag:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Grund / Kommentar:</strong> {reason}
                        </p>

                        <p>
                            Bitte melden Sie sich in der Anwendung an, wenn Sie weitere Details oder Updates benötigen.
                        </p>

                        <p style="margin-top:25px;">
                            Mit freundlichen Grüßen,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Greifen Sie hier auf Ihr Konto zu:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Resignation Status Update
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hello <strong>{employee_name}</strong>,</p>

                        <p>
                            Your resignation request at <strong>{company_name}</strong> has been reviewed by the management.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Last Working Date:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Reason / Comment:</strong> {reason}
                        </p>

                        <p>
                            Please login to the application if you need more details or updates.
                        </p>

                        <p style="margin-top:25px;">
                            Regards,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Access your account here:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Actualización del estado de la renuncia
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hola <strong>{employee_name}</strong>,</p>

                        <p>
                            Su solicitud de renuncia en <strong>{company_name}</strong> ha sido revisada por la administración.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Estado:</strong> {status}
                        </p>

                        <p>
                            <strong>Último día de trabajo:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Motivo / Comentario:</strong> {reason}
                        </p>

                        <p>
                            Por favor, inicie sesión en la aplicación si necesita más detalles o actualizaciones.
                        </p>

                        <p style="margin-top:25px;">
                            Saludos,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acceda a su cuenta aquí:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Mise à jour du statut de démission
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Bonjour <strong>{employee_name}</strong>,</p>

                        <p>
                            Votre demande de démission chez <strong>{company_name}</strong> a été examinée par la direction.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Statut :</strong> {status}
                        </p>

                        <p>
                            <strong>Dernier jour de travail :</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Raison / Commentaire :</strong> {reason}
                        </p>

                        <p>
                            Veuillez vous connecter à l’application si vous avez besoin de plus de détails ou de mises à jour.
                        </p>

                        <p style="margin-top:25px;">
                            Cordialement,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Accédez à votre compte ici :<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aggiornamento dello stato delle dimissioni
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Ciao <strong>{employee_name}</strong>,</p>

                        <p>
                            La tua richiesta di dimissioni presso <strong>{company_name}</strong> è stata esaminata dalla direzione.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Stato:</strong> {status}
                        </p>

                        <p>
                            <strong>Ultimo giorno lavorativo:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Motivo / Commento:</strong> {reason}
                        </p>

                        <p>
                            Accedi all’applicazione se hai bisogno di ulteriori dettagli o aggiornamenti.
                        </p>

                        <p style="margin-top:25px;">
                            Cordiali saluti,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Accedi al tuo account qui:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - 退職ステータスの更新
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>こんにちは <strong>{employee_name}</strong> さん、</p>

                        <p>
                            <strong>{company_name}</strong> に提出された退職申請は、管理部門によって確認されました。
                        </p>

                        <p style="margin:18px 0;">
                            <strong>ステータス:</strong> {status}
                        </p>

                        <p>
                            <strong>最終勤務日:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>理由 / コメント:</strong> {reason}
                        </p>

                        <p>
                            詳細や更新情報が必要な場合は、アプリケーションにログインしてください。
                        </p>

                        <p style="margin-top:25px;">
                            よろしくお願いいたします。<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        こちらからアカウントにアクセスできます:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Update van de ontslagstatus
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>
                            Uw ontslagverzoek bij <strong>{company_name}</strong> is door het management beoordeeld.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Laatste werkdag:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Reden / Opmerking:</strong> {reason}
                        </p>

                        <p>
                            Log in op de applicatie als u meer details of updates nodig heeft.
                        </p>

                        <p style="margin-top:25px;">
                            Met vriendelijke groet,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Ga hier naar uw account:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aktualizacja statusu rezygnacji
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Witaj <strong>{employee_name}</strong>,</p>

                        <p>
                            Twoja prośba o rezygnację w <strong>{company_name}</strong> została sprawdzona przez kierownictwo.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Ostatni dzień pracy:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Powód / Komentarz:</strong> {reason}
                        </p>

                        <p>
                            Zaloguj się do aplikacji, jeśli potrzebujesz więcej szczegółów lub aktualizacji.
                        </p>

                        <p style="margin-top:25px;">
                            Z poważaniem,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Uzyskaj dostęp do swojego konta tutaj:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Atualização do status de demissão
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>
                            Sua solicitação de demissão em <strong>{company_name}</strong> foi revisada pela administração.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Último dia de trabalho:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Motivo / Comentário:</strong> {reason}
                        </p>

                        <p>
                            Por favor, faça login no aplicativo se precisar de mais detalhes ou atualizações.
                        </p>

                        <p style="margin-top:25px;">
                            Atenciosamente,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acesse sua conta aqui:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Atualização do status de demissão
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>
                            Sua solicitação de demissão em <strong>{company_name}</strong> foi revisada pela administração.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Status:</strong> {status}
                        </p>

                        <p>
                            <strong>Último dia de trabalho:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Motivo / Comentário:</strong> {reason}
                        </p>

                        <p>
                            Por favor, faça login no aplicativo se precisar de mais detalhes ou atualizações.
                        </p>

                        <p style="margin-top:25px;">
                            Atenciosamente,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acesse sua conta aqui:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Обновление статуса увольнения
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Здравствуйте <strong>{employee_name}</strong>,</p>

                        <p>
                            Ваш запрос на увольнение в <strong>{company_name}</strong> был рассмотрен руководством.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Статус:</strong> {status}
                        </p>

                        <p>
                            <strong>Последний рабочий день:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Причина / Комментарий:</strong> {reason}
                        </p>

                        <p>
                            Пожалуйста, войдите в приложение, если вам нужны дополнительные детали или обновления.
                        </p>

                        <p style="margin-top:25px;">
                            С уважением,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Доступ к вашему аккаунту здесь:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - עדכון סטטוס התפטרות
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>שלום <strong>{employee_name}</strong>,</p>

                        <p>
                            בקשת ההתפטרות שלך ב-<strong>{company_name}</strong> נבדקה על ידי ההנהלה.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>סטטוס:</strong> {status}
                        </p>

                        <p>
                            <strong>יום העבודה האחרון:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>סיבה / הערה:</strong> {reason}
                        </p>

                        <p>
                            אנא התחבר לאפליקציה אם אתה צריך פרטים נוספים או עדכונים.
                        </p>

                        <p style="margin-top:25px;">
                            בברכה,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        גש לחשבון שלך כאן:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - İstifa Durumu Güncellemesi
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Merhaba <strong>{employee_name}</strong>,</p>

                        <p>
                            <strong>{company_name}</strong> şirketindeki istifa talebiniz yönetim tarafından incelenmiştir.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Durum:</strong> {status}
                        </p>

                        <p>
                            <strong>Son Çalışma Günü:</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>Neden / Yorum:</strong> {reason}
                        </p>

                        <p>
                            Daha fazla detay veya güncelleme için lütfen uygulamaya giriş yapın.
                        </p>

                        <p style="margin-top:25px;">
                            Saygılarımızla,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Hesabınıza buradan erişin:<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#4f46e5;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - 辞职状态更新
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>您好 <strong>{employee_name}</strong>，</p>

                        <p>
                            您在 <strong>{company_name}</strong> 提交的辞职申请已由管理层审核。
                        </p>

                        <p style="margin:18px 0;">
                            <strong>状态：</strong> {status}
                        </p>

                        <p>
                            <strong>最后工作日期：</strong> {last_working_date}
                        </p>

                        <p>
                            <strong>原因 / 备注：</strong> {reason}
                        </p>

                        <p>
                            如果您需要更多详细信息或更新，请登录应用程序。
                        </p>

                        <p style="margin-top:25px;">
                            此致敬礼，<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        在这里访问您的账户：<br>
                        <a href="{app_url}" style="color:#4f46e5;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                ],
            ],
            'Warning Approval' => [
                'subject' => 'Warning Approved',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Employee Name": "employee_name",
                    "Warning Type Name": "warning_type_name",
                    "Subject": "subject"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - تحديث إشعار التحذير
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>مرحبًا <strong>{employee_name}</strong>،</p>

                        <p>
                            نود إعلامك بأنه تم إصدار إشعار تحذير لك من قبل <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>نوع التحذير:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>الموضوع:</strong> {subject}
                        </p>

                        <p>
                            يرجى مراجعة هذا الإشعار بعناية وضمان الامتثال لسياسات وإرشادات الشركة في المستقبل.
                        </p>

                        <p>
                            إذا كنت بحاجة إلى أي توضيح، لا تتردد في الاتصال بقسم الموارد البشرية.
                        </p>

                        <p style="margin-top:25px;">
                            مع تحياتنا,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        قم بالوصول إلى حسابك هنا:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'da' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Advarselsmeddelelse Opdatering
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hej <strong>{employee_name}</strong>,</p>

                        <p>
                            Dette er for at informere dig om, at der er udstedt en advarselsmeddelelse til dig af <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Advarselstype:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Emne:</strong> {subject}
                        </p>

                        <p>
                            Gennemgå venligst denne meddelelse nøje og sørg for at overholde virksomhedens politikker og retningslinjer fremadrettet.
                        </p>

                        <p>
                            Hvis du har brug for yderligere afklaring, er du velkommen til at kontakte HR-afdelingen.
                        </p>

                        <p style="margin-top:25px;">
                            Venlig hilsen,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Få adgang til din konto her:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'de' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aktualisierung der Verwarnung
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>
                            Hiermit möchten wir Sie informieren, dass eine Verwarnung von <strong>{company_name}</strong> an Sie ausgestellt wurde.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Verwarnungsart:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Betreff:</strong> {subject}
                        </p>

                        <p>
                            Bitte überprüfen Sie diese Mitteilung sorgfältig und stellen Sie sicher, dass Sie die Unternehmensrichtlinien und Vorgaben künftig einhalten.
                        </p>

                        <p>
                            Wenn Sie eine Klärung benötigen, wenden Sie sich bitte an die Personalabteilung.
                        </p>

                        <p style="margin-top:25px;">
                            Mit freundlichen Grüßen,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Greifen Sie hier auf Ihr Konto zu:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Warning Notice Update
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hello <strong>{employee_name}</strong>,</p>

                        <p>
                            This is to inform you that a warning notice has been issued to you by <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Warning Type:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Subject:</strong> {subject}
                        </p>

                        <p>
                            Please review this notice carefully and ensure compliance with the company policies and guidelines moving forward.
                        </p>

                        <p>
                            If you require any clarification, please feel free to contact the HR department.
                        </p>

                        <p style="margin-top:25px;">
                            Regards,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Access your account here:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'es' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Actualización de Aviso de Advertencia
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hola <strong>{employee_name}</strong>,</p>

                        <p>
                            Le informamos que se le ha emitido un aviso de advertencia por parte de <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Tipo de Advertencia:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Asunto:</strong> {subject}
                        </p>

                        <p>
                            Revise este aviso cuidadosamente y asegúrese de cumplir con las políticas y directrices de la empresa en adelante.
                        </p>

                        <p>
                            Si necesita alguna aclaración, no dude en contactar con el departamento de Recursos Humanos.
                        </p>

                        <p style="margin-top:25px;">
                            Saludos,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acceda a su cuenta aquí:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Mise à jour de l’avertissement
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Bonjour <strong>{employee_name}</strong>,</p>

                        <p>
                            Nous vous informons qu’un avertissement a été émis à votre encontre par <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Type d’avertissement :</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Objet :</strong> {subject}
                        </p>

                        <p>
                            Veuillez examiner attentivement cet avertissement et vous assurer de respecter les politiques et directives de l’entreprise à l’avenir.
                        </p>

                        <p>
                            Si vous avez besoin de précisions, n’hésitez pas à contacter le service des ressources humaines.
                        </p>

                        <p style="margin-top:25px;">
                            Cordialement,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Accédez à votre compte ici :<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aggiornamento Avviso di Richiamo
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Ciao <strong>{employee_name}</strong>,</p>

                        <p>
                            Ti informiamo che un avviso di richiamo è stato emesso nei tuoi confronti da <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Tipo di avviso:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Oggetto:</strong> {subject}
                        </p>

                        <p>
                            Ti preghiamo di leggere attentamente questo avviso e di assicurarti di rispettare le politiche e le linee guida dell’azienda in futuro.
                        </p>

                        <p>
                            Se necessiti di chiarimenti, non esitare a contattare il reparto Risorse Umane.
                        </p>

                        <p style="margin-top:25px;">
                            Cordiali saluti,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Accedi al tuo account qui:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - 警告通知の更新
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>こんにちは <strong>{employee_name}</strong>、</p>

                        <p>
                            <strong>{company_name}</strong> より警告通知が発行されたことをお知らせします。
                        </p>

                        <p style="margin:18px 0;">
                            <strong>警告の種類：</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>件名：</strong> {subject}
                        </p>

                        <p>
                            この通知を注意深く確認し、今後は会社の方針およびガイドラインに従うようにしてください。
                        </p>

                        <p>
                            ご不明な点がございましたら、人事部までお気軽にお問い合わせください。
                        </p>

                        <p style="margin-top:25px;">
                            宜しくお願いいたします。<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        アカウントにアクセスする:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Waarschuwingsmelding Update
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>
                            Hierbij informeren wij u dat er een waarschuwingsmelding is uitgegeven door <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Waarschuwingssoort:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Onderwerp:</strong> {subject}
                        </p>

                        <p>
                            Lees deze melding zorgvuldig door en zorg ervoor dat u zich voortaan houdt aan het bedrijfsbeleid en de richtlijnen.
                        </p>

                        <p>
                            Indien u vragen heeft, neem dan gerust contact op met de HR-afdeling.
                        </p>

                        <p style="margin-top:25px;">
                            Met vriendelijke groet,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Toegang tot uw account hier:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Aktualizacja ostrzeżenia
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Witaj <strong>{employee_name}</strong>,</p>

                        <p>
                            Informujemy, że zostało wydane ostrzeżenie przez <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Rodzaj ostrzeżenia:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Temat:</strong> {subject}
                        </p>

                        <p>
                            Prosimy o dokładne zapoznanie się z tym ostrzeżeniem i zapewnienie przestrzegania polityk i wytycznych firmy w przyszłości.
                        </p>

                        <p>
                            Jeśli potrzebujesz wyjaśnień, skontaktuj się z działem HR.
                        </p>

                        <p style="margin-top:25px;">
                            Z poważaniem,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Uzyskaj dostęp do swojego konta tutaj:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Atualização de Aviso
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>
                            Informamos que um aviso foi emitido a você por <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Tipo de aviso:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Assunto:</strong> {subject}
                        </p>

                        <p>
                            Por favor, revise este aviso cuidadosamente e assegure-se de cumprir as políticas e diretrizes da empresa daqui para frente.
                        </p>

                        <p>
                            Se precisar de esclarecimentos, entre em contato com o departamento de RH.
                        </p>

                        <p style="margin-top:25px;">
                            Atenciosamente,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acesse sua conta aqui:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Atualização de Aviso
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>
                            Informamos que um aviso foi emitido a você por <strong>{company_name}</strong>.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Tipo de aviso:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Assunto:</strong> {subject}
                        </p>

                        <p>
                            Por favor, revise este aviso cuidadosamente e assegure-se de cumprir as políticas e diretrizes da empresa daqui para frente.
                        </p>

                        <p>
                            Se precisar de esclarecimentos, entre em contato com o departamento de RH.
                        </p>

                        <p style="margin-top:25px;">
                            Atenciosamente,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Acesse sua conta aqui:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Обновление предупреждения
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Здравствуйте, <strong>{employee_name}</strong>,</p>

                        <p>
                            Сообщаем вам, что <strong>{company_name}</strong> выдало вам предупреждение.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Тип предупреждения:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Тема:</strong> {subject}
                        </p>

                        <p>
                            Пожалуйста, внимательно ознакомьтесь с этим уведомлением и соблюдайте правила и инструкции компании в будущем.
                        </p>

                        <p>
                            Если вам нужны разъяснения, свяжитесь с отделом кадров.
                        </p>

                        <p style="margin-top:25px;">
                            С уважением,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Доступ к вашему аккаунту здесь:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - עדכון אזהרה
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>שלום <strong>{employee_name}</strong>,</p>

                        <p>
                            אנו מודיעים לך כי הוציאה לך <strong>{company_name}</strong> אזהרה.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>סוג האזהרה:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>נושא:</strong> {subject}
                        </p>

                        <p>
                            אנא עיין באזהרה זו בקפידה והקפד על עמידה במדיניות ובנהלים של החברה בעתיד.
                        </p>

                        <p>
                            אם נדרשים הבהרות, אנא פנה למחלקת משאבי אנוש.
                        </p>

                        <p style="margin-top:25px;">
                            בברכה,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        גש לחשבונך כאן:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - Uyarı Bildirimi Güncellemesi
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>Merhaba <strong>{employee_name}</strong>,</p>

                        <p>
                            <strong>{company_name}</strong> tarafından size bir uyarı bildirimi gönderildiğini bildirmek isteriz.
                        </p>

                        <p style="margin:18px 0;">
                            <strong>Uyarı Türü:</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>Konu:</strong> {subject}
                        </p>

                        <p>
                            Lütfen bu bildirimi dikkatlice inceleyin ve bundan sonra şirket politikalarına ve yönergelerine uyduğunuzdan emin olun.
                        </p>

                        <p>
                            Herhangi bir açıklama gerekiyorsa, İK departmanı ile iletişime geçebilirsiniz.
                        </p>

                        <p style="margin-top:25px;">
                            Saygılarımızla,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        Hesabınıza buradan erişin:<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;background:#f3f5fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                            
                        <div style="background:#f59e0b;color:#ffffff;padding:22px 28px;font-size:20px;font-weight:600;">
                        {app_name} - 警告通知更新
                        </div>

                        <div style="padding:30px;font-size:15px;color:#374151;line-height:1.6;">

                        <p>您好 <strong>{employee_name}</strong>，</p>

                        <p>
                            我们通知您，<strong>{company_name}</strong> 已向您发出警告通知。
                        </p>

                        <p style="margin:18px 0;">
                            <strong>警告类型：</strong> {warning_type_name}
                        </p>

                        <p>
                            <strong>主题：</strong> {subject}
                        </p>

                        <p>
                            请仔细查看此通知，并确保今后遵守公司的政策和指南。
                        </p>

                        <p>
                            如需任何说明，请随时联系HR部门。
                        </p>

                        <p style="margin-top:25px;">
                            此致,<br>
                            <strong>{company_name}</strong>
                        </p>

                        </div>

                        <div style="background:#f9fafb;padding:16px 30px;font-size:13px;color:#6b7280;text-align:center;">
                        在此访问您的帐户：<br>
                        <a href="{app_url}" style="color:#f59e0b;text-decoration:none;">{app_url}</a>
                        </div>

                        </div>
                    </div>',
                ],
            ],
            'Transfers Approval' => [
                'subject' => 'Transfers Approved',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Employee Name ": "employee_name",
                    "From Branch Name": "from_branch_name",
                    "From Department Name": "from_department_name",
                    "From Designation Name": "from_designation_name",
                    "To Branch Name": "to_branch_name",
                    "To Department Name": "to_department_name",
                    "To Designation Name": "to_designation_name",
                    "Transfer Date": "transfer_date",
                    "Reason": "reason"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 تم الموافقة على النقل!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">مرحبًا <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    تهانينا! لقد تم <strong style="color:#f59e0b;">الموافقة</strong> على طلب نقلك. تحقق من تفاصيل المهمة الجديدة أدناه:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">من الفرع</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">من القسم</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">من المسمى الوظيفي</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">إلى الفرع</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">إلى القسم</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">إلى المسمى الوظيفي</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">تاريخ النقل</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">السبب</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">اذهب إلى {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">مع أطيب التحيات,<br>
                                <strong>فريق الموارد البشرية في {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Overførsel Godkendt!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Hej <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Tillykke! Din overførselsanmodning er blevet <strong style="color:#f59e0b;">godkendt</strong>. Se dine nye opgavedetaljer nedenfor:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Fra Afdeling</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Fra Sektion</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Fra Titel</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Til Afdeling</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Til Sektion</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Til Titel</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Overførselsdato</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Årsag</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Gå til {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Venlig hilsen,<br>
                                <strong>{company_name} HR Team</strong></p>
                            </div>
                        </div>
                    </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Übertragung genehmigt!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Hallo <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Herzlichen Glückwunsch! Ihre Übertragungsanfrage wurde <strong style="color:#f59e0b;">genehmigt</strong>. Unten finden Sie die Details Ihrer neuen Aufgabe:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Von Filiale</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Von Abteilung</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Von Position</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Zu Filiale</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Zu Abteilung</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Zu Position</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Übertragungsdatum</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Grund</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Gehe zu {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Mit freundlichen Grüßen,<br>
                                <strong>{company_name} HR Team</strong></p>
                            </div>
                        </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 ¡Transferencia Aprobada!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Hola <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    ¡Felicidades! Su solicitud de transferencia ha sido <strong style="color:#f59e0b;">aprobada</strong>. Consulte los detalles de su nueva asignación a continuación:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Sucursal</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Sucursal</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Fecha de Transferencia</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Motivo</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Ir a {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Saludos cordiales,<br>
                                <strong>Equipo de RRHH de {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',
                    'en' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transfer Approved!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Hello <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Congratulations! Your transfer request has been <strong style="color:#f59e0b;">approved</strong>. Check out your new assignment details below:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">From Branch</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">From Department</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">From Designation</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">To Branch</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">To Department</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">To Designation</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Transfer Date</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Reason</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Go to {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Best regards,<br>
                                <strong>{company_name} HR Team</strong></p>
                            </div>
                        </div>
                    </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transfert Approuvé !
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Bonjour <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Félicitations ! Votre demande de transfert a été <strong style="color:#f59e0b;">approuvée</strong>. Consultez les détails de votre nouvelle affectation ci-dessous :
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De la Branche</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Du Département</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De la Fonction</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">À la Branche</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Au Département</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">À la Fonction</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Date de Transfert</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Raison</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Accéder à {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Cordialement,<br>
                                <strong>L’équipe RH de {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'it' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Trasferimento Approvato!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Ciao <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Congratulazioni! La tua richiesta di trasferimento è stata <strong style="color:#f59e0b;">approvata</strong>. Consulta i dettagli del tuo nuovo incarico qui sotto:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Da Filiale</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Da Reparto</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Da Mansione</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Filiale</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Reparto</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">A Mansione</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Data del Trasferimento</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Motivo</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Vai a {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Cordiali saluti,<br>
                                <strong>Team HR di {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'ja' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 転勤承認済み！
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">こんにちは <strong>{employee_name}</strong> さん、</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    おめでとうございます！あなたの転勤申請は<strong style="color:#f59e0b;">承認されました</strong>。以下に新しい配属の詳細をご確認ください：
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">元支店</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">元部署</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">元役職</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">転勤先支店</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">転勤先部署</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">転勤先役職</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">転勤日</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">理由</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">{app_name} へ移動</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">よろしくお願いいたします。<br>
                                <strong>{company_name} 人事チーム</strong></p>
                            </div>
                        </div>
                    </div>',

                    'nl' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Overplaatsing Goedgekeurd!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Hallo <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Gefeliciteerd! Je overplaatsingsverzoek is <strong style="color:#f59e0b;">goedgekeurd</strong>. Bekijk hieronder de details van je nieuwe functie:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Van Vestiging</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Van Afdeling</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Van Functie</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Naar Vestiging</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Naar Afdeling</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Naar Functie</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Overplaatsingsdatum</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Reden</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Ga naar {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Met vriendelijke groet,<br>
                                <strong>{company_name} HR Team</strong></p>
                            </div>
                        </div>
                    </div>',

                    'pl' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transfer Zatwierdzony!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Cześć <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Gratulacje! Twój wniosek o transfer został <strong style="color:#f59e0b;">zatwierdzony</strong>. Szczegóły nowego przydziału znajdziesz poniżej:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Z Oddziału</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Z Działu</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Z Stanowiska</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Do Oddziału</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Do Działu</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Do Stanowiska</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Data Transferu</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Powód</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Przejdź do {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Pozdrawiam,<br>
                                <strong>Zespół HR {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'pt' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transferência Aprovada!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Olá <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Parabéns! Sua solicitação de transferência foi <strong style="color:#f59e0b;">aprovada</strong>. Confira os detalhes da sua nova função abaixo:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Filial</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Filial</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Data da Transferência</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Motivo</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Ir para {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Atenciosamente,<br>
                                <strong>Equipe de RH {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transferência Aprovada!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Olá <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Parabéns! Sua solicitação de transferência foi <strong style="color:#f59e0b;">aprovada</strong>. Confira os detalhes da sua nova função abaixo:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Filial</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">De Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Filial</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Departamento</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Para Cargo</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Data da Transferência</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Motivo</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Ir para {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Atenciosamente,<br>
                                <strong>Equipe de RH {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'ru' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Перевод Одобрен!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Здравствуйте, <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Поздравляем! Ваша заявка на перевод была <strong style="color:#f59e0b;">одобрена</strong>. Ниже приведены детали вашего нового назначения:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Из Филиала</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Из Отдела</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">С Должности</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">В Филиал</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">В Отдел</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">На Должность</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Дата Перевода</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Причина</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">Перейти в {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">С уважением,<br>
                                <strong>HR команда {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'he' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;direction:rtl;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 העברה אושרה!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">שלום <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    ברכות! בקשת ההעברה שלך <strong style="color:#f59e0b;">אושרה</strong>. ראה את פרטי המשימה החדשה שלך למטה:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">מהסניף</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">מהמחלקה</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">מהתפקיד</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">אל הסניף</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">אל המחלקה</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">אל התפקיד</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">תאריך ההעברה</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">סיבה</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">גש ל-{app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">בברכה,<br>
                                <strong>צוות משאבי אנוש {company_name}</strong></p>
                            </div>
                        </div>
                    </div>',

                    'tr' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 Transfer Onaylandı!
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">Merhaba <strong>{employee_name}</strong>,</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    Tebrikler! Transfer talebiniz <strong style="color:#f59e0b;">onaylandı</strong>. Yeni görev detaylarınızı aşağıda inceleyebilirsiniz:
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Gönderen Şube</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Gönderen Departman</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Gönderen Pozisyon</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Alıcı Şube</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Alıcı Departman</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Alıcı Pozisyon</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Transfer Tarihi</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">Sebep</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">{app_name} Git</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">Saygılarımızla,<br>
                                <strong>{company_name} İK Ekibi</strong></p>
                            </div>
                        </div>
                    </div>',

                    'zh' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f0f2f8;padding:40px 20px;">
                        <div style="max-width:700px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.1);">
                            
                            <div style="background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#ffffff;padding:30px 28px;text-align:center;font-size:22px;font-weight:bold;border-bottom:1px solid #e5e7eb;">
                                🎯 调动已批准！
                            </div>
                            
                            <div style="padding:28px;">
                                <p style="font-size:16px;color:#374151;">您好，<strong>{employee_name}</strong>：</p>
                                
                                <p style="font-size:16px;color:#374151;">
                                    恭喜！您的调动申请已被 <strong style="color:#f59e0b;">批准</strong>。请查看以下新岗位的详细信息：
                                </p>
                                
                                <div style="margin-top:20px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                    <table style="width:100%;border-collapse:collapse;">
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">原分支</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_branch_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">原部门</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_department_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">原职位</td>
                                            <td style="padding:12px 16px;color:#111827;">{from_designation_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">调至分支</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_branch_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">调至部门</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_department_name}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">调至职位</td>
                                            <td style="padding:12px 16px;color:#111827;">{to_designation_name}</td>
                                        </tr>
                                        <tr style="background:#f3f4f6;">
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">调动日期</td>
                                            <td style="padding:12px 16px;color:#111827;">{transfer_date}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 16px;font-weight:bold;color:#1f2937;">原因</td>
                                            <td style="padding:12px 16px;color:#111827;">{reason}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div style="text-align:center;margin-top:25px;">
                                    <a href="{app_url}" style="display:inline-block;background:#f59e0b;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;">前往 {app_name}</a>
                                </div>
                                
                                <p style="margin-top:30px;font-size:16px;color:#374151;">此致,<br>
                                <strong>{company_name} 人力资源团队</strong></p>
                            </div>
                        </div>
                    </div>',
                ],
            ],
            'Leave Status' => [
                'subject' => 'Leave Status Updated',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Employee Name": "employee_name",
                    "Leave type": "leave_type",
                    "Start Date": "start_date",
                    "End Date": "end_date",
                    "Total Days": "total_days",
                    "Reason": "reason",
                    "Status": "status"
                  }',
                  'lang' => [
                    'ar' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                إشعار حالة الإجازة
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>مرحباً <strong>{Employee Name}</strong>,</p>
                                
                                <p>تم <strong>{Status}</strong> طلب الإجازة الخاص بك من قبل <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>نوع الإجازة:</strong> {Leave type} <br>
                                    <strong>تاريخ البدء:</strong> {Start Date} <br>
                                    <strong>تاريخ الانتهاء:</strong> {End Date} <br>
                                    <strong>إجمالي الأيام:</strong> {Total Days} <br>
                                    <strong>السبب:</strong> {Reason}<br>
                                    <strong>الحالة:</strong> {Status}        
                                </p>

                                <p>
                                    يمكنك التحقق من المزيد من التفاصيل في 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    مع التحية,<br>
                                    فريق {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',
                    'da' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Meddelelse om orlovsstatus
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hej <strong>{Employee Name}</strong>,</p>
                                
                                <p>Din anmodning om orlov er blevet <strong>{Status}</strong> af <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Orlovstype:</strong> {Leave type} <br>
                                    <strong>Startdato:</strong> {Start Date} <br>
                                    <strong>Slutdato:</strong> {End Date} <br>
                                    <strong>Samlede dage:</strong> {Total Days} <br>
                                    <strong>Årsag:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Du kan se flere detaljer i 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Med venlig hilsen,<br>
                                    {Company Name} Team
                                </p>

                            </div>
                        </div>
                    </div>',

                    'de' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Benachrichtigung zum Urlaubsstatus
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hallo <strong>{Employee Name}</strong>,</p>
                                
                                <p>Ihr Urlaubsantrag wurde von <strong>{Company Name}</strong> <strong>{Status}</strong>.</p>
                                
                                <p>
                                    <strong>Urlaubstyp:</strong> {Leave type} <br>
                                    <strong>Startdatum:</strong> {Start Date} <br>
                                    <strong>Enddatum:</strong> {End Date} <br>
                                    <strong>Gesamttage:</strong> {Total Days} <br>
                                    <strong>Grund:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Weitere Details finden Sie in 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Mit freundlichen Grüßen,<br>
                                    {Company Name} Team
                                </p>

                            </div>
                        </div>
                    </div>',

                    'en' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Leave Status Notification
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hi <strong>{Employee Name}</strong>,</p>
                                
                                <p>Your leave request has been <strong>{Status}</strong> by <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Leave Type:</strong> {Leave type} <br>
                                    <strong>Start Date:</strong> {Start Date} <br>
                                    <strong>End Date:</strong> {End Date} <br>
                                    <strong>Total Days:</strong> {Total Days} <br>
                                    <strong>Reason:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    You can check more details in the 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Regards,<br>
                                    {Company Name} Team
                                </p>

                            </div>
                        </div>
                    </div>',

                    'es' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Notificación del estado de la licencia
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hola <strong>{Employee Name}</strong>,</p>
                                
                                <p>Tu solicitud de licencia ha sido <strong>{Status}</strong> por <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Tipo de licencia:</strong> {Leave type} <br>
                                    <strong>Fecha de inicio:</strong> {Start Date} <br>
                                    <strong>Fecha de finalización:</strong> {End Date} <br>
                                    <strong>Total de días:</strong> {Total Days} <br>
                                    <strong>Motivo:</strong> {Reason}<br>
                                    <strong>Estado:</strong> {Status}        
                                </p>

                                <p>
                                    Puedes ver más detalles en 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Saludos,<br>
                                    Equipo de {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'fr' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Notification du statut du congé
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Bonjour <strong>{Employee Name}</strong>,</p>
                                
                                <p>Votre demande de congé a été <strong>{Status}</strong> par <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Type de congé :</strong> {Leave type} <br>
                                    <strong>Date de début :</strong> {Start Date} <br>
                                    <strong>Date de fin :</strong> {End Date} <br>
                                    <strong>Total des jours :</strong> {Total Days} <br>
                                    <strong>Raison :</strong> {Reason}<br>
                                    <strong>Statut :</strong> {Status}        
                                </p>

                                <p>
                                    Vous pouvez consulter plus de détails dans 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Cordialement,<br>
                                    Équipe {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'it' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Notifica dello stato del congedo
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Ciao <strong>{Employee Name}</strong>,</p>
                                
                                <p>La tua richiesta di congedo è stata <strong>{Status}</strong> da <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Tipo di congedo:</strong> {Leave type} <br>
                                    <strong>Data di inizio:</strong> {Start Date} <br>
                                    <strong>Data di fine:</strong> {End Date} <br>
                                    <strong>Totale giorni:</strong> {Total Days} <br>
                                    <strong>Motivo:</strong> {Reason}<br>
                                    <strong>Stato:</strong> {Status}        
                                </p>

                                <p>
                                    Puoi controllare maggiori dettagli in 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Cordiali saluti,<br>
                                    Team {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'ja' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                休暇ステータス通知
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>こんにちは <strong>{Employee Name}</strong> さん,</p>
                                
                                <p>あなたの休暇申請は <strong>{Company Name}</strong> によって <strong>{Status}</strong> されました。</p>
                                
                                <p>
                                    <strong>休暇の種類:</strong> {Leave type} <br>
                                    <strong>開始日:</strong> {Start Date} <br>
                                    <strong>終了日:</strong> {End Date} <br>
                                    <strong>合計日数:</strong> {Total Days} <br>
                                    <strong>理由:</strong> {Reason}<br>
                                    <strong>ステータス:</strong> {Status}        
                                </p>

                                <p>
                                    詳細は 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>
                                    で確認できます。
                                </p>

                                <p>
                                    よろしくお願いいたします。<br>
                                    {Company Name} チーム
                                </p>

                            </div>
                        </div>
                    </div>',

                    'nl' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Verlofstatus melding
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hallo <strong>{Employee Name}</strong>,</p>
                                
                                <p>Je verlofaanvraag is <strong>{Status}</strong> door <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Verloftype:</strong> {Leave type} <br>
                                    <strong>Startdatum:</strong> {Start Date} <br>
                                    <strong>Einddatum:</strong> {End Date} <br>
                                    <strong>Totaal aantal dagen:</strong> {Total Days} <br>
                                    <strong>Reden:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Je kunt meer details bekijken in 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Met vriendelijke groet,<br>
                                    {Company Name} Team
                                </p>

                            </div>
                        </div>
                    </div>',

                    'pl' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Powiadomienie o statusie urlopu
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Cześć <strong>{Employee Name}</strong>,</p>
                                
                                <p>Twoja prośba o urlop została <strong>{Status}</strong> przez <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Rodzaj urlopu:</strong> {Leave type} <br>
                                    <strong>Data rozpoczęcia:</strong> {Start Date} <br>
                                    <strong>Data zakończenia:</strong> {End Date} <br>
                                    <strong>Łączna liczba dni:</strong> {Total Days} <br>
                                    <strong>Powód:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Możesz sprawdzić więcej szczegółów w 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Pozdrawiamy,<br>
                                    Zespół {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'pt' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Notificação de status da licença
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Olá <strong>{Employee Name}</strong>,</p>
                                
                                <p>Sua solicitação de licença foi <strong>{Status}</strong> por <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Tipo de licença:</strong> {Leave type} <br>
                                    <strong>Data de início:</strong> {Start Date} <br>
                                    <strong>Data de término:</strong> {End Date} <br>
                                    <strong>Total de dias:</strong> {Total Days} <br>
                                    <strong>Motivo:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Você pode verificar mais detalhes em 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Atenciosamente,<br>
                                    Equipe {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'pt-BR' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Notificação de status da licença
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Olá <strong>{Employee Name}</strong>,</p>
                                
                                <p>Sua solicitação de licença foi <strong>{Status}</strong> por <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Tipo de licença:</strong> {Leave type} <br>
                                    <strong>Data de início:</strong> {Start Date} <br>
                                    <strong>Data de término:</strong> {End Date} <br>
                                    <strong>Total de dias:</strong> {Total Days} <br>
                                    <strong>Motivo:</strong> {Reason}<br>
                                    <strong>Status:</strong> {Status}        
                                </p>

                                <p>
                                    Você pode verificar mais detalhes em 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    Atenciosamente,<br>
                                    Equipe {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'ru' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Уведомление о статусе отпуска
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Здравствуйте <strong>{Employee Name}</strong>,</p>
                                
                                <p>Ваш запрос на отпуск был <strong>{Status}</strong> компанией <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>Тип отпуска:</strong> {Leave type} <br>
                                    <strong>Дата начала:</strong> {Start Date} <br>
                                    <strong>Дата окончания:</strong> {End Date} <br>
                                    <strong>Общее количество дней:</strong> {Total Days} <br>
                                    <strong>Причина:</strong> {Reason}<br>
                                    <strong>Статус:</strong> {Status}        
                                </p>

                                <p>
                                    Вы можете проверить более подробную информацию в 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    С уважением,<br>
                                    Команда {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'he' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                הודעת סטטוס חופשה
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>שלום <strong>{Employee Name}</strong>,</p>
                                
                                <p>בקשת החופשה שלך <strong>{Status}</strong> על ידי <strong>{Company Name}</strong>.</p>
                                
                                <p>
                                    <strong>סוג החופשה:</strong> {Leave type} <br>
                                    <strong>תאריך התחלה:</strong> {Start Date} <br>
                                    <strong>תאריך סיום:</strong> {End Date} <br>
                                    <strong>סה״כ ימים:</strong> {Total Days} <br>
                                    <strong>סיבה:</strong> {Reason}<br>
                                    <strong>סטטוס:</strong> {Status}        
                                </p>

                                <p>
                                    ניתן לבדוק פרטים נוספים ב 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>.
                                </p>

                                <p>
                                    בברכה,<br>
                                    צוות {Company Name}
                                </p>

                            </div>
                        </div>
                    </div>',

                    'tr' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                İzin Durumu Bildirimi
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Merhaba <strong>{Employee Name}</strong>,</p>
                                
                                <p>İzin talebiniz <strong>{Company Name}</strong> tarafından <strong>{Status}</strong>.</p>
                                
                                <p>
                                    <strong>İzin Türü:</strong> {Leave type} <br>
                                    <strong>Başlangıç Tarihi:</strong> {Start Date} <br>
                                    <strong>Bitiş Tarihi:</strong> {End Date} <br>
                                    <strong>Toplam Gün:</strong> {Total Days} <br>
                                    <strong>Neden:</strong> {Reason}<br>
                                    <strong>Durum:</strong> {Status}        
                                </p>

                                <p>
                                    Daha fazla detayı 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>
                                    içinde kontrol edebilirsiniz.
                                </p>

                                <p>
                                    Saygılarımızla,<br>
                                    {Company Name} Ekibi
                                </p>

                            </div>
                        </div>
                    </div>',

                    'zh' => ' <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:680px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#4f46e5;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                请假状态通知
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>您好 <strong>{Employee Name}</strong>,</p>
                                
                                <p>您的请假申请已被 <strong>{Company Name}</strong> <strong>{Status}</strong>。</p>
                                
                                <p>
                                    <strong>请假类型:</strong> {Leave type} <br>
                                    <strong>开始日期:</strong> {Start Date} <br>
                                    <strong>结束日期:</strong> {End Date} <br>
                                    <strong>总天数:</strong> {Total Days} <br>
                                    <strong>原因:</strong> {Reason}<br>
                                    <strong>状态:</strong> {Status}        
                                </p>

                                <p>
                                    您可以在 
                                    <a href="{App Url}" style="color:#4f46e5;text-decoration:none;">
                                        {App Name}
                                    </a>
                                    中查看更多详情。
                                </p>

                                <p>
                                    此致敬礼,<br>
                                    {Company Name} 团队
                                </p>

                            </div>
                        </div>
                    </div>',
                ],
            ],
            'Payroll Processed' => [
                'subject' => 'Your Salary Has Been Processed',
                'variables' => '{
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "App Url": "app_url",
                    "Employee Name": "employee_name",
                    "Title": "title",
                    "Payroll Frequency": "payroll_frequency",
                    "Pay Period Start": "pay_period_start",
                    "Pay Period End": "pay_period_end",
                    "Pay Date": "pay_date",
                    "Basic Salary": "basic_salary",
                    "Total Allowances": "total_allowances",
                    "Total Loans": "total_loans",
                    "Gross Pay": "gross_pay",
                    "Net Pay": "net_pay",
                    "Working Days": "working_days",
                    "Present Days": "present_days",
                    "Absent Days": "absent_days",
                    "Half Days": "half_days",
                    "Paid Leave Days": "paid_leave_days",
                    "Unpaid Leave Days": "unpaid_leave_days",
                    "Overtime Hours": "overtime_hours",
                    "Status": "status"
                  }',
                  'lang' => [
                    'ar' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        تمت معالجة الرواتب بنجاح
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>مرحبًا <strong>{employee_name}</strong>,</p>

                        <p>تمت معالجة راتبك لدورة الرواتب <strong>{payroll_frequency}</strong> بنجاح من قبل <strong>{company_name}</strong>. يرجى الاطلاع على ملخص الرواتب أدناه.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">معلومات الرواتب</p>

                        <p>
                        <strong>المسمى الوظيفي:</strong> {title} <br>
                        <strong>تكرار الرواتب:</strong> {payroll_frequency} <br>
                        <strong>فترة الدفع:</strong> {pay_period_start} إلى {pay_period_end} <br>
                        <strong>تاريخ الدفع:</strong> {pay_date} <br>
                        <strong>الحالة:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">ملخص الراتب</p>

                        <p>
                        <strong>الراتب الأساسي:</strong> {basic_salary} <br>
                        <strong>إجمالي البدلات:</strong> {total_allowances} <br>
                        <strong>إجمالي القروض:</strong> {total_loans} <br>
                        <strong>إجمالي الراتب:</strong> {gross_pay} <br>
                        <strong>صافي الراتب:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">ملخص الحضور</p>

                        <p>
                        <strong>أيام العمل:</strong> {working_days} <br>
                        <strong>أيام الحضور:</strong> {present_days} <br>
                        <strong>أيام الغياب:</strong> {absent_days} <br>
                        <strong>أنصاف الأيام:</strong> {half_days} <br>
                        <strong>أيام الإجازة المدفوعة:</strong> {paid_leave_days} <br>
                        <strong>أيام الإجازة غير المدفوعة:</strong> {unpaid_leave_days} <br>
                        <strong>ساعات العمل الإضافي:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        يمكنك تسجيل الدخول إلى بوابة الموظفين لعرض قسيمة الراتب الكاملة والتفاصيل الإضافية.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        عرض قسيمة الراتب في {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        إذا كان لديك أي أسئلة بخصوص تفاصيل الرواتب، يرجى التواصل مع قسم الموارد البشرية أو مسؤول الرواتب.
                        </p>

                        <p>
                        مع التحية,<br>
                        فريق {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',

                    'da' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Løn behandlet med succes
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Hej <strong>{employee_name}</strong>,</p>

                        <p>Din løn for <strong>{payroll_frequency}</strong> lønperiode er blevet behandlet med succes af <strong>{company_name}</strong>. Se venligst lønoversigten nedenfor.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Løninformation</p>

                        <p>
                        <strong>Titel:</strong> {title} <br>
                        <strong>Lønfrekvens:</strong> {payroll_frequency} <br>
                        <strong>Lønperiode:</strong> {pay_period_start} til {pay_period_end} <br>
                        <strong>Udbetalingsdato:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Lønoversigt</p>

                        <p>
                        <strong>Grundløn:</strong> {basic_salary} <br>
                        <strong>Samlede tillæg:</strong> {total_allowances} <br>
                        <strong>Samlede lån:</strong> {total_loans} <br>
                        <strong>Bruttoløn:</strong> {gross_pay} <br>
                        <strong>Nettoløn:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Fremmødeoversigt</p>

                        <p>
                        <strong>Arbejdsdage:</strong> {working_days} <br>
                        <strong>Tilstedeværende dage:</strong> {present_days} <br>
                        <strong>Fraværsdage:</strong> {absent_days} <br>
                        <strong>Halve dage:</strong> {half_days} <br>
                        <strong>Betalte fridage:</strong> {paid_leave_days} <br>
                        <strong>Ubetalte fridage:</strong> {unpaid_leave_days} <br>
                        <strong>Overarbejdstimer:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Du kan logge ind på medarbejderportalen for at se din fulde lønseddel.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Se lønseddel i {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Hvis du har spørgsmål om din løn, kontakt venligst HR eller lønadministratoren.
                        </p>

                        <p>
                        Med venlig hilsen,<br>
                        {company_name} Team
                        </p>

                        </div>
                        </div>
                        </div>',

                    'de' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Gehaltsabrechnung erfolgreich verarbeitet
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>Ihr Gehalt für den <strong>{payroll_frequency}</strong> Abrechnungszyklus wurde erfolgreich von <strong>{company_name}</strong> verarbeitet. Nachfolgend finden Sie eine Zusammenfassung Ihrer Gehaltsabrechnung.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Gehaltsinformationen</p>

                        <p>
                        <strong>Titel:</strong> {title} <br>
                        <strong>Abrechnungsfrequenz:</strong> {payroll_frequency} <br>
                        <strong>Abrechnungszeitraum:</strong> {pay_period_start} bis {pay_period_end} <br>
                        <strong>Zahlungsdatum:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Gehaltsübersicht</p>

                        <p>
                        <strong>Grundgehalt:</strong> {basic_salary} <br>
                        <strong>Gesamtzulagen:</strong> {total_allowances} <br>
                        <strong>Gesamtdarlehen:</strong> {total_loans} <br>
                        <strong>Bruttogehalt:</strong> {gross_pay} <br>
                        <strong>Nettogehalt:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Anwesenheitsübersicht</p>

                        <p>
                        <strong>Arbeitstage:</strong> {working_days} <br>
                        <strong>Anwesende Tage:</strong> {present_days} <br>
                        <strong>Fehltage:</strong> {absent_days} <br>
                        <strong>Halbe Tage:</strong> {half_days} <br>
                        <strong>Bezahlte Urlaubstage:</strong> {paid_leave_days} <br>
                        <strong>Unbezahlte Urlaubstage:</strong> {unpaid_leave_days} <br>
                        <strong>Überstunden:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Sie können sich im Mitarbeiterportal anmelden, um Ihre vollständige Gehaltsabrechnung einzusehen.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Gehaltsabrechnung in {app_name} ansehen
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Wenn Sie Fragen zu Ihren Gehaltsdetails haben, wenden Sie sich bitte an Ihre HR- oder Payroll-Abteilung.
                        </p>

                        <p>
                        Mit freundlichen Grüßen,<br>
                        {company_name} Team
                        </p>

                        </div>
                        </div>
                        </div>',
                        
                    'en' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                                Payroll Processed Successfully
                            </div>
                            
                            <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">
                                
                                <p>Hi <strong>{employee_name}</strong>,</p>

                                <p>Your salary for the <strong>{payroll_frequency}</strong> payroll cycle has been successfully processed by <strong>{company_name}</strong>. Please find your payroll summary below.</p>

                                <p style="margin-top:20px;font-weight:600;font-size:16px;">Payroll Information</p>

                                <p>
                                    <strong>Title:</strong> {title} <br>
                                    <strong>Payroll Frequency:</strong> {payroll_frequency} <br>
                                    <strong>Pay Period:</strong> {pay_period_start} to {pay_period_end} <br>
                                    <strong>Pay Date:</strong> {pay_date} <br>
                                    <strong>Status:</strong> {status}
                                </p>

                                <p style="margin-top:20px;font-weight:600;font-size:16px;">Salary Summary</p>

                                <p>
                                    <strong>Basic Salary:</strong> {basic_salary} <br>
                                    <strong>Total Allowances:</strong> {total_allowances} <br>
                                    <strong>Total Loans:</strong> {total_loans} <br>
                                    <strong>Gross Pay:</strong> {gross_pay} <br>
                                    <strong>Net Pay:</strong> {net_pay}
                                </p>

                                <p style="margin-top:20px;font-weight:600;font-size:16px;">Attendance Summary</p>

                                <p>
                                    <strong>Working Days:</strong> {working_days} <br>
                                    <strong>Present Days:</strong> {present_days} <br>
                                    <strong>Absent Days:</strong> {absent_days} <br>
                                    <strong>Half Days:</strong> {half_days} <br>
                                    <strong>Paid Leave Days:</strong> {paid_leave_days} <br>
                                    <strong>Unpaid Leave Days:</strong> {unpaid_leave_days} <br>
                                    <strong>Overtime Hours:</strong> {overtime_hours}
                                </p>

                                <p style="margin-top:20px;">
                                    You can log in to your employee portal to view the complete payslip and additional details.
                                    <br><br>
                                    <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                                        View Payslip in {app_name}
                                    </a>
                                </p>

                                <p style="margin-top:25px;">
                                    If you have any questions regarding your payroll details, please contact your HR or payroll administrator.
                                </p>

                                <p>
                                    Regards,<br>
                                    {company_name} Team
                                </p>

                            </div>
                        </div>
                    </div>',

                    'es' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Nómina procesada correctamente
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Hola <strong>{employee_name}</strong>,</p>

                        <p>Tu salario para el ciclo de nómina <strong>{payroll_frequency}</strong> ha sido procesado correctamente por <strong>{company_name}</strong>. Consulta el resumen de tu nómina a continuación.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Información de la nómina</p>

                        <p>
                        <strong>Título:</strong> {title} <br>
                        <strong>Frecuencia de nómina:</strong> {payroll_frequency} <br>
                        <strong>Periodo de pago:</strong> {pay_period_start} a {pay_period_end} <br>
                        <strong>Fecha de pago:</strong> {pay_date} <br>
                        <strong>Estado:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumen del salario</p>

                        <p>
                        <strong>Salario base:</strong> {basic_salary} <br>
                        <strong>Total de asignaciones:</strong> {total_allowances} <br>
                        <strong>Total de préstamos:</strong> {total_loans} <br>
                        <strong>Salario bruto:</strong> {gross_pay} <br>
                        <strong>Salario neto:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumen de asistencia</p>

                        <p>
                        <strong>Días laborables:</strong> {working_days} <br>
                        <strong>Días presentes:</strong> {present_days} <br>
                        <strong>Días ausentes:</strong> {absent_days} <br>
                        <strong>Medios días:</strong> {half_days} <br>
                        <strong>Días de licencia pagados:</strong> {paid_leave_days} <br>
                        <strong>Días de licencia no pagados:</strong> {unpaid_leave_days} <br>
                        <strong>Horas extra:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Puedes iniciar sesión en el portal de empleados para ver tu recibo de nómina completo.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Ver recibo de nómina en {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Si tienes alguna pregunta sobre tu nómina, contacta con RRHH o con el administrador de nómina.
                        </p>

                        <p>
                        Saludos,<br>
                        Equipo de {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'fr' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Paie traitée avec succès
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Bonjour <strong>{employee_name}</strong>,</p>

                        <p>Votre salaire pour le cycle de paie <strong>{payroll_frequency}</strong> a été traité avec succès par <strong>{company_name}</strong>. Veuillez trouver ci-dessous le résumé de votre paie.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Informations sur la paie</p>

                        <p>
                        <strong>Titre :</strong> {title} <br>
                        <strong>Fréquence de paie :</strong> {payroll_frequency} <br>
                        <strong>Période de paie :</strong> {pay_period_start} à {pay_period_end} <br>
                        <strong>Date de paiement :</strong> {pay_date} <br>
                        <strong>Statut :</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Résumé du salaire</p>

                        <p>
                        <strong>Salaire de base :</strong> {basic_salary} <br>
                        <strong>Total des allocations :</strong> {total_allowances} <br>
                        <strong>Total des prêts :</strong> {total_loans} <br>
                        <strong>Salaire brut :</strong> {gross_pay} <br>
                        <strong>Salaire net :</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Résumé de présence</p>

                        <p>
                        <strong>Jours ouvrables :</strong> {working_days} <br>
                        <strong>Jours présents :</strong> {present_days} <br>
                        <strong>Jours d\absence :</strong> {absent_days} <br>
                        <strong>Demi-journées :</strong> {half_days} <br>
                        <strong>Congés payés :</strong> {paid_leave_days} <br>
                        <strong>Congés non payés :</strong> {unpaid_leave_days} <br>
                        <strong>Heures supplémentaires :</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Vous pouvez vous connecter au portail des employés pour consulter votre fiche de paie complète.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Voir la fiche de paie dans {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Si vous avez des questions concernant votre paie, veuillez contacter votre service RH ou l’administrateur de la paie.
                        </p>

                        <p>
                        Cordialement,<br>
                        Équipe {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'it' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Busta paga elaborata con successo
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Ciao <strong>{employee_name}</strong>,</p>

                        <p>Il tuo stipendio per il ciclo di paga <strong>{payroll_frequency}</strong> è stato elaborato con successo da <strong>{company_name}</strong>. Di seguito trovi il riepilogo della tua busta paga.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Informazioni sulla paga</p>

                        <p>
                        <strong>Titolo:</strong> {title} <br>
                        <strong>Frequenza di paga:</strong> {payroll_frequency} <br>
                        <strong>Periodo di paga:</strong> {pay_period_start} a {pay_period_end} <br>
                        <strong>Data di pagamento:</strong> {pay_date} <br>
                        <strong>Stato:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Riepilogo dello stipendio</p>

                        <p>
                        <strong>Stipendio base:</strong> {basic_salary} <br>
                        <strong>Totale indennità:</strong> {total_allowances} <br>
                        <strong>Totale prestiti:</strong> {total_loans} <br>
                        <strong>Stipendio lordo:</strong> {gross_pay} <br>
                        <strong>Stipendio netto:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Riepilogo presenze</p>

                        <p>
                        <strong>Giorni lavorativi:</strong> {working_days} <br>
                        <strong>Giorni presenti:</strong> {present_days} <br>
                        <strong>Giorni assenti:</strong> {absent_days} <br>
                        <strong>Mezze giornate:</strong> {half_days} <br>
                        <strong>Giorni di ferie pagate:</strong> {paid_leave_days} <br>
                        <strong>Giorni di ferie non pagate:</strong> {unpaid_leave_days} <br>
                        <strong>Ore di straordinario:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Puoi accedere al portale dipendenti per visualizzare la tua busta paga completa.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Visualizza la busta paga in {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Se hai domande sulla tua busta paga, contatta il reparto HR o l\amministratore delle paghe.
                        </p>

                        <p>
                        Cordiali saluti,<br>
                        Team {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'ja' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        給与処理が正常に完了しました
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>こんにちは <strong>{employee_name}</strong> さん、</p>

                        <p><strong>{company_name}</strong> により、<strong>{payroll_frequency}</strong> の給与サイクルの給与処理が正常に完了しました。以下に給与の概要をご確認ください。</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">給与情報</p>

                        <p>
                        <strong>役職:</strong> {title} <br>
                        <strong>給与頻度:</strong> {payroll_frequency} <br>
                        <strong>支給期間:</strong> {pay_period_start} 〜 {pay_period_end} <br>
                        <strong>支給日:</strong> {pay_date} <br>
                        <strong>ステータス:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">給与概要</p>

                        <p>
                        <strong>基本給:</strong> {basic_salary} <br>
                        <strong>手当合計:</strong> {total_allowances} <br>
                        <strong>ローン合計:</strong> {total_loans} <br>
                        <strong>総支給額:</strong> {gross_pay} <br>
                        <strong>手取り額:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">勤怠概要</p>

                        <p>
                        <strong>勤務日数:</strong> {working_days} <br>
                        <strong>出勤日数:</strong> {present_days} <br>
                        <strong>欠勤日数:</strong> {absent_days} <br>
                        <strong>半日:</strong> {half_days} <br>
                        <strong>有給休暇日数:</strong> {paid_leave_days} <br>
                        <strong>無給休暇日数:</strong> {unpaid_leave_days} <br>
                        <strong>残業時間:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        従業員ポータルにログインして、詳細な給与明細を確認できます。
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        {app_name}で給与明細を見る
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        給与の詳細についてご質問がある場合は、人事部または給与管理者にお問い合わせください。
                        </p>

                        <p>
                        よろしくお願いいたします。<br>
                        {company_name} チーム
                        </p>

                        </div>
                        </div>
                        </div>',
                    'nl' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Salaris succesvol verwerkt
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Hallo <strong>{employee_name}</strong>,</p>

                        <p>Je salaris voor de <strong>{payroll_frequency}</strong> looncyclus is succesvol verwerkt door <strong>{company_name}</strong>. Hieronder vind je een samenvatting van je loon.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Looninformatie</p>

                        <p>
                        <strong>Titel:</strong> {title} <br>
                        <strong>Loonfrequentie:</strong> {payroll_frequency} <br>
                        <strong>Loonperiode:</strong> {pay_period_start} tot {pay_period_end} <br>
                        <strong>Betaaldatum:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Salarisoverzicht</p>

                        <p>
                        <strong>Basissalaris:</strong> {basic_salary} <br>
                        <strong>Totaal toelagen:</strong> {total_allowances} <br>
                        <strong>Totaal leningen:</strong> {total_loans} <br>
                        <strong>Brutoloon:</strong> {gross_pay} <br>
                        <strong>Nettoloon:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Aanwezigheidsoverzicht</p>

                        <p>
                        <strong>Werkdagen:</strong> {working_days} <br>
                        <strong>Aanwezige dagen:</strong> {present_days} <br>
                        <strong>Afwezige dagen:</strong> {absent_days} <br>
                        <strong>Halve dagen:</strong> {half_days} <br>
                        <strong>Betaalde verlofdagen:</strong> {paid_leave_days} <br>
                        <strong>Onbetaalde verlofdagen:</strong> {unpaid_leave_days} <br>
                        <strong>Overuren:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Je kunt inloggen op het werknemersportaal om je volledige loonstrook te bekijken.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Bekijk loonstrook in {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Als je vragen hebt over je salaris, neem dan contact op met HR of de salarisadministratie.
                        </p>

                        <p>
                        Met vriendelijke groet,<br>
                        {company_name} Team
                        </p>

                        </div>
                        </div>
                        </div>',
                    'pl' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Wynagrodzenie przetworzone pomyślnie
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Cześć <strong>{employee_name}</strong>,</p>

                        <p>Twoje wynagrodzenie za cykl płacowy <strong>{payroll_frequency}</strong> zostało pomyślnie przetworzone przez <strong>{company_name}</strong>. Poniżej znajduje się podsumowanie Twojej wypłaty.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Informacje o wynagrodzeniu</p>

                        <p>
                        <strong>Stanowisko:</strong> {title} <br>
                        <strong>Częstotliwość wypłaty:</strong> {payroll_frequency} <br>
                        <strong>Okres rozliczeniowy:</strong> {pay_period_start} do {pay_period_end} <br>
                        <strong>Data wypłaty:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Podsumowanie wynagrodzenia</p>

                        <p>
                        <strong>Wynagrodzenie podstawowe:</strong> {basic_salary} <br>
                        <strong>Łączne dodatki:</strong> {total_allowances} <br>
                        <strong>Łączne pożyczki:</strong> {total_loans} <br>
                        <strong>Wynagrodzenie brutto:</strong> {gross_pay} <br>
                        <strong>Wynagrodzenie netto:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Podsumowanie obecności</p>

                        <p>
                        <strong>Dni robocze:</strong> {working_days} <br>
                        <strong>Dni obecności:</strong> {present_days} <br>
                        <strong>Dni nieobecności:</strong> {absent_days} <br>
                        <strong>Pół dni:</strong> {half_days} <br>
                        <strong>Płatne urlopy:</strong> {paid_leave_days} <br>
                        <strong>Bezpłatne urlopy:</strong> {unpaid_leave_days} <br>
                        <strong>Nadgodziny:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Możesz zalogować się do portalu pracownika, aby zobaczyć pełny pasek wynagrodzenia.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Zobacz pasek wynagrodzenia w {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Jeśli masz pytania dotyczące wynagrodzenia, skontaktuj się z działem HR lub administratorem płac.
                        </p>

                        <p>
                        Pozdrawiamy,<br>
                        Zespół {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'pt' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Folha de pagamento processada com sucesso
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>Seu salário para o ciclo de folha de pagamento <strong>{payroll_frequency}</strong> foi processado com sucesso por <strong>{company_name}</strong>. Veja abaixo o resumo da sua folha de pagamento.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Informações da folha de pagamento</p>

                        <p>
                        <strong>Título:</strong> {title} <br>
                        <strong>Frequência da folha:</strong> {payroll_frequency} <br>
                        <strong>Período de pagamento:</strong> {pay_period_start} a {pay_period_end} <br>
                        <strong>Data de pagamento:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumo salarial</p>

                        <p>
                        <strong>Salário base:</strong> {basic_salary} <br>
                        <strong>Total de benefícios:</strong> {total_allowances} <br>
                        <strong>Total de empréstimos:</strong> {total_loans} <br>
                        <strong>Salário bruto:</strong> {gross_pay} <br>
                        <strong>Salário líquido:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumo de presença</p>

                        <p>
                        <strong>Dias de trabalho:</strong> {working_days} <br>
                        <strong>Dias presentes:</strong> {present_days} <br>
                        <strong>Dias ausentes:</strong> {absent_days} <br>
                        <strong>Meio período:</strong> {half_days} <br>
                        <strong>Dias de licença remunerada:</strong> {paid_leave_days} <br>
                        <strong>Dias de licença não remunerada:</strong> {unpaid_leave_days} <br>
                        <strong>Horas extras:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Você pode acessar o portal do funcionário para visualizar seu holerite completo.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Ver holerite no {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Se você tiver alguma dúvida sobre sua folha de pagamento, entre em contato com o RH ou administrador da folha.
                        </p>

                        <p>
                        Atenciosamente,<br>
                        Equipe {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'pt-BR' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Folha de pagamento processada com sucesso
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Olá <strong>{employee_name}</strong>,</p>

                        <p>Seu salário para o ciclo de folha de pagamento <strong>{payroll_frequency}</strong> foi processado com sucesso por <strong>{company_name}</strong>. Veja abaixo o resumo da sua folha de pagamento.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Informações da folha de pagamento</p>

                        <p>
                        <strong>Título:</strong> {title} <br>
                        <strong>Frequência da folha:</strong> {payroll_frequency} <br>
                        <strong>Período de pagamento:</strong> {pay_period_start} a {pay_period_end} <br>
                        <strong>Data de pagamento:</strong> {pay_date} <br>
                        <strong>Status:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumo salarial</p>

                        <p>
                        <strong>Salário base:</strong> {basic_salary} <br>
                        <strong>Total de benefícios:</strong> {total_allowances} <br>
                        <strong>Total de empréstimos:</strong> {total_loans} <br>
                        <strong>Salário bruto:</strong> {gross_pay} <br>
                        <strong>Salário líquido:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Resumo de presença</p>

                        <p>
                        <strong>Dias de trabalho:</strong> {working_days} <br>
                        <strong>Dias presentes:</strong> {present_days} <br>
                        <strong>Dias ausentes:</strong> {absent_days} <br>
                        <strong>Meio período:</strong> {half_days} <br>
                        <strong>Dias de licença remunerada:</strong> {paid_leave_days} <br>
                        <strong>Dias de licença não remunerada:</strong> {unpaid_leave_days} <br>
                        <strong>Horas extras:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Você pode acessar o portal do funcionário para visualizar seu holerite completo.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Ver holerite no {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Se você tiver alguma dúvida sobre sua folha de pagamento, entre em contato com o RH ou administrador da folha.
                        </p>

                        <p>
                        Atenciosamente,<br>
                        Equipe {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'ru' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Зарплата успешно обработана
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Здравствуйте, <strong>{employee_name}</strong>,</p>

                        <p>Ваша зарплата за расчетный период <strong>{payroll_frequency}</strong> была успешно обработана компанией <strong>{company_name}</strong>. Ниже приведена сводка вашей зарплаты.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Информация о зарплате</p>

                        <p>
                        <strong>Должность:</strong> {title} <br>
                        <strong>Частота выплат:</strong> {payroll_frequency} <br>
                        <strong>Период оплаты:</strong> {pay_period_start} до {pay_period_end} <br>
                        <strong>Дата выплаты:</strong> {pay_date} <br>
                        <strong>Статус:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Сводка зарплаты</p>

                        <p>
                        <strong>Базовая зарплата:</strong> {basic_salary} <br>
                        <strong>Общие надбавки:</strong> {total_allowances} <br>
                        <strong>Общие займы:</strong> {total_loans} <br>
                        <strong>Валовая зарплата:</strong> {gross_pay} <br>
                        <strong>Чистая зарплата:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Сводка посещаемости</p>

                        <p>
                        <strong>Рабочие дни:</strong> {working_days} <br>
                        <strong>Дни присутствия:</strong> {present_days} <br>
                        <strong>Дни отсутствия:</strong> {absent_days} <br>
                        <strong>Половина дня:</strong> {half_days} <br>
                        <strong>Оплачиваемые отпуска:</strong> {paid_leave_days} <br>
                        <strong>Неоплачиваемые отпуска:</strong> {unpaid_leave_days} <br>
                        <strong>Сверхурочные часы:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Вы можете войти в портал сотрудников, чтобы просмотреть полный расчетный лист.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        Просмотреть расчетный лист в {app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Если у вас есть вопросы по поводу вашей зарплаты, пожалуйста, свяжитесь с отделом кадров или администратором по расчету заработной платы.
                        </p>

                        <p>
                        С уважением,<br>
                        Команда {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'he' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        השכר עובד בהצלחה
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>שלום <strong>{employee_name}</strong>,</p>

                        <p>השכר שלך עבור מחזור השכר <strong>{payroll_frequency}</strong> עובד בהצלחה על ידי <strong>{company_name}</strong>. להלן סיכום השכר שלך.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">מידע על השכר</p>

                        <p>
                        <strong>תפקיד:</strong> {title} <br>
                        <strong>תדירות השכר:</strong> {payroll_frequency} <br>
                        <strong>תקופת תשלום:</strong> {pay_period_start} עד {pay_period_end} <br>
                        <strong>תאריך תשלום:</strong> {pay_date} <br>
                        <strong>סטטוס:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">סיכום השכר</p>

                        <p>
                        <strong>שכר בסיס:</strong> {basic_salary} <br>
                        <strong>סך כל ההטבות:</strong> {total_allowances} <br>
                        <strong>סך כל ההלוואות:</strong> {total_loans} <br>
                        <strong>שכר ברוטו:</strong> {gross_pay} <br>
                        <strong>שכר נטו:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">סיכום נוכחות</p>

                        <p>
                        <strong>ימי עבודה:</strong> {working_days} <br>
                        <strong>ימי נוכחות:</strong> {present_days} <br>
                        <strong>ימי היעדרות:</strong> {absent_days} <br>
                        <strong>חצאי ימים:</strong> {half_days} <br>
                        <strong>ימי חופשה בתשלום:</strong> {paid_leave_days} <br>
                        <strong>ימי חופשה ללא תשלום:</strong> {unpaid_leave_days} <br>
                        <strong>שעות נוספות:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        ניתן להתחבר לפורטל העובדים כדי לצפות בתלוש השכר המלא.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        צפה בתלוש השכר ב-{app_name}
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        אם יש לך שאלות לגבי פרטי השכר שלך, אנא פנה למחלקת משאבי אנוש או למנהל השכר.
                        </p>

                        <p>
                        בברכה,<br>
                        צוות {company_name}
                        </p>

                        </div>
                        </div>
                        </div>',
                    'tr' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        Bordro Başarıyla İşlendi
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>Merhaba <strong>{employee_name}</strong>,</p>

                        <p><strong>{company_name}</strong> tarafından <strong>{payroll_frequency}</strong> bordro dönemi için maaşınız başarıyla işlenmiştir. Aşağıda bordro özetinizi bulabilirsiniz.</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Bordro Bilgileri</p>

                        <p>
                        <strong>Unvan:</strong> {title} <br>
                        <strong>Bordro Sıklığı:</strong> {payroll_frequency} <br>
                        <strong>Ödeme Dönemi:</strong> {pay_period_start} - {pay_period_end} <br>
                        <strong>Ödeme Tarihi:</strong> {pay_date} <br>
                        <strong>Durum:</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Maaş Özeti</p>

                        <p>
                        <strong>Temel Maaş:</strong> {basic_salary} <br>
                        <strong>Toplam Ödenekler:</strong> {total_allowances} <br>
                        <strong>Toplam Krediler:</strong> {total_loans} <br>
                        <strong>Brüt Maaş:</strong> {gross_pay} <br>
                        <strong>Net Maaş:</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">Devam Özeti</p>

                        <p>
                        <strong>Çalışma Günleri:</strong> {working_days} <br>
                        <strong>Mevcut Günler:</strong> {present_days} <br>
                        <strong>Devamsız Günler:</strong> {absent_days} <br>
                        <strong>Yarım Günler:</strong> {half_days} <br>
                        <strong>Ücretli İzin Günleri:</strong> {paid_leave_days} <br>
                        <strong>Ücretsiz İzin Günleri:</strong> {unpaid_leave_days} <br>
                        <strong>Fazla Mesai Saatleri:</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        Çalışan portalına giriş yaparak tam maaş bordronuzu görüntüleyebilirsiniz.
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        {app_name} içinde Bordroyu Görüntüle
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        Bordro detaylarınız hakkında herhangi bir sorunuz varsa, lütfen İK veya bordro yöneticinizle iletişime geçin.
                        </p>

                        <p>
                        Saygılarımızla,<br>
                        {company_name} Ekibi
                        </p>

                        </div>
                        </div>
                        </div>',
                    'zh' => '<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6fb;padding:40px 20px;">
                        <div style="max-width:720px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#16a34a;color:#ffffff;padding:20px 25px;font-size:18px;font-weight:600;">
                        工资已成功处理
                        </div>

                        <div style="padding:25px;font-size:15px;line-height:1.7;color:#333333;">

                        <p>您好 <strong>{employee_name}</strong>,</p>

                        <p>您的 <strong>{payroll_frequency}</strong> 工资周期薪资已由 <strong>{company_name}</strong> 成功处理。请查看下面的工资摘要。</p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">工资信息</p>

                        <p>
                        <strong>职位：</strong> {title} <br>
                        <strong>发薪频率：</strong> {payroll_frequency} <br>
                        <strong>发薪周期：</strong> {pay_period_start} 至 {pay_period_end} <br>
                        <strong>发薪日期：</strong> {pay_date} <br>
                        <strong>状态：</strong> {status}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">薪资摘要</p>

                        <p>
                        <strong>基本工资：</strong> {basic_salary} <br>
                        <strong>津贴总额：</strong> {total_allowances} <br>
                        <strong>贷款总额：</strong> {total_loans} <br>
                        <strong>税前工资：</strong> {gross_pay} <br>
                        <strong>税后工资：</strong> {net_pay}
                        </p>

                        <p style="margin-top:20px;font-weight:600;font-size:16px;">出勤摘要</p>

                        <p>
                        <strong>工作天数：</strong> {working_days} <br>
                        <strong>出勤天数：</strong> {present_days} <br>
                        <strong>缺勤天数：</strong> {absent_days} <br>
                        <strong>半天：</strong> {half_days} <br>
                        <strong>带薪假期：</strong> {paid_leave_days} <br>
                        <strong>无薪假期：</strong> {unpaid_leave_days} <br>
                        <strong>加班时数：</strong> {overtime_hours}
                        </p>

                        <p style="margin-top:20px;">
                        您可以登录员工门户查看完整的工资单和更多详细信息。
                        <br><br>
                        <a href="{app_url}" style="background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;font-size:14px;">
                        在 {app_name} 中查看工资单
                        </a>
                        </p>

                        <p style="margin-top:25px;">
                        如果您对工资详情有任何疑问，请联系您的 HR 或薪资管理员。
                        </p>

                        <p>
                        此致,<br>
                        {company_name} 团队
                        </p>

                        </div>
                        </div>
                        </div>',
                ],
            ],
        ];
        foreach($emailTemplate as $eTemp)
        {     
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','Hrm')->exists();
            if(!$table)
            {
                $emailtemplate=  EmailTemplate::create(
                    [
                    'name' => $eTemp,
                    'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Automas ERP',
                    'module_name' => 'Hrm',
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