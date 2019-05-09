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

use Yii;
use yii\web\HttpException;



/**
 * network functions
 * @author boylee
 *
 */
class eeNet {
    
    /**
     * input url do get and return get result string
     * 
     * @return string get result
     */
    
    public static function load($url, $header=null, $https = false, $repeatTimes = 3, $inputCookies = ''){
        if (empty($url)) {
        	return '';
        }//do nothing for empty
        
        //start log
//         Yii::log('========= START Grabing : '.$url, 'crawler');
        
        //params
        //init
        $ch = curl_init();

        //set options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return result
//         curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1); //follow 301

        //user agent
        $userAgent = eeNet::getUserAgent();
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);   //set User-Agent
        
        
        //outtime
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 6); //connection timeout
        curl_setopt ($ch, CURLOPT_TIMEOUT, 20); //timeout
        
        //set more header
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER , $header );
        }
        
//         curl_setopt($ch, CURLOPT_HEADER, 0); //no need response header, faster
        curl_setopt($ch, CURLOPT_HEADER, TRUE);    //表示需要response header
        curl_setopt($ch, CURLOPT_NOBODY, FALSE); //表示需要response body
//         curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        
        
        //for https, skip verify paper and host
        if ($https) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        
        
        
        //gzip force decode.
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        
        
        //input cookies
        if (!empty($inputCookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $inputCookies);
        }
        
        //get result
        $response = curl_exec($ch);
        if ($response == false) {
            var_dump(curl_error($ch));
            exit;
        }
//         var_dump($response);
//         exit;
        
        //do regrab for curl fail
        //url wrong or dns fail or net problem
        for ($i = 1; $i <= $repeatTimes; $i++) {
            if ($response == false) {
                //if not found, sleep 5 and redo.
//                 Yii::log("$i load fail for $url ", 'crawler');
                sleep(2);
                $response = curl_exec($ch);
            }else{
                break;
            }
        }
        
        if ($response === false) {
            //nothing load, return false as end.
            return $response;
        }
        
        
                
        //split header and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        
        //200 for success
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            $body = substr($response, $headerSize);
        }
        
        //if 302 - redirected try fake cookie
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '302') {
            
            //try set cookies and re get
            //use regex load cookie values
            //Set-Cookie:(.*?)Domain
            preg_match_all('|Set-Cookie:(.*?)Domain|', $header, $cookiesArr);
//             var_dump($cookiesArr[1]);
//             exit;
            
            $cookieValue = '';
            if (isset($cookiesArr[1])) {
                foreach ($cookiesArr[1] as $key => $value) {
                    $cookieValue .= trim($value);
                }
            }
            
            if (!empty($cookieValue)) {
                //cookies
                //get set cookies from header and set it.
                
                curl_setopt($ch, CURLOPT_COOKIE, $cookieValue);
                
                
                //reload                
                //get result
                $response = curl_exec($ch);
                
                //reload success check
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
                    //split header and body
                    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
//                     $header = substr($response, 0, $headerSize);
                    //only need new body here.
                    $body = substr($response, $headerSize);
                }else {
                    echo '302 reload fail.';
                }
            }
        }
        
        if (!in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), [200, 302])){
            //no 200, no 302, use status code as body, it's all we need.
            $body = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        
        //close
        curl_close($ch);
//         Yii::log('========= END Grabed : '.strlen($body), 'crawler');        
        
        //return 
        return $body;
    }
    
    
    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    
