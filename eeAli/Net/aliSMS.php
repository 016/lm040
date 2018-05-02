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
class aliSMS extends aliBased {
    
    protected $paramString; //for signle send
    protected $recNum; //for signle send
    protected $templateParam; //for batch send
    protected $phoneNumbers; //for batch send
    protected $signName;
    protected $templateCode;
    
    public function init() {
        parent::init ();
        $this->signName = \Yii::$app->params['sms.aliyun.signName'];
        $this->templateCode = \Yii::$app->params['sms.aliyun.tempCode'];
        
    }
    
    /**
     * send SMS by phone and given params
     */
    public function sendSingleSMS($phone, $params) {
        $this->action = 'SingleSendSms';
        $this->paramsList['paramString'] = 'ParamString';
        $this->paramsList['recNum'] = 'RecNum';
        $this->paramsList['signName'] = 'SignName';
        $this->paramsList['templateCode'] = 'TemplateCode';
        
        $this->version = '2016-09-27';
//         $this->product = '';
        $this->urlbased = 'https://sms.aliyuncs.com/?';

        //prepare params
        $this->recNum = $phone;
        $this->paramString = json_encode($params);
        
        $this->computeSignature();
        
        $this->computeUrl();
//         var_dump($this->url);
        // send by api
        $response = eeNet::load ( $this->url );
//         var_dump($response);
        $res = json_decode($response);
        
//         var_dump($res);
        
        return $res;
        
    }
    
    
    /**
     * send batch SMS by phone and given params
     * phone accetp 1300000000,1300000001
     */
    public function sendSMS($phone, $params) {

        $this->action = 'SendSms';
        $this->paramsList['templateParam'] = 'TemplateParam';
        $this->paramsList['phoneNumbers'] = 'PhoneNumbers';
        $this->paramsList['signName'] = 'SignName';
        $this->paramsList['templateCode'] = 'TemplateCode';
        
        
        $this->version = '2017-05-25';
        $this->product = 'Dysmsapi';
        $this->urlbased = 'https://dysmsapi.aliyuncs.com/?';
    
        //prepare params
        $this->phoneNumbers = $phone;
        $this->templateParam = json_encode($params);
    
        $this->computeSignature();
    
        $this->computeUrl();
        //         var_dump($this->url);
        // send by api
        $response = eeNet::load ( $this->url );
        //         var_dump($response);
        //         exit;
        $res = json_decode($response);
    
        //         var_dump($res);
    
        return $res;
    
    }
    
}