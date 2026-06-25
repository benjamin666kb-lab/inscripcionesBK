<?php
require "mail_config.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";
require "PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';

$mail->isSMTP();
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_USER, "Test");
$mail->addAddress(SMTP_USER);

$mail->Subject = "TEST SMTP";
$mail->Body = "Hola prueba";

$mail->send();