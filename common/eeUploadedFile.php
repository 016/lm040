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

use yii\web\UploadedFile;

class eeUploadedFile extends UploadedFile{
    public $newName;
    
    /**
     * move upload file with default path, to cover about 80% conditions
     * 
     * @param string $path            
     */
    public function moveUploadFile($path = '', $fileName = '') {
        $basedPath = \Yii::$app->params ['path.upload'];
        
        if (empty ( $path )) {
            // default path
            $path = 'site/';
        }
        
        // random name
        $this->newName = $fileName;
        if (empty($fileName)) {
            $this->newName = $path . eeString::randomString ( 10, 1, 2, '_' ) . '.' . $this->extension;
        }
        
        return $this->saveAs ( $basedPath . $this->newName );
    }
}