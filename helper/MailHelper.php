<?php
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class MailHelper
{
    public static function sendStart()
    {
        $subject = '[auto] kenshoo start - '. date("Y-m-d H:i:s");
        $body    = "kenshoo email by Simply Bridal";
        self::send($subject, $body);
    }

    public static function sendSuccess()
    {
        $subject = '[auto] kenshoo success - '. date("Y-m-d H:i:s");
        $body    = "kenshoo email by Simply Bridal";
        self::send($subject, $body);
    }

    public static function sendFail()
    {
        $subject = '[auto] kenshoo fail - '. date("Y-m-d H:i:s");
        $body    = "kenshoo email by Simply Bridal";
        self::send($subject, $body);
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    private static function send($subject, $body)
    {
        $mail = new Message;
        $mail->setFrom('Debug Messager <localhost@localhost.com>')
            ->addTo('lawrence@lngmgmt.com')
            ->addTo('Davidc@lngmgmt.com')
            ->addTo('chris.tou@simplybridal.com')
            ->addTo('higeno@hotmail.com')
            ->addTo('Brian Lee <brian.lee@simplybridal.com>')
            ->addTo('glenn <glenn.profile@gmail.com>')
            ->setSubject($subject)
            ->setBody($body);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

}

