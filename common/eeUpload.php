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

class eeUpload{
    /**
     * for upload file, input MIME type return ext
     * @param MINETYPE $mineType
     * @return string
     */
    public static function uploadFileExt($mineType){
        $ext = 'jpg';
    
        $typelist=array();
        $typelist['image/gif'] = 'gif';
        $typelist['image/jpeg'] = 'jpg';
        $typelist['image/pjpeg'] = 'jpg';
        $typelist['image/png'] = 'png';
        $typelist['image/x-png'] = 'png';
        $typelist['video/mp4'] = 'mp4';
        if (isset($typelist[$mineType])) {
            $ext = $typelist[$mineType];
        }
    
        return $ext;
    }
}