//         //如果有配置代理这里就设置代理
//         if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
//                 && WxPayConfig::CURL_PROXY_PORT != 0){
//             curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
//             curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
//         }

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
//         if($useCert == true){
//             //设置证书
//             //使用证书：cert 与 key 分别属于两个.pem文件
//             curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//             curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
//             curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//             curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
//         }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new HttpException(400, "curl出错，错误码:$error");
        }
    }
    
    
    /**
     * post data to given url, allow change header
     * @param array|string $postData
     * @param string $url
     * @param array $header
     * @param array $opt
     * @param bool $sslEnabled
     * @param number $second
     * @throws HttpException
     * @return mixed
     */
    public static function postCurl($postData, $url, $header = [], $opt = [], $sslEnabled = 0, $second = 30)
    {
    
        $ch = curl_init();
    
        //setting for overtime
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    
        //user agent
        $userAgent = eeNet::getUserAgent();
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);   //set User-Agent
    
        curl_setopt($ch,CURLOPT_URL, $url);
    
        if ($sslEnabled) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE); //check ssl
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2); //check hard
        }else{
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
    
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        //set more header
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
    
    
        //         //return result.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        //         //config post as verb
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
    
        //         //load all external opt at the end
        foreach ($opt as $oneKey => $oneV) {
            curl_setopt($ch, $oneKey, $oneV);
        }
    
    
        //run curl
        $data = curl_exec($ch);
    
        //         var_dump($data);
        //         exit;
        //result
        if($data ){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new HttpException(400, "curl fail，error code:$error");
        }
    }
    
    
    
    /**
     * send sms to given phone with given message by direct request url.
     * @param unknown $message
     */
    public static function sendSMS($phone, $message) {
        
        $username = \Yii::$app->params['sms.username'];
        $pass = \Yii::$app->params['sms.password'];
        $seed = date('YmdHis');
        $key = md5(md5($pass).$seed);
        
        $content = $message;
        //add affix
        //【签小秘】
        if (!strpos($content, '【签小秘】')) {
            $content .='【签小秘】';
        }//add affix
        
        $content =  mb_convert_encoding($content, 'gb2312', 'utf-8');
        $content =  urlencode($content);
        
        
        //support group message
        if (is_array($phone)) {
            $phones = implode(',', $phone);
        }else{
            $phones = $phone;
        }
        $url = "http://api.jj-mobile.com:8080/eums/send_strong.do?name=$username&seed=$seed&key=$key&dest=$phones&content=$content";
        
        return eeNet::load($url);
        
    }
    
    
    /**
     * for yuntongxun.com send sms via restful api.
     * @param string $phone
     * @param array $message, replace data
     */
    public static function sendSMSRESTful($phone, $message) {
    
        $accountId = \Yii::$app->params['sms.accountId'];
        $accountAuthToken = \Yii::$app->params['sms.accountAuthToken'];
        
        $curDate = date('YmdHis');
        
        //encrypt
        $sigParam = strtoupper(md5($accountId.$accountAuthToken.$curDate));
        
        $url = "https://app.cloopen.com:8883/2013-12-26/Accounts/$accountId/SMS/TemplateSMS?sig=$sigParam";
//         $url = "http://localhost/lm960/frontend/web/index_l.php/sms/r";
        
//         var_dump($url);
        
        
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json; charset=utf-8';
//         $headers[] = 'Content-Length: 123;';
        $headers[] = 'Authorization:'.base64_encode($accountId.':'.$curDate);
//         $header = array("Accept:application/json","Content-Type:application/json;charset=utf-8","Authorization:123");
        
//         $headers = ["Accept:application/json","Content-Type:application/json;charset=utf-8"];

        $data['to'] = $phone;
        $data['appId'] = \Yii::$app->params['sms.appId'];
        $data['templateId'] = \Yii::$app->params['sms.templateId'];
        
        $data['datas'] = $message;
//         var_dump($data);
//         exit;
        
        return eeNet::postCurl(json_encode($data), $url, $headers);
    
    }
    
    
    /**
     * get random useragent
     * @return string
     */
    public static function getUserAgent() {
        $thisRandCount = rand(0, 6);
        
        
        //user agent
        $userAgent = ''; //empty for 0
        $userAgents = []; //empty for 0
        //try mac header
        $userAgents[] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2657.0 Safari/537.36';
        //猎豹浏览器2.0.10.3198 急速模式on Windows 7 x64：
        $userAgents[] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER';
        //360 Quick
        $userAgents[] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1';
        //Firefox x64 4.0b13pre on Windows 7 x64：
        $userAgents[] = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b13pre) Gecko/20110307 Firefox/4.0b13pre';
        //Chrome x86 10.0.648.133 on Windows 7 x64：
        $userAgents[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16';
        //IE9 x64 9.0.8112.16421 on Windows 7 x64：
        $userAgents[] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0)';
        
        if (isset($userAgents[$thisRandCount])) {
            $userAgent = $userAgents[$thisRandCount];
        }
        
        return $userAgent;
    }
}