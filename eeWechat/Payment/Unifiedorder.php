<?php

namespace eeTools\eeWechat\Payment;


use common\eeTools\eeString;
use common\eeTools\eeNet;

use yii\web\HttpException;
use common\models\OrderPaymentMethod;
use common\models\PaymentMethod;

class Unifiedorder extends wcPayBaseData{
    
    const UNIFIEDORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
//     const UNIFIEDORDER = 'https://api.mch.weixin.qq.com/sandbox/pay/unifiedorder';
    
    protected $tradeTypes = array('JSAPI', 'NATIVE', 'APP', 'WAP');
    
    public $device_info;
    public $nonce_str;
    public $body;
    
    public $detail;
    public $attach;
    public $out_trade_no;
    public $fee_type;
    public $total_fee;
    
    public $spbill_create_ip;
    public $time_start;
    public $time_expire;
    public $goods_tag;
    
    public $notify_url;
    public $trade_type;
    public $product_id;
    public $limit_pay;
    public $openid;
    
    //response
    public $prepay_id;
    public $code_url;
    public $return_code;
    public $result_code;
    
    
    public function init(){
        parent::init();

        $this->findOrder();
        
        //init important params
        $this->notify_url = \Yii::$app->params['wechat.pay.notifyUrl'];
        
        $this->safeKeys = ['appid', 'mch_id', 'device_info', 'nonce_str', 'body',
        'detail','attach', 'out_trade_no', 'fee_type', 'total_fee',
        'spbill_create_ip', 'time_start', 'time_expire', 'goods_tag',
        'notify_url', 'trade_type', 'product_id', 'limit_pay', 'openid', 'sign'];
        
    }
    
    
    public function scenarios()
    {
        return [
                'createOrder' => ['body', 'out_trade_no', 'total_fee', 'trade_type'],
                'JSAPI_return' => ['prepay_id', 'return_code', 'result_code'],
                'QR_return' => ['prepay_id', 'return_code', 'result_code', 'code_url'],
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
    
    
    public function checkTradeTypeParam($attributes, $value){
        
        //check trade type correct
        if (!in_array($value, $this->tradeTypes)) {
            $this->addError($attributes, 'trade_type invalida');
        }
        
        if ($value == 'JSAPI' && empty($this->openid)) {
            $this->addError($attributes, 'need open id, when trade_type is JSAPI');
        }
        
        if ($value == 'NATIVE' && empty($this->product_id)) {
            $this->addError($attributes, 'need product id, when trade_type is NATIVE');
        }
    }

    
    /**
     * create unifiedOrder by given or_id, and post xml to wechat pay api.
     */
    public function createUnifiedOrder($pm_id){
        
        $tradeType = 'JSAPI';
        
        //check paid, if already paid, direct do the return.
        if ($this->order->or_status_id == OrderPaymentMethod::OPM_PAID) {
            //already paid
            echo json_encode(['code'=>2]);
            exit;
        }
        
        //create orderPaymentMethod first, use this to skip duplicate out_trade_no, error
        $opm = new OrderPaymentMethod(['scenario'=>'prePay']);
        $opm->opm_order_id = $this->order->or_id;
        $opm->opm_price = $this->order->or_price;
        $opm->opm_paymentMethod_id = $pm_id;
        $opm->opm_tradeType_send = $tradeType;
        
        $opm->save();

        
        $this->body = $this->order->or_productTitle;
        $this->total_fee = $this->order->or_price;
        $this->openid = \Yii::$app->session->get('wechat.openId');
        if (YII_ENV_DEV) {
            //lee open id.
            $this->openid = 'oo5axt9DbdP2zoein7EqXXFbt13Y';//fake for dev env only, for local test only 
        }//fake for dev environment.

        
        $this->trade_type = $tradeType;
        $this->out_trade_no = 'QXM'.$opm->opm_order_id.'_'.$opm->opm_id;
        
        
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];//终端ip
        $this->nonce_str = eeString::randomString(16);
        
        //签名
        $this->SetSign();
        $this->xml = $this->ToXml();
        
//         var_dump($this->xml);
//         exit;
        
        $response = eeNet::postXmlCurl($this->xml, self::UNIFIEDORDER, false, 6);
//         var_dump($response);
//         exit;
        
        $this->scenario = 'JSAPI_return';
        $response = $this->FromXml($response);
//         var_dump($response);
//         exit;
        
        if ($this->result_code != 'SUCCESS') {
            //something wrong
            throw new HttpException(404, 'UnifiedOrder API return fail');
        }

        return $response;
    }
    
    
    /**
     * create unifiedOrder for QR mode, need get qr url from wechat pay api.
     */
    public function createQRUnifiedOrder($pm_id){
        
        $tradeType = 'NATIVE';
        
        //check paid, if already paid, direct do the return.
        if ($this->order->or_status_id == OrderPaymentMethod::OPM_PAID) {
            //already paid
            echo json_encode(['code'=>2]);
            exit;
        }
        
        //create orderPaymentMethod first, use this to skip duplicate out_trade_no, error
        $opm = new OrderPaymentMethod(['scenario'=>'prePay']);
        $opm->opm_order_id = $this->order->or_id;
        $opm->opm_price = $this->order->or_price;
        $opm->opm_paymentMethod_id = $pm_id;
        $opm->opm_tradeType_send = $tradeType;
        
        $opm->save();

        
        $this->body = $this->order->or_productTitle;
        $this->total_fee = $this->order->or_price;
        $this->product_id = $this->order->or_product_id;

        
        $this->trade_type = $tradeType;
        $this->out_trade_no = 'QXM'.$opm->opm_order_id.'_'.$opm->opm_id;
        
        
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];//终端ip
        $this->nonce_str = eeString::randomString(16);
        
        //签名
        $this->SetSign();
        $this->xml = $this->ToXml();
        
//         var_dump($this->xml);
//         exit;
        
        $response = eeNet::postXmlCurl($this->xml, self::UNIFIEDORDER, false, 6);
//         var_dump($response);
//         exit;
        
        $this->scenario = 'QR_return';
        $response = $this->FromXml($response);
        
        if ($this->result_code != 'SUCCESS') {
            //something wrong
            throw new HttpException(404, 'UnifiedOrder API return fail');
        }

        return $response;
    }
    
    
    /**
     * create unifiedOrder for WAP mode, need get WAP jump url from wechat pay api.
     */
    public function createWapUnifiedOrder(){
        
        $tradeType = 'WAP';
        $pm_id = PaymentMethod::PM_WECHATMOBILE;
        
        //check paid, if already paid, direct do the return.
        if ($this->order->or_status_id == OrderPaymentMethod::OPM_PAID) {
            //already paid
            echo json_encode(['code'=>2]);
            exit;
        }
        
        //create orderPaymentMethod first, use this to skip duplicate out_trade_no, error
        $opm = new OrderPaymentMethod(['scenario'=>'prePay']);
        $opm->opm_order_id = $this->order->or_id;
        $opm->opm_price = $this->order->or_price;
        $opm->opm_paymentMethod_id = $pm_id;
        $opm->opm_tradeType_send = $tradeType;
        
        $opm->save();

        
        $this->body = $this->order->or_productTitle;
        $this->total_fee = $this->order->or_price;
        $this->product_id = $this->order->or_product_id;

        
        $this->trade_type = $tradeType;
        $this->out_trade_no = 'QXM'.$opm->opm_order_id.'_'.$opm->opm_id;
        
        
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];//终端ip
        $this->nonce_str = eeString::randomString(16);
        
        //签名
        $this->SetSign();
        $this->xml = $this->ToXml();
        
//         var_dump($this->xml);
//         exit;
        
        $response = eeNet::postXmlCurl($this->xml, self::UNIFIEDORDER, false, 6);
        var_dump($response);
        exit;
        
        $this->scenario = 'WAP_return';
        $response = $this->FromXml($response);
        
        if ($this->result_code != 'SUCCESS') {
            var_dump($this->result_code);
            exit;
            //something wrong
            throw new HttpException(404, 'UnifiedOrder API return fail');
        }

        return $response;
    }
    
}
