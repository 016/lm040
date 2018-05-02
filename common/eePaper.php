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
 * paper class for question select and metal select
 * @author boylee
 */
class eePaper{
    /**
     * input country.id, round Count, and already selected array
     * return this round question data
     * @param unknown $cou_id
     * @param unknown $ro
     * @param array $selectArr
     * @return multitype:
     */
    public static function FindQuestion($cou_id, $ro, Array $selectArr){
        $thisRound = [];
        
        
        //load question list & find this round question
        $thisCountryQ = require \Yii::$app->params['paperDataPath'].'q_'.$cou_id.'.php';
        
        //find this round questions
        $answerList = []; //correct find
        $tmpAnswerList = []; //this round
        $tmpQuestion = ''; //this round
        if (isset($thisCountryQ[$ro])) {
            $tmpAnswerList = $thisCountryQ[$ro]['answer'];
            $tmpQuestion = $thisCountryQ[$ro]['question']['title'];
        }else {
            //no question left
            $thisRound['questionCount'] = count($thisCountryQ);
            $thisRound['ro'] = $ro;
            
            return $thisRound;
            
        }
        
        //check question skip logic,
        if ($thisCountryQ[$ro]['question']['hideDepend'] == '' || self::checkDependence($thisCountryQ[$ro]['question']['hideDepend'], $selectArr) == false) {
            //find correct list
            foreach ($tmpAnswerList as $key=>$oneAnswer) {
    
                if (self::checkDependence($oneAnswer['depend'], $selectArr)) {
                    //if get true, mean it's the question we need, so add to array
                    $answerList[$key] = $oneAnswer;
                }
            }
        }else{
            //round ++, find again
            $ro ++;
            $tmpNewRound = self::FindQuestion($cou_id, $ro, $selectArr);
            $ro = $tmpNewRound['ro'];
            $tmpQuestion = $tmpNewRound['question'];
            $answerList = $tmpNewRound['answer'];
        }
        
        
        //skip question check
        if (empty($answerList) && $ro < count($thisCountryQ)) {
            //round ++, find again
            $ro ++;
            $tmpNewRound = self::FindQuestion($cou_id, $ro, $selectArr);
            $ro = $tmpNewRound['ro'];
            $tmpQuestion = $tmpNewRound['question'];
            $answerList = $tmpNewRound['answer'];
        }
        
        //some condition we need $ro++, so cache ro here.
        $thisRound['questionCount'] = count($thisCountryQ);
        $thisRound['ro'] = $ro;
        $thisRound['question'] = $tmpQuestion;
        $thisRound['answer'] = $answerList;
        
        return $thisRound;
    }
    
    /**
     * check dependence on select array
     * return true for passed
     * return false for not passed
     * 
     * @param unknown $depend
     * @param array $selectArr
     * 
     * @return boolean
     */
    protected static function checkDependence($depend, Array $selectArr){
        if (empty($depend)) {
            return true;
        }//always return true for empty depend
        
        
        //check ( exit
        if (strpos($depend, '(') !== false) {
            ///adv mode
//             echo 'adv';
            
            //1. adv type find
            $advType = 1; //()+, all true mode, return false at first false
            $split = '+';
            if (strpos($depend, '|(') !== false || strpos($depend, ')|') !== false) {
                //()|, one true mode, return true if get one true.
                $advType = 2;
                $split = '|';
            }
            
            $splitedDepend = [];
            //2. split to get depend array
            //2.1 get all () out
            preg_match_all('|\((.*?)\)|', $depend, $tmpArr);
            //add brackets section into splited depend array, and do replace 
            if (!empty($tmpArr[1])) {
                foreach ($tmpArr[1] as $oneSection) {
                    //add
                    $splitedDepend[] = $oneSection;
                    
                    //replace
                    $depend = str_replace("($oneSection)", "", $depend);
                    
                }
            }
            
            $tmpRule = '|\\'.$split.'{2,}|';
            //2.2 remove left operate in left string
            $depend = preg_replace($tmpRule, $split, $depend);
            
            //2.3 use split do split again, for left string.
            $tmpArr = explode($split, $depend);
            foreach ($tmpArr as $oneSection) {
                if (!empty($oneSection)) {
                    $splitedDepend[] = $oneSection;
                }
            }
            
//             var_dump($splitedDepend);
            //3. loop depend array one by one to compare with the rules.
            foreach ($splitedDepend as $oneSD) {
                if ($advType == 1) {
                    //()+, all true mode, return false at first false
                    if (self::checkDependence($oneSD, $selectArr) === false) {
                        return false;
                    }
                }else{
                    //()|, one true mode, return true if get one true.
                    if (self::checkDependence($oneSD, $selectArr) === true) {
                        return true;
                    }
                    
                }
            }
            
            if ($advType == 1) {
                //no false, then return true
                return true;
            }else {
                //no true, then return false
                return false;
            }
            
        }else{
            ///normal mode
//             echo 'nor';
            
            //split "|" first
            $firstSplitArr = explode('|', $depend);
            
            //loop 1'rd round, and split with + 2'rd
            foreach ($firstSplitArr as $oneFS) {
                $secSplitArr = explode('+', $oneFS);
                
                //true default
                $add = true;
                
                
                //loop second to find match
                foreach ($secSplitArr as $oneSS) {
                    if (!isset($selectArr[$oneSS])) {
                        $add = false;
                        
                        //stop this second round.
                        break;
                        
                    }//one not found,  skip this second round.
                }
                
                if ($add) {
                    return true;
                }
            }
            
            //no true found, then return false in the end.
            return false;
        }
        
        
        
        
    }
    
    /**
     * find require material list after all questions answered.
     * @param unknown $cou_id
     * @param array $selectArr
     */
    public static function FindMaterial($cou_id, Array $selectArr){
        $mList = [];
        $mgList = [];
        
        //load raw material list
        $thisCountryM = require \Yii::$app->params['paperDataPath'].'m_'.$cou_id.'.php';
        $mgList = require \Yii::$app->params['paperDataPath'].'g_'.$cou_id.'.php';
        
        //find correct list
        foreach ($thisCountryM as $key=>$oneM) {

            if (self::checkDependence($oneM['depend'], $selectArr)) {
                //if get true, mean it's the question we need, so add to array
                $mList[$oneM['groupId']][$key] = $oneM;
            }
        }
        
        return ['mList'=>$mList, 'mgList'=>$mgList];
    }
}