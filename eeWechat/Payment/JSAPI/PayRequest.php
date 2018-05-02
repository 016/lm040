<?php

namespace eeTools\eeWechat\Payment\JSAPI;


use eeTools\eeWechat\Payment\wcPayBaseData;
use eeTools\eeWechat\Payment\Unifiedorder;
use common\eeTools\eeString;
use common\models\PaymentMethod;


class PayRequest extends wcPayBaseData{
    
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
     * generate config json string for pay request js function
     * for JSAPI
     */
    public function generateConfig(){
        $config = '';
        
        //load unifiedOrder by order.or_id, we need prepay_id here.
        $uo = new Unifiedorder(['o_id'=>$this->o_id]);
        //values
        $uo->createUnifiedOrder(PaymentMethod::PM_INSIDEWECHAT);
        
        
        //config string
        $this->timeStamp = time();
        $this->nonceStr = eeString::randomString(16);
        $this->package = 'prepay_id='.$uo->prepay_id;
        $this->SetSign();
        $this->paySign = $this->sign;
        
        //create config string
        $this->makeSafeValues();
        $configArr = [];
        
        //remove debug:true after dev.
//         $configArr['debug'] = 'true';
        foreach ($this->safeValues as $oneKey=>$oneV) {
            $configArr[$oneKey] = (string)$oneV;
        }
        $config = json_encode($configArr);
        
        return $config;
    }
    
}
