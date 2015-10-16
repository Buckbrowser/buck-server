<?php

require_once "vendor/autoload.php";
require "core/Mail.php";
require_once "config/mail.php";

$mail = new Mail();

$mail->addAddress("wybren.kortstra@gmail.com", "Wybren Kortstra");

$mail->isHTML(true);

$mail->Subject = "Subject Text";
$mail->Body = "<i>Mail body in HTML</i>";
$mail->AltBody = "This is the plain text version of the email content";

if(!$mail->send())
{
    echo "Mailer Error: " . $mail->ErrorInfo;
}
else
{
    echo "Message has been sent successfully";
}