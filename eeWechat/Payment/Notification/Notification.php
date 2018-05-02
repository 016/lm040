<?php

namespace eeTools\eeWechat\Payment\Notification;


use eeTools\eeWechat\Payment\wcPayBaseData;
use eeTools\common\eeFile;
use yii\helpers\VarDumper;
use common\models\OrderPaymentMethod;

/**
 * get notification from wechat post request, and return correct response to update wechat status.
 * @author Boy.Lee
 *
 */

class Notification extends wcPayBaseData{
    
    //return to wechat post request
    public $return_code; //SUCCESS/FAIL
    public $return_msg;
    public $feedback_return_xml;
    
    //notification return params
    public $noti_result_code;
    public $noti_return_code;
    public $cash_fee;
    public $openid;
    public $out_trade_no;
    public $time_end;
    public $total_fee;
    public $trade_type;
    public $transaction_id;
    
    
    public function init(){
        parent::init();
    }
    
    
    public function scenarios()
    {
        return [
                'notification' => ['appid', 'cash_fee', 'openid', 'out_trade_no', 'time_end', 'total_fee', 'trade_type', 'transaction_id'],
                'return' => ['prepay_id', 'return_code', 'result_code'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//                 [['body', 'out_trade_no', 'total_fee', 'trade_type'], 'required'],
//                 [['trade_type'], 'checkTradeTypeParam'],
                [['appid', 'cash_fee', 'openid', 'out_trade_no', 'result_code', 'return_code', 'time_end', 'total_fee', 'trade_type', 'transaction_id'], 'safe'],
        ];
    }
    
    /**
     * load post and xml notification from post request.
     */
    public function loadNotification(){
        //remove trace code.
//         eeFile::saveFile('new post request get.');
        
        //get xml
        $tmpXML = file_get_contents('php://input');
        if (empty($tmpXML)) {
            $this->generateReturnXML(0);
        }//xml content not get.
        
        //save response to file to get the structure.
        eeFile::saveFile($tmpXML);
        
        //xml to array
        $tmpXMLArr = $this->FromXml($tmpXML);
        if ($tmpXMLArr['return_code'] != "SUCCESS") {
                $this->generateReturnXML();
        }
        
        //check sign
        $this->safeValues = $tmpXMLArr;
        if ($tmpXMLArr['sign'] != $this->MakeSign()) {
            $this->generateReturnXML(0);
        };
        
        //massive attributes
        $this->scenario = 'notification';
        $this->attributes = $tmpXMLArr;
        $this->noti_result_code = $tmpXMLArr['result_code'];
//         var_dump($this->attributes);
        
        //remove trace code.
//         eeFile::saveFile('otn->'.$this->out_trade_no);
        //load OPM
        $tmpArr = explode('_', $this->out_trade_no);
        if (!isset($tmpArr[1])) {
            $this->generateReturnXML(0);
        }
        $opm = OrderPaymentMethod::findOne((int)$tmpArr[1]);
        if (empty($opm)) {
            $this->generateReturnXML();
        }
        
        if ($opm->opm_status_id == OrderPaymentMethod::OPM_PAID) {
            //already paid, direct return success
            $this->generateReturnXML(1);
        }
        
        
        $opm->scenario = 'notification';
        //update opm
        $opm->opm_paid = $this->total_fee/100;
        $opm->opm_cash = $this->cash_fee/100;
        $opm->opm_openId = $this->openid;
        $opm->opm_tradeType_return = $this->trade_type;
        $opm->opm_transaction_id = $this->transaction_id;
        $opm->opm_paidDate = date('Y-m-d H:i:s', strtotime($this->time_end));
        
        if ($this->noti_result_code == 'SUCCESS') {
            //SUCCESS
            //update opm status
            $opm->opm_status_id = OrderPaymentMethod::OPM_PAID;
            $opm->save();
//             var_dump($opm->errors);
//             exit;
            
            //load ORDER
            $order = $opm->opmOrder;
            if (!empty($order)) {
                $order->scenario = 'notification';
                $order->or_pricePaid = $this->total_fee/100;
                $order->or_status_id = OrderPaymentMethod::OPM_PAID;
                $order->or_paidDate = $opm->opm_paidDate;
                $order->save();
            }
        }else{
            //FAIL
            //update opm status
            $opm->opm_status_id = OrderPaymentMethod::OPM_FAIL;
            $opm->save();
            
            //load ORDER
            $order = $opm->opmOrder;
            if (!empty($order)) {
                $order->or_status_id = OrderPaymentMethod::OPM_FAIL;
                $order->save();
            }
        }
        
        $this->generateReturnXML(1);
    }
    
    /**
     * return result XML as WeChat POST response
     * @param number $result, 1 - SUCCESS, 0 - FAIL
     */
    protected function generateReturnXML($result = 0){
        if ($result == 1) {
            $this->return_code = 'SUCCESS';
        }
        if ($result == 0) {
            $this->return_code = 'FAIL';
        }
        
        //init
        $this->safeKeys = ['return_code', 'return_msg'];
        $this->feedback_return_xml = $this->ToXml();
        
        echo $this->feedback_return_xml;
        exit;
    }

}
