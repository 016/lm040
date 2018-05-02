<?php

namespace eeTools\eeWeChat\Message;

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
class wcServiceMsg extends eeWeChatBased {
    
    public function init(){
        parent::init();
    }
    
    /**
     * load access_token from wechat api.
     */
    public function sendServiceMsg($toOpenId, $msg='eeTest') {
        
        $this->getAppAccessToken();
        
        $response = '';
        if (YII_ENV_DEV) {
//             eeDebug::varDump('dev');
            
//             $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
//             eeDebug::varDump('live');
            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$this->appAccessToken";
            
            //msg send obj
            $msgObj = [];
            $msgObj['touser'] = $toOpenId;
            $msgObj['msgtype'] = 'text';
            $msgObj['text'] = ['content'=>$msg];
            
            $response = eeNet::postCurl( json_encode($msgObj, JSON_UNESCAPED_UNICODE), $url );
        }
        $res = json_decode ( $response );
        
//         eeFile::saveFile(VarDumper::dumpAsString($res));
    }
    
    public function sendTemplateMsg($tempId, $toOpenId, $dataObj, $linkUrl) {
        
        //encode return url b4 send.
        $linkUrl = urlencode($linkUrl);
        $linkUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=$linkUrl&response_type=code&scope=snsapi_userinfo&state=insideWeChat#wechat_redirect";
        //             eeDebug::varDump($linkUrl);
        
        $this->getAppAccessToken();
        
        $response = '';
        if (YII_ENV_DEV) {
//             eeDebug::varDump('dev');
            
//             $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
//             eeDebug::varDump('live');
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$this->appAccessToken";
            
            //msg send obj
            $msgObj = [];
            $msgObj['touser'] = $toOpenId;
            $msgObj['template_id'] = $tempId;
            $msgObj['url'] = $linkUrl;
            $msgObj['topcolor'] = '#FF0000';
            $msgObj['data'] = $dataObj;
            
            $response = eeNet::postCurl( json_encode($msgObj, JSON_UNESCAPED_UNICODE), $url );
        }
        $res = json_decode ( $response );
        
        return $res;
    }
    
    
    
    public function getMSGList($startTime, $endTime, $msgid = 1, $number = 10000) {
        
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        
        if ($number > 10000) {
            $number = 10000;
        }//max
        if ($msgid < 1) {
            $msgid = 1;
        }//min
        
        $this->getAppAccessToken();
        
        $response = '';
        if (YII_ENV_DEV) {
//             eeDebug::varDump('dev');
            
//             $response = '{"access_token":"C_u41HrD_sLmncwgA5Q9kxmGl81PsvzmtK3r3clipwG2veiK-lhLOgbqW_0YWMDSdYFz2y1maHngF4a5QC_Nt7gdsGIrjdhFflZ-8dNHUgs","expires_in":7200,"refresh_token":"ZhhtjiAHivstW47cMfdeT2cL6Pjy1yiWOvDCe9YwHsNNL3GT8xhy1NREKPolvZwCQo3HWZQUZtO0IeRmxunpdrRrw7bc7imVBG-lC8a9-4o","openid":"ot2EGwQFjf6Wu4tKJN7ElMmYgkik","scope":"snsapi_userinfo","unionid":"oDLMXwnjPD_93kUEYWEOvRrIpSvQ"}';
        } else {
//             eeDebug::varDump('live');
            $url = "https://api.weixin.qq.com/customservice/msgrecord/getmsglist?access_token=$this->appAccessToken";
            
            //msg send obj
            $msgObj = [];
            $msgObj['starttime'] = $startTime;
            $msgObj['endtime'] = $endTime;
            $msgObj['msgid'] = $msgid;
            $msgObj['number'] = $number;
            
            $response = eeNet::postCurl( json_encode($msgObj, JSON_UNESCAPED_UNICODE), $url );
        }
        $res = json_decode ( $response );
        
        return $res;
    }
    
    
    
}