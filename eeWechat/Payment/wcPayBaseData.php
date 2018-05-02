<?php

namespace eeTools\eeWechat\Payment;

use yii\web\HttpException;
use yii\base\Model;
use common\models\Order;


/**
 * 
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出xml格式的参数、从xml读取数据对象等
 * @author widyhu
 *
 */
class wcPayBaseData extends Model
{
	
	protected $appId;
	protected $appid;
	protected $mch_id;	
	protected $key; //pay api key
	
	protected $sign;
	protected $xml;
	
	protected $safeKeys = []; //key list for all sort values
	protected $safeValues = []; //safe value list for encrypt only.
	
	//order
	public $o_id;
	
	/**
	 * 
	 * @var common\models\Order
	 */
	protected $order;
	
	
	public function init(){
	    parent::init();
	
	    //init important params
	    $this->appId = \Yii::$app->params['wechat.appId'];
	    $this->appid = \Yii::$app->params['wechat.appId'];
	    $this->mch_id = \Yii::$app->params['wechat.pay.mchId'];
	    $this->key = \Yii::$app->params['wechat.pay.key'];
	
	}
	
	/**
	* 设置签名，详见签名生成算法
	* @param string $value 
	**/
	public function SetSign()
	{
	    //safeValue generate
	    $this->makeSafeValues();
	    
		$sign = $this->MakeSign();
		$this->sign = $sign;
		return $sign;
	}
	/**
	* prepare safeValues from safeKeys
	* @param string $value 
	**/
	public function makeSafeValues()
	{
	    //init
	    $this->safeValues = [];
	    
	    foreach ($this->safeKeys as $oneKey)
	    {
	        if (!$this->hasProperty($oneKey) || $this->{$oneKey} == '') {
	            continue;
	        }//skip for not exist and empty
	        	
	        $this->safeValues[$oneKey] = $this->{$oneKey};
	    }
	    
// 	    var_dump($this->safeKeys);
	    
// 	    var_dump($this->safeValues);
// 	    exit;
	}
	
	
	
	/**
	* 获取签名，详见签名生成算法的值
	* @return 值
	**/
	public function GetSign()
	{
		return $this->sign;
	}
	
	/**
	* 判断签名，详见签名生成算法是否存在
	* @return true 或 false
	**/
	public function IsSignSet()
	{
		return array_key_exists('sign', $this->values);
	}

	/**
	 * 输出xml字符
	 * @throws WxPayException
	**/
	public function ToXml()
	{
	    $this->makeSafeValues();
	    
		if(!is_array($this->safeValues) 
			|| count($this->safeValues) <= 0)
		{
    		throw new HttpException(404, "数组数据异常！");
    	}
    	
    	$xml = "<xml>";
    	foreach ($this->safeValues as $oneKey=>$tmpVal)
    	{
    		if (is_numeric($tmpVal)){
    			$xml.="<".$oneKey.">".$tmpVal."</".$oneKey.">";
    		}else{
    			$xml.="<".$oneKey."><![CDATA[".$tmpVal."]]></".$oneKey.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}
	
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	public function FromXml($xml)
	{	
		if(!$xml){
			throw new HttpException(404, "xml数据异常！");
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $tmpValues = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        
        $this->attributes = $tmpValues;
        
        return $tmpValues;
	}
	
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->safeValues as $oneKey=>$tmpValue)
		{
			if($oneKey != "sign" && $tmpValue != "" && !is_array($tmpValue)){
				$buff .= $oneKey . "=" . $tmpValue . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign()
	{
		//签名步骤一：按字典序排序参数
		ksort($this->safeValues);

		$string = $this->ToUrlParams();

		//签名步骤二：在string后加入KEY, pay api key
		$string = $string . "&key=".$this->key;
		
		//签名步骤三：MD5加密
		$string = md5($string);
		
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
// 		var_dump($result);
// 		exit;
		return $result;
	}
	
	/**
	 * 获取设置的值
	 */
	public function GetValues()
	{
		return $this->values;
	}
	
	/**
	 * load linked order.
	 * @throws HttpException
	 */
	public function findOrder() {
        $this->order = Order::find()->where(['or_id'=>$this->o_id, 'or_creationUser_id'=>\Yii::$app->user->id])->one();
	    if (empty($this->order)) {
	        throw new HttpException(404, 'order not found. 133');
	    }
	    
	    $this->order->switchPrice(0);
	}
}