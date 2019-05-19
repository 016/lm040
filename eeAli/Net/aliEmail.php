<?php

namespace eeTools\eeAli\Net;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use yii\base\Object;
use yii\web\HttpException;
use eeTools\eeAli\aliBased;
use yii;
use eeTools\common\eeNet;

/**
 * https://sms.aliyuncs.com/
 * send sms via aliyun API
 * 
 * @author boylee
 *        
 */
class aliEmail extends aliBased {
    
    protected $accountName;
    protected $replyToAddress = 'true';
    protected $addressType = 1;
    protected $toAddress;
    protected $subject;
    protected $htmlBody;
    
    protected $regionId;
    
    public function init() {
        parent::init ();
        
        //clean
        $this->paramsList['regionId'] = 'RegionId';
        
        $this->paramsList['accountName'] = 'AccountName';
        $this->paramsList['replyToAddress'] = 'ReplyToAddress';
        $this->paramsList['addressType'] = 'AddressType';
        $this->paramsList['toAddress'] = 'ToAddress';
        $this->paramsList['subject'] = 'Subject';
        $this->paramsList['htmlBody'] = 'HtmlBody';
        
        
        
        $this->accountName = \Yii::$app->params['email.aliyun.accountName'];
        
        $this->urlbased = 'https://dm.aliyuncs.com/?';
    }
    
    /**
     * send mail to user
     */
    public function sendMail($emailAdd, $subject, $body = '') {
        $this->action = 'SingleSendMail';
        
        //Version=2015-11-23
        $this->version = '2015-11-23';
        
        //RegionId=cn-hangzhou
        $this->regionId = 'cn-hangzhou';
        
        $this->toAddress = $emailAdd;
        $this->subject = $subject;
        $this->htmlBody = $body;
        
        $this->computeSignature();
        
        $this->computeUrl();
        // var_dump($this->url);
        // exit;

        // send by api
        $response = eeNet::load ( $this->url );
//         var_dump($response);
        $res = json_decode($response);
        
//         var_dump($res);
        
        return $res;
        
    }
    
        
}