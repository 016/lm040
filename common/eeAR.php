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

class eeAR{
    
    
    ////old

//     /**
//      * input modelName and checkArr, check checkArr's index as model's attribute or relation, if not exist will auto move it out from checkArr and return result array
//      * @param string $modelName
//      * @param array $checkArr
//      * @return array
//      */
//     public static function attrValid($modelName, $checkArr){
//         $model = new $modelName();
//         foreach ($checkArr as $key=>$v) {
//             $key = trim($key);
//             if (!$model->hasAttribute($key)) {
//                 //check relation
//                 if (!method_exists($model, 'get'.$key)) {
//                     unset($checkArr[$key]);
//                 }
// //                 else{
//                         //TODO relation check logic fail.
// //                     //validate relation attributes
// //                     $checkArr[$key] = self::attrValid($model->metaData->relations[$key]->className, $checkArr[$key]);
// //                 }
//             }//check attribute
//         }
    
//         return $checkArr;
//     }
    
    
    ////methods.

    /**
     * input user-test, return userTest
     * @param string $resourceName
     * @return array
     */
    public static function fullResourceName($resourceName){
        $fullname = '';
    
        //split with -
        $tmpArr = explode('-', $resourceName);
    
        foreach ($tmpArr as $key => $value) {
            if ($key > 0) {
                $tmpArr[$key] = ucfirst($value);
            }
        }
    
        $fullname = implode('', $tmpArr);
    
        return $fullname;
    }
    
    
    /**
     * input modelName and checkArr, check model's attributes, auto remove invalid attributes, and return correct attributes array
     * @param string $modelName
     * @param array $checkArr
     * @return array
     */
    public static function validateAttributes($modelName, $checkArr){
        $checkedArr = [];
        
        $model = new $modelName();
        foreach ($checkArr as $key=>$v) {
            
            if ($model->hasAttribute($key)) {
                //attributes found
                $checkedArr[$key] = $v;
            }
        }
    
        return $checkedArr;
    }
    
    
    /**
     * input modelName and orderBy, check orderBy by model's attributes, auto remove invalid attributes, and return correct order by array
     * @param string $modelName
     * @param string $orderBy
     * @return array
     */
    public static function validateOrderBy($modelName, $orderBy){
        $orderResult = '';
        $allowVerb = ['ASC', 'DESC'];
        
        $model = new $modelName();
        foreach (explode(',', $orderBy) as $key=>$v) {
            $v = trim($v);
            
            $tmpV = explode(' ', $v);
            if (!isset($tmpV[0])) {
                continue;
            }//skip in-correct string
            
            if ($model->hasAttribute($tmpV[0])) {
                //attributes found
                
                $tmpVerb = 'ASC';
                if (isset($tmpV[1]) && in_array(strtoupper($tmpV[1]), $allowVerb)) {
                    $tmpVerb = strtoupper($tmpV[1]);
                }

                if (!empty($orderResult)) {
                    $orderResult .=', ';
                }
                $orderResult .= "$tmpV[0] $tmpVerb"; 
            }
        }
    
        return $orderResult;
    }
    
    /**
     * validate relation array
     * @param string $modelName
     * @param array $checkArr
     * @return array
     */
    public static function validateRelation($modelName, $checkArr){
        
        $model = new $modelName();
        
        foreach ($checkArr as $key=>$v) {
            $key = trim($key);
            if (!$model->hasAttribute($key)) {
                //check relation
                if (!method_exists($model, 'get'.$key)) {
                    unset($checkArr[$key]);
                }
            }//check attribute
        }
    
        return $checkArr;
    }
    
    /**
     * filter attributes b4 use the data as response.
     * @param array $model
     * @param array $modelRemove
     * @param array $keepArr
     * @param array $relationArr
     * @param array $removeArr
     * @return Ambigous <unknown, multitype:Ambigous <multitype:NULL , unknown> >
     */
    public static function filterAttributes($model, $modelRemoveArr, $keepArr, $relationArr) {
        $returnArr = [];

		//loop model to check if 2 dimensional array
		foreach ($model as $key => $oneM) {
		    
			if ($key === (int)$key) { //0 == 0 here, for 2 dimensional array only have 0 1 2 3 4 5 as key
			    //findAll mode - 2 dimensional array
				$returnArr[$key] = self::filterAttributes($oneM, $modelRemoveArr, $keepArr, $relationArr);
			}else{
			    //find mode or 1 model
			    
			    ////remove
			    foreach ($modelRemoveArr as $removeKey=>$v) {
			        if (!isset($relationArr[$removeKey])) {
    			        unset($model[$removeKey]);
			        }//don't remove full relations
			    }//remove model level attribute
				
				////keep
				if (!empty($keepArr)) {
				    if ($keepArr !== '') {
    				    foreach ($model as $key=>$v){
    				    	if (!isset($relationArr[$key]) && !isset($keepArr[$key])) {
    				    	    //remove attr
    				    	    unset($model[$key]);
    				    	}
    				    }
				    }//if keepArr empty, we keep all.
				}
				
				////relation
				//loop model again for relation filter.
				foreach ($model as $index => $value) {
				    if (is_array($value) && isset($relationArr[$index])) {
				        
				        //find relation level remove attributes list
				        $rRemoveArr = [];
				        if (isset($modelRemoveArr[$index])) {
				            $rRemoveArr = $modelRemoveArr[$index];
				        }
				        
				        foreach ($model[$index] as $rKey=>$rValue) {
				            if ($rKey === (int)$rKey) {
				                //2-dimensional
				                
				                $model[$index][$rKey] = self::filterAttributes($rValue, $rRemoveArr, $relationArr[$index], $relationArr);
				            }else{
				                //1-dimensional
				                $model[$index] = self::filterAttributes($model[$index], $rRemoveArr, $relationArr[$index], $relationArr);
				            }
				        }
				        
				    }
				}
				
				$returnArr = $model;
// 				exit;
				break;//only need loop once for 1 model mode
			}
		}
        
        return $returnArr;
    }
    
    /**
     * input checked condtion array and op, return condition string.
     * @param array $checkedArr
     * @param string $op
     */
    public static function generateSearchCondtionString($checkedArr, $op = 'AND') {
        $conditionString = '';
        
        //check op
        if (!in_array(strtolower($op), ['and', 'or'])) {
            $op = 'AND';
        }//use and as default
        
        foreach ($checkedArr as $oneCondition) {
            
            if (!empty($conditionString)) {
                $conditionString .= " $op ";
            }
            
            $conditionString .= $oneCondition;
        }//loop and generate condtion string.
        
        
        return $conditionString;
    }
}