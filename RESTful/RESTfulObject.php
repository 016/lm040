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
use yii\base\Model;
use yii\web\HttpException;
use eeTools\common\eeResponse;
use common\models\OauthAccessToken;
use eeTools\common\eeDate;
use eeTools\common\eeAR;
use eeTools\common\eeArray;
use common\models\User;
use yii\helpers\ArrayHelper;
use eeTools\common\eeDebug;


/**
 * RESTful data object, contains all elements need for
 */
class RESTfulObject extends Model
{
    
    //basic RESTful actions
    public $_version = '1'; //request version number.
    public $_requestMapping = []; //version level allow request path list, use it filter user request
    public $_inputMapping = []; //version level allow request path list, use it filter user request

    //common
    private $_config = []; //init config data obj
    public $_controllerName = ''; //controller name
    public $_actionName = ''; //action name
    public $_requestPath = ''; //request path for request mapping and rbac check, like topic/get, topic/get-asso/comment
    public $_modelName = ''; //resource or asso model name with full name space, like common/models/Topic
    public $_pkName = ''; //pk field name
    public $_deletedName = ''; //deleted field name
    
    //resource
    public $_resourceName = ''; //resource name, just name no name space, like topic, caps small start and no s in the end
    public $_resourceValidateFieldName = ''; //t_creationUser_id the field in resource table we use it for resource valid
    public $_resourcePKName = ''; //resource pk field name like, t_id
    public $_rId = ''; //resource pk value
    
    //association
    public $_assoName = ''; //association modelName
    public $_assoNameMapping = array();
    public $_assoPKName = ''; //asso pk attribute name, like com_id
    public $_assoId = ''; //asso pk value
    public $_assoResourceFieldName = '';//com_topic_id, for asso level resource validate
    public $_assoValidateFieldName = '';//com_creationUser_id, for asso level oauth validate
    
    public $_inputData = array();
    public $_inputFile = array();
    public $_count = 0; //for special condition, load once in list actions
    public $_limit = 10;
    public $_offset = 0;
    public $_order = '';
    public $_searchCondition = ''; //for search, list and count, get search param from URI then do filter and generate
    public $_selfElement = 0; //self element or full elements for load.

    
    public $keepArr = []; //request field list, for field, for keep arrtibutes
    public $relationArr = []; //request embed relation list
    public $removeArr = []; //remove from the result before send back to client.
    
    //Oauth 2.0
    public $_oat_value = 0; //token linked value.
    public $_oat_level = 1; //[optional] some resouce have public and private property, 1 - public, 2 - private  
    public $_oat_process = false; //need run oat workflow?
    
    
    
    //construct
    public function __construct($config, $defaultConfig = ['scenario'=>'initFromController']) {
        $this->_config = $config;
        
        parent::__construct($defaultConfig);
        
    }
    
