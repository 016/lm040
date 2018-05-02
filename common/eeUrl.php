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

use yii\helpers\Url;
use common\models\AppLocale;
use common\models\ProjectAppLocale;
use common\models\TopicAppLocale;
use common\models\TopicCategoryAppLocale;
use common\models\TopicTagAppLocale;

class eeUrl{
    /**
     * support multi url
     * @return string
     * 
     * string|boolean $forceLocale
     * false | no force
     * true | reverse 
     * 17 | en 
     * 40 | zh
     */
    public static function mulUrl($inputUrl = '', $scheme = false, $forceLocaleId = false){
        $url  = [];
        $request = \Yii::$app->request;
        
        //get cur locale ID
        $localeId = AppLocale::AL_ZH;
        if (\Yii::$app->language == AppLocale::AL_EN_TEXT) {
            $localeId = AppLocale::AL_EN;
        }
        
        if ($forceLocaleId === true) {
            $localeId = AppLocale::AL_EN;
            if (\Yii::$app->language == AppLocale::AL_EN_TEXT) {
                $localeId = AppLocale::AL_ZH;
            }
        }//reserve locale id
        
        //special locale id
        if ($forceLocaleId === AppLocale::AL_EN) {
            $localeId = AppLocale::AL_EN;
        }
        if ($forceLocaleId === AppLocale::AL_ZH) {
            $localeId = AppLocale::AL_ZH;
        }
        
//         var_dump($localeId);

        if (empty($inputUrl)) {
            //load current url by request path
            
            //get path from request
            $path = $request->pathInfo; 
            
//             var_dump($path);exit;
            
            $languageArr = [AppLocale::AL_EN_TEXT];
            
            //do split
            $pathArr = explode('/', $path);
            $pathOffset = 0;
            
            if (in_array($pathArr[0], $languageArr) || empty($pathArr[0])) {
                unset($pathArr[0]);
                $pathOffset = 1;
            }//remove en && empty
            
            //use urlManage rules restore path to array
            // /index /about /project /topic
            if (count($pathArr) == 1) {
                //for en/index url, copy 1->0
                if (in_array($pathArr[0+$pathOffset], ['project', 'topic'])) {
                    //add index
                    $url[] = $pathArr[0+$pathOffset].'/index';
                }else{
                    //add site
                    $url[] = 'site/'.$pathArr[0+$pathOffset];                    
                }
            }
            if (empty($pathArr)) {
                $url[] = 'site/index';
            }//empty pathArr mean no path get from request url
            
            

            if (count($pathArr) == 3 || count($pathArr) == 2) {
                
                if ($pathArr[0+$pathOffset] == 'topic' && $pathArr[1+$pathOffset] == 'search') {
                    //topic/search
                    $url[] = 'topic/search';
                
                    $kw = $request->getQueryParam('kw', '');
                    $url['kw'] = $kw;
                
                }else{
                    //need fix router + load correct title
                    //for the follow conditions
                    //project/1/{title}
                    //topic/1/{title}
                    //topic-category/1/{title}
                    //topic-tag/1/{title}
                    
                    $url[] = $pathArr[0+$pathOffset].'/view';
                    $url['id'] = $pathArr[1+$pathOffset];
                    
                    //load title from db. in this case we can keep url unique
                    $title = 'Welcome to YiiLib.com';
                    if (count($pathArr) == 3) {
                        $title = $pathArr[2+$pathOffset]; //default title
                    }
                    
                    if ($pathArr[0+$pathOffset] == 'project') {
                        $al = ProjectAppLocale::find()->where(['pal_appLocale_id'=>$localeId, 'pal_project_id'=>$pathArr[1+$pathOffset]])->one();
                        if (!empty($al)) {
                            $title = $al->pal_title;
                        }
                    }//project/view's title
                    
                    //skip topic/search
                    if ($pathArr[0+$pathOffset] == 'topic' && $pathArr[1+$pathOffset] != 'search') {
    //                     var_dump($pathArr);exit;
                        $al = TopicAppLocale::find()->where(['tal_appLocale_id'=>$localeId, 'tal_topic_id'=>$pathArr[1+$pathOffset]])->one();
                        if (!empty($al)) {
                            $title = $al->tal_title;
                        }
                    }//topic/view's title
                    if ($pathArr[0+$pathOffset] == 'topic-category') {
                        $al = TopicCategoryAppLocale::find()->where(['tcal_appLocale_id'=>$localeId, 'tcal_topicCategory_id'=>$pathArr[1+$pathOffset]])->one();
                        if (!empty($al)) {
                            $title = $al->tcal_title;
                        }
                    }//topic-category/view's title
                    if ($pathArr[0+$pathOffset] == 'topic-tag') {
                        $al = TopicTagAppLocale::find()->where(['ttal_appLocale_id'=>$localeId, 'ttal_topicTag_id'=>$pathArr[1+$pathOffset]])->one();
                        if (!empty($al)) {
                            $title = $al->ttal_title;
                        }
                    }//topic-tag/view's title
                    
                    
                    $url['title'] = $title;
                }
                
            }
            
            
            //fix locale
            if ($localeId != AppLocale::AL_ZH) {
                //add locale en for non-zh language
                $url['locale'] = AppLocale::AL_EN_TEXT;
            }
            
            //fix page
            $page = $request->getQueryParam('page');
            if ($page != null) {
                $url['page'] = $page;
            }
            //fix per-page
            $perPage = $request->getQueryParam('per-page');
            if ($perPage != null) {
                $url['per-page'] = $perPage;
            }
            
        }else{
            
            //custom url, input target router
            if (is_array($inputUrl)) {
                $url = $inputUrl;
            }else{
                $url[] = $inputUrl;
            }
            
            //add locale = en for non zh language
            if ($localeId != AppLocale::AL_ZH) {
                $url['locale'] = AppLocale::AL_EN_TEXT;
            }
        }

        //send correct url to Url to
        $resutlUrl = Url::to($url, $scheme);

        return $resutlUrl;
    }
    
}