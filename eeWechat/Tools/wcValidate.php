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

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */
class wcValidate extends eeWeChatBased {
    
    /**
     * for wechat url validate.
     */
    public function validateUrlSignature() {
        // wechat open account validate
        $request = \Yii::$app->getRequest ();
        $signature = $request->getQueryParam ( "signature", false );
        $timestamp = $request->getQueryParam ( "timestamp", false );
        $nonce = $request->getQueryParam ( "nonce", false );
        $echostr = $request->getQueryParam ( "echostr", false );
        
        if ($signature === false || $timestamp === false || $nonce === false || $echostr === false) {
            return false;
        }//only run if all 4 ready, for some kind msg request will has first 3 in request url.
        
        $tmpArr = array (
                $this->appToken,
                $timestamp,
                $nonce 
        );
        sort ( $tmpArr, SORT_STRING );
        $tmpStr = implode ( $tmpArr );
        $tmpStr = sha1 ( $tmpStr );
        
        if ($tmpStr == $signature) {
            echo $echostr;
            exit ();
        } else {
            return false;
        }
    }
    
    
    
}