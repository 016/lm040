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


use yii\helpers\VarDumper;

class eeDebug{
    /**
     * self debug function, with colorful output.
     */
    public static function varDump($obj, $exist = true, $deep = 10, $highLight = true){
        VarDumper::dump($obj, $deep, $highLight);
        
        if ($exist) {
            exit;
        }
    }
    
    public static function show($obj, $exist = false) {
        var_dump($obj);
    
        if ($exist) {
            exit ();
        }
    }
    
}