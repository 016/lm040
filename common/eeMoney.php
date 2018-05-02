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


use common\models\CommonConst;
class eeMoney{
    /**
     * do personal tax calculate
     * @param unknown $salary
     * @param string $employeeType
     * @return multitype:
     */
    public static function calculatePersonalTax($salary, $employeeType = null){
        
        //part-time tax as default
        $pt['taxAmount'] = $salary - 800;
        $pt['taxRate'] = 20;
        $pt['taxQuickMinus'] = 0;
        $pt['taxed'] = $pt['taxAmount'] * $pt['taxRate'] / 100;
        
        if ($employeeType === null) {
            $employeeType = CommonConst::COMWORKTYPE_FT;
        }//use full time as default
        
        
        if ($employeeType === CommonConst::COMWORKTYPE_FT) {
            $pt = self::calculateFTTax($salary);
        }
        
        
        
        return $pt;
        
    }
    
    
    /**
     * calculate full-time personal tax
     * @param unknown $salary
     * @param string $employeeType
     */
    public static function calculateFTTax($salary){
        //part-time tax as default
        $pt['taxAmount'] = 0;
        $pt['taxRate'] = 0;
        $pt['taxQuickMinus'] = 0;
        $pt['taxed'] = 0;
        
        if ($salary > 3500) {
            //need calculate personal tax now.
            $pt['taxAmount'] = $salary - 3500;
            
            switch ($pt['taxAmount']) {
                case $pt['taxAmount'] <= 1500:
                    $pt['taxRate'] = 3;
                    $pt['taxQuickMinus'] = 0;
                break;
                
                case $pt['taxAmount'] <= 4500:
                    $pt['taxRate'] = 10;
                    $pt['taxQuickMinus'] = 105;
                break;
                
                case $pt['taxAmount'] <= 9000:
                    $pt['taxRate'] = 20;
                    $pt['taxQuickMinus'] = 555;
                break;
                
                case $pt['taxAmount'] <= 35000:
                    $pt['taxRate'] = 25;
                    $pt['taxQuickMinus'] = 1005;
                break;
                
                case $pt['taxAmount'] <= 55000:
                    $pt['taxRate'] = 30;
                    $pt['taxQuickMinus'] = 2755;
                break;
                
                case $pt['taxAmount'] <= 80000:
                    $pt['taxRate'] = 35;
                    $pt['taxQuickMinus'] = 5505;
                break;
                
                default:
                    $pt['taxRate'] = 45;
                    $pt['taxQuickMinus'] = 13505;
                break;
            }
            
            $pt['taxed'] = ($pt['taxAmount'] * $pt['taxRate'] / 100) - $pt['taxQuickMinus']; 
        }
        
        
        
        return $pt;
        
        
    }
    
    
    
}