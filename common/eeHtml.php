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

class eeHtml{
    /**
     * render content for QXM, auto add max height and show more button
     * @return string
     * 
                                    <div class="info-text on">
                                        <?= $curProduct->p_descProduct; ?>
                                    </div>
<!--                                     <a class="show-more">显示详情</a> -->
     */
    public static function renderContent($content){
        
        $returnString = '';
    	
        //show more
        $showMore = false;
        $pCount = substr_count($content, '<p>');
        if ($pCount > \Yii::$app->params['contentPCount']) {
            $showMore = true;
        }//show more if count > 7
        
        $returnString = '<div class="info-text on">';
        if ($showMore) {
            $returnString = '<div class="info-text">';
        }
        
        $returnString .= $content;
        
        $returnString .= '</div>';
        
        if ($showMore) {            
            $returnString .= '<a class="show-more">显示详情</a>';
        }
        
    	return $returnString;
    }
}