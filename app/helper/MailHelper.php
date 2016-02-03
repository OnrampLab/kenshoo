<?php
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class MailHelper
{
    public static function sendStart( $message="kenshoo email by Simply Bridal" )
    {
        $subject = '[auto] kenshoo start - '. date("Y-m-d H:i:s");
        self::send($subject, $message);
    }

    public static function sendSuccess( $message="kenshoo email by Simply Bridal" )
    {
        $subject = '[auto] kenshoo success - '. date("Y-m-d H:i:s");
        self::send($subject, $message);
    }

    public static function sendFail( $message="kenshoo email by Simply Bridal" )
    {
        $subject = '[auto] kenshoo fail - '. date("Y-m-d H:i:s");
        self::send($subject, $message);
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    private static function send($subject, $body)
    {
        $mail = new Message;
        $mail->setFrom('kenshoo upload status <localhost@localhost.com>')
            ->addTo('lawrence@lngmgmt.com')
            ->addTo('Davidc@lngmgmt.com')
            ->addTo('chris.tou@simplybridal.com')
            ->addTo('fobtastic.chris@gmail.com')
            ->addTo('higeno@hotmail.com')
            ->addTo('Brian Lee <brian.lee@simplybridal.com>')
            ->addTo('glenn.guan@simplybridal.com')
            ->setSubject($subject)
            ->setBody($body);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

}

