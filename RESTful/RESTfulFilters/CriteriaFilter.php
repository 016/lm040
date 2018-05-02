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
 * RESTful basic filter
 * check requestMapping
 * @author boylee
 *
 */
class CriteriaFilter extends BasedFilter{
    
    protected $oat_process = false;
    
    public $searchCheckAction = [];

    
    /**
     * @see \yii\base\ActionFilter::beforeAction()
     */
    public function beforeAction($action) {
        
        //validate RESTful request url
        $action->controller->restObj->loadCriteriaBasic();
        
        if (in_array($action->id, $this->searchCheckAction)) {
            $action->controller->restObj->loadCriteriaSearch();            
        }//search check

        
        return true;
    }
    
    
}
