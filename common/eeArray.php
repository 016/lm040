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

class eeArray{

    /**
     * use value as key
     */
    public static function arrIndexAdd($inputArr, $t = true){
        if ($inputArr == null) {
            $inputArr = [];
        }
    	$returnArr = array();
    	foreach ($inputArr as $value) {
    	    if ($t) {
        	    $value = trim($value);
    	    }//trim
    		$returnArr[$value] = $value;
    	}
    	
    	return $returnArr;
    	
    }
    
    
    /**
     * use one second lvl value as key
     */
    public static function formatArr($inputArr, $keyIndex, $isObj = false){
        if ($inputArr == null) {
            $inputArr = [];
        }
        $returnArr = [];
        foreach ($inputArr as $oneI) {
            if ($isObj) {
                $returnArr[$oneI->$keyIndex] = $oneI;
            }else{
                $returnArr[$oneI[$keyIndex]] = $oneI;
            }
        }
         
        return $returnArr;
         
    }
    

    /**
     * array to string
     * $t type use 1 for key, 2 for value
     */
    public static function arrToString($inputArr, $t = 1, $glue = '-') {
        if ($inputArr == null) {
            $inputArr = [];
        }
    
        $returnString = '';
        foreach ( $inputArr as $key=>$value ) {
            if (!empty($returnString)) {
                $returnString .= $glue;
            }
    
            if ($t == 1) {
                $returnString .= trim ( $key );
            } // key
            if ($t == 2) {
                $returnString .= trim ( $value );
            } // value
    
        }
    
        return $returnString;
    }
}