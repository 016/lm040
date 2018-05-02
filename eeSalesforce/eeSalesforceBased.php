<?php

namespace eeTools\eeSalesforce;

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
use common\models\SiteElement;
use common\components\CommonConst;

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */

class eeSalesforceBased extends Object {
    
    protected $appKey;
    protected $appSecret;
    protected $appLoginUsername;
    protected $appLoginPassword;
    protected $appAccessToken;
    protected $appInstanceUrl;

    public function init() {
        parent::init ();
        
        //load params from params.php or db
        $this->appKey = \Yii::$app->params ['sf.app.key'];
        $this->appSecret = \Yii::$app->params ['sf.app.secret'];
        $this->appLoginUsername = \Yii::$app->params ['sf.app.loginUsername'];
        $this->appLoginPassword = '';
        
        //lad password from db.
        $seLoginPass = SiteElement::find()->where(['se_code'=>CommonConst::SECODE_SFAPILOGINPASS])->one();
        if (!empty($seLoginPass)){
            $this->appLoginPassword = $seLoginPass->se_v4;
        }
        
    }
    
    /**
     * load app level access_token from wechat api.
     * this token need cache for 2 hours!, we used 1.6 hours instand, use cache logic for cache
     */
    public function getAppAccessToken($cleanToken = false) {
        
        //get wechat.appAccessToken in cache
        $cache = \Yii::$app->cache;
        $key = 'sf.appAccessToken';
        $keyIns = 'sf.appInstanceUrl';
        
        $appAccessToken = $cache->get($key);
        $appIns = $cache->get($keyIns);
        if (empty($appAccessToken) || $cleanToken) {
            //load new one from wechat API
            $url = "https://login.salesforce.com/services/oauth2/token?grant_type=password&client_id=$this->appKey&client_secret=$this->appSecret&username=$this->appLoginUsername&password=$this->appLoginPassword";
            $response = eeNet::postCurl([], $url, [], 30, true );
            $res = json_decode($response);
            
            if (isset($res->access_token)) {
                $appAccessToken = $res->access_token;
                $appIns = $res->instance_url;
                //save into cache
                $cache->set($key, $appAccessToken, \Yii::$app->params['sf.appAccessTokenExpired']);
                $cache->set($keyIns, $appIns, \Yii::$app->params['sf.appAccessTokenExpired']);
            }
        }
        
        $this->appAccessToken = $appAccessToken;
        $this->appInstanceUrl = $appIns;
                
        // save openId in session
        \Yii::$app->session->set ( 'wechat.appAccessToken', $this->appAccessToken );
    }
    
    
}