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

use yii\web\HttpException;


class eeFile {
    
    /**
     * Function: unZipOnLinux($sourceFileName,$destinationPath)
     * Unzipping a zip file on linux
     * @param string $sourceFileName, source zip file name with absolute path
     * @param string $destinationPath, destination fath for unzipped file (absolute path)
     * 
     * @return null for success, 404 for unzip fail
     */
    
    public static  function unZipOnLinux($sourceFileName,$destinationPath){
        $success = false;
    
        $directoryPos = strrpos($sourceFileName,'/');
        $directory = substr($sourceFileName,0,$directoryPos+1);
        $dir = opendir( $directory );
        $info = pathinfo($sourceFileName);
        if ( strtolower($info['extension']) == 'zip' ) {
            $success = true;
            system('unzip -q '.$sourceFileName .'  -d '. $destinationPath);
        }else{
            $success = false;
        }
        closedir( $dir );
    
        if (!$success) {
            throw new HttpException(404, 'unzip fail...');
        }
    
    }
    
    //save file to text path
    public static  function saveFile($text, $path = '', $replace = false){
        if (empty($path)) {
            $path = \Yii::$app->params['log.filePath'];
        }
        
        if ($replace) {
            //replace
            $f = fopen($path, 'w+');
        }else{
            //append
            $f = fopen($path, 'a');
            fwrite($f, "\n\r>>>>>>>>>>>>>>".date('Y-m-d H:i:s')."\n\r");
        }
        
        fwrite($f, $text);
        fclose($f);
    } 
}