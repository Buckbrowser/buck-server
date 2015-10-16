<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 15-10-15
 * Time: 17:38
 */

class Mail extends PHPMailer{

    public $db;
    public $token;

    function __construct()
    {
        parent::__construct();

//Set PHPMailer to use SMTP.
        $this->isSMTP();
//Set SMTP host name
        $this->Host = MAIL_HOST;
//Set this to true if SMTP host requires authentication to send email
        $this->SMTPAuth = true;
//Provide username and password
        $this->Username = MAIL_USER;
        $this->Password = MAIL_PASS;
//If SMTP requires TLS encryption then set it
        $this->SMTPSecure = "tls";
//Set TCP port to connect to
        $this->Port = 587;

        $this->From = FROM_MAIL_INFO_ADDRESS;
        $this->FromName = FROM_MAIL_INFO_NAME;

//        $this->SMTPDebug = 3;
    }

}