    public function scenarios()
    {
        return [
                'initFromController' => ['_modelName', '_version', '_requestMapping', '_assoNameMapping', '_inputMapping', '_resourceValidateFieldName', 'removeArr', '_selfElement'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
                [['_modelName', '_resourceValidateFieldName'] , 'string', 'max'=>54],
                [['_modelName', '_resourceValidateFieldName'] , 'trim'],
                [['_version', '_selfElement'] , 'integer'],
                [['_requestMapping', '_assoNameMapping', 'removeArr'] , 'safe'],
        ];
    }
    

    public function init(){
        parent::init();
        
        //1. LOAD conifg by massive update
//         var_dump($this->_config);
        $this->load($this->_config, '');
//         var_dump($config);
//         var_dump($this->_requestMapping);

        
        //2. CHECK resource level model name
        if (empty($this->_modelName)) {
            throw new HttpException(400, '400001');
        }//must have modelName
    
    }
    
    
    /**
     * init request mapping
     * @param unknown $requestMapping
     */
    public function initRequestMapping($requestMapping = []) {
        if (!empty($requestMapping)) {
            $this->_requestMapping = $requestMapping;
        }
        
        //only use two get for default.
        if (empty($this->_requestMapping)) {
            $this->_requestMapping["$this->_controllerName/list"] = 1;
            $this->_requestMapping["$this->_controllerName/view"] = 1;
        }
    }
    
    /**
     * load basic params when init, this params will used for all methods
     * trig after controller and action name set
     * //init from RESTful based controller
     * 1. resource name
     * 2. resource id
     * 3. asso name
     * 4. asso id
     * 5. try load asso model name
     * 6. generate requestPath 
     */
    public function loadBasicParams(){
        $request = \Yii::$app->getRequest();
        
        /// GET _resourceName from url and try remove end 's'
        $this->_resourceName = $this->_controllerName;
        if (substr($this->_resourceName, -1) == 's') {
            $this->_resourceName = substr($this->_resourceName, 0, -1);
        }
        
        /// GET resource pk name, always try get this one first, Asso mode will overwrite _pkName.
        $model = new $this->_modelName();
        $tmpPKAttr = $model->primaryKey();
        if (empty($tmpPKAttr)) {
            eeResponse::errorResponse(404002, 404);
        }
        $this->_resourcePKName = $this->_pkName = $tmpPKAttr[0];
        
        /// GET resource id
        $this->_rId = $request->get('r_id', '');
        
        
        /// GET _assoName from url, and try remove end s
        $assoName = $request->get('asso', '');
        
        $this->_assoName = $assoName;
        if (substr($this->_assoName, -1) == 's') {
            $this->_assoName = substr($this->_assoName, 0, -1);
        }
        
        /// GET asso id
        $this->_assoId = $request->get('asso_id', '');
        
        
        /// GENERATE requestPath
        $this->_requestPath = $this->_resourceName.'/'.$this->_actionName;
        if(!empty($this->_assoName)){
            $this->_requestPath .= '/'.$this->_assoName;
        }
        
        ///GENERATE default rules for requestMapping
        $this->initRequestMapping();
        
        /// GET
    }
    
    ////VALIDATE
    //validate requestMapping
    public function validateRequestMapping(){
        
//         var_dump($this->_requestPath);
//         var_dump($this->_requestMapping);
//         var_dump(isset($this->_requestMapping[$this->_requestPath]));
//         exit;
        
        if (isset($this->_requestMapping[$this->_requestPath])) {
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * get X_CSRF params from request header, use it load db find record
     * can be overwrite in each module's based controller, to suit diff logic
     * @throws CHttpException
     */
    public function validateAccessToken(){
        
        $inputToken = '';
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $inputToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        
        //load access token
        $t = OauthAccessToken::find()->where(['oat_accessToken'=>$inputToken, 'oat_closed'=>0])->andWhere(['>=', 'oat_expiredDate', eeDate::f()])->one();
        if (empty($t)) {
            eeResponse::errorResponse(401003, 401);
        }
        
        //save user.id into property
        $this->_oat_value = $t->oat_value;
        
//         //TODO check
//         $this->oat_process = true;

    }
    
    /**
     * rule: only run RBAC validate if oauth token value found.
     */
    public function validateRBAC(){


        if ($this->_oat_value === 0 || empty($this->_oat_value)) {
            return true;
        }//token basic not run, so we don't need check SRBAC here.
        
        
        //load user load user.u_role_id
        $user = User::findOne($this->_oat_value);
        if (empty($user)) {
            eeResponse::errorResponse(404060, 404);
        }//admin level has authRole.ar_id <= 10, so we don't need check it. also work for all dev users.
        

        //count sql. complex sql here.
        $sql = "select count(arai_id) from authRoleAuthItem where arai_authItem_id in (select aiar_authItem_id from authItemAuthRule where aiar_authRule_id = (select ar_id from authRule where ar_code=:ar_code)) and arai_authRole_id = :role_id";
        
        $tmpCount = \Yii::$app->db->createCommand($sql, [':ar_code'=>$this->_requestPath, ':role_id'=>$user->u_authRole_id])->queryScalar();
        
        if ($tmpCount < 1) {
            eeResponse::errorResponse(403061, 403);
        }
    }
    
    
    
    ////Resource
    /**
     * get resource basic data
     * @param unknown $controllerName
     * all moved to basic params, empty on Jul 4, 2016
     */
    public function loadResourceBasic(){
    }
    
    /**
     * validate resource id in url.
     * @throws HttpException
     */
    public function validateResourceId(){
        if (empty($this->_rId)) {
            eeResponse::errorResponse(400100, 400);
        }
    }
    
    
    /**
     * valid resource owner relation.
     * @throws HttpException
     */
    public function validateResource(){
        
        //check for resource validate field name.
        if (empty($this->_resourceValidateFieldName)) {
            eeResponse::errorResponse(404102, 404);
        }

        //valid resource
        $modelName = $this->_modelName;
        $model = $modelName::find()->where([$this->_resourcePKName => $this->_rId, $this->_resourceValidateFieldName => $this->_oat_value])->one();
        
        if (empty($model)) {
            eeResponse::errorResponse(404101, 404);
        }
    }
    
    
    ////Asso
    /**
     * get AssoModelName from AssoMapping
     * get pkName and resource field name
     * 
     * @throws CHttpException
     */
    public function loadAssoBasic(){
        
        /// GET asso's _modelName from assoNameMapping, here we get full model name with namespace include.
        if (!isset($this->_assoNameMapping[$this->_assoName])) {
            eeResponse::errorResponse(404200, 404);
        }
        $this->_modelName = $this->_assoNameMapping[$this->_assoName]['name'];
        $this->_assoValidateFieldName = $this->_assoNameMapping[$this->_assoName]['validateField'];
        
        //pk + _assoResourceFieldName
        $model = new $this->_modelName();
        $tmpPKArr = $model->primaryKey();
        
        if (empty($tmpPKArr)) {
            eeResponse::errorResponse(404201, 404);
        }//validate pk from db
        
        $this->_assoPKName = $this->_pkName = $tmpPKArr[0];
        
        //run one eeAR function to get full resource name
        $fullResourceName = eeAR::fullResourceName($this->_resourceName);
        $this->_assoResourceFieldName = str_replace('_', "_{$fullResourceName}_", $this->_pkName);
        //         var_dump($this->_assoResourceFieldName);
        //         exit;
                
    }
    
    /**
     * check asso resource id in GET
     * @throws HttpException
     */
    public function validateAssoId(){
        
    	if (empty($this->_assoId)) {
    		eeResponse::errorResponse(400202, 400);
    	}
    	
    }
    
    
    /**
     * validate asso and resouce relation( asso_id + asso level resource field name )
     * com_id + com_topic_id relation.
     */
    public function assoValidateResource(){
    
        //valid resource
        $modelName = $this->_modelName;
        $model = $modelName::find()->where([
                $this->_assoPKName => $this->_assoId,
                $this->_assoResourceFieldName => $this->_rId,
                
        ])->one();
    
        if (empty($model)) {
            eeResponse::errorResponse(404203, 404);
        }
    }
    
    /**
     * validate asso owner ship
     * com_id + com_creationUser_id relation.
     */
    public function assoValidateAsso(){
        if (empty($this->_assoValidateFieldName)) {
            eeResponse::errorResponse(404204, 404);
        }

        //valid resource
        $modelName = $this->_modelName;
        $model = $modelName::find()->where([
                $this->_assoPKName => $this->_assoId,
                $this->_assoValidateFieldName => $this->_oat_value,
                
        ])->one();
    
        if (empty($model)) {
            eeResponse::errorResponse(404205, 404);
        }
    }
    
    
    /**
     * all basic criteria condition goes here.
     * limit
     * offset
     * order
     * field
     * embed
     */
    public function loadCriteriaBasic(){

        //criteria
        $request = Yii::$app->getRequest();
        $limit = $request->getQueryParam('limit', $this->_limit);
        $offset = $request->getQueryParam('offset', 0);
        $order = $request->getQueryParam('sort', false);
        $field = $request->getQueryParam('field', false);
        $embed = $request->getQueryParam('embed', false);
        $count = $request->getQueryParam('count', false);
        
        ///LIMIT
        if ($limit !== false) {
            $this->_limit = (int)$limit;
        }//limit
//         var_dump($this->_limit);
        if ($this->_limit > \Yii::$app->params['rest.maxLimit']) {
            $this->_limit = \Yii::$app->params['rest.maxLimit'];
        }//keep max limit
//         var_dump($this->_limit);
//         exit;
        
        ///OFFSET
        if ($offset !== false) {
            $this->_offset = (int)$offset;
        }//offset
//         var_dump($this->_offset);
//         exit;
        
        
        ///ORDER
        if ($order !== false) {
            //filter attributes in order string, remove invalid attributes
            
            
            $this->_order = eeAR::validateOrderBy($this->_modelName, $order);
        }//order
//         var_dump($this->_order);
//         exit;
        
        
        ///FIELD
        if ($field !== false && !empty($field)) {
            //explode
            $this->keepArr = eeArray::arrIndexAdd(explode(',', $field));
        }//field
//         var_dump($this->keepArr);
//         exit;
        
        
        
        ///EMBED
        if ($embed !== false) {
            //explode to create array with relationName as key
            //a:x1,x2,x3;b:x1,x2
            $relationArr = array();
            foreach (explode(';', $embed) as $value) {
                if (empty($value)) {
                    continue;
                }//skip empty
        
                $tmpArr = explode(':', $value);
                if (!isset($tmpArr[1])) {
                    $relationArr[$tmpArr[0]] = array();
                }else{
                    $relationArr[$tmpArr[0]] = eeArray::arrIndexAdd(explode(',', $tmpArr[1]));
                }
            }//expload value for validate and return filter
            
//             var_dump($relationArr);
            $this->relationArr = eeAR::validateRelation($this->_modelName, $relationArr);
        }//embed
//         var_dump($this->relationArr);
//         exit;

        
        ////COUNT
        if ($count !== false) {
            $this->_count = $count;
        }
    }
    
    /**
     * load search criteria conditon from URI
     * generate where string as output at end.
     */
    public function loadCriteriaSearch(){

        $request = Yii::$app->getRequest();
        $q = $request->getQueryParam('q', false);
        $s = $request->getQueryParam('s', false);
        $op = $request->getQueryParam('op', 'AND');
//                 var_dump($q);exit;
        
        $checkArr = array();
        
        if ($q !== false) {
            //split to one item
            $tmpArr = explode(';', $q);
            
            foreach ($tmpArr as $one) {
                
                $tmpArr2 = explode('<=', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $one;
                    continue;
                }//<=
                
                $tmpArr2 = explode('<', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $one;
                    continue;
                }//<
                
                $tmpArr2 = explode('>=', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $one;
                    continue;
                }//>=
                
                $tmpArr2 = explode('>', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $one;
                    continue;
                }//>
                 
                
                // = as default
                $tmpArr2 = explode('=', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $one;
                    continue;
                }//=
            }//use attributeName as index use condition as value
            
        }//query
        //         var_dump($checkArr);exit;
        
        if ($s !== false) {
            //prepare check array
            $tmpArr = explode(';', $s);
            foreach ($tmpArr as $one) {
                $tmpArr2 = explode('=', $one);
                if (isset($tmpArr2[1])) {
                    $checkArr[$tmpArr2[0]] = $tmpArr2[0]." LIKE  '%$tmpArr2[1]%' ";
                }
            }//use attributeName as index use condition as value
        }//like
        
//         var_dump($checkArr);
//         exit;
        if (!empty($checkArr)) {
            $checkedArr = eeAR::validateAttributes($this->_modelName, $checkArr);
            
            //render checked condition string.
            $this->_searchCondition = eeAR::generateSearchCondtionString($checkedArr, $op);
            
//             var_dump($this->_searchCondition);
        }
        
        //         var_dump($this->_criteria);
//         exit;
        
    }
    
    /**
     * load and check input data for POST and PUT
     * support $_FILES too.
     */
    public function loadInputData(){
        $method = $_SERVER['REQUEST_METHOD'];
        
        //normal code
        $inputDataString = file_get_contents('php://input');
        $inputData = json_decode($inputDataString, true);
        
        //support form-data for POST
        if (empty($inputData) && $method == 'POST') {
            $inputData = $_POST;
        }//only for post support form-data
        
        //$_File check
        if (!empty($_FILES)) {
            $this->_inputFile = $_FILES;
            if (empty($inputData)) {
                $inputData = [];
            }
            $inputData = ArrayHelper::merge($inputData, $_POST);
        }//post mode
        
        if (empty($inputData) && empty($this->_inputFile)) {
            eeResponse::errorResponse(400005, 400);
        }//check url
        
        
        //input mapping convert
//         var_dump($this->_inputMapping[$this->_actionName]);
//         exit;
        if (isset($this->_inputMapping[$this->_actionName])) {
            
            foreach ($this->_inputMapping[$this->_actionName] as $inputName => $mappingName) {
                if (isset($inputData[$inputName])) {
                    $inputData[$mappingName] = $inputData[$inputName];
                    unset($inputData[$inputName]);
                }
            }//check mapping copy and unset
            
        }
        
        $this->_inputData = $inputData;
    }
    
    /**
     * do something before render output data
     * add remove field name into removeArr
     * !!the last filter before render.
     * TODO some more logic
     */
    public function prepareOutputData(){
        
//         //0
//         //auto add x_creationUser_id , x_updateDate, x_updateUser_id to removeArr
//         $this->removeArr[$this->_resourceModelName][str_replace('_id', '_creationUser_id', $this->_resourcePKName)] = 1;
//         $this->removeArr[$this->_resourceModelName][str_replace('_id', '_updateUser_id', $this->_resourcePKName)] = 1;
//         $this->removeArr[$this->_resourceModelName][str_replace('_id', '_updateDate', $this->_resourcePKName)] = 1;
        
//         if (!empty($this->_assoModelName)) {
//             $this->removeArr[$this->_assoModelName][str_replace('_id', '_creationUser_id', $this->_pkName)] = 1;
//             $this->removeArr[$this->_assoModelName][str_replace('_id', '_updateUser_id', $this->_pkName)] = 1;
//             $this->removeArr[$this->_assoModelName][str_replace('_id', '_updateDate', $this->_pkName)] = 1;
//         }
    }
    
    
    
    /// LOAD ACTIONS
    /**
     * create new model instant for create.
     */
    public function createModel($sendResponse = true, $scenario = 'restCreate'){
        //new
        $modelName = $this->_modelName;
        $model = new $modelName();
        $model->scenario = 'restCreate';
        $model->load($this->_inputData, '');
        
        //system trail
        $model->_user_id = $this->_oat_value;
        
        
        //Asso
        if (!empty($this->_assoName)) {
            $model->{$this->_assoResourceFieldName} = $this->_rId;
        }
        
        if (!$model->save()) {
            //save fail.
            
//             var_dump($model->errors);
//             exit;
            
            eeResponse::errorResponse(404080, 404, $model->errors);
            
            
        }
        
        if ($sendResponse) {
            $this->sendResponse($model->attributes);
        }else {
            return $model->attributes;
        }
        
    }
    

    
    /**
     * replace one
     * replace one single model and return saved data as array
     * 
     * send response as default
     */
    public function replaceModel($sendResponse = true, $id = '', $scenario = 'restReplace'){
        //load
        $model = $this->loadModel(false, false, $id);
        
        //replace
        $model->scenario = 'restReplace';
        $model->load($this->_inputData, '');
        
        //system trail
        $model->_user_id = $this->_oat_value;
        
        if (!$model->save()) {
            //save fail.
            eeResponse::errorResponse(404081, 404, $model->errors);
        }
        
        
        if ($sendResponse) {
            $this->sendResponse($model->attributes);
        }else {
            return $model->attributes;
        }
        
    }
    
    /**
     * update one
     * update one single model and return saved data as array
     * 
     * send response as default
     */
    public function updateModel($sendResponse = true, $id = '', $scenario = 'restUpdate'){
        //load
        $model = $this->loadModel(false, false, $id);
        
        //update
        $model->scenario = $scenario;
        $model->load($this->_inputData, '');
        
        //system trail
        $model->_user_id = $this->_oat_value;
        
        if (!$model->save()) {
            //save fail.
            eeResponse::errorResponse(404082, 404, $model->errors);
        }
        
        
        if ($sendResponse) {
            $this->sendResponse($model->attributes);
        }else {
            return $model->attributes;
        }
        
    }
    
    /**
     * update one
     * update one single model and return saved data as array
     * 
     * send response as default
     */
    public function deleteModel($sendResponse = true, $id = '', $scenario = 'restDelete'){
        //load
        $model = $this->loadModel(false, false, $id);
        
        //set scenario
        $model->scenario = 'restDelete';
        
        //system trail
        $model->_user_id = $this->_oat_value;
        
        if ($sendResponse) {
            $model->delete();
        }else {
            return $model->delete();
        }
        
    }
    
    
    /**
     * massive replace all input
     * loop and replace
     *
     * send response as default
     */
    public function massiveReplaceModel($sendResponse = true, $id = '', $scenario = 'restMassiveReplace'){
        
        //log all successful saved attributes.
        $successResult = [];
        
        //loop to find each replace item.
        foreach ($this->_inputData as $oneM) {
            if (!isset($oneM[$this->_pkName])) {
                continue;
            }//if not found pk value, direct skip
            
            //find and update and log
            
            //load
            $model = $this->loadModel(false, false, $oneM[$this->_pkName]);
            if (empty($model)) {
                continue;
            }//skip on not load
            
            
            //TODO need test
            //validate resource
            //validate owner
            if (!empty($this->_assoName)) {
                //Asso mode
                //resource asso relation
                if ($model->{$this->_assoResourceFieldName} != $this->_rId) {
                    continue;
                }//skip on resource validate fail.
                
                //owner
                if ($model->{$this->_assoValidateFieldName} != $this->_oat_value) {
                    continue;
                }//skip on onwer validate fail.
            }else{
                //Resource mode
                if ($model->{$this->_resourceValidateFieldName} != $this->_oat_value) {
                    continue;
                }//skip on onwer validate fail.
            }
            
            
            $model->scenario = 'restMassiveReplace';
            $model->load($oneM, '');
            
            //system trail
            $model->_user_id = $this->_oat_value;
            
            if (!$model->save()) {
//                 var_dump($model->errors);
//                 exit;
                //save fail.
                eeResponse::errorResponse(404083, 404, $model->errors);
            }
            
            //log
            $successResult[] = $model->attributes;
            
        }
    
    
        if ($sendResponse) {
            $this->sendResponse($successResult);
        }else {
            return $successResult;
        }
    
    }
    
    
    
    /**
     * load one
     * load one single model and return the data as array
     *
     * send response as default
     */
    public function countModel($sendResponse = true){
        //load
//         var_dump($this->_modelName);
//         exit;
        $modelName = $this->_modelName;
        $model = $modelName::find();
        
        //serach conditions go here.
        if (!empty($this->_searchCondition)) {
            $model->andWhere($this->_searchCondition);
        }
        
        
        ///ASSO
        if (!empty($this->_assoName)) {
            $model->andWhere([$this->_assoResourceFieldName => $this->_rId]);
        }
        
        
        $data = $model->count();
        
        if ($sendResponse) {
            $this->sendResponse($data);
        }else{
            return $data;
        }
    }
    
    
    /**
     * search models
     * load list models and return the data as array
     */
    public function searchModel($sendResponse = true, $asArray = true){
        //load
        $modelName = $this->_modelName;
        $model = $modelName::find();
    
        //limit
        if (!empty($this->_limit)) {
            $model->limit((int)$this->_limit);
        }
    
        //offset
        if (!empty($this->_offset)) {
            $model->offset((int)$this->_offset);
        }
    
        //order
        if (!empty($this->_order)) {
            $model->orderBy($this->_order);
        }
    
        //relations
        foreach ($this->relationArr as $relationName => $value) {
            $model->with($relationName);
        }
    
        //FOR PERFORMANCE REASON!!! only run in searchModel
        //search condtions
        if (!empty($this->_searchCondition)) {
            $model->andWhere($this->_searchCondition);
        }
    
    
        ///Asso
        if (!empty($this->_assoName)) {
            $model->andWhere([$this->_assoResourceFieldName => $this->_rId]);
        }
    
    
        $data = $model->asArray($asArray)->all();
    
        if ($sendResponse) {
    
            //try load count for response direct mode. only load once.
            if (empty($this->_count)) {
                $this->_count = $this->countModel(false);
            }
    
            $this->sendResponse($data);
        }else{
            return $data;
        }
    }
    
    /**
     * load one
     * load one single model and return the data as array
     *
     * send response as default
     */
    public function loadModel($sendResponse = true, $asArray = true,  $id = ''){
        //prepare id
        if (empty($id)) {
            //use r_id or asso_id
            $id = $this->_rId;
            if (!empty($this->_assoId)) {
                $id = $this->_assoId;
            }
        }
    
        //load
        $modelName = $this->_modelName;
        $model = $modelName::find()->where([$this->_pkName=>$id]);
        
        //self element load
        if ($this->_selfElement == 1 && !empty($this->_resourceValidateFieldName) && !empty($this->_oat_value)) {
            //add self load condition
            $model->andWhere([$this->_resourceValidateFieldName => $this->_oat_value]);
        }
        
        $model = $model->asArray($asArray)->one();
    
        if ($sendResponse) {
            $this->sendResponse($model);
        }else {
    
            return $model;
        }
    
    }
    
    
    /**
     * load list
     * load list models and return the data as array
     */
    public function loadModels($sendResponse = true, $asArray = true){
        //load
        $modelName = $this->_modelName;
        $model = $modelName::find();
        
        //limit
        if (!empty($this->_limit)) {
            $model->limit((int)$this->_limit);
        }
        
        //offset
        if (!empty($this->_offset)) {
            $model->offset((int)$this->_offset);
        }
        
        //order
        if (!empty($this->_order)) {
            $model->orderBy($this->_order);
        }
        
        //relations
        foreach ($this->relationArr as $relationName => $value) {
            $model->with($relationName);
        }
        

        //self element load
        if ($this->_selfElement == 1 && !empty($this->_resourceValidateFieldName) && !empty($this->_oat_value)) {
            //add self load condition
            $model->andWhere([$this->_resourceValidateFieldName => $this->_oat_value]);
        }
        
        //FOR PERFORMANCE REASON!!! forbid search condtion run in loadModels, only run in searchModel
        //search condtions
//         if (!empty($this->_searchCondition)) {
//             $model->andWhere($this->_searchCondition);
//         }


        ///Asso
        if (!empty($this->_assoName)) {
            $model->andWhere([$this->_assoResourceFieldName => $this->_rId]);
        }
        
        
        $data = $model->asArray($asArray)->all();
        
        if ($sendResponse) {
            
            //try load count for response direct mode. only load once.
            if (empty($this->_count)) {
                $this->_count = $this->countModel(false);
            }
            
            $this->sendResponse($data);
        }else{
            return $data;
        }
    }
    
    
    
    //// response
    /**
     * send response and do some prepare work like filter attributes and relations
     * it's for data return only, 
     * will do some re-orgnaze 
     */
    public function sendResponse($returnData = [], $showState = false, $statusCode = 200, $encodeType = 'json'){
        $returnArr = [];
    
        //filter return array
        if ($statusCode == 200) {
            if (is_array($returnData)) {
                $returnData = $this->filterReturnArr($returnData);
            }
        }//only filter success data
        
        
        if ($showState) {
            //format state for usage.
            $returnState = [];
            $returnState['limit'] = $this->_limit;
            $returnState['offset'] = $this->_offset;
            
            //TODO finish all state params when online.
            $returnState['count'] = $this->_count;
            $nextOffset = $this->_limit + $this->_offset;
            
            //skip empty url.
            $nextUri = '';
            if ($this->_offset < $this->_count - 1) {
                $nextUri = "next?limit=$this->_limit&offset=$nextOffset&count=$this->_count";
            }
            $returnState['next'] = $nextUri;
            
            
            
            $returnArr['state'] = $returnState;
            $returnArr['data'] = $returnData;
        }else{
            //direct return the data
            $returnArr = $returnData;
        }

        
        eeResponse::sendResponse($returnArr, $statusCode, $encodeType);
    
    }
    
    /**
     * filter return array, remove forbid attributes
     * @param array $returnArr
     * @return array
     */
    public function filterReturnArr($returnArr){
    
        //filter by remove*2, field and relatioArr
        
        $modelRemoveArr = [];

        if (isset($this->removeArr[$this->_requestPath])) {
            $modelRemoveArr = $this->removeArr[$this->_requestPath];
        }//get removeArr
        
        //send response
        return eeAR::filterAttributes($returnArr, $modelRemoveArr, $this->keepArr, $this->relationArr);
    
    }
}
