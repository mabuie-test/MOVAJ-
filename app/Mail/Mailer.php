<?php

declare(strict_types=1);

namespace App\Mail;

use App\Core\Env;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    public function make(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string)Env::get('MAIL_HOST', '');
        $mail->Port = (int)Env::get('MAIL_PORT', 587);
        $mail->SMTPAuth = true;
        $mail->Username = (string)Env::get('MAIL_USERNAME', '');
        $mail->Password = (string)Env::get('MAIL_PASSWORD', '');

        $encryption = (string)Env::get('MAIL_ENCRYPTION', '');
        if ($encryption !== '') {
            $mail->SMTPSecure = $encryption;
        }

        return $mail;
    }
}
