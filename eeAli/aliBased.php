<?php

namespace eeTools\eeAli;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use yii\base\Object;
use eeTools\common\eeNet;
use eeTools\common\eeDebug;
use yii\web\HttpException;

/**
 * Aliyun OS Based Class
 * 
 * @author boylee
 *        
 */
class aliBased extends Object {
    
    protected $verb; // GET OR POST for generate signature
    protected $action;
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $format;
    protected $regionId;
    protected $signatureMethod;
    protected $signatureNonce;
    protected $signatureVersion;
    protected $timestamp;
    protected $version;
    protected $signature;
    protected $paramsList;
    protected $product;
    
    public $urlbased;
    public $url;

    public function init() {
        parent::init ();
        
        // init
        
        $this->urlbased = 'https://sms.aliyuncs.com/?';
        $this->verb = 'GET';
        
        
        $this->accessKeyId = \Yii::$app->params['sms.aliyun.accessKeyId'];
        $this->accessKeySecret = \Yii::$app->params['sms.aliyun.accessKeySecret'];
        $this->format = 'JSON';
        $this->signatureMethod = 'HMAC-SHA1';
        $this->signatureNonce = uniqid();
        $this->signatureVersion = '1.0';
        $this->version = '2016-09-27';
        
        
        // switch timezone
        date_default_timezone_set("UTC");
        $this->timestamp = date('c');
        date_default_timezone_set('PRC');
        
        // the list use in url compute
        $paramsList = [];
        $paramsList['accessKeyId'] = 'AccessKeyId';
        $paramsList['action'] = 'Action';
        $paramsList['format'] = 'Format';
        $paramsList['signatureMethod'] = 'SignatureMethod';
        $paramsList['signatureNonce'] = 'SignatureNonce';
        $paramsList['signatureVersion'] = 'SignatureVersion';
        $paramsList['timestamp'] = 'Timestamp';
        $paramsList['version'] = 'Version';
        
        
        $this->paramsList = $paramsList;
        
    }
    
    public function computeUrl() {
        $this->url = $this->urlbased . $this->generateQueryString();
    }
    
    public function computeSignature() {
        $stringToSign = $this->verb . '&%2F&' . $this->percentEncode($this->generateQueryString());
        $this->signString($stringToSign);
        
        // add signature into list after signature generated.
        $this->paramsList['signature'] = 'Signature';
    }
    
    /**
     * generate query string by paramsList
     * @return string
     */
    public function generateQueryString(){
        ksort($this->paramsList);
        $canonicalizedQueryString = '';
        foreach($this->paramsList as $key => $value)
        {
            $canonicalizedQueryString .= '&' . $this->percentEncode($value). '=' . $this->percentEncode($this->$key);
        }
        
        return substr($canonicalizedQueryString, 1);
    }
    

    /**
     * remove invalid word
     */
    public function percentEncode($str){
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    
    /**
     * encode and calculate sign string with hash alg
     * @param string $source
     */
    public function signString($source){
        $accessSecret = $this->accessKeySecret.'&';
        $this->signature = base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }
    
    
}