<?php

namespace core\models\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static $DEFAULT_HEADERS = [
        "MIME-Version" => "1.0",
        "Content-Type" => "text/html; charset=UTF-8"
    ];
    private $view;
    private $style_data;
    private $logo_image_data;
    private $logo_image_url;
    private $template_dir;
    private $mail_config;

    public function __construct($view, $template_dir, $mail_config) {
        global $final_global_template_vars;

        $this->view = $view;

        $this->style_data = trim(file_get_contents($_SERVER["PATH_TO_CORE"] . "library/css/mail.css"));

        $this->logo_image_data = base64_encode(file_get_contents($final_global_template_vars["swpg_module_list"]["site"]["absolute_path_to_this_module"] .
            "/library/images/CPTAC_certified_seal_101x101.gif"));

        $this->logo_image_url = $final_global_template_vars['site_logo'];

        $this->template_dir = $template_dir;

        $this->mail_config = $mail_config;
    }

    public function sendMail($template_file_name, array $from_addresses, array $to_addresses, array $bcc_addresses = [], $subject,
                             array $headers = [], array $context = []) {
        $headers = array_merge(self::$DEFAULT_HEADERS, $headers, [
            "From" => implode(", ", $from_addresses)
        ]);

        if (!empty($bcc_addresses)) {
            $headers["Bcc"] = implode(", ", $bcc_addresses);
        }

        mail(implode(", ", $to_addresses)
            , $subject
            , $this->view->fetch($template_file_name, array_merge([
                "base_template_file_name" => "mail.twig",
                "heading" => $subject,
                "logo_image_data" => $this->logo_image_data,
                "logo_image_mimetype" => "image/gif",
                "logo_image_url" => $this->logo_image_url,
                "style_data" => $this->style_data,
                "title" => $subject
            ], $context))
            , array_reduce(array_keys($headers), function (string $headers_str, string $header_name) use ($headers) {
            return ($headers_str . $header_name . ": " . $headers[$header_name] . "\r\n");
        }, ""));
    }

    /**
     * @throws Exception
     */
    public function sendSmtpMail($template_file_name, string $from_address, string $to_address, string $bcc_address,
                                 string $subject, array $headers = [], array $context = []): string
    {

        $message = $this->view->fetch($template_file_name, array_merge([
            "base_template_file_name" => "mail.twig",
            "heading" => $subject,
            "logo_image_data" => $this->logo_image_data,
            "logo_image_mimetype" => "image/gif",
            "logo_image_url" => $this->logo_image_url,
            "style_data" => $this->style_data,
            "title" => $subject
        ], $context));

        $mail = new PHPMailer();
        $mail->IsSMTP(); // This is the SMTP mail server

        $mail->SMTPSecure = $this->mail_config['smtp_secure'];
        $mail->Host = $this->mail_config['host'];
        $mail->Port = $this->mail_config['port'];
        $mail->SMTPAuth = $this->mail_config['smtp_auth'];
        $mail->Username = $this->mail_config['username'];
        $mail->Password = $this->mail_config['password'];
        $mail->SetFrom($from_address);
        $mail->AddAddress($to_address);
        $mail->Subject = $subject;
        $mail->MsgHTML($message);
        $mail->IsHTML(true);
        $mail->CharSet="utf-8";
        //$mail->AltBody(strip_tags($message));

        if(!$mail->Send()) {
            return "Error: " . $mail->ErrorInfo;
        } else {
            return "Sent";
        }
    }

}