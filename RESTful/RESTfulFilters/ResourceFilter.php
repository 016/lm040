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
 * access token check
 * @author boylee
 *
 */
class ResourceFilter extends BasedFilter{
    
    public $resourceIdCheckAction = [];
    public $resourceValidateCheckAction = [];

    
    /**
     * @see \yii\base\ActionFilter::beforeAction()
     */
    public function beforeAction($action) {

        //step 2. for some actions need check resource id.
        //keep this one to prevent system level error.
        if (in_array($action->id, $this->resourceIdCheckAction)) {
            $action->controller->restObj->validateResourceId();
        }
        
        //step 3. resource valid, check resource owner
        if (in_array($action->id, $this->resourceValidateCheckAction)) {
            $action->controller->restObj->validateResource();
        }
        
        return true;
    }
    
    

    
    
}
