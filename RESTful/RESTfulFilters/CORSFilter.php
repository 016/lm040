<?php
namespace eeTools\RESTful\RESTfulFilters;

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
 * check request verb, send correct response header
 * @author boylee
 *
 */
class CORSFilter extends BasedFilter{
    
    
    /**
     * @see \yii\base\ActionFilter::beforeAction()
     */
    public function beforeAction($action) {
        
        //maybe we need origin all the time.
        $allowOrigin = 'http://yiilib.com';
        if (isset(\Yii::$app->params['rest.cors.allowOrigin'])) {
            $allowOrigin = \Yii::$app->params['rest.cors.allowOrigin'];
        }
        
        header('Access-Control-Allow-Origin: '.$allowOrigin);
        
        
        if (\Yii::$app->request->getMethod() == 'OPTIONS') {
            header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN, X-COOKIES');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Max-Age: 3600');
            
            //stop after header return.
            exit;
        }
        
        return true;
    }
}
