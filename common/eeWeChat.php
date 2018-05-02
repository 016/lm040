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

use yii\base\Object;
use common\models\AppUser;


/**
 * WeChat function class
 * @author boylee
 *
 */

class eeWeChat extends Object{
    private $appId;
    private $appSecret;
    private $appToken;
    
    public $code; //redirect code
    public $openId;
    public $unionId;
    public $accessToken;
    public $refreshToken;
    public $userInfo;
    
    
    public function init(){
        parent::init();
        
        $this->code = \Yii::$app->request->getQueryParam('code');
        $state = \Yii::$app->request->getQueryParam('state');
        
        
        $this->appId = \Yii::$app->params['wechat.appId'];
        $this->appSecret = \Yii::$app->params['wechat.appSecret'];
        if ($state == 'wechatLogin') {
            $this->appId = \Yii::$app->params['wechat.webApp.appId'];
            $this->appSecret = \Yii::$app->params['wechat.webApp.appSecret'];            
        }//webApp, need use different id and secret.
        
        
        $this->appToken = \Yii::$app->params['wechat.token'];
        
        
    }
    
    
    /**
     * for wechat url validate.
     */
    public function validateSignature() {
    	//wechat open account validate
    	$request = \Yii::$app->getRequest();
        $signature = $request->getQueryParam("signature");
        $timestamp = $request->getQueryParam("timestamp");
        $nonce = $request->getQueryParam("nonce");
        $echostr = $request->getQueryParam("echostr");
            		
    	$tmpArr = array($this->appToken, $timestamp, $nonce);
    	sort($tmpArr, SORT_STRING);
    	$tmpStr = implode( $tmpArr );
    	$tmpStr = sha1( $tmpStr );
    	
    	if( $tmpStr == $signature ){
    		echo $echostr;
    		exit;
    	}else{
    		return false;
    	}
    }
    
    /**
     * load access_token from wechat api.
     */
    public function getAccessToken() {
        
        if (YII_ENV_DEV) {
//             echo 'dev.';
            $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        }else{
//             echo 'live';
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$this->code&grant_type=authorization_code";
            $response = eeNet::load($url);
        }


        $res = json_decode($response);
        @$this->accessToken = $res->access_token;
        @$this->refreshToken = $res->refresh_token;
        @$this->openId = $res->openid;
        @$this->unionId = $res->unionid;
        
        //save openId in session
        \Yii::$app->session->set('wechat.openId', $this->openId);
    }
    
    /**
     * try login user with unionID, if not found try get access token and login again
     */
    public function loginUser() {
        if (empty($this->unionId)) {
            return 0;
        }//direct exit if no user found.
        
        //check appUser exist in db
        $appUser = AppUser::find()->where(['au_wechatUnionId'=>$this->unionId])->one();
        
        if (empty($appUser)) {
            //try create new user.
            $appUser = new AppUser(['scenario'=>'wechatReg']);
            $appUser->au_wechatUnionId = $this->unionId;
            $appUser->save();
        }
        
        //login into global session
        $appUser->loginUser();
        
    }
    
    /**
     * get user info by access token from wechat API
     */
    public function getUserInfo(){
        
    }
    
}