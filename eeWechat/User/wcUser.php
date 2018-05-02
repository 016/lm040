<?php

namespace eeTools\eeWeChat\User;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use eeTools\common\eeNet;
use eeTools\eeWeChat\eeWeChatBased;
use eeTools\common\eeDebug;
use eeTools\common\eeDate;

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */
class wcUser extends eeWeChatBased {
    
    public $openId;
    public $unionId;
    public $accessToken;  //use accesstoken not service access token
    public $refreshToken;
    public $userInfo;
    
    public $nickname = '';
    public $sex = ''; //1 - male, 0 - female
    public $language = ''; //en/cn
    public $city = '';
    public $province = '';
    public $country = '';
    public $headimgurl = '';
    
    public function init(){
        parent::init();
        
        //try load value from session when init, for refresh.
//         eeDebug::varDump(\Yii::$app->session->get ('wechat.openId'), false);
        $this->openId = \Yii::$app->session->get ('wechat.openId');
        $this->unionId = \Yii::$app->session->get ('wechat.unionId');
        $this->accessToken = \Yii::$app->session->get ('wechat.accessToken');
    }
    
    
    /**
     * load access_token from wechat api.
     */
    public function getUserAccessToken() {
        
        if (!empty($this->openId) && !empty($this->accessToken)) {
            return true;
        }//openId and accessToken already in session.
        
        if (YII_ENV_DEV) {
            // echo 'dev.';
            $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
            // echo 'live';
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$this->code&grant_type=authorization_code";
            $response = eeNet::load ( $url );
        }
        $res = json_decode ( $response );
//         var_dump($res);

        if (!isset($res->errcode)) {
            @$this->accessToken = $res->access_token;
            @$this->refreshToken = $res->refresh_token;
            @$this->openId = $res->openid;
            @$this->unionId = $res->unionid;
            
    //         var_dump($this->openId);
    //         var_dump($this->accessToken);
            // save openId in session
            \Yii::$app->session->set ( 'wechat.openId', $this->openId );
            \Yii::$app->session->set ( 'wechat.accessToken', $this->accessToken );
        }
    }
    
    /**
     * try login user with unionID, if not found try get access token and login again
     */
    public function loginUser() {
        if (empty ( $this->unionId )) {
            return 0;
        } // direct exit if no user found.
          
        // check appUser exist in db
        $appUser = AppUser::find ()->where ( [ 
                'au_wechatUnionId' => $this->unionId 
        ] )->one ();
        
        if (empty ( $appUser )) {
            // try create new user.
            $appUser = new AppUser ( [ 
                    'scenario' => 'wechatReg' 
            ] );
            $appUser->au_wechatUnionId = $this->unionId;
            $appUser->save ();
        }
        
        // login into global session
        $appUser->loginUser ();
    }
    
    /**
     * get user info by access token from wechat API
     */
    public function getUserInfo() {

        if (YII_ENV_DEV) {
            // echo 'dev.';
            $response = '{"openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","nickname":"XinHua Lee","sex":1,"language":"en","city":"连云港","province":"江苏","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/faYuoNAiaIjjnpoKm92GibhkzXucCAqGcibylHZmofD6ib3nSiaHPoHvM0ibwrX3CgYUGicibU98QsNKpDLwPbP5JLWUtQ\/0","privilege":[],"unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
            // echo 'live';
            $accessToken = \Yii::$app->session->get( 'wechat.accessToken');
            $openId = \Yii::$app->session->get( 'wechat.openId');
            
            
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openId&lang=zh_CN";
            $response = eeNet::load ( $url );
        }
        $res = json_decode ( $response );
//         eeDebug::varDump($res);
        if (!isset($res->errcode)) {
            $this->nickname = $res->nickname;
            $this->sex = $res->sex; //1 - male, 0 - female
            $this->language = $res->language; //en/cn
            $this->city = $res->city;
            $this->province = $res->province;
            $this->country = $res->country;
            $this->headimgurl = $res->headimgurl;            
        }
        
    }
    
    
}