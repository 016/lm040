<?php

namespace eeTools\common;

use yii\base\Model;
use yii\helpers\VarDumper;
use eeTools\common\eeDebug;
use common\models\Hotword;

/**
 * This is the model class for import product
 *
 */
class eeExcelImport extends Model
{
    public $importFile;
    public $importSuccess = 0;
    public $importFail = 0;
    public $importError = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['importFile'], 'validateImportFile'],
        ];
    }
    
    public function validateImportFile($attribute, $params){

        //extension check
        if (strtoupper($this->importFile->type)!= 'text/csv') {
            $this->addError($attribute, 'only CSV file');
        }
        
        //size
        if (strtoupper($this->importFile->size) < 1) {
            $this->addError($attribute, 'too small');
        }
        
    }
    

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'importFile' => '导入文件',        
                
        ];
    }
    
    /**
     * 
     */
    public function importFile() {
        //load by manual, phpexcel do support namespace so we have to so it like this.
        include_once  EE_VENDORPATH. "/phpexcel/Classes/PHPExcel.php";
        
        //load by try
        try {
            $objReader = \PHPExcel_IOFactory::createReaderForFile($this->importFile->tempName);
            $objPHPExcel = $objReader->load($this->importFile->tempName);
            //PHPExcel_Reader_Exception
        } catch (\Exception $e) {
            return 0;
            exit;
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//         var_dump($sheetData);
        
//         foreach ($sheetData as $rowKey => $oneRow) {
//             var_dump($rowKey);
//             var_dump($oneRow);
//         }
//         exit;
        
        if (count($sheetData) < 2) {
            return 0;
            exit;
            //no data load in file.
        }
        
        //get first row which has file basic info.
        $basicRow = $sheetData[1];
        
        //loop to get each row and save into db.
        foreach ($sheetData as $rowKey => $oneRow) {
//             eeDebug::show($sheetData, 1);
            if ($rowKey == 1) {
                continue;
            }//auto skip first row
            
            //one new row
            
            
            //insert product
            $tmpModel = new Hotword();
            $tmpModel->scenario = 'b-import';
            $tmpModel->load(array_combine($basicRow, $oneRow), '');
            // var_dump($basicRow);
            // var_dump($oneRow);
            // var_dump(array_combine($basicRow, $oneRow));
            // var_dump($tmpModel->attributes);
            // exit;
            
            $tmpModel->save();
            // var_dump($tmpModel->errors);
            // var_dump($tmpModel->_successSavedCnt);
            // var_dump($tmpModel->_failSavedCnt);
            // exit;
            
            $this->importSuccess += $tmpModel->_successSavedCnt;
            $this->importFail += $tmpModel->_failSavedCnt;
            
        }
    }
}
