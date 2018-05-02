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


use eeTools\common\eeResponse;
/**
 * RESTful basic filter
 * check requestMapping
 * @author boylee
 *
 */
class RESTfulFilter extends BasedFilter{
    
    protected $oat_process = false;

    
    /**
     * @see \yii\base\ActionFilter::beforeAction()
     */
    public function beforeAction($action) {
        //validate RESTful request url

        if (!$action->controller->restObj->validateRequestMapping()) {
            eeResponse::errorResponse(400004, 400);
        }

        
        return true;
    }
    
    
}
