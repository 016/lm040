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


class eeXML{
    /**
     * loop and add all elements to sitemap xml
     */
    public static function addSiteMapElement($xml, $siteMapelementsArr){
        
        foreach ($siteMapelementsArr as $oneElement){
            //start at url node
            $url = $xml->addChild('url');
            
            //use loop to add all elements to url node
            foreach ($oneElement as $key=>$value) {
                if ($key == 'lastmod') {
                    $value = date('Y-m-d', strtotime($value));
                }//force format date.
                
                $loc = $url->addChild($key, $value);
            }
            
        }
        
    }
    
}