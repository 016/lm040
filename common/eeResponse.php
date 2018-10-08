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


use yii\helpers\Json;



class eeResponse{

    /**
     * for error return
     * @param integer $errorCode
     * @param array $errorMsg
     * @param string $encodeType
     */
    public static function errorResponse($errorCode, $statusCode = 404 ,$errorData = [], $encodeType = 'json'){

        //combine error array
        $errorArr = [];
        $errorArr['code'] = $errorCode;
        $errorArr['msg'] = self::getErrorMsg($errorCode);
        $errorArr['data'] = self::formatErrorArr($errorData);
        

        //public send function
        self::sendResponse($errorArr, $statusCode, $encodeType);
    }
    
    
    /**
     * format Yii AR stand errors array to RESTful style to suit this API
     * @param array $errorsArr
     */
    public static function formatErrorArr(Array $errorsArr){
        $formattedErrors = [];
        
        foreach ($errorsArr as $attrName => $oneAttrErrs) {
            $tmpErrors = [];
            
            foreach ($oneAttrErrs as $key => $oneErrCode) {
//                 $oneErrCode = (int) $oneErrCode;
//                 var_dump($oneErrCode);
//                 var_dump(self::getErrorMsg($oneErrCode));
                $tmpErrors['code'] = $oneErrCode;
                $tmpErrors['msg'] = self::getErrorMsg($oneErrCode);
            }
            
            $formattedErrors[$attrName][] = $tmpErrors;
        }
        
        
        return $formattedErrors;
    }
    
    

    /**
     * get error message by error code
     * @param integer $errorCode
     * @return string
     */
    public static function getErrorMsg($errorCode){
        $errorMsg = '';
        
        //force change type here.
//         $errorCode = (int)$errorCode;
        
        //load error code mapping
        $errorCodeMapping = require __DIR__.'/../data/eeErrorCode.php';
        
        if (isset($errorCodeMapping[$errorCode])) {
            $errorMsg = $errorCodeMapping[$errorCode];
        }
        
        return $errorMsg;
    }
    
    
    public static function sendResponse($returnArr, $statusCode = 200, $encodeType = 'json'){
        //more common format work can go here.
        
        
        //encode
        if ($encodeType == 'json') {
            $returnBody = Json::encode($returnArr);
            $type = 'application/json;charset=UTF-8';
        }
        
        self::renderResponse($returnBody, $statusCode);
    }
    /**
     * render response html
     * @param unknown $returnArr
     * @param number $statusCode
     * @param string $encodeType
     */
    public static function renderResponse($body = '', $status = 200, $content_type = 'text/html'){
        // set the status
        //error_log("================= sendResponse==============");
        //error_log($body);
        $status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);
        
        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
            exit;
        } else {
            // we need to create the body if none is passed
            // create some body messages
            $message = '';
        
            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }
        
            // servers don't always have a signature turned on
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
        
            // this should be templated in a real-world solution
            $body = '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <title>' . $status . ' ' . self::getStatusCodeMessage($status) . '</title>
            </head>
            <body>
            <h1>' . self::getStatusCodeMessage($status) . '</h1>
            <p>' . $message . '</p>
            <hr />
            <address>' . $signature . '</address>
            </body>
            </html>';
        
            echo $body;
            exit;
        }
    }
    
    public static function getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
}