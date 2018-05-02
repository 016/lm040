<?php

namespace eeTools\eeWeChat\Tools;

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

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */
class wcMenu extends eeWeChatBased {
    
    /**
     * get user info by access token from wechat API
     */
    public function updateMenu($buttons = []) {
        $this->getAppAccessToken();
        
//         eeDebug::varDump($this->appAccessToken);
        
        //update menu by post request
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->appAccessToken;
        
        //buttons
        if (empty($buttons)) {
//             $buttons[] = ['type'=>'view', 'name'=>'帮助', 'url'=>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe680754ccce9d9de&redirect_uri=http%3a%2f%2ftest.purekiwi.cn%2ffrontend%2fweb%2findex.php%2fissue%2fcategory&response_type=code&scope=snsapi_userinfo&state=insideWeChat#wechat_redirect'];
            $buttons[] = ['type'=>'click', 'name'=>'帮助', "key"=>"SPLASH_Welcome"];
            $buttons[] = ['type'=>'view', 'name'=>'帐户', 'url'=>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe680754ccce9d9de&redirect_uri=http%3a%2f%2ftest.purekiwi.cn%2ffrontend%2fweb%2findex.php%2fclient%2finfo&response_type=code&scope=snsapi_userinfo&state=insideWeChat#wechat_redirect'];
            $buttons[] = ['type'=>'view', 'name'=>'水滴国际', 'url'=>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe680754ccce9d9de&redirect_uri=http%3a%2f%2ftest.purekiwi.cn%2ffrontend%2fweb%2findex.php%2fsite%2findex&response_type=code&scope=snsapi_userinfo&state=insideWeChat#wechat_redirect'];
//             $subButtons = [];
//             $subButtons[] = ['type'=>'view', 'name'=>'你是?', 'url'=>'http://yiilib.com'];
//             $subButtons[] = ['type'=>'view', 'name'=>'许大玲', 'url'=>'http://yiilib.com/topic'];
//             $subButtons[] = ['type'=>'view', 'name'=>'对不对', 'url'=>'http://yiilib.com/topic'];
//             $buttons[] = ['name'=>'点我!点我!', 'sub_button'=>$subButtons];
//             $subButtons = [];
//             $subButtons[] = ['type'=>'view', 'name'=>'User', 'url'=>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxce4b89636a3d462f&redirect_uri=http%3a%2f%2ft965.yiilib.com%2ffrontend%2fweb%2findex.php%2fclient%2finfo&response_type=code&scope=snsapi_userinfo&state=insideWeChat#wechat_redirect'];
//             $subButtons[] = ['type'=>'view', 'name'=>'Help', 'url'=>'http://yiilib.com/topic'];
//             $subButtons[] = ['type'=>'view', 'name'=>'About', 'url'=>'http://yiilib.com/topic'];
//             $buttons[] = ['name'=>'lm965', 'sub_button'=>$subButtons];
        }
        
        $json = json_encode(['button'=>$buttons], JSON_UNESCAPED_UNICODE);
//         eeDebug::varDump($json);
        
        eeDebug::varDump(eeNet::postCurl($json, $url));
    }
    
    
    
}