<?php
require_once ROOT . 'config/email-config.php';
require_once ROOT . 'vendor/PHPMailer/PHPMailer.php';
require_once ROOT . 'vendor/PHPMailer/SMTP.php';
require_once ROOT . 'vendor/PHPMailer/Exception.php';

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    /** 
     * Send email to a specific email address
     * 
     * @param string $email Recipient's email address
     * @param string $name Recipient's first name
     * @param string $surname Recipient's last name
     * @param string $subject Subject of the email
     * @param string $body HTML body of the email
     * @param string $altBody Plain text alternative body of the email
     * 
     * @return void
     * @throws Exception If a problem occurs during sending the email
     */
    public function sendEmail(
        string $email,
        string $name,
        string $surname,
        string $subject,
        string $body,
        string $altBody
    ): void {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = SMTP_SERVER;                            //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = SMTP_USERNAME;                          //SMTP username
            $mail->Password   = SMTP_PASSWORD;                          //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = SMTP_PORT;                              //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(SEND_FROM, SEND_FROM_NAME);
            $mail->addAddress($email, $name . " " . $surname);         //Add a recipient
            $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;

            // Send the email
            $mail->send();
        } catch (Exception $e) {
            logError("EmailService.php: Email couldn't be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
