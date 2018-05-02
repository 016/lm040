<?php
namespace eeTools\common;


use Hashids\Hashids;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 *
 */

class eeString{
    /**
     * generate random string and return it.
     * @param number $len
     * @param number $type //1-only letter + number //2-full chars
     * @param number $prefixType //1 Y-M-D H:i:s //2-unixtimestamp
     * @return string
     */
    public static function randomString($len = 10, $type = 1, $prefixType = 0, $prefixConnection = ''){
    	$string = '';
    	$prefix = '';
    	
    	for ($i = 0; $i<$len; $i++){
    	    if ($type == 1) {
    	        $t = rand(0, 2);
    	        if ($t==0) {
    	        	$string .= rand(0, 9);
    	        }
    	        if ($t==1) {
            		$string .= chr(rand(65, 90));
    	        }
    	        if ($t==2) {
            		$string .= chr(rand(97, 122));
    	        }
    	    }//only letter + number
    	    if ($type == 2) {
        		$string .= chr(rand(33, 126));
    	    }//full chars
    	}
    	
    	if ($prefixType == 1) {
    		$prefix = date('Y-m-d H:i:s');
    	}
    	
    	if ($prefixType == 2) {
    		$prefix = time();
    	}
    	
    	if ($prefixType == 3) {
    	    $prefix = date('Ymd');
    	}
    	
    	if (!empty($prefix)) {
    		$prefix .= $prefixConnection;
    	}
    	
    	
    	
    	return $prefix.$string;
    }
    
    

    /**
     * load dynamic const values
     * @param unknown $key like 1
     * @param unknown $prefix like COMCOUNT_
     * @param string $constClass like common\models\CommonConst
     * @return string|mixed
     */
    public static function loadConstValue($key, $prefix, $constClass = 'common\models\CommonConst') {
        $constValue = '';
    
        $constName = "$constClass::$prefix$key";
    
        if (defined($constName)) {
            $constValue = constant($constName);
        }
    
        return $constValue;
    }
    
    /**
     * encode id to hash string
     * @param unknown $id
     */
    public static function hashEncodeInt($id) {
        $salt = \Yii::$app->params['hashid.salt'];
        $minLength = \Yii::$app->params['hashid.minLength'];
        $encodeString = '';
    
        $ha = new Hashids($salt, $minLength);
        $encodeString = $ha->encode($id);
    
    
        return $encodeString;
    }
    
    public static function hashDecodeInt($id) {
        $salt = \Yii::$app->params['hashid.salt'];
        $minLength = \Yii::$app->params['hashid.minLength'];
        $decodeString = '';
    
        $ha = new Hashids($salt, $minLength);
        $decodeString = $ha->decode($id);
    
    
        return @$decodeString[0];
    }
}