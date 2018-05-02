<?php

namespace eeTools\eeWechat\Payment\QRCODE;


use eeTools\eeWechat\Payment\wcPayBaseData;
use eeTools\eeWechat\Payment\Unifiedorder;
use common\models\PaymentMethod;


class QRCode extends wcPayBaseData{
    
    protected  $timeStamp;
    protected  $nonceStr;
    protected  $package;
    protected  $signType;
    protected  $paySign;
    
    
    public function init(){
        parent::init();
        
        //init important params
        
        //force MD5 for hash method.
        $this->signType = 'MD5';
        
        $this->safeKeys = ['appId', 'timeStamp', 'nonceStr', 'package', 'signType', 'paySign'];
        
    }
    
    
    public function scenarios()
    {
        return [
                'createOrder' => ['body', 'out_trade_no', 'total_fee', 'trade_type'],
                'return' => ['prepay_id', 'return_code', 'result_code'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
                [['body', 'out_trade_no', 'total_fee', 'trade_type'], 'required'],
                [['trade_type'], 'checkTradeTypeParam'],
                [['prepay_id', 'return_code', 'result_code'], 'safe'],
        ];
    }

    
    /**
     * generate QR Code URI
     * for native model
     */
    public function generateQRUri(){
        $returnData = [];
        
        //load unifiedOrder by order.or_id, we need code_url here.
        $uo = new Unifiedorder(['o_id'=>$this->o_id]);
        //values
        $uo->createQRUnifiedOrder(PaymentMethod::PM_WECHATWEB);
        
        $returnData['qr_uri'] = $uo->code_url;
        
        //return as json.
        echo json_encode($returnData);
    }
    
}
