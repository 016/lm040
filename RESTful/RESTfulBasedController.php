<?php

namespace eeTools\RESTful;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 *
 */

use Yii;
use yii\web\Controller;
use eeTools\RESTful\RESTfulObject;
use eeTools\RESTful\RESTfulFilters\CORSFilter;
use eeTools\RESTful\RESTfulFilters\RESTfulFilter;
use eeTools\RESTful\RESTfulFilters\OauthFilter;
use eeTools\RESTful\RESTfulFilters\ResourceFilter;
use yii\helpers\ArrayHelper;
use eeTools\RESTful\RESTfulFilters\AssoResourceFilter;
use eeTools\RESTful\RESTfulFilters\RightFilter;
use eeTools\RESTful\RESTfulFilters\CriteriaFilter;
use eeTools\RESTful\RESTfulFilters\InputFilter;
use eeTools\RESTful\RESTfulFilters\OutputFilter;
/**
 * RESTful standard basic controller, control all based actions here. 90% works done here.
 */
class RESTfulBasedController extends Controller{
    
    public $restObj;
    protected $requestMapping = [];
    protected $assoNameMapping = [];
    protected $inputMapping = []; //input name mapping, for convert something.
    protected $modelName = '';
    protected $version = 1;
    protected $resourceValidateFieldName = '';
    protected $removeArr = [];
    protected $selfElement = 0; //load self element or all element
    
    public function init(){
        parent::init();

        //disable csrf validation
        $this->enableCsrfValidation = false;
        
        //config data object
        $configObj['_modelName'] = $this->modelName; 
        $configObj['_requestMapping'] = $this->requestMapping;
        $configObj['_inputMapping'] = $this->inputMapping;
        $configObj['_assoNameMapping'] = $this->assoNameMapping;
        $configObj['_version'] = $this->version; 
        $configObj['_resourceValidateFieldName'] = $this->resourceValidateFieldName; 
        $configObj['removeArr'] = $this->removeArr; 
        $configObj['_selfElement'] = $this->selfElement; 
//         var_dump($configObj);
//         exit;

        //init restful obj as start
        $this->restObj = new RESTfulObject($configObj);
        
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                'cors' => [
                        'class' => CORSFilter::className(),
                ],
                
                
                'restfulFilter' => [
                        'class' => RESTfulFilter::className(),
                ],
                'oauthFilter' => [
                        'class' => OauthFilter::className(),
//                         'except'=>['list'],
                        'only'=>['view', 'delete', 'create', 'update', 'replace', 'view-asso', 'delete-asso', 'create-asso', 'update-asso', 'replace-asso', 'massive-replace', 'massive-replace-asso'],
                ],
                
                
                'resourceFilter' => [
                        'class' => ResourceFilter::className(),
                        'resourceIdCheckAction'=>['view', 'delete', 'update', 'replace'],
                        'resourceValidateCheckAction'=>['view', 'delete', 'update', 'replace'],
                ],
                'assoResourceFilter' => [
                        'class' => AssoResourceFilter::className(),
                        'only'=>['list-asso', 'view-asso', 'create-asso', 'update-asso', 'replace-asso', 'delete-asso', 'massive-replace-asso', 'count-asso', 'search-asso'],
                        'assoIdCheckAction'=>['view-asso', 'delete-asso', 'update-asso', 'replace-asso'],
                        'resourceValidateCheckAction'=>['view-asso', 'delete-asso', 'update-asso', 'replace-asso'],
                        'assoValidateCheckAction'=>['view-asso', 'delete-asso', 'update-asso', 'replace-asso'],
                ],
                
                
                'rightFilter' => [
                        'class' => RightFilter::className(),
                        //run for all as default.
                ],
                
                
                'criteriaFilter' => [
                        'class' => CriteriaFilter::className(),
                        'only'=>['search', 'count', 'list', 'search-asso', 'count-asso', 'list-asso'],
                        'searchCheckAction'=>['search', 'count', 'list', 'search-asso', 'count-asso', 'list-asso'],
                ],
                
                
                'inputFilter' => [
                        'class' => InputFilter::className(),
                        //run for update actions only.
                        'only'=>['replace', 'create', 'update', 'replace-asso', 'create-asso', 'update-asso', 'massive-replace', 'massive-replace-asso' ],
                ],
                'ouputFilter' => [
                        'class' => OutputFilter::className(),
                        //run for all as default.
                ],
        ]);
    }
    
    /**
     * some common code before actions
     */
    
    public function beforeAction($action){
        //set controller and action name to restful data obj
        $this->restObj->_actionName = $this->action->id;
        $this->restObj->_controllerName = $this->id;
        
//         var_dump($expression)
        
        $this->restObj->loadBasicParams();
        
        return parent::beforeAction($action);
    }
    
    
    
    
    ////BASIC ACTIONS

    /**
     * default actions
     */
    public function actionIndex()
    {
        echo 'hellow world.';
        exit;
    }
    
    /**
     * list one resource's all data
     * @return mixed
     */
    public function actionList()
    {
        $this->restObj->loadModels();
    }

    /**
     * return signle resource detail
     * @return mixed
     */
    public function actionView()
    {
        $this->restObj->loadModel();
    }

    /**
     * Creates a new resource.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->restObj->createModel();
    }

    /**
     * Patch action to update an existing resource.
     * @return mixed
     */
    public function actionUpdate()
    {
        $this->restObj->updateModel();
    }
    
    /**
     * full replace an existing resource.
     * @return mixed
     */
    public function actionReplace()
    {
        $this->restObj->replaceModel();
    }

    /**
     * Deletes an existing resource
     * @return mixed
     */
    public function actionDelete()
    {
        $this->restObj->deleteModel();
    }
   
    /**
     * list one resource's all data
     * @return mixed
     */
    public function actionListAsso()
    {
        $this->restObj->loadModels();
    }

    /**
     * return signle resource detail
     * @return mixed
     */
    public function actionViewAsso()
    {
        $this->restObj->loadModel();
        
    }

    /**
     * Creates a new resource.
     * @return mixed
     */
    public function actionCreateAsso()
    {
        $this->restObj->createModel();
    }

    /**
     * Patch action to update an existing resource.
     * @return mixed
     */
    public function actionUpdateAsso()
    {
        $this->restObj->updateModel();
    }
    
    /**
     * full replace an existing resource.
     * @return mixed
     */
    public function actionReplaceAsso()
    {
        $this->restObj->replaceModel();
    }
    

    /**
     * Delete an existing resource
     * @return mixed
     */
    public function actionDeleteAsso()
    {
         $this->restObj->deleteModel();
    }
    
    
    
    ////Massive Replace
    /**
     * full replace a banch existing resource.
     * @return mixed
     */
    public function actionMassiveReplace()
    {
        $this->restObj->massiveReplaceModel();
    }
    
    /**
     * full replace a banch existing resources.
     * @return mixed
     */
    public function actionMassiveReplaceAsso()
    {
        $this->restObj->massiveReplaceModel();
    }
    
    ////Count
    /**
     * count resource
     * @return mixed
     */
    public function actionCount()
    {
        $this->restObj->countModel();
    }
    
    /**
     * count asso resource
     * @return mixed
     */
    public function actionCountAsso()
    {
        $this->restObj->countModel();
    }
    
    
    
    ////Search
    /**
     * search resource
     * @return mixed
     */
    public function actionSearch()
    {
        $this->restObj->searchModel();
    }
    
    /**
     * search asso resource
     * @return mixed
     */
    public function actionSearchAsso()
    {
        $this->restObj->searchModel();
    }
    
    
    
}
