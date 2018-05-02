<?php

namespace eeTools\eeWeChat\KF;

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
use eeTools\common\eeFile;
use yii\helpers\VarDumper;

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */
class wcKF extends eeWeChatBased {
    
    public $openId;
    public $unionId;
    public $accessToken;  //use accesstoken not service access token
    public $refreshToken;
    public $userInfo;
    
    public function init(){
        parent::init();
        
        //try load app level access token
        $this->getAppAccessToken();
    }
    
    /**
     * load access_token from wechat api.
     */
    public function addKF($kfInfo) {
        
        
        if (YII_ENV_DEV) {
            // echo 'dev.';
            $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
            // echo 'live';
            $url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=$this->appAccessToken";
            
            //new kf obj
            $response = eeNet::postCurl(json_encode($kfInfo), $url );
            var_dump($response);
            
            
        }
//         $res = json_decode ( $response );
//         var_dump($res);
    }
    
    /**
     * load access_token from wechat api.
     */
    public function listKF($online = false) {
        $kfList = [];
        
        if (YII_ENV_DEV) {
            // echo 'dev.';
            $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
            // echo 'live';
            $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=$this->appAccessToken";
            if ($online) {
                $url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=$this->appAccessToken";
            }
            
//             eeDebug::varDump($url);
//             eeFile::saveFile($url);
            
            $response = eeNet::load($url);
            $res = json_decode ( $response );
//             eeFile::saveFile(VarDumper::dumpAsString($res));
//             var_dump($res);
            if (isset($res->kf_list)) {
                $kfList = $res->kf_list;
            }
            if (isset($res->kf_online_list)) {
                $kfList = $res->kf_online_list;
            }
        }
        
        return $kfList;
    }
    
    /**
     * load access_token from wechat api.
     */
    public function inviteKF($kfInfo) {
        
        
        if (YII_ENV_DEV) {
            // echo 'dev.';
            $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
            // echo 'live';
            $url = "https://api.weixin.qq.com/customservice/kfaccount/inviteworker?access_token=$this->appAccessToken";
            
            //new kf obj
            $response = eeNet::postCurl(json_encode($kfInfo), $url );
            var_dump($response);
            
        }
//         $res = json_decode ( $response );
//         var_dump($res);
    }
    
    
}