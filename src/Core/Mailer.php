<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Envoi d'e-mails.
 * - Si SMTP configuré (.env) et PHPMailer installé (composer) : envoi réel en SMTP/TLS.
 * - Sinon (développement) : l'e-mail est journalisé dans storage/logs/mail.log.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null): bool
    {
        $html = self::layout($subject, $body);

        if (env('MAIL_HOST') && class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            try {
                $m = new \PHPMailer\PHPMailer\PHPMailer(true);
                $m->isSMTP();
                $m->Host = env('MAIL_HOST');
                $m->Port = (int) env('MAIL_PORT', '587');
                $m->SMTPAuth = true;
                $m->Username = env('MAIL_USER', '');
                $m->Password = env('MAIL_PASS', '');
                $m->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $m->CharSet = 'UTF-8';
                $m->setFrom(env('MAIL_FROM', 'contact@vitegourmand.fr'), env('MAIL_FROM_NAME', 'Vite & Gourmand'));
                $m->addAddress($to);
                if ($replyTo) {
                    $m->addReplyTo($replyTo);
                }
                $m->isHTML(true);
                $m->Subject = $subject;
                $m->Body = $html;
                $m->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));
                return $m->send();
            } catch (\Throwable $e) {
                error_log('Mailer : ' . $e->getMessage());
                return false;
            }
        }

        $log = sprintf(
            "==== %s ====\nÀ : %s\nSujet : %s\n%s%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $replyTo ? "Répondre à : $replyTo\n" : '',
            trim(strip_tags(str_replace(['<br>', '</p>', '</li>'], "\n", $body)))
        );
        return file_put_contents(ROOT_PATH . '/storage/logs/mail.log', $log, FILE_APPEND | LOCK_EX) !== false;
    }

    private static function layout(string $title, string $body): string
    {
        return '<!doctype html><html lang="fr"><body style="margin:0;background:#FBF6EE;font-family:Arial,sans-serif;color:#2B2220">'
            . '<div style="max-width:560px;margin:0 auto;padding:32px 24px">'
            . '<p style="font-family:Georgia,serif;font-size:22px;color:#6B1E2E;margin:0 0 24px">Vite &amp; Gourmand</p>'
            . '<h1 style="font-size:18px;margin:0 0 16px">' . e($title) . '</h1>'
            . '<div style="font-size:15px;line-height:1.6">' . $body . '</div>'
            . '<p style="font-size:12px;color:#6b5f5a;margin-top:32px">Vite &amp; Gourmand — Traiteur à Bordeaux depuis 25 ans</p>'
            . '</div></body></html>';
    }
}
