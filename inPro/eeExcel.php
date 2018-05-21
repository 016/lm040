<?php

namespace backendplus\models;

use yii\base\Model;
use yii\helpers\VarDumper;
use eeTools\common\eeDebug;
use common\models\Hotword;
use eeTools\common\eeString;

/**
 * This is the model class for import product
 *
 */
class eeExcel extends Model
{
    public $importFile;
    public $importSuccess = 0;
    public $importFail = 0;
    public $importError = [];
    
    
    public $_rowCnt = 0;

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
     * output file
     * @param array $dataArray output models data
     * @param string $fileName output filename, if null direct output in browser
     */
    public function exportFile(array $dataArray, array $titleArray = null, $fileName = null) {
        include_once  EE_VENDORPATH. "/phpexcel/Classes/PHPExcel.php";
        
        $objPHPExcel = new \PHPExcel();
        
        $objSheet = $objPHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        
        $objSheet->setTitle('QHM Output'); //给当前活动sheet设置名称
        
        if (!empty($titleArray)) {
            $this->setCellValue($objSheet, $titleArray);
        }
        
        foreach ($dataArray as $oneDArr) {
            $this->setCellValue($objSheet, $oneDArr);
        }
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5'); // 按照指定格式生成excel文件 'Excel5代表生成 Excel03文件 后缀名为.xls', 'Excel2007代表生成 Excel07文件 后缀名为.xlsx'
        
        if ($fileName === null) {
            //for null direct send file in browser, user will get auto download
            
            $fileName = eeString::randomString(8);
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="'.$fileName.'.xls"');
            header("Content-Transfer-Encoding: binary");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            
            $objWriter->save('php://output');
        }
        
    }
    
    
    public function setCellValue($objSheet, $dataArray){
        $this->_rowCnt ++;
        
        $tmpCnt = 64;
        $preCol = '';
        foreach ($dataArray as $oneValue) {
            $tmpCnt ++;
            
            if ($tmpCnt == 91) {
                $tmpCnt = 64;
                $preCol .= 'A';
            }
            
            $tmpColName = $preCol. chr($tmpCnt). $this->_rowCnt;
            $objSheet->setCellValue($tmpColName, $oneValue);
        }
        
//         $objSheet->setCellValue('A1','姓名')->setCellValue('B1','分数'); //给当前活动sheet填充数据
        
//         $objSheet->setCellValue('A2','张三')->setCellValue('B2','50');
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
            
            $tmpModel->saveMultiTitle();
            // var_dump($tmpModel->errors);
            // var_dump($tmpModel->_successSavedCnt);
            // var_dump($tmpModel->_failSavedCnt);
            // exit;
            
            $this->importSuccess += $tmpModel->_successSavedCnt;
            $this->importFail += $tmpModel->_failSavedCnt;
            
        }
    }
}
