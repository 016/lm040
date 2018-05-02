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

class eeDate{
    /**
     * format date.
     * @param number $type
     * 1 - current date, 2 - change days
     * @param sting $op // the operate string +5 days
     * @param datetime $sd source date, for type=2 when the op will add to. keep null for now
     *
     *
     * EED::f(2, '-1 hours', '2015-01-01') -> string(19) "2014-12-31 23:00:00"
     *
     * support source date
     * @return string
     */
    public static function f($type = 1, $op = null, $sd = '', $format= ''){
        
        if (empty($format)) {
            $format = 'Y-m-d H:i:s';
        }
    
        if ($type == 1) {
            if (empty($sd)) {
                $sd = date($format);
            }
            $dateString = date($format, strtotime($sd));
        }
    
        if ($type == 2) {
            if (empty($sd)) {
                $sd = date($format);
            }
            $dateString = date($format, strtotime($op, strtotime($sd)));
        }
    
        // 	    var_dump($dateString);exit;
        return $dateString;
    }
    
    
    /**
     * input two times, return unixtimstamp difference
     * @param datetime $f
     * @param string $u
     * @return number
     */
    public static function unixDiff($u, $f = ''){
    
        //get correct from time
        if ($f === '') {
            $f = time();
        }else{
            $f = strtotime($f);
        }
         
        return strtotime($u) - $f;
    
    }
    
    /**
     * time diff between two datetime
     * @param unknown $f
     * @param string $u
     * @param unknown $type, 1 - s, 2 - m, 3 - h, 4 - d, 5 - w, 6 - m, 7 - y
     * @return number
     */
    public static function timeDiff($f, $u = '', $type = 1) {
        // get correct from time
        if ($u === '') {
            $u = time ();
        } else {
            $u = strtotime ( $u );
        }
    
        $f = strtotime($f);
    
    
        $diff = $u - $f; //min diff
    
        if ($type >= 2) {
            $diff = $diff / 60;
        }//m
        if ($type >= 3) {
            $diff = $diff / 60;
        }//h
        if ($type >= 4) {
            $diff = $diff / 24;
        }//d
        if ($type >= 5) {
            $diff = $diff / 7;
        }//w
        if ($type >= 6) {
            $diff = $diff * 7 / 30;
        }//m
        if ($type >= 7) {
            $diff = $diff * 30 / 365;
        }//y
    
    
    
        return ceil($diff);
    }
    
    
    /**
     * calculate age by input birth day and now date, will return age
     *
     * @param datetime $birthDay
     * @param datetime $now
     * @return number
     */
    public static function age($birthDay, $now = '') {
        if (empty($birthDay)) {
            return '';
        }
        // get correct from time
        if ($now === '') {
            $now = time ();
        } else {
            $now = strtotime ( $now );
        }
        
        return ceil(($now - strtotime ( $birthDay ))/60/60/24/365);
    }
    
}