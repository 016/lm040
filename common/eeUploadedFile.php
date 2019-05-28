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
use yii\helpers\BaseFileHelper;

class eeUploadedFile extends UploadedFile{
    
    public $_user_id;

    public $useMimeExt = false;
    public $_ext = '';
    public $newName;
    public $fileName;
    public $v = 1;
    
    public function init() {
        parent::init();
        
        //new property for writable.
        $this->_ext = $this->extension;
        
    }
    
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
        
        //extension check bye mime type
        if ($this->useMimeExt) {
            $this->checkMimeExtension();
        }
        
        // random name
        $this->newName = $fileName;
        $tmpArr = explode('?v=', $this->newName);
        $this->fileName = $tmpArr[0];
        if (isset($tmpArr[1])) {
            $this->v = $tmpArr[1];
        }
        
        
        if (empty($fileName)) {
            $this->fileName = $path;
            if (!empty($this->_user_id)) {
                $this->fileName .= $this->_user_id.'_';
            }

            $this->fileName .= eeString::randomString ( 10, 1, 2, '_' ) . '.' . $this->_ext;
        }else{
            $this->v ++;
            
            //check extension for exist file name
            $tmpArr = explode('.', $this->fileName);
            if (!empty($tmpArr) && $this->_ext != end($tmpArr)) {
                //remove old file
                unlink($basedPath . $this->fileName);
                
                $this->fileName = str_replace('.'.end($tmpArr), '.'.$this->_ext, $this->fileName);
            }//auto switch extension name.
        }
        
        //add v back to name
        $this->newName = $this->fileName.'?v='.$this->v;
        
        //move file with filename
        return $this->saveAs ( $basedPath . $this->fileName );
    }
    
    /**
     * get extension name from mime type, the true extension name.
     */
    public function checkMimeExtension() {
    
        $mimeType = BaseFileHelper::getMimeType($this->tempName);
    
        $extensionName = 'eex';
    
        $mimeArr = [];
        $mimeArr['image/jpg'] = 'jpg';
        $mimeArr['image/png'] = 'png';
        $mimeArr['image/jpeg'] = 'jpeg';
    
        if (isset($mimeArr[$mimeType])) {
            $extensionName = $mimeArr[$mimeType];
        }
        $this->_ext = $extensionName;
    }
}