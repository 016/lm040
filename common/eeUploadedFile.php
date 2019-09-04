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
    
    public $compressPic = true;
    public $compressName;//compressed file name, like site/_12453_34j43j43.jpg
    
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
        
        //extension check by mime type
        if ($this->useMimeExt) {
            $this->checkMimeExtension();
        }
        
        // random name
        $tmpArr = explode('?v=', $fileName);
        $this->compressName = $this->fileName = $tmpArr[0];
        if (isset($tmpArr[1])) {
            $this->v = $tmpArr[1];
        }
        
        //remove _ at begin of ->fileName
        $tmpArr2 = explode('/', $this->fileName);
        if (isset($tmpArr2[1])) {
            if (substr($tmpArr2[1], 0, 1) == '_') {
                $this->fileName = $tmpArr2[0].'/'.substr($tmpArr2[1], 1);
            }//auto remove _ out name
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
                @unlink($basedPath . $this->compressName);
                
                $this->fileName = str_replace('.'.end($tmpArr), '.'.$this->_ext, $this->fileName);
            }//auto switch extension name.
        }
        
        $savedFile = true;
        
        //move file with filename
        if (!$this->saveAs ( $basedPath . $this->fileName )) {
            $savedFile = false;
        }else{
            
            //resize after success saved, used name '_'.$this->fileName
            if ($this->compressPic) {
                
         
                if (extension_loaded('imagick')) {
                    self::compressPic($this->fileName, $this->compressName, $basedPath);
                }else{
                    //no extension load, just copy orginal file with compress name.
                    
                    //error extension not working to log
                    \Yii::error('imagick extension not load when compress image');
                    
                    //save same pic if no extension load
                    //do copy here because can only save once
                    copy($basedPath . $this->fileName, $basedPath . $this->compressName);
                    
                }
            }
        }
        
        
        //add v back to name
        //also add
        $this->newName = $this->compressName.'?v='.$this->v;
        
        return $savedFile;
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
    
    
    /**
     * compress pic with magick
     * @param unknown $targetName
     * @param unknown $compressName
     * @param unknown $path
     */
    public static function compressPic($targetName, $compressName , $basedPath = null) {
        if (empty($basedPath)) {
            $basedPath = \Yii::$app->params ['path.upload'];
        }
    
    
        //check extesion b4 start
        if (extension_loaded('imagick')) {
            //do pic compress
    
            //load pic
            $image = new \Imagick($basedPath.$targetName);
    
            //get params
            $width = $image->getimagewidth();
            if ($width > 800) {
                $image->resizeimage(800, 0, \Imagick::FILTER_CATROM, 1);
            }
    
            $image->setImageFormat('JPEG');
            $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
    
    
            $quality = $image->getImageCompressionQuality();
    
            if ($quality == 0 || $quality > 75) {
                $quality = 75;
            }//force to quality 75
    
            $image->setImageCompressionQuality($quality);
    
            $image->stripimage();
    
            //save and clean
            $image->writeImage($basedPath.$compressName);
            $image->clear();  // clean everything prevent memery error
            $image->destroy();
    
    
        }
    }
    
}