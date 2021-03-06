<?php
namespace eeTools\common;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 *
 */


class eeMail{
    /**
     * send email by mailgun api v3
     *
     * @param string/array $toObj || boy.lee.here@gmail.com or [['mail'=>'boy.lee.here@gmail.com', 'name'=>'Lee'], ...]
     * @param string $subject
     * @param string $body
     * @return string
    
     * @return mixed
     */
    public static function sendMailGunAPI($toObj, $subject = '', $body = ''){
        $to = '';
        $recipient_variable = [];
    
        $mode = 'single';
    
        if (is_string($toObj)) {
            $to = $toObj;
        }else{
            $mode = 'batch';
            //generate correct to and recipient data
            foreach ($toObj as $key => $oneTo) {
                if (!empty($to)) {
                    $to .= ', ';
                }
                $to .= $oneTo['mail'];
                $recipient_variable[$oneTo['mail']] = ['first'=>$oneTo['name'], 'id'=>$key];
            }
    
        }
    
    
    
    
        //         var_dump($recipient_variable);
        //         var_dump($to);
        //         exit;
    
        $config = array();
    
        $config['api_key'] = \Yii::$app->params['mailGun_API_key'];
    
        $config['api_url'] = \Yii::$app->params['mailGun_API_url'];
    
        $message = array();
    
        $message['from'] = \Yii::$app->params['adminEmail'];
    
        $message['to'] = $to;
        if ($mode == 'batch') {
            $message['recipient-variables'] = json_encode($recipient_variable);
        }
    
        $message['subject'] = $subject;
    
        $message['html'] = $body;
    
        //         eeDebug::varDump($message);
    
        $opt = [];
        $opt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $opt[CURLOPT_USERPWD] = "api:{$config['api_key']}";
    
    
        return eeNet::postCurl($message, $config['api_url'], [], $opt, 1);
    
    }
    
    /**
     * send mail by sendcloud api
     * based on v2.0 email send api http://www.sendcloud.net/doc/email_v2/send_email/
     * @param unknown $toArr [['mail'=>'xxx'], [xx]]
     * @param string $subject
     * @param string $body
     * @param \CURLFile $file 
     * @return mixed
     */
    public static function sendMailSCAPI($toArr, $subject = '', $body = '', $file = null) {
        $to = '';
        $recipient_variable = [ ];
    
        // generate correct to and recipient data
        foreach ( $toArr as $key => $oneTo ) {
            if (! empty ( $to )) {
                $to .= ', ';
            }
            $to .= $oneTo ['mail'];
        }
    
        // var_dump($recipient_variable);
        //         var_dump($to);
        //         exit;
    
    
        $message = [];
        $message ['apiKey'] = \Yii::$app->params ['sendcloud_API_key'];
    
        $message ['apiUser'] = \Yii::$app->params ['sendcloud_API_user'];
    
    
        $message ['from'] = \Yii::$app->params ['adminEmail'];
    
        $message ['to'] = $to;
    
        $message ['subject'] = $subject;
    
        $message ['html'] = $body;

        if ($file !== null) {
            $message['attachments'] = $file;
        }
    
        $ch = curl_init ();

        curl_setopt ( $ch, CURLOPT_URL, \Yii::$app->params ['sendcloud_API_url'] );
    
        curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
    
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );



    
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
    
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );


    
        // curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $message );
    
        $result = curl_exec ( $ch );

        if($result === false) {
            $result = curl_error($ch);
        }

    
        curl_close ( $ch );
    
        return $result;
    }
    
    /**
     * send email direct from server
     * @param unknown $to
     * @param unknown $subject
     * @param unknown $body
     * @param string $from
     * @return boolean
     */
    public static  function sendServerMail($to, $subject, $body, $from = ''){
        if (empty($from)) {
            $from = \Yii::$app->params['adminEmail'];
        }
        
        $mailer = \Yii::$app->mailer->compose();
        $mailer->setFrom($from);
        $mailer->setTo($to);
        $mailer->setSubject($subject);
        $mailer->setTextBody('5 PM?');
        $mailer->setHtmlBody($body);
        return $mailer->send();
    }
}