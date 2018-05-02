<?php

namespace eeTools\eeSalesforce\Sync;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use yii\base\Object;
use common\models\AppUser;
use eeTools\common\eeNet;
use eeTools\common\eeDebug;
use yii\web\HttpException;
use eeTools\eeSalesforce\eeSalesforceBased;
use eeTools\common\eeAR;

/**
 * WeChat function class
 * 
 * @author boylee
 *        
 */

class sfLoad extends eeSalesforceBased {
    
    public function init() {
        parent::init ();
        
    }
    
    /**
     * load and return state & site list from SF API
     */
    public function loadStateSiteList($cleanToken = false) {
        \Yii::$app->session->set ( 'sf.time.companyLoadStart', time());
        
        //token ready
        $this->getAppAccessToken($cleanToken);
        
        //send get to load
        $queryUrl = "select Id, Account_Name_Local_Language__c, ShippingState from Account where ShippingCountry = 'China' and RecordTypeId = '01261000000CGemAAG' and isDeleted = false and Account_Status__c = 'Active'";
        $queryUrl = urlencode($queryUrl);
        $url = $this->appInstanceUrl."/services/data/v38.0/query?q=$queryUrl";
        
        $headers = [];
        $headers[] = 'Authorization: Bearer '.$this->appAccessToken;
        $response = eeNet::load($url, $headers);
        if($response == 401){
            //token fail, need get new token and redo.
            $this->loadStateSiteList(true);
            return '';
        }
        if ($response == false) {
//             var_dump('01');
//             eeDebug::varDump($response);
            throw new HttpException(404, 'SF API data load fail, plz check params and try again or contact admin user for deep check.01');
        }
        $res = json_decode($response);

        
        //return load result.
        return $res;
    }
    
    /**
     * load and return one user's account info
     */
    public function loadAccountInfo($accountId, $cleanToken = false) {
        
        //token ready
        $this->getAppAccessToken($cleanToken);
        
        //send get to load
        $queryUrl = "select Account_Name_Local_Language__c, Address_Local_Language__c, Primary_Contact_Local_Language__c, npe01__One2OneContact__c from Account where Id = '$accountId'";
        $queryUrl = urlencode($queryUrl);
        $url = $this->appInstanceUrl."/services/data/v38.0/query?q=$queryUrl";
        
        $headers = [];
        $headers[] = 'Authorization: Bearer '.$this->appAccessToken;
        $response = eeNet::load($url, $headers);
//         eeDebug::varDump($response);
        if($response == 401){
            //token fail, need get new token and redo.
            $this->loadAccountInfo($accountId, true);
            return '';
        }
        if ($response == false) {
//             var_dump('02');
//             eeDebug::varDump($response);
            throw new HttpException(404, 'SF API data load fail, plz check params and try again or contact admin user for deep check.02');
        }
        $res = json_decode($response);
        
        //return load result.
        return $res;
    }
    
    /**
     * load and return one user's account info
     */
    public function loadFilterInfo($contactId, $cleanToken = false) {
        
        //token ready
        $this->getAppAccessToken($cleanToken);
        
        //send get to load
        $queryUrl = "SELECT Site_Name__c, Water_Filter_System_Type__c, Installation_Date__c, of_Carbon__c, of_UV__c FROM Site_filter__c where Site_Name__c = '$contactId'";
        $queryUrl = urlencode($queryUrl);
        $url = $this->appInstanceUrl."/services/data/v38.0/query?q=$queryUrl";
        
        $headers = [];
        $headers[] = 'Authorization: Bearer '.$this->appAccessToken;
        $response = eeNet::load($url, $headers);
//         eeDebug::varDump($response);
        if($response == 401){
            //token fail, need get new token and redo.
            $this->loadFilterInfo($contactId, true);
            return '';
        }
        if ($response == false) {
//             var_dump('03');
//             eeDebug::varDump($response);
            throw new HttpException(404, 'SF API data load fail, plz check params and try again or contact admin user for deep check.021');
        }
        $res = json_decode($response);
        
        //return load result.
        return $res;
    }
    
    
    /**
     * load and return one user's account info
     */
    public function loadContactInfo($contactId, $cleanToken = false) {
        
        //token ready
        $this->getAppAccessToken($cleanToken);
        
        //send get to load
        $queryUrl = "SELECT Id, MobilePhone FROM Contact where Id = '$contactId'";
        $queryUrl = urlencode($queryUrl);
        $url = $this->appInstanceUrl."/services/data/v38.0/query?q=$queryUrl";
        
        $headers = [];
        $headers[] = 'Authorization: Bearer '.$this->appAccessToken;
        $response = eeNet::load($url, $headers);        
        if($response == 401){
            //token fail, need get new token and redo.
            $this->loadContactInfo($contactId, true);
            return '';
        }
        if ($response == false) {
//             var_dump('03');
//             eeDebug::varDump($response);
            throw new HttpException(404, 'SF API data load fail, plz check params and try again or contact admin user for deep check.03');
        }
        $res = json_decode($response);
        
        //return load result.
        return $res;
    }
    
    
    /**
     * load and return one user's account info
     */
    public function loadReplacementDate($accountId, $cleanToken = false) {
        
        //token ready
        $this->getAppAccessToken($cleanToken);
        
        //send get to load
        $queryUrl = "SELECT Id, CloseDate FROM Opportunity where AccountId = '$accountId' and RecordTypeId = '01261000000CH5M' and StageName = 'Pending Part Install' Order By CloseDate Desc";
        $queryUrl = urlencode($queryUrl);
        $url = $this->appInstanceUrl."/services/data/v38.0/query?q=$queryUrl";
        
        $headers = [];
        $headers[] = 'Authorization: Bearer '.$this->appAccessToken;
        $response = eeNet::load($url, $headers);        
        if($response == 401){
            //token fail, need get new token and redo.
            $this->loadReplacementDate($accountId, true);
            return '';
        }
        if ($response == false) {
//             var_dump('04');
//             eeDebug::varDump($response);
            throw new HttpException(404, 'SF API data load fail, plz check params and try again or contact admin user for deep check.04');
        }
        $res = json_decode($response);
        
        //return load result.
        return $res;
    }
    
    
}