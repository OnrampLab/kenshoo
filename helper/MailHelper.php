<?php
use Nette\Mail\Message;

class MailHelper
{
    public static function send()
    {
        $mail = new Message;
        $mail->setFrom('glenn <glenn.profile@gmail.com>')
            //->addTo('peter@example.com')
            ->setSubject('[auto] kenshoo execute start -'. date("Y-m-d H:i:s"))
            ->setBody("kenshoo start");
    }

}