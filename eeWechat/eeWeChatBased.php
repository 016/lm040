<?php

namespace eeTools\eeWeChat;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use yii\base\Object;
use common\models\AppUser;
use eeTools\common\eeNet;
use eeTools\common\eeDebug;
use yii\web\HttpException;

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */
class eeWeChatBased extends Object {
    
    protected  $appId;
    protected  $appSecret;
    protected $appToken;
    protected $appAccessToken;

    public $code; // redirect code
    
    public function init() {
        parent::init ();
        
        $code = '';
        if (isset($_GET['code'])) {
            $this->code = $_GET['code'];
            
        }
        
        $state = '';
        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        }
    //         eeDebug::varDump(\Yii::$app->request->absoluteUrl, false);
    //         eeDebug::varDump($this->code);
        
        $this->appId = \Yii::$app->params ['wechat.appId'];
        $this->appSecret = \Yii::$app->params ['wechat.appSecret'];
        if ($state == 'wechatLogin') {
            $this->appId = \Yii::$app->params ['wechat.webApp.appId'];
            $this->appSecret = \Yii::$app->params ['wechat.webApp.appSecret'];
        } // webApp, need use different id and secret.
        
        $this->appToken = \Yii::$app->params ['wechat.token'];
    }
    
    /**
     * load app level access_token from wechat api.
     * this token need cache for 2 hours!, we used 1.6 hours instand, use cache logic for cache
     */
    public function getAppAccessToken() {
//         echo '1';
        if (YII_ENV_DEV) {
            return '';
        }//skip for dev environment
//         echo '2';
        //get wechat.appAccessToken in cache
        $cache = \Yii::$app->cache;
        $key = 'wecaht.appAccessToken';
        
        $appAccessToken = $cache->get($key);
        if (empty($appAccessToken)) {
            //load new one from wechat API
            $url = "https://api.weixin.qq.com/cgi-bin/token?appid=$this->appId&secret=$this->appSecret&grant_type=client_credential";
            $response = eeNet::load ( $url );
            $res = json_decode($response);
//             var_dump($res);
            
            $appAccessToken = $res->access_token;
            //save into cache
            $cache->set($key, $appAccessToken, \Yii::$app->params['wechat.appAccessTokenExpired']);
        }
        
        $this->appAccessToken = $appAccessToken;
                
        // save openId in session
        \Yii::$app->session->set ( 'wechat.appAccessToken', $this->appAccessToken );
    }
    
    
    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml()
    {
        $this->makeSafeValues();
         
        if(!is_array($this->safeValues)
                || count($this->safeValues) <= 0)
        {
            throw new HttpException(404, "数组数据异常！");
        }
         
        $xml = "<xml>";
        foreach ($this->safeValues as $oneKey=>$tmpVal)
        {
            if (is_numeric($tmpVal)){
                $xml.="<".$oneKey.">".$tmpVal."</".$oneKey.">";
            }else{
                $xml.="<".$oneKey."><![CDATA[".$tmpVal."]]></".$oneKey.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    public function generateXML($xmlArr = [])
    {
        $xml = "<xml>";
        foreach ($xmlArr as $oneKey=>$tmpVal)
        {
            if (is_array($tmpVal)) {
                $xml .= "<$oneKey>";
                foreach ($tmpVal as $secKey => $secVal) {
                    if (is_numeric($secVal)){
                        $xml.="<".$secKey.">".$secVal."</".$secKey.">";
                    }else{
                        $xml.="<".$secKey."><![CDATA[".$secVal."]]></".$secKey.">";
                    }
                }
                $xml .= "</$oneKey>";
            }else{
                if (is_numeric($tmpVal)){
                    $xml.="<".$oneKey.">".$tmpVal."</".$oneKey.">";
                }else{
                    $xml.="<".$oneKey."><![CDATA[".$tmpVal."]]></".$oneKey.">";
                }
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if(!$xml){
            throw new HttpException(404, "xml数据异常！");
        }
        
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $tmpValues = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    
//         $this->attributes = $tmpValues;
    
        return $tmpValues;
    }
    
    
}