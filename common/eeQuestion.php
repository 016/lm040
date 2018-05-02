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

/**
 * Question generate Class for iMath project
 * @author Lee
 */
class eeQuestion{
    
    ////////////////////////
    ////////////////////////
    //// Entry functions
    ////////////////////////
    ////////////////////////
    
    //delivery functions
    /**
     * we need >=2 methods for generate logic, so we build one delivery function as entry
     * add auto filter to skip not need equation.like has number > maxValue
     *
     *
     * operator type
     *  1 = 1+1  - generatePublic
     *  2 = 1-1  - ??generateMinus
     *  3 = 1x1  - generatePublic
     *  4 = 1/1  - generatePublic
     *  5 = 1+1-1 - ??
     *  6 = 1x1/1 - genereatePublic
     *  7 = 1+1-1x1/1 - ??
     * @param number $type
     * @param number $len
     * @param number $maxValue
     * @param string $sumValue
     * @param string $negativeRangeMax
     */
    public static function gCenter($type = 1, $len = 2, $maxValue = 99, $negativeRangeMax = 0, $floatBit = 0){
        $formulaObj = array();
        
        switch ($type) {
            
            case 1:
                // +
                $formulaObj = self::gPlus($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            case 101:
                // + limit sum value
                $formulaObj = self::gPlusLimitMax($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 2:
                // -
                $formulaObj = self::gMinus($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            case 102:
                // - limit sum value, we can direct use gMinus here because of the logic correct
                $formulaObj = self::gMinus($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 3:
                // *
                $formulaObj = self::gMulti($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 4:
                // /
                $formulaObj = self::gDivision($len, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 10:
                // + -
                $formulaObj = self::gMix($type, $len, 0, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 11:
                // * /
                $formulaObj = self::gMix($type, $len, 1, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            
            case 50:
                // + - * /
                $formulaObj = self::gMix($type, $len, 1, $maxValue, $negativeRangeMax, $floatBit);
            break;
            
            default:
                $formulaObj = self::gMix($type, $len, 0, $maxValue, $negativeRangeMax, $floatBit);
            break;
        }
        
        //check formula if not pass we need re-generate
        if (self::checkFormula($formulaObj, $type, $maxValue, $negativeRangeMax)) {
            return $formulaObj;
        }else {
            return self::gCenter($type, $len, $maxValue, $negativeRangeMax, $floatBit);
        }
    }    
    
    
    ////////////////////////
    ////////////////////////
    //// Main Functions
    ////////////////////////
    ////////////////////////
    
    /**
     * generate plus equation with negative support
     *
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    public static function gPlus($len = 1, $maxValue = 99, $negativeRangeMax = 0, $floatBit = 0){
//         var_dump($floatBit);exit;
        $formula = '';
        $minValue = 0;
        $type = 1;
        $op = self::loadOp($type);
        
        //result
        $result = 0;
        
        
        //start
        $rnObj = self::randNumber($maxValue, $minValue, 3, $negativeRangeMax, $floatBit);
        $startValue = $rnObj['n'];
        
        //how many left, used in loop
        $result = $startValue;
        
        $formula = $rnObj['n']; //we don't need () for first value.
        
        for ($i = 0; $i < $len; $i++) {
        
            //rand number
            $oneObj = self::randNumber($maxValue, $minValue, 3, $negativeRangeMax, $floatBit);
    
            //section display
            $sKey = mt_rand(0, 4); //20%
            if ($sKey == 2 && $i < $len-1) {
                //add special section
                $formula .= " $op ".self::generateSpecialSection($type, $maxValue, $oneObj['n'], $negativeRangeMax, $minValue, $floatBit);
    
                //because special section has 1 opereate we need minus 1 for it.
                $i++;
            }else{
                $formula .= " $op ".$oneObj['s'];
            }
    
            $result = bcadd($result, $oneObj['n'], $floatBit);//remove this value out left
            
        }
        
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        return $returnObj;
    }
    
    
    /**
     * generate plus equation with negative support
     *
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    public static function gPlusLimitMax($len = 1, $maxValue = 99, $negativeRangeMax = 0, $floatBit = 0){
//         var_dump($floatBit);exit;
        $formula = '';
        $minValue = 0;
        $type = 1;
        $op = self::loadOp($type);
        
        //result
        $rnObj = self::randNumber($maxValue, $minValue, 3, $negativeRangeMax, $floatBit);
        $result = $rnObj['n'];
        
        
        //start
        $rnObj = self::randNumber($result, $minValue, 3, $negativeRangeMax, $floatBit);
        $startValue = $rnObj['n'];
        
        //how many left, used in loop
        $left = bcsub($result, $startValue, $floatBit);
        
        $formula = $startValue; //we don't need () for first value.
        
        for ($i = 0; $i < $len; $i++) {
            
            if ($i == $len-1) {
                //rand number
                $oneObj = self::randNumber($left, $left, 3, $negativeRangeMax, $floatBit);
            }else{
                
                //rand number
                $oneObj = self::randNumber($left, $minValue, 3, $negativeRangeMax, $floatBit);
            }
    
            //section display
            $sKey = mt_rand(0, 4); //20%
            if ($sKey == 2 && $i < $len-1) {
                
                $tmpSum = $oneObj['n'];
                if ($i == $len - 2) {
                    $tmpSum = $left;
                }
                
                //add special section
                $formula .= " $op ".self::generateSpecialSection($type, $maxValue, $tmpSum, $negativeRangeMax, $minValue, $floatBit);
    
                //because special section has 1 opereate we need minus 1 for it.
                $i++;
            }else{
                $formula .= " $op ".$oneObj['s'];
            }
        
    
            $left = bcsub($left, $oneObj['n'], $floatBit);//remove this value out left
            
        }
        
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        return $returnObj;
    }

    
    
    /**
     * generate minus equation with negative support
     * 
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    public static function gMinus($len = 1, $maxValue = 99, $negativeRangeMax = 0, $floatBit = 0){
        $formula = '';
        $minValue = 0;
        $type = 2;
        $op = self::loadOp($type);
        
        //result
        $rnObj = self::randNumber($maxValue, $minValue, 3, $negativeRangeMax, $floatBit);
        $result = $rnObj['n'];

        //start
        $tmpR = $result;
        if ($tmpR <= 0) {
            $tmpR = 0;
        }
        $rnObj = self::randNumber($maxValue, $tmpR, 10, $negativeRangeMax,  $floatBit);
        $startValue = $rnObj['n'];

        
        //how many left, used in loop
//         $left = $startValue - $result;
        $left = bcsub($startValue, $result, $floatBit);
        
        $formula = $startValue;
        
        for ($i = 0; $i < $len; $i++) {
            
            if ($i == $len - 1) {
                //last round
                $formula .= " $op ". $left;
            }else{
                
                //rand number
                $oneObj = self::randNumber($left, $minValue, 3, $negativeRangeMax, $floatBit);
                
                //section display
                $sKey = mt_rand(0, 4); //20%
                if ($sKey == 2) {
                    if ($i == $len-2) {
                        $oneObj['n'] = $left;
                    }
                    //add special section
                    $formula .= " $op ".self::generateSpecialSection($type, $maxValue, $oneObj['n'], $negativeRangeMax, $minValue, $floatBit);
                    
                    //because special section has 1 opereate we need minus 1 for it.
                    $i++;
                }else{
                    $formula .= " $op ".$oneObj['s'];
                }
                
                $left = bcsub($left, $oneObj['n'], $floatBit);//remove this value out left
            }
        }
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        return $returnObj;
    }
    
    /**
     * generate multiply equation with negative support
     *
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    
    public static function gMulti($len = 1, $maxValue = 100, $negativeRangeMax = 0, $floatBit = 0){
        $floatBit = 0;
        $formula = '';
        $minValue = 0;
        $type = 3;
        $op = self::loadOp($type);
        $loop = 5;
        
        //result
        $result = '';
        

        //start
        $rnObj = self::randNumber($maxValue, $minValue, $loop, $negativeRangeMax, $floatBit);
        $startValue = $rnObj['n'];
        
        //how many left, used in loop
        $result = $startValue;
        
        $formula = $startValue;
        
        for ($i = 0; $i < $len; $i++) {
                
            //rand number
            $oneObj = self::randNumber($maxValue, $minValue, $loop, $negativeRangeMax, $floatBit);
            
            //section display
            $sKey = mt_rand(0, 4); //20%
            if ($sKey == 2 && $i < $len-1) {
                //add special section
                $formula .= " $op ".self::generateSpecialSection($type, $maxValue, $oneObj['n'], $negativeRangeMax, $minValue, $floatBit);
                
                //because special section has 1 opereate we need minus 1 for it.
                $i++;
            }else{
                $formula .= " $op ".$oneObj['s'];
            }
            
            $result *= $oneObj['n']; //remove this value out left
        }
        
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        return $returnObj;
    }
    
    
    /**
     * generate division equation with negative support
     *
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    public static function gDivision($len = 1, $maxValue = 100, $negativeRangeMax = 0, $floatBit = 0){
//         $floatBit = 0;
        $formula = '';
        $minValue = 1;
        $type = 4;
        $op = self::loadOp($type);
        $loop = 5;
        
        //result
        
        
        //start
        $rnObj = self::randNumber($maxValue, $minValue, $loop, $negativeRangeMax, $floatBit);
        $startValue = $rnObj['n'];
        $tmpObj = self::findFactor($startValue, $floatBit); //load result + first level B
        
        $left = $tmpObj['B'];
        $result = $tmpObj['A'];
        
        $tmpRate = mt_rand(0, 1);
        if ($tmpObj['A'] > $tmpObj['B'] && $tmpRate == 1) {
            $left = $tmpObj['A'];
            $result = $tmpObj['B'];            
        }//do a switch 50% use smaller as result
        
        $formula = $startValue;
        
        for ($i = 0; $i < $len; $i++) {
            
            if ($i == $len - 1) {
                //last round
                $formula .= " $op ".$left;
            }else{
                
                //rand number
                //this level A+B
                $oneObj = self::findFactor($left, $floatBit);
                
                $left = $oneObj['B'];
                
                $formula .= " $op ".$oneObj['A'];
                
            }
        
        }
        
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        return $returnObj;
    }
    
    /**
     * generate +- or x÷ or +-x÷ equation with negative support
     *
     * @param number $len
     * @param number $maxValue
     * @param number $negativeRangeMax
     */
    public static function gMix($type, $len = 1, $minValue = 0, $maxValue = 99, $negativeRangeMax = 0, $floatBit = 0){
        $formula = '';
        $loop = 5;
        
        //result format
        $formatResult = array();
        $formatResult['11'] = 2;
        $formatResult['50'] = 2;
        
        //result
        $result = 0;
        
        
        //start, get a value for start
        $rnObj = self::randNumber($maxValue, $minValue, $loop, $negativeRangeMax, $floatBit);
        $startValue = $rnObj['n'];
        
        $formula = $startValue;
        
        for ($i = 0; $i < $len; $i++) {
            
            $oneType = self::randType($type);
            
            //op
            $oneOp = self::loadOp($oneType);
            
            
        
            //rand number
            $oneObj = self::randNumber($maxValue, $minValue, $loop, $negativeRangeMax, $floatBit);
            
            
            //section display
            $sKey = mt_rand(0, 4); //20%
            if ($sKey == 2 && $i < $len-1) {
                //add special section
                $formula .= " $oneOp ".self::generateSpecialSection($oneType, $maxValue, $oneObj['n'], $negativeRangeMax, $minValue, $floatBit);
            
                //because special section has 1 opereate we need minus 1 for it.
                $i++;
            }else{
                $formula .= " $oneOp ".$oneObj['s'];
            }
            
    
        }
        
        
        @$result = eval("return $formula;");
        if ($result === false) {
            echo 'LeeDebug';
            var_dump($formula);
            
        }
        
        //run format result logic, usual we will format it to given decimal
        if (isset($formatResult[$type])) {            
            $result = number_format($result, $formatResult[$type], '.', ''); 
        }
        
        $returnObj['formula'] = $formula;
        $returnObj['result'] = $result;
        
//         var_dump($returnObj);
        return $returnObj;        
    }
    
    
	
	/**
	 * @param integer $type
	 *  
	 * @param boolean $nagetiveOpen 
	 * support nagetive operator or not
	 * 
	 * @param integer $len
	 * the length for the formula like 2 => 1+1 4 => 1+1-1+1  
	 * 
	 * @param integer $sum
	 * sum for this level
	 * 
	 * @param integer $max
	 * max value for result
	 * 
	 */
	public static function generatePublic($type = 1, $len = 1, $maxValue = 100, $sumValue = null, $negativeRangeMax = 0, $round = 0){
	    //-1 init work
	    $round++;
	    
	    //0 params
	    $formulaObj = array('formula'=>'', 'result'=>'');
	    $partA = '';
	    $partB = '';
	    $op = '';
	    
	    $lenA = 0;
	    $lenB = 0;
	    
	    //1. params load for first level
	    $paramsObj = self::generateParams($type, $maxValue, $sumValue, $negativeRangeMax);
	    $op = $paramsObj['op'];
	    
	    //split by len, if >=4 do split, else generate code by len-- each time
	    if ($len >= 4) {
	        $lenA = ceil($len/2);
	        $lenB = $len - $lenA;
	        
	        if (mt_rand(0, 1)){
    	        $lenA--;//remove this round from lenA	            
	        }else{
	            $lenB--;//remove this round from lenB
	        }
	        
	        $rA = self::generatePublic($type, $lenA, $maxValue, $paramsObj['valueA']['n'], $negativeRangeMax, $round);
	        $rB = self::generatePublic($type, $lenB, $maxValue, $paramsObj['valueB']['n'], $negativeRangeMax, $round);
	        $partA = $rA['formula'];
	        $partB = $rB['formula'];
	        
	        
	    }else{
	        //len --
	        $len --;
	        
	        $partA = $paramsObj['valueA']['s'];
	        
	        if ($len > 0) {
	            
    	        $rB = self::generatePublic($type, $len, $maxValue, $paramsObj['valueB']['n'], $negativeRangeMax, $round);	            
    	        $partB = $rB['formula'];	            
	        }else{
	            $partB = $paramsObj['valueB']['s'];
	        }
	        
	    }
	    
	           
    	$formulaObj['formula'] = $partA.$op.$partB;
	    $formulaObj['result'] = $paramsObj['result'];
	    
	    
	    //return
	    return $formulaObj;
	}
	
		
	
	


	
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	//// Support functions
	//////////////////////////////////////////////////////////////////////////////////////////	
	//////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * split input formula find invalid values
	 * split check logic, we check result and formula apart
	 * A. filter invalid num in equal
	 * B. filter for show 0 twice
	 * @param string $formula
	 * @param intager $type
	 * @param string $maxValue
	 * @param intater $zeroLimitCount if the count of zero disapper >= this value we will drop this formula to re-generate
	 * @param intater $oneLimitCount if the count of one disapper >= this value we will drop this formula to re-generate
	 * @return boolean
	 */
	public static function checkFormula($formulaObj, $type, $maxValue, $negativeRangeMax = 0, $resultFloatBit = 10) {
	    $checkResult = false; //flag for result check open
	    $checkResultMax = false; //check value for max
	    
	    $checkResultOne = false; //flag for result one check open
	    $checkResultOneRangeMax = 10; //rate to keep result = 1
	    
	    
	    $zeroLimitCount = 5; 
	    $oneLimitCount = 5;
	    
	    $checkResultType = array();
	    $checkResultType[3] = 1; //* limit sum value
	    $checkResultType[4] = 1;
	    $checkResultType[10] = 1; //+- 
	    $checkResultType[11] = 1; //*/ 
	    $checkResultType[101] = 1; //+ limit sum value
	    $checkResultType[102] = 1; //- limit sum value
	    $checkResultType[50] = 1; //*/ limit sum value
	    
	    //limit part
	    if ($type == 4) {
	        $oneLimitCount = EEH::getN($maxValue);
	        $checkResultOne = true;
	        
	        if ($resultFloatBit == 10) {
    	        $resultFloatBit = 2;
	        }
	    }//for division use max find one limit count
	    
	    if ($type == 11) {
	        $zeroLimitCount = 2;
	        $checkResultOne = true;
	        
	        if ($resultFloatBit == 10) {
    	        $resultFloatBit = 2;
	        }
	    }//for +-, zeroCount < 2, flotBit < 2
	    
	    if ($type == 50) {
	        $zeroLimitCount = 2;
	        if ($resultFloatBit == 10) {
	            $resultFloatBit = 2;
	        }
	    }//for division use max find one limit count
	    
// 	    var_dump($oneLimitCount);exit;

	    ////Result start
	    $formula = $formulaObj['formula'];
        $result = trim($formulaObj['result']);
	    if (isset($checkResultType[$type])) {
	        $checkResult = true;
	        
	        
	        if (empty($result) && $result !== 0) {
	            return false;
	        }//check for error type if error result = '' empty string
	        
	        if ($result < 0 && $negativeRangeMax == 0) {
	            return false;
	        }// result < 0 but we don't want negative value display
	        
	        //check result float bit
	        $reArr = explode('.', $result);
// 	        var_dump($reArr);
	        if (isset($reArr[1])) {
    	        if (strlen($reArr[1]) > $resultFloatBit) {
    	            return false;
    	        }//float bit too large
	        }
	        
	        //check 1 for type  = 4 
	    }//set result check flag + public validator
	    
	    if ($checkResult && $checkResultMax) {
	        if (abs($result) > $maxValue) {
	            return false;
	        }// result > max
	    }//result level max check
	    
	    if ($checkResult && $checkResultOne && $result == 1) {
	        $tmpRate = mt_rand(0, $checkResultOneRangeMax);
	        if ($tmpRate != 1) {
	            return false;
	        }// only keep when rand = 1
	    }//result level value = 1 check
	    	    
	    //// start check formula
	    //remove ()
	    $formula = str_replace('(', '', $formula);
	    $formula = str_replace(')', '', $formula);
	    
	    //replace operator to - for explode
	    $formula = str_replace('+', '-', $formula);
	    $formula = str_replace('*', '-', $formula);
	    $formula = str_replace('/', '-', $formula);
	    $numArr = explode('-', $formula);
	    
	    
// 	    var_dump($numArr);

	    
	    //loop if >$max or <-$max return false
	    $zeroCount = 0;
	    $oneCount = 0;
	    
	    foreach ($numArr as $oneNum) {  
	        if (abs($oneNum) > $maxValue) {
	            return false;
	        }
	        
	        $oneNum = trim($oneNum);
	        if ($oneNum === '') {
	            continue;
	        }//skip empty string. but keep 0
	        
	        if ($oneNum == 0) {
	            $zeroCount ++;
	        }
	        if ($oneNum == 1) {
	            $oneCount ++;
	        }
	        
	        if ($zeroCount >= $zeroLimitCount) {
	            return false;
	        }
	        if ($oneCount >= $oneLimitCount) {
	            return false;
	        }
	        
	    }
	    
	    return true;
	}
	
	/**
	 * input general type return detail type
	 * @param unknown $type
	 */
	public static function randType($type){
	    $detailType = 1;
	    
	    if ($type == 10) {
	        $detailType = mt_rand(1, 2);
	    }
	    if ($type == 11) {
	        $detailType = mt_rand(3, 4);
	    }
	    if ($type == 50) {
	        $detailType = mt_rand(1, 4);
	    }
	    
	    return $detailType;
	}
	
	/**
	 * input type return op
	 * @param int $type
	 */
	public static function loadOp($type){
	    $op = '+';
	    
	    switch ($type) {
	        case 1:
	            $op = '+';
	        break;
	        case 2:
	            $op = '-';
	        break;
	        case 3:
	            $op = '*';
	        break;
	        case 4:
	            $op = '/';
	        break;
	        
	        default:
	            $op = '+';
	        break;
	    }
	    
	    
	    return $op;
	}

	/**
	 * number rand function, here we have brim re-rand logic, if number == min or max we will use loop
	 * @param number $max
	 * @param number $min
	 * @param number $loopTime
	 * @param number $negativeRangeMax, max range for negative needle, negativeNeedle = mt_rand(0, negativeRangeMax) if == 1 show as negative.
	 *
	 * @return array['n'=>number, 's'=>formatted number]
	 */
	public static function randNumber($maxValue, $minValue = 0, $reRandTime=2, $negativeRangeMax = 0, $floatBit = 0){
	    
	    //10^$floatBit
	    $floatMultiple = pow(10, $floatBit);
	    
	    $maxValueOld = $maxValue;
	    //float support
	    $maxValue = bcmul($maxValue, $floatMultiple, $floatBit);
	    $minValue = bcmul($minValue, $floatMultiple, $floatBit);
	    
	    
	    $number = 0; //value
	    $numberString = 0; //display
	
	
	    //correct value
	    $number = mt_rand($minValue, $maxValue);
	    for ($i = 0; $i < $reRandTime; $i++) {
	        if ($number == $minValue || $number == $maxValue) {
	            $number = mt_rand($minValue, $maxValue);
	        }
	
	        if ($number < $maxValue/10) {
	            $number = mt_rand($minValue, $maxValue);
	        }
	
	    }
	
	    //negative add
	    $negativeNeedle = mt_rand(0, $negativeRangeMax);
	    if ($negativeNeedle == 1) {
	        //negative active
	        $number *= -1;
	    }
	    
	    //float support
	    $number = bcdiv($number, $floatMultiple, $floatBit);
	
	    if ($number < 0) {
	        $numberString = '('.$number.' ) ';
	    }else{
	        $numberString = $number;
	    }
	
	    $numObj = array();
	    $numObj['n'] = $number;
	    $numObj['s'] = $numberString;
	
	    return $numObj;
	}
	
	/**
	 * input sum, find all factor return 1 rand
	 * support sum < 0
	 * @param int $sum
	 *
	 */
	public static function findFactor($sum, $floatBit = 0){
	    if ($sum == 0) {
	        return array('A'=> 0, 'B'=> 0);
	        exit;
	    }
	    
	    $floatMultiple = pow(10, $floatBit);
	    
	    $sum = bcmul($sum, $floatMultiple, $floatBit);
// 	    var_dump($sum);
	    
	    $negative = 1;
	    $factorArr = array();
	    
	    if ($sum < 0) {
	        $negative = -1;
	        $sum = abs($sum);
	    }
	    
	    for ($i = 1; $i <= $sum; $i++) {
	
	        //force to use 10 for check different
	        $valueACheck = bcdiv($sum, $i, 10); //for check, x.xxxxxxxxxx
	        $valueACheckInt = bcdiv($valueACheck, 1, 0); //for check, x
	        $valueA = bcdiv($valueACheck, 1, $floatBit); //for return
	        
// 	        var_dump($valueA);
// 	        echo '<hr/>';
	        if (bcsub($valueACheck, $valueACheckInt, 10) == 0) {
	            
	            //only need remove $flooatMultiple once
	            if (mt_rand(0, 1)) {
	                //remove from vA
    	            $vA = bcdiv($i, $floatMultiple, $floatBit); // remove float out vA
    	            $vB = $valueA; //remove value check
    	            $vA = bcmul($vA, $negative, $floatBit); //fix +-
	            }else{
	                //remove from vB
    	            $vA = $i;
    	            $vB = bcdiv($valueA, $floatMultiple, $floatBit); //remove float out vB
    	            $vA = bcmul($vA, $negative, $floatBit); //fix +-
	                
	            }
	            
	            
	            $factorArr[] = array('A'=>$vA, 'B'=>$vB);
	            
	            if ($i > 1000 || count($factorArr) > 20) {
// 	                echo 'Lee4000';
	                break;
	            }//for performance reason we will break the code when we have enough sample
	        }
	    }
	    
// 	    var_dump($factorArr);
// 	    echo '<hr/>';

	    $index = mt_rand(0, (count($factorArr)-1));
	    
	    if ((count($factorArr)-1) > 2) {
	        if ($index == 0 || $index == (count($factorArr)-1)) {
        	    $index = mt_rand(0, (count($factorArr)-1));
	        }
	    }//re-generate once
	
	
	    return $factorArr[$index];
	}
	
	/**
	 * sometime we need put a brackets into formula like A - B - (C - D) -F
	 * so this function will generate (C-D) section and return it.
	 * @param unknown $type
	 * @param unknown $sum
	 * @param unknown $maxValue
	 * @param number $negativeRangeMax
	 * @return string
	 */
	public static function generateSpecialSection($type, $maxValue, $sum, $negativeRangeMax = 0, $minValue = 0, $floatBit = 0){
	    $params = self::generateParams($type, $maxValue, $sum, $negativeRangeMax, $minValue, $floatBit);
	    $formular = '( '.$params['valueA']['n'].' '.$params['op'].' '.$params['valueB']['s'].' )';
	
	    return $formular;
	}
	
	/**
	 * genreate params for each step formula
	 * @param unknown $type
	 * @param unknown $sum
	 * @param unknown $negativeRangeMax
	 */
	public static function generateParams($type, $maxValue, $sumValue = null, $negativeRangeMax = 0, $minValue = 0, $floatBit = 0){
	    $vA = array();
	    $vB = array();
	    $result = 0;
	    $op = '';
	     
	    switch ($type){
	        case 1:
	            //A + B = C
	            $op = '+';
	             
	            $result = $sumValue;
	            if ($result === null){
	                $randObj = self::randNumber($maxValue, 0, 2, $negativeRangeMax, $floatBit);
	                $result = $randObj['n'];
	            }
	             
	            if ($result > 0) {
	                $vA = self::randNumber($result, 0, 2, $negativeRangeMax, $floatBit);
	            }else{
	                $vA = self::randNumber(0, $result, 2, $negativeRangeMax, $floatBit);
	            }
	             
	            $objB['n'] = bcsub($result, $vA['n'], $floatBit);
	            $objB['s'] = $objB['n'];
	            if ($objB['n'] < 0) {
	                $objB['s'] = '( '.$objB['n'].' )';
	            }
	            $vB = $objB;
	            break;
	        case 2:
	            //A - B = C
	            $op = '-';
	             
	            $result = $sumValue;
	            if ($result === null){
	                $randObj = self::randNumber($maxValue, 0, 1, $negativeRangeMax, $floatBit);
	                $result = $randObj['n'];
	            }//only rand when result not given
	
	            //some time we have $max = 100 so we need switch $result = 104
	            if ($maxValue > $result) {
	                $randObj = self::randNumber($maxValue, $result, 1, $negativeRangeMax, $floatBit);
	            }else{
	                $randObj = self::randNumber($result, $maxValue, 1, $negativeRangeMax, $floatBit);
	            }
	            $vA = $randObj;
	
	            $objB['n'] = bcsub($vA['n'], $result, $floatBit);
	            $objB['s'] = $objB['n'];
	            if ($objB['n'] < 0) {
	                $objB['s'] = '( '.$objB['n'].' )';
	            }
	            $vB = $objB;
	             
	            break;
	        case 3:
	             
	            //A * B = C
	            $op = '*';

	            $result = $sumValue;
	            if ($result === null){
	                $randObj = self::randNumber($maxValue, 0, 1, $negativeRangeMax, $floatBit);
	                $result = $randObj['n'];
	            }//only rand when result not given
	            $tmpObj = self::findFactor($result, $floatBit);
// 	            var_dump($tmpObj);//exit;
	            @$vA['n'] = $tmpObj['A'];
	            @$vA['s'] = $tmpObj['A'];
	            if ($vA['n'] < 0) {
	               $vA['s'] = ' ( '.$tmpObj['A']. ' ) ';
	            }
	            
	            @$vB['n'] = $tmpObj['B'];
	            @$vB['s'] = $tmpObj['B'];
	            if ($vB['n'] < 0) {
	               $vB['s'] = ' ( '.$tmpObj['B']. ' ) ';
	            }
	            
	            break;
	            
	        case 4:
	            //A / B = C
	            $op = '/';
	            
	            $result = $sumValue;
	            if ($result === null){
	                $randObj = self::randNumber($maxValue, 0, 1, $negativeRangeMax, $floatBit);
	                $result = $randObj['n'];
	            }//only rand when result not given
	            
	            $vB = self::randNumber($maxValue, $minValue, 1, $negativeRangeMax, $floatBit);
	            
	            $vA['s'] = $vA['n'] = bcmul($vB['n'], $result, $floatBit);
	            if ($vA['n'] < 0) {
	                $vA['s'] = " ( ".$vA['n']." ) ";
	            }
	            
	            break;
	             
	        default:
	            $params['op'] = '+';
	            $params['method'] = 1;
	            $params['valueA'] = 1;
	            $params['valueB'] = 1;
	            $params['result'] = 2;
	             
	    }
	     
	    $params = array();
	    $params['op'] = $op;
	    $params['valueA'] = $vA;
	    $params['valueB'] = $vB;
	    $params['result'] = $result;
// 	    	    var_dump($op);
	    // 	    echo '<hr/>';
	     
	    return $params;
	}
	
	
	
	/**
	 * format formula for display
	 * @param unknown $formula
	 */
	public static function formatFormula($formula, $suffix = false) {
	    $formula = str_replace('/', '÷', $formula);
	    $formula = str_replace('*', 'x', $formula);
	     
	    if ($suffix){
	        if (substr($formula, -4) != ' = ?') {
	            $formula .= ' = ?';
	        }//if not end with =? we need add it for display, becareful we only add it once!!
	    }
	     
	    return $formula;
	}